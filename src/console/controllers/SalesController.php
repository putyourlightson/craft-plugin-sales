<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\pluginsales\console\controllers;

use Craft;
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

        $refreshed = PluginSales::$plugin->sales->refresh();

        if ($refreshed !== false) {
            $this->stdout(
                Craft::t('plugin-sales', '{count} plugin sale(s) refreshed.', ['count' => $refreshed]) . PHP_EOL,
                BaseConsole::FG_GREEN
            );
        }
        else {
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
}
