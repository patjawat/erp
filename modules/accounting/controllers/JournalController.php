<?php
namespace app\modules\accounting\controllers;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use app\modules\accounting\models\AccountingChartAccount;
use app\modules\accounting\models\AccountingChartVersion;
use app\modules\accounting\models\AccountingFiscalConfig;
use app\modules\accounting\models\AccountingJournalDraft;
use app\modules\accounting\services\AccountingJournalDraftService;
use app\modules\finance\models\FinancePayable;
use app\modules\finance\services\FinancePayableDraftService;
class JournalController extends Controller
{
    public function behaviors(){return array_merge(parent::behaviors(),['access'=>['class'=>AccessControl::class,'rules'=>[['allow'=>true,'actions'=>['index','view'],'roles'=>['accountingView']],['allow'=>true,'actions'=>['create'],'roles'=>['accountingPrepare']],['allow'=>true,'actions'=>['settings'],'roles'=>['accountingChartManage']]]],'verbs'=>['class'=>VerbFilter::class,'actions'=>['create'=>['POST'],'settings'=>['GET','POST']]]]);}
    public function actionIndex(){return $this->render('index',['dataProvider'=>new ActiveDataProvider(['query'=>AccountingJournalDraft::find()->orderBy(['id'=>SORT_DESC]),'pagination'=>['pageSize'=>30]])]);}
    public function actionView($id){$model=AccountingJournalDraft::findOne($id);if(!$model)throw new NotFoundHttpException('ไม่พบรายการบัญชีร่าง');return $this->render('view',compact('model'));}
    public function actionCreate($payable_id){$payable=FinancePayable::findOne($payable_id);if(!$payable)throw new NotFoundHttpException('ไม่พบทะเบียนเจ้าหนี้');try{$journal=(new AccountingJournalDraftService())->createFromPayable($payable);Yii::$app->session->setFlash('success','สร้างรายการบัญชีร่างและตรวจสอบยอดสมดุลแล้ว');return $this->redirect(['view','id'=>$journal->id]);}catch(\DomainException $e){Yii::$app->session->setFlash('warning',$e->getMessage());}catch(\Throwable $e){Yii::error($e,__METHOD__);Yii::$app->session->setFlash('error','สร้างรายการบัญชีร่างไม่สำเร็จ กรุณาติดต่อผู้ดูแลระบบ');}return $this->redirect(['/accounting/payable/view','id'=>$payable->id]);}
    public function actionSettings($year=null){$year=(int)($year?:FinancePayableDraftService::fiscalYearForDate(date('Y-m-d')));$model=AccountingFiscalConfig::findOne(['fiscal_year'=>$year])?:new AccountingFiscalConfig(['fiscal_year'=>$year]);if($model->load(Yii::$app->request->post())){$model->fiscal_year=$year;try{(new AccountingJournalDraftService())->saveConfig($model);Yii::$app->session->setFlash('success','บันทึกค่าตั้งต้นบัญชีปีงบประมาณแล้ว');return $this->redirect(['settings','year'=>$year]);}catch(\DomainException $e){$model->addError('payable_account_id',$e->getMessage());}catch(\Throwable $e){Yii::error($e,__METHOD__);$model->addError('payable_account_id','บันทึกค่าตั้งต้นไม่สำเร็จ กรุณาติดต่อผู้ดูแลระบบ');}}$version=AccountingChartVersion::findOne(['fiscal_year'=>$year,'scope'=>AccountingChartVersion::SCOPE_HOSPITAL,'status'=>AccountingChartVersion::STATUS_ACTIVE]);$accounts=$version?AccountingChartAccount::find()->where(['version_id'=>$version->id,'is_active'=>1])->orderBy('code')->all():[];$model->chart_version_id=$version?->id;return $this->render('settings',['model'=>$model,'version'=>$version,'payableOptions'=>ArrayHelper::map(array_filter($accounts,fn($a)=>$a->category==='2'),'id',fn($a)=>$a->code.' — '.$a->name),'vatOptions'=>ArrayHelper::map(array_filter($accounts,fn($a)=>$a->category==='1'),'id',fn($a)=>$a->code.' — '.$a->name)]);}
}
