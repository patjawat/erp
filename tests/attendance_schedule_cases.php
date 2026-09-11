<?php
// Included inside the integration suite's rollback-only transaction.
use app\modules\attendance\services\WorkScheduleService;
use app\modules\attendance\services\ScheduleDirectory;
use app\modules\hr\models\Employees;

$app->user->reviewer = true;
$ids = array_map('strval', array_column($people, 'id'));
$db->createCommand()->update('employees', ['department'=>$unit,'status'=>Employees::STATUS_WORKING], ['id'=>$ids])->execute();
$input = ['mode'=>'normal','schedule_id'=>(string)$normal->id,'effective_from'=>$date,'reason'=>'bulk schedule regression'];
$assignmentCount = fn() => (int)(new yii\db\Query())->from('attendance_assignment')->count();
$before = $assignmentCount();
$count = WorkScheduleService::assignMembers($unit, [$ids[0],$ids[1],$ids[0]], $input);
verifyAttendance('bulk assignment saves each selected employee once', $count === 2 && $assignmentCount() === $before + 2);
$directory = new ScheduleDirectory();
foreach ($ids as $id) {
    $state = $directory->state(['id'=>$id,'department'=>$unit,'work_shift'=>'normal'],$date);
    verifyAttendance('bulk assignment is visible in current employee schedule '.$id, $state['source'] === 'รายบุคคล' && (int)$state['schedule']->id === (int)$normal->id);
}
$before = $assignmentCount();
deniedAttendance('bulk assignment rejects a member outside the department atomically', fn() => WorkScheduleService::assignMembers($unit, [$ids[0],'0'], $input));
verifyAttendance('invalid bulk member leaves no partial assignments', $assignmentCount() === $before);
deniedAttendance('bulk assignment rejects empty selection', fn() => WorkScheduleService::assignMembers($unit, [], $input));
deniedAttendance('bulk assignment rejects malformed member ids', fn() => WorkScheduleService::assignMembers($unit, [[$ids[0]]], $input));
$db->createCommand()->update('employees', ['status'=>'0'], ['id'=>$ids[1]])->execute();
deniedAttendance('bulk assignment rejects inactive members', fn() => WorkScheduleService::assignMembers($unit, [$ids[0],$ids[1]], $input));
verifyAttendance('inactive member also rolls back earlier writes', $assignmentCount() === $before);
$db->createCommand()->update('employees', ['status'=>Employees::STATUS_WORKING], ['id'=>$ids[1]])->execute();
WorkScheduleService::assignMembers($unit, [$ids[0]], array_merge($input,['mode'=>'inherit','schedule_id'=>'']));
$state = WorkScheduleService::resolve(['id'=>$ids[0],'department'=>$unit,'work_shift'=>'normal'],$date);
verifyAttendance('bulk change can restore department inheritance', $state['source'] === 'หน่วยงาน');
$state = WorkScheduleService::resolve(['id'=>$ids[0],'department'=>$unit,'work_shift'=>'shift'],$date);
verifyAttendance('restoring inheritance retains shift-worker policy', $state['mode'] === 'shift' && $state['schedule'] === null);
$before = $assignmentCount();
$leader = \app\components\UserHelper::GetEmployee();
$node = \app\modules\hr\models\Organization::findOne($unit);
$unitData = (array)$node->data_json;
$unitData['leader1'] = $leader->id;
$db->createCommand()->update(\app\modules\hr\models\Organization::tableName(), ['data_json'=>json_encode($unitData)], ['id'=>$unit])->execute();
$app->user->reviewer = false;
verifyAttendance('bulk permission fixture is a department leader', WorkScheduleService::canAssign('department',$unit));
deniedAttendance('bulk assignment cannot bypass per-employee permission on self', fn() => WorkScheduleService::assignMembers($unit, [(string)\app\components\UserHelper::GetEmployee()->id], $input));
verifyAttendance('permission denial writes nothing', $assignmentCount() === $before);
$app->user->reviewer = true;

class AttendanceScheduleTestController extends \app\modules\attendance\controllers\ScheduleController
{
    public function render($view, $params = []) { return $params; }
}
$requestBody = $app->request->getBodyParams();
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? null;
try {
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $app->request->setBodyParams(['Assignment'=>array_merge($input,['apply_to'=>'members']), 'members'=>[$ids[0]], 'member_count'=>'2']);
    $scheduleController = new AttendanceScheduleTestController('schedule', new \app\modules\attendance\Module('attendance'));
    $result = $scheduleController->actionAssign('department',(string)$unit);
    verifyAttendance('truncated bulk form is rejected before any write', $result['error'] !== null && $assignmentCount() === $before);
} finally {
    $app->request->setBodyParams($requestBody);
    if ($requestMethod === null) unset($_SERVER['REQUEST_METHOD']); else $_SERVER['REQUEST_METHOD'] = $requestMethod;
}
