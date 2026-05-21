<?php

use yii\db\Migration;

/**
 * สร้างตารางหลักของ purchaseV2 แบบปลอดภัย
 */
class m260520_000001_create_purchase_v2_tables extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createPurchaseRequestTable($tableOptions);
        $this->createPurchaseRequestItemTable($tableOptions);
        $this->createPurchaseRequestApprovalTable($tableOptions);
        $this->createPurchaseRequestLogTable($tableOptions);
    }

    public function safeDown()
    {
        $this->dropTableIfExists('{{%purchase_request_log}}');
        $this->dropTableIfExists('{{%purchase_request_approval}}');
        $this->dropTableIfExists('{{%purchase_request_item}}');
        $this->dropTableIfExists('{{%purchase_request}}');
    }

    protected function createPurchaseRequestTable($tableOptions)
    {
        $table = '{{%purchase_request}}';
        if ($this->tableExists($table)) {
            return;
        }

        $this->createTable($table, [
            'id' => $this->primaryKey(),
            'ref' => $this->string(255)->notNull()->comment('รหัสอ้างอิงหลักสำหรับไฟล์แนบ'),
            'request_no' => $this->string(100)->notNull()->comment('เลขที่คำขอ'),
            'request_date' => $this->date()->comment('วันที่คำขอ'),
            'request_type' => $this->string(20)->notNull()->defaultValue('planned')->comment('planned/unplanned'),
            'request_title' => $this->string(255)->notNull()->comment('ชื่อเรื่องคำขอ'),
            'summary' => $this->text()->comment('รายละเอียด/ความจำเป็น'),
            'requester_emp_id' => $this->integer()->comment('ผู้ขอ'),
            'department_id' => $this->integer()->comment('หน่วยงาน'),
            'budget_year' => $this->integer()->comment('ปีงบประมาณ'),
            'budget_type_code' => $this->string(50)->comment('ประเภทงบประมาณ'),
            'budget_amount' => $this->decimal(15, 2)->defaultValue(0)->comment('วงเงินงบประมาณ'),
            'subtotal_amount' => $this->decimal(15, 2)->defaultValue(0)->comment('ยอดก่อนหักส่วนลด'),
            'discount_amount' => $this->decimal(15, 2)->defaultValue(0)->comment('ส่วนลด'),
            'vat_type' => $this->string(10)->notNull()->defaultValue('NONE')->comment('NONE / IN / EX'),
            'vat_amount' => $this->decimal(15, 2)->defaultValue(0)->comment('ภาษีมูลค่าเพิ่ม'),
            'grand_total' => $this->decimal(15, 2)->defaultValue(0)->comment('ยอดรวมสุทธิ'),
            'vendor_id' => $this->string(255)->comment('รหัสผู้ขาย/ผู้รับจ้าง (categorise.code)'),
            'vendor_name' => $this->string(255)->comment('ชื่อผู้ขาย/ผู้รับจ้าง (categorise.title)'),
            'status' => $this->integer()->notNull()->defaultValue(0)->comment('สถานะกระบวนการ'),
            'current_approval_level' => $this->integer()->defaultValue(0)->comment('ระดับอนุมัติปัจจุบัน'),
            'pr_number' => $this->string(100)->comment('เลขที่ขอซื้อ'),
            'pq_number' => $this->string(100)->comment('เลขทะเบียนคุม'),
            'po_number' => $this->string(100)->comment('เลขที่สั่งซื้อ'),
            'gr_number' => $this->string(100)->comment('เลขที่ตรวจรับ'),
            'submitted_at' => $this->dateTime()->comment('เวลาส่งอนุมัติ'),
            'approved_at' => $this->dateTime()->comment('เวลาผ่านอนุมัติ'),
            'ordered_at' => $this->dateTime()->comment('เวลาออกใบสั่งซื้อ'),
            'received_at' => $this->dateTime()->comment('เวลาได้รับของ'),
            'stocked_at' => $this->dateTime()->comment('เวลาเข้าคลัง'),
            'completed_at' => $this->dateTime()->comment('เวลาปิดงาน'),
            'cancelled_at' => $this->dateTime()->comment('เวลายกเลิก'),
            'legacy_order_id' => $this->integer()->comment('ID จากตาราง orders เดิม'),
            'legacy_ref' => $this->string(255)->comment('ref เดิมเพื่อ trace'),
            'legacy_status' => $this->integer()->comment('สถานะเดิม'),
            'migrated_from' => $this->string(50)->comment('ต้นทางการย้ายข้อมูล'),
            'migrated_at' => $this->dateTime()->comment('เวลาย้ายข้อมูล'),
            'migrated_by' => $this->integer()->comment('ผู้ย้ายข้อมูล'),
            'data_json' => $this->json()->comment('ข้อมูลเสริม'),
            'created_at' => $this->dateTime()->comment('วันที่สร้าง'),
            'updated_at' => $this->dateTime()->comment('วันที่แก้ไข'),
            'created_by' => $this->integer()->comment('ผู้สร้าง'),
            'updated_by' => $this->integer()->comment('ผู้แก้ไข'),
        ], $tableOptions);

        $this->createIndex('ux_purchase_request_ref', $table, 'ref', true);
        $this->createIndex('ux_purchase_request_request_no', $table, 'request_no', true);
        $this->createIndex('ux_purchase_request_legacy_order_id', $table, 'legacy_order_id', true);
        $this->createIndex('idx_purchase_request_status', $table, 'status');
        $this->createIndex('idx_purchase_request_request_date', $table, 'request_date');
        $this->createIndex('idx_purchase_request_requester', $table, 'requester_emp_id');
        $this->createIndex('idx_purchase_request_department', $table, 'department_id');
        $this->createIndex('idx_purchase_request_year', $table, 'budget_year');
    }

    protected function createPurchaseRequestItemTable($tableOptions)
    {
        $table = '{{%purchase_request_item}}';
        if ($this->tableExists($table)) {
            return;
        }

        $this->createTable($table, [
            'id' => $this->primaryKey(),
            'request_id' => $this->integer()->notNull()->comment('อ้างอิง purchase_request.id'),
            'line_no' => $this->integer()->defaultValue(1)->comment('ลำดับรายการ'),
            'item_type' => $this->string(30)->comment('ประเภท รายการ/ครุภัณฑ์/บริการ'),
            'item_code' => $this->string(100)->comment('รหัสรายการ'),
            'item_name' => $this->string(255)->notNull()->comment('ชื่อรายการ'),
            'detail' => $this->text()->comment('รายละเอียด'),
            'unit_name' => $this->string(100)->comment('หน่วยนับ'),
            'qty' => $this->decimal(12, 2)->defaultValue(0)->comment('จำนวน'),
            'unit_price' => $this->decimal(15, 2)->defaultValue(0)->comment('ราคาต่อหน่วย'),
            'amount' => $this->decimal(15, 2)->defaultValue(0)->comment('ราคารวม'),
            'budget_type_code' => $this->string(50)->comment('ประเภทงบ'),
            'legacy_order_item_id' => $this->integer()->comment('ID รายการเดิม'),
            'legacy_ref' => $this->string(255)->comment('ref เดิมของรายการ'),
            'data_json' => $this->json()->comment('ข้อมูลเสริม'),
            'created_at' => $this->dateTime()->comment('วันที่สร้าง'),
            'updated_at' => $this->dateTime()->comment('วันที่แก้ไข'),
            'created_by' => $this->integer()->comment('ผู้สร้าง'),
            'updated_by' => $this->integer()->comment('ผู้แก้ไข'),
        ], $tableOptions);

        $this->createIndex('idx_purchase_request_item_request', $table, 'request_id');
        $this->createIndex('idx_purchase_request_item_line', $table, ['request_id', 'line_no']);
        $this->createIndex('ux_purchase_request_item_legacy_id', $table, 'legacy_order_item_id', true);
    }

    protected function createPurchaseRequestApprovalTable($tableOptions)
    {
        $table = '{{%purchase_request_approval}}';
        if ($this->tableExists($table)) {
            return;
        }

        $this->createTable($table, [
            'id' => $this->primaryKey(),
            'request_id' => $this->integer()->notNull()->comment('อ้างอิง purchase_request.id'),
            'step_no' => $this->integer()->defaultValue(1)->comment('ลำดับขั้น'),
            'step_type' => $this->string(50)->notNull()->defaultValue('workflow')->comment('workflow/committee/committee_detail'),
            'role_name' => $this->string(100)->comment('ชื่อบทบาท'),
            'approver_emp_id' => $this->integer()->comment('รหัสพนักงานผู้อนุมัติ'),
            'approver_user_id' => $this->integer()->comment('รหัส user ผู้อนุมัติ'),
            'approver_name' => $this->string(255)->comment('ชื่อผู้อนุมัติ'),
            'approver_position' => $this->string(255)->comment('ตำแหน่งผู้อนุมัติ'),
            'status' => $this->string(20)->notNull()->defaultValue('None')->comment('None/Pending/Pass/Reject/Info'),
            'comment' => $this->text()->comment('ความคิดเห็น'),
            'action_at' => $this->dateTime()->comment('เวลาทำรายการ'),
            'legacy_approve_id' => $this->integer()->comment('ID approve เดิม'),
            'data_json' => $this->json()->comment('ข้อมูลเสริม'),
            'created_at' => $this->dateTime()->comment('วันที่สร้าง'),
            'updated_at' => $this->dateTime()->comment('วันที่แก้ไข'),
            'created_by' => $this->integer()->comment('ผู้สร้าง'),
            'updated_by' => $this->integer()->comment('ผู้แก้ไข'),
        ], $tableOptions);

        $this->createIndex('idx_purchase_request_approval_request', $table, 'request_id');
        $this->createIndex('idx_purchase_request_approval_step', $table, ['request_id', 'step_no']);
        $this->createIndex('idx_purchase_request_approval_status', $table, 'status');
        $this->createIndex('idx_purchase_request_approval_emp', $table, 'approver_emp_id');
        $this->createIndex('ux_purchase_request_approval_legacy_id', $table, 'legacy_approve_id', true);
    }

    protected function createPurchaseRequestLogTable($tableOptions)
    {
        $table = '{{%purchase_request_log}}';
        if ($this->tableExists($table)) {
            return;
        }

        $this->createTable($table, [
            'id' => $this->primaryKey(),
            'request_id' => $this->integer()->notNull()->comment('อ้างอิง purchase_request.id'),
            'action' => $this->string(50)->notNull()->comment('action code'),
            'message' => $this->text()->comment('ข้อความบันทึก'),
            'from_status' => $this->integer()->comment('สถานะก่อนหน้า'),
            'to_status' => $this->integer()->comment('สถานะใหม่'),
            'actor_emp_id' => $this->integer()->comment('พนักงานผู้กระทำ'),
            'actor_user_id' => $this->integer()->comment('user ผู้กระทำ'),
            'data_json' => $this->json()->comment('ข้อมูลเสริม'),
            'created_at' => $this->dateTime()->comment('วันที่สร้าง'),
            'created_by' => $this->integer()->comment('ผู้สร้าง'),
        ], $tableOptions);

        $this->createIndex('idx_purchase_request_log_request', $table, 'request_id');
        $this->createIndex('idx_purchase_request_log_action', $table, ['request_id', 'action']);
        $this->createIndex('idx_purchase_request_log_created_at', $table, 'created_at');
    }

    protected function tableExists($tableName): bool
    {
        return $this->db->getTableSchema($tableName, true) !== null;
    }

    protected function dropTableIfExists($tableName): void
    {
        if ($this->tableExists($tableName)) {
            $this->dropTable($tableName);
        }
    }
}
