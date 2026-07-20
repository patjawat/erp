<?php

use yii\db\Migration;

/**
 * อัตราค่าเสื่อมหลายช่วงต่อ 1 เกณฑ์ (เช่น 3 ปีแรก 5%, ปีต่อมา 3%)
 *
 * ข้อกำหนด:
 *  - ห้ามช่วงเดือนซ้อนกันใน profile เดียวกัน (บังคับใช้ระดับ service/validation)
 *  - start_month >= 1, end_month >= start_month (end_month = null หมายถึงเปิดปลายช่วง)
 *  - FK ไป depreciation_profiles แบบ RESTRICT (ห้ามลบเกณฑ์ที่ถูกใช้แล้ว)
 */
class m260712_100001_create_depreciation_profile_rates_table extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%depreciation_profile_rates}}', [
            'id' => $this->primaryKey(),
            'depreciation_profile_id' => $this->integer()->notNull()->comment('FK depreciation_profiles.id'),
            'start_month' => $this->integer()->notNull()->comment('เดือนเริ่มของช่วง (>=1)'),
            'end_month' => $this->integer()->null()->comment('เดือนสิ้นสุดของช่วง (null = เปิดปลายช่วง)'),
            'rate_percent' => $this->decimal(8, 4)->notNull()->comment('อัตราค่าเสื่อมของช่วง (%)'),
            'sequence' => $this->integer()->notNull()->defaultValue(0)->comment('ลำดับช่วง'),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
        ], $tableOptions);

        $this->createIndex('idx_dep_profile_rates_profile', '{{%depreciation_profile_rates}}', 'depreciation_profile_id');
        $this->createIndex('idx_dep_profile_rates_range', '{{%depreciation_profile_rates}}', ['depreciation_profile_id', 'start_month', 'end_month']);

        $this->addForeignKey(
            'fk_dep_profile_rates_profile',
            '{{%depreciation_profile_rates}}',
            'depreciation_profile_id',
            '{{%depreciation_profiles}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_dep_profile_rates_profile', '{{%depreciation_profile_rates}}');
        $this->dropTable('{{%depreciation_profile_rates}}');
    }
}
