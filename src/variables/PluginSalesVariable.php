<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\pluginsales\variables;

use craft\helpers\DateTimeHelper;
use DateTime;
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
     * Returns refresh date.
     *
     * @return DateTime|bool
     */
    public function getRefreshDate()
    {
        $lastRefresh = PluginSales::$plugin->sales->getLastRefresh();

        return $lastRefresh['dateCreated'] ? DateTimeHelper::toDateTime($lastRefresh['dateCreated']) : false;
    }

    /**
     * Returns currency
     *
     * @return string
     */
    public function getCurrency(): string
    {
        $lastRefresh = PluginSales::$plugin->sales->getLastRefresh();

        return $lastRefresh['currency'] ?? 'USD';
    }

    /**
     * Returns exchange rate
     *
     * @return float
     */
    public function getExchangeRate(): float
    {
        $lastRefresh = PluginSales::$plugin->sales->getLastRefresh();

        return $lastRefresh['exchangeRate'] ?? 1;
    }

    /**
     * Returns colour palette
     *
     * @return array
     */
    public function getColourPalette(): array
    {
        return PluginSales::$plugin->settings->colourPalette;
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
