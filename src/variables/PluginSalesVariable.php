<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\pluginsales\variables;

use putyourlightson\pluginsales\PluginSales;
use putyourlightson\pluginsales\services\ReportsService;

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
     * Returns last refresh date.
     *
     * @return string|null|false
     */
    public function getLastRefreshDate()
    {
        return PluginSales::$plugin->sales->getLastRefreshDate();
    }

    /**
     * Returns whether the plugin has a valid license.
     *
     * @return bool
     */
    public function hasValidLicense()
    {
        return PluginSales::$plugin->hasValidLicense();
    }
}
