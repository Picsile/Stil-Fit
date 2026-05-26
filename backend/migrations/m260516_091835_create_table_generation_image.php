<?php

use yii\db\Migration;

class m260516_091835_create_table_generation_image extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%generation_image}}',
            [
                'id' => $this->primaryKey()->unsigned(),
                'generation_task_id' => $this->integer()->unsigned()->notNull(),
                'image_id' => $this->integer()->unsigned()->notNull(),
            ],
            $tableOptions
        );

        $this->createIndex('generation_id', '{{%generation_image}}', ['generation_task_id']);
        $this->createIndex('image_id', '{{%generation_image}}', ['image_id']);

        $this->addForeignKey(
            'generation_image_ibfk_2',
            '{{%generation_image}}',
            ['image_id'],
            '{{%image}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'generation_image_ibfk_3',
            '{{%generation_image}}',
            ['generation_task_id'],
            '{{%generation_task}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropTable('{{%generation_image}}');
    }
}
