<?php

namespace app\modules\attendance\services;

use Yii;
use app\modules\attendance\models\CheckinRecord;
use app\modules\attendance\models\CheckinLocation;
use app\modules\hr\models\Employees;
use app\modules\approveV2\models\Approve;

class AttendanceService
{
    public static function now(): string
    {
        return (new \DateTimeImmutable('now', new \DateTimeZone('Asia/Bangkok')))->format('Y-m-d H:i:s');
    }

    public static function record(Employees $employee, array $input): CheckinRecord
    {
        $type = $input['check_type'] ?? 'in';
        $method = $input['method'] ?? 'manual';
        if (!in_array($type, ['in', 'out'], true) || !in_array($method, ['manual', 'qrcode', 'photo'], true)) {
            throw new \DomainException('ประเภทหรือวิธีลงเวลาไม่ถูกต้อง');
        }
        foreach (['qr_token', 'out_of_location_reason', 'request_id', 'photo_path', 'roster_item_id'] as $key) {
            if (isset($input[$key]) && !is_scalar($input[$key])) throw new \DomainException('รูปแบบข้อมูลไม่ถูกต้อง');
        }
        $token = trim((string)($input['qr_token'] ?? ''));
        if ($method === 'qrcode' && $token === '') throw new \DomainException('กรุณาสแกน QR จุดลงเวลา');
        if ($method !== 'qrcode' && $token !== '') throw new \DomainException('กรุณาเลือกวิธีสแกน QR');
        $requestId = (string)($input['request_id'] ?? '');
        if ($requestId !== '' && !preg_match('/^[a-zA-Z0-9_-]{16,100}$/D', $requestId)) throw new \DomainException('รหัสคำขอไม่ถูกต้อง กรุณาเปิดหน้าลงเวลาใหม่');
        $reason = trim((string)($input['out_of_location_reason'] ?? ''));
        if (mb_strlen($reason) > 2000) throw new \DomainException('เหตุผลยาวได้ไม่เกิน 2,000 ตัวอักษร');
        $photo = (string)($input['photo_path'] ?? '');
        if ($method === 'photo' && (!preg_match('~^uploads/checkin/\d{8}_\d{6}_' . (int)$employee->id . '_[a-f0-9]{8}\.(jpg|jpeg|png|gif|webp)$~D', $photo)
            || !is_file(Yii::getAlias('@webroot/' . $photo)))) {
            throw new \DomainException('กรุณาอัปโหลดรูปถ่ายของคุณก่อนลงเวลา');
        }
        $db = Yii::$app->db;
        $tx = $db->beginTransaction();
        try {
            // Serialise submissions per employee, including requests from multiple tabs/devices.
            $db->createCommand('SELECT id FROM {{%employees}} WHERE id=:id FOR UPDATE', [':id' => $employee->id])->queryScalar();
            if ($requestId !== '') {
                $existing = CheckinRecord::find()->where(['emp_id' => $employee->id])
                    ->andWhere("JSON_UNQUOTE(JSON_EXTRACT(data_json, '$.request_id')) = :request", [':request' => $requestId])->one();
                if ($existing) {
                    $tx->commit();
                    return $existing;
                }
            }
            $at = self::now();
            $validation = CheckinLocation::validateClockIn($input['lat'] ?? null, $input['lng'] ?? null, $token, $reason);
            if (!$validation['ok']) throw new \DomainException($validation['message']);
            $candidates = RosterAttendance::candidates((int)$employee->id, $at);
            $shiftId = (string)($input['roster_item_id'] ?? '');
            $shift = null;
            foreach ($candidates as $candidate) {
                if ((string)$candidate['id'] === $shiftId) $shift = $candidate;
            }
            if ($shiftId !== '' && !$shift) throw new \DomainException('เวรที่เลือกไม่ใช่เวรของคุณหรือยังไม่ประกาศใช้ กรุณาโหลดตารางเวรใหม่');
            if (!$shift && count($candidates) === 1) $shift = $candidates[0];
            if (!$shift && count($candidates) > 1) throw new \DomainException('มีหลายเวร กรุณาเลือกเวรที่จะลงเวลา');
            $recent = CheckinRecord::find()->where(['emp_id' => $employee->id, 'check_type' => $type])
                ->andWhere(['<>', 'status', CheckinRecord::STATUS_REJECTED])
                ->andWhere(['>=', 'checkin_at', date('Y-m-d H:i:s', strtotime($at . ' -30 seconds'))])->all();
            foreach ($recent as $previous) {
                $previousShift = RosterAttendance::forRecord($previous)['shift'];
                if (($previousShift['id'] ?? null) === ($shift['id'] ?? null)) {
                    $tx->commit();
                    return $previous;
                }
            }
            $record = new CheckinRecord();
            $record->emp_id = $employee->id;
            $record->checkin_at = $at;
            $record->method = $method;
            $record->check_type = $type;
            $record->lat = $input['lat'];
            $record->lng = $input['lng'];
            $record->location_id = $validation['location']->id ?? null;
            $record->is_in_location = $validation['inside'] ? 1 : 0;
            $record->out_of_location_reason = $validation['inside'] ? null : $reason;
            $record->qr_token = $token ?: null;
            $record->photo_path = $method === 'photo' ? $photo : null;
            $record->status = CheckinRecord::STATUS_PENDING;
            $record->data_json = [
                'request_id' => $requestId ?: null,
                'attendance' => RosterAttendance::evaluate($at, $type, $shift),
                'geofence' => $validation['meta'],
            ];
            if (!$record->save()) throw new \DomainException(implode(' ', $record->getFirstErrors()));
            $record->createApproveRecord();
            $tx->commit();
            return $record;
        } catch (\Throwable $e) {
            $tx->rollBack();
            throw $e;
        }
    }

