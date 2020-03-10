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
    /**
     * @inheritdoc
     */
    public function execute($queue)
    {
        // Set progress so it at least looks like something is happening
        $this->setProgress($queue, 0.1);

        PluginSales::$plugin->sales->refresh();
    }

    /**
     * @inheritdoc
     */
    protected function defaultDescription(): string
    {
        return Craft::t('plugin-sales', 'Refreshing plugin sales');
    }
}
