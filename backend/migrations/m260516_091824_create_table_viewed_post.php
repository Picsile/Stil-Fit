<?php

use yii\db\Migration;

class m260516_091824_create_table_viewed_post extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%viewed_post}}',
            [
                'user_id' => $this->integer()->unsigned()->notNull(),
                'post_id' => $this->integer()->unsigned()->notNull(),
                'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            ],
            $tableOptions
        );

        $this->addPrimaryKey('PRIMARYKEY', '{{%viewed_post}}', ['user_id', 'post_id']);

        $this->createIndex('post_id', '{{%viewed_post}}', ['post_id']);

        $this->addForeignKey(
            'viewed_post_ibfk_2',
            '{{%viewed_post}}',
            ['user_id'],
            '{{%user}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropTable('{{%viewed_post}}');
    }
}
