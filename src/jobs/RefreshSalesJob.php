<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\pluginsales\jobs;

use Craft;
use craft\queue\BaseJob;
use putyourlightson\pluginsales\PluginSales;

class RefreshSalesJob extends BaseJob
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     *
     * @throws Exception
     * @throws Throwable
     */
    public function execute($queue)
    {
        PluginSales::$plugin->sales->refresh();
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function defaultDescription(): string
    {
        return Craft::t('plugin-sales', 'Refreshing plugin sales');
    }
}
