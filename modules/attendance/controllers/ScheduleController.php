<?php
namespace app\modules\attendance\controllers;

use Yii;
use app\modules\attendance\models\WorkSchedule;
use app\modules\attendance\services\WorkScheduleService;
use app\modules\attendance\services\AttendanceService;
use app\modules\attendance\services\ScheduleDirectory;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;
use yii\data\ArrayDataProvider;

class ScheduleController extends \yii\web\Controller
{
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) return false;
        if (!WorkScheduleService::ready()) throw new \yii\web\HttpException(503, 'กรุณารัน migration การตั้งค่าเวลางานก่อน');
        return true;
    }
    public function actionIndex($tab = 'departments', $q = '', $department = '', $schedule_id = null)
    {
        if (!WorkScheduleService::manager()) throw new \yii\web\ForbiddenHttpException('สำหรับผู้ดูแลลงเวลา');
        if (Yii::$app->request->isPost) return $this->actionCreate();
        if (!in_array($tab, ['departments','employees','schedules'], true) || !is_string($q) || !is_string($department) || ($department !== '' && !ctype_digit($department))) throw new \yii\web\BadRequestHttpException('ตัวกรองไม่ถูกต้อง');
        $directory = new ScheduleDirectory();
        $preset = $this->presetSchedule($schedule_id);
        $today = substr(AttendanceService::now(), 0, 10);
        $rows = $tab === 'departments' ? array_values($directory->nodes) : ($tab === 'employees' ? $directory->employees : array_values($directory->schedules));
        $rows = array_values(array_filter($rows, function ($row) use ($tab, $q, $department, $directory) {
            $name = $tab === 'employees' ? $row['fname'].' '.$row['lname'] : $row['name'];
            if ($q !== '' && mb_stripos($name, trim($q)) === false) return false;
            return $tab !== 'employees' || $department === '' || in_array((int)$department, $directory->chains[$row['department']] ?? [(int)$row['department']]);
        }));
        $provider = new ArrayDataProvider(['allModels'=>$rows,'pagination'=>['pageSize'=>20],'sort'=>false]);
        return $this->render('index', compact('tab','q','department','directory','today','provider','preset'));
    }
    private function presetSchedule($id): ?WorkSchedule
    {
        if ($id === null || $id === '') return null;
        if (!is_scalar($id) || !ctype_digit((string)$id) || !($schedule = WorkSchedule::findOne($id))) throw new \yii\web\NotFoundHttpException('ไม่พบชุดเวลา');
        return $schedule;
    }
    public function actionCreate($copy = null)
    {
        if (!WorkScheduleService::manager()) throw new \yii\web\ForbiddenHttpException('สำหรับผู้ดูแลลงเวลา');
        if (!WorkScheduleService::ready()) throw new \yii\web\HttpException(503, 'กรุณารัน migration การตั้งค่าเวลางานก่อน');
        $model = new WorkSchedule(['weekdays'=>'1,2,3,4,5','grace_minutes'=>0,'window_minutes'=>240]);
        $source = null;
        if ($copy !== null) {
            if (!is_scalar($copy) || !ctype_digit((string)$copy) || !($source = WorkSchedule::findOne($copy))) throw new \yii\web\NotFoundHttpException('ไม่พบชุดเวลา');
            $model->setAttributes($source->getAttributes(['name','start_time','end_time','weekdays','holidays','grace_minutes','window_minutes']));
        }
        $post=Yii::$app->request->post();
        if (isset($post['WorkSchedule']['weekdays']) && is_array($post['WorkSchedule']['weekdays'])) {
            $days=$post['WorkSchedule']['weekdays'];
            $post['WorkSchedule']['weekdays']=count(array_filter($days,'is_scalar'))===count($days)?implode(',',$days):'';
        }
        if (Yii::$app->request->isPost && $model->load($post)) {
            $model->created_at = AttendanceService::now(); $model->created_by = Yii::$app->user->id;
            if ($model->save()) { Yii::$app->session->setFlash('success','บันทึกชุดเวลาแล้ว เลือกหน่วยงานและกำหนดชุดเวลานี้เพื่อเริ่มใช้งาน'); return $this->redirect(['index','schedule_id'=>$model->id]); }
        }
        return $this->render('create', compact('model','source'));
    }
    public function actionChoose($target)
    {
        if (!is_string($target) || !preg_match('/^(employee|department):([0-9]+)$/D',$target,$parts)) throw new \yii\web\BadRequestHttpException('กรุณาเลือกหน่วยงานหรือพนักงาน');
        return $this->redirect(['assign','scope'=>$parts[1],'id'=>$parts[2]]);
    }
    public function actionAssign($scope = 'employee', $id = null, $schedule_id = null)
    {
        if (!in_array($scope, ['employee','department'], true) || !is_scalar($id) || !ctype_digit((string)$id) || !WorkScheduleService::canAssign($scope, (int)$id)) throw new \yii\web\ForbiddenHttpException('ไม่มีสิทธิ์กำหนดเวลาของเป้าหมายนี้');
        $target = $scope === 'employee' ? \app\modules\hr\models\Employees::findOne($id) : \app\modules\hr\models\Organization::findOne($id);
        if (!$target) throw new \yii\web\NotFoundHttpException('ไม่พบข้อมูล');
        $preset = $this->presetSchedule($schedule_id);
        $error = null; $values = Yii::$app->request->post('Assignment', []);
        $selected = Yii::$app->request->post('members', []);
        if (Yii::$app->request->isPost) {
            try {
                if (!is_array($values) || !is_array($selected)) throw new \DomainException('รูปแบบข้อมูลไม่ถูกต้อง');
                if ($scope === 'department' && ($values['apply_to'] ?? 'department') === 'members') {
                    $expected = Yii::$app->request->post('member_count');
                    if (!is_string($expected) || !ctype_digit($expected) || (int)$expected !== count($selected)) throw new \DomainException('รายชื่อที่ส่งมาไม่ครบ กรุณาเลือกจำนวนคนน้อยลงแล้วบันทึกอีกครั้ง');
                    $count = WorkScheduleService::assignMembers((int)$id, $selected, $values);
                    $message = 'บันทึกเวลาทำงานให้บุคลากรที่เลือก '.$count.' คนแล้ว';
                } else {
                    if ($scope === 'department' && ($values['apply_to'] ?? 'department') !== 'department') throw new \DomainException('เลือกวิธีกำหนดเวลาให้ถูกต้อง');
                    WorkScheduleService::assign(array_merge($values, ['scope'=>$scope,'target_id'=>(string)$id]));
                    $message = 'บันทึกเวลาทำงานแล้ว เริ่มมีผล '.$values['effective_from'].' พร้อมเก็บประวัติเดิม';
                }
                Yii::$app->session->setFlash('success',$message);
                return $this->redirect(['assign','scope'=>$scope,'id'=>$id]);
            } catch (\DomainException $e) { $error = $e->getMessage(); }
        }
        $values = array_filter((array)$values, 'is_string');
        $selected = array_filter((array)$selected, 'is_string');
        $directory = new ScheduleDirectory();
        $today = substr(AttendanceService::now(), 0, 10);
        $current = $scope === 'department' ? $directory->departmentState((int)$id, $today) : $directory->state($target->getAttributes(), $today);
        $upcoming = $directory->upcoming($scope, (int)$id, $today);
        $history = $directory->history[$scope][$id] ?? [];
        $members = $scope === 'department' ? $directory->members((int)$id) : [];
        $values += ['mode'=>$scope === 'department' || $preset ? 'normal' : ($current['source'] === 'รายบุคคล' ? $current['mode'] : 'inherit'),
            'schedule_id'=>(string)($preset->id ?? $current['schedule']->id ?? ''), 'effective_from'=>$today, 'reason'=>'', 'apply_to'=>'department'];
        $schedules = \yii\helpers\ArrayHelper::map($directory->schedules, 'id', fn($s)=>$s->name.' · '.$s->start_time.'–'.$s->end_time);
        return $this->render('assign', compact('scope','id','target','values','selected','error','schedules','history','directory','today','current','upcoming','members'));
    }
}
