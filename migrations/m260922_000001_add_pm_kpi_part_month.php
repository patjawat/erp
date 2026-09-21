<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * เพิ่ม HA Part (ตอน I-IV) + ข้อมูลรายเดือน + คอลัมน์กลุ่ม/ตัวชี้วัด สำหรับ KPI โรงพยาบาล
 * - pm_kpi_ha_part : ทะเบียนตอน HA (Part เดียวต่อตัวชี้วัด)
 * - pm_kpi_indicator : + ha_part_id, aggregation (วิธีสรุปค่ารายปีจากรายเดือน)
 * - pm_kpi_group : + name_en, description
 * - pm_kpi_indicator_month : ค่ารายเดือน (ต.ค.→ก.ย.) ต่อปีงบ
 */
final class m260922_000001_add_pm_kpi_part_month extends Migration
{
    public function safeUp(): void
    {
        $options = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB'
            : null;
        $audit = [
            'ref' => $this->string(64)->notNull()->unique(),
            'created_at' => $this->dateTime()->null(), 'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(), 'updated_by' => $this->integer()->null(),
        ];

        // ทะเบียนตอน HA
        $this->createTable('{{%pm_kpi_ha_part}}', array_merge([
            'id' => $this->primaryKey(),
            'code' => $this->string(20)->notNull(),
            'name' => $this->string(255)->notNull(),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'is_active' => $this->boolean()->notNull()->defaultValue(true),
        ], $audit), $options);
        $this->createIndex('uq-pm_kpi_ha_part-code', '{{%pm_kpi_ha_part}}', 'code', true);

        // seed ตอน I-IV (จากภาพผู้ใช้)
        $now = date('Y-m-d H:i:s');
        $parts = [
            ['I', 'ตอนที่ I: ภาพรวมของการบริหารองค์กร', 1],
            ['II', 'ตอนที่ II: ระบบงานสำคัญของโรงพยาบาล', 2],
            ['III', 'ตอนที่ III: กระบวนการดูแลผู้ป่วย', 3],
            ['IV', 'ตอนที่ IV: ผลลัพธ์', 4],
        ];
        foreach ($parts as [$code, $name, $sort]) {
            $this->insert('{{%pm_kpi_ha_part}}', [
                'ref' => substr(Yii::$app->security->generateRandomString(40), 0, 32),
                'code' => $code, 'name' => $name, 'sort_order' => $sort, 'is_active' => 1,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        // ตัวชี้วัด + Part + วิธีสรุปค่ารายปี
        $this->addColumn('{{%pm_kpi_indicator}}', 'ha_part_id', $this->integer()->null()->after('group_id'));
        $this->addColumn('{{%pm_kpi_indicator}}', 'aggregation', $this->string(10)->notNull()->defaultValue('avg')->after('operator'));
        $this->createIndex('idx-pm_kpi_indicator-ha_part', '{{%pm_kpi_indicator}}', 'ha_part_id');
        $this->addForeignKey('fk-pm_kpi_indicator-ha_part', '{{%pm_kpi_indicator}}', 'ha_part_id', '{{%pm_kpi_ha_part}}', 'id', 'SET NULL', 'CASCADE');

        // กลุ่ม + ชื่อ EN/คำอธิบาย
        $this->addColumn('{{%pm_kpi_group}}', 'name_en', $this->string(255)->null()->after('name'));
        $this->addColumn('{{%pm_kpi_group}}', 'description', $this->text()->null()->after('name_en'));

        // ค่ารายเดือน (ต่อ pm_kpi_indicator_year)
        $this->createTable('{{%pm_kpi_indicator_month}}', array_merge([
            'id' => $this->primaryKey(),
            'kpi_indicator_year_id' => $this->integer()->notNull(),
            'month' => $this->integer()->notNull(),
            'value' => $this->decimal(18, 4)->null(),
            'numerator' => $this->decimal(18, 4)->null(),
            'denominator' => $this->decimal(18, 4)->null(),
            'note' => $this->text()->null(),
        ], $audit), $options);
        $this->createIndex('uq-pm_kpi_indicator_month-ym', '{{%pm_kpi_indicator_month}}', ['kpi_indicator_year_id', 'month'], true);
        $this->addForeignKey('fk-pm_kpi_indicator_month-year', '{{%pm_kpi_indicator_month}}', 'kpi_indicator_year_id', '{{%pm_kpi_indicator_year}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%pm_kpi_indicator_month}}');
        $this->dropColumn('{{%pm_kpi_group}}', 'description');
        $this->dropColumn('{{%pm_kpi_group}}', 'name_en');
        $this->dropForeignKey('fk-pm_kpi_indicator-ha_part', '{{%pm_kpi_indicator}}');
        $this->dropColumn('{{%pm_kpi_indicator}}', 'aggregation');
        $this->dropColumn('{{%pm_kpi_indicator}}', 'ha_part_id');
        $this->dropTable('{{%pm_kpi_ha_part}}');
    }
}
