<?php
namespace app\modules\accounting\controllers;

use yii\filters\AccessControl;
use yii\web\Controller;
use app\modules\accounting\models\AccountingPeriod;
use app\modules\accounting\services\AccountingPeriodReviewService;

class PeriodReviewController extends Controller
{
    public function behaviors(){return array_merge(parent::behaviors(),['access'=>['class'=>AccessControl::class,'rules'=>[['allow'=>true,'roles'=>['accountingView']]]]]);}

    public function actionIndex($year=null,$period_id=null)
    {
        $year=(int)($year?:543+(int)date('Y')+(date('n')>=10?1:0));
        $periods=AccountingPeriod::find()->where(['fiscal_year'=>$year,'period_type'=>AccountingPeriod::TYPE_MONTH])->orderBy('period_no')->all();
        $period=null;
        if($period_id!==null)$period=AccountingPeriod::findOne(['id'=>(int)$period_id,'fiscal_year'=>$year,'period_type'=>AccountingPeriod::TYPE_MONTH]);
        if(!$period&&$periods)$period=$periods[0];
        $review=$period?(new AccountingPeriodReviewService())->review($period):null;
        return $this->render('index',['year'=>$year,'periods'=>$periods,'period'=>$period,'review'=>$review]);
    }
}
