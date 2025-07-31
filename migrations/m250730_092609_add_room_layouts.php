<?php

use yii\db\Migration;

class m250730_092609_add_room_layouts extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        //เพิ่มรูปแบบการจัดห้องประชุม
        $table = $this->db->getTableSchema('meeting', true);
        if ($table !== null && !isset($table->columns['room_layout_id'])) {
            $this->addColumn('meeting', 'room_layout_id', $this->string(255)->comment('รูปแบบการจัดห้อง')->after('room_id'));
        }
          $roomLayout = (new \yii\db\Query())->from('categorise')->where(['name' => 'room_layout'])->count();
            if ($roomLayout == 0) {
                Yii::$app->db->createCommand("
                INSERT INTO categorise (code, title, name, description) VALUES
                        ('classroom', 'แบบห้องเรียน', 'room_layout', 'เหมาะสำหรับการฝึกอบรมหรือการบรรยาย'),
                        ('theater', 'แบบโรงละคร', 'room_layout', 'เหมาะสำหรับการสัมมนา จำนวนผู้เข้าร่วมมาก'),
                        ('u_shape', 'แบบตัวยู', 'room_layout', 'เหมาะสำหรับการประชุมกลุ่มที่ต้องการปฏิสัมพันธ์'),
                        ('boardroom', 'แบบห้องประชุม', 'room_layout', 'เหมาะสำหรับการประชุมระดับบริหาร'),
                        ('banquet', 'แบบจัดเลี้ยง', 'room_layout', 'เหมาะสำหรับงานเลี้ยงหรือกิจกรรมสังสรรค์'),
                        ('cabaret', 'แบบกลุ่มกลม', 'room_layout', 'เหมาะสำหรับเวิร์กช็อป หรือการระดมความคิด'),
                        ('pods', 'แบบกลุ่มย่อย', 'room_layout', 'เหมาะสำหรับกิจกรรมกลุ่มย่อย 4-6 คน');
                ")->execute();
            }


        //เพิ่มสถานะของห้องประชุม

          $roomStatus = (new \yii\db\Query())->from('categorise')->where(['name' => 'room_status'])->count();
            if ($roomStatus == 0) {
                Yii::$app->db->createCommand("INSERT INTO categorise (code, title, name) VALUES
                            ('available', 'พร้อมใช้งาน', 'room_status'),
                            ('in_use', 'กำลังใช้งาน', 'room_status'),
                            ('maintenance', 'ปิดปรับปรุง', 'room_status'),
                            ('cleaning', 'กำลังทำความสะอาด', 'room_status'),
                            ('closed', 'ปิดให้บริการ', 'room_status'),
                            ('reserved_only', 'สงวนสิทธิ์เฉพาะ', 'room_status');")->execute();
            }
 

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // echo "m250730_092609_add_room_layouts cannot be reverted.\n";

        // return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250730_092609_add_room_layouts cannot be reverted.\n";

        return false;
    }
    */
}
