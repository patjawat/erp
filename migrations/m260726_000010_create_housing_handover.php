<?php

declare(strict_types=1);

use yii\db\Migration;

final class m260726_000010_create_housing_handover extends Migration
{
    public function safeUp(): void
    {
        $options = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        $this->createTable('{{%housing_handover}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(100)->notNull()->unique(),
            'handover_no' => $this->string(50)->notNull()->unique(),
            'occupancy_id' => $this->integer()->notNull()->unique(),
            'handover_type' => $this->string(20)->notNull()->defaultValue('check_in'),
            'handover_date' => $this->date()->notNull(),
            'electric_meter_value' => $this->decimal(12, 2),
            'water_meter_value' => $this->decimal(12, 2),
            'asset_snapshot' => $this->text(),
            'condition_note' => $this->text(),
            'handed_over_by_emp_id' => $this->integer(),
            'handed_over_by_name' => $this->string(255)->notNull(),
            'received_by_emp_id' => $this->integer()->notNull(),
            'received_by_name' => $this->string(255)->notNull(),
            'handed_over_signed_at' => $this->dateTime(),
            'received_signed_at' => $this->dateTime(),
            'status' => $this->string(20)->notNull()->defaultValue('draft'),
            'confirmed_at' => $this->dateTime(),
            'confirmed_by' => $this->integer(),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ], $options);
        $this->createIndex('ix_housing_handover_status', '{{%housing_handover}}', ['status', 'handover_date']);
        $this->addForeignKey(
            'fk_housing_handover_occupancy',
            '{{%housing_handover}}',
            'occupancy_id',
            '{{%housing_occupancy}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%housing_handover}}');
    }
}
