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

class SalesController extends Controller
{
    /**
     * @return int
     */
    public function actionRefresh(): int
    {
        $this->stdout(Craft::t('plugin-sales', 'Refreshing sales...').PHP_EOL, Console::FG_YELLOW);

        $refreshed = PluginSales::$plugin->sales->refresh();

        if ($refreshed !== false) {
            $this->stdout(
                Craft::t('plugin-sales', '{count} plugin sales successfully refreshed.', ['count' => $refreshed]).PHP_EOL,
                Console::FG_GREEN
            );
        }
        else {
            $this->stderr(
                Craft::t('plugin-sales', 'Plugin sales could not be refreshed. Check the credentials and network connection.').PHP_EOL,
                Console::FG_RED
            );
        }

        return ExitCode::OK;
    }

    /**
     * @return int
     */
    public function actionDelete(): int
    {
        PluginSales::$plugin->sales->delete();

        $this->stdout(Craft::t('plugin-sales', 'Plugin sales successfully deleted.').PHP_EOL, Console::FG_GREEN);

        return ExitCode::OK;
    }
}
