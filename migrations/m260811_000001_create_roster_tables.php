<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * ตารางเวร (Duty Roster) — เฟส 1
 *
 * แยก "ความหมายของเวร" ออกจาก "เวลาจริงของเวร" เพราะแต่ละหน่วยงานใช้เวลาไม่เท่ากัน
 * (ER อาจ 08:00–16:00 แต่ซักฟอกเริ่ม 06:00) แต่ยังต้องรวมรายงาน "เวรดึกทั้ง รพ." ได้
 *
 *   roster_shift_type      ช/บ/ด/ควบ — ความหมายกลาง ใช้ร่วมทั้ง รพ. (ผูกอัตราค่าตอบแทนที่นี่)
 *   roster_unit_shift        เวลาเข้า-ออกจริง + จำนวนคนที่ต้องการ ของหน่วยนั้น
 *   roster_unit_rule         กฎจัดเวรของหน่วยนั้น (เตือน ไม่ใช่ห้าม)
 *
 *   roster_period          รอบเวร = 1 หน่วยงาน × 1 เดือน (หน่วยของการอนุมัติ/ประกาศ)
 *     roster_item            1 แถว = 1 คน × 1 วัน × 1 เวร  ← เก็บแนวตั้ง ไม่ใช่คอลัมน์กว้าง 31 ช่อง
 *     roster_request         คำขอหยุด/ขออยู่ ที่เจ้าหน้าที่ยื่นก่อนหัวหน้าจัดเวร
 *
 * เหตุผลที่ roster_item เก็บแนวตั้ง: รองรับควบเวร (คนเดียว 2 ผลัดในวันเดียว), join
 * checkin_record ได้ตรงเพื่อประเมินสาย (เฟส 3), และคิดค่าตอบแทนรายเวรได้โดยไม่ต้อง unpivot
 */
