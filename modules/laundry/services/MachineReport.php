<?php

namespace app\modules\laundry\services;

use app\modules\am\models\Asset;
use Yii;
use yii\base\InvalidArgumentException;
use yii\db\Connection;
use yii\db\Query;

/** Cycle statistics, not a measure of machine availability or repair downtime. */
class MachineReport
{
    private $db;

    public function __construct(?Connection $db = null)
    {
        $this->db = $db ?: Yii::$app->db;
    }

    public function yearly(int $year): array
    {
        if ($year < 2000 || $year > 2200) {
            throw new InvalidArgumentException('ปีรายงานไม่ถูกต้อง');
        }
        $machines = (new Query())->select([
            'asset_id' => 'm.asset_id', 'machine_type' => 'm.machine_type',
            'capacity_kg' => 'm.capacity_kg', 'asset_code' => 'a.code', 'asset_name' => 'a.asset_name',
        ])->from(['m' => 'laundry_machine'])
            ->innerJoin(['a' => Asset::tableName()], 'a.id = m.asset_id')
            ->orderBy(['m.machine_type' => SORT_ASC, 'a.code' => SORT_ASC])->all($this->db);
        $batches = (new Query())->select(['asset_id', 'stage', 'linen_class', 'status', 'input_kg', 'output_kg', 'started_at', 'ended_at'])
            ->from('laundry_processing_batch')
            ->where(['>=', 'started_at', sprintf('%04d-01-01 00:00:00', $year)])
            ->andWhere(['<', 'started_at', sprintf('%04d-01-01 00:00:00', $year + 1)])
            ->all($this->db);
        $byMachine = [];
        foreach ($batches as $batch) {
            $byMachine[$batch['asset_id']][] = $batch;
        }
        foreach ($machines as &$machine) {
            $machine['statistics'] = self::summarize($byMachine[$machine['asset_id']] ?? [], (float) $machine['capacity_kg']);
        }
        unset($machine);
        return $machines;
    }

    public static function summarize(array $batches, float $capacityKg): array
    {
        $result = [
            'completed' => 0, 'aborted' => 0, 'running' => 0,
            'soiled_kg' => 0.0, 'infectious_kg' => 0.0, 'output_kg' => 0.0,
            'aborted_input_kg' => 0.0, 'completed_minutes' => 0,
            'load_percent' => null,
        ];
        $completedInput = 0.0;
        foreach ($batches as $batch) {
            if ($batch['status'] === 'RUNNING') {
                $result['running']++;
                continue;
            }
            if ($batch['status'] === 'ABORTED') {
                $result['aborted']++;
                $result['aborted_input_kg'] += (float) $batch['input_kg'];
                continue;
            }
            if ($batch['status'] !== 'COMPLETED') {
                continue;
            }
            $result['completed']++;
            $input = (float) $batch['input_kg'];
            $completedInput += $input;
            if ($batch['linen_class'] === 'INFECTIOUS') {
                $result['infectious_kg'] += $input;
            } else {
                $result['soiled_kg'] += $input;
            }
            $result['output_kg'] += (float) $batch['output_kg'];
            if ($batch['ended_at'] && $batch['started_at']) {
                $seconds = strtotime($batch['ended_at']) - strtotime($batch['started_at']);
                $result['completed_minutes'] += max(0, (int) ceil($seconds / 60));
            }
        }
        if ($result['completed'] > 0 && $capacityKg > 0) {
            $result['load_percent'] = round(100 * $completedInput / ($result['completed'] * $capacityKg), 1);
        }
        return $result;
    }
}
