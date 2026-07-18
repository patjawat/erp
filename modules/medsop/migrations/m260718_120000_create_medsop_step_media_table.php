<?php

use yii\db\Migration;

class m260718_120000_create_medsop_step_media_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%medsop_document_step_media}}', [
            'id' => $this->bigPrimaryKey(),
            'step_id' => $this->bigInteger()->notNull(),
            'media_type' => $this->string(10)->notNull(),
            'file_name' => $this->string(255)->notNull(),
            'file_path' => $this->string(500)->notNull(),
            'mime_type' => $this->string(100)->notNull(),
            'file_size' => $this->bigInteger()->notNull(),
            'sort_order' => $this->integer()->notNull()->defaultValue(1),
            'created_by' => $this->integer(),
            'created_at' => $this->dateTime()->notNull(),
        ], 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB');

        $this->createIndex('ix_medsop_step_media_order', '{{%medsop_document_step_media}}', ['step_id', 'sort_order']);
        $this->addForeignKey('fk_medsop_step_media_step', '{{%medsop_document_step_media}}', 'step_id', '{{%medsop_document_step}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_medsop_step_media_step', '{{%medsop_document_step_media}}');
        $this->dropTable('{{%medsop_document_step_media}}');
    }
}
