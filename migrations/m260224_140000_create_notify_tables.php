<?php

use yii\db\Migration;

/**
 * สร้างตารางสำหรับโมดูล Notify (แจ้งเตือน)
 * ใช้เก็บการแจ้งเตือนให้ผู้รับทราบ เช่น การขออนุมัติลา จัดซื้อ ลงเวลาเข้างาน
 */
class m260224_140000_create_notify_tables extends Migration
{
    public function safeUp()
    {
        $tableOptions = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB'
            : null;

        $this->createTable('{{%notify}}', [
            'id' => $this->primaryKey(),
            'type' => $this->string(64)->notNull()->comment('ประเภท: leave_approve, purchase_approve, checkin_approve, vehicle_approve, stock_approve, development_approve, asset_move_approve'),
            'title' => $this->string(255)->notNull()->comment('หัวข้อแจ้งเตือน'),
            'message' => $this->text()->null()->comment('ข้อความเพิ่มเติม'),
            'ref_type' => $this->string(64)->null()->comment('ตารางอ้างอิง: approve, leave, order, checkin_record ฯลฯ'),
            'ref_id' => $this->string(64)->null()->comment('รหัสอ้างอิง เช่น from_id หรือ id ของรายการ'),
            'recipient_emp_id' => $this->integer()->notNull()->comment('พนักงานผู้รับการแจ้งเตือน'),
            'read_at' => $this->datetime()->null()->comment('เวลาที่อ่านแล้ว null = ยังไม่อ่าน'),
            'created_at' => $this->datetime()->notNull(),
            'data_json' => $this->text()->null()->comment('ข้อมูลเพิ่มเติม JSON'),
        ], $tableOptions);

        $this->createIndex('idx-notify-recipient_emp_id', '{{%notify}}', 'recipient_emp_id');
        $this->createIndex('idx-notify-read_at', '{{%notify}}', 'read_at');
        $this->createIndex('idx-notify-type', '{{%notify}}', 'type');
        $this->createIndex('idx-notify-created_at', '{{%notify}}', 'created_at');
        $this->createIndex('idx-notify-ref', '{{%notify}}', ['ref_type', 'ref_id']);

        $this->addForeignKey(
            'fk-notify-recipient_emp',
            '{{%notify}}',
            'recipient_emp_id',
            '{{%employees}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-notify-recipient_emp', '{{%notify}}');
        $this->dropTable('{{%notify}}');
    }
}
