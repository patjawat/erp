<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * ทะเบียนตัวชี้วัด (KPI) ระดับโรงพยาบาลนอกแผนยุทธศาสตร์ — โมดูล pm
 *
 * ต่อยอดจากโมดูลยุทธศาสตร์: ตัวชี้วัดยุทธศาสตร์ (กลุ่ม 1) ใช้ pm_strategy_indicator เดิม
 * ส่วนกลุ่ม 2-5 (รพ.สำหรับ Monitor / คุณภาพทีมประสาน / งานพยาบาล / หน่วยงาน) เก็บในตารางชุดนี้
 *
 * หมายเหตุ: HA Part + mapping (pm_kpi_ha_part / pm_kpi_ha_map) เป็นงานเฟสหลัง ไม่อยู่ในไฟล์นี้
 */
final class m260921_000001_create_pm_kpi_tables extends Migration
{
    public function safeUp(): void
    {
        $options = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB'
            : null;

        $audit = [
            'ref' => $this->string(64)->notNull()->unique(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ];

        // กลุ่มตัวชี้วัด — kind=strategy ชี้ว่าดึงจาก pm_strategy_indicator, standalone ใช้ pm_kpi_indicator
        $this->createTable('{{%pm_kpi_group}}', array_merge([
            'id' => $this->primaryKey(),
            'code' => $this->string(50)->notNull(),
            'name' => $this->string(255)->notNull(),
            'kind' => $this->string(20)->notNull()->defaultValue('standalone'),
            'icon' => $this->string(50)->null(),
            'color' => $this->string(20)->null(),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'is_active' => $this->boolean()->notNull()->defaultValue(true),
        ], $audit), $options);
        $this->createIndex('uq-pm_kpi_group-code', '{{%pm_kpi_group}}', 'code', true);

        // ตัวชี้วัดนอกแผน (ตัวแม่ คงที่ข้ามปี)
        $this->createTable('{{%pm_kpi_indicator}}', array_merge([
            'id' => $this->primaryKey(),
            'group_id' => $this->integer()->notNull(),
            'org_unit_id' => $this->integer()->null(),
            'name' => $this->text()->notNull(),
            'unit' => $this->string(100)->null(),
            'operator' => $this->string(10)->null(),           // ทิศทาง: >=,<=,=,>,<
            'definition' => $this->text()->null(),
            'formula' => $this->text()->null(),
            'evaluation_method' => $this->text()->null(),
            'data_source' => $this->text()->null(),
            'owner_name' => $this->string(150)->null(),
            'frequency' => $this->string(20)->null(),           // month/quarter/year
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'is_active' => $this->boolean()->notNull()->defaultValue(true),
        ], $audit), $options);
        $this->createIndex('idx-pm_kpi_indicator-group', '{{%pm_kpi_indicator}}', 'group_id');
        $this->createIndex('idx-pm_kpi_indicator-org_unit', '{{%pm_kpi_indicator}}', 'org_unit_id');
        $this->addForeignKey('fk-pm_kpi_indicator-group', '{{%pm_kpi_indicator}}', 'group_id', '{{%pm_kpi_group}}', 'id', 'CASCADE', 'CASCADE');

        // ค่ารายปีงบประมาณ (เป้า + ผลจริง + สถานะที่คำนวณไว้)
        $this->createTable('{{%pm_kpi_indicator_year}}', array_merge([
            'id' => $this->primaryKey(),
            'kpi_indicator_id' => $this->integer()->notNull(),
            'fiscal_year' => $this->integer()->notNull(),
            'target_value' => $this->decimal(18, 4)->null(),
            'actual_value' => $this->decimal(18, 4)->null(),
            'status' => $this->string(20)->null(),              // pass/gap/nodata (คำนวณตอนบันทึก)
            'note' => $this->text()->null(),
        ], $audit), $options);
        $this->createIndex('idx-pm_kpi_indicator_year-indicator', '{{%pm_kpi_indicator_year}}', 'kpi_indicator_id');
        $this->createIndex('uq-pm_kpi_indicator_year-year', '{{%pm_kpi_indicator_year}}', ['kpi_indicator_id', 'fiscal_year'], true);
        $this->createIndex('idx-pm_kpi_indicator_year-year-status', '{{%pm_kpi_indicator_year}}', ['fiscal_year', 'status']);
        $this->addForeignKey('fk-pm_kpi_indicator_year-indicator', '{{%pm_kpi_indicator_year}}', 'kpi_indicator_id', '{{%pm_kpi_indicator}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%pm_kpi_indicator_year}}');
        $this->dropTable('{{%pm_kpi_indicator}}');
        $this->dropTable('{{%pm_kpi_group}}');
    }
}
