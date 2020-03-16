<?php

namespace putyourlightson\pluginsales\migrations;

use craft\db\Migration;
use putyourlightson\pluginsales\records\RefreshRecord;

class m200316_120000_add_currency_exchangerate_columns extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $table = RefreshRecord::tableName();

        if (!$this->db->columnExists($table, 'currency')) {
            $this->addColumn($table, 'currency', $this->string(3)->after('refreshed'));
        }

        if (!$this->db->columnExists($table, 'exchangeRate')) {
            $this->addColumn($table, 'exchangeRate', $this->float()->after('currency'));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo self::class." cannot be reverted.\n";

        return false;
    }
}
