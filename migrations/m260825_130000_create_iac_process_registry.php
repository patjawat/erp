<?php

use yii\db\Migration;

/** Phase 2: normalized annual process registry linked to Service Profile revisions. */
class m260825_130000_create_iac_process_registry extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%iac_service_process}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(64)->notNull(),
            'hospital_id' => $this->integer()->notNull(),
            'owner_type' => $this->string(30)->notNull(),
            'owner_id' => $this->integer()->notNull(),
            'active' => $this->boolean()->notNull()->defaultValue(true),
            'created_at' => $this->dateTime()->null(), 'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(), 'updated_by' => $this->integer()->null(),
        ]);
        $this->createIndex('ux-iac-process-ref', '{{%iac_service_process}}', 'ref', true);
        $this->createIndex('idx-iac-process-owner', '{{%iac_service_process}}', ['hospital_id', 'owner_type', 'owner_id', 'active']);
        $this->addForeignKey('fk-iac-process-hospital', '{{%iac_service_process}}', 'hospital_id', '{{%iac_hospital}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%iac_service_process_version}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(64)->notNull(),
            'process_id' => $this->integer()->notNull(),
            'service_profile_id' => $this->integer()->notNull(),
            'service_profile_section_id' => $this->integer()->notNull(),
            'source_item_ref' => $this->string(64)->notNull(),
            'fiscal_year' => $this->integer()->notNull(),
            'revision_no' => $this->integer()->notNull(),
            'sequence' => $this->integer()->notNull()->defaultValue(10),
            'name' => $this->string(500)->notNull(),
            'objective' => $this->text()->null(),
            'review_status' => $this->string(30)->notNull()->defaultValue('pending'),
            'review_note' => $this->text()->null(),
            'reviewed_at' => $this->dateTime()->null(),
            'reviewed_by' => $this->integer()->null(),
            'created_at' => $this->dateTime()->null(), 'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(), 'updated_by' => $this->integer()->null(),
        ]);
        $this->createIndex('ux-iac-process-version-ref', '{{%iac_service_process_version}}', 'ref', true);
        $this->createIndex('ux-iac-process-version-item', '{{%iac_service_process_version}}', ['service_profile_section_id', 'source_item_ref'], true);
        $this->createIndex('idx-iac-process-version-profile', '{{%iac_service_process_version}}', ['service_profile_id', 'sequence']);
        $this->createIndex('idx-iac-process-version-year', '{{%iac_service_process_version}}', ['process_id', 'fiscal_year', 'revision_no']);
        $this->addForeignKey('fk-iac-process-version-process', '{{%iac_service_process_version}}', 'process_id', '{{%iac_service_process}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-iac-process-version-profile', '{{%iac_service_process_version}}', 'service_profile_id', '{{%service_profile}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-iac-process-version-section', '{{%iac_service_process_version}}', 'service_profile_section_id', '{{%service_profile_section}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropTable('{{%iac_service_process_version}}');
        $this->dropTable('{{%iac_service_process}}');
    }
}
