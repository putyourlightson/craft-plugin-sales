<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\pluginsales\jobs;

use Craft;
use craft\helpers\App;
use craft\queue\BaseJob;
use putyourlightson\pluginsales\PluginSales;

class RefreshSalesJob extends BaseJob
{
    /**
     * @inheritdoc
     */
    public function execute($queue)
    {
        App::maxPowerCaptain();

        // Set progress so it at least looks like something is happening
        $this->setProgress($queue, 0.5);

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
