<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Seed 5 กลุ่มตัวชี้วัดตามที่ผู้ใช้กำหนด
 * กลุ่ม 1 (strategy) เป็น marker ชี้ว่าดึงข้อมูลจากทะเบียนยุทธศาสตร์เดิม
 */
final class m260921_000002_seed_pm_kpi_group extends Migration
{
    private array $groups = [
        ['strategy',         'ตัวชี้วัดระดับยุทธศาสตร์',              'strategy',   'bi-diagram-3',    '#4f46e5', 1],
        ['hospital_monitor', 'ตัวชี้วัดของโรงพยาบาลสำหรับ Monitor',   'standalone', 'bi-speedometer2', '#0ea5e9', 2],
        ['quality_team',     'ตัวชี้วัดคุณภาพของทีมประสาน',           'standalone', 'bi-shield-check', '#10b981', 3],
        ['nursing',          'ตัวชี้วัดทางงานพยาบาล',                 'standalone', 'bi-heart-pulse',  '#ec4899', 4],
        ['unit',             'ตัวชี้วัดของหน่วยงาน',                  'standalone', 'bi-building',     '#f59e0b', 5],
    ];

    public function safeUp(): void
    {
        $now = date('Y-m-d H:i:s');
        foreach ($this->groups as [$code, $name, $kind, $icon, $color, $sort]) {
            $exists = (new \yii\db\Query())->from('{{%pm_kpi_group}}')->where(['code' => $code])->exists($this->db);
            if ($exists) {
                continue;
            }
            $this->insert('{{%pm_kpi_group}}', [
                'ref' => substr(Yii::$app->security->generateRandomString(40), 0, 32),
                'code' => $code,
                'name' => $name,
                'kind' => $kind,
                'icon' => $icon,
                'color' => $color,
                'sort_order' => $sort,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function safeDown(): void
    {
        $this->delete('{{%pm_kpi_group}}', ['code' => array_column($this->groups, 0)]);
    }
}
