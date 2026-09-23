<?php

namespace app\modules\laundry\services;

use Yii;
use yii\db\Connection;
use yii\db\Query;

class CollectionReport
{
    private $db;

    public function __construct(?Connection $db = null)
    {
        $this->db = $db ?: Yii::$app->db;
    }

    /**
     * Rows grouped by actual collection month and department, never by wash date.
     * นับทุกรอบรับผ้า (ไม่กรองเฉพาะ CONFIRMED) — หน้ารับผ้าบันทึกเข้ารอบรายวันสถานะ OPEN และไม่มีขั้นยืนยันรอบ
     * ในเมนูแล้ว ถ้ากรอง CONFIRMED รายงานจะว่างตลอด (ให้ตรงกับแดชบอร์ด)
     */
    public function monthly(string $from, string $to, ?int $departmentId = null): array
    {
        $query = (new Query())
            ->select([
                'month' => new \yii\db\Expression("DATE_FORMAT(s.collected_at, '%Y-%m')"),
                'department_id' => 's.department_id',
                'department_name' => 'd.name',
                'soiled_kg' => new \yii\db\Expression("SUM(CASE WHEN w.linen_class = 'SOILED' THEN w.net_kg ELSE 0 END)"),
                'infectious_kg' => new \yii\db\Expression("SUM(CASE WHEN w.linen_class = 'INFECTIOUS' THEN w.net_kg ELSE 0 END)"),
                'total_kg' => new \yii\db\Expression('SUM(w.net_kg)'),
                'round_count' => new \yii\db\Expression('COUNT(DISTINCT r.id)'),
            ])
            ->from(['w' => 'laundry_collection_weight'])
            ->innerJoin(['s' => 'laundry_collection_stop'], 's.id = w.stop_id')
            ->innerJoin(['r' => 'laundry_collection_round'], 'r.id = s.round_id')
            ->leftJoin(['d' => 'tree'], 'd.id = s.department_id')
            ->where(['>=', 's.collected_at', $from . ' 00:00:00'])
            ->andWhere(['<', 's.collected_at', (new \DateTimeImmutable($to))->modify('+1 day')->format('Y-m-d') . ' 00:00:00'])
            ->groupBy(['month', 's.department_id', 'd.name'])
            ->orderBy(['month' => SORT_ASC, 'department_name' => SORT_ASC]);
        if ($departmentId) {
            $query->andWhere(['s.department_id' => $departmentId]);
        }
        return $query->all($this->db);
    }
}
