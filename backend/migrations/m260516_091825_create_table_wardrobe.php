<?php

use yii\db\Migration;

class m260516_091825_create_table_wardrobe extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%wardrobe}}',
            [
                'user_id' => $this->integer()->unsigned()->notNull(),
                'post_id' => $this->integer()->unsigned()->notNull(),
                'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            ],
            $tableOptions
        );

        $this->addPrimaryKey('PRIMARYKEY', '{{%wardrobe}}', ['user_id', 'post_id']);

        $this->createIndex('post_id', '{{%wardrobe}}', ['post_id']);

        $this->addForeignKey(
            'wardrobe_ibfk_2',
            '{{%wardrobe}}',
            ['user_id'],
            '{{%user}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropTable('{{%wardrobe}}');
    }
}
