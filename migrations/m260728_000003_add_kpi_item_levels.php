<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * เพิ่มเกณฑ์คะแนน 5 ระดับต่อ KPI (คะแนนตามระดับค่าเป้าหมาย) + ทิศทางการวัด
 * ใช้แปลงผลงานเป็น "ระดับ (1–5)" ตามแบบฟอร์มประเมินผลสัมฤทธิ์ของงาน
 */
final class m260728_000003_add_kpi_item_levels extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn('{{%kpi_item}}', 'level1', $this->decimal(14, 2)->null()->comment('เกณฑ์ระดับ 1')->after('target_value'));
        $this->addColumn('{{%kpi_item}}', 'level2', $this->decimal(14, 2)->null()->comment('เกณฑ์ระดับ 2')->after('level1'));
        $this->addColumn('{{%kpi_item}}', 'level3', $this->decimal(14, 2)->null()->comment('เกณฑ์ระดับ 3')->after('level2'));
        $this->addColumn('{{%kpi_item}}', 'level4', $this->decimal(14, 2)->null()->comment('เกณฑ์ระดับ 4')->after('level3'));
        $this->addColumn('{{%kpi_item}}', 'level5', $this->decimal(14, 2)->null()->comment('เกณฑ์ระดับ 5')->after('level4'));
        $this->addColumn('{{%kpi_item}}', 'direction', $this->string(10)->notNull()->defaultValue('asc')->comment('asc=มากขึ้นดี / desc=น้อยลงดี')->after('level5'));
    }

    public function safeDown(): void
    {
        foreach (['direction', 'level5', 'level4', 'level3', 'level2', 'level1'] as $col) {
            $this->dropColumn('{{%kpi_item}}', $col);
        }
    }
}
