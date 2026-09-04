<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * สิทธิ์เข้าถึงเอกสาร SOP/WI ข้ามหน่วยงาน + ร่องรอยการส่งอนุมัติ
 *
 * ตาราง medsop_organization_access เก็บคู่ "หน่วยงานเจ้าของเอกสาร → หน่วยงานที่เข้าดูได้"
 * หัวหน้าหน่วยงานเจ้าของเอกสาร (tree.data_json.leader_1) เป็นผู้เปิดสิทธิ์ให้เอง
 * ไม่ต้องผ่านผู้ดูแลระบบ จึงต้องบันทึกว่าใครเป็นคนเปิดสิทธิ์ไว้ตรวจสอบย้อนหลัง
 *
 * คอลัมน์ submitted_* / review_note รองรับ flow ใหม่
 * ผู้จัดทำหน่วยงานส่งอนุมัติ (DRAFT → PENDING) แล้วผู้ดูแลระบบเผยแพร่หรือส่งกลับแก้ไข
 */
final class m260903_101000_create_medsop_organization_access extends Migration
{
    public function safeUp(): void
    {
        $options = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB'
            : null;

        $this->createTable('{{%medsop_organization_access}}', [
            'id' => $this->primaryKey(),
            'owner_organization_id' => $this->integer()->notNull()->comment('หน่วยงานเจ้าของเอกสาร'),
            'viewer_organization_id' => $this->integer()->notNull()->comment('หน่วยงานที่เข้าดูเอกสารได้'),
            'note' => $this->string(255)->null(),
            'created_by' => $this->integer()->null(),
            'created_at' => $this->dateTime()->null(),
        ], $options);

        $this->createIndex(
            'idx_medsop_org_access_pair',
            '{{%medsop_organization_access}}',
            ['owner_organization_id', 'viewer_organization_id'],
            true
        );
        $this->createIndex('idx_medsop_org_access_viewer', '{{%medsop_organization_access}}', 'viewer_organization_id');

        $this->addColumn('{{%medsop_document}}', 'submitted_by', $this->integer()->null()->after('published_at'));
        $this->addColumn('{{%medsop_document}}', 'submitted_at', $this->dateTime()->null()->after('submitted_by'));
        $this->addColumn('{{%medsop_document}}', 'review_note', $this->string(500)->null()->after('submitted_at'));
    }

    public function safeDown(): void
    {
        $this->dropColumn('{{%medsop_document}}', 'review_note');
        $this->dropColumn('{{%medsop_document}}', 'submitted_at');
        $this->dropColumn('{{%medsop_document}}', 'submitted_by');
        $this->dropTable('{{%medsop_organization_access}}');
    }
}
