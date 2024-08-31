<?php

/*
 * This file is part of the Dektrium project.
 *
 * (c) Dektrium project <http://github.com/dektrium/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use dektrium\user\migrations\Migration;

/**
 * @author Dmitry Erofeev <dmeroff@gmail.com
 */
class m140209_132017_init extends Migration
{
    public function up()
    {
        $this->createTable('{{%user}}', [
            'id' => $this->primaryKey(),
            'username' => $this->string(25)->notNull(),
            'email' => $this->string(255),
            'line_id' => $this->string(255),
            'password_hash' => $this->string(60)->notNull(),
            'auth_key' => $this->string(32)->notNull(),
            'confirmation_token' => $this->string(32)->null(),
            'password_reset_token' => $this->string(255)->null(),
            'confirmation_sent_at' => $this->integer()->null(),
            'confirmed_at' => $this->integer()->null(),
            'unconfirmed_email' => $this->string(255)->null(),
            'recovery_token' => $this->string(32)->null(),
            'recovery_sent_at' => $this->integer()->null(),
            'blocked_at' => $this->integer()->null(),
            'registered_from' => $this->integer()->null(),
            'logged_in_from' => $this->integer()->null(),
            'logged_in_at' => $this->integer()->null(),
            'status' => $this->integer()->null(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $this->tableOptions);

        $this->createIndex('{{%user_unique_username}}', '{{%user}}', 'username', true);
        $this->createIndex('{{%user_unique_email}}', '{{%user}}', 'email', true);
        $this->createIndex('{{%user_confirmation}}', '{{%user}}', 'id, confirmation_token', true);
        $this->createIndex('{{%user_recovery}}', '{{%user}}', 'id, recovery_token', true);

        $this->createTable('{{%profile}}', [
            'user_id' => $this->integer()->notNull()->append('PRIMARY KEY'),
            'name' => $this->string(255)->null(),
            'public_email' => $this->string(255)->null(),
            'gravatar_email' => $this->string(255)->null(),
            'gravatar_id' => $this->string(32)->null(),
            'location' => $this->string(255)->null(),
            'website' => $this->string(255)->null(),
            'bio' => $this->text()->null(),
        ], $this->tableOptions);

        $this->addForeignKey('{{%fk_user_profile}}', '{{%profile}}', 'user_id', '{{%user}}', 'id', $this->cascade, $this->restrict);
        
        try {
            \Yii::$app->runAction('migrate', ['migrationPath' => '@yii/rbac/migrations/']);
            //code...
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public function down()
    {
        if (Yii::$app->db->schema->getTableSchema('{{%profile}}')) {
            $this->dropTable('{{%profile}}');
        }
        if (Yii::$app->db->schema->getTableSchema('{{%user}}')) {
            $this->dropTable('{{%user}}');
        }
        if (Yii::$app->db->schema->getTableSchema('{{%auth_item_child}}')) {
            $this->dropTable('{{%auth_item_child}}');
        }

        if (Yii::$app->db->schema->getTableSchema('{{%auth_assignment}}')) {
            $this->dropTable('{{%auth_assignment}}');
        }

        if (Yii::$app->db->schema->getTableSchema('{{%auth_item}}')) {
            $this->dropTable('{{%auth_item}}');
        }

        if (Yii::$app->db->schema->getTableSchema('{{%auth_rule}}')) {
            $this->dropTable('{{%auth_rule}}');
        }

    }
}