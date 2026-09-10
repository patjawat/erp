<?php
namespace app\modules\attendance\controllers;

use Yii;
use app\modules\attendance\models\WorkSchedule;
use app\modules\attendance\services\WorkScheduleService;
use app\modules\attendance\services\AttendanceService;

class ScheduleController extends \yii\web\Controller
{
    public function actionIndex()
    {
        if (!WorkScheduleService::manager()) throw new \yii\web\ForbiddenHttpException('สำหรับผู้ดูแลลงเวลา');
        if (!WorkScheduleService::ready()) throw new \yii\web\ServiceUnavailableHttpException('กรุณารัน migration การตั้งค่าเวลางานก่อน');
        $model = new WorkSchedule(['weekdays'=>'1,2,3,4,5','grace_minutes'=>0,'window_minutes'=>240]);
        $post=Yii::$app->request->post();
        if (isset($post['WorkSchedule']['weekdays']) && is_array($post['WorkSchedule']['weekdays'])) {
            $days=$post['WorkSchedule']['weekdays'];
            $post['WorkSchedule']['weekdays']=count(array_filter($days,'is_scalar'))===count($days)?implode(',',$days):'';
        }
        if (Yii::$app->request->isPost && $model->load($post)) {
            $model->created_at = AttendanceService::now(); $model->created_by = Yii::$app->user->id;
            if ($model->save()) { Yii::$app->session->setFlash('success','สร้างชุดเวลาแล้ว เลือกกำหนดให้หน่วยงานหรือพนักงาน'); return $this->redirect(['index']); }
        }
        $schedules = WorkSchedule::find()->orderBy(['id'=>SORT_DESC])->all();
        $targets=['หน่วยงาน'=>[], 'พนักงาน'=>[]];
        foreach (\app\modules\hr\models\Organization::find()->select(['id','name'])->asArray()->orderBy(['name'=>SORT_ASC])->all() as $row) $targets['หน่วยงาน']['department:'.$row['id']]=$row['name'];
        foreach (\app\modules\hr\models\Employees::find()->select(['id','fname','lname'])->asArray()->orderBy(['fname'=>SORT_ASC,'lname'=>SORT_ASC])->all() as $row) $targets['พนักงาน']['employee:'.$row['id']]=$row['fname'].' '.$row['lname'];
        return $this->render('index', compact('model','schedules','targets'));
    }
    public function actionChoose($target)
    {
        if (!is_string($target) || !preg_match('/^(employee|department):([0-9]+)$/D',$target,$parts)) throw new \yii\web\BadRequestHttpException('กรุณาเลือกหน่วยงานหรือพนักงาน');
        return $this->redirect(['assign','scope'=>$parts[1],'id'=>$parts[2]]);
    }
    public function actionAssign($scope = 'employee', $id = null)
    {
        if (!in_array($scope, ['employee','department'], true) || !is_scalar($id) || !ctype_digit((string)$id) || !WorkScheduleService::canAssign($scope, (int)$id)) throw new \yii\web\ForbiddenHttpException('ไม่มีสิทธิ์กำหนดเวลาของเป้าหมายนี้');
        $target = $scope === 'employee' ? \app\modules\hr\models\Employees::findOne($id) : \app\modules\hr\models\Organization::findOne($id);
        if (!$target) throw new \yii\web\NotFoundHttpException('ไม่พบข้อมูล');
        $error = null; $values = Yii::$app->request->post('Assignment', []);
        if (Yii::$app->request->isPost) {
            try {
                if (!is_array($values)) throw new \DomainException('รูปแบบข้อมูลไม่ถูกต้อง');
                WorkScheduleService::assign(array_merge($values, ['scope'=>$scope,'target_id'=>(string)$id]));
                Yii::$app->session->setFlash('success','กำหนดเวลาทำงานแล้ว พร้อมเก็บประวัติ');
                return $this->redirect(['assign','scope'=>$scope,'id'=>$id]);
            } catch (\DomainException $e) { $error = $e->getMessage(); }
        }
        $values = array_filter((array)$values, 'is_string');
        $schedules = \yii\helpers\ArrayHelper::map(WorkSchedule::find()->all(), 'id', fn($s)=>$s->name.' '.$s->start_time.'–'.$s->end_time);
        $history = (new \yii\db\Query())->from('{{%attendance_assignment}}')->where(['scope'=>$scope,'target_id'=>$id])->orderBy(['effective_from'=>SORT_DESC,'id'=>SORT_DESC])->all();
        return $this->render('assign', compact('scope','id','target','values','error','schedules','history'));
    }
}
