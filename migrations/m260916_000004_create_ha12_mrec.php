<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * HA12-PCT — เฟส 2b : ความสมบูรณ์ของเวชระเบียน (กิจกรรม 9)
 *
 *   - ha12_mrec_audit : การตรวจ 1 รอบ/ช่วง ต่อหน่วยงาน (จำนวนที่ตรวจ + ปัญหา + ผล)
 *   - ha12_mrec_item  : หัวข้อความครบถ้วน (seed 12 หัวข้อมาตรฐาน แก้ได้) + จำนวนที่ครบ → ร้อยละ
 */
final class m260916_000004_create_ha12_mrec extends Migration
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

        $this->createTable('{{%ha12_mrec_audit}}', array_merge([
            'id' => $this->primaryKey(),
            'owner_unit_id' => $this->bigInteger()->null()->comment('หน่วยงานเจ้าของ (tree.id)'),
            'fiscal_year' => $this->integer()->notNull(),
            'period_start' => $this->date()->null(),
            'period_end' => $this->date()->null(),
            'review_date' => $this->date()->null(),
            'total_charts' => $this->integer()->null()->comment('จำนวนเวชระเบียนที่ตรวจ (ตัวหารร้อยละ)'),
            'problem' => $this->text()->null()->comment('ปัญหาที่พบ'),
            'result' => $this->text()->null()->comment('ผล/การปรับปรุง'),
            'note' => $this->text()->null(),
            'deleted' => $this->tinyInteger(1)->notNull()->defaultValue(0),
        ], $audit()));
        $this->createIndex('uq-ha12_mrec_audit-ref', '{{%ha12_mrec_audit}}', 'ref', true);
        $this->createIndex('idx-ha12_mrec_audit-scope', '{{%ha12_mrec_audit}}', ['owner_unit_id', 'fiscal_year', 'deleted']);
        $this->addForeignKey('fk-ha12_mrec_audit-unit', '{{%ha12_mrec_audit}}', 'owner_unit_id', '{{%tree}}', 'id', 'SET NULL', 'CASCADE');

        $this->createTable('{{%ha12_mrec_item}}', array_merge([
            'id' => $this->primaryKey(),
            'audit_id' => $this->integer()->notNull(),
            'item_no' => $this->smallInteger()->notNull(),
            'item_name' => $this->string(255)->notNull(),
            'is_other' => $this->tinyInteger(1)->notNull()->defaultValue(0),
            'complete_count' => $this->integer()->null()->comment('จำนวนที่ครบถ้วน (null=ยังไม่ตรวจ)'),
            'note' => $this->string(500)->null(),
            'sort' => $this->integer()->notNull()->defaultValue(0),
        ], $audit()));
        $this->createIndex('uq-ha12_mrec_item-ref', '{{%ha12_mrec_item}}', 'ref', true);
        $this->createIndex('idx-ha12_mrec_item-audit', '{{%ha12_mrec_item}}', ['audit_id', 'sort']);
        $this->addForeignKey('fk-ha12_mrec_item-audit', '{{%ha12_mrec_item}}', 'audit_id', '{{%ha12_mrec_audit}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%ha12_mrec_item}}');
        $this->dropTable('{{%ha12_mrec_audit}}');
    }
}
