<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\pluginsales\services;

use Craft;
use craft\base\Component;
use DateInterval;
use DateTime;
use putyourlightson\pluginsales\PluginSales;
use putyourlightson\pluginsales\records\SaleRecord;
use yii\db\ActiveQuery;

/**
 *
 * @property string $salesData
 * @property array $monthlyTotals
 * @property array $monthlyLicenseTotals
 * @property array $monthlyRenewalTotals
 * @property array $months
 */
class ReportsService extends Component
{
    const MONTH_FORMAT = 'M Y';

    const CACHE_KEYS = [
        'salesData' => 'pluginSales.salesData',
        'totals' => 'pluginSales.totals',
        'pluginTotals' => 'pluginSales.pluginTotals',
        'licenseRenewalTotals' => 'pluginSales.licenseRenewalTotals',
        'months' => 'pluginSales.months',
        'monthlyTotals' => 'pluginSales.monthlyTotals',
        'monthlyPluginTotals' => 'pluginSales.monthlyPluginTotals',
        'monthlyLicenseRenewalTotals' => 'pluginSales.monthlyLicenseRenewalTotals',
    ];

    /**
     * Returns cached plugin sale data.
     *
     * @return string
     */
    public function getSalesData(): string
    {
        if ($data = Craft::$app->getCache()->get(self::CACHE_KEYS['salesData'])) {
            return $data;
        }

        $data = [];

        $sales = SaleRecord::find()
            ->with('plugin')
            ->orderBy(['dateSold' => SORT_DESC])
            ->all();

        foreach ($sales as $sale) {
            $data[] = [
                $sale->plugin->name,
                ucfirst($sale->edition),
                Craft::t('plugin-sales', $sale->renewal ? 'Renewal' : 'License'),
                $sale->email,
                number_format($sale->grossAmount, 2),
                number_format($sale->netAmount, 2),
                $sale->dateSold,
            ];
        }

        $data = json_encode($data);

        Craft::$app->getCache()->set(self::CACHE_KEYS['salesData'], $data);

        return $data;
    }

    /**
     * Returns totals.
     *
     * @return array
     */
    public function getTotals(): array
    {
        if ($totals = Craft::$app->getCache()->get(self::CACHE_KEYS['totals'])) {
            //return $totals;
        }

        $totals = $this->_populateZeroValues(['grossAmount', 'netAmount']);

        $sales = $this->_getTotalsQuery()->all();

        foreach ($sales as $sale) {
            $totals['grossAmount'] = $sale['grossAmount'];
            $totals['netAmount'] = $sale['netAmount'];
        }

        Craft::$app->getCache()->set(self::CACHE_KEYS['totals'], $totals);

        return $totals;
    }

    /**
     * Returns plugin totals.
     *
     * @return array
     */
    public function getPluginTotals(): array
    {
        if ($totals = Craft::$app->getCache()->get(self::CACHE_KEYS['pluginTotals'])) {
            //return $totals;
        }

        $totals = $this->_populateZeroValues(PluginSales::$plugin->plugins->getNames());

        $sales = $this->_getTotalsQuery()
            ->addSelect(['pluginId', 'name'])
            ->groupBy(['pluginId'])
            ->joinWith('plugin')
            ->all();

        foreach ($sales as $sale) {
            $totals[$sale['name']] = $sale['grossAmount'];
        }

        Craft::$app->getCache()->set(self::CACHE_KEYS['pluginTotals'], $totals);

        return $totals;
    }

    /**
     * Returns license and renewal totals.
     *
     * @return array
     */
    public function getLicenseRenewalTotals(): array
    {
        if ($totals = Craft::$app->getCache()->get(self::CACHE_KEYS['licenseRenewalTotals'])) {
            //return $totals;
        }

        $totals = $this->_populateZeroValues(['licenses', 'renewals']);

        $sales = $this->_getTotalsQuery()
            ->addSelect(['renewal'])
            ->groupBy(['renewal'])
            ->all();

        foreach ($sales as $sale) {
            $key = $sale['renewal'] ? 'renewals' : 'licenses';
            $totals[$key] = $sale['grossAmount'];
        }

        Craft::$app->getCache()->set(self::CACHE_KEYS['licenseRenewalTotals'], $totals);

        return $totals;
    }

    /**
     * Returns all months of plugin sales.
     *
     * @return array
     */
    public function getMonths(): array
    {
        if ($months = Craft::$app->getCache()->get(self::CACHE_KEYS['months'])) {
            return $months;
        }

        $months = [];
        $sales = $this->getMonthlyTotals();

        if (empty($sales)) {
            return $months;
        }

        $lastMonth = end($sales);
        $firstMonth = reset($sales);
        $currentMonth = new DateTime($firstMonth['year'].'-'.$firstMonth['month'].'-1');

        while ($currentMonth->format('n') <= $lastMonth['month']
            || $currentMonth->format('Y') < $lastMonth['year']
        ) {
            $months[] = $currentMonth->format(self::MONTH_FORMAT);
            $currentMonth->add(new DateInterval('P1M'));
        }

        Craft::$app->getCache()->set(self::CACHE_KEYS['months'], $months);

        return $months;
    }

