<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\pluginsales\variables;

use Craft;
use putyourlightson\pluginsales\models\SaleModel;
use putyourlightson\pluginsales\PluginSales;
use putyourlightson\pluginsales\services\ReportsService;
use yii\web\ForbiddenHttpException;

class PluginSalesVariable
{
    /**
     * Returns reports service
     *
     * @return ReportsService
     */
    public function getReports(): ReportsService
    {
        return PluginSales::$plugin->reports;
    }

    /**
     * Returns plugin sales.
     *
     * @param int|null $limit
     *
     * @return SaleModel[]
     */
    public function get($limit = null)
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
