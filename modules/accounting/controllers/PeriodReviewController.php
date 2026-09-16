<?php
namespace app\modules\accounting\controllers;

use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use app\modules\accounting\models\AccountingPeriod;
use app\modules\accounting\services\AccountingPeriodReviewService;
use app\modules\accounting\services\AccountingPeriodCloseService;
use app\modules\accounting\models\AccountingGlPeriodClose;
use Yii;

class PeriodReviewController extends Controller
{
    public function behaviors(){return array_merge(parent::behaviors(),['access'=>['class'=>AccessControl::class,'rules'=>[['allow'=>true,'actions'=>['index'],'roles'=>['accountingView']],['allow'=>true,'actions'=>['close'],'roles'=>['accountingClosePeriod']]]],'verbs'=>['class'=>VerbFilter::class,'actions'=>['close'=>['POST']]]]);}

    public function actionIndex($year=null,$period_id=null)
    {
        $year=(int)($year?:543+(int)date('Y')+(date('n')>=10?1:0));
        $periods=AccountingPeriod::find()->where(['fiscal_year'=>$year,'period_type'=>AccountingPeriod::TYPE_MONTH])->orderBy('period_no')->all();
        $period=null;
        if($period_id!==null)$period=AccountingPeriod::findOne(['id'=>(int)$period_id,'fiscal_year'=>$year,'period_type'=>AccountingPeriod::TYPE_MONTH]);
        if(!$period&&$periods)$period=$periods[0];
        $review=$period?(new AccountingPeriodReviewService())->review($period):null;
        $close=$period?AccountingGlPeriodClose::findOne(['period_id'=>$period->id]):null;
        return $this->render('index',['year'=>$year,'periods'=>$periods,'period'=>$period,'review'=>$review,'close'=>$close]);
    }
    public function actionClose($id){$period=AccountingPeriod::findOne(['id'=>(int)$id,'period_type'=>AccountingPeriod::TYPE_MONTH]);if(!$period)throw new \yii\web\NotFoundHttpException('ไม่พบงวดบัญชี');try{(new AccountingPeriodCloseService())->close($period);Yii::$app->session->setFlash('success','ปิดงวด GL และบันทึกยอด snapshot แล้ว');}catch(\DomainException $e){Yii::$app->session->setFlash('warning',$e->getMessage());}catch(\Throwable $e){Yii::error($e,__METHOD__);Yii::$app->session->setFlash('error','ปิดงวด GL ไม่สำเร็จ กรุณาติดต่อผู้ดูแลระบบ');}return $this->redirect(['index','year'=>$period->fiscal_year,'period_id'=>$period->id]);}
}
