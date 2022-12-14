<?php

namespace putyourlightson\pluginsales\migrations;

use craft\db\Migration;
use putyourlightson\pluginsales\records\SaleRecord;

class m221213_120000_add_first_column extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        if (!$this->db->columnExists(SaleRecord::tableName(), 'first')) {
            $this->addColumn(
                SaleRecord::tableName(),
                'first',
                $this->boolean()->defaultValue(false)->after('renewal'),
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
