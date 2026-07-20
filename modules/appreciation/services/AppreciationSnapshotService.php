<?php

namespace app\modules\appreciation\services;

use Yii;
use app\modules\appreciation\models\AppreciationProgramYear;
use app\modules\appreciation\models\AppreciationValue;
use app\modules\hr\models\Employees;

class AppreciationSnapshotService
{
    public static function employee($empId, $eventDate = null)
    {
        $empId = (int) $empId;
        $employee = Employees::findOne($empId);
        $raw = Yii::$app->db->createCommand(
            'SELECT birthday, department FROM {{%employees}} WHERE id = :id',
            [':id' => $empId]
        )->queryOne() ?: [];

        $age = self::ageAt($raw['birthday'] ?? null, $eventDate ?: date('Y-m-d'));

        return [
            'department_id' => isset($raw['department']) ? (int) $raw['department'] : null,
            'department_name' => $employee ? trim((string) $employee->departmentName()) : null,
            'position_name' => $employee ? trim((string) $employee->positionName()) : null,
            'position_group_name' => $employee ? trim((string) $employee->positionGroupName()) : null,
            'age' => $age,
            'age_band' => self::ageBand($age),
        ];
    }

    public static function value($badgeType)
    {
        $value = $badgeType ? AppreciationValue::findOne(['code' => $badgeType]) : null;
        return [
            'value_name' => $value ? $value->name : null,
            'core_value_code' => $value ? ($value->core_value_code ?: $value->code) : $badgeType,
            'core_value_name' => $value ? ($value->core_value_name ?: $value->name) : $badgeType,
        ];
    }

    public static function activeProgramYearId()
    {
        $year = AppreciationProgramYear::active();
        return $year ? (int) $year->id : null;
    }

    public static function ageBand($age)
    {
        if ($age === null) return 'unknown';
        if ($age < 25) return 'under_25';
        if ($age < 35) return '25_34';
        if ($age < 45) return '35_44';
        if ($age < 55) return '45_54';
        return '55_plus';
    }

    public static function ageBandLabels()
    {
        return [
            'under_25' => 'ต่ำกว่า 25 ปี',
            '25_34' => '25–34 ปี',
            '35_44' => '35–44 ปี',
            '45_54' => '45–54 ปี',
            '55_plus' => '55 ปีขึ้นไป',
            'unknown' => 'ไม่ระบุวันเกิด',
        ];
    }

    private static function ageAt($birthday, $eventDate)
    {
        if (!$birthday || $birthday === '0000-00-00') return null;
        try {
            $birth = new \DateTimeImmutable(substr((string) $birthday, 0, 10));
            $event = new \DateTimeImmutable(substr((string) $eventDate, 0, 10));
            if ($birth > $event) return null;
            return (int) $birth->diff($event)->y;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
