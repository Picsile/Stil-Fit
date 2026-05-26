<?php

use yii\db\Migration;

class m260516_091812_create_table_generation_resolution extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%generation_resolution}}',
            [
                'id' => $this->primaryKey()->unsigned(),
                'value' => $this->string()->notNull(),
            ],
            $tableOptions
        );
    }

    public function safeDown()
    {
        $this->dropTable('{{%generation_resolution}}');
    }
}
