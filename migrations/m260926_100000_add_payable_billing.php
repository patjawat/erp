<?php

use yii\db\Migration;

/**
 * ทะเบียนรับวางบิล — บริษัทมาวางบิล 1 ครั้ง (1 แถว) ครอบคลุมได้หลายบิลของบริษัทนั้น
 * บันทึก: วันที่รับวาง, บริษัท, ผู้วาง (ตัวแทนบริษัท), ผู้รับวาง (เจ้าหน้าที่) → พิมพ์ใบรับวางบิลได้
 * บิลที่วางแล้วผูก finance_payable.billing_id และคำนวณวันครบกำหนดใหม่จากวันวางบิล
 */
class m260926_100000_add_payable_billing extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%finance_payable_billing}}', [
            'id' => $this->primaryKey(),
            'billing_no' => $this->string(30)->notNull()->comment('เลขที่ใบรับวางบิล'),
            'billing_date' => $this->date()->notNull()->comment('วันที่รับวางบิล'),
            'vendor_id' => $this->integer()->null(),
            'vendor_name' => $this->string(255)->notNull(),
            'vendor_ref' => $this->string(60)->null()->comment('เลขที่ใบวางบิลของบริษัท'),
            'deliverer_name' => $this->string(150)->null()->comment('ผู้วางบิล (ตัวแทนบริษัท)'),
            'receiver_id' => $this->integer()->null()->comment('ผู้รับวางบิล (user id)'),
            'receiver_name' => $this->string(150)->null()->comment('ผู้รับวางบิล'),
            'bill_count' => $this->integer()->notNull()->defaultValue(0),
            'total_amount' => $this->decimal(15, 2)->notNull()->defaultValue(0),
            'note' => $this->string(255)->null(),
            'cancelled_at' => $this->dateTime()->null(),
            'cancelled_by' => $this->integer()->null(),
            'cancel_reason' => $this->string(255)->null(),
            'created_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
        ]);
        $this->createIndex('uq-finance_payable_billing-no', '{{%finance_payable_billing}}', 'billing_no', true);
        $this->createIndex('idx-finance_payable_billing-date', '{{%finance_payable_billing}}', 'billing_date');
        $this->createIndex('idx-finance_payable_billing-vendor', '{{%finance_payable_billing}}', 'vendor_id');

        $this->addColumn('{{%finance_payable}}', 'billing_id', $this->integer()->null()->comment('ใบรับวางบิล (null = ยังไม่วางบิล)'));
        $this->createIndex('idx-finance_payable-billing', '{{%finance_payable}}', 'billing_id');
    }

    public function safeDown()
    {
        $this->dropIndex('idx-finance_payable-billing', '{{%finance_payable}}');
        $this->dropColumn('{{%finance_payable}}', 'billing_id');
        $this->dropTable('{{%finance_payable_billing}}');
    }
}
