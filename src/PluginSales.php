<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\pluginsales;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\web\twig\variables\CraftVariable;
use putyourlightson\pluginsales\models\SettingsModel;
use putyourlightson\pluginsales\services\FetchService;
use putyourlightson\pluginsales\services\PluginsService;
use putyourlightson\pluginsales\services\ReportsService;
use putyourlightson\pluginsales\services\SalesService;
use putyourlightson\pluginsales\variables\PluginSalesVariable;
use yii\base\Event;

/**
 * @property FetchService $fetch
 * @property PluginsService $plugins
 * @property ReportsService $reports
 * @property SalesService $sales
 * @property SettingsModel $settings
 * @property mixed $settingsResponse
 *
 * @method SettingsModel getSettings()
 */
class PluginSales extends Plugin
{
    /**
     * @var PluginSales
     */
    public static $plugin;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        self::$plugin = $this;

        $this->_registerComponents();
        $this->_registerVariables();
    }

    /**
     * Returns whether the plugin has a valid license.
     *
     * @return bool
     */
    public function hasValidLicense()
    {
        $projectConfig = Craft::$app->getProjectConfig();
        $configKey = Plugins::CONFIG_PLUGINS_KEY .'.'.$this->handle;
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
    protected function settingsHtml()
    {
        return Craft::$app->getView()->renderTemplate('plugin-sales/settings', [
            'settings' => $this->getSettings()
        ]);
    }

    /**
     * Registers the components
     */
    private function _registerComponents()
    {
        $this->setComponents([
            'fetch' => FetchService::class,
            'plugins' => PluginsService::class,
            'reports' => ReportsService::class,
            'sales' => SalesService::class,
        ]);
    }

    /**
     * Registers the variables
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
}
