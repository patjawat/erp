<?php

use yii\db\Migration;

/**
 * ทะเบียนประวัติการแก้ไขใบลา
 * เก็บทุกครั้งที่ผู้ดูแลระบบลาแก้ไขใบลา (โดยเฉพาะการเปลี่ยนวันที่)
 * แม้ใบลาจะได้รับอนุมัติแล้ว
 */
class m260915_000001_create_leave_edit_history_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%leave_edit_history}}', [
            'id' => $this->primaryKey(),
            'leave_id' => $this->integer()->notNull()->comment('รหัสใบลา'),
            'changes' => $this->json()->comment('รายการฟิลด์ที่เปลี่ยน {field:{old,new}}'),
            'status_snapshot' => $this->string(255)->comment('สถานะใบลาขณะแก้ไข'),
            'note' => $this->text()->comment('หมายเหตุการแก้ไข'),
            'edited_by' => $this->integer()->comment('ผู้แก้ไข'),
            'edited_at' => $this->dateTime()->comment('วันที่แก้ไข'),
        ]);

        $this->createIndex(
            'idx-leave_edit_history-leave_id',
            '{{%leave_edit_history}}',
            'leave_id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%leave_edit_history}}');
    }
}
