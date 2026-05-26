<?php

use yii\db\Migration;

class m260516_091834_create_table_favorite extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%favorite}}',
            [
                'user_id' => $this->integer()->unsigned()->notNull(),
                'post_id' => $this->integer()->unsigned()->notNull(),
                'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            ],
            $tableOptions
        );

        $this->addPrimaryKey('PRIMARYKEY', '{{%favorite}}', ['user_id', 'post_id']);

        $this->createIndex('post_id', '{{%favorite}}', ['post_id']);

        $this->addForeignKey(
            'favorite_ibfk_2',
            '{{%favorite}}',
            ['user_id'],
            '{{%user}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropTable('{{%favorite}}');
    }
}
