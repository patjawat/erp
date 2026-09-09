<?php

namespace app\modules\attendance\services;

use Yii;
use app\components\UserHelper;
use app\modules\hr\models\Employees;

class AttendanceAccess
{
    public static function isReviewer(): bool
    {
        return !Yii::$app->user->isGuest && (Yii::$app->user->can('admin') || Yii::$app->user->can('hr') || Yii::$app->user->can('attendanceReview'));
    }

    public static function canReview($record): bool
    {
        if (Yii::$app->user->isGuest) return false;
        $me = UserHelper::GetEmployee();
        if (!$me || (int)$record->emp_id === (int)$me->id) return false;
        return self::isReviewer() || ($record->employee && (int)$record->employee->supervisorEmpId() === (int)$me->id);
    }

    public static function canView($record): bool
    {
        if (Yii::$app->user->isGuest) return false;
        $me = UserHelper::GetEmployee();
        return self::isReviewer() || ($me && ((int)$record->emp_id === (int)$me->id || self::canReview($record)));
    }

    /** Supervisors are resolved from the employee's current organisation, including group fallback. */
    public static function reviewableEmployeeIds(): array
    {
        $me = UserHelper::GetEmployee();
        if (!$me) return [];
        $ids = [];
        $leaders = [];
        foreach (\app\modules\hr\models\Organization::find()->all() as $node) {
            $probe = new Employees();
            $probe->populateRelation('empDepartment', $node);
            $units = $probe->orgUnits();
            foreach (['unit', 'group'] as $level) {
                $unit = $units[$level] ?? null;
                if (!$unit) continue;
                $json = is_array($unit->data_json) ? $unit->data_json : json_decode((string)$unit->data_json, true);
                $leaders[(int)$node->id][] = (int)($json['leader1'] ?? 0);
            }
        }
        // Avoid Employees::afterFind side effects and repeated hierarchy queries for every employee.
        foreach (Employees::find()->select(['id', 'department'])->asArray()->all() as $employee) {
            if ((int)$employee['id'] === (int)$me->id) continue;
            foreach ($leaders[(int)$employee['department']] ?? [] as $leader) {
                if (!$leader || $leader === (int)$employee['id']) continue;
                if ($leader === (int)$me->id) $ids[] = (int)$employee['id'];
                break;
            }
        }
        return $ids;
    }
}
