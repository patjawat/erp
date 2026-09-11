<?php
namespace app\modules\attendance\services;

use app\modules\attendance\models\WorkSchedule;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;
use yii\db\Query;

/** Batched read model for the work schedule settings and member preview. */
class ScheduleDirectory
{
    public $nodes;
    public $schedules;
    public $employees;
    public $history = [];
    public $chains = [];

    public function __construct()
    {
        $this->nodes = Organization::find()->orderBy(['root'=>SORT_ASC, 'lft'=>SORT_ASC])->indexBy('id')->asArray()->all();
        $this->schedules = WorkSchedule::find()->orderBy(['id'=>SORT_DESC])->indexBy('id')->all();
        $this->employees = Employees::find()->select(['id','fname','lname','department','work_shift'])
            ->where(['status'=>Employees::STATUS_WORKING])->orderBy(['fname'=>SORT_ASC,'lname'=>SORT_ASC])->asArray()->all();
        foreach ((new Query())->from('{{%attendance_assignment}}')->orderBy(['effective_from'=>SORT_DESC,'id'=>SORT_DESC])->all() as $row) {
            $this->history[$row['scope']][$row['target_id']][] = $row;
        }
        foreach ($this->nodes as $id => $node) {
            $parents = [];
            foreach ($this->nodes as $parent) {
                if ($parent['root'] == $node['root'] && $parent['lft'] < $node['lft'] && $parent['rgt'] > $node['rgt']) $parents[$parent['lft']] = $parent['id'];
            }
            krsort($parents);
            $this->chains[$id] = array_merge([$id], array_values($parents));
        }
    }

    public function members(int $id): array
    {
        return array_values(array_filter($this->employees, fn($e) => in_array($id, $this->chains[$e['department']] ?? [(int)$e['department']])));
    }

    public function state(array $employee, string $date): array
    {
        return WorkScheduleService::resolve($employee, $date, $this->history, $this->schedules, $this->chains);
    }

    public function departmentState(int $id, string $date): array
    {
        return $this->state(['id'=>0,'department'=>$id,'work_shift'=>'normal'], $date);
    }

    public function upcoming(string $scope, int $id, string $date): ?array
    {
        $future = array_filter($this->history[$scope][$id] ?? [], fn($row) => $row['effective_from'] > $date);
        usort($future, fn($a,$b) => strcmp($a['effective_from'],$b['effective_from']) ?: ($b['id'] <=> $a['id']));
        return $future[0] ?? null;
    }

    public static function label(array $state): string
    {
        if ($state['mode'] === 'shift') return 'ตามตารางเวร';
        $s = $state['schedule'];
        return $s ? $s->name.' · '.$s->start_time.'–'.$s->end_time : 'ยังไม่กำหนดเวลาปกติ';
    }
}
