<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\pluginsales\variables;

use Craft;
use putyourlightson\pluginsales\models\SaleModel;
use putyourlightson\pluginsales\PluginSales;
use yii\web\ForbiddenHttpException;

class PluginSalesVariable
{
    /**
     * Returns cached plugin sale data.
     *
     * @param int|null $limit
     *
     * @return string
     */
    public function getSalesData($limit = null)
    {
        $cacheKey = 'PluginSales.SalesData.'.($limit ?? '*');
        $data = Craft::$app->getCache()->get($cacheKey);

        if ($data !== false) {
            return $data;
        }

        $data = [];

        $saleModels = PluginSales::$plugin->sales->get($limit);

        foreach ($saleModels as $saleModel) {
            $data[] = [
                $saleModel->pluginName,
                ucfirst($saleModel->edition),
                Craft::t('plugin-sales', ($saleModel->renewal ? 'Renewal' : 'License')),
                $saleModel->email,
                $saleModel->grossAmount,
                $saleModel->netAmount,
                $saleModel->dateSold,
            ];
        }

        $data = json_encode($data);

        Craft::$app->getCache()->set($cacheKey, $data);

        return $data;
    }

    /**
     * Returns monthly plugin sale totals.
     *
     * @param int|null $limit
     *
     * @return string
     */
    public function getMonthlyTotals($limit = null)
    {
        $cacheKey = 'PluginSales.MonthlyTotals.'.($limit ?? '*');
        $data = Craft::$app->getCache()->get($cacheKey);

        if ($data !== false) {
            //return $data;
        }

        $data = PluginSales::$plugin->sales->getMonthlyTotals($limit);

        Craft::$app->getCache()->set($cacheKey, $data);

        return $data;
    }

    /**
     * Returns plugin sales.
     *
     * @param int|null $limit
     *
     * @return SaleModel[]
     */
    public function getSales($limit = null)
    {
        return PluginSales::$plugin->sales->get($limit);
    }

    /**
     * Refreshes plugin sales.
     *
     * @throws ForbiddenHttpException
     */
    public function refreshSales()
    {
        PluginSales::$plugin->sales->refresh();
    }
}
