<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * สรุปผลการประชุม/อบรม/ดูงาน ของใบขออนุญาตไปราชการแต่ละใบ
 *
 * เก็บแยกตาราง (ไม่ยัดลง development.data_json) เพราะ
 *   - ทะเบียนต้องกรองว่าใบไหนสรุปแล้ว/ยังไม่สรุป
 *   - มีสถานะและวันที่ของตัวเอง แยกจากสถานะอนุมัติของใบไปราชการ
 *
 * ผู้รับทราบไม่เก็บที่นี่ ใช้ตาราง approve กลางของระบบ
 * (name = 'development_summary', from_id = development_id) เหมือนที่โมดูลอื่นทำ
 */
final class m260810_000001_create_development_summary extends Migration
{
    public function safeUp(): void
    {
        $options = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB'
            : null;

        $this->createTable('{{%development_summary}}', [
            'id' => $this->primaryKey(),
            'development_id' => $this->integer()->notNull()->comment('ใบขออนุญาตไปราชการที่สรุปผลนี้ผูกอยู่'),
            'status' => $this->string(20)->notNull()->defaultValue('draft')->comment('draft = ยังไม่ส่ง, submitted = ส่งแล้วรอรับทราบ, acknowledged = ผู้รับทราบครบทุกคน'),
            'content' => $this->text()->null()->comment('สรุปเนื้อหา/สาระสำคัญที่ได้รับ'),
            'benefit' => $this->text()->null()->comment('การนำไปใช้ประโยชน์ต่อหน่วยงาน'),
            'suggestion' => $this->text()->null()->comment('ข้อเสนอแนะต่อหน่วยงาน'),
            'ref' => $this->string(255)->null()->comment('token ของ filemanager สำหรับไฟล์แนบ'),
            'data_json' => $this->json()->null()->comment('ช่องเพิ่มเติมภายหลัง ไม่ต้อง migrate ซ้ำ'),
            'submitted_at' => $this->dateTime()->null(),
            'submitted_by' => $this->integer()->null()->comment('user id ที่กดส่งให้รับทราบ'),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
            'deleted_at' => $this->dateTime()->null(),
            'deleted_by' => $this->integer()->null(),
        ], $options);

        // 1 ใบไปราชการมีสรุปได้ใบเดียว
        $this->createIndex('uq-development_summary-development', '{{%development_summary}}', 'development_id', true);
        $this->createIndex('idx-development_summary-status', '{{%development_summary}}', 'status');
        $this->addForeignKey('fk-development_summary-development', '{{%development_summary}}', 'development_id', '{{%development}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%development_summary}}');
    }
}
