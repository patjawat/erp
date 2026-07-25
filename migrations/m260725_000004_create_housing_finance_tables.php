<?php

declare(strict_types=1);

use yii\db\Migration;

final class m260725_000004_create_housing_finance_tables extends Migration
{
    public function safeUp(): void
    {
        $options = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';

        $this->createTable('{{%housing_charge_type}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(100)->notNull()->unique(),
            'code' => $this->string(50)->notNull()->unique(),
            'name' => $this->string(150)->notNull(),
            'description' => $this->text(),
            'status' => $this->string(30)->notNull()->defaultValue('active'),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ], $options);

        $this->createTable('{{%housing_billing_period}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(100)->notNull()->unique(),
            'period_code' => $this->string(20)->notNull()->unique(),
            'name' => $this->string(150)->notNull(),
            'start_date' => $this->date()->notNull(),
            'end_date' => $this->date()->notNull(),
            'due_date' => $this->date()->notNull(),
            'status' => $this->string(30)->notNull()->defaultValue('open'),
            'note' => $this->text(),
            'closed_at' => $this->dateTime(),
            'closed_by' => $this->integer(),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ], $options);

        $this->createTable('{{%housing_invoice}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(100)->notNull()->unique(),
            'invoice_no' => $this->string(50)->notNull()->unique(),
            'billing_period_id' => $this->integer()->notNull(),
            'occupancy_id' => $this->integer()->notNull(),
            'payer_emp_id' => $this->integer()->notNull(),
            'issued_at' => $this->dateTime(),
            'due_date' => $this->date()->notNull(),
            'subtotal' => $this->decimal(12, 2)->notNull()->defaultValue(0),
            'discount' => $this->decimal(12, 2)->notNull()->defaultValue(0),
            'total_amount' => $this->decimal(12, 2)->notNull()->defaultValue(0),
            'paid_amount' => $this->decimal(12, 2)->notNull()->defaultValue(0),
            'balance_amount' => $this->decimal(12, 2)->notNull()->defaultValue(0),
            'status' => $this->string(30)->notNull()->defaultValue('draft'),
            'note' => $this->text(),
            'confirmed_at' => $this->dateTime(),
            'confirmed_by' => $this->integer(),
            'cancelled_at' => $this->dateTime(),
            'cancelled_by' => $this->integer(),
            'cancel_reason' => $this->text(),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ], $options);
        $this->createIndex('ux_housing_invoice_period_occupancy', '{{%housing_invoice}}', ['billing_period_id', 'occupancy_id'], true);
        $this->createIndex('ix_housing_invoice_payer_status', '{{%housing_invoice}}', ['payer_emp_id', 'status']);
        $this->addForeignKey('fk_housing_invoice_period', '{{%housing_invoice}}', 'billing_period_id', '{{%housing_billing_period}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_housing_invoice_occupancy', '{{%housing_invoice}}', 'occupancy_id', '{{%housing_occupancy}}', 'id', 'RESTRICT', 'CASCADE');

        $this->createTable('{{%housing_invoice_item}}', [
            'id' => $this->primaryKey(),
            'invoice_id' => $this->integer()->notNull(),
            'charge_type_id' => $this->integer(),
            'description' => $this->string(255)->notNull(),
            'quantity' => $this->decimal(12, 2)->notNull()->defaultValue(1),
            'unit_name' => $this->string(50),
            'unit_price' => $this->decimal(12, 2)->notNull()->defaultValue(0),
            'amount' => $this->decimal(12, 2)->notNull()->defaultValue(0),
            'calculation_note' => $this->string(255),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ], $options);
        $this->createIndex('ix_housing_invoice_item_invoice', '{{%housing_invoice_item}}', 'invoice_id');
        $this->addForeignKey('fk_housing_invoice_item_invoice', '{{%housing_invoice_item}}', 'invoice_id', '{{%housing_invoice}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_housing_invoice_item_type', '{{%housing_invoice_item}}', 'charge_type_id', '{{%housing_charge_type}}', 'id', 'SET NULL', 'CASCADE');

        $this->createTable('{{%housing_payment}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(100)->notNull()->unique(),
            'payment_no' => $this->string(50)->notNull()->unique(),
            'payer_emp_id' => $this->integer()->notNull(),
            'paid_at' => $this->dateTime()->notNull(),
            'amount' => $this->decimal(12, 2)->notNull(),
            'payment_method' => $this->string(30)->notNull(),
            'reference_no' => $this->string(150),
            'note' => $this->text(),
            'status' => $this->string(30)->notNull()->defaultValue('confirmed'),
            'received_by' => $this->integer(),
            'cancelled_at' => $this->dateTime(),
            'cancelled_by' => $this->integer(),
            'cancel_reason' => $this->text(),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ], $options);
        $this->createIndex('ix_housing_payment_payer', '{{%housing_payment}}', ['payer_emp_id', 'paid_at']);

        $this->createTable('{{%housing_payment_allocation}}', [
            'id' => $this->primaryKey(),
            'payment_id' => $this->integer()->notNull(),
            'invoice_id' => $this->integer()->notNull(),
            'amount' => $this->decimal(12, 2)->notNull(),
            'created_at' => $this->dateTime(),
            'created_by' => $this->integer(),
        ], $options);
        $this->createIndex('ux_housing_payment_invoice', '{{%housing_payment_allocation}}', ['payment_id', 'invoice_id'], true);
        $this->addForeignKey('fk_housing_allocation_payment', '{{%housing_payment_allocation}}', 'payment_id', '{{%housing_payment}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_housing_allocation_invoice', '{{%housing_payment_allocation}}', 'invoice_id', '{{%housing_invoice}}', 'id', 'RESTRICT', 'CASCADE');

        $this->createTable('{{%housing_receipt}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(100)->notNull()->unique(),
            'receipt_no' => $this->string(50)->notNull()->unique(),
            'payment_id' => $this->integer()->notNull()->unique(),
            'issued_at' => $this->dateTime()->notNull(),
            'amount' => $this->decimal(12, 2)->notNull(),
            'verification_code' => $this->string(100)->notNull()->unique(),
            'status' => $this->string(30)->notNull()->defaultValue('issued'),
            'issued_by' => $this->integer(),
            'cancelled_at' => $this->dateTime(),
            'cancelled_by' => $this->integer(),
            'cancel_reason' => $this->text(),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ], $options);
        $this->addForeignKey('fk_housing_receipt_payment', '{{%housing_receipt}}', 'payment_id', '{{%housing_payment}}', 'id', 'RESTRICT', 'CASCADE');

        $now = date('Y-m-d H:i:s');
        $this->batchInsert('{{%housing_charge_type}}', ['ref', 'code', 'name', 'sort_order', 'created_at', 'updated_at'], [
            ['housing-charge-water', 'WATER', 'ค่าน้ำ', 10, $now, $now],
            ['housing-charge-electric', 'ELECTRIC', 'ค่าไฟ', 20, $now, $now],
            ['housing-charge-common', 'COMMON', 'ค่าส่วนกลาง', 30, $now, $now],
            ['housing-charge-appliance', 'APPLIANCE', 'ค่าเครื่องใช้ไฟฟ้า', 40, $now, $now],
            ['housing-charge-cleaning', 'CLEANING', 'ค่าทำความสะอาด', 50, $now, $now],
            ['housing-charge-aircon', 'AIRCON', 'ค่าล้างแอร์', 60, $now, $now],
            ['housing-charge-other', 'OTHER', 'ค่าใช้จ่ายอื่น', 99, $now, $now],
        ]);
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%housing_receipt}}');
        $this->dropTable('{{%housing_payment_allocation}}');
        $this->dropTable('{{%housing_payment}}');
        $this->dropTable('{{%housing_invoice_item}}');
        $this->dropTable('{{%housing_invoice}}');
        $this->dropTable('{{%housing_billing_period}}');
        $this->dropTable('{{%housing_charge_type}}');
    }
}