final class m260811_000001_create_roster_tables extends Migration
{
    public function safeUp(): void
    {
        $options = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB'
            : null;

        // ── ประเภทเวร (ความหมายกลาง) ────────────────────────────────────────
        $this->createTable('{{%roster_shift_type}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(255)->null(),
            'code' => $this->string(20)->notNull()->comment('รหัสอ้างในโค้ด เช่น M/A/N/X'),
            'short_name' => $this->string(10)->notNull()->comment('อักษรย่อที่แสดงในกริด เช่น ช/บ/ด'),
            'title' => $this->string(100)->notNull()->comment('ชื่อเต็ม เช่น เวรเช้า'),
            'is_night' => $this->tinyInteger(1)->notNull()->defaultValue(0)->comment('1 = เวรดึก ใช้กับกฎห้ามดึกติดเช้า'),
            'is_ot' => $this->tinyInteger(1)->notNull()->defaultValue(0)
                ->comment('1 = นอกเวลาราชการ ใช้คิดค่าตอบแทน (เฟส 4) — ต้องติ๊กให้ถูกตั้งแต่แรก'),
            'is_extra' => $this->tinyInteger(1)->notNull()->defaultValue(0)->comment('1 = เวรเสริม/ควบ ไม่นับเป็นเวรหลักของวัน'),
            'color' => $this->string(20)->null()
                ->comment('ชื่อสี Bootstrap (warning/info/primary/…) ใช้เป็น bg-*-subtle — เก็บชื่อไม่เก็บ hex เพื่อให้ตามธีมสว่าง/มืด'),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'active' => $this->tinyInteger(1)->notNull()->defaultValue(1),
            'data_json' => $this->json()->null(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ], $options);
        $this->createIndex('uq-roster_shift_type-code', '{{%roster_shift_type}}', 'code', true);
        $this->createIndex('idx-roster_shift_type-active', '{{%roster_shift_type}}', ['active', 'sort_order']);

        // ── เวลาเวร + จำนวนคนที่ต้องการ ต่อหน่วยงาน ──────────────────────────
        // ไม่ seed เวลาเริ่มต้น เพราะแต่ละหน่วยไม่เท่ากัน — หัวหน้าหน่วยกรอกเอง
        $this->createTable('{{%roster_unit_shift}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(255)->null(),
            'unit_id' => $this->integer()->notNull()->comment('tree.id — หน่วยงาน'),
            'shift_type_id' => $this->integer()->notNull(),
            'start_time' => $this->time()->null(),
            'end_time' => $this->time()->null(),
            'hours' => $this->decimal(4, 2)->null()->comment('ชั่วโมงต่อเวร คำนวณจาก start/end แต่แก้ทับได้'),
            'cross_midnight' => $this->tinyInteger(1)->notNull()->defaultValue(0)
                ->comment('1 = เวรข้ามเที่ยงคืน (เช่น 23:00–07:00) จำเป็นตอน join checkin ให้ตรงวัน'),
            'required_staff' => $this->integer()->notNull()->defaultValue(0)
                ->comment('จำนวนคนที่ต้องการต่อเวร — ใช้แสดงตัวนับความครบในกริด (2/2 หรือ 1/2)'),
            'active' => $this->tinyInteger(1)->notNull()->defaultValue(1),
            'data_json' => $this->json()->null(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ], $options);
        $this->createIndex('uq-roster_unit_shift-unit_type', '{{%roster_unit_shift}}', ['unit_id', 'shift_type_id'], true);
        $this->addForeignKey('fk-roster_unit_shift-type', '{{%roster_unit_shift}}', 'shift_type_id', '{{%roster_shift_type}}', 'id', 'CASCADE', 'CASCADE');

        // ── กฎการจัดเวรของหน่วย (key-value เพื่อเพิ่มกฎใหม่ได้โดยไม่ต้อง migrate) ──
        // ทุกกฎเป็น "เตือน" ไม่ใช่ "ห้ามบันทึก" — หน้างานจริงมีวันที่คนไม่พอจนต้องฝืนกฎ
        $this->createTable('{{%roster_unit_rule}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(255)->null(),
            'unit_id' => $this->integer()->notNull()->comment('tree.id'),
            'rule_key' => $this->string(60)->notNull()
                ->comment('min_rest_hours | max_rest_violations | forbid_same_day | forbid_next_day | max_consecutive_shift | max_consecutive_workdays'),
            'shift_type_id' => $this->integer()->null()->comment('กฎที่ผูกกับเวรชนิดหนึ่ง เช่น ดึกติดกันไม่เกิน N วัน'),
            'int_value' => $this->integer()->null(),
            'data_json' => $this->json()->null()->comment('กฎที่เป็นคู่เวร เช่น {"a":1,"b":2} = ห้าม ช คู่กับ บ'),
            'active' => $this->tinyInteger(1)->notNull()->defaultValue(1),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ], $options);
        $this->createIndex('idx-roster_unit_rule-unit', '{{%roster_unit_rule}}', ['unit_id', 'active']);
        $this->addForeignKey('fk-roster_unit_rule-type', '{{%roster_unit_rule}}', 'shift_type_id', '{{%roster_shift_type}}', 'id', 'SET NULL', 'CASCADE');

        // ── รอบเวร = 1 หน่วยงาน × 1 เดือน ───────────────────────────────────
        $this->createTable('{{%roster_period}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(255)->null(),
            'unit_id' => $this->integer()->notNull()->comment('tree.id'),
            'month' => $this->tinyInteger()->notNull()->comment('1–12'),
            'year_ce' => $this->smallInteger()->notNull()->comment('ค.ศ. ใช้คำนวณวันที่'),
            'thai_year' => $this->smallInteger()->null()->comment('พ.ศ. ใช้แสดงผล'),
            'title' => $this->string(255)->null(),
            'status' => $this->string(20)->notNull()->defaultValue('draft')
                ->comment('draft | submitted | approved | published | closed'),
            'note' => $this->text()->null(),
            'submitted_at' => $this->dateTime()->null(),
            'submitted_by' => $this->integer()->null(),
            'approved_at' => $this->dateTime()->null(),
            'approved_by' => $this->integer()->null(),
            'published_at' => $this->dateTime()->null(),
            'published_by' => $this->integer()->null(),
            'data_json' => $this->json()->null(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
            'deleted_at' => $this->dateTime()->null(),
            'deleted_by' => $this->integer()->null(),
        ], $options);
        $this->createIndex('uq-roster_period-unit_month', '{{%roster_period}}', ['unit_id', 'year_ce', 'month'], true);
        $this->createIndex('idx-roster_period-status', '{{%roster_period}}', ['status', 'year_ce', 'month']);

