<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\pluginsales\migrations;

use craft\db\Migration;
use putyourlightson\pluginsales\records\PluginRecord;
use putyourlightson\pluginsales\records\RefreshRecord;
use putyourlightson\pluginsales\records\SaleRecord;

class Install extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        if (!$this->db->tableExists(RefreshRecord::tableName())) {
            $this->createTable(RefreshRecord::tableName(), [
                'id' => $this->primaryKey(),
                'refreshed' => $this->integer()->notNull(),
                'currency' => $this->string(3),
                'exchangeRate' => $this->float(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
            ]);
        }

        if (!$this->db->tableExists(PluginRecord::tableName())) {
            $this->createTable(PluginRecord::tableName(), [
                'id' => $this->primaryKey(),
                'name' => $this->string(),
                'hasMultipleEditions' => $this->boolean(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
            ]);
        }

        if (!$this->db->tableExists(SaleRecord::tableName())) {
            $this->createTable(SaleRecord::tableName(), [
                'id' => $this->primaryKey(),
                'saleId' => $this->integer()->notNull(),
                'pluginId' => $this->integer()->notNull(),
                'edition' => $this->string(),
                'renewal' => $this->boolean(),
                'email' => $this->string(),
                'notice' => $this->string(),
                'grossAmount' => $this->float()->notNull(),
                'netAmount' => $this->float()->notNull(),
                'dateSold' => $this->dateTime()->notNull(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
            ]);

            $this->addForeignKey(null, SaleRecord::tableName(), 'pluginId', PluginRecord::tableName(), 'id', 'CASCADE');
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        $this->dropTableIfExists(SaleRecord::tableName());
        $this->dropTableIfExists(PluginRecord::tableName());
        $this->dropTableIfExists(RefreshRecord::tableName());

        return true;
    }
}
