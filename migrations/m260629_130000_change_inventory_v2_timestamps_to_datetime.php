<?php

use yii\db\Migration;

/**
 * เปลี่ยน created_at / updated_at ใน V2 inventory จาก INT Unix timestamp เป็น DATETIME
 * เพื่อให้รองรับรูปแบบเดียวกับ V1 stock_events ตอนโอนข้อมูลประวัติ
 */
class m260629_130000_change_inventory_v2_timestamps_to_datetime extends Migration
{
    /**
     * @var array<string, string[]> map table => fields ที่จะแปลง
     */
    private $targets = [
        '{{%stock_item}}' => ['created_at', 'updated_at'],
        '{{%stock_order}}' => ['created_at', 'updated_at'],
        '{{%stock_detail}}' => ['created_at', 'updated_at'],
        '{{%stock_balance}}' => ['created_at', 'updated_at'],
        '{{%stock_monthly_report}}' => ['created_at'],
    ];

    public function safeUp()
    {
        foreach ($this->targets as $table => $fields) {
            foreach ($fields as $col) {
                $tmp = '_' . $col . '_dt';
                $this->addColumn($table, $tmp, $this->dateTime()->null());
                $this->execute("UPDATE $table SET `$tmp` = FROM_UNIXTIME(`$col`) WHERE `$col` IS NOT NULL AND `$col` > 0");
                $this->dropColumn($table, $col);
                $this->renameColumn($table, $tmp, $col);
            }
        }

        return true;
    }

    public function safeDown()
    {
        foreach ($this->targets as $table => $fields) {
            foreach ($fields as $col) {
                $tmp = '_' . $col . '_int';
                $this->addColumn($table, $tmp, $this->integer()->null());
                $this->execute("UPDATE $table SET `$tmp` = UNIX_TIMESTAMP(`$col`) WHERE `$col` IS NOT NULL");
                $this->dropColumn($table, $col);
                $this->renameColumn($table, $tmp, $col);
            }
        }

        return true;
    }
}
