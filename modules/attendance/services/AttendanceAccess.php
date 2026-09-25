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
        // อ่านค่า ผอ. ครั้งเดียวต่อ request — ถูกเรียกต่อพนักงานทุกคนใน reviewableEmployeeIds()
        static $directorId = null;
        if ($directorId === null) $directorId = (int)(\app\components\SiteHelper::getInfo()['director_name'] ?? 0);
        return (bool)$empId && $directorId && (int)$empId === $directorId;
    }

    const SETTING_NAME = 'attendance_setting';

    /** ผู้ยืนยันแทน — ใช้เมื่อไม่มีหัวหน้าในผัง หรือหัวหน้าถัดไปเป็น ผอ. (ตั้งค่าที่หน้าตั้งค่าเวลาทำงาน) */
    public static function fallbackReviewerId(): ?int
    {
        try {
            $row = \app\models\Categorise::findOne(['name' => self::SETTING_NAME]);
        } catch (\Throwable $e) {
            return null;
        }
        $json = $row && is_array($row->data_json) ? $row->data_json : [];
        $id = (int)($json['fallback_reviewer'] ?? 0);
        return $id ?: null;
    }

    public static function saveFallbackReviewerId(?int $empId): bool
    {
        $row = \app\models\Categorise::findOne(['name' => self::SETTING_NAME]) ?: new \app\models\Categorise(['name' => self::SETTING_NAME, 'title' => 'ตั้งค่าระบบลงเวลา']);
        $json = is_array($row->data_json) ? $row->data_json : [];
        $json['fallback_reviewer'] = $empId ?: null;
        $row->data_json = $json;
        return $row->save(false);
    }

    /**
     * ผู้ยืนยันของพนักงาน: หัวหน้าหน่วย → หัวหน้ากลุ่ม → ผู้ยืนยันแทน (เมื่อไม่มีหัวหน้าหรือหัวหน้าเป็น ผอ.)
     * คืน null = ไม่มีรายบุคคล ให้ admin/hr/attendance ยืนยันจากคิวรวม
     */
    public static function resolveReviewerId(int $empId, ?int $supervisorId, ?int $fallback = null): ?int
    {
        if ($supervisorId && $supervisorId !== $empId && !self::isDirector($supervisorId)) return $supervisorId;
        $fallback = $fallback ?? self::fallbackReviewerId();
        if ($fallback && $fallback !== $empId && !self::isDirector($fallback)) return $fallback;
        return null;
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

    /** คิวรอยืนยัน + ตัวกรองชื่อ/บริเวณ — ใช้ร่วมกันระหว่างกล่องอนุมัติ (/approve-v2) และหน้าผู้ดูแล (/attendance) */
    public static function searchPending(string $q = '', string $loc = '')
    {
        $query = self::pendingQuery()->orderBy(['approve.id' => SORT_DESC]);
        if ($q !== '') {
            $empIds = Employees::find()->where(['or', ['like', 'fname', $q], ['like', 'lname', $q]])->select('id')->column();
            $query->andWhere(['checkin_record.emp_id' => $empIds ?: [0]]);
        }
        if ($loc === 'in') $query->andWhere(['checkin_record.is_in_location' => 1]);
        elseif ($loc === 'out') $query->andWhere(['checkin_record.is_in_location' => 0]);
        return $query;
    }

    public static function canReview($record): bool
    {
        if (Yii::$app->user->isGuest) return false;
        $me = UserHelper::GetEmployee();
        if (!$me || (int)$record->emp_id === (int)$me->id) return false;
        if (self::isReviewer()) return true;
        if (self::isDirector((int)$me->id)) return false; // ผอ. ไม่ต้องยืนยันการลงเวลา
        if (!$record->employee) return false;
        return self::resolveReviewerId((int)$record->emp_id, (int)$record->employee->supervisorEmpId() ?: null) === (int)$me->id;
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
        $fallback = self::fallbackReviewerId();
        foreach (Employees::find()->select(['id', 'department'])->asArray()->all() as $employee) {
            if ((int)$employee['id'] === (int)$me->id) continue;
            $supervisor = null;
            foreach ($leaders[(int)$employee['department']] ?? [] as $leader) {
                if (!$leader || $leader === (int)$employee['id']) continue;
                $supervisor = $leader;
                break;
            }
            if (self::resolveReviewerId((int)$employee['id'], $supervisor, $fallback) === (int)$me->id) $ids[] = (int)$employee['id'];
        }
        return $ids;
    }
}