        // ── ช่องเวร: 1 แถว = 1 คน × 1 วัน × 1 เวร ────────────────────────────
        $this->createTable('{{%roster_item}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(255)->null(),
            'period_id' => $this->integer()->notNull(),
            'emp_id' => $this->integer()->notNull()->comment('employees.id'),
            'work_date' => $this->date()->notNull(),
            'shift_type_id' => $this->integer()->notNull(),
            'is_extra' => $this->tinyInteger(1)->notNull()->defaultValue(0)->comment('1 = ควบเวร/เวรเสริมของวันนั้น'),
            'status' => $this->string(20)->notNull()->defaultValue('planned')->comment('planned | swapped | cancelled'),
            'note' => $this->string(255)->null(),
            // เว้นไว้ให้เฟส 4 (ค่าตอบแทน) จะได้ไม่ต้อง migrate ซ้ำ — snapshot ตอนอนุมัติ
            'ot_rate' => $this->decimal(10, 2)->null()->comment('อัตราที่ใช้จริง บันทึกตอนอนุมัติ (เฟส 4)'),
            'ot_amount' => $this->decimal(10, 2)->null()->comment('จำนวนเงินที่คำนวณได้ (เฟส 4)'),
            'data_json' => $this->json()->null(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ], $options);
        // กันจัดซ้ำโดยไม่ตั้งใจ แต่ควบเวร (คนละ shift_type ในวันเดียว) ยังทำได้
        $this->createIndex('uq-roster_item-emp_date_shift', '{{%roster_item}}', ['emp_id', 'work_date', 'shift_type_id'], true);
        $this->createIndex('idx-roster_item-period', '{{%roster_item}}', ['period_id', 'work_date']);
        $this->createIndex('idx-roster_item-date', '{{%roster_item}}', ['work_date', 'shift_type_id']);
        $this->addForeignKey('fk-roster_item-period', '{{%roster_item}}', 'period_id', '{{%roster_period}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-roster_item-type', '{{%roster_item}}', 'shift_type_id', '{{%roster_shift_type}}', 'id', 'RESTRICT', 'CASCADE');

        // ── คำขอหยุด/ขออยู่ ที่ยื่นก่อนหัวหน้าจัดเวร ──────────────────────────
        $this->createTable('{{%roster_request}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(255)->null(),
            'period_id' => $this->integer()->null()->comment('NULL = ยื่นล่วงหน้าก่อนหัวหน้าเปิดรอบ'),
            'unit_id' => $this->integer()->notNull()->comment('tree.id — ใช้หาคำขอได้แม้ยังไม่มีรอบ'),
            'emp_id' => $this->integer()->notNull(),
            'work_date' => $this->date()->notNull(),
            'type' => $this->string(10)->notNull()->comment('off = ขอหยุด | on = ขออยู่เวร'),
            'shift_type_id' => $this->integer()->null()->comment('ระบุผลัดที่ขอ (เฉพาะ type=on)'),
            'reason' => $this->string(255)->null(),
            'status' => $this->string(20)->notNull()->defaultValue('pending')
                ->comment('pending | accepted | rejected — หัวหน้าตอบตอนจัดเวร'),
            'responded_at' => $this->dateTime()->null(),
            'responded_by' => $this->integer()->null(),
            'data_json' => $this->json()->null(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ], $options);
        $this->createIndex('uq-roster_request-emp_date_type', '{{%roster_request}}', ['emp_id', 'work_date', 'type'], true);
        $this->createIndex('idx-roster_request-unit_date', '{{%roster_request}}', ['unit_id', 'work_date']);
        $this->createIndex('idx-roster_request-period', '{{%roster_request}}', ['period_id', 'status']);
        $this->addForeignKey('fk-roster_request-type', '{{%roster_request}}', 'shift_type_id', '{{%roster_shift_type}}', 'id', 'SET NULL', 'CASCADE');
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%roster_request}}');
        $this->dropTable('{{%roster_item}}');
        $this->dropTable('{{%roster_period}}');
        $this->dropTable('{{%roster_unit_rule}}');
        $this->dropTable('{{%roster_unit_shift}}');
        $this->dropTable('{{%roster_shift_type}}');
    }
}
