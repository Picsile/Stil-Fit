<?php

use yii\db\Migration;

class m260516_091839_create_table_outfit_item extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%outfit_item}}',
            [
                'outfit_id' => $this->integer()->unsigned()->notNull(),
                'item_id' => $this->integer()->unsigned()->notNull(),
            ],
            $tableOptions
        );

        $this->addPrimaryKey('PRIMARYKEY', '{{%outfit_item}}', ['outfit_id', 'item_id']);

        $this->createIndex('outfit_item_ibfk_1', '{{%outfit_item}}', ['item_id']);
    }

    public function safeDown()
    {
        $this->dropTable('{{%outfit_item}}');
    }
}
