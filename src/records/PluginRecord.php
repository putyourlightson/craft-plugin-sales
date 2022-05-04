<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\pluginsales\records;

use craft\db\ActiveRecord;

/**
 * @property int $id
 * @property string $name
 * @property bool $hasMultipleEditions
 */
class PluginRecord extends ActiveRecord
{
    /**
    * @inheritdoc
    */
    public static function tableName(): string
    {
        return '{{%pluginsales_plugins}}';
    }
}
