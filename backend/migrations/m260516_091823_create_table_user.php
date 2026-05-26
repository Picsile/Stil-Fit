<?php

use yii\db\Migration;

class m260516_091823_create_table_user extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%user}}',
            [
                'id' => $this->primaryKey()->unsigned(),
                'login' => $this->string()->notNull(),
                'password' => $this->string()->notNull(),
                'email' => $this->string()->notNull(),
                'avatar_path' => $this->string(),
                'background_path' => $this->string(),
                'role_id' => $this->integer()->unsigned()->notNull()->defaultValue('1'),
                'auth_key' => $this->string()->notNull(),
                'quantity_fitcoins' => $this->integer()->notNull()->defaultValue('200'),
                'terms_accepted' => $this->boolean()->notNull(),
                'privacy_accepted' => $this->boolean()->notNull(),
                'email_verified' => $this->tinyInteger()->notNull()->defaultValue('0'),
                'verification_token' => $this->string(),
                'verification_token_expires' => $this->dateTime(),
            ],
            $tableOptions
        );

        $this->createIndex('email', '{{%user}}', ['email'], true);
        $this->createIndex('login_2', '{{%user}}', ['email'], true);
        $this->createIndex('role_id', '{{%user}}', ['role_id']);

        $this->addForeignKey(
            'user_ibfk_1',
            '{{%user}}',
            ['role_id'],
            '{{%user_role}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropTable('{{%user}}');
    }
}
