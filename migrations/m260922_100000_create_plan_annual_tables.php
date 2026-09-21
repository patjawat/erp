<?php

use yii\db\Migration;

/**
 * ตารางสำหรับบล็อกสภาพคล่อง + แนบ 1/แนบ 2 ของหน้าแผนประจำปี (plan/annual)
 * อ้างอิงแบบฟอร์มแผนรับ-จ่ายเงินบำรุง สป.สธ.
 *  - plan_annual_ledger      : เงินคงเหลือยกมา + เงินคงเหลือแยกประเภท (ต่อปีงบ)
 *  - plan_annual_attachment  : แนบ1 กองทุนรอจัดสรร (reserve) + แนบ2 ภาระผูกพัน (commitment)
 */
class m260922_100000_create_plan_annual_tables extends Migration
{
    public function safeUp()
    {
        $tbl = $this->db->driverName === 'mysql' ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB' : null;

        $this->createTable('{{%plan_annual_ledger}}', [
            'id' => $this->primaryKey(),
            'fiscal_year' => $this->integer()->notNull()->comment('ปีงบประมาณ (พ.ศ.)'),
            'carry_forward' => $this->decimal(15, 2)->notNull()->defaultValue(0)->comment('เงินคงเหลือสะสมยกมา (กรอกเฉพาะปีเริ่ม)'),
            'cash' => $this->decimal(15, 2)->notNull()->defaultValue(0)->comment('เงินสด'),
            'deposit_treasury' => $this->decimal(15, 2)->notNull()->defaultValue(0)->comment('เงินฝากคลัง'),
            'deposit_fixed' => $this->decimal(15, 2)->notNull()->defaultValue(0)->comment('เงินฝากธนาคาร ประเภทประจำ'),
            'deposit_saving' => $this->decimal(15, 2)->notNull()->defaultValue(0)->comment('เงินฝากธนาคาร ประเภทออมทรัพย์'),
            'deposit_current' => $this->decimal(15, 2)->notNull()->defaultValue(0)->comment('เงินฝากธนาคาร ประเภทกระแสรายวัน'),
            'note' => $this->text()->null(),
            'created_at' => $this->integer()->null(),
            'updated_at' => $this->integer()->null(),
        ], $tbl);
        $this->createIndex('uq-plan_annual_ledger-year', '{{%plan_annual_ledger}}', 'fiscal_year', true);

        $this->createTable('{{%plan_annual_attachment}}', [
            'id' => $this->primaryKey(),
            'fiscal_year' => $this->integer()->notNull()->comment('ปีงบประมาณ (พ.ศ.)'),
            'kind' => $this->string(20)->notNull()->comment('reserve=กองทุนรอจัดสรร(แนบ1), commitment=ภาระผูกพัน(แนบ2)'),
            'name' => $this->string(255)->notNull()->comment('รายการ'),
            'amount' => $this->decimal(15, 2)->notNull()->defaultValue(0),
            'note' => $this->string(255)->null(),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'created_at' => $this->integer()->null(),
        ], $tbl);
        $this->createIndex('idx-plan_annual_attachment-year-kind', '{{%plan_annual_attachment}}', ['fiscal_year', 'kind']);
    }

    public function safeDown()
    {
        $this->dropTable('{{%plan_annual_attachment}}');
        $this->dropTable('{{%plan_annual_ledger}}');
    }
}
