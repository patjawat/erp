<?php

use yii\db\Migration;
use yii\helpers\Json;
use app\components\AppHelper;

/**
 * seed รอบการทำแผนปัจจุบัน (planning period) — เก็บใน categorise name='plan_period'
 * เปิดรับแผนปีถัดไป (YearBudget()+1) เป็นค่าเริ่มต้น
 */
class m260731_000003_seed_plan_period extends Migration
{
    public function safeUp()
    {
        $year = (int) AppHelper::YearBudget() + 1; // ช่วงนี้ = 2570

        $exists = (new \yii\db\Query())->from('categorise')
            ->where(['name' => 'plan_period', 'code' => (string) $year])->exists();

        if (!$exists) {
            $this->insert('categorise', [
                'name'      => 'plan_period',
                'code'      => (string) $year,
                'title'     => 'รอบทำแผนปี ' . $year,
                'active'    => 1,
                'data_json' => Json::encode(['phase' => 'open', 'current' => 1]),
            ]);
        }
    }

    public function safeDown()
    {
        $this->delete('categorise', ['name' => 'plan_period']);
    }
}
