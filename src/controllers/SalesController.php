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
        $refreshed = PluginSales::$plugin->sales->refresh();

        if ($refreshed !== false) {
            Craft::$app->getSession()->setNotice(
                Craft::t('plugin-sales', '{count} plugin sales successfully refreshed.', ['count' => $refreshed])
            );
        }
        else {
            Craft::$app->getSession()->setError(
                Craft::t('plugin-sales', 'Plugin sales could not be refreshed. Check the credentials and network connection.')
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

        $request = Craft::$app->getRequest();
        $data = PluginSales::$plugin->reports->getSalesData($request->get('start'), $request->get('end'));
        $data = json_decode($data);

        foreach ($data as $row) {
            $csv .= implode(',', $row).PHP_EOL;
        }

        return Craft::$app->getResponse()->sendContentAsFile($csv, 'plugin-sales.csv');
    }
}
