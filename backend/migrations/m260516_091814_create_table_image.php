<?php

use yii\db\Migration;

class m260516_091814_create_table_image extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%image}}',
            [
                'id' => $this->primaryKey()->unsigned(),
                'path' => $this->string()->notNull(),
                'path_preview' => $this->string()->notNull(),
                'width' => $this->integer()->notNull(),
                'height' => $this->integer()->notNull(),
                'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            ],
            $tableOptions
        );
    }

    public function safeDown()
    {
        $this->dropTable('{{%image}}');
    }
}
