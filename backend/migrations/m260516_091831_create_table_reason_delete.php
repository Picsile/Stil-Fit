<?php

use yii\db\Migration;

class m260516_091831_create_table_reason_delete extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%reason_delete}}',
            [
                'post_id' => $this->primaryKey()->unsigned(),
                'reason' => $this->text()->notNull(),
                'user_id' => $this->integer()->unsigned()->notNull(),
                'delete_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            ],
            $tableOptions
        );

        $this->addForeignKey(
            'reason_delete_ibfk_2',
            '{{%reason_delete}}',
            ['user_id'],
            '{{%user}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropTable('{{%reason_delete}}');
    }
}
