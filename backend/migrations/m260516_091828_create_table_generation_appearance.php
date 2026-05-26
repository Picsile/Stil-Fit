<?php

use yii\db\Migration;

class m260516_091828_create_table_generation_appearance extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%generation_appearance}}',
            [
                'id' => $this->primaryKey(),
                'generation_id' => $this->integer()->unsigned()->notNull(),
                'image_id' => $this->integer()->unsigned()->notNull(),
            ],
            $tableOptions
        );

        $this->createIndex('generation_id', '{{%generation_appearance}}', ['generation_id']);
        $this->createIndex('image_id', '{{%generation_appearance}}', ['image_id']);

        $this->addForeignKey(
            'generation_appearance_ibfk_1',
            '{{%generation_appearance}}',
            ['generation_id'],
            '{{%generation}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'generation_appearance_ibfk_2',
            '{{%generation_appearance}}',
            ['image_id'],
            '{{%image}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropTable('{{%generation_appearance}}');
    }
}
