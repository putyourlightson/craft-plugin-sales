<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\pluginsales\controllers;

use Craft;
use craft\web\Controller;
use putyourlightson\pluginsales\PluginSales;
use yii\web\Response;

class SalesController extends Controller
{
    /**
     * Refreshes plugin sales.
     */
    public function actionRefresh(): Response
    {
        $refreshed = PluginSales::$plugin->sales->refresh();

        if ($refreshed !== false) {
            Craft::$app->getSession()->setNotice(
                Craft::t('plugin-sales', '{count} plugin sale(s) refreshed.', ['count' => $refreshed])
            );
        } else {
            Craft::$app->getSession()->setError(
                Craft::t('plugin-sales', 'Plugin sales could not be refreshed. Check the credentials and network connection.')
            );
        }

        return $this->redirect('plugin-sales');
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
