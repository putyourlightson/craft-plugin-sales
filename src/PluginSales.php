<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\pluginsales;

use Craft;
use craft\base\Plugin;
use craft\events\PluginEvent;
use craft\helpers\UrlHelper;
use craft\services\Plugins;
use craft\services\ProjectConfig;
use craft\web\twig\variables\CraftVariable;
use putyourlightson\pluginsales\jobs\RefreshSalesJob;
use putyourlightson\pluginsales\models\SettingsModel;
use putyourlightson\pluginsales\services\PluginsService;
use putyourlightson\pluginsales\services\ReportsService;
use putyourlightson\pluginsales\services\SalesService;
use putyourlightson\pluginsales\variables\PluginSalesVariable;
use yii\base\Event;

/**
 * @property PluginsService $plugins
 * @property ReportsService $reports
 * @property SalesService $sales
 *
 * @property SettingsModel $settings
 * @property mixed $settingsResponse
 */
class PluginSales extends Plugin
{
    /**
     * @var PluginSales
     */
    public static PluginSales $plugin;

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
    public string $schemaVersion = '1.2.0';

    /**
     * @inheritdoc
     */
    public string $minVersionRequired = '1.2.0';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        self::$plugin = $this;

        $this->_registerComponents();
        $this->_registerVariables();
        $this->_registerRedirectAfterInstall();
        $this->_registerRefreshAfterSettingsSaved();
    }

    /**
     * Returns whether the plugin has a valid license.
     */
    public function hasValidLicense(): bool
    {
        $projectConfig = Craft::$app->getProjectConfig();
        $configKey = ProjectConfig::PATH_PLUGINS.'.'.$this->handle;
        $data = $projectConfig->get($configKey) ?? $projectConfig->get($configKey, true);

        return !empty($data['licenseKey']);
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
            'settings' => $this->getSettings()
        ]);
    }

    /**
     * Registers the components.
     */
    private function _registerComponents()
    {
        $this->setComponents([
            'plugins' => PluginsService::class,
            'reports' => ReportsService::class,
            'sales' => SalesService::class,
        ]);
    }

    /**
     * Registers the variables.
     */
    private function _registerVariables()
    {
        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT,
            function(Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('pluginSales', PluginSalesVariable::class);
            }
        );
    }

    /**
     * Registers redirect after install.
     */
    private function _registerRedirectAfterInstall()
    {
        Event::on(Plugins::class, Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function(PluginEvent $event) {
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
    private function _registerRefreshAfterSettingsSaved()
    {
        Event::on(Plugins::class, Plugins::EVENT_AFTER_SAVE_PLUGIN_SETTINGS,
            function(PluginEvent $event) {
                if ($event->plugin === $this) {
                    PluginSales::$plugin->sales->delete();

                    // Create queue job
                    Craft::$app->getQueue()->push(new RefreshSalesJob());
                }
            }
        );
    }
}
