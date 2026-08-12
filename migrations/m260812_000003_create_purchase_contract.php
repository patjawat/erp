<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * งานบริหารสัญญา — เฟส 1 (ทะเบียนสัญญา + งวดงาน + ค่าปรับ)
 *
 *   purchase_contract            หัวสัญญา 1 ฉบับ
 *   purchase_contract_milestone  งวดงาน N งวด — ใช้คิดค่าปรับรายงวดได้
 *   purchase_wht_rate            ตารางตั้งค่าอัตราภาษีหัก ณ ที่จ่าย
 *
 * เจตนาของโครงสร้าง
 *
 * 1) order_id ผูกใบสั่งซื้อได้แต่ไม่บังคับ (UNIQUE + null ได้)
 *    ใบสั่งซื้อเดิมเก็บ วันลงนาม/กำหนดส่ง/วันตรวจรับ/ค่าปรับ ไว้ใน orders.data_json อยู่แล้ว
 *    แต่เป็น JSON ที่ index ไม่ได้ และ fine เก็บเป็นข้อความ ("ไม่มี") ไม่ใช่อัตรา
 *    สัญญาจึงเก็บสำเนาของตัวเองเป็นคอลัมน์จริง — ค่าที่ใช้คำนวณค่าปรับต้องนิ่ง
 *    ไม่เปลี่ยนตามที่ใครไปแก้ใบสั่งซื้อทีหลัง ส่วนความไม่ตรงกันให้หน้า view เตือนแทน
 *    ไม่ใส่ FK ไป orders เพราะ orders ใช้ soft delete และตารางนี้มีข้อมูลจริงเดินอยู่
 *    (แบบเดียวกับที่เฟส TOR เพิ่ม orders.tor_id ไว้เป็น index เปล่า)
 *
 * 2) fine_rate เก็บเป็น decimal ไม่ใช่ข้อความ เพราะต้องคำนวณ
 *    fine_base เลือกได้ว่าคิดจากวงเงินทั้งสัญญา หรือจากวงเงินงวดที่ส่งล่าช้า
 *    (สัญญาแบ่งงวดต้องคิดจากมูลค่างวด ไม่ใช่ทั้งสัญญา)
 *
 * 3) อัตราภาษีหัก ณ ที่จ่ายอยู่ในตารางตั้งค่า ไม่ hardcode
 *    ค่าที่ seed ไว้ยังไม่ผ่านการยืนยันจากงานการเงิน — ดู migration ตัวถัดไป
 */
