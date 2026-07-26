<?php

declare(strict_types=1);

use yii\db\Migration;

final class m260726_000006_create_housing_monthly_charges extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn('{{%housing_charge_type}}', 'category', $this->string(30)->notNull()->defaultValue('other')->after('name'));
        $this->addColumn('{{%housing_charge_type}}', 'calculation_method', $this->string(30)->notNull()->defaultValue('manual')->after('category'));
        $this->addColumn('{{%housing_charge_type}}', 'unit_name', $this->string(50)->null()->after('calculation_method'));
        $this->update('{{%housing_charge_type}}', ['category' => 'utility', 'calculation_method' => 'meter', 'unit_name' => 'หน่วย'], ['code' => ['WATER', 'ELECTRIC']]);
        $this->update('{{%housing_charge_type}}', ['category' => 'equipment', 'calculation_method' => 'equipment', 'unit_name' => 'เครื่อง'], ['code' => 'APPLIANCE']);
        $this->update('{{%housing_charge_type}}', ['category' => 'common', 'calculation_method' => 'flat_unit', 'unit_name' => 'ห้อง'], ['code' => ['COMMON', 'CLEANING']]);

        $options = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        $this->createTable('{{%housing_monthly_charge}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(100)->notNull()->unique(),
            'billing_period_id' => $this->integer()->notNull(),
            'building_id' => $this->integer()->notNull(),
            'unit_id' => $this->integer()->notNull(),
            'charge_type_id' => $this->integer()->notNull(),
            'previous_value' => $this->decimal(12, 2)->notNull()->defaultValue(0),
            'current_value' => $this->decimal(12, 2)->notNull()->defaultValue(0),
            'quantity' => $this->decimal(12, 2)->notNull()->defaultValue(1),
            'unit_rate' => $this->decimal(12, 2)->notNull()->defaultValue(0),
            'calculated_amount' => $this->decimal(12, 2)->notNull()->defaultValue(0),
            'actual_amount' => $this->decimal(12, 2)->notNull()->defaultValue(0),
            'is_overridden' => $this->boolean()->notNull()->defaultValue(false),
            'override_reason' => $this->string(255),
            'status' => $this->string(20)->notNull()->defaultValue('draft'),
            'note' => $this->text(),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ], $options);
        $this->createIndex('ux_housing_monthly_charge', '{{%housing_monthly_charge}}', ['billing_period_id', 'unit_id', 'charge_type_id'], true);
        $this->createIndex('ix_housing_monthly_charge_building', '{{%housing_monthly_charge}}', ['building_id', 'billing_period_id']);
        $this->addForeignKey('fk_housing_monthly_charge_period', '{{%housing_monthly_charge}}', 'billing_period_id', '{{%housing_billing_period}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_housing_monthly_charge_building', '{{%housing_monthly_charge}}', 'building_id', '{{%housing_building}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_housing_monthly_charge_unit', '{{%housing_monthly_charge}}', 'unit_id', '{{%housing_unit}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_housing_monthly_charge_type', '{{%housing_monthly_charge}}', 'charge_type_id', '{{%housing_charge_type}}', 'id', 'RESTRICT', 'CASCADE');
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%housing_monthly_charge}}');
        $this->dropColumn('{{%housing_charge_type}}', 'unit_name');
        $this->dropColumn('{{%housing_charge_type}}', 'calculation_method');
        $this->dropColumn('{{%housing_charge_type}}', 'category');
    }
}
