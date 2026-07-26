<?php

declare(strict_types=1);

use yii\db\Migration;

final class m260726_000003_create_housing_maintenance_table extends Migration
{
    public function safeUp(): void
    {
        $options = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        $this->createTable('{{%housing_maintenance}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(100)->notNull()->unique(),
            'ticket_no' => $this->string(50)->notNull()->unique(),
            'building_id' => $this->integer()->notNull(),
            'location_note' => $this->string(255),
            'reported_at' => $this->dateTime()->notNull(),
            'reporter_name' => $this->string(255)->notNull(),
            'title' => $this->string(255)->notNull(),
            'description' => $this->text()->notNull(),
            'priority' => $this->string(20)->notNull()->defaultValue('normal'),
            'assigned_employee_id' => $this->integer(),
            'status' => $this->string(30)->notNull()->defaultValue('new'),
            'resolution' => $this->text(),
            'expense_amount' => $this->decimal(12, 2)->notNull()->defaultValue(0),
            'repaired_at' => $this->dateTime(),
            'closed_at' => $this->dateTime(),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ], $options);
        $this->createIndex('ix_housing_maintenance_building', '{{%housing_maintenance}}', ['building_id', 'reported_at']);
        $this->createIndex('ix_housing_maintenance_status', '{{%housing_maintenance}}', ['status', 'priority']);
        $this->addForeignKey('fk_housing_maintenance_building', '{{%housing_maintenance}}', 'building_id', '{{%housing_building}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_housing_maintenance_assignee', '{{%housing_maintenance}}', 'assigned_employee_id', '{{%employees}}', 'id', 'SET NULL', 'CASCADE');
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%housing_maintenance}}');
    }
}
