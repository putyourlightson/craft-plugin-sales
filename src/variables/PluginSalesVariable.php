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
