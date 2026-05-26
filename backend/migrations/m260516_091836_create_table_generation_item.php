<?php

use yii\db\Migration;

class m260516_091836_create_table_generation_item extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%generation_item}}',
            [
                'generation_id' => $this->integer()->unsigned()->notNull(),
                'post_id' => $this->integer()->unsigned()->notNull(),
            ],
            $tableOptions
        );

        $this->addPrimaryKey('PRIMARYKEY', '{{%generation_item}}', ['generation_id', 'post_id']);

        $this->createIndex('post_id', '{{%generation_item}}', ['post_id']);

        $this->addForeignKey(
            'generation_item_ibfk_1',
            '{{%generation_item}}',
            ['generation_id'],
            '{{%generation}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropTable('{{%generation_item}}');
    }
}
