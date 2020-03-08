<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\pluginsales\records;

use craft\db\ActiveRecord;

/**
 * @property int $id
 * @property int $fetched
 */
class FetchRecord extends ActiveRecord
{
     /**
     * @inheritdoc
     *
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%pluginsales_fetches}}';
    }
}
