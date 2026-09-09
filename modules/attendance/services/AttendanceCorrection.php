<?php

namespace app\modules\attendance\services;

use Yii;
use app\modules\attendance\models\CheckinRecord;
use app\modules\approveV2\models\Approve;

/** Corrections are auditable requests, never a shortcut around approval. */
class AttendanceCorrection
{
    public static function canAmend(CheckinRecord $record): bool
    {
        return !Yii::$app->user->isGuest && (Yii::$app->user->can('admin') || Yii::$app->user->can('hr') || AttendanceAccess::canReview($record));
    }

    public static function revision(CheckinRecord $record): string
    {
        return hash('sha256', json_encode($record->getAttributes(), JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION));
    }

    public static function timestamp($value): string
    {
        if (!is_string($value)) throw new \DomainException('รูปแบบวันเวลาไม่ถูกต้อง');
        $value = str_replace('T', ' ', trim($value));
        if (strlen($value) === 16) $value .= ':00';
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $value, new \DateTimeZone('Asia/Bangkok'));
        if (!$date || $date->format('Y-m-d H:i:s') !== $value) throw new \DomainException('กรุณาระบุวันเวลาที่มีอยู่จริง ในรูปแบบ ปี-เดือน-วัน ชั่วโมง:นาที:วินาที');
        if ($value > AttendanceService::now()) throw new \DomainException('ไม่สามารถบันทึกเวลาในอนาคต');
        return $value;
    }

    public static function shift(int $employeeId, string $at, $id): ?array
    {
        if ($id === '' || $id === null) return null;
        if (!is_scalar($id) || !ctype_digit((string)$id)) throw new \DomainException('รหัสเวรไม่ถูกต้อง');
        foreach (RosterAttendance::candidates($employeeId, $at) as $shift) {
            if ((string)$shift['id'] === (string)$id) return $shift;
        }
        throw new \DomainException('เวรไม่ตรงกับพนักงานหรือช่วงวันเวลา หรือยังไม่ประกาศใช้');
    }

    public static function amend(int $id, array $input): CheckinRecord
    {
        foreach (['checkin_at', 'check_type', 'roster_item_id', 'reason', 'revision'] as $key) {
            if (!isset($input[$key]) || !is_string($input[$key])) throw new \DomainException('ข้อมูลการแก้ไขไม่ครบ กรุณาโหลดหน้าใหม่');
        }
        $reason = trim($input['reason']);
        if ($reason === '' || mb_strlen($reason) > 2000) throw new \DomainException('กรุณาระบุเหตุผลแก้ไข ไม่เกิน 2,000 ตัวอักษร');
        $at = self::timestamp($input['checkin_at']);
        if (!in_array($input['check_type'], ['in', 'out'], true)) throw new \DomainException('ประเภทลงเวลาไม่ถูกต้อง');
        $db = Yii::$app->db;
        $tx = $db->beginTransaction();
        try {
            $record = CheckinRecord::findOne($id);
            if (!$record || !self::canAmend($record)) throw new \yii\web\ForbiddenHttpException('ไม่มีสิทธิ์แก้ไขรายการนี้');
            $db->createCommand('SELECT id FROM {{%employees}} WHERE id=:id FOR UPDATE', [':id' => $record->emp_id])->queryScalar();
            $db->createCommand('SELECT id FROM {{%checkin_record}} WHERE id=:id FOR UPDATE', [':id' => $id])->queryScalar();
            if (!$record->refresh()) throw new \DomainException('รายการถูกลบแล้ว');
            if (!hash_equals(self::revision($record), $input['revision'])) throw new \DomainException('รายการเปลี่ยนแปลงแล้ว กรุณาโหลดหน้าใหม่ก่อนแก้ไข');
            $shift = self::shift((int)$record->emp_id, $at, $input['roster_item_id']);
            $duplicate = CheckinRecord::find()->where(['emp_id' => $record->emp_id, 'checkin_at' => $at, 'check_type' => $input['check_type']])
                ->andWhere(['<>', 'id', $id])->exists();
            if ($duplicate) throw new \DomainException('มีรายการประเภทเดียวกันในเวลานี้แล้ว');
            $data = is_array($record->data_json) ? $record->data_json : [];
            $data['amendments'][] = [
                'at' => AttendanceService::now(), 'by' => Yii::$app->user->id, 'reason' => $reason,
                'before' => ['checkin_at' => $record->checkin_at, 'check_type' => $record->check_type, 'attendance' => $data['attendance'] ?? null,
                    'status' => $record->status, 'approved_by' => $record->approved_by, 'approved_at' => $record->approved_at, 'comment' => $record->comment],
                'after' => ['checkin_at' => $at, 'check_type' => $input['check_type'], 'roster_item_id' => $shift['id'] ?? null],
            ];
            $data['attendance'] = RosterAttendance::evaluate($at, $input['check_type'], $shift);
            $record->checkin_at = $at;
            $record->check_type = $input['check_type'];
            $record->data_json = $data;
            $record->status = CheckinRecord::STATUS_PENDING;
            $record->approved_by = null;
            $record->approved_at = null;
            $record->comment = null;
            if (!$record->save()) throw new \DomainException(implode(' ', $record->getFirstErrors()));
            // Keep earlier decisions for audit, while one fresh request becomes actionable.
            $activeApprovals = Approve::find()->where(['name' => 'checkin', 'from_id' => (string)$id, 'deleted_at' => null])->all();
            foreach ($activeApprovals as $old) {
                $old->deleted_at = AttendanceService::now();
                $old->deleted_by = Yii::$app->user->id;
                if (!$old->save()) throw new \RuntimeException('Could not archive previous decision');
            }
            $record->createApproveRecord();
            $tx->commit();
            return $record;
        } catch (\Throwable $e) {
            $tx->rollBack();
            throw $e;
        }
    }
}
