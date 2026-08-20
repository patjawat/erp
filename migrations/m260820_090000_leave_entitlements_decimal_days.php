<?php

use yii\db\Migration;

/**
 * สิทธิลาพักผ่อนต้องเก็บทศนิยมได้ (เช่น 15.5 วัน)
 * - leave_on_year (สิทธิลาประจำปี) เดิมเป็น integer จึงปัดทศนิยมทิ้งทุกครั้งที่บันทึก
 * - days (รวมสิทธิลาพักผ่อน) เป็น float อยู่แล้ว ปรับเป็น double ให้ชนิดตรงกับ balance
 */
class m260820_090000_leave_entitlements_decimal_days extends Migration
{
    public function safeUp()
    {
        $table = Yii::$app->db->getTableSchema('{{%leave_entitlements}}', true);
        if ($table === null) {
            echo "ไม่พบตาราง leave_entitlements ข้ามการแก้ไข\n";
            return true;
        }

        if (isset($table->columns['leave_on_year'])) {
            $this->alterColumn(
                '{{%leave_entitlements}}',
                'leave_on_year',
                $this->double()->notNull()->defaultValue(0)->comment('วันที่ลาพักผ่อนประจำปี')
            );
        }

        if (isset($table->columns['days'])) {
            $this->alterColumn(
                '{{%leave_entitlements}}',
                'days',
                $this->double()->notNull()->defaultValue(0)->comment('วันที่ลาได้')
            );
        }

        return true;
    }

    public function safeDown()
    {
        $table = Yii::$app->db->getTableSchema('{{%leave_entitlements}}', true);
        if ($table === null) {
            return true;
        }

        if (isset($table->columns['leave_on_year'])) {
            $this->alterColumn(
                '{{%leave_entitlements}}',
                'leave_on_year',
                $this->integer()->notNull()->defaultValue(0)->comment('วันที่ลาพักผ่อนประจำปี')
            );
        }

        if (isset($table->columns['days'])) {
            $this->alterColumn(
                '{{%leave_entitlements}}',
                'days',
                $this->float()->notNull()->defaultValue(0)->comment('วันที่ลาได้')
            );
        }

        return true;
    }
}
