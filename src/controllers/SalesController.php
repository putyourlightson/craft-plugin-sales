<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\pluginsales\controllers;

use Craft;
use craft\helpers\Queue;
use craft\web\Controller;
use putyourlightson\pluginsales\jobs\RefreshSalesJob;
use putyourlightson\pluginsales\PluginSales;
use yii\web\Response;

class SalesController extends Controller
{
    /**
     * Refreshes plugin sales.
     */
    public function actionRefresh(): Response
    {
        Queue::push(
            job: new RefreshSalesJob(),
            ttr: PluginSales::$plugin->settings->refreshSalesJobTtr,
        );

        return $this->asSuccess(
            message: Craft::t('plugin-sales', 'Plugin sales queued for refreshing.'),
            redirect: 'plugin-sales',
        );
    }

    /**
     * Refreshes all plugin sales.
     */
    public function actionRefreshAll(): Response
    {
        $this->requireAdmin();

        PluginSales::$plugin->sales->delete();

        Queue::push(
            job: new RefreshSalesJob(),
            ttr: PluginSales::$plugin->settings->refreshSalesJobTtr,
        );

        return $this->asSuccess(
            message: Craft::t('plugin-sales', 'Plugin sales successfully deleted and queued for refreshing.'),
            redirect: 'settings/plugins/plugin-sales',
        );
    }

    /**
     * Exports sales to CSV.
     */
    public function actionExport(): Response
    {
        $csv = '';

        $request = Craft::$app->getRequest();
        $data = PluginSales::$plugin->reports->getSalesData(null, $request->get('start'), $request->get('end'));

        foreach ($data as $row) {
            $csv .= implode(',', $row) . PHP_EOL;
        }

        return Craft::$app->getResponse()->sendContentAsFile($csv, 'plugin-sales.csv');
    }
}
