<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * นิยามตัวชี้วัดตามแบบฟอร์ม KPI Template — เก็บที่ชั้นรายปีเพื่อให้ปรับนิยามได้ปีต่อปี
 * พร้อมตารางลูก: เกณฑ์คะแนน 5 ระดับ, รอบการประเมิน, ผลงานรายเดือน และข้อมูลพื้นฐานย้อนหลัง
 */
final class m260808_000002_add_kpi_template_to_indicator_year extends Migration
{
    private const PARENT = '{{%pm_strategy_indicator_year}}';

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

        foreach ([
            'owner_team' => $this->string(150)->null(),
            'target_population' => $this->text()->null(),
            'definition' => $this->text()->null(),
            'formula' => $this->text()->null(),
            'evaluation_method' => $this->text()->null(),
            'data_source' => $this->text()->null(),
            'baseline_label' => $this->string(255)->null(),
            'baseline_average' => $this->decimal(18, 4)->null(),
            'supervisor_name' => $this->string(150)->null(),
            'supervisor_phone' => $this->string(50)->null(),
            'owner_name' => $this->string(150)->null(),
            'owner_phone' => $this->string(50)->null(),
        ] as $column => $type) {
            $this->addColumn(self::PARENT, $column, $type);
        }

        // สูตรคำนวณย้ายไปอยู่ชั้นรายปีแล้ว เพื่อไม่ให้มีแหล่งข้อมูลสองที่
        $this->dropColumn('{{%pm_strategy_indicator}}', 'calculation');

        // เกณฑ์การให้คะแนน 5 ระดับ
        $this->createTable('{{%pm_strategy_indicator_score}}', array_merge([
            'id' => $this->primaryKey(),
            'indicator_year_id' => $this->integer()->notNull(),
            'level' => $this->integer()->notNull(),
            'description' => $this->text()->null(),
            'min_value' => $this->decimal(18, 4)->null(),
            'max_value' => $this->decimal(18, 4)->null(),
        ], $audit), $options);
        $this->childKeys('score', ['indicator_year_id', 'level']);

        // รอบการประเมิน (1/3/6/9/12 เดือน) — is_selected คือ "ระยะเวลาการประเมินผล" ในแบบฟอร์ม
        $this->createTable('{{%pm_strategy_indicator_period}}', array_merge([
            'id' => $this->primaryKey(),
            'indicator_year_id' => $this->integer()->notNull(),
            'period_month' => $this->integer()->notNull(),
            'is_selected' => $this->boolean()->notNull()->defaultValue(false),
            'target_value' => $this->decimal(18, 4)->null(),
            'actual_value' => $this->decimal(18, 4)->null(),
            'score_level' => $this->integer()->null(),
            'note' => $this->text()->null(),
        ], $audit), $options);
        $this->childKeys('period', ['indicator_year_id', 'period_month']);

        // ผลงานจริงรายเดือน — เก็บเลขเดือนปฏิทิน 1-12 เรียงตามปีงบประมาณตอนแสดงผล
        $this->createTable('{{%pm_strategy_indicator_month}}', array_merge([
            'id' => $this->primaryKey(),
            'indicator_year_id' => $this->integer()->notNull(),
            'month' => $this->integer()->notNull(),
            'numerator' => $this->decimal(18, 4)->null(),
            'denominator' => $this->decimal(18, 4)->null(),
            'value' => $this->decimal(18, 4)->null(),
            'note' => $this->text()->null(),
        ], $audit), $options);
        $this->childKeys('month', ['indicator_year_id', 'month']);

        // ข้อมูลพื้นฐานย้อนหลัง (Baseline Data)
        $this->createTable('{{%pm_strategy_indicator_baseline}}', array_merge([
            'id' => $this->primaryKey(),
            'indicator_year_id' => $this->integer()->notNull(),
            'fiscal_year' => $this->integer()->notNull(),
            'value' => $this->decimal(18, 4)->null(),
        ], $audit), $options);
        $this->childKeys('baseline', ['indicator_year_id', 'fiscal_year']);
    }

    private function childKeys(string $suffix, array $unique): void
    {
        $table = "{{%pm_strategy_indicator_{$suffix}}}";
        $this->createIndex("uq-pm_strategy_indicator_{$suffix}", $table, $unique, true);
        $this->addForeignKey("fk-pm_strategy_indicator_{$suffix}-year", $table, 'indicator_year_id', self::PARENT, 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown(): void
    {
        foreach (['baseline', 'month', 'period', 'score'] as $suffix) {
            $this->dropTable("{{%pm_strategy_indicator_{$suffix}}}");
        }
        $this->addColumn('{{%pm_strategy_indicator}}', 'calculation', $this->text()->null());
        foreach (['owner_phone', 'owner_name', 'supervisor_phone', 'supervisor_name', 'baseline_average', 'baseline_label',
                  'data_source', 'evaluation_method', 'formula', 'definition', 'target_population', 'owner_team'] as $column) {
            $this->dropColumn(self::PARENT, $column);
        }
    }
}
