<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\pluginsales;

use Craft;
use craft\base\Plugin;
use craft\web\twig\variables\CraftVariable;
use putyourlightson\pluginsales\models\SettingsModel;
use putyourlightson\pluginsales\services\SalesService;
use putyourlightson\pluginsales\variables\PluginSalesVariable;
use yii\base\Event;

/**
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
