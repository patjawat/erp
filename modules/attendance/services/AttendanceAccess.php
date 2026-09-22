<?php

namespace app\modules\attendance\services;

use Yii;
use app\components\UserHelper;
use app\modules\hr\models\Employees;

class AttendanceAccess
{
    public static function isReviewer(): bool
    {
        if (Yii::$app->user->isGuest) return false;
        // ผอ. ไม่ต้องยืนยันการลงเวลา (ตัด director ออก) — เหลือ admin/hr/attendance (เจ้าหน้าที่ลงเวลา) ดูแลภาพรวม + เป็น fallback
        return Yii::$app->user->can('admin') || Yii::$app->user->can('hr') || Yii::$app->user->can('attendance');
    }

    /** ผอ. (ตามค่าระบบ) ไม่ต้องยืนยันการลงเวลา — กันไม่ให้ถูกมอบหมาย/เห็นคิว ไม่ว่าจะเป็นหัวหน้าหน่วยหรือไม่ */
    private static function isDirector(?int $empId): bool
    {
        return (bool)$empId && \app\components\SiteHelper::isDirectorFromSettings((int)$empId);
    }

    /** Shared by inbox and badges: only pending, active requests the viewer can decide. */
    public static function pendingQuery()
    {
        $query = \app\modules\approveV2\models\Approve::find()->alias('approve')
            ->joinWith(['checkinRecord', 'checkinRecord.employee'])
            ->where(['approve.name'=>'checkin','approve.status'=>'Pending','approve.deleted_at'=>null,'checkin_record.status'=>'pending']);
        $me = UserHelper::GetEmployee();
        if (!$me || Yii::$app->user->isGuest) return $query->andWhere('1=0');
        $query->andWhere(['<>','checkin_record.emp_id',$me->id]);
        if (!self::isReviewer()) $query->andWhere(['checkin_record.emp_id'=>self::reviewableEmployeeIds()]);
        return $query;
    }

    public static function canReview($record): bool
    {
        if (Yii::$app->user->isGuest) return false;
        $me = UserHelper::GetEmployee();
        if (!$me || (int)$record->emp_id === (int)$me->id) return false;
        if (self::isReviewer()) return true;
        if (self::isDirector((int)$me->id)) return false; // ผอ. ไม่ต้องยืนยันการลงเวลา
        return $record->employee && (int)$record->employee->supervisorEmpId() === (int)$me->id;
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
        if (self::isDirector((int)$me->id)) return []; // ผอ. ไม่ยืนยันการลงเวลาของใคร
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
