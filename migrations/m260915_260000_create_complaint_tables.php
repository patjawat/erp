<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * ระบบรับเรื่องร้องเรียน (Complaint) — เฟส 1 : โครงตาราง
 *
 * พอร์ตจากระบบเดิม Google Apps Script + Sheets (รพ.แวงน้อย) มาเป็น Yii2/RDBMS
 * ตามข้อเสนอในเอกสารสรุป (ข้อ 9): consolidate ขั้นตอน workflow 1:1 ไว้ในตารางหลัก
 * แล้วแยกตารางลูกเฉพาะที่เป็น 1:หลาย (การดำเนินงาน/ไฟล์แนบ/แบบสำรวจ/ประวัติ)
 *
 * คอนเวนชันเดียวกับ KM/QMS:
 *   หน่วยงาน = tree.id (bigint) ตามผังองค์กร, คน = employees.id (int ผ่าน created_by/updated_by
 *   และคอลัมน์ *_by), fiscal_year เก็บเป็น int (พ.ศ.) ตรง ๆ
 *
 * ขอบเขตเฟส 1: ภายในเท่านั้น (เจ้าหน้าที่บันทึกให้) — ไม่มี route สาธารณะ
 */
final class m260915_260000_create_complaint_tables extends Migration
{
    public function safeUp(): void
    {
        $audit = fn (): array => [
            'ref' => $this->string(64)->notNull(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ];

        // 1) ตัวเลือกกลาง (แทน Master_Data sheet) --------------------------------
        // group: channel | type | patient_right | relationship | action_kind | reject_reason
        $this->createTable('{{%complaint_master}}', array_merge([
            'id' => $this->primaryKey(),
            'group' => $this->string(32)->notNull()->comment('กลุ่มตัวเลือก: channel|type|patient_right|relationship|action_kind|reject_reason'),
            'name' => $this->string(255)->notNull()->comment('ชื่อรายการ'),
            'code' => $this->string(64)->null()->comment('รหัสอ้างอิง (ถ้ามี)'),
            'sort' => $this->integer()->notNull()->defaultValue(0),
            'is_active' => $this->tinyInteger(1)->notNull()->defaultValue(1),
        ], $audit()));
        $this->createIndex('uq-complaint_master-ref', '{{%complaint_master}}', 'ref', true);
        $this->createIndex('idx-complaint_master-group', '{{%complaint_master}}', ['group', 'is_active', 'sort']);

        // 2) ตารางหลัก (แทน Complaint_Log + Step2/3/5 sheet แบบ 1:1) --------------
        $this->createTable('{{%complaint}}', array_merge([
            'id' => $this->primaryKey(),
            'complaint_no' => $this->string(32)->notNull()->comment('เลขที่เรื่อง (CPL-ปีงบ-ลำดับ)'),
            'tracking_code' => $this->string(16)->notNull()->comment('รหัสติดตาม (สุ่ม)'),
            'fiscal_year' => $this->integer()->notNull()->comment('ปีงบประมาณ (พ.ศ.)'),
            'status' => $this->string(16)->notNull()->defaultValue('reported')
                ->comment('reported|intake|assessed|in_progress|closed|rejected'),

            // --- ขั้น 1 แจ้งเรื่อง ---
            'complaint_date' => $this->date()->null()->comment('วันที่ร้องเรียน'),
            'complaint_time' => $this->time()->null()->comment('เวลาร้องเรียน'),
            'channel_id' => $this->integer()->null()->comment('ช่องทาง (complaint_master)'),
            'type_id' => $this->integer()->null()->comment('ประเภท (complaint_master)'),
            'title' => $this->string(500)->notNull()->comment('ชื่อเรื่องโดยย่อ'),
            'detail' => $this->text()->null()->comment('รายละเอียดเรื่องร้องเรียน'),
            'reporter_name' => $this->string(255)->null()->comment('ชื่อผู้ร้อง'),
            'reporter_phone' => $this->string(64)->null()->comment('เบอร์ติดต่อ'),
            'reporter_relation_id' => $this->integer()->null()->comment('ความสัมพันธ์ผู้ร้อง (complaint_master)'),
            'is_anonymous' => $this->tinyInteger(1)->notNull()->defaultValue(0)->comment('ไม่ประสงค์ออกนาม'),
            'assigned_unit_id' => $this->bigInteger()->null()->comment('หน่วยงานที่รับผิดชอบ (tree.id)'),
            'assigned_to' => $this->integer()->null()->comment('ผู้รับผิดชอบ (employees.id)'),

            // --- ขั้น 2 รับเรื่อง (Step2_Intake) ---
            'intake_date' => $this->date()->null()->comment('วันที่รับเรื่อง'),
            'intake_by' => $this->integer()->null()->comment('ผู้รับเรื่อง (employees.id)'),
            'intake_checklist' => $this->text()->null()->comment('checklist ความครบถ้วน (JSON)'),
            'intake_address' => $this->string(500)->null()->comment('ที่อยู่ผู้ร้อง'),
            'intake_impact' => $this->text()->null()->comment('ผลกระทบ'),
            'patient_right_id' => $this->integer()->null()->comment('สิทธิผู้ป่วยที่เกี่ยวข้อง (complaint_master)'),
            'intake_need' => $this->text()->null()->comment('ความต้องการของผู้ร้อง'),
            'intake_note' => $this->text()->null()->comment('บันทึกเพิ่มเติมขั้นรับเรื่อง'),

            // --- ขั้น 3 ประเมิน (Step3_Assessment) ---
            'assess_date' => $this->date()->null()->comment('วันที่ประเมิน'),
            'assess_by' => $this->integer()->null()->comment('ผู้ประเมิน (employees.id)'),
            'severity_level' => $this->tinyInteger(1)->null()->comment('ระดับความรุนแรง 1-4'),
            'risk_level' => $this->string(32)->null()->comment('ระดับความเสี่ยง (เช่น เมทริกซ์ HFMEA)'),
            'need_rca' => $this->tinyInteger(1)->notNull()->defaultValue(0)->comment('ต้องทำ RCA'),
            'assess_decision' => $this->string(16)->null()->comment('accept|refer|reject'),
            'assess_note' => $this->text()->null()->comment('บันทึกการประเมิน'),
            'reject_reason_id' => $this->integer()->null()->comment('เหตุผลไม่รับพิจารณา (complaint_master)'),

            // --- SLA (คำนวณจาก severity_level + complaint_date ตอนประเมิน) ---
            'respond_due' => $this->date()->null()->comment('กำหนดตอบสนอง'),
            'review_due' => $this->date()->null()->comment('กำหนดตรวจสอบ/RCA'),
            'reply_due' => $this->date()->null()->comment('กำหนดตอบกลับ'),
            'close_due' => $this->date()->null()->comment('กำหนดปิดเคส'),

            // --- ขั้น 5 ปิดเคส (Step5_Close) ---
            'close_date' => $this->date()->null()->comment('วันที่ปิดเคส'),
            'close_by' => $this->integer()->null()->comment('ผู้ปิดเคส (employees.id)'),
            'lesson_learned' => $this->text()->null()->comment('ถอดบทเรียน'),
            'remedy' => $this->text()->null()->comment('การเยียวยา'),
            'close_note' => $this->text()->null()->comment('บันทึกปิดเคส/ข้อเสนอแนะ'),
        ], $audit()));
        $this->createIndex('uq-complaint-ref', '{{%complaint}}', 'ref', true);
        $this->createIndex('uq-complaint-no', '{{%complaint}}', 'complaint_no', true);
        $this->createIndex('uq-complaint-tracking', '{{%complaint}}', 'tracking_code', true);
        $this->createIndex('idx-complaint-year', '{{%complaint}}', ['fiscal_year', 'status']);
        $this->createIndex('idx-complaint-unit', '{{%complaint}}', 'assigned_unit_id');
        $this->createIndex('idx-complaint-assignee', '{{%complaint}}', 'assigned_to');
        $this->createIndex('idx-complaint-date', '{{%complaint}}', 'complaint_date');
        $this->createIndex('idx-complaint-severity', '{{%complaint}}', 'severity_level');
        $this->addForeignKey('fk-complaint-unit', '{{%complaint}}', 'assigned_unit_id', '{{%tree}}', 'id', 'SET NULL', 'CASCADE');

        // 3) การดำเนินงาน L1-L4 (Step4_Action) — 1:หลาย, timeline ---------------
        $this->createTable('{{%complaint_action}}', array_merge([
            'id' => $this->primaryKey(),
            'complaint_id' => $this->integer()->notNull(),
            'action_date' => $this->date()->null()->comment('วันที่ดำเนินการ'),
            'action_level' => $this->tinyInteger(1)->null()->comment('ระดับ L1-L4'),
            'action_kind' => $this->string(32)->null()->comment('start|review|rca|rrt|committee|reply|meeting|remedy|outcome|refer'),
            'title' => $this->string(500)->null()->comment('หัวข้อ/สรุปการดำเนินการ'),
            'detail' => $this->text()->null()->comment('รายละเอียด'),
            'action_by' => $this->integer()->null()->comment('ผู้ดำเนินการ (employees.id)'),
        ], $audit()));
        $this->createIndex('uq-complaint_action-ref', '{{%complaint_action}}', 'ref', true);
        $this->createIndex('idx-complaint_action-cid', '{{%complaint_action}}', ['complaint_id', 'action_date']);
        $this->addForeignKey('fk-complaint_action-cid', '{{%complaint_action}}', 'complaint_id', '{{%complaint}}', 'id', 'CASCADE', 'CASCADE');

        // 4) ไฟล์แนบ (Attachments) — self-managed storage นอก webroot ------------
        $this->createTable('{{%complaint_attachment}}', array_merge([
            'id' => $this->primaryKey(),
            'complaint_id' => $this->integer()->notNull(),
            'action_id' => $this->integer()->null()->comment('ผูกกับการดำเนินการ (ถ้ามี)'),
            'category' => $this->string(32)->notNull()->defaultValue('general')
                ->comment('publicComplaint|intakeEvidence|assessmentEvidence|actionReview|actionReply|closeEvidence|general'),
            'file_path' => $this->string(500)->notNull()->comment('พาธไฟล์จริง (relative นอก webroot)'),
            'thumbnail_path' => $this->string(500)->null()->comment('พาธ thumbnail (รูปภาพ)'),
            'file_name' => $this->string(255)->null()->comment('ชื่อไฟล์เดิม'),
            'mime' => $this->string(128)->null(),
            'size' => $this->integer()->null()->comment('ขนาด (bytes)'),
            'sort' => $this->integer()->notNull()->defaultValue(0),
        ], $audit()));
        $this->createIndex('uq-complaint_attachment-ref', '{{%complaint_attachment}}', 'ref', true);
        $this->createIndex('idx-complaint_attachment-cid', '{{%complaint_attachment}}', ['complaint_id', 'category', 'sort']);
        $this->addForeignKey('fk-complaint_attachment-cid', '{{%complaint_attachment}}', 'complaint_id', '{{%complaint}}', 'id', 'CASCADE', 'CASCADE');

        // 5) แบบสำรวจความพึงพอใจ (Satisfaction_Survey) — 1:หลาย ------------------
        $this->createTable('{{%complaint_survey}}', array_merge([
            'id' => $this->primaryKey(),
            'complaint_id' => $this->integer()->notNull(),
            'survey_date' => $this->date()->null(),
            'score1' => $this->tinyInteger(1)->null()->comment('ด้านที่ 1 (1-5)'),
            'score2' => $this->tinyInteger(1)->null()->comment('ด้านที่ 2 (1-5)'),
            'score3' => $this->tinyInteger(1)->null()->comment('ด้านที่ 3 (1-5)'),
            'score4' => $this->tinyInteger(1)->null()->comment('ด้านที่ 4 (1-5)'),
            'score5' => $this->tinyInteger(1)->null()->comment('ด้านที่ 5 (1-5)'),
            'avg_score' => $this->decimal(4, 2)->null()->comment('คะแนนเฉลี่ย'),
            'comment' => $this->text()->null()->comment('ข้อเสนอแนะ'),
        ], $audit()));
        $this->createIndex('uq-complaint_survey-ref', '{{%complaint_survey}}', 'ref', true);
        $this->createIndex('idx-complaint_survey-cid', '{{%complaint_survey}}', 'complaint_id');
        $this->addForeignKey('fk-complaint_survey-cid', '{{%complaint_survey}}', 'complaint_id', '{{%complaint}}', 'id', 'CASCADE', 'CASCADE');

        // 6) ประวัติ/audit trail (Process_Log) — บันทึกการเปลี่ยนสถานะ -----------
        $this->createTable('{{%complaint_log}}', array_merge([
            'id' => $this->primaryKey(),
            'complaint_id' => $this->integer()->notNull(),
            'action' => $this->string(32)->notNull()->comment('create|status|update|note'),
            'from_status' => $this->string(16)->null(),
            'to_status' => $this->string(16)->null(),
            'note' => $this->string(500)->null(),
        ], $audit()));
        $this->createIndex('uq-complaint_log-ref', '{{%complaint_log}}', 'ref', true);
        $this->createIndex('idx-complaint_log-cid', '{{%complaint_log}}', ['complaint_id', 'id']);
        $this->addForeignKey('fk-complaint_log-cid', '{{%complaint_log}}', 'complaint_id', '{{%complaint}}', 'id', 'CASCADE', 'CASCADE');

        $this->seedMaster();
    }

    /** เติมตัวเลือกกลางเริ่มต้น (แก้ไข/เพิ่มได้ที่หน้าตั้งค่า) */
    private function seedMaster(): void
    {
        $now = date('Y-m-d H:i:s');
        $rows = [];
        $add = function (string $group, array $names) use (&$rows, $now): void {
            $i = 0;
            foreach ($names as $name) {
                $rows[] = [$group, $name, ++$i, 1, bin2hex(random_bytes(12)), $now];
            }
        };

        $add('channel', ['ด้วยตนเอง', 'โทรศัพท์', 'หนังสือ/จดหมาย', 'กล่องรับความคิดเห็น', 'เว็บไซต์/ออนไลน์', 'Facebook/Line', 'สื่อมวลชน', 'หน่วยงานภายนอก/ต้นสังกัด']);
        $add('type', ['พฤติกรรมบริการ', 'ระบบบริการ/ขั้นตอน', 'คุณภาพการรักษา', 'ความปลอดภัยผู้ป่วย', 'สิ่งแวดล้อม/สถานที่', 'ค่าใช้จ่าย/สิทธิ', 'อื่น ๆ']);
        $add('patient_right', ['สิทธิได้รับข้อมูล', 'สิทธิการรักษาที่ได้มาตรฐาน', 'สิทธิความเป็นส่วนตัว', 'สิทธิได้รับความยินยอม', 'สิทธิการร้องเรียน', 'อื่น ๆ']);
        $add('relationship', ['ผู้ป่วยเอง', 'ญาติ', 'ผู้ดูแล', 'เจ้าหน้าที่', 'บุคคลทั่วไป']);
        $add('action_kind', ['เริ่มดำเนินการ', 'ตรวจสอบข้อเท็จจริง/RCA', 'ทีม RRT/ลงหน้างาน', 'ประชุมคณะกรรมการ', 'ตอบกลับผู้ร้อง', 'ประชุม/เยียวยา', 'ปรับปรุงแก้ไข', 'สรุปผล']);
        $add('reject_reason', ['ไม่อยู่ในขอบเขตความรับผิดชอบ', 'ข้อมูลไม่เพียงพอ/ติดต่อไม่ได้', 'ซ้ำกับเรื่องเดิม', 'เป็นข้อเสนอแนะทั่วไป']);

        $this->batchInsert('{{%complaint_master}}', ['group', 'name', 'sort', 'is_active', 'ref', 'created_at'], $rows);
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%complaint_log}}');
        $this->dropTable('{{%complaint_survey}}');
        $this->dropTable('{{%complaint_attachment}}');
        $this->dropTable('{{%complaint_action}}');
        $this->dropTable('{{%complaint}}');
        $this->dropTable('{{%complaint_master}}');
    }
}
