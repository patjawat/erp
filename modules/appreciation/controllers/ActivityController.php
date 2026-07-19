<?php
namespace app\modules\appreciation\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\components\UserHelper;
use app\modules\appreciation\models\AppreciationActivity;
use app\modules\appreciation\models\AppreciationParticipation;
use app\modules\appreciation\models\AppreciationProgramYear;
use app\modules\appreciation\services\AppreciationPointService;

class ActivityController extends Controller
{
    public function behaviors(){ return ['verbs'=>['class'=>VerbFilter::class,'actions'=>['join'=>['post'],'submit'=>['post']]]]; }
    public function actionIndex()
    {
        $me=UserHelper::GetEmployee(); if(!$me)return $this->redirect(['/me']);
        $year=AppreciationProgramYear::active();
        $activities=$year ? AppreciationActivity::find()->where(['program_year_id'=>$year->id,'status'=>AppreciationActivity::STATUS_PUBLISHED])->orderBy(['end_at'=>SORT_ASC])->all() : [];
        $participations=$year ? AppreciationParticipation::find()->where(['emp_id'=>$me->id,'program_year_id'=>$year->id])->indexBy('activity_id')->all() : [];
        return $this->render('index',['me'=>$me,'year'=>$year,'activities'=>$activities,'participations'=>$participations,'summary'=>AppreciationPointService::summary($me->id,$year)]);
    }
    public function actionJoin($id)
    {
        $me=UserHelper::GetEmployee(); $activity=AppreciationActivity::findOne((int)$id);
        if(!$me || !$activity || !$activity->isOpen())throw new NotFoundHttpException('ไม่พบกิจกรรมหรือกิจกรรมปิดรับแล้ว');
        $tx=Yii::$app->db->beginTransaction();
        try{
            $table=Yii::$app->db->quoteTableName(AppreciationActivity::tableName());
            Yii::$app->db->createCommand("SELECT id FROM {$table} WHERE id=:id FOR UPDATE",[':id'=>$activity->id])->queryScalar();
            if(AppreciationParticipation::find()->where(['activity_id'=>$activity->id,'emp_id'=>$me->id])->exists())throw new \DomainException('คุณลงทะเบียนกิจกรรมนี้แล้ว');
            if($activity->capacity && AppreciationParticipation::find()->where(['activity_id'=>$activity->id])->andWhere(['<>','status',AppreciationParticipation::STATUS_REJECTED])->count() >= $activity->capacity)throw new \DomainException('กิจกรรมนี้มีผู้ลงทะเบียนครบแล้ว');
            $status=$activity->requires_review ? AppreciationParticipation::STATUS_REGISTERED : AppreciationParticipation::STATUS_COMPLETED;
            $p=new AppreciationParticipation(['activity_id'=>$activity->id,'program_year_id'=>$activity->program_year_id,'emp_id'=>$me->id,'status'=>$status,'points_awarded'=>$status===AppreciationParticipation::STATUS_COMPLETED?$activity->points:0,'registered_at'=>date('Y-m-d H:i:s'),'completed_at'=>$status===AppreciationParticipation::STATUS_COMPLETED?date('Y-m-d H:i:s'):null]);
            if(!$p->save())throw new \RuntimeException('ลงทะเบียนไม่สำเร็จ'); $tx->commit();
            Yii::$app->session->setFlash('success',$status===AppreciationParticipation::STATUS_COMPLETED?'เข้าร่วมกิจกรรมและรับคะแนนแล้ว':'ลงทะเบียนแล้ว กรุณาทำกิจกรรมตามรายละเอียด');
            if($activity->participation_mode===AppreciationActivity::MODE_EXTERNAL && $activity->external_url)return $this->redirect($activity->external_url);
        }catch(\Throwable $e){$tx->rollBack(); Yii::$app->session->setFlash('error',$e instanceof \DomainException?$e->getMessage():'ไม่สามารถลงทะเบียนได้');}
        return $this->redirect(['index']);
    }
    public function actionSubmit($id)
    {
        $me=UserHelper::GetEmployee(); $p=AppreciationParticipation::findOne(['activity_id'=>(int)$id,'emp_id'=>$me?$me->id:0]);
        if(!$p || $p->status!==AppreciationParticipation::STATUS_REGISTERED)throw new NotFoundHttpException();
        $p->status=AppreciationParticipation::STATUS_SUBMITTED; $p->evidence_url=Yii::$app->request->post('evidence_url'); $p->note=Yii::$app->request->post('note');
        $p->save(); Yii::$app->session->setFlash('success','ส่งหลักฐานการเข้าร่วมแล้ว รอผู้ดูแลตรวจสอบ'); return $this->redirect(['index']);
    }
}
