<?php
namespace app\modules\attendance\services;

use Yii;
use yii\db\Query;
use app\modules\attendance\models\WorkSchedule;
use app\modules\hr\models\Employees;

/** Append-only effective assignments; a schedule is immutable once created. */
class WorkScheduleService
{
    public static function ready(): bool { return Yii::$app->db->getTableSchema('{{%attendance_schedule}}') !== null; }
    public static function manager(): bool { return !Yii::$app->user->isGuest && (Yii::$app->user->can('admin') || Yii::$app->user->can('hr')); }
    public static function canAssign(string $scope, int $id): bool
    {
        if (self::manager()) return true;
        $me = \app\components\UserHelper::GetEmployee();
        if (!$me) return false;
        if ($scope === 'employee') {
            $employee = Employees::findOne($id);
            return $employee && (int)$employee->id !== (int)$me->id && (int)$employee->supervisorEmpId() === (int)$me->id;
        }
        $node = \app\modules\hr\models\Organization::findOne($id);
        return $node && (int)($node->data_json['leader1'] ?? 0) === (int)$me->id;
    }
    public static function assign(array $input): void
    {
        foreach (['scope','target_id','mode','schedule_id','effective_from','reason'] as $key) if (!isset($input[$key]) || !is_string($input[$key])) throw new \DomainException('ข้อมูลการกำหนดเวลาไม่ครบ');
        if (!in_array($input['scope'], ['employee','department'], true) || !ctype_digit($input['target_id'])) throw new \DomainException('หน่วยงานหรือพนักงานไม่ถูกต้อง');
        $id = (int)$input['target_id'];
        if (!self::canAssign($input['scope'], $id)) throw new \yii\web\ForbiddenHttpException('ไม่มีสิทธิ์กำหนดเวลาของบุคลากรหรือหน่วยงานนี้');
        $exists = $input['scope'] === 'employee' ? Employees::find()->where(['id'=>$id])->exists() : \app\modules\hr\models\Organization::find()->where(['id'=>$id])->exists();
        if (!$exists) throw new \DomainException('ไม่พบหน่วยงานหรือพนักงาน');
        if (!in_array($input['mode'], ['normal','shift','inherit'], true) || ($input['scope'] === 'department' && $input['mode'] !== 'normal')) throw new \DomainException('รูปแบบทำงานไม่ถูกต้อง');
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $input['effective_from']);
        if (!$date || $date->format('Y-m-d') !== $input['effective_from'] || $input['effective_from'] < substr(AttendanceService::now(),0,10)) throw new \DomainException('เลือกวันที่เริ่มมีผลตั้งแต่วันนี้ เพื่อไม่เปลี่ยนประวัติย้อนหลัง');
        $reason = trim($input['reason']);
        if ($reason === '' || mb_strlen($reason) > 2000) throw new \DomainException('ระบุเหตุผลไม่เกิน 2,000 ตัวอักษร');
        $schedule = $input['mode'] === 'normal' ? WorkSchedule::findOne($input['schedule_id']) : null;
        if ($input['mode'] === 'normal' && !$schedule) throw new \DomainException('กรุณาเลือกชุดเวลาปกติ');
        Yii::$app->db->createCommand()->insert('{{%attendance_assignment}}', [
            'scope'=>$input['scope'],'target_id'=>$id,'mode'=>$input['mode'],'schedule_id'=>$schedule->id ?? null,
            'effective_from'=>$input['effective_from'],'reason'=>$reason,'created_at'=>AttendanceService::now(),'created_by'=>Yii::$app->user->id,
        ])->execute();
    }
    public static function resolve(array $employee, string $date, ?array $history = null, ?array $schedules = null, ?array $chains = null): array
    {
        $base = ['mode'=>$employee['work_shift'] ?? '', 'schedule'=>null, 'source'=>'ยังไม่กำหนด', 'assignment'=>null];
        if (!self::ready()) return $base;
        $latest = static function ($scope, $id) use ($date, $history) {
            if ($history !== null) {
                foreach ($history[$scope][$id] ?? [] as $row) if ($row['effective_from'] <= $date) return $row;
                return null;
            }
            return (new Query())->from('{{%attendance_assignment}}')->where(['scope'=>$scope,'target_id'=>$id])->andWhere(['<=','effective_from',$date])->orderBy(['effective_from'=>SORT_DESC,'id'=>SORT_DESC])->one();
        };
        $own = $latest('employee', $employee['id']);
        if ($own && $own['mode'] !== 'inherit') {
            $base['mode'] = $own['mode']; $assignment = $own; $base['source'] = 'รายบุคคล';
        } else {
            $assignment = null;
            if ($base['mode'] === 'normal') {
                $departmentIds = $chains[$employee['department']] ?? null;
                if ($departmentIds === null) {
                    $node=\app\modules\hr\models\Organization::findOne($employee['department']);
                    $departmentIds=array_merge([(int)$employee['department']],$node?$node->parents()->orderBy(['lft'=>SORT_DESC])->select('id')->column():[]);
                }
                foreach ($departmentIds as $departmentId) if ($assignment=$latest('department',$departmentId)) break;
            }
            if ($assignment) $base['source'] = 'หน่วยงาน';
        }
        $base['assignment'] = $assignment;
        if ($base['mode'] === 'normal' && $assignment && $assignment['schedule_id']) $base['schedule'] = $schedules !== null ? ($schedules[$assignment['schedule_id']] ?? null) : WorkSchedule::findOne($assignment['schedule_id']);
        return $base;
    }
    /** Assign selected members atomically; every employee still requires individual permission. */
    public static function assignMembers(int $departmentId, array $employeeIds, array $input): int
    {
        if (!self::canAssign('department', $departmentId)) throw new \yii\web\ForbiddenHttpException('ไม่มีสิทธิ์กำหนดเวลาของหน่วยงานนี้');
        if (!$employeeIds || count($employeeIds) > 5000) throw new \DomainException('กรุณาเลือกบุคลากรอย่างน้อย 1 คน (ไม่เกิน 5,000 คน)');
        foreach ($employeeIds as $id) if (!is_string($id) || !ctype_digit($id)) throw new \DomainException('รายการบุคลากรไม่ถูกต้อง');
        $employeeIds = array_values(array_unique($employeeIds));
        return Yii::$app->db->transaction(function () use ($departmentId, $employeeIds, $input) {
            $directory = new ScheduleDirectory();
            $allowed = array_column($directory->members($departmentId), 'id');
            foreach ($employeeIds as $id) {
                if (!in_array((int)$id, $allowed)) throw new \DomainException('มีบุคลากรย้ายหน่วยงานหรือไม่ได้ปฏิบัติงานอยู่ กรุณาโหลดรายชื่อใหม่');
                self::assign(array_merge($input, ['scope'=>'employee','target_id'=>$id]));
            }
            return count($employeeIds);
        });
    }
    public static function shifts(array $ids, string $from, string $to, array $occupied): array
    {
        if (!self::ready() || !$ids) return [];
        $result = [];
        $employees = (new Query())->from('{{%employees}}')->select(['id','department','work_shift'])->where(['id'=>$ids])->all();
        $nodes=\app\modules\hr\models\Organization::find()->select(['id','root','lft','rgt'])->asArray()->indexBy('id')->all();
        $chains=[];
        foreach (array_unique(array_column($employees,'department')) as $departmentId) {
            $node=$nodes[$departmentId]??null; $parents=[];
            if ($node) foreach ($nodes as $parent) if ($parent['root']===$node['root'] && $parent['lft']<$node['lft'] && $parent['rgt']>$node['rgt']) $parents[$parent['lft']]=$parent['id'];
            krsort($parents); $chains[$departmentId]=array_merge([$departmentId],array_values($parents));
        }
        $history=[];
        foreach ((new Query())->from('{{%attendance_assignment}}')->where(['or',['scope'=>'employee','target_id'=>$ids],['scope'=>'department']])->andWhere(['<=','effective_from',$to])->orderBy(['effective_from'=>SORT_DESC,'id'=>SORT_DESC])->all() as $row) $history[$row['scope']][$row['target_id']][]=$row;
        $schedules=WorkSchedule::find()->indexBy('id')->all();
        $holidays=[];
        if (Yii::$app->db->getTableSchema('calendar') !== null) {
            $rows=(new Query())->from('calendar')->where(['name'=>'holiday','deleted_at'=>null])->andWhere(['<=','date_start',$to])
                ->andWhere(['or',['>=','date_end',$from],['and',['date_end'=>null],['>=','date_start',$from]]])->all();
            foreach ($rows as $row) {
                $end=min(substr($row['date_end'] ?: $row['date_start'],0,10),$to);
                for ($day=new \DateTimeImmutable(max(substr($row['date_start'],0,10),$from)); $day->format('Y-m-d') <= $end; $day=$day->modify('+1 day')) $holidays[$day->format('Y-m-d')]=true;
            }
        }
        foreach ($employees as $employee) {
            for ($d = new \DateTimeImmutable($from); $d->format('Y-m-d') <= $to; $d = $d->modify('+1 day')) {
                $date = $d->format('Y-m-d');
                if (isset($holidays[$date])) continue;
                if (isset($occupied[$employee['id']][$date])) continue;
                $resolved = self::resolve($employee, $date, $history, $schedules, $chains); $s = $resolved['schedule'];
                if (!$s || !in_array($d->format('N'), explode(',', $s->weekdays), true) || in_array($date, preg_split('/[\s,]+/', trim((string)$s->holidays)), true)) continue;
                $result[] = array_merge(RosterAttendance::interval($date, $s->start_time, $s->end_time), [
                    'id'=>'normal:'.$resolved['assignment']['id'].':'.$date, 'emp_id'=>(int)$employee['id'], 'work_date'=>$date,
                    'name'=>$s->name, 'source'=>'normal', 'grace_minutes'=>(int)$s->grace_minutes, 'window_minutes'=>(int)$s->window_minutes,
                ]);
            }
        }
        return $result;
    }
}
