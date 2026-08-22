<?php

use yii\db\Migration;

/**
 * ซ่อม data_json ของทะเบียนหน่วยงานที่ถูกเข้ารหัส JSON ซ้ำสองชั้น
 *
 * คอลัมน์เป็นชนิด json อยู่แล้ว แต่โมเดลเดิม json_encode ก่อนบันทึก แล้ว Yii เข้ารหัสให้อีกชั้น
 * ค่าที่ได้จึงเป็น JSON string ('"{\"team_group_id\":1}"') ไม่ใช่ object ทำให้ JSON_EXTRACT หาไม่เจอ
 * ผลคือการนำเข้าทีมประสานซ้ำรอบที่สองจะไม่รู้ว่ามีอยู่แล้ว และสร้างแถวซ้ำ
 *
 * แก้เฉพาะแถวที่ยังเป็น STRING และ unquote แล้วเป็น JSON object ที่ถูกต้อง — รันซ้ำได้
 */
class m260818_090000_fix_org_unit_data_json extends Migration
{
    public function safeUp()
    {
        $fixed = $this->db->createCommand("
            UPDATE org_unit
               SET data_json = CAST(JSON_UNQUOTE(data_json) AS JSON)
             WHERE data_json IS NOT NULL
               AND JSON_TYPE(data_json) = 'STRING'
               AND JSON_VALID(JSON_UNQUOTE(data_json))
               AND JSON_TYPE(CAST(JSON_UNQUOTE(data_json) AS JSON)) = 'OBJECT'
        ")->execute();

        echo "    > ซ่อม org_unit.data_json {$fixed} แถว\n";
    }

    public function safeDown()
    {
        echo "    > ไม่ย้อนกลับ — การเข้ารหัสซ้ำเป็นข้อผิดพลาด ไม่ใช่รูปแบบที่ต้องรักษาไว้\n";
        return true;
    }
}
