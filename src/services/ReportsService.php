<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\pluginsales\services;

use Craft;
use craft\base\Component;
use craft\helpers\Db;
use craft\helpers\Html;
use DateInterval;
use DateTime;
use putyourlightson\pluginsales\PluginSales;
use putyourlightson\pluginsales\records\SaleRecord;
use yii\db\ActiveQuery;

class ReportsService extends Component
{
    /**
     * The month format to use in reports.
     */
    public const MONTH_FORMAT = 'M Y';

    /**
     * Returns plugin sales data.
     */
    public function getSalesData(string $start = null, string $end = null, string $email = null): string
    {
        $data = [];

        $query = SaleRecord::find()
            ->with('plugin')
            ->orderBy(['dateSold' => SORT_DESC])
            ->asArray();

        $this->_applyConditions($query, $start, $end, $email);
        $sales = $query->all();

        /** @var SaleRecord[] $sales */
        foreach ($sales as $sale) {
            $type = Craft::t('plugin-sales', $sale['renewal'] ? 'Renewal' : 'License');
            if ($sale['notice']) {
                $type .= Html::tag('span', '', [
                    'title' => $sale['notice'],
                    'class' => 'notice with-icon',
                ]);
            }

            $row = [];
            if ($email == null) {
                $row[] = Html::tag('a', $sale['email'], [
                    'onclick' => 'openCustomerSlideout(this.text)',
                ]);
            }

            $row[] = $sale['plugin']['name'];
            $row[] = ucfirst($sale['edition']);
            $row[] = $type;

            // Format but don't convert amounts
            $row[] = number_format($sale['grossAmount'], 2);
            $row[] = number_format($sale['netAmount'], 2);

            $row[] = $sale['dateSold'];

            $data[] = $row;
        }

        return json_encode($data);
    }

    /**
     * Returns customer data.
     */
    public function getCustomerData(string $start = null, string $end = null, string $email = null): string
    {
        $data = [];

        $customers = $this->_getTotalsQuery($start, $end, $email)
            ->addSelect(['email'])
            ->groupBy(['email'])
            ->orderBy(['netAmount' => SORT_DESC])
            ->all();

        foreach ($customers as $customer) {
            $email = Html::tag('a', $customer['email'], [
                'onclick' => 'openCustomerSlideout("' . $customer['email'] . '")',
            ]);
            $data[] = [
                $email,
                $customer['count'],
                // Format amounts but don't convert them
                number_format($customer['grossAmount'], 2),
                number_format($customer['netAmount'], 2),
            ];
        }

        return json_encode($data);
    }

    /**
     * Returns totals.
     */
    public function getTotals(string $start = null, string $end = null): array
    {
        $totals = $this->_populateZeroValues(['grossAmount', 'netAmount']);

        $sales = $this->_getTotalsQuery($start, $end)->all();

        foreach ($sales as $sale) {
            if ($sale['count'] > 0) {
                $totals['grossAmount'] = $this->_prepareAmount($sale['grossAmount']);
                $totals['netAmount'] = $this->_prepareAmount($sale['netAmount']);
            }
        }

        return $totals;
    }

    /**
     * Returns plugin totals.
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
            $totals[$sale['name']] = $this->_prepareAmount($sale['grossAmount']);
        }

        return $totals;
    }

    /**
     * Returns license and renewal totals.
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
            $totals[$key] = $this->_prepareAmount($sale['grossAmount']);
        }

        return $totals;
    }

    /**
     * Returns all months of plugin sales.
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
        $currentMonth = new DateTime($firstMonth['year'] . '-' . $firstMonth['month'] . '-1');

        while (
            ($currentMonth->format('n') <= $lastMonth['month'] && $currentMonth->format('Y') == $lastMonth['year'])
            || $currentMonth->format('Y') < $lastMonth['year']
        ) {
            $months[] = $currentMonth->format(self::MONTH_FORMAT);
            $currentMonth->add(new DateInterval('P1M'));
        }

        return $months;
    }

    /**
     * Returns monthly totals.
     */
    public function getMonthlyTotals(string $start = null, string $end = null): array
    {
        $monthlyTotals = $this->_getMonthlyTotalsQuery($start, $end)->all();

        foreach ($monthlyTotals as $key => $monthlyTotal) {
            $monthlyTotals[$key]['grossAmount'] = $this->_prepareAmount($monthlyTotal['grossAmount']);
            $monthlyTotals[$key]['netAmount'] = $this->_prepareAmount($monthlyTotal['netAmount']);
        }

        return $monthlyTotals;
    }

    /**
     * Returns monthly plugin totals.
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
            $currentMonth = new DateTime($sale['year'] . '-' . $sale['month'] . '-1');

            $totals[$key][$currentMonth->format(self::MONTH_FORMAT)] = $this->_prepareAmount($sale['grossAmount']);
        }

        foreach ($totals as $key => $values) {
            $totals[$key] = array_values($values);
        }

        return $totals;
    }

    /**
     * Returns monthly license and renewal totals.
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
            $currentMonth = new DateTime($sale['year'] . '-' . $sale['month'] . '-1');

            $totals[$key][$currentMonth->format(self::MONTH_FORMAT)] = $this->_prepareAmount($sale['grossAmount']);
        }

        foreach ($totals as $key => $values) {
            $totals[$key] = array_values($values);
        }

        return $totals;
    }

    /**
     * Returns totals query.
     */
    private function _getTotalsQuery(string $start = null, string $end = null, string $email = null): ActiveQuery
    {
        $query = SaleRecord::find()
            ->select([
                'COUNT(*) as count',
                'ROUND(SUM(grossAmount), 2) as grossAmount',
                'ROUND(SUM(netAmount), 2) as netAmount',
            ])
            ->asArray();

        $this->_applyConditions($query, $start, $end, $email);

        return $query;
    }

    /**
     * Returns monthly totals query.
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


        $this->_applyConditions($query, $start, $end);

        return $query;
    }

    /**
     * Converts and formats an amount.
     */
    private function _prepareAmount(float|int $value = 0): float
    {
        return round($value * PluginSales::$plugin->sales->getExchangeRate(), 2);
    }

    /**
     * Populates an array with zero values.
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
     * Applies condition to a sales query.
     */
    private function _applyConditions(ActiveQuery $query, string $start = null, string $end = null, string $email = null): void
    {
        $start = $start ? Db::prepareDateForDb($start . ' 00:00:00') : null;

        if ($start) {
            $query->andWhere(['>=', 'dateSold', $start]);
        }

        $end = $end ? Db::prepareDateForDb($end . ' 23:59:59') : null;

        if ($end) {
            $query->andWhere(['<=', 'dateSold', $end]);
        }

        if ($email) {
            $query->andWhere(['email' => $email]);
        }
    }
}
