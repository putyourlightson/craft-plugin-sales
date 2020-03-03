<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\pluginsales\variables;

use putyourlightson\pluginsales\models\SaleModel;
use putyourlightson\pluginsales\PluginSales;
use yii\web\ForbiddenHttpException;

class PluginSalesVariable
{
    /**
     * Returns plugin sales
     *
     * @return SaleModel[]
     */
    public function get()
    {
        return PluginSales::$plugin->sales->get();
    }

    /**
     * Refreshes plugin sales
     *
     * @throws ForbiddenHttpException
     */
    public function refresh()
    {
        PluginSales::$plugin->sales->refresh();
    }
}
