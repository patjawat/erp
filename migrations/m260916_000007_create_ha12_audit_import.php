<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * HA12-PCT — เฟส 5 : audit trail + รองรับการนำเข้าข้อมูล (บท 11, 14-15)
 *
 *   - ha12_audit : ร่องรอยเหตุการณ์กำกับดูแล (สร้าง/เผยแพร่/ปิดรอบ/ลบ ฯลฯ)
 *   - เพิ่มคอลัมน์ source_system / source_ref ใน ha12_review เพื่อ traceability + idempotency
 *     ตอนนำเข้าข้อมูลเดิม (mapping กับ __recordId ของ Sheets เดิม / กันนำเข้าซ้ำ)
 */
final class m260916_000007_create_ha12_audit_import extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%ha12_audit}}', [
            'id' => $this->primaryKey(),
            'entity_type' => $this->string(32)->notNull()->comment('round|assessment|review|med|mrec|indicator|import'),
            'entity_id' => $this->integer()->null(),
            'action' => $this->string(32)->notNull()->comment('create|update|delete|restore|publish|unpublish|close|reopen|import'),
            'detail' => $this->string(500)->null(),
            'ref' => $this->string(64)->notNull(),
            'created_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
        ]);
        $this->createIndex('uq-ha12_audit-ref', '{{%ha12_audit}}', 'ref', true);
        $this->createIndex('idx-ha12_audit-entity', '{{%ha12_audit}}', ['entity_type', 'entity_id']);
        $this->createIndex('idx-ha12_audit-created', '{{%ha12_audit}}', 'created_at');

        // traceability ของการนำเข้า
        $this->addColumn('{{%ha12_review}}', 'source_system', $this->string(32)->null()->comment('แหล่งข้อมูลต้นทาง (เช่น gsheet, import)'));
        $this->addColumn('{{%ha12_review}}', 'source_ref', $this->string(128)->null()->comment('รหัสอ้างอิงต้นทาง (กันนำเข้าซ้ำ)'));
        $this->createIndex('idx-ha12_review-source', '{{%ha12_review}}', ['activity_id', 'source_ref']);
    }

    public function safeDown(): void
    {
        $this->dropIndex('idx-ha12_review-source', '{{%ha12_review}}');
        $this->dropColumn('{{%ha12_review}}', 'source_ref');
        $this->dropColumn('{{%ha12_review}}', 'source_system');
        $this->dropTable('{{%ha12_audit}}');
    }
}