final class m260812_000003_create_purchase_contract extends Migration
{
    public function safeUp(): void
    {
        $options = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB'
            : null;

        // ── หัวสัญญา ─────────────────────────────────────────────────────────
        $this->createTable('{{%purchase_contract}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(255)->null(),
            'doc_no' => $this->string(50)->null()->comment('เลขที่สัญญาในระบบ'),
            'contract_no' => $this->string(100)->null()->comment('เลขที่สัญญาตามที่ออกจริง (ถ้าต่างจาก doc_no)'),
            'thai_year' => $this->integer()->notNull()->comment('ปีงบประมาณ (พ.ศ.)'),
            'title' => $this->string(255)->notNull()->comment('ชื่อสัญญา/รายการ'),

            // ── การเชื่อมโยง ──
            'order_id' => $this->integer()->null()->comment('ใบสั่งซื้อที่ผูก -> orders.id (name=order) ปล่อยว่างได้'),
            'tor_id' => $this->integer()->null()->comment('TOR ที่ใช้ -> purchase_tor.id'),

            // ── คู่สัญญา ──
            'contract_type' => $this->string(20)->notNull()->defaultValue('buy')
                ->comment('buy=ซื้อขาย, hire=จ้าง, lease=เช่า, agreement=ข้อตกลง'),
            'vendor_id' => $this->string(255)->null()->comment('-> categorise name=vendor code'),
            'vendor_name' => $this->string(255)->null()->comment('ชื่อคู่สัญญา snapshot กันทะเบียนผู้ขายถูกแก้ทีหลัง'),
            'party_type' => $this->string(20)->notNull()->defaultValue('juristic')
                ->comment('juristic=นิติบุคคล, personal=บุคคลธรรมดา — ใช้เลือกเกณฑ์ภาษีหัก ณ ที่จ่าย'),
            'egp_no' => $this->string(50)->null()->comment('เลขที่โครงการ e-GP'),

            // ── วงเงินและภาษี ──
            'budget' => $this->decimal(15, 2)->notNull()->defaultValue(0)->comment('วงเงินตามสัญญา'),
            'vat_included' => $this->tinyInteger(1)->notNull()->defaultValue(1)
                ->comment('1 = วงเงินรวม VAT แล้ว (ฐานคำนวณภาษีหัก ณ ที่จ่ายต้องถอด VAT ออกก่อน)'),
            'wht_rate' => $this->decimal(5, 2)->null()->comment('อัตราภาษีหัก ณ ที่จ่าย (%) ที่ใช้จริงกับสัญญานี้'),
            'wht_base' => $this->decimal(15, 2)->null()->comment('ฐานคำนวณภาษี'),
            'wht_amount' => $this->decimal(15, 2)->notNull()->defaultValue(0)->comment('ภาษีหัก ณ ที่จ่าย'),

            // ── กำหนดเวลา ──
            'sign_date' => $this->date()->null()->comment('วันลงนามในสัญญา'),
            'start_date' => $this->date()->null()->comment('วันเริ่มนับระยะเวลา'),
            'end_date' => $this->date()->null()->comment('วันครบกำหนดส่งมอบ — ใช้เป็นวันตั้งต้นคิดค่าปรับ'),
            'delivery_date' => $this->date()->null()->comment('วันที่ส่งมอบจริง'),
            'receive_date' => $this->date()->null()->comment('วันที่ตรวจรับ'),
            'warranty_end' => $this->date()->null()->comment('วันสิ้นสุดการรับประกัน'),

            // ── ค่าปรับ ──
            'fine_rate' => $this->decimal(6, 4)->notNull()->defaultValue(0.01)
                ->comment('อัตราค่าปรับ %/วัน เก็บเป็นตัวเลขเพื่อคำนวณ (0.01 = ร้อยละ 0.01 ต่อวัน)'),
            'fine_base' => $this->string(20)->notNull()->defaultValue('contract')
                ->comment('contract=คิดจากวงเงินทั้งสัญญา, milestone=รวมค่าปรับรายงวด'),
            'fine_days' => $this->integer()->notNull()->defaultValue(0)->comment('จำนวนวันที่ล่าช้า'),
            'fine_amount' => $this->decimal(15, 2)->notNull()->defaultValue(0)->comment('ค่าปรับที่คำนวณได้'),
            'fine_capped' => $this->tinyInteger(1)->notNull()->defaultValue(0)
                ->comment('1 = ค่าปรับชนเพดานวงเงินสัญญาแล้ว'),

            // ── เนื้อความ ──
            'extra_term' => $this->text()->null()
                ->comment('เงื่อนไขเพิ่มเติมที่ไหลลงร่างสัญญา (HTML) — ต้องผ่าน HtmlPurifier ก่อนบันทึก'),
            'note' => $this->text()->null()->comment('หมายเหตุภายใน ไม่พิมพ์ลงเอกสาร'),

            'status' => $this->string(20)->notNull()->defaultValue('draft')
                ->comment('draft=ร่าง, active=กำลังดำเนินการ, delivered=ส่งมอบแล้วรอตรวจรับ,'
                    . ' received=ตรวจรับแล้ว, overdue=ค้างส่ง, cancelled=ยกเลิก'),

            'department_id' => $this->integer()->null()->comment('หน่วยงานเจ้าของงาน (tree.id)'),
            'emp_id' => $this->integer()->null()->comment('ผู้บันทึก (employees.id)'),

            'data_json' => $this->json()->null(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
            'deleted_at' => $this->dateTime()->null(),
            'deleted_by' => $this->integer()->null(),
        ], $options);

        $this->createIndex('idx-purchase_contract-year_status', '{{%purchase_contract}}', ['thai_year', 'status']);
        $this->createIndex('idx-purchase_contract-doc_no', '{{%purchase_contract}}', 'doc_no');
        $this->createIndex('idx-purchase_contract-vendor', '{{%purchase_contract}}', 'vendor_id');
        $this->createIndex('idx-purchase_contract-tor', '{{%purchase_contract}}', 'tor_id');
        // วันครบกำหนดใช้กรองงานค้างส่ง/ใกล้ครบกำหนดทุกครั้งที่เปิดหน้าทะเบียน
        $this->createIndex('idx-purchase_contract-end_date', '{{%purchase_contract}}', 'end_date');
        $this->createIndex('idx-purchase_contract-deleted', '{{%purchase_contract}}', 'deleted_at');
        // ใบสั่งซื้อ 1 ใบต้องมีสัญญาได้ไม่เกิน 1 ฉบับ — MySQL ยอมให้ค่า NULL ซ้ำได้หลายแถว
        // จึงใช้ unique คุมได้โดยไม่กระทบสัญญาที่ไม่ผูกใบสั่งซื้อ
        $this->createIndex('idx-purchase_contract-order', '{{%purchase_contract}}', 'order_id', true);

        // ── งวดงาน ───────────────────────────────────────────────────────────
        $this->createTable('{{%purchase_contract_milestone}}', [
            'id' => $this->primaryKey(),
            'contract_id' => $this->integer()->notNull(),
            'seq' => $this->integer()->notNull()->defaultValue(0)->comment('ลำดับงวด'),
            'detail' => $this->string(500)->null()->comment('รายละเอียดงานในงวด'),
            'percent' => $this->decimal(6, 2)->null()->comment('สัดส่วนของวงเงิน (%)'),
            'amount' => $this->decimal(15, 2)->notNull()->defaultValue(0)->comment('วงเงินของงวด'),
            'due_date' => $this->date()->null()->comment('กำหนดส่งมอบงวดนี้'),
            'delivered_date' => $this->date()->null()->comment('วันที่ส่งมอบจริง'),
            'receive_date' => $this->date()->null()->comment('วันที่ตรวจรับงวดนี้'),
            'status' => $this->string(20)->notNull()->defaultValue('pending')
                ->comment('pending=รอส่ง, delivered=ส่งแล้ว, received=ตรวจรับแล้ว'),
            'fine_days' => $this->integer()->notNull()->defaultValue(0),
            'fine_amount' => $this->decimal(15, 2)->notNull()->defaultValue(0),
            'note' => $this->string(500)->null(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
        ], $options);

        $this->createIndex(
            'idx-purchase_contract_milestone-contract',
            '{{%purchase_contract_milestone}}',
            ['contract_id', 'seq']
        );
        $this->addForeignKey(
            'fk-purchase_contract_milestone-contract',
            '{{%purchase_contract_milestone}}',
            'contract_id',
            '{{%purchase_contract}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // ── ตารางตั้งค่าอัตราภาษีหัก ณ ที่จ่าย ────────────────────────────────
        // แยกเป็นตารางเพราะอัตรา/เกณฑ์เปลี่ยนตามกฎหมาย และหน่วยงานต้องแก้เองได้
        // โดยไม่ต้องรอแก้โค้ด
        $this->createTable('{{%purchase_wht_rate}}', [
            'id' => $this->primaryKey(),
            'code' => $this->string(20)->notNull()->comment('ตรงกับ purchase_contract.contract_type'),
            'party_type' => $this->string(20)->notNull()->comment('juristic | personal'),
            'title' => $this->string(255)->notNull()->comment('คำอธิบายที่แสดงในหน้าตั้งค่า'),
            'rate' => $this->decimal(5, 2)->notNull()->defaultValue(0)->comment('อัตรา (%)'),
            'threshold' => $this->decimal(15, 2)->notNull()->defaultValue(0)
                ->comment('ยอดจ่ายขั้นต่ำที่เริ่มหัก — ต่ำกว่านี้ไม่หัก'),
            'law_ref' => $this->string(255)->null()->comment('มาตรา/หนังสือสั่งการที่อ้างอิง'),
            'note' => $this->text()->null(),
            'active' => $this->tinyInteger(1)->notNull()->defaultValue(1),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ], $options);

        $this->createIndex('idx-purchase_wht_rate-key', '{{%purchase_wht_rate}}', ['code', 'party_type'], true);
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%purchase_wht_rate}}');
        $this->dropForeignKey('fk-purchase_contract_milestone-contract', '{{%purchase_contract_milestone}}');
        $this->dropTable('{{%purchase_contract_milestone}}');
        $this->dropTable('{{%purchase_contract}}');
    }
}
