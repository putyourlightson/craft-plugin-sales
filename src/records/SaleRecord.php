<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\pluginsales\records;

use craft\db\ActiveRecord;
use DateTime;
use yii\db\ActiveQuery;

/**
 * @property int $id
 * @property int $saleId
 * @property int $pluginId
 * @property string $edition
 * @property bool $renewal
 * @property string $email
 * @property float $grossAmount
 * @property float $netAmount
 * @property DateTime $dateSold
 * @property PluginRecord $plugin
 */
class SaleRecord extends ActiveRecord
{
     /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return '{{%pluginsales_sales}}';
    }

    /**
     * Returns the plugin model.
     */
    public function getPlugin(): ActiveQuery
    {
        return $this->hasOne(PluginRecord::class, ['id' => 'pluginId']);
    }
}
