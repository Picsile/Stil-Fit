<?php

use yii\db\Migration;

class m260516_091829_create_table_generation_task extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%generation_task}}',
            [
                'id' => $this->primaryKey()->unsigned(),
                'generation_id' => $this->integer()->unsigned()->notNull(),
                'task_id' => $this->string()->notNull(),
                'status' => $this->string()->defaultValue('pending'),
                'image_id' => $this->integer()->unsigned(),
                'error' => $this->text(),
            ],
            $tableOptions
        );

        $this->addForeignKey(
            'fk-generation_tasks-generation_id',
            '{{%generation_task}}',
            ['generation_id'],
            '{{%generation}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-generation_tasks-image_id',
            '{{%generation_task}}',
            ['image_id'],
            '{{%image}}',
            ['id'],
            'SET NULL',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropTable('{{%generation_task}}');
    }
}
