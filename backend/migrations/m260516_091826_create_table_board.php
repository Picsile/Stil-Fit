<?php

use yii\db\Migration;

class m260516_091826_create_table_board extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%board}}',
            [
                'id' => $this->primaryKey()->unsigned(),
                'user_id' => $this->integer()->unsigned()->notNull(),
                'title' => $this->string()->notNull(),
                'visible_id' => $this->integer()->unsigned()->notNull(),
                'status_id' => $this->integer()->unsigned()->notNull(),
                'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            ],
            $tableOptions
        );

        $this->createIndex('visible_id', '{{%board}}', ['visible_id']);

        $this->addForeignKey(
            'board_ibfk_1',
            '{{%board}}',
            ['user_id'],
            '{{%user}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'board_ibfk_2',
            '{{%board}}',
            ['status_id'],
            '{{%status}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'board_ibfk_3',
            '{{%board}}',
            ['visible_id'],
            '{{%visible}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropTable('{{%board}}');
    }
}
