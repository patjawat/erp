<?php

declare(strict_types=1);

use yii\db\Migration;

final class m260726_000011_create_housing_checkout extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%housing_checkout}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(100)->notNull()->unique(),
            'checkout_no' => $this->string(50)->notNull()->unique(),
            'occupancy_id' => $this->integer()->notNull()->unique(),
            'requested_date' => $this->date()->notNull(),
            'checkout_date' => $this->date(),
            'move_out_reason' => $this->text()->notNull(),
            'electric_meter_value' => $this->decimal(12, 2),
            'water_meter_value' => $this->decimal(12, 2),
            'asset_snapshot' => $this->text(),
            'condition_note' => $this->text(),
            'resident_emp_id' => $this->integer()->notNull(),
            'resident_name' => $this->string(255)->notNull(),
            'inspected_by_emp_id' => $this->integer(),
            'inspected_by_name' => $this->string(255),
            'resident_signed_at' => $this->dateTime(),
            'inspector_signed_at' => $this->dateTime(),
            'status' => $this->string(30)->notNull()->defaultValue('requested'),
            'completed_at' => $this->dateTime(),
            'completed_by' => $this->integer(),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ], 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB');
        $this->createIndex('ix_housing_checkout_status', '{{%housing_checkout}}', ['status', 'requested_date']);
        $this->addForeignKey('fk_housing_checkout_occupancy', '{{%housing_checkout}}', 'occupancy_id', '{{%housing_occupancy}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%housing_checkout}}');
    }
}
