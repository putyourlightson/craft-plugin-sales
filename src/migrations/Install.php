<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\pluginsales\migrations;

use craft\db\Migration;
use putyourlightson\pluginsales\records\PluginRecord;
use putyourlightson\pluginsales\records\SaleRecord;

class Install extends Migration
{
    /**
     * @return boolean
     */
    public function safeUp(): bool
    {
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
                'edition' => $this->string(),
                'grossAmount' => $this->double()->notNull(),
                'netAmount' => $this->double()->notNull(),
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
     * @return boolean
     */
    public function safeDown(): bool
    {
        $this->dropTableIfExists(SaleRecord::tableName());
        $this->dropTableIfExists(PluginRecord::tableName());

        return true;
    }
}
