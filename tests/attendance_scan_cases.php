<?php
// Included by attendance_integration.php inside its rollback-only transaction.
use app\modules\attendance\services\ScanMatcher;
use app\modules\attendance\services\WorkScheduleService;
use app\modules\attendance\models\WorkSchedule;
use app\modules\attendance\services\AttendanceService;
use app\modules\attendance\services\AttendanceAccess;
use app\modules\attendance\services\RosterAttendance;
use app\modules\attendance\models\CheckinRecord;
use app\modules\hr\models\Employees;

$sample=['id'=>'normal:test','emp_id'=>(int)$employee->id,'work_date'=>'2026-09-10','name'=>'test','start'=>'2026-09-10 08:00:00','end'=>'2026-09-10 16:00:00'];
verifyAttendance('automatic morning scan matches start', ScanMatcher::match('2026-09-10 07:55:00',[$sample])['type']==='in');
verifyAttendance('automatic evening scan matches end', ScanMatcher::match('2026-09-10 16:05:00',[$sample])['type']==='out');
verifyAttendance('midpoint scan stays unresolved', ScanMatcher::match('2026-09-10 12:00:00',[$sample])['type']==='scan');
verifyAttendance('outside matching window stays unresolved', ScanMatcher::match('2026-09-10 01:00:00',[$sample])['type']==='scan');
verifyAttendance('overlapping boundaries stay unresolved', ScanMatcher::match('2026-09-10 08:00:00',[$sample,array_merge($sample,['id'=>'other'])])['type']==='scan');
verifyAttendance('night end is automatic checkout', ScanMatcher::match($shift['end'],[$shift])['type']==='out');
verifyAttendance('missing schedule is raw scan', ScanMatcher::match($at,[])['type']==='scan');

$app->user->reviewer=true;
$normal=new WorkSchedule(['name'=>$tag,'start_time'=>'08:00','end_time'=>'16:00','weekdays'=>'1,2,3,4,5,6,7','grace_minutes'=>0,'window_minutes'=>240]);
$normal->created_at=AttendanceService::now(); $normal->created_by=$app->user->id;
verifyAttendance('normal schedule validates and saves', $normal->save());
$badSchedule=new WorkSchedule(['name'=>'bad','start_time'=>'25:00','end_time'=>'16:00','weekdays'=>'8','holidays'=>'2026-02-30','grace_minutes'=>-1,'window_minutes'=>0]);
verifyAttendance('invalid normal schedule rejected', !$badSchedule->validate());
$assign=['scope'=>'department','target_id'=>(string)$unit,'mode'=>'normal','schedule_id'=>(string)$normal->id,'effective_from'=>$date,'reason'=>'test assignment'];
WorkScheduleService::assign($assign);
$empArray=['id'=>(int)$employee->id,'department'=>$unit,'work_shift'=>'normal'];
$resolved=WorkScheduleService::resolve($empArray,$date);
verifyAttendance('normal employee inherits unit schedule', $resolved['source']==='หน่วยงาน' && $resolved['schedule']->id===$normal->id);
verifyAttendance('shift worker never inherits normal unit time', WorkScheduleService::resolve(array_merge($empArray,['work_shift'=>'shift']),$date)['schedule']===null);
WorkScheduleService::assign(array_merge($assign,['scope'=>'employee','target_id'=>(string)$employee->id]));
verifyAttendance('individual assignment takes precedence', WorkScheduleService::resolve($empArray,$date)['source']==='รายบุคคล');
$tomorrow=date('Y-m-d',strtotime($date.' +1 day'));
WorkScheduleService::assign(array_merge($assign,['scope'=>'employee','target_id'=>(string)$employee->id,'mode'=>'shift','schedule_id'=>'','effective_from'=>$tomorrow]));
verifyAttendance('future assignment does not alter today', WorkScheduleService::resolve($empArray,$date)['mode']==='normal');
verifyAttendance('assignment starts on effective date', WorkScheduleService::resolve($empArray,$tomorrow)['mode']==='shift');
deniedAttendance('retroactive assignment blocked',fn()=>WorkScheduleService::assign(array_merge($assign,['effective_from'=>date('Y-m-d',strtotime($date.' -1 day'))])));
verifyAttendance('explicit roster day suppresses normal fallback', WorkScheduleService::shifts([(int)$employee->id],$date,$date,[$employee->id=>[$date=>true]])===[]);
$db->createCommand()->update('roster_period',['status'=>'published'],['id'=>$periodId])->execute();
$todayShifts=RosterAttendance::shifts([(int)$employee->id],$date,$date);
verifyAttendance('published roster wins over individual normal hours', !array_filter($todayShifts,fn($s)=>($s['source']??'')==='normal'));

