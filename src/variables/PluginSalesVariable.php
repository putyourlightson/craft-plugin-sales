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
     * Returns cached plugin sales.
     *
     * @param int|null $limit
     *
     * @return string
     */
    public function getCachedSales(int $limit = null)
    {
        $cacheKey = 'PluginSales'.($limit ?? '*');
        $data = Craft::$app->getCache()->get($cacheKey);

        if ($data !== false) {
            return $data;
        }

        $data = [];

        $saleModels = $this->get($limit);

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
     * Returns cached plugin sales.
     *
     * @param int|null $limit
     *
     * @return string
     */
    public function getCachedAmounts(int $limit = null)
    {
        $cacheKey = 'PluginSalesAmounts'.($limit ?? '*');
        $data = Craft::$app->getCache()->get($cacheKey);

        if ($data !== false) {
            return $data;
        }

        $data = [
            'categories' => [],
            'values' => [],
        ];

        $saleModels = $this->get($limit);

        foreach ($saleModels as $saleModel) {
            $month = $saleModel->dateSold->format('m-Y');

            $value = $data['categories'][$month] ?? 0;
            $data['categories'][$month] = $month;
            $data['values'][$month] = $value + $saleModel->grossAmount;
        }

        $data = json_encode($data);

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
    public function get(int $limit = null)
    {
        return PluginSales::$plugin->sales->get($limit);
    }

    /**
     * Refreshes plugin sales.
     *
     * @throws ForbiddenHttpException
     */
    public function refresh()
    {
        PluginSales::$plugin->sales->refresh();
    }
}
