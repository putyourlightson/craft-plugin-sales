<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\pluginsales\console\controllers;

use Craft;
use craft\helpers\Console;
use putyourlightson\pluginsales\PluginSales;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\BaseConsole;

/**
 * Allows you to perform actions on sales.
 *
 * @author    PutYourLightsOn
 * @package   Campaign
 * @since     1.3.0
 */
class SalesController extends Controller
{
    /**
     * Refreshes all plugin sales.
     */
    public function actionRefresh(): int
    {
        $this->stdout(Craft::t('plugin-sales', 'Refreshing sales...') . PHP_EOL, BaseConsole::FG_YELLOW);

        $refreshed = PluginSales::$plugin->sales->refresh([$this, 'setProgressHandler']);

        if ($refreshed !== false) {
            $this->stdout(
                Craft::t('plugin-sales', '{count} plugin sale(s) refreshed.', ['count' => $refreshed]) . PHP_EOL,
                BaseConsole::FG_GREEN
            );
        } else {
            $this->stderr(
                Craft::t('plugin-sales', 'Plugin sales could not be refreshed. Check the credentials and network connection.') . PHP_EOL,
                BaseConsole::FG_RED
            );
        }

        return ExitCode::OK;
    }

    /**
     * Deletes all plugin sales.
     */
    public function actionDelete(): int
    {
        PluginSales::$plugin->sales->delete();

        $this->stdout(Craft::t('plugin-sales', 'Plugin sales successfully deleted.') . PHP_EOL, BaseConsole::FG_GREEN);

        return ExitCode::OK;
    }

    /**
     * Handles setting the progress.
     */
    public function setProgressHandler(int $count, int $total): void
    {
        if ($count === 0) {
            Console::startProgress(0, $total, '', 0.8);
        }

        Console::updateProgress($count, $total);

        if ($count >= $total) {
            Console::endProgress();
        }
    }
}
