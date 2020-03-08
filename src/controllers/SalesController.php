<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\pluginsales\controllers;

use Craft;
use craft\web\Controller;
use putyourlightson\pluginsales\PluginSales;

class SalesController extends Controller
{
    /**
     * Refreshes plugin sales.
     */
    public function actionRefresh()
    {
        $success = PluginSales::$plugin->sales->refresh();

        if (!$success) {
            Craft::$app->getSession()->setError(
                Craft::t('plugin-sales', 'Plugin sales could not be refreshed.')
            );
        }

        return $this->redirect('plugin-sales');
    }

    /**
     * Exports sales to CSV.
     */
    public function actionExport()
    {
        $csv = '';

        $data = PluginSales::$plugin->reports->getSalesData();
        $data = json_decode($data);

        foreach ($data as $row) {
            $csv .= implode(',', $row).PHP_EOL;
        }

        return Craft::$app->getResponse()->sendContentAsFile($csv, 'plugin-sales.csv');
    }
}
