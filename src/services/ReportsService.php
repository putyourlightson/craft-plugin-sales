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

    /**
     * Returns plugin sales data.
     *
     * @param string|null $start
     * @param string|null $end
     *
     * @return string
     */
    public function getSalesData(string $start = null, string $end = null): string
    {
        $data = [];

        $query = SaleRecord::find()
            ->with('plugin')
            ->orderBy(['dateSold' => SORT_DESC]);

        $query = $this->_applyDataRange($query, $start, $end);

        foreach ($query->all() as $sale) {
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

        return $data;
    }

    /**
     * Returns totals.
     *
     * @param string|null $start
     * @param string|null $end
     *
     * @return array
     */
    public function getTotals(string $start = null, string $end = null): array
    {
        $totals = $this->_populateZeroValues(['grossAmount', 'netAmount']);

        $sales = $this->_getTotalsQuery($start, $end)->all();

        foreach ($sales as $sale) {
            $totals['grossAmount'] = $sale['grossAmount'];
            $totals['netAmount'] = $sale['netAmount'];
        }

        return $totals;
    }

    /**
     * Returns plugin totals.
     *
     * @param string|null $start
     * @param string|null $end
     *
     * @return array
     */
    public function getPluginTotals(string $start = null, string $end = null): array
    {
        $totals = $this->_populateZeroValues(PluginSales::$plugin->plugins->getNames());

        $sales = $this->_getTotalsQuery($start, $end)
            ->addSelect(['pluginId', 'name'])
            ->groupBy(['pluginId'])
            ->joinWith('plugin')
            ->all();

        foreach ($sales as $sale) {
            $totals[$sale['name']] = $sale['grossAmount'];
        }

        return $totals;
    }

    /**
     * Returns license and renewal totals.
     *
     * @param string|null $start
     * @param string|null $end
     *
     * @return array
     */
    public function getLicenseRenewalTotals(string $start = null, string $end = null): array
    {
        $totals = $this->_populateZeroValues(['licenses', 'renewals']);

        $sales = $this->_getTotalsQuery($start, $end)
            ->addSelect(['renewal'])
            ->groupBy(['renewal'])
            ->all();

        foreach ($sales as $sale) {
            $key = $sale['renewal'] ? 'renewals' : 'licenses';
            $totals[$key] = $sale['grossAmount'];
        }

        return $totals;
    }

    /**
     * Returns all months of plugin sales.
     *
     * @param string|null $start
     * @param string|null $end
     *
     * @return array
     */
    public function getMonths(string $start = null, string $end = null): array
    {
        $months = [];
        $sales = $this->getMonthlyTotals($start, $end);

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

        return $months;
    }

    /**
     * Returns monthly totals.
     *
     * @param string|null $start
     * @param string|null $end
     *
     * @return array
     */
    public function getMonthlyTotals(string $start = null, string $end = null): array
    {
        return $this->_getMonthlyTotalsQuery($start, $end)->all();
    }

    /**
     * Returns monthly plugin totals.
     *
     * @param string|null $start
     * @param string|null $end
     *
     * @return array
     */
    public function getMonthlyPluginTotals(string $start = null, string $end = null): array
    {
        $totals = $this->_populateZeroValues(
            PluginSales::$plugin->plugins->getNames(),
            $this->getMonths($start, $end)
        );

        $sales = $this->_getMonthlyTotalsQuery($start, $end)
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

        return $totals;
    }

    /**
     * Returns monthly license and renewal totals.
     *
     * @param string|null $start
     * @param string|null $end
     *
     * @return array
     */
    public function getMonthlyLicenseRenewalTotals(string $start = null, string $end = null): array
    {
        $totals = $this->_populateZeroValues(
            ['licenses', 'renewals'],
            $this->getMonths($start, $end)
        );

        $sales = $this->_getMonthlyTotalsQuery($start, $end)
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

        return $totals;
    }

    /**
     * Returns totals query.
     *
     * @param string|null $start
     * @param string|null $end
     *
     * @return ActiveQuery
     */
    private function _getTotalsQuery(string $start = null, string $end = null): ActiveQuery
    {
        $query = SaleRecord::find()
            ->select([
                'COUNT(*) as count',
                'ROUND(SUM(grossAmount), 2) as grossAmount',
                'ROUND(SUM(netAmount), 2) as netAmount',
            ])
            ->asArray();

        $query = $this->_applyDataRange($query, $start, $end);

        return $query;
    }

    /**
     * Returns monthly totals query.
     *
     * @param string|null $start
     * @param string|null $end
     *
     * @return ActiveQuery
     */
    private function _getMonthlyTotalsQuery(string $start = null, string $end = null): ActiveQuery
    {
        $select = [
            'COUNT(*) as count',
            'ROUND(SUM(grossAmount), 2) as grossAmount',
            'ROUND(SUM(netAmount), 2) as netAmount',
            'MONTH(dateSold) as month',
            'YEAR(dateSold) as year',
        ];

        // Special format for postgres
        if (Craft::$app->getDb()->getIsPgsql()) {
            $select = [
                'COUNT(*) as count',
                'ROUND(SUM("grossAmount")::numeric, 2) as grossAmount',
                'ROUND(SUM("netAmount")::numeric, 2) as netAmount',
                'EXTRACT(month from "dateSold") as month',
                'EXTRACT(year from "dateSold") as year',
            ];
        }

        $query = SaleRecord::find()
            ->select($select)
            ->groupBy(['year', 'month'])
            ->orderBy(['year' => SORT_ASC, 'month' => SORT_ASC])
            ->asArray();

        $query = $this->_applyDataRange($query, $start, $end);

        return $query;
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

    /**
     * Applies a date range condition to a query.
     *
     * @param ActiveQuery $query
     * @param string|null $start
     * @param string|null $end
     *
     * @return ActiveQuery
     */
    private function _applyDataRange(ActiveQuery $query, string $start = null, string $end = null): ActiveQuery
    {
        if ($start) {
            $query->andWhere(['>=', 'dateSold', $start]);
        }

        if ($end) {
            $query->andWhere(['<=', 'dateSold', $end]);
        }

        return $query;
    }
}
