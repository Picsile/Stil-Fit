<?php

use yii\db\Migration;

class m260516_091815_create_table_post_image extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%post_image}}',
            [
                'id' => $this->primaryKey()->unsigned(),
                'post_id' => $this->integer()->unsigned()->notNull(),
                'image_id' => $this->integer()->unsigned()->notNull(),
            ],
            $tableOptions
        );

        $this->createIndex('image_id', '{{%post_image}}', ['image_id']);
        $this->createIndex('post_id', '{{%post_image}}', ['post_id']);

        $this->addForeignKey(
            'post_image_ibfk_1',
            '{{%post_image}}',
            ['image_id'],
            '{{%image}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropTable('{{%post_image}}');
    }
}
