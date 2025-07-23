<?php

use yii\db\Migration;

class m250722_064946_hekpdesk_update_field extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
      $table = '{{%helpdesk}}';
        $schema = Yii::$app->db->getTableSchema($table, true);


                   if (!isset($schema->columns['repair_number'])) {
                $this->addColumn($table, 'repair_number', $this->string(100)->comment('รหัสงานซ่อม')->after('id'));
            }

            if (!isset($schema->columns['device_type_id'])) {
                $this->addColumn($table, 'device_type_id', $this->string(100)->comment('ประเภทอุปกรณ์')->after('repair_number'));
            }

            if (!isset($schema->columns['asset_number'])) {
                $this->addColumn($table, 'asset_number', $this->string(100)->comment('หมายเลขครุภัณฑ์')->after('device_type_id'));
            }

            if (!isset($schema->columns['request_repair_date'])) {
                $this->addColumn($table, 'request_repair_date', $this->date()->comment('วันที่ต้องการให้ซ่อม')->after('fsn_number'));
            }


            if (!isset($schema->columns['receive_date'])) {
                $this->addColumn($table, 'receive_date', $this->dateTime()->comment('วันที่ต้องการให้ซ่อม')->after('code'));
            }

               if (!isset($schema->columns['repair_type'])) {
                   $this->addColumn($table, 'repair_type', $this->string(100)->comment('ประเภทการซ่อม')->after('request_repair_date'));
                }
                
                if (!isset($schema->columns['repair_result'])) {
                    $this->addColumn($table, 'repair_result', $this->string(100)->comment('ผลการซ่อม (ซ่อมได้/ซ่อมไม่ได้)')->after('request_repair_date'));
                }





       if (!isset($schema->columns['title'])) {
                $this->addColumn($table, 'title', $this->string(100)->comment('ปัญหา')->after('request_repair_date'));
            }



        $HelpdeskUrgency = (new \yii\db\Query())->from('categorise')->where(['name' => 'helpdesk_urgency'])->count();
        if ($HelpdeskUrgency == 0) {
            // เพิ่มข้อมูลความเร่งด่วน
        Yii::$app->db->createCommand("
                INSERT INTO categorise (name, title, code, data_json) VALUES
                    ('helpdesk_urgency','ต่ำ', 'low', JSON_OBJECT('description', 'สามารถรอได้', 'color', 'light')),
                    ('helpdesk_urgency','ปานกลาง', 'medium', JSON_OBJECT('description', 'ควรซ่อมภายใน 3 วัน', 'color', 'info')),
                    ('helpdesk_urgency','สูง', 'high', JSON_OBJECT('description', 'ต้องซ่อมภายใน 24 ชั่วโมง', 'color', 'warning')),
                    ('helpdesk_urgency','วิกฤต', 'critical', JSON_OBJECT('description', 'ต้องซ่อมทันที', 'color', 'danger'))
            ")->execute();

        }

         $deviceType = (new \yii\db\Query())->from('categorise')->where(['name' => 'device_type'])->count();
        if ($deviceType == 0) {
            // เพิ่มข้อมูลความเร่งด่วน
          Yii::$app->db->createCommand("
                    INSERT INTO categorise (name, title, code, data_json) VALUES
                    ('device_type', 'คอมพิวเตอร์/โน๊ตบุ๊ค', 'computer', JSON_OBJECT('description', 'คอมพิวเตอร์/โน๊ตบุ๊ค')),
                    ('device_type', 'เครื่องพิมพ์/สแกนเนอร์', 'printer', JSON_OBJECT('description', 'เครื่องพิมพ์/สแกนเนอร์')),
                    ('device_type', 'อุปกรณ์เครือข่าย', 'network', JSON_OBJECT('description', 'อุปกรณ์เครือข่าย')),
                    ('device_type', 'เครื่องปรับอากาศ', 'ac', JSON_OBJECT('description', 'เครื่องปรับอากาศ')),
                    ('device_type', 'ระบบไฟฟ้า', 'electrical', JSON_OBJECT('description', 'ระบบไฟฟ้า')),
                    ('device_type', 'ระบบประปา', 'plumbing', JSON_OBJECT('description', 'ระบบประปา')),
                    ('device_type', 'เฟอร์นิเจอร์', 'furniture', JSON_OBJECT('description', 'เฟอร์นิเจอร์')),
                    ('device_type', 'อื่นๆ', 'other', JSON_OBJECT('description', 'อื่นๆ'))
                ")->execute();

        }

        Yii::$app->db->createCommand("UPDATE `categorise` SET name = 'repair_status_old' WHERE `name` = 'repair_status' ")->execute();
          $repairStatus = (new \yii\db\Query())->from('categorise')->where(['name' => 'repair_status'])->count();
            if ($repairStatus == 0) {
                Yii::$app->db->createCommand("
                    INSERT INTO categorise (name, title, code, sort, data_json) VALUES
                        ('repair_status', 'รอดำเนินการ', 'pending','1',JSON_OBJECT('color', 'secondary')),
                        ('repair_status', 'รับเรื่อง', 'receive','2',JSON_OBJECT('color', 'warning')),
                        ('repair_status', 'กำลังดำเนินการ', 'in_progress','3', JSON_OBJECT('color', 'primary')),
                        ('repair_status', 'เสร็จสิ้น', 'success','4', JSON_OBJECT('color', 'success')),
                        ('repair_status', 'ยกเลิก', 'cancel','0', JSON_OBJECT('color', 'danger'))
                ")->execute();
            }


    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        //     $table = '{{%helpdesk}}';
        // $schema = Yii::$app->db->getTableSchema($table, true);

        // if (isset($schema->columns['repair_number'])) {
        //     $this->dropColumn($table, 'repair_number');
        // }


        // if (isset($schema->columns['fsn_number'])) {
        //     $this->dropColumn($table, 'fsn_number');
        // }


        // if (isset($schema->columns['device_type_id'])) {
        //     $this->dropColumn($table, 'device_type_id');
        // }

        // if (isset($schema->columns['request_repair_date'])) {
        //     $this->dropColumn($table, 'request_repair_date');
        // }

    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250722_064946_hekpdesk_update_field cannot be reverted.\n";

        return false;
    }
    */
}
