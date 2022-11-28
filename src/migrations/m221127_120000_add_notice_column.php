<?php

namespace putyourlightson\pluginsales\migrations;

use craft\db\Migration;
use putyourlightson\pluginsales\records\SaleRecord;

class m221127_120000_add_notice_column extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        if (!$this->db->columnExists(SaleRecord::tableName(), 'notice')) {
            $this->addColumn(
                SaleRecord::tableName(),
                'notice',
                $this->string()->after('email'),
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