    /**
     * Returns monthly totals.
     *
     * @return array
     */
    public function getMonthlyTotals(): array
    {
        if ($totals = Craft::$app->getCache()->get(self::CACHE_KEYS['monthlyTotals'])) {
            return $totals;
        }

        $totals = $this->_getMonthlyTotalsQuery()->all();

        Craft::$app->getCache()->set(self::CACHE_KEYS['monthlyTotals'], $totals);

        return $totals;
    }

    /**
     * Returns monthly plugin totals.
     *
     * @return array
     */
    public function getMonthlyPluginTotals(): array
    {
        if ($totals = Craft::$app->getCache()->get(self::CACHE_KEYS['monthlyPluginTotals'])) {
            return $totals;
        }

        $totals = $this->_populateZeroValues(
            PluginSales::$plugin->plugins->getNames(),
            $this->getMonths()
        );

        $sales = $this->_getMonthlyTotalsQuery()
            ->addSelect(['pluginId', 'name'])
            ->addGroupBy(['pluginId'])
            ->joinWith('plugin')
            ->all();

        if (empty($sales)) {
            return $totals;
        }

        foreach ($sales as $sale) {
            $key = $sale['name'];
            $currentMonth = new DateTime($sale['year'].'-'.$sale['month'].'-1');

            $totals[$key][$currentMonth->format(self::MONTH_FORMAT)] = $sale['grossAmount'];
        }

        foreach ($totals as $key => $values) {
            $totals[$key] = array_values($values);
        }

        Craft::$app->getCache()->set(self::CACHE_KEYS['monthlyPluginTotals'], $totals);

        return $totals;
    }

    /**
     * Returns monthly license and renewal totals.
     *
     * @return array
     */
    public function getMonthlyLicenseRenewalTotals(): array
    {
        if ($totals = Craft::$app->getCache()->get(self::CACHE_KEYS['monthlyLicenseRenewalTotals'])) {
            return $totals;
        }

        $totals = $this->_populateZeroValues(
            ['licenses', 'renewals'],
            $this->getMonths()
        );

        $sales = $this->_getMonthlyTotalsQuery()
            ->addSelect(['renewal'])
            ->addGroupBy(['renewal'])
            ->all();

        if (empty($sales)) {
            return $totals;
        }

        foreach ($sales as $sale) {
            $key = $sale['renewal'] ? 'renewals' : 'licenses';
            $currentMonth = new DateTime($sale['year'].'-'.$sale['month'].'-1');

            $totals[$key][$currentMonth->format(self::MONTH_FORMAT)] = $sale['grossAmount'];
        }

        foreach ($totals as $key => $values) {
            $totals[$key] = array_values($values);
        }

        Craft::$app->getCache()->set(self::CACHE_KEYS['monthlyLicenseRenewalTotals'], $totals);

        return $totals;
    }

    /**
     * Clears cached reports.
     */
    public function clearCachedReports()
    {
        foreach (self::CACHE_KEYS as $cacheKey) {
            Craft::$app->getCache()->delete($cacheKey);
        }
    }

    /**
     * Returns totals query.
     *
     * @return ActiveQuery
     */
    private function _getTotalsQuery(): ActiveQuery
    {
        return SaleRecord::find()
            ->select([
                'COUNT(*) as count',
                'ROUND(SUM(grossAmount), 2) as grossAmount',
                'ROUND(SUM(netAmount), 2) as netAmount',
            ])
            ->asArray();
    }

    /**
     * Returns monthly totals query.
     *
     * @return ActiveQuery
     */
    private function _getMonthlyTotalsQuery(): ActiveQuery
    {
        return SaleRecord::find()
            ->select([
                'MONTH(dateSold) as month',
                'YEAR(dateSold) as year',
                'COUNT(*) as count',
                'ROUND(SUM(grossAmount), 2) as grossAmount',
                'ROUND(SUM(netAmount), 2) as netAmount',
            ])
            ->groupBy(['YEAR(dateSold)', 'MONTH(dateSold)'])
            ->orderBy(['YEAR(dateSold)' => SORT_ASC, 'MONTH(dateSold)' => SORT_ASC])
            ->asArray();
    }

    /**
     * Populates an array with zero values.
     *
     * @param array $keys
     * @param array|null $subkeys
     *
     * @return array
     */
    private function _populateZeroValues(array $keys, array $subkeys = null): array
    {
        $values = [];

        foreach ($keys as $key) {
            if (is_array($subkeys)) {
                foreach ($subkeys as $subkey) {
                    $values[$key][$subkey] = 0;
                }
            }
            else {
                $values[$key] = 0;
            }
        }

        return $values;
    }
}
