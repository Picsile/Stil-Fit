<?php

use yii\db\Migration;

class m260516_091830_create_table_post extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%post}}',
            [
                'id' => $this->primaryKey()->unsigned(),
                'user_id' => $this->integer()->unsigned()->notNull(),
                'type_id' => $this->integer()->unsigned()->notNull(),
                'main_image_id' => $this->integer()->unsigned(),
                'category_id' => $this->integer()->unsigned(),
                'title' => $this->string()->notNull(),
                'description' => $this->text()->notNull(),
                'likes_count' => $this->integer()->notNull()->defaultValue('0'),
                'visible_id' => $this->integer()->unsigned()->notNull(),
                'status_id' => $this->integer()->unsigned()->notNull(),
                'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            ],
            $tableOptions
        );

        $this->createIndex('category_id', '{{%post}}', ['category_id']);
        $this->createIndex('post_visible_id', '{{%post}}', ['visible_id']);
        $this->createIndex('type_id', '{{%post}}', ['type_id']);
        $this->createIndex('user_id', '{{%post}}', ['user_id']);
        $this->createIndex('visibility_id', '{{%post}}', ['status_id']);

        $this->addForeignKey(
            'post_ibfk_1',
            '{{%post}}',
            ['type_id'],
            '{{%type}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'post_ibfk_2',
            '{{%post}}',
            ['user_id'],
            '{{%user}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'post_ibfk_3',
            '{{%post}}',
            ['status_id'],
            '{{%status}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'post_ibfk_4',
            '{{%post}}',
            ['visible_id'],
            '{{%visible}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'post_ibfk_6',
            '{{%post}}',
            ['main_image_id'],
            '{{%image}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropTable('{{%post}}');
    }
}
