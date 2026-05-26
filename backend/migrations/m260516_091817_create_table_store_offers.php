<?php

use yii\db\Migration;

class m260516_091817_create_table_store_offers extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%store_offers}}',
            [
                'id' => $this->primaryKey()->unsigned(),
                'quantity_fitcoin' => $this->integer()->notNull(),
                'price' => $this->integer()->notNull(),
            ],
            $tableOptions
        );
    }

    public function safeDown()
    {
        $this->dropTable('{{%store_offers}}');
    }
}
