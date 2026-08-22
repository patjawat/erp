<?php

use yii\db\Migration;

/**
 * ปรับตารางกรอบให้เก็บได้ทั้งระดับโรงพยาบาลและระดับหน่วยงาน + เพิ่มสถานะรอบรายปี
 *
 * เดิมคีย์เป็น (ปี, หน่วยงาน, ตำแหน่ง) ซึ่งใช้ไม่ได้จริง เพราะ
 *  - กรอบตามเกณฑ์กำหนดที่ "สายงาน" ไม่ใช่ "ตำแหน่งของโรงพยาบาล"
 *  - กรอบจากสูตรเป็นตัวเลขระดับโรงพยาบาล ยังไม่มีหน่วยงานเจ้าของ
 *    และผังไม่มี org_unit ระดับ "ทั้งโรงพยาบาล" ให้อ้าง
 *
 * จึงเปลี่ยนเป็น (ปี, หน่วยงานหรือทั้งโรงพยาบาล, สายงาน)
 *
 * MySQL ปล่อยให้ NULL ซ้ำได้ใน unique index จึงต้องห่อ org_unit_id ด้วย IFNULL
 * ใช้ functional index (MySQL 8.0.13+) ไม่ใช่ generated column แบบ STORED
 * เพราะ STORED บังคับให้ MySQL สร้างตารางใหม่แล้วผูก foreign key ซ้ำ ซึ่งล้มเหลว
 */
class m260819_100000_workforce_frame_round extends Migration
{
    public function safeUp()
    {
        // ── คีย์ใหม่ของตารางกรอบ ──
        $this->dropIndex('uq-wf-frame-row', '{{%workforce_frame}}');
        $this->dropForeignKey('fk-wf-frame-org-unit', '{{%workforce_frame}}');

        $this->alterColumn('{{%workforce_frame}}', 'org_unit_id', $this->bigInteger()->null()
            ->comment('หน่วยงาน (NULL = กรอบระดับทั้งโรงพยาบาล)'));
        $this->addForeignKey('fk-wf-frame-org-unit', '{{%workforce_frame}}', 'org_unit_id', '{{%org_unit}}', 'id', 'CASCADE', 'CASCADE');

        $this->addColumn('{{%workforce_frame}}', 'scope', $this->string(10)->notNull()->defaultValue('hospital')
            ->after('thai_year')->comment('hospital = ทั้งโรงพยาบาล, unit = รายหน่วยงาน'));

        $this->execute('CREATE UNIQUE INDEX `uq-wf-frame-line`
            ON {{%workforce_frame}} (`thai_year`, (IFNULL(`org_unit_id`, 0)), `line_id`)');

        // ── สถานะรอบจัดทำกรอบ เก็บที่โปรไฟล์ของปี เพราะ 1 ปี = 1 รอบ ──
        $this->addColumn('{{%workforce_profile}}', 'status', $this->string(20)->notNull()->defaultValue('draft')
            ->comment('draft|submitted|approved|closed'));
        $this->addColumn('{{%workforce_profile}}', 'submitted_at', $this->dateTime()->null());
        $this->addColumn('{{%workforce_profile}}', 'submitted_by', $this->integer()->null());
        $this->addColumn('{{%workforce_profile}}', 'approved_at', $this->dateTime()->null());
        $this->addColumn('{{%workforce_profile}}', 'approved_by', $this->integer()->null());
        $this->addColumn('{{%workforce_profile}}', 'approval_note', $this->text()->null());
    }

    public function safeDown()
    {
        foreach (['approval_note', 'approved_by', 'approved_at', 'submitted_by', 'submitted_at', 'status'] as $column) {
            $this->dropColumn('{{%workforce_profile}}', $column);
        }

        $this->dropIndex('uq-wf-frame-line', '{{%workforce_frame}}');
        $this->dropColumn('{{%workforce_frame}}', 'scope');

        $this->dropForeignKey('fk-wf-frame-org-unit', '{{%workforce_frame}}');
        $this->delete('{{%workforce_frame}}', ['org_unit_id' => null]);
        $this->alterColumn('{{%workforce_frame}}', 'org_unit_id', $this->bigInteger()->notNull());
        $this->addForeignKey('fk-wf-frame-org-unit', '{{%workforce_frame}}', 'org_unit_id', '{{%org_unit}}', 'id', 'CASCADE', 'CASCADE');
        $this->createIndex('uq-wf-frame-row', '{{%workforce_frame}}', ['thai_year', 'org_unit_id', 'employee_position_id'], true);
    }
}
