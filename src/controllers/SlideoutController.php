<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\pluginsales\controllers;

use Craft;
use craft\web\Controller;
use putyourlightson\pluginsales\PluginSales;
use yii\web\Response;

class SlideoutController extends Controller
{
    /**
     * Renders a slideout.
     */
    public function actionRender(): Response
    {
        $email = $this->request->getRequiredParam('email');
        $sales = PluginSales::$plugin->reports->getSalesData(email: $email);

        return $this->asCpScreen()
            ->contentTemplate('plugin-sales/slideout', [
                'email' => $email,
                'sales' => $sales,
            ]);
    }
}