$future=date('Y-m-d',strtotime($date.' +2 days'));
WorkScheduleService::assign(array_merge($assign,['scope'=>'employee','target_id'=>(string)$employee->id,'effective_from'=>$future]));
$normalDays=WorkScheduleService::shifts([(int)$employee->id],$future,date('Y-m-d',strtotime($future.' +30 days')),[]);
verifyAttendance('normal hours generate scheduled work dates', count($normalDays)>0 && substr($normalDays[0]['start'],11,5)==='08:00' && substr($normalDays[0]['end'],11,5)==='16:00');
$normalDay=$normalDays[0]['work_date'];
$holidaySchedule=new WorkSchedule(['name'=>$tag.' holiday','start_time'=>'08:00','end_time'=>'16:00','weekdays'=>'1,2,3,4,5,6,7','holidays'=>$normalDay,'grace_minutes'=>0,'window_minutes'=>240]);
$holidaySchedule->created_at=AttendanceService::now();$holidaySchedule->created_by=$app->user->id;
if (!$holidaySchedule->save()) throw new RuntimeException('Holiday fixture failed');
WorkScheduleService::assign(array_merge($assign,['scope'=>'employee','target_id'=>(string)$employee->id,'effective_from'=>$future,'schedule_id'=>(string)$holidaySchedule->id]));
verifyAttendance('group holiday has no automatic normal shift', WorkScheduleService::shifts([(int)$employee->id],$normalDay,$normalDay,[])===[]);
verifyAttendance('stored schedule snapshot survives reassignment', RosterAttendance::evaluate($normalDay.' 08:10:00','in',$normalDays[0])['late_minutes']===10);
$normalRecord=new CheckinRecord(['emp_id'=>$employee->id,'checkin_at'=>$normalDay.' 08:10:00','check_type'=>'in','status'=>'approved','data_json'=>['attendance'=>['shift'=>$normalDays[0]]]]);
$normalSummary=RosterAttendance::summarize([array_merge($normalDays[0],['id'=>'normal:changed:'.$normalDay])],[$normalRecord],$normalDay.' 20:00:00');
verifyAttendance('normal reassignment does not invent a second missing shift', $normalSummary[$employee->id][$normalDay]['shifts']===1 && $normalSummary[$employee->id][$normalDay]['missing']===0);

$scanEmployee=Employees::findOne($people[1]['id']);
$app->user->setIdentity(new AttendanceTestIdentity($people[1]['user_id']));
// Isolate today's duplicate window without changing any data outside the transaction.
$db->createCommand()->update('checkin_record',['status'=>'rejected'],['and',['emp_id'=>$scanEmployee->id],['>=','checkin_at',date('Y-m-d H:i:s',strtotime($at.' -5 minutes'))]])->execute();
$scanInput=['method'=>'qrcode','lat'=>0,'lng'=>0,'qr_token'=>$tag,'request_id'=>$tag.'-scan'];
$raw=AttendanceService::record($scanEmployee,$scanInput,true);
verifyAttendance('one-button scan preserves original timestamp and GPS', $raw->data_json['raw_scan']['at']===$raw->checkin_at && (float)$raw->data_json['raw_scan']['lat']===0.0);
$before=CheckinRecord::find()->where(['emp_id'=>$scanEmployee->id])->count();
$duplicate=AttendanceService::record($scanEmployee,array_merge($scanInput,['request_id'=>$tag.'-duplicate','check_type'=>'out']),true);
verifyAttendance('two-minute dedup ignores incoming direction', $duplicate->id===$raw->id && $duplicate->wasDuplicate && CheckinRecord::find()->where(['emp_id'=>$scanEmployee->id])->count()===$before);
$db->createCommand()->update('checkin_record',['checkin_at'=>date('Y-m-d H:i:s',strtotime($at.' -5 minutes'))],['id'=>$raw->id])->execute();
$aliasRetry=AttendanceService::record($scanEmployee,array_merge($scanInput,['request_id'=>$tag.'-duplicate']),true);
verifyAttendance('duplicate request alias remains idempotent after cooldown', $aliasRetry->id===$raw->id && $aliasRetry->wasDuplicate);
verifyAttendance('pending inbox excludes own scan', !AttendanceAccess::pendingQuery()->andWhere(['checkin_record.id'=>$raw->id])->exists());
verifyAttendance('approval badge equals actionable inbox', \app\components\ApproveHelper::Checkin()['total']===(int)AttendanceAccess::pendingQuery()->count());
$app->user->reviewer=false;
deniedAttendance('employee cannot set own work hours',fn()=>WorkScheduleService::assign(array_merge($assign,['scope'=>'employee','target_id'=>(string)$scanEmployee->id])));
$app->user->reviewer=true;
$site=\app\models\Categorise::findOne(['name'=>'site']);
if (!$site) throw new RuntimeException('Expected configured test site');
$siteData=is_array($site->data_json)?$site->data_json:json_decode($site->data_json,true);
$siteData['director_name']=(string)$scanEmployee->id;
$db->createCommand()->update(\app\models\Categorise::tableName(),['data_json'=>json_encode($siteData)],['id'=>$site->id])->execute();
$app->user->reviewer=false;
verifyAttendance('configured director can review without HR role', AttendanceAccess::isReviewer() && AttendanceAccess::canReview(CheckinRecord::findOne($record->id)));
verifyAttendance('configured director still cannot approve own scan', !AttendanceAccess::canReview($raw));
$app->user->reviewer=true;
$app->request->setQueryParams(['CheckinRecordSearch'=>['emp_id'=>$employee->id]]);
$historyController=new class('checkin',$module) extends \app\modules\attendance\controllers\CheckinController {
    public $visible=[];
    public function render($view,$params=[]) { $this->visible=$params['dataProvider']->getModels(); return ''; }
};
$historyController->actionIndex();
verifyAttendance('personal history cannot expose requested other employee even for HR', !array_filter($historyController->visible,fn($r)=>(int)$r->emp_id!==(int)$scanEmployee->id));
$app->request->setQueryParams([]);
