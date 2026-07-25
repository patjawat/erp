<?php

declare(strict_types=1);

use yii\db\Migration;

final class m260725_000003_create_housing_workflow_tables extends Migration
{
    public function safeUp(): void
    {
        $options = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';

        $this->createTable('{{%housing_request}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(100)->notNull()->unique(),
            'request_no' => $this->string(50)->notNull()->unique(),
            'request_type' => $this->string(30)->notNull()->defaultValue('move_in'),
            'emp_id' => $this->integer()->notNull(),
            'current_occupancy_id' => $this->integer(),
            'preferred_building_type' => $this->string(30),
            'requested_at' => $this->dateTime(),
            'reason' => $this->text(),
            'status' => $this->string(30)->notNull()->defaultValue('draft'),
            'staff_note' => $this->text(),
            'submitted_at' => $this->dateTime(),
            'completed_at' => $this->dateTime(),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ], $options);
        $this->createIndex('ix_housing_request_emp', '{{%housing_request}}', ['emp_id', 'status']);
        $this->createIndex('ix_housing_request_status', '{{%housing_request}}', ['status', 'requested_at']);

        $this->createTable('{{%housing_request_status_log}}', [
            'id' => $this->primaryKey(),
            'request_id' => $this->integer()->notNull(),
            'from_status' => $this->string(30),
            'to_status' => $this->string(30)->notNull(),
            'comment' => $this->text(),
            'acted_at' => $this->dateTime()->notNull(),
            'acted_by' => $this->integer(),
        ], $options);
        $this->createIndex('ix_housing_request_log_request', '{{%housing_request_status_log}}', ['request_id', 'acted_at']);
        $this->addForeignKey('fk_housing_request_log_request', '{{%housing_request_status_log}}', 'request_id', '{{%housing_request}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%housing_committee_decision}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(100)->notNull()->unique(),
            'request_id' => $this->integer()->notNull()->unique(),
            'decision' => $this->string(20)->notNull(),
            'decision_date' => $this->date()->notNull(),
            'meeting_reference' => $this->string(150),
            'note' => $this->text(),
            'recorded_by' => $this->integer(),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ], $options);
        $this->addForeignKey('fk_housing_decision_request', '{{%housing_committee_decision}}', 'request_id', '{{%housing_request}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%housing_occupancy}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(100)->notNull()->unique(),
            'request_id' => $this->integer(),
            'emp_id' => $this->integer()->notNull(),
            'payer_emp_id' => $this->integer()->notNull(),
            'unit_id' => $this->integer()->notNull(),
            'room_id' => $this->integer(),
            'occupancy_type' => $this->string(30)->notNull(),
            'allocated_at' => $this->dateTime(),
            'start_date' => $this->date(),
            'end_date' => $this->date(),
            'status' => $this->string(30)->notNull()->defaultValue('allocated'),
            'move_out_reason' => $this->text(),
            'note' => $this->text(),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ], $options);
        $this->createIndex('ix_housing_occupancy_emp', '{{%housing_occupancy}}', ['emp_id', 'status']);
        $this->createIndex('ix_housing_occupancy_unit', '{{%housing_occupancy}}', ['unit_id', 'room_id', 'status']);
        $this->addForeignKey('fk_housing_occupancy_request', '{{%housing_occupancy}}', 'request_id', '{{%housing_request}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_housing_occupancy_unit', '{{%housing_occupancy}}', 'unit_id', '{{%housing_unit}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_housing_occupancy_room', '{{%housing_occupancy}}', 'room_id', '{{%housing_room}}', 'id', 'RESTRICT', 'CASCADE');

        $this->addForeignKey('fk_housing_request_current_occupancy', '{{%housing_request}}', 'current_occupancy_id', '{{%housing_occupancy}}', 'id', 'SET NULL', 'CASCADE');

        $this->createTable('{{%housing_resident}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(100)->notNull()->unique(),
            'occupancy_id' => $this->integer()->notNull(),
            'resident_type' => $this->string(30)->notNull(),
            'relationship' => $this->string(50),
            'prefix' => $this->string(30),
            'first_name' => $this->string(150)->notNull(),
            'last_name' => $this->string(150)->notNull(),
            'citizen_id' => $this->string(20),
            'birth_date' => $this->date(),
            'phone' => $this->string(50),
            'start_date' => $this->date()->notNull(),
            'end_date' => $this->date(),
            'count_for_charge' => $this->boolean()->notNull()->defaultValue(true),
            'status' => $this->string(30)->notNull()->defaultValue('active'),
            'note' => $this->text(),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ], $options);
        $this->createIndex('ix_housing_resident_occupancy', '{{%housing_resident}}', ['occupancy_id', 'status']);
        $this->addForeignKey('fk_housing_resident_occupancy', '{{%housing_resident}}', 'occupancy_id', '{{%housing_occupancy}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%housing_guest_request}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(100)->notNull()->unique(),
            'request_no' => $this->string(50)->notNull()->unique(),
            'occupancy_id' => $this->integer()->notNull(),
            'requested_by_emp_id' => $this->integer()->notNull(),
            'guest_name' => $this->string(255)->notNull(),
            'citizen_id' => $this->string(20),
            'relationship' => $this->string(100),
            'phone' => $this->string(50),
            'reason' => $this->text()->notNull(),
            'start_date' => $this->date()->notNull(),
            'end_date' => $this->date()->notNull(),
            'status' => $this->string(30)->notNull()->defaultValue('pending'),
            'decision_note' => $this->text(),
            'decided_at' => $this->dateTime(),
            'decided_by' => $this->integer(),
            'checked_in_at' => $this->dateTime(),
            'checked_out_at' => $this->dateTime(),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ], $options);
        $this->createIndex('ix_housing_guest_occupancy', '{{%housing_guest_request}}', ['occupancy_id', 'status']);
        $this->createIndex('ix_housing_guest_period', '{{%housing_guest_request}}', ['start_date', 'end_date']);
        $this->addForeignKey('fk_housing_guest_occupancy', '{{%housing_guest_request}}', 'occupancy_id', '{{%housing_occupancy}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%housing_guest_request}}');
        $this->dropTable('{{%housing_resident}}');
        $this->dropForeignKey('fk_housing_request_current_occupancy', '{{%housing_request}}');
        $this->dropTable('{{%housing_occupancy}}');
        $this->dropTable('{{%housing_committee_decision}}');
        $this->dropTable('{{%housing_request_status_log}}');
        $this->dropTable('{{%housing_request}}');
    }
}
