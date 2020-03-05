<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\pluginsales\services;

use Craft;
use craft\base\Component;
use putyourlightson\pluginsales\records\SaleRecord;
use yii\db\ActiveQuery;

/**
 * @property null|array $firstMonth
 * @property null|array $lastMonth
 * @property array $monthlyTotals
 */
class ReportsService extends Component
{
    /**
     * Returns cached plugin sale data.
     *
     * @return string
     */
    public function getSalesData()
    {
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

        return $data;
    }

    /**
     * Returns monthly license totals.
     *
     * @return array
     */
    public function getMonthlyLicenseTotals(): array
    {
        return $this->_getMonthlyTotalsQuery()
            ->where(['renewal' => false])
            ->all();
    }

    /**
     * Returns monthly renewal totals.
     *
     * @return array
     */
    public function getMonthlyRenewalTotals(): array
    {
        return $this->_getMonthlyTotalsQuery()
            ->where(['renewal' => true])
            ->all();
    }

    /**
     * Returns first month of plugin sales.
     *
     * @return array|null
     */
    public function getFirstMonth()
    {
        return $this->_getMonthQuery()
            ->orderBy(['YEAR(dateSold)' => SORT_ASC, 'MONTH(dateSold)' => SORT_ASC])
            ->one();
    }

    /**
     * Returns last month of plugin sales.
     *
     * @return array|null
     */
    public function getLastMonth()
    {
        return $this->_getMonthQuery()
            ->orderBy(['YEAR(dateSold)' => SORT_DESC, 'MONTH(dateSold)' => SORT_DESC])
            ->one();
    }

    /**
     * Returns monthly license totals query.
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
     * Returns month query.
     *
     * @return ActiveQuery
     */
    private function _getMonthQuery(): ActiveQuery
    {
        return SaleRecord::find()
            ->select(['MONTH(dateSold) as month', 'YEAR(dateSold) as year'])
            ->asArray();
    }
}
