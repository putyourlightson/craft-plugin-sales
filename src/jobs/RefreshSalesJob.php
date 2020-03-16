<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\pluginsales\jobs;

use Craft;
use craft\queue\BaseJob;
use craft\queue\Queue;
use putyourlightson\pluginsales\PluginSales;

class RefreshSalesJob extends BaseJob
{
    /**
     * @var Queue
     */
    private $_queue;

    /**
     * @inheritdoc
     */
    public function execute($queue)
    {
        $this->_queue = $queue;

        PluginSales::$plugin->sales->refresh([$this, 'setProgressHandler']);
    }

    /**
     * Handles setting the progress.
     *
     * @param int $count
     * @param int $total
     */
    public function setProgressHandler(int $count, int $total)
    {
        $progress = $total > 0 ? ($count / $total) : 0;

        $this->setProgress($this->_queue, $progress);
    }

    /**
     * @inheritdoc
     */
    protected function defaultDescription(): string
    {
        return Craft::t('plugin-sales', 'Refreshing plugin sales');
    }
}
