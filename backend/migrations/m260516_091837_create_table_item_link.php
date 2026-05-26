<?php

use yii\db\Migration;

class m260516_091837_create_table_item_link extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%item_link}}',
            [
                'id' => $this->primaryKey()->unsigned(),
                'post_id' => $this->integer()->unsigned()->notNull(),
                'link' => $this->string()->notNull(),
            ],
            $tableOptions
        );

        $this->createIndex('item_link_ibfk_1', '{{%item_link}}', ['post_id']);
    }

    public function safeDown()
    {
        $this->dropTable('{{%item_link}}');
    }
}
