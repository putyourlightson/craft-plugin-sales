<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\pluginsales\variables;

use Craft;
use craft\helpers\DateTimeHelper;
use DateTime;
use NumberFormatter;
use putyourlightson\pluginsales\PluginSales;
use putyourlightson\pluginsales\services\ReportsService;

class PluginSalesVariable
{
    /**
     * Returns reports service
     */
    public function getReports(): ReportsService
    {
        return PluginSales::$plugin->reports;
    }

    /**
     * Returns refresh date.
     */
    public function getRefreshDate(): DateTime|bool
    {
        $lastRefresh = PluginSales::$plugin->sales->getLastRefresh();

        return $lastRefresh['dateCreated'] ? DateTimeHelper::toDateTime($lastRefresh['dateCreated']) : false;
    }

    /**
     * Returns currency
     */
    public function getCurrency(): string
    {
        $lastRefresh = PluginSales::$plugin->sales->getLastRefresh();

        return $lastRefresh['currency'] ?? 'USD';
    }

    /**
     * Returns formatted amount
     */
    public function getFormattedAmount(float $amount): string
    {
        $formatter =  new NumberFormatter(Craft::$app->locale, NumberFormatter::CURRENCY);
        $currency = $this->getCurrency();

        return $formatter->formatCurrency($amount, $currency);
    }

    /**
     * Returns exchange rate
     */
    public function getExchangeRate(): float
    {
        return PluginSales::$plugin->sales->getExchangeRate();
    }

    /**
     * Returns colour palette
     */
    public function getColourPalette(): array
    {
        $colourPalette = PluginSales::$plugin->settings->colourPalette;

        foreach ($colourPalette as &$colour) {
            $colour = '#'.trim($colour, '#');
        }

        return $colourPalette;
    }

    /**
     * Returns whether the plugin has a valid license.
     */
    public function hasValidLicense(): bool
    {
        return PluginSales::$plugin->hasValidLicense();
    }
}
