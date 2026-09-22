<?php

namespace app\modules\attendance\services;

use Yii;
use app\modules\hr\models\Employees;
use app\modules\attendance\models\CheckinRecord;

class AttendanceCsv
{
    /** Header-based import. Any invalid row rolls back the entire file. */
    public static function import($handle): array
    {
        if (Yii::$app->user->isGuest || (!Yii::$app->user->can('admin') && !Yii::$app->user->can('hr') && !Yii::$app->user->can('attendance'))) throw new \yii\web\ForbiddenHttpException('ไม่มีสิทธิ์นำเข้า');
        $result = ['saved' => 0, 'skipped' => 0, 'errors' => [], 'lineNo' => 0];
        $header = fgetcsv($handle);
        if (!$header) { $result['errors'][] = 'ไม่พบหัวตาราง CSV'; return $result; }
        $header = array_map(static fn($v) => trim((string)$v), $header);
        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
        $identifiers = array_values(array_intersect(['emp_id', 'cid', 'code'], $header));
        $allowed = ['emp_id', 'cid', 'code', 'checkin_at', 'check_type', 'method', 'lat', 'lng', 'out_of_location_reason', 'roster_item_id'];
        if (count($identifiers) !== 1 || !in_array('checkin_at', $header, true) || count(array_unique($header)) !== count($header) || array_diff($header, $allowed)) {
            $result['errors'][] = 'หัวตารางต้องมี emp_id หรือ cid หรือ code เพียงหนึ่งคอลัมน์ และ checkin_at โดยชื่อคอลัมน์ต้องไม่ซ้ำ';
            return $result;
        }
        $identifier = $identifiers[0];
        $tx = Yii::$app->db->beginTransaction();
        try {
            $line = 1;
            while (($values = fgetcsv($handle)) !== false) {
                $line++;
                if ($values === [null]) continue;
                $result['lineNo']++;
                if ($result['lineNo'] > 5000) { $result['errors'][] = 'นำเข้าได้ไม่เกิน 5,000 แถวต่อไฟล์'; break; }
                try {
                    if (count($values) !== count($header)) throw new \DomainException('จำนวนคอลัมน์ไม่ตรงกับหัวตาราง');
                    $row = array_combine($header, array_map(static fn($v) => trim((string)$v), $values));
                    if (!mb_check_encoding(implode('', $row), 'UTF-8')) throw new \DomainException('กรุณาบันทึกไฟล์เป็น UTF-8');
                    if ($row[$identifier] === '') throw new \DomainException('ไม่ได้ระบุพนักงาน');
                    if ($identifier === 'emp_id' && !ctype_digit($row[$identifier])) throw new \DomainException('emp_id ต้องเป็นรหัสตัวเลข');
                    // Legacy "code" means CID, never guess a CID as an internal database id.
                    $employee = Employees::findOne([$identifier === 'emp_id' ? 'id' : 'cid' => $row[$identifier]]);
                    if (!$employee) throw new \DomainException('ไม่พบพนักงานตามรหัสที่ระบุ');
                    $row['checkin_at'] = AttendanceCorrection::timestamp($row['checkin_at']);
                    $row['check_type'] = $row['check_type'] ?? 'in';
                    if (!in_array($row['check_type'], ['in', 'out'], true)) throw new \DomainException('check_type ต้องเป็น in หรือ out');
                    $sourceMethod = $row['method'] ?? 'manual';
                    if (!in_array($sourceMethod, ['manual', 'photo', 'qrcode', 'csv'], true)) throw new \DomainException('method ไม่ถูกต้อง');
                    $lat = $row['lat'] ?? '';
                    $lng = $row['lng'] ?? '';
                    if (($lat === '') !== ($lng === '') || ($lat !== '' && (!is_numeric($lat) || !is_numeric($lng) || !is_finite((float)$lat) || !is_finite((float)$lng) || abs((float)$lat) > 90 || abs((float)$lng) > 180))) throw new \DomainException('พิกัดต้องครบคู่และอยู่ในช่วงที่ถูกต้อง');
                    $reason = $row['out_of_location_reason'] ?? '';
                    if (mb_strlen($reason) > 1800) throw new \DomainException('เหตุผลยาวเกิน 1,800 ตัวอักษร');
                    $shift = AttendanceCorrection::shift((int)$employee->id, $row['checkin_at'], $row['roster_item_id'] ?? '');
                    $db = Yii::$app->db;
                    $db->createCommand('SELECT id FROM {{%employees}} WHERE id=:id FOR UPDATE', [':id' => $employee->id])->queryScalar();
                    if (CheckinRecord::find()->where(['emp_id' => $employee->id, 'checkin_at' => $row['checkin_at'], 'check_type' => $row['check_type']])->exists()) {
                        $result['skipped']++;
                        continue;
                    }
                    $record = new CheckinRecord();
                    $record->emp_id = $employee->id;
                    $record->checkin_at = $row['checkin_at'];
                    $record->check_type = $row['check_type'];
                    $record->method = CheckinRecord::METHOD_IMPORT;
                    $record->lat = $lat === '' ? null : $lat;
                    $record->lng = $lng === '' ? null : $lng;
                    $record->is_in_location = 0;
                    $record->out_of_location_reason = 'นำเข้าจาก CSV — ไม่ได้ตรวจ GPS ขณะบันทึก' . ($reason !== '' ? ': ' . $reason : '');
                    $record->status = CheckinRecord::STATUS_PENDING;
                    $record->data_json = ['source' => 'csv', 'source_method' => $sourceMethod, 'gps_verified' => false, 'imported_at' => AttendanceService::now(), 'attendance' => RosterAttendance::evaluate($record->checkin_at, $record->check_type, $shift)];
                    if (!$record->save()) throw new \DomainException(implode(' ', $record->getFirstErrors()));
                    $record->createApproveRecord();
                    $result['saved']++;
                } catch (\DomainException $e) {
                    $result['errors'][] = 'บรรทัด ' . $line . ': ' . $e->getMessage();
                }
            }
            if ($result['errors']) {
                $tx->rollBack();
                $result['saved'] = 0;
                $result['skipped'] = 0;
            } else {
                $tx->commit();
            }
        } catch (\Throwable $e) {
            $tx->rollBack();
            Yii::error($e, __METHOD__);
            $result['saved'] = 0;
            $result['skipped'] = 0;
            $result['errors'][] = 'นำเข้าไม่สำเร็จ ระบบย้อนกลับทั้งไฟล์แล้ว กรุณาติดต่อผู้ดูแลระบบ';
        }
        return $result;
    }
}
