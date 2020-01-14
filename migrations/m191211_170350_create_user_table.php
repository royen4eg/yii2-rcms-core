<?php

use rcms\core\migrations\Migration;
use rcms\core\models\User;

/**
 * Handles the creation of table `{{%user}}`.
 */
class m191211_170350_create_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%user}}', [
            'user_id' => $this->primaryKey(),
            'username' => $this->string('32')->notNull(),
            'password_hash' => $this->string('64')->notNull(),
            'password_reset_token' => $this->string('128'),
            'auth_key' => $this->string('64')->notNull(),
            'email' => $this->string('128')->notNull(),
            'status' => $this->boolean()->notNull()->defaultValue(1),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex('{{%user_unique_idx}}', '{{%user}}', [
            'user_id'
        ], true);

        $this->createIndex('{{%user_username_unique_idx}}', '{{%user}}', [
            'username'
        ], true);

        $this->createIndex('{{%user_password_reset_token_unique_idx}}', '{{%user}}', [
            'password_reset_token'
        ], true);

        $this->createIndex('{{%user_auth_key_unique_idx}}', '{{%user}}', [
            'auth_key'
        ], true);

        $this->createIndex('{{%user_email_unique_idx}}', '{{%user}}', [
            'email'
        ], true);

        $this->createIndex('{{%user_status_idx}}', '{{%user}}', [
            'status'
        ]);


        $this->insert('{{%user}}', [
            'user_id' => 100,
            'username' => 'admin',
            'password_hash' => Yii::$app->security->generatePasswordHash('admin'),
            'auth_key' => Yii::$app->security->generateRandomString(),
            'email' => 'admin@localhost.com',
            'status' => User::STATUS_ACTIVE,
            'created_at' => time(),
            'updated_at' => time(),
        ]);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%user}}');
    }
}
