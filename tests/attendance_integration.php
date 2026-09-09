<?php
/** Run in the authorised test container. All fixtures and attendance writes are rolled back. */
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';
$app = new yii\console\Application(require __DIR__ . '/../config/console.php');

use app\modules\attendance\services\AttendanceService;
use app\modules\attendance\services\AttendanceAccess;
use app\modules\attendance\services\RosterAttendance;
use app\modules\attendance\models\CheckinRecord;
use app\modules\attendance\models\CheckinLocation;
use app\modules\approveV2\models\Approve;
use app\modules\hr\models\Employees;

class AttendanceTestIdentity implements yii\web\IdentityInterface
{
    public $id;
    public function __construct($id) { $this->id = $id; }
    public static function findIdentity($id) { return new self($id); }
    public static function findIdentityByAccessToken($token, $type = null) { return null; }
    public function getId() { return $this->id; }
    public function getAuthKey() { return ''; }
    public function validateAuthKey($authKey) { return false; }
}
class AttendanceTestUser extends yii\web\User
{
    public $reviewer = false;
    public function can($permissionName, $params = [], $allowCaching = true) { return $this->reviewer && $permissionName === 'hr'; }
}
$app->set('user', ['class' => AttendanceTestUser::class, 'identityClass' => AttendanceTestIdentity::class, 'enableSession' => false]);
$pass = 0;
function verifyAttendance($label, $condition) {
    global $pass;
    if (!$condition) throw new RuntimeException('FAIL: ' . $label);
    $pass++;
    echo "PASS: $label\n";
}
function deniedAttendance($label, callable $fn) {
    try { $fn(); } catch (DomainException | yii\web\ForbiddenHttpException $e) { verifyAttendance($label, true); return; }
    throw new RuntimeException('FAIL: ' . $label . ' was accepted');
}
$db = $app->db;
$tx = $db->beginTransaction();
try {
    $people = (new yii\db\Query())->select(['id', 'user_id', 'department'])->from('employees')
        ->where(['not', ['user_id' => null]])->andWhere(['>', 'department', 0])->limit(2)->all();
    if (count($people) < 2) throw new RuntimeException('Need two employee profiles in the test database');
    $app->user->setIdentity(new AttendanceTestIdentity($people[0]['user_id']));
    $employee = Employees::findOne($people[0]['id']);
    $unit = (int)$employee->department;
    $at = AttendanceService::now();
    $date = substr($at, 0, 10);
    $tag = 'attendance-test-' . bin2hex(random_bytes(4));
    $typeId = (new yii\db\Query())->select('id')->from('roster_shift_type')->where(['is_off' => 0])->scalar();
    $db->createCommand()->insert('roster_unit_shift', ['unit_id' => $unit, 'shift_type_id' => $typeId, 'name' => $tag, 'start_time' => '23:00:00', 'end_time' => '07:00:00', 'cross_midnight' => 1, 'active' => 1])->execute();
    $unitShiftId = $db->getLastInsertID();
    $period = (new yii\db\Query())->from('roster_period')->where(['unit_id' => $unit, 'month' => (int)date('n'), 'year_ce' => (int)date('Y')])->one();
    if (!$period) {
        $db->createCommand()->insert('roster_period', ['unit_id' => $unit, 'month' => (int)date('n'), 'year_ce' => (int)date('Y'), 'title' => $tag, 'status' => 'published'])->execute();
        $periodId = $db->getLastInsertID();
    } else {
        $periodId = $period['id'];
        $db->createCommand()->update('roster_period', ['status' => 'published', 'deleted_at' => null], ['id' => $periodId])->execute();
    }
    $db->createCommand()->insert('roster_item', ['period_id' => $periodId, 'emp_id' => $employee->id, 'work_date' => $date, 'unit_shift_id' => $unitShiftId, 'shift_type_id' => $typeId, 'status' => 'planned'])->execute();
    $itemId = $db->getLastInsertID();
    $location = new CheckinLocation(['name' => $tag, 'lat' => 0, 'lng' => 0, 'radius_m' => 100, 'qr_token' => $tag, 'active' => 1]);
    verifyAttendance('create test GPS location', $location->save());
    verifyAttendance('GPS mandatory even without a QR', !CheckinLocation::validateClockIn(null, null, null)['ok']);
    verifyAttendance('invalid latitude rejected', !CheckinLocation::validateClockIn(91, 0, $tag)['ok']);
    verifyAttendance('array coordinates rejected', !CheckinLocation::validateClockIn([], 0, $tag)['ok']);
    verifyAttendance('zero coordinates accepted', CheckinLocation::validateClockIn(0, 0, $tag)['inside']);
    verifyAttendance('outside requires a reason', !CheckinLocation::validateClockIn(1, 1, $tag)['ok']);
    verifyAttendance('outside plus reason accepted as outside', !CheckinLocation::validateClockIn(1, 1, $tag, 'test outside')['inside']);
    verifyAttendance('unknown QR rejected', !CheckinLocation::validateClockIn(0, 0, 'missing-token')['ok']);
    $shifts = RosterAttendance::candidates((int)$employee->id, $at);
    $shift = array_values(array_filter($shifts, fn($s) => $s['id'] === (int)$itemId))[0] ?? null;
    verifyAttendance('published roster candidate loaded', $shift !== null);
    verifyAttendance('overnight end falls on next day', substr($shift['end'], 0, 10) === date('Y-m-d', strtotime($date . ' +1 day')));
    $late = RosterAttendance::evaluate($date . ' 23:12:00', 'in', $shift);
    verifyAttendance('late based on shift start', $late['late_minutes'] === 12);
    $early = RosterAttendance::evaluate(substr($shift['end'], 0, 10) . ' 06:45:00', 'out', $shift);
    verifyAttendance('checkout is never late', $early['late_minutes'] === null);
    verifyAttendance('overnight early checkout computed', $early['early_minutes'] === 15);
    verifyAttendance('unmatched shift has no assumed late time', RosterAttendance::evaluate($at, 'in', null)['late_minutes'] === null);
    $input = ['method' => 'qrcode', 'check_type' => 'in', 'lat' => 0, 'lng' => 0, 'qr_token' => $tag, 'roster_item_id' => (string)$itemId, 'request_id' => str_repeat('a', 20)];
    deniedAttendance('missing QR is rejected', fn() => AttendanceService::record($employee, array_merge($input, ['qr_token' => ''])));
    deniedAttendance('invalid type is rejected', fn() => AttendanceService::record($employee, array_merge($input, ['check_type' => 'invalid'])));
    deniedAttendance('another/nonexistent roster is rejected', fn() => AttendanceService::record($employee, array_merge($input, ['roster_item_id' => '999999999'])));
    $record = AttendanceService::record($employee, $input);
    verifyAttendance('checkin and approval created together', Approve::find()->where(['name' => 'checkin', 'from_id' => (string)$record->id])->exists());
    verifyAttendance('shift snapshot persisted', RosterAttendance::forRecord($record)['shift']['id'] === (int)$itemId);
    verifyAttendance('retry returns same record', AttendanceService::record($employee, $input)->id === $record->id);
    verifyAttendance('separate rapid duplicate returns same record', AttendanceService::record($employee, array_merge($input, ['request_id' => str_repeat('b', 20)]))->id === $record->id);
    $outside = AttendanceService::record($employee, array_merge($input, ['check_type' => 'out', 'lat' => 1, 'lng' => 1, 'out_of_location_reason' => 'test off site', 'request_id' => str_repeat('c', 20)]));
    verifyAttendance('outside reason persisted pending', (int)$outside->is_in_location === 0 && $outside->status === 'pending' && $outside->out_of_location_reason === 'test off site');
    $approval = Approve::findOne(['name' => 'checkin', 'from_id' => (string)$record->id]);
    $app->user->reviewer = true;
    deniedAttendance('self approval forbidden even for reviewer', fn() => AttendanceService::approve((int)$approval->id, 'Pass', ''));
    $app->user->setIdentity(new AttendanceTestIdentity($people[1]['user_id']));
    $app->user->reviewer = false;
    // Force an unrelated supervisor assignment for this access test only.
    $record->populateRelation('employee', new class extends Employees { public function supervisorEmpId() { return null; } });
    verifyAttendance('ordinary user cannot read another employee', !AttendanceAccess::canView($record));
    $app->user->reviewer = true;
    deniedAttendance('invalid approval state rejected', fn() => AttendanceService::approve((int)$approval->id, 'unexpected', ''));
    AttendanceService::approve((int)$approval->id, 'Pass', 'integration test');
    $record->refresh(); $approval->refresh();
    verifyAttendance('record and approval statuses synchronised', $record->status === 'approved' && $approval->status === 'Pass');
    verifyAttendance('actual reviewer recorded', (int)$record->approved_by === (int)$people[1]['id']);
    deniedAttendance('second decision is rejected', fn() => AttendanceService::approve((int)$approval->id, 'Reject', ''));
    $db->createCommand()->update('roster_period', ['status' => 'draft'], ['id' => $periodId])->execute();
    verifyAttendance('draft roster excluded', !array_filter(RosterAttendance::candidates((int)$employee->id, $at), fn($s) => $s['id'] === (int)$itemId));
    // Compare after-midnight scan against the starting work date in monthly output.
    $record->checkin_at = $date . ' 23:12:00';
    $summary = RosterAttendance::summarize([$shift], [$record], date('Y-m-d H:i:s', strtotime($date . ' +2 days')));
    verifyAttendance('monthly late attaches to starting work date', $summary[$employee->id][$date]['late'] === 1);
    $record->status = 'pending';
    $summary = RosterAttendance::summarize([$shift], [$record], date('Y-m-d H:i:s', strtotime($date . ' +2 days')));
    verifyAttendance('pending is not final late or missing', $summary[$employee->id][$date]['late'] === 0 && $summary[$employee->id][$date]['missing'] === 0 && $summary[$employee->id][$date]['pending'] === 1);
    $db->createCommand()->update('roster_period', ['status' => 'published'], ['id' => $periodId])->execute();
    $record->refresh();
    $originalRevision = \app\modules\attendance\services\AttendanceCorrection::revision($record);
    $correction = ['checkin_at' => $record->checkin_at, 'check_type' => 'in', 'roster_item_id' => (string)$itemId, 'reason' => 'แก้ไขเพื่อทดสอบ', 'revision' => $originalRevision];
    deniedAttendance('amendment requires a reason', fn() => \app\modules\attendance\services\AttendanceCorrection::amend((int)$record->id, array_merge($correction, ['reason' => ''])));
    deniedAttendance('impossible date rejected', fn() => \app\modules\attendance\services\AttendanceCorrection::timestamp('2026-02-30 08:00:00'));
    deniedAttendance('future timestamp rejected', fn() => \app\modules\attendance\services\AttendanceCorrection::timestamp('2099-01-01 08:00:00'));
    $amended = \app\modules\attendance\services\AttendanceCorrection::amend((int)$record->id, array_merge($correction, ['emp_id' => '999', 'status' => 'approved']));
    verifyAttendance('amendment resets approval and ignores unsafe fields', $amended->status === 'pending' && $amended->approved_at === null && (int)$amended->emp_id === (int)$employee->id);
    verifyAttendance('original decision preserved in audit', $amended->data_json['amendments'][0]['before']['status'] === 'approved');
    verifyAttendance('original recording evidence preserved', $amended->method === 'qrcode' && $amended->qr_token === $tag);
    $approval->refresh();
    verifyAttendance('previous approval archived', $approval->deleted_at !== null && $approval->status === 'Pass');
    $newApproval = Approve::findOne(['name' => 'checkin', 'from_id' => (string)$record->id, 'deleted_at' => null]);
    verifyAttendance('fresh approval is pending', $newApproval && $newApproval->id !== $approval->id && $newApproval->status === 'Pending');
    deniedAttendance('stale edit cannot overwrite new revision', fn() => \app\modules\attendance\services\AttendanceCorrection::amend((int)$record->id, $correction));
    deniedAttendance('archived approval cannot decide revised record', fn() => AttendanceService::approve((int)$approval->id, 'Pass', ''));
    $csv = static function (string $text): array {
        $handle = fopen('php://temp', 'r+'); fwrite($handle, $text); rewind($handle);
        try { return \app\modules\attendance\services\AttendanceCsv::import($handle); } finally { fclose($handle); }
    };
    $importAt = date('Y-m-d H:i:s', strtotime($at . ' -2 hours'));
    $csvText = "\xEF\xBB\xBFemp_id,checkin_at,check_type,roster_item_id\n{$employee->id},{$importAt},in,{$itemId}\n{$employee->id},{$importAt},in,{$itemId}\n";
    $import = $csv($csvText);
    verifyAttendance('CSV accepts BOM, skips duplicate rows', !$import['errors'] && $import['saved'] === 1 && $import['skipped'] === 1);
    $imported = CheckinRecord::findOne(['emp_id' => $employee->id, 'checkin_at' => $importAt, 'check_type' => 'in']);
    verifyAttendance('import is pending and does not claim GPS proof', $imported && $imported->method === 'csv' && $imported->status === 'pending' && !$imported->data_json['gps_verified'] && (int)$imported->is_in_location === 0);
    $again = $csv($csvText);
    verifyAttendance('reimport is idempotent', $again['saved'] === 0 && $again['skipped'] === 2);
    $beforeCount = CheckinRecord::find()->count();
    $bad = $csv("emp_id,checkin_at,check_type\n{$employee->id},{$importAt},out\n{$employee->id},2026-02-30 08:00:00,in\n");
    verifyAttendance('invalid row rolls back all earlier imported rows', count($bad['errors']) === 1 && $bad['saved'] === 0 && CheckinRecord::find()->count() === $beforeCount);
    verifyAttendance('ambiguous CSV identifiers rejected', count($csv("emp_id,cid,checkin_at\n1,2,2026-01-01 08:00:00\n")['errors']) === 1);
    verifyAttendance('invalid CSV direction rejected', count($csv("emp_id,checkin_at,check_type\n{$employee->id},{$importAt},invalid\n")['errors']) === 1);
    $app->user->reviewer = false;
    deniedAttendance('ordinary user cannot import', fn() => $csv($csvText));
    $organisation = \app\modules\hr\models\Organization::findOne($unit);
    $orgData = is_array($organisation->data_json) ? $organisation->data_json : json_decode((string)$organisation->data_json, true);
    $orgData['leader1'] = (string)$people[1]['id'];
    $db->createCommand()->update('tree', ['data_json' => json_encode($orgData)], ['id' => $unit])->execute();
    $amended = CheckinRecord::findOne($record->id);
    verifyAttendance('current organisation supervisor can review', AttendanceAccess::canReview($amended));
    AttendanceService::approve((int)$newApproval->id, 'Pass', 'supervisor test');
    $amended->refresh();
    verifyAttendance('supervisor can approve without HR role', $amended->status === 'approved');
    verifyAttendance('review inbox includes supervised employee', in_array((int)$employee->id, AttendanceAccess::reviewableEmployeeIds(), true));
    $app->user->reviewer = true;
    $app->set('request', ['class' => yii\web\Request::class, 'cookieValidationKey' => 'attendance-test-only', 'scriptFile' => dirname(__DIR__) . '/web/index.php', 'scriptUrl' => '/index.php']);
    $app->set('response', ['class' => yii\web\Response::class]);
    $app->request->setQueryParams(['CheckinRecordSearch' => ['id' => $amended->id]]);
    $module = new app\modules\attendance\Module('attendance', $app);
    $controller = new app\modules\attendance\controllers\CheckinController('checkin', $module);
    $readWorkbook = static function ($response) {
        $handle = $response->stream[0];
        $path = stream_get_meta_data($handle)['uri'];
        try { return \PhpOffice\PhpSpreadsheet\IOFactory::load($path); }
        finally { fclose($handle); $response->stream = null; unlink($path); }
    };
    $export = $readWorkbook($controller->actionExportExcel())->getActiveSheet();
    $expected = RosterAttendance::forRecord($amended);
    verifyAttendance('Excel uses recorded shift name', $export->getCell('H3')->getValue() === $expected['shift']['name']);
    verifyAttendance('Excel late matches shared evaluation', (int)$export->getCell('J3')->getValue() === $expected['late_minutes']);
    verifyAttendance('Excel checkin does not invent early departure', $export->getCell('K3')->getValue() === '-');
    $app->request->setQueryParams([]);
    $monthly = $readWorkbook($controller->actionMonthlyExcel((int)date('n'), (int)date('Y') + 543, null, $unit))->getActiveSheet();
    verifyAttendance('monthly Excel generated from shared matrix', str_contains($monthly->getCell('A1')->getValue(), 'สรุปการลงเวลา'));
    $report = new ReflectionMethod($controller, 'buildMonthlyMatrix');
    $report->setAccessible(true);
    $matrix = $report->invoke($controller, (int)date('n'), (int)date('Y'), null, $unit);
    $excelRows = $monthly->getHighestDataRow() - 2;
    verifyAttendance('monthly Excel and screen matrix contain same employees', $excelRows === count($matrix['rows']));
    $days = $matrix['daysInMonth'];
    foreach ($matrix['rows'] as $index => $row) {
        $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(3 + $days + 4);
        if ((int)$monthly->getCell($column . ($index + 3))->getValue() !== $row['lateCount']) throw new RuntimeException('Excel monthly late totals differ');
    }
    verifyAttendance('all monthly Excel late totals match screen matrix', true);
    echo "SUCCESS: $pass checks; all fixture changes will be rolled back.\n";
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage() . "\n" . $e->getTraceAsString() . "\n");
    $failed = true;
} finally {
    $tx->rollBack();
}
exit(empty($failed) ? 0 : 1);
