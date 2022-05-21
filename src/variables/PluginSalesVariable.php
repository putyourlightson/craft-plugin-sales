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

        if ($lastRefresh === null) {
            return false;
        }

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
     * Returns formatted amount
     *
     * @param float $amount
     * @return string
     */
    public function getFormattedAmount(float $amount): string
    {
        $formatter =  new NumberFormatter(Craft::$app->locale, NumberFormatter::CURRENCY);
        $currency = $this->getCurrency();

        return $formatter->formatCurrency($amount, $currency);
    }

    /**
     * Returns exchange rate
     *
     * @return float
     */
    public function getExchangeRate(): float
    {
        return PluginSales::$plugin->sales->getExchangeRate();
    }

    /**
     * Returns colour palette
     *
     * @return array
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
     *
     * @return bool
     */
    public function hasValidLicense(): bool
    {
        return PluginSales::$plugin->hasValidLicense();
    }
}
