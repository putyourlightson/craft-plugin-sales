<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\pluginsales;

use Craft;
use craft\base\Plugin;
use craft\events\PluginEvent;
use craft\helpers\UrlHelper;
use craft\log\MonologTarget;
use craft\services\Plugins;
use craft\services\ProjectConfig;
use craft\web\twig\variables\CraftVariable;
use Monolog\Formatter\LineFormatter;
use Psr\Log\LogLevel;
use putyourlightson\pluginsales\jobs\RefreshSalesJob;
use putyourlightson\pluginsales\models\SettingsModel;
use putyourlightson\pluginsales\services\PluginsService;
use putyourlightson\pluginsales\services\ReportsService;
use putyourlightson\pluginsales\services\SalesService;
use putyourlightson\pluginsales\variables\PluginSalesVariable;
use putyourlightson\sprig\Sprig;
use yii\base\Event;
use yii\log\Logger;

/**
 * @property-read PluginsService $plugins
 * @property-read ReportsService $reports
 * @property-read SalesService $sales
 * @property-read SettingsModel $settings
 */
class PluginSales extends Plugin
{
    /**
     * @var PluginSales
     */
    public static PluginSales $plugin;

    /**
     * @inerhitdoc
     */
    public static function config(): array
    {
        return [
            'components' => [
                'plugins' => ['class' => PluginsService::class],
                'reports' => ['class' => ReportsService::class],
                'sales' => ['class' => SalesService::class],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public bool $hasCpSettings = true;

    /**
     * @inheritdoc
     */
    public bool $hasCpSection = true;
    /**
     * @inheritdoc
     */
    public string $schemaVersion = '2.4.0';

    /**
     * @inheritdoc
     */
    public string $minVersionRequired = '1.2.0';

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();
        self::$plugin = $this;

        Sprig::getInstance()->init();

        $this->_registerVariables();
        $this->_registerLogTarget();
        $this->_registerRefreshAfterSettingsSaved();

        // Register control panel events
        if (Craft::$app->getRequest()->getIsCpRequest()) {
            $this->_registerRedirectAfterInstall();
        }
    }

    /**
     * Returns whether the plugin has a valid license.
     */
    public function hasValidLicense(): bool
    {
        $projectConfig = Craft::$app->getProjectConfig();
        $configKey = ProjectConfig::PATH_PLUGINS . '.' . $this->handle;
        $data = $projectConfig->get($configKey) ?? $projectConfig->get($configKey, true);

        return !empty($data['licenseKey']);
    }

    /**
     * Logs a message.
     */
    public function log(string $message, array $params = [], int $type = Logger::LEVEL_INFO): void
    {
        $message = Craft::t('plugin-sales', $message, $params);

        Craft::getLogger()->log($message, $type, 'plugin-sales');
    }

    /**
     * @inheritdoc
     */
    protected function createSettingsModel(): SettingsModel
    {
        return new SettingsModel();
    }

    /**
     * @inheritdoc
     */
    protected function settingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate('plugin-sales/settings', [
            'settings' => $this->getSettings(),
        ]);
    }

    /**
     * Registers the variables.
     */
    private function _registerVariables(): void
    {
        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('pluginSales', PluginSalesVariable::class);
            }
        );
    }

    /**
     * Registers a custom log target, keeping the format as simple as possible.
     *
     * @see LineFormatter::SIMPLE_FORMAT
     */
    private function _registerLogTarget(): void
    {
        Craft::getLogger()->dispatcher->targets[] = new MonologTarget([
            'name' => 'plugin-sales',
            'categories' => ['plugin-sales'],
            'level' => LogLevel::INFO,
            'logContext' => false,
            'allowLineBreaks' => false,
            'formatter' => new LineFormatter(
                format: "[%datetime%] %message%\n",
                dateFormat: 'Y-m-d H:i:s',
            ),
        ]);
    }

    /**
     * Registers redirect after install.
     */
    private function _registerRedirectAfterInstall(): void
    {
        Event::on(Plugins::class, Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                    Craft::$app->getResponse()->redirect(
                        UrlHelper::cpUrl('settings/plugins/plugin-sales')
                    )->send();
                }
            }
        );
    }

    /**
     * Registers a refresh after settings are saved.
     */
    private function _registerRefreshAfterSettingsSaved(): void
    {
        Event::on(Plugins::class, Plugins::EVENT_AFTER_SAVE_PLUGIN_SETTINGS,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                    PluginSales::$plugin->sales->delete();

                    // Create queue job
                    Craft::$app->getQueue()->push(new RefreshSalesJob());
                }
            }
        );
    }
}