    public static function approve(int $approveId, string $status, string $comment): void
    {
        if (!in_array($status, ['Pass', 'Reject'], true)) throw new \DomainException('สถานะอนุมัติไม่ถูกต้อง');
        if (mb_strlen($comment) > 2000) throw new \DomainException('ความเห็นยาวได้ไม่เกิน 2,000 ตัวอักษร');
        $db = Yii::$app->db;
        $tx = $db->beginTransaction();
        try {
            $approve = Approve::findOne(['id' => $approveId, 'name' => 'checkin', 'deleted_at' => null]);
            if (!$approve) throw new \DomainException('ไม่พบคำขออนุมัติ');
            $db->createCommand('SELECT id FROM {{%checkin_record}} WHERE id=:id FOR UPDATE', [':id' => (int)$approve->from_id])->queryScalar();
            if (!$approve->refresh() || $approve->deleted_at !== null) throw new \DomainException('คำขอนี้ถูกแทนที่แล้ว กรุณาโหลดรายการใหม่');
            $record = CheckinRecord::findOne((int)$approve->from_id);
            if (!$record || !AttendanceAccess::canReview($record)) throw new \yii\web\ForbiddenHttpException('ไม่มีสิทธิ์อนุมัติรายการนี้');
            if ($record->status !== CheckinRecord::STATUS_PENDING || $approve->status !== 'Pending') throw new \DomainException('รายการนี้ถูกพิจารณาแล้ว กรุณาโหลดรายการใหม่');
            $me = \app\components\UserHelper::GetEmployee();
            $approve->status = $status;
            $approve->emp_id = $me->id;
            $approve->updated_at = self::now();
            $approve->updated_by = Yii::$app->user->id;
            $approve->data_json = array_merge((array)$approve->data_json, ['approve_date' => self::now(), 'comment' => $comment, 'reviewer_emp_id' => (int)$me->id]);
            if (!$approve->save() || !$record->applyApproveResult($status, $comment, $me->id)) throw new \RuntimeException('Could not persist approval');
            $tx->commit();
        } catch (\Throwable $e) {
            $tx->rollBack();
            throw $e;
        }
    }
}
