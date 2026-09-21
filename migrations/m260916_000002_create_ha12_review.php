<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * HA12-PCT — เฟส 1 : การทบทวน (กิจกรรมทั่วไป)
 *
 * แยก "การทบทวนจริง" ออกเป็น 3 ตาราง ตามหลักการในคู่มือ (บท 11):
 *   - ha12_review          : รายการทบทวน (สถานะล่าสุด) เจ้าของ = หน่วยงาน (tree.id)
 *   - ha12_review_version  : ประวัติทุกรุ่น (id คงที่ + revision เพิ่มทุกครั้ง) — audit ที่ตรวจย้อนได้
 *   - ha12_review_followup : ผลติดตามหลายครั้งต่อ 1 รายการ (วัน/ผลที่พบ/หลักฐาน)
 *
 * รายละเอียดแบบฟอร์มที่ต่างกันรายกิจกรรมเก็บใน payload_json + schema_version
 * (ฟิลด์กลางที่ต้องค้น/แสดง = แยกเป็นคอลัมน์: activity_id/owner_unit_id/fiscal_year/review_date/title)
 */
final class m260916_000002_create_ha12_review extends Migration
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

        // 1) รายการทบทวน (สถานะล่าสุด) -----------------------------------------
        $this->createTable('{{%ha12_review}}', array_merge([
            'id' => $this->primaryKey(),
            'activity_id' => $this->integer()->notNull()->comment('กิจกรรม (ha12_activity)'),
            'owner_unit_id' => $this->bigInteger()->null()->comment('หน่วยงานเจ้าของ (tree.id)'),
            'fiscal_year' => $this->integer()->notNull()->comment('ปีงบประมาณ (พ.ศ.)'),
            'review_date' => $this->date()->null()->comment('วันที่ทบทวน'),
            'title' => $this->string(500)->null()->comment('หัวข้อย่อ (derive จากฟิลด์หลัก) ใช้แสดงในรายการ'),
            'reviewer_name' => $this->string(255)->null()->comment('ผู้ทบทวน'),
            'payload_json' => $this->text()->null()->comment('รายละเอียดตามแบบฟอร์มของกิจกรรม (JSON)'),
            'schema_version' => $this->integer()->notNull()->defaultValue(1),
            'revision' => $this->integer()->notNull()->defaultValue(1)->comment('เลขรุ่นล่าสุด'),
            'deleted' => $this->tinyInteger(1)->notNull()->defaultValue(0)->comment('ลบแบบกู้คืนได้'),
        ], $audit()));
        $this->createIndex('uq-ha12_review-ref', '{{%ha12_review}}', 'ref', true);
        $this->createIndex('idx-ha12_review-scope', '{{%ha12_review}}', ['activity_id', 'owner_unit_id', 'fiscal_year', 'deleted']);
        $this->createIndex('idx-ha12_review-date', '{{%ha12_review}}', 'review_date');
        $this->addForeignKey('fk-ha12_review-activity', '{{%ha12_review}}', 'activity_id', '{{%ha12_activity}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk-ha12_review-unit', '{{%ha12_review}}', 'owner_unit_id', '{{%tree}}', 'id', 'SET NULL', 'CASCADE');

        // 2) ประวัติทุกรุ่น -----------------------------------------------------
        $this->createTable('{{%ha12_review_version}}', array_merge([
            'id' => $this->primaryKey(),
            'review_id' => $this->integer()->notNull(),
            'revision' => $this->integer()->notNull(),
            'action' => $this->string(16)->notNull()->defaultValue('update')->comment('create|update|delete|restore'),
            'review_date' => $this->date()->null(),
            'title' => $this->string(500)->null(),
            'reviewer_name' => $this->string(255)->null(),
            'payload_json' => $this->text()->null(),
            'schema_version' => $this->integer()->notNull()->defaultValue(1),
            'deleted' => $this->tinyInteger(1)->notNull()->defaultValue(0),
        ], $audit()));
        $this->createIndex('uq-ha12_review_version-ref', '{{%ha12_review_version}}', 'ref', true);
        $this->createIndex('uq-ha12_review_version-rev', '{{%ha12_review_version}}', ['review_id', 'revision'], true);
        $this->addForeignKey('fk-ha12_review_version-review', '{{%ha12_review_version}}', 'review_id', '{{%ha12_review}}', 'id', 'CASCADE', 'CASCADE');

        // 3) ผลติดตาม (หลายครั้งต่อรายการ) --------------------------------------
        $this->createTable('{{%ha12_review_followup}}', array_merge([
            'id' => $this->primaryKey(),
            'review_id' => $this->integer()->notNull(),
            'followup_date' => $this->date()->null()->comment('วันที่ติดตาม'),
            'finding' => $this->text()->null()->comment('ผลที่พบ'),
            'evidence' => $this->text()->null()->comment('หลักฐาน (ข้อความ; ไฟล์แนบเฟสถัดไป)'),
        ], $audit()));
        $this->createIndex('uq-ha12_review_followup-ref', '{{%ha12_review_followup}}', 'ref', true);
        $this->createIndex('idx-ha12_review_followup-review', '{{%ha12_review_followup}}', ['review_id', 'followup_date']);
        $this->addForeignKey('fk-ha12_review_followup-review', '{{%ha12_review_followup}}', 'review_id', '{{%ha12_review}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%ha12_review_followup}}');
        $this->dropTable('{{%ha12_review_version}}');
        $this->dropTable('{{%ha12_review}}');
    }
}
