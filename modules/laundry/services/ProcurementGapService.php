<?php

namespace app\modules\laundry\services;

use Yii;
use yii\base\InvalidArgumentException;
use yii\db\Connection;
use yii\db\Query;

/** Read-only planning estimate; not an authority to create purchase orders. */
class ProcurementGapService
{
    private $db;

    public function __construct(?Connection $db = null)
    {
        $this->db = $db ?: Yii::$app->db;
    }

    public function report(int $year): array
    {
        if ($year < 2000 || $year > 2200) {
            throw new InvalidArgumentException('ปีสอบยอดไม่ถูกต้อง');
        }
        $items = (new Query())->from('laundry_item')->orderBy(['item_name' => SORT_ASC])->all($this->db);
        $pars = (new Query())->from('laundry_par')->all($this->db);
        $counts = (new Query())->select(['c.department_id', 'c.cutoff_at', 'l.item_id', 'l.actual_qty'])
            ->from(['c' => 'laundry_annual_count'])
            ->innerJoin(['l' => 'laundry_annual_count_line'], 'l.count_id = c.id')
            ->where(['c.count_year' => $year, 'c.status' => 'APPROVED'])->all($this->db);
        $byCount = [];
        foreach ($counts as $count) {
            $byCount[$count['department_id']][$count['item_id']] = $count;
        }
        $byItem = [];
        foreach ($pars as $par) {
            $itemId = (int) $par['item_id'];
            $count = $byCount[$par['department_id']][$itemId] ?? null;
            $byItem[$itemId][] = [
                'target' => (int) $par['target_qty'],
                'actual' => $count === null || $count['actual_qty'] === null ? null : (int) $count['actual_qty'],
            ];
        }
        $inventory = new PieceInventoryService($this->db);
        $rows = [];
        foreach ($items as $item) {
            $itemId = (int) $item['id'];
            $central = $inventory->balance($itemId, 'CLEAN');
            $gap = self::calculate($byItem[$itemId] ?? [], $central);
            $rows[] = array_merge($item, $gap, ['clean_qty' => $central]);
        }
        return $rows;
    }

    /** @param array<int,array{target:int,actual:?int}> $departments */
    public static function calculate(array $departments, int $centralClean): array
    {
        $target = 0;
        $counted = 0;
        $missing = 0;
        $wardDeficit = 0;
        foreach ($departments as $department) {
            $target += $department['target'];
            if ($department['actual'] === null) {
                $missing++;
                continue;
            }
            $counted++;
            $wardDeficit += max(0, $department['target'] - $department['actual']);
        }
        return [
            'target_qty' => $target,
            'counted_departments' => $counted,
            'missing_departments' => $missing,
            'ward_deficit' => $wardDeficit,
            'preliminary_gap' => $missing === 0 && $counted > 0 ? max(0, $wardDeficit - max(0, $centralClean)) : null,
        ];
    }
}
