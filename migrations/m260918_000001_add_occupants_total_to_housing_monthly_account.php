<?php

use yii\db\Migration;

/**
 * เพิ่มคอลัมน์ occupants_total (จำนวนผู้พักอาศัยรวมทั้งหมด) ในทะเบียนค่าใช้จ่ายประจำเดือนบ้านพัก
 * ของเดิมมีเฉพาะ occupants_over_15 (ผู้พักเกิน 15 ปี สำหรับคิดค่าใช้จ่ายรายหัว)
 */
final class m260918_000001_add_occupants_total_to_housing_monthly_account extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn(
            '{{%housing_monthly_account}}',
            'occupants_total',
            $this->integer()->notNull()->defaultValue(0)->after('occupants_over_15')
        );
        // ตั้งค่าเริ่มต้นให้ข้อมูลเก่า: อย่างน้อยเท่ากับจำนวนผู้พักเกิน 15 ปีที่บันทึกไว้
        $this->update(
            '{{%housing_monthly_account}}',
            ['occupants_total' => new \yii\db\Expression('occupants_over_15')],
            ['and', ['occupants_total' => 0], ['>', 'occupants_over_15', 0]]
        );
    }

    public function safeDown(): void
    {
        $this->dropColumn('{{%housing_monthly_account}}', 'occupants_total');
    }
}
