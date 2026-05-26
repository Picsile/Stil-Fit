<?php

use yii\db\Migration;

class m260516_091810_create_table_category extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%category}}',
            [
                'id' => $this->primaryKey()->unsigned(),
                'parent_id' => $this->integer()->unsigned(),
                'title' => $this->string()->notNull(),
            ],
            $tableOptions
        );

        $this->createIndex('parent_id', '{{%category}}', ['parent_id']);
    }

    public function safeDown()
    {
        $this->dropTable('{{%category}}');
    }
}
