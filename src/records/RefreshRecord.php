<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\pluginsales\records;

use craft\db\ActiveRecord;

/**
 * @property int $id
 * @property int $refreshed
 * @property string $currency
 * @property float $exchangeRate
 */
class RefreshRecord extends ActiveRecord
{
     /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return '{{%pluginsales_refreshes}}';
    }
}
