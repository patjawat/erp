<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * เพิ่มวิธีสรุปผลรายปีของ KPI (sum/avg/min/max/last)
 * ใช้รวมค่ารายเดือนเป็น "ผลงานสรุป" ของตัวชี้วัด
 */
final class m260728_000002_add_kpi_item_aggregation extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn('{{%kpi_item}}', 'aggregation',
            $this->string(20)->notNull()->defaultValue('avg')
                ->comment('วิธีสรุปผลรายปี: sum/avg/min/max/last')
                ->after('value_type'));
    }

    public function safeDown(): void
    {
        $this->dropColumn('{{%kpi_item}}', 'aggregation');
    }
}
