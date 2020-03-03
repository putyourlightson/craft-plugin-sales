<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\pluginsales\records;

use craft\db\ActiveRecord;
use DateTime;

/**
 * @property int $id
 * @property int $saleId
 * @property int $pluginId
 * @property string $edition
 * @property bool $renewal
 * @property string $email
 * @property double $grossAmount
 * @property double $netAmount
 * @property DateTime $dateSold
 */
class SaleRecord extends ActiveRecord
{
     /**
     * @inheritdoc
     *
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%pluginsales_sales}}';
    }
}
