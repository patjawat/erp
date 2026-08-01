<?php

use yii\db\Migration;
use yii\db\Query;
use app\modules\settings\models\OrgUnit;

/**
 * ผูกแผน (plan_order) กับทะเบียนหน่วยงานกลาง (org_unit) — เฟส 2a
 *  - เพิ่มคอลัมน์ plan_unit_id
 *  - sync org_unit ให้ปีที่มีแผนแต่ยังไม่มีทะเบียน
 *  - backfill: department_id (tree.id) -> org_unit ปีเดียวกัน (source=structure)
 *  - department_id ยังคงไว้ (dual-write) ยังไม่เลิกใช้
 */
class m260801_000004_add_plan_unit_id_to_plan_order extends Migration
{
    public function safeUp()
    {
        $this->addColumn('plan_order', 'plan_unit_id', $this->bigInteger()->null()->after('department_id')->comment('หน่วยงานในทะเบียน org_unit'));
        $this->createIndex('idx_plan_order_unit', 'plan_order', 'plan_unit_id');

        // sync org_unit สำหรับปีที่มีแผนแต่ทะเบียนยังว่าง
        foreach ((new Query())->select('thai_year')->distinct()->from('plan_order')->column() as $y) {
            $y = (int) $y;
            if ($y > 0 && !(new Query())->from('org_unit')->where(['thai_year' => $y])->exists()) {
                $r = OrgUnit::syncStructure($y);
                echo "    > sync org_unit ปี {$y}: +{$r['added']}\n";
            }
        }

        // backfill plan_unit_id จาก department_id
        $n = $this->db->createCommand("
            UPDATE plan_order p
            JOIN org_unit o ON o.thai_year = p.thai_year AND o.source = 'structure' AND o.ref_id = p.department_id
            SET p.plan_unit_id = o.id
            WHERE p.plan_unit_id IS NULL
        ")->execute();
        echo "    > backfill plan_unit_id: {$n} แถว\n";
    }

    public function safeDown()
    {
        $this->dropColumn('plan_order', 'plan_unit_id');
    }
}
