<?php

namespace putyourlightson\pluginsales\migrations;

use craft\db\Migration;
use putyourlightson\pluginsales\records\SaleRecord;

class m231204_120000_rename_email_column extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        if ($this->db->columnExists(SaleRecord::tableName(), 'email')) {
            $this->renameColumn(
                SaleRecord::tableName(),
                'email',
                'customer',
            );
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
