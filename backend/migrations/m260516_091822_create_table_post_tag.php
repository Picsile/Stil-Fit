<?php

use yii\db\Migration;

class m260516_091822_create_table_post_tag extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%post_tag}}',
            [
                'post_id' => $this->integer()->unsigned()->notNull(),
                'tag_id' => $this->integer()->unsigned()->notNull(),
            ],
            $tableOptions
        );

        $this->addPrimaryKey('PRIMARYKEY', '{{%post_tag}}', ['post_id', 'tag_id']);

        $this->addForeignKey(
            'post_tag_ibfk_2',
            '{{%post_tag}}',
            ['tag_id'],
            '{{%tag}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropTable('{{%post_tag}}');
    }
}
