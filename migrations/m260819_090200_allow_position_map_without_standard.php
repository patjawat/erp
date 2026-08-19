<?php

use yii\db\Migration;

/**
 * ให้จับคู่ตำแหน่งเป็น "ไม่มีในเกณฑ์" ได้
 *
 * ตำแหน่งจำนวนมากของโรงพยาบาลไม่มีสายงานตรงกันในเกณฑ์ สป.สธ.
 * (พนักงานช่วยเหลือคนไข้ พนักงานเปล พนักงานบริการ พนักงานประจำห้องยา ฯลฯ)
 * ถ้าไม่มีทางบันทึกข้อสรุปนี้ รายการจะค้างเป็น "ยังไม่จับคู่" ตลอดไป
 * แล้ว HR จะไล่ตรวจซ้ำทุกปีโดยไม่จำเป็น
 *
 * line_id = NULL หมายถึง ยืนยันแล้วว่าไม่มีสายงานตรงในเกณฑ์
 */
class m260819_090200_allow_position_map_without_standard extends Migration
{
    public function safeUp()
    {
        $this->dropForeignKey('fk-wf-map-line', '{{%workforce_position_map}}');
        $this->alterColumn('{{%workforce_position_map}}', 'line_id', $this->integer()->null()
            ->comment('สายงานมาตรฐาน (NULL = ยืนยันแล้วว่าไม่มีในเกณฑ์)'));
        $this->addForeignKey('fk-wf-map-line', '{{%workforce_position_map}}', 'line_id', '{{%workforce_standard_line}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown()
    {
        $this->delete('{{%workforce_position_map}}', ['line_id' => null]);
        $this->dropForeignKey('fk-wf-map-line', '{{%workforce_position_map}}');
        $this->alterColumn('{{%workforce_position_map}}', 'line_id', $this->integer()->notNull());
        $this->addForeignKey('fk-wf-map-line', '{{%workforce_position_map}}', 'line_id', '{{%workforce_standard_line}}', 'id', 'CASCADE', 'CASCADE');
    }
}
