<?php

namespace putyourlightson\pluginsales\migrations;

use Craft;
use craft\db\Migration;
use putyourlightson\pluginsales\models\SettingsModel;

class m220523_120000_update_colourpalette_setting extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $projectConfig = Craft::$app->getProjectConfig();

        // Don't make the same config changes twice
        $schemaVersion = $projectConfig->get('plugins.plugin-sales.schemaVersion', true);

        if (version_compare($schemaVersion, '2.1.0', '<')) {
            $colourPalette = $projectConfig->get('plugins.plugin-sales.settings.colourPalette', true) ?? [];

            // Compare the first 2 colours only
            if (trim($colourPalette[0], '#') == '008FFB' && trim($colourPalette[1], '#') == '00E396') {
                $defaultColourPalette = (new SettingsModel())->colourPalette;
                $projectConfig->set('plugins.plugin-sales.settings.colourPalette', $defaultColourPalette);
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo self::class . " cannot be reverted.\n";

        return false;
    }
}
