<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\pluginsales\services;

use Craft;
use craft\base\Component;
use DateInterval;
use DateTime;
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
        'monthlyTotals' => 'pluginSales.monthlyTotals',
        'monthlyLicenseTotals' => 'pluginSales.monthlyLicenseTotals',
        'monthlyRenewalTotals' => 'pluginSales.monthlyRenewalTotals',
        'months' => 'pluginSales.months',
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
     * Returns monthly totals.
     *
     * @return array
     */
    public function getMonthlyTotals(): array
    {
        if ($monthlyTotals = Craft::$app->getCache()->get(self::CACHE_KEYS['monthlyTotals'])) {
            return $monthlyTotals;
        }

        $monthlyTotals = $this->_getMonthlyTotalsQuery()->all();

        Craft::$app->getCache()->set(self::CACHE_KEYS['monthlyTotals'], $monthlyTotals);

        return $monthlyTotals;
    }

    /**
     * Returns monthly license totals.
     *
     * @return array
     */
    public function getMonthlyLicenseTotals(): array
    {
        if ($monthlyTotals = Craft::$app->getCache()->get(self::CACHE_KEYS['monthlyLicenseTotals'])) {
            return $monthlyTotals;
        }

        $monthlyTotals = $this->_getMonthlyLicenseRenewalTotals(false);

        Craft::$app->getCache()->set(self::CACHE_KEYS['monthlyLicenseTotals'], $monthlyTotals);

        return $monthlyTotals;
    }

    /**
     * Returns monthly renewal totals.
     *
     * @return array
     */
    public function getMonthlyRenewalTotals(): array
    {
        if ($monthlyTotals = Craft::$app->getCache()->get(self::CACHE_KEYS['monthlyRenewalTotals'])) {
            return $monthlyTotals;
        }

        $monthlyTotals = $this->_getMonthlyLicenseRenewalTotals(true);

        Craft::$app->getCache()->set(self::CACHE_KEYS['monthlyRenewalTotals'], $monthlyTotals);

        return $monthlyTotals;
    }

    /**
     * Returns all months of plugin sales.
     *
     * @return array
     */
    public function getMonths()
    {
        if ($months = Craft::$app->getCache()->get(self::CACHE_KEYS['months'])) {
            return $months;
        }

        $monthlyTotals = $this->getMonthlyTotals();

        if (empty($monthlyTotals)) {
            return [];
        }

        $firstMonth = reset($monthlyTotals);
        $lastMonth = end($monthlyTotals);
        $currentMonth = new DateTime($firstMonth['year'].'-'.$firstMonth['month'].'-1');
        $months = [];

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
     * Returns monthly license or renewal totals.
     *
     * @param bool $renewal
     *
     * @return array
     */
    private function _getMonthlyLicenseRenewalTotals(bool $renewal): array
    {
        $monthlyTotals = $this->_getMonthlyTotalsQuery()
            ->where(['renewal' => $renewal])
            ->all();

        $allMonthlyTotals = [];
        $currentMonthIndex = 0;

        foreach ($this->getMonths() as $month) {
            if ($currentMonthIndex >= count($monthlyTotals)) {
                break;
            }

            $monthlyTotal = $monthlyTotals[$currentMonthIndex];
            $currentMonth = new DateTime($monthlyTotal['year'].'-'.$monthlyTotal['month'].'-1');

            if ($currentMonth->format(self::MONTH_FORMAT) == $month) {
                $allMonthlyTotals[] = $monthlyTotal['grossAmount'];
                $currentMonthIndex++;
            }
            else {
                $allMonthlyTotals[] = 0;
            }
        }

        return $allMonthlyTotals;
    }
}
