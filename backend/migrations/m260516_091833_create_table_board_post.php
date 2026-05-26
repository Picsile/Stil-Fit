<?php

use yii\db\Migration;

class m260516_091833_create_table_board_post extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%board_post}}',
            [
                'board_id' => $this->integer()->unsigned()->notNull(),
                'post_id' => $this->integer()->unsigned()->notNull(),
                'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            ],
            $tableOptions
        );

        $this->addPrimaryKey('PRIMARYKEY', '{{%board_post}}', ['board_id', 'post_id']);

        $this->createIndex('post_id', '{{%board_post}}', ['post_id']);

        $this->addForeignKey(
            'board_post_ibfk_2',
            '{{%board_post}}',
            ['board_id'],
            '{{%board}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropTable('{{%board_post}}');
    }
}
