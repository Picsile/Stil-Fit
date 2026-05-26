<?php

use yii\db\Migration;

class m260516_091827_create_table_generation extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%generation}}',
            [
                'id' => $this->primaryKey()->unsigned(),
                'user_id' => $this->integer()->unsigned()->notNull(),
                'visible_id' => $this->integer()->unsigned()->notNull(),
                'prompt' => $this->text()->notNull(),
                'resolution_id' => $this->integer()->unsigned()->notNull(),
                'ratio_id' => $this->integer()->unsigned()->notNull(),
                'quantity' => $this->integer()->notNull(),
                'status_id' => $this->integer()->unsigned()->notNull(),
                'error' => $this->text(),
                'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            ],
            $tableOptions
        );

        $this->createIndex('status_generation_id', '{{%generation}}', ['status_id']);
        $this->createIndex('user_id', '{{%generation}}', ['user_id']);
        $this->createIndex('visible_id', '{{%generation}}', ['visible_id']);

        $this->addForeignKey(
            'generation_ibfk_1',
            '{{%generation}}',
            ['user_id'],
            '{{%user}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'generation_ibfk_2',
            '{{%generation}}',
            ['ratio_id'],
            '{{%generation_ratio}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'generation_ibfk_3',
            '{{%generation}}',
            ['resolution_id'],
            '{{%generation_resolution}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'generation_ibfk_4',
            '{{%generation}}',
            ['status_id'],
            '{{%generation_status}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'generation_ibfk_6',
            '{{%generation}}',
            ['visible_id'],
            '{{%visible}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropTable('{{%generation}}');
    }
}
