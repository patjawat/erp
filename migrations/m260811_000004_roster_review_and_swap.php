<?php

declare(strict_types=1);

use app\modules\approveV2\models\ApproveLevelSetting;
use yii\db\Migration;

/**
 * เพิ่มขั้น "ตรวจสอบ" และระบบแลกเวร
 *
 * สายงานจริง: หัวหน้าหน่วยจัด → หัวหน้ากลุ่มงานตรวจสอบ → ผู้อำนวยการอนุมัติ+ประกาศ
 * (ยุบ approved กับ published เป็นขั้นเดียว เพราะเมื่อ ผอ. อนุมัติแล้วต้องประกาศทันที
 *  คอลัมน์ approved_at/approved_by ยังเก็บไว้บันทึกว่า ผอ. อนุมัติเมื่อไร)
 *
 * หลังประกาศแล้ว ตารางเวรเป็นเอกสารที่ใช้เบิกค่าตอบแทน จึงแก้กริดตรงๆ ไม่ได้
 * ทุกการเปลี่ยนตัวต้องผ่าน roster_swap เพื่อให้ตรวจสอบย้อนหลังได้
 */
final class m260811_000004_roster_review_and_swap extends Migration
{
    public function safeUp(): void
    {
        $options = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB'
            : null;

        // ── ขั้นตรวจสอบโดยหัวหน้ากลุ่มงาน ──
        $this->addColumn('{{%roster_period}}', 'reviewed_at', $this->dateTime()->null()->after('submitted_by'));
        $this->addColumn('{{%roster_period}}', 'reviewed_by', $this->integer()->null()->after('reviewed_at')
            ->comment('หัวหน้ากลุ่มงานที่ตรวจสอบ'));

        // ── ใบเปลี่ยนตัวเวร (แลก / ยกให้ / หัวหน้าเปลี่ยนตัวฉุกเฉิน) ──
        $this->createTable('{{%roster_swap}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(255)->null(),
            'period_id' => $this->integer()->notNull(),
            'item_id' => $this->integer()->notNull()->comment('เวรที่ถูกเปลี่ยนตัว'),
            'counter_item_id' => $this->integer()->null()
                ->comment('เวรของอีกฝ่ายที่ยกมาแลก — NULL = ไม่ใช่การแลกสองทาง'),
            'type' => $this->string(10)->notNull()
                ->comment('swap = แลกกันสองทาง | give = ยกเวรให้ | replace = หัวหน้าเปลี่ยนตัวฉุกเฉิน'),
            'from_emp_id' => $this->integer()->notNull()->comment('คนเดิม บันทึกไว้ตอนยื่น เพื่อตรวจย้อนหลังได้แม้ item เปลี่ยนไปแล้ว'),
            'to_emp_id' => $this->integer()->notNull()->comment('คนใหม่ที่มารับเวร'),
            'reason' => $this->string(255)->null(),
            'status' => $this->string(20)->notNull()->defaultValue('pending')
                ->comment('pending → accepted → approved | rejected | cancelled'),
            'requested_by' => $this->integer()->null()->comment('emp_id ผู้ยื่น (replace = หัวหน้า)'),
            'responded_at' => $this->dateTime()->null(),
            'responded_by' => $this->integer()->null()->comment('คู่กรณีที่ตอบรับ/ปฏิเสธ'),
            'approved_at' => $this->dateTime()->null(),
            'approved_by' => $this->integer()->null()->comment('หัวหน้าหน่วยที่อนุมัติ'),
            'warnings' => $this->json()->null()->comment('กฎที่ถูกละเมิดจากการเปลี่ยนตัวครั้งนี้ เก็บไว้เป็นหลักฐาน'),
            'data_json' => $this->json()->null(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ], $options);
        $this->createIndex('idx-roster_swap-period', '{{%roster_swap}}', ['period_id', 'status']);
        $this->createIndex('idx-roster_swap-item', '{{%roster_swap}}', 'item_id');
        $this->createIndex('idx-roster_swap-to_emp', '{{%roster_swap}}', ['to_emp_id', 'status']);
        $this->addForeignKey('fk-roster_swap-period', '{{%roster_swap}}', 'period_id', '{{%roster_period}}', 'id', 'CASCADE', 'CASCADE');
        // ไม่ผูก FK กับ roster_item เพราะใบเปลี่ยนตัวต้องอยู่เป็นหลักฐานแม้เวรถูกลบภายหลัง

        // ── สายอนุมัติ 3 ขั้น ──
        // ApproveLevelResolver สลับเลข org_node_level 1↔2 (UI ระดับ 1 = tree lvl 2 = หัวหน้างาน)
        // จึงต้องใส่ 1 สำหรับหัวหน้าหน่วย และ 2 สำหรับหัวหน้ากลุ่มงาน
        $this->delete('{{%approve_level_setting}}', ['system' => 'roster']);
        $now = date('Y-m-d H:i:s');
        $rows = [
            [1, 'เสนอ', ApproveLevelSetting::TYPE_ORG_LEADER1, null, 1],
            [2, 'ตรวจสอบ', ApproveLevelSetting::TYPE_ORG_LEADER1, null, 2],
            [3, 'อนุมัติ', ApproveLevelSetting::TYPE_DIRECTOR, null, null],
        ];
        foreach ($rows as [$level, $label, $type, $value, $orgLevel]) {
            $this->insert('{{%approve_level_setting}}', [
                'system' => 'roster',
                'level' => $level,
                'label' => $label,
                'title' => 'ตารางเวร',
                'approver_type' => $type,
                'approver_value' => $value,
                'org_node_level' => $orgLevel,
                'sort_order' => $level,
                'active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // รอบที่เคยอยู่สถานะ approved (จากโครงเดิม 2 ขั้น) ให้ถือว่าประกาศแล้ว
        $this->update('{{%roster_period}}', ['status' => 'published'], ['status' => 'approved']);
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%roster_swap}}');
        $this->dropColumn('{{%roster_period}}', 'reviewed_by');
        $this->dropColumn('{{%roster_period}}', 'reviewed_at');
        $this->delete('{{%approve_level_setting}}', ['system' => 'roster']);
    }
}
