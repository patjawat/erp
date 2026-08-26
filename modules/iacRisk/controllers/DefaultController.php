<?php

namespace app\modules\iacRisk\controllers;

use app\modules\iacRisk\models\Activity;
use app\modules\iacRisk\models\Csa;
use app\modules\iacRisk\models\CsaStep;
use app\modules\iacRisk\models\CsaRisk;
use app\modules\iacRisk\models\RiskControl;
use app\modules\iacRisk\models\ControlAssessment;
use app\modules\iacRisk\models\ImprovementPlan;
use app\modules\iacRisk\models\RiskRegister;
use app\modules\iacRisk\models\Pk4;
use app\modules\iacRisk\models\Pk4Item;
use app\modules\iacRisk\models\Pk1;
use app\modules\iacRisk\models\RiskReport;
use app\modules\iacRisk\models\RiskFollowup;
use app\modules\iacRisk\models\ServiceProcessVersion;
use app\modules\iacRisk\services\AccessService;
use app\modules\iacRisk\services\ActivityService;
use app\modules\iacRisk\services\ContextService;
use app\modules\iacRisk\services\RiskRegisterService;
use app\modules\iacRisk\services\Pk5Service;
use app\modules\iacRisk\services\RiskReportService;
use app\modules\iacRisk\services\RiskReportAccessService;
use app\modules\iacRisk\services\RiskFollowupService;
use app\components\AppHelper;
use app\components\SiteHelper;
use app\modules\serviceProfile\models\ServiceProfile;
use app\modules\serviceProfile\services\OwnerDirectoryService;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;

class DefaultController extends Controller
{
    private AccessService $access;

    public function init(): void
    {
        parent::init();
        $this->access = new AccessService();
    }

    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'access' => ['class' => AccessControl::class, 'rules' => [['allow' => true, 'roles' => ['@']]]],
            'verbs' => ['class' => VerbFilter::class, 'actions' => [
                'review-process'=>['POST'],'create-csa'=>['POST'],'save-csa-snapshot'=>['POST'],
                'save-step'=>['POST'],'delete-step'=>['POST'],'save-risk'=>['POST'],'delete-risk'=>['POST'],
                'confirm-csa'=>['POST'],'send-csa-head'=>['POST'],'approve-csa'=>['POST'],'return-csa'=>['POST'],
                'save-manual-risk'=>['POST'],'delete-manual-risk'=>['POST'],
                'create-pk4'=>['POST'],'save-pk4'=>['POST'],
                'create-pk1'=>['POST'],'save-pk1'=>['POST'],
                'create-risk-report'=>['POST'],'submit-risk-report'=>['POST'],'approve-risk-report'=>['POST'],'return-risk-report'=>['POST'],
                'create-followup'=>['POST'],'save-followup'=>['POST'],
            ]],
        ]);
    }

    public function beforeAction($action): bool
    {
        if (!$this->access->canEnter()) throw new ForbiddenHttpException('คุณไม่มีสิทธิ์ใช้งาน IAC&Risk');
        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        $context = (new ContextService())->resolve();
        $activities = [];
        if ($context['hospitalId']) {
            $query = Activity::find()->where(['hospital_id' => $context['hospitalId']]);
            if ($context['fiscalYearId']) $query->andWhere(['fiscal_year_id' => $context['fiscalYearId']]);
            if ($context['orgUnitId']) $query->andWhere(['or', ['org_unit_id' => $context['orgUnitId']], ['org_unit_id' => null]]);
            $activities = $query->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC])->limit(8)->all();
        }
        return $this->render('index', ['context' => $context, 'activities' => $activities, 'access' => $this->access]);
    }

    public function actionProcesses()
    {
        $context = (new ContextService())->resolve();
        $fiscalYear = (int) ($context['fiscalYear']?->fiscal_year ?: AppHelper::YearBudget());
        $directory = new OwnerDirectoryService();
        $orgUnitId = (int) $context['orgUnitId'];
        if (!$orgUnitId && !$this->access->canScopeAllUnits()) {
            $employee = $this->access->employee();
            $orgUnitId = (int) ($directory->orgUnitForDepartment($employee?->department ? (int) $employee->department : null, $fiscalYear)?->id ?: 0);
        }
        $profileQuery = ServiceProfile::find()->where(['fiscal_year' => $fiscalYear]);
        if ($orgUnitId) {
            try {
                $owner = $directory->resolveOwner($orgUnitId, $fiscalYear);
                $profileQuery->andWhere(['owner_type' => $owner['owner_type'], 'owner_id' => $owner['owner_id']]);
            } catch (\DomainException $e) { $profileQuery->andWhere('0=1'); }
        } elseif (!$this->access->canScopeAllUnits()) $profileQuery->andWhere('0=1');
        $profileRows = $profileQuery->orderBy(['owner_type' => SORT_ASC, 'owner_id' => SORT_ASC, 'revision_no' => SORT_DESC, 'id' => SORT_DESC])->all();
        $profiles = [];
        foreach ($profileRows as $candidate) {
            $ownerKey = $candidate->owner_type . ':' . (int) $candidate->owner_id;
            if (!isset($profiles[$ownerKey])) $profiles[$ownerKey] = $candidate;
        }
        $profiles = array_values($profiles);
        $profileIds = array_map(static fn (ServiceProfile $item) => (int) $item->id, $profiles);
        $versions = $profileIds ? ServiceProcessVersion::find()->with('profile')->where(['service_profile_id' => $profileIds])
            ->orderBy(['service_profile_id' => SORT_ASC, 'sequence' => SORT_ASC])->all() : [];
        $profile = count($profiles) === 1 ? $profiles[0] : null;
        $profileAccess = new \app\modules\serviceProfile\services\AccessService();
        $canEditByProfile = [];
        foreach ($profiles as $item) {
            $canEditByProfile[(int) $item->id] = $profileAccess->canEdit($item)
                || Yii::$app->user->can('iacRiskCoordinate') || Yii::$app->user->can('iacRiskAdmin');
        }
        $canEdit = $profile ? ($canEditByProfile[(int) $profile->id] ?? false) : false;
        return $this->render('processes', compact('context', 'fiscalYear', 'profile', 'profiles', 'versions', 'canEdit', 'canEditByProfile'));
    }

    public function actionReviewProcess(int $id)
    {
        $version = ServiceProcessVersion::find()->with('profile')->where(['id' => $id])->one();
        if (!$version || !$version->profile) throw new \yii\web\NotFoundHttpException('ไม่พบกระบวนงาน');
        $canEdit = (new \app\modules\serviceProfile\services\AccessService())->canEdit($version->profile)
            || Yii::$app->user->can('iacRiskCoordinate') || Yii::$app->user->can('iacRiskAdmin');
        if (!$canEdit) throw new ForbiddenHttpException('คุณไม่มีสิทธิ์ทบทวนกระบวนงานนี้');
        $status = trim((string) Yii::$app->request->post('review_status'));
        if (!array_key_exists($status, ServiceProcessVersion::reviewLabels())) throw new \yii\web\BadRequestHttpException('สถานะทบทวนไม่ถูกต้อง');
        $version->review_status = $status;
        $version->review_note = trim((string) Yii::$app->request->post('review_note')) ?: null;
        $version->reviewed_at = date('Y-m-d H:i:s');
        $version->reviewed_by = Yii::$app->user->id;
        $version->updated_at = date('Y-m-d H:i:s');
        $version->updated_by = Yii::$app->user->id;
        if (!$version->save()) Yii::$app->session->setFlash('error', implode(' ', $version->getFirstErrors()));
        else Yii::$app->session->setFlash('success', 'บันทึกผลทบทวนกระบวนงานแล้ว');
        return $this->redirect(array_merge(['processes'], ContextService::query((new ContextService())->resolve())));
    }
    public function actionServiceProfile()
    {
        $context = (new ContextService())->resolve();
        $fiscalYear = (int) ($context['fiscalYear']?->fiscal_year ?: AppHelper::YearBudget());
        $orgUnitId = (int) $context['orgUnitId'];
        $directory = new OwnerDirectoryService();

        if (!$orgUnitId && !$this->access->canScopeAllUnits()) {
            $employee = $this->access->employee();
            $unit = $directory->orgUnitForDepartment($employee?->department ? (int) $employee->department : null, $fiscalYear);
            $orgUnitId = (int) ($unit?->id ?: 0);
        }

        $query = ServiceProfile::find()->with(['authors.employee', 'approvals.employee'])
            ->where(['fiscal_year' => $fiscalYear]);
        if ($orgUnitId) {
            try {
                $owner = $directory->resolveOwner($orgUnitId, $fiscalYear);
                $query->andWhere(['owner_type' => $owner['owner_type'], 'owner_id' => $owner['owner_id']]);
            } catch (\DomainException $e) {
                $query->andWhere('0=1');
            }
        } elseif (!$this->access->canScopeAllUnits()) {
            $query->andWhere('0=1');
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query->orderBy(['owner_name_snapshot' => SORT_ASC, 'revision_no' => SORT_DESC]),
            'pagination' => ['pageSize' => 20],
        ]);

        return $this->render('service-profile', [
            'context' => $context,
            'dataProvider' => $dataProvider,
            'fiscalYear' => $fiscalYear,
        ]);
    }
    public function actionCsa()
    {
        $context = (new ContextService())->resolve();
        $query = Csa::find()->with(['processVersion.profile','steps.risks'])->where(['hospital_id'=>$context['hospitalId'] ?: 0]);
        if ($context['fiscalYearId']) $query->andWhere(['fiscal_year_id'=>$context['fiscalYearId']]);
        if ($context['orgUnitId']) $query->andWhere(['org_unit_id'=>$context['orgUnitId']]);
        elseif (!($context['canScopeAllUnits'] ?? false)) $query->andWhere('0=1');
        $models = $query->orderBy(['org_unit_id'=>SORT_ASC,'id'=>SORT_DESC])->all();

        $fiscalYear = (int)($context['fiscalYear']?->fiscal_year ?: AppHelper::YearBudget());
        $processQuery = ServiceProcessVersion::find()->alias('v')->with('profile')->joinWith('profile p')
            ->where(['v.fiscal_year'=>$fiscalYear])->andWhere(['<>','v.review_status',ServiceProcessVersion::REVIEW_RETIRED]);
        if ($context['orgUnitId']) {
            try {
                $owner=(new OwnerDirectoryService())->resolveOwner((int)$context['orgUnitId'],$fiscalYear);
                $processQuery->andWhere(['p.owner_type'=>$owner['owner_type'],'p.owner_id'=>$owner['owner_id']]);
            } catch (\DomainException $e) { $processQuery->andWhere('0=1'); }
        } elseif (!($context['canScopeAllUnits'] ?? false)) $processQuery->andWhere('0=1');
        $processes=$processQuery->orderBy(['p.owner_name_snapshot'=>SORT_ASC,'v.sequence'=>SORT_ASC])->all();
        $csaByProcess=[]; foreach($models as $item)$csaByProcess[(int)$item->process_id]=$item;
        return $this->render('csa',['context'=>$context,'models'=>$models,'processes'=>$processes,'csaByProcess'=>$csaByProcess]);
    }

    public function actionCreateCsa(int $process_version_id)
    {
        $version=ServiceProcessVersion::find()->with('profile')->where(['id'=>$process_version_id])->one();
        if(!$version||!$version->profile)throw new \yii\web\NotFoundHttpException('ไม่พบกระบวนงาน');
        $canEdit=(new \app\modules\serviceProfile\services\AccessService())->canEdit($version->profile)||Yii::$app->user->can('iacRiskCoordinate')||Yii::$app->user->can('iacRiskAdmin');
        if(!$canEdit)throw new ForbiddenHttpException('คุณไม่มีสิทธิ์เริ่ม CSA ของกระบวนงานนี้');
        try{$csa=(new \app\modules\iacRisk\services\CsaService())->create($version);Yii::$app->session->setFlash('success','สร้าง CSA ฉบับร่างและบันทึก snapshot แล้ว');return $this->redirect(['csa-view','id'=>$csa->id]);}
        catch(\Throwable $e){Yii::$app->session->setFlash('error',$e->getMessage());return $this->redirect(array_merge(['csa'],ContextService::query((new ContextService())->resolve())));}
    }

    public function actionCsaView(int $id)
    {
        $model=Csa::find()->with(['processVersion.profile','steps.risks.controls','steps.risks.assessment','steps.risks.plans'])->where(['id'=>$id])->one();
        if(!$model)throw new \yii\web\NotFoundHttpException('ไม่พบ CSA');
        $profile=$model->processVersion?->profile;
        $access=new \app\modules\serviceProfile\services\AccessService();
        if(!$profile||(!$access->canView($profile)&&!Yii::$app->user->can('iacRiskCoordinate')&&!Yii::$app->user->can('iacRiskAdmin')))throw new \yii\web\NotFoundHttpException('ไม่พบ CSA');
        $csaAccess=new \app\modules\iacRisk\services\CsaAccessService();$canEdit=$csaAccess->canEdit($model);
        $readiness=(new \app\modules\iacRisk\services\CsaReadinessService())->inspect($model);
        $canConfirm=$csaAccess->canConfirm($model);$canSendHead=$csaAccess->canSendHead($model);$canHeadAct=$csaAccess->canHeadAct($model);
        $context=(new ContextService())->resolve();
        return $this->render('csa-view',compact('model','canEdit','context','readiness','canConfirm','canSendHead','canHeadAct'));
    }

    public function actionCsaFlowchartPdf(int $id)
    {
        $model=Csa::find()->with(['processVersion.profile','steps.risks.controls'])->where(['id'=>$id])->one();if(!$model||!($model->processVersion?->profile))throw new \yii\web\NotFoundHttpException('ไม่พบ CSA');
        $profile=$model->processVersion->profile;$access=new \app\modules\serviceProfile\services\AccessService();if(!$access->canView($profile)&&!Yii::$app->user->can('iacRiskCoordinate')&&!Yii::$app->user->can('iacRiskAdmin'))throw new \yii\web\NotFoundHttpException('ไม่พบ CSA');
        $fontPath=Yii::getAlias('@webroot/fonts/THSarabunNew');$defaultConfig=(new \Mpdf\Config\ConfigVariables())->getDefaults();$defaultFontConfig=(new \Mpdf\Config\FontVariables())->getDefaults();
        $mpdf=new \Mpdf\Mpdf(['mode'=>'utf-8','format'=>'A4','orientation'=>'P','margin_left'=>12,'margin_right'=>12,'margin_top'=>12,'margin_bottom'=>15,'fontDir'=>array_merge($defaultConfig['fontDir'],[$fontPath]),'fontdata'=>$defaultFontConfig['fontdata']+['thsarabunnew'=>['R'=>'THSarabunNew.ttf','B'=>'THSarabunNew-Bold.ttf','I'=>'THSarabunNew-Italic.ttf','BI'=>'THSarabunNew BoldItalic.ttf']],'default_font'=>'thsarabunnew','tempDir'=>Yii::getAlias('@runtime/mpdf')]);
        if(!in_array($model->status,[Csa::STATUS_HEAD_APPROVED,Csa::STATUS_COORDINATOR_REVISED],true)){$mpdf->SetWatermarkText('ฉบับร่าง',0.07);$mpdf->showWatermarkText=true;}
        $mpdf->SetTitle('Flow chart '.$model->process_name_snapshot);$mpdf->SetAuthor((string)(SiteHelper::getInfo()['company_name']??'ERP Hospital'));
        $mpdf->SetHTMLFooter('<table style="width:100%;border-top:.3mm solid #777;font-size:10pt;color:#555"><tr><td>CSA Revision '.(int)$model->revision_no.'</td><td style="text-align:right">หน้า {PAGENO} จาก {nbpg}</td></tr></table>');
        $mpdf->WriteHTML($this->renderPartial('_csa_flowchart_pdf',['model'=>$model,'siteInfo'=>SiteHelper::getInfo()]));
        $safe=preg_replace('/[^a-zA-Z0-9_-]+/','_','CSA_Flowchart_'.$model->id.'_'.$model->fiscal_year.'_R'.$model->revision_no);return $mpdf->Output($safe.'.pdf',\Mpdf\Output\Destination::INLINE);
    }

    public function actionSaveCsaSnapshot(int $id)
    {
        [$model]=$this->editableCsa($id);
        $name=trim((string)Yii::$app->request->post('process_name_snapshot'));
        if($name==='')throw new \yii\web\BadRequestHttpException('กรุณาระบุชื่อกระบวนงาน');
        $model->process_name_snapshot=$name;$model->objective_snapshot=trim((string)Yii::$app->request->post('objective_snapshot'))?:null;
        $this->touch($model);$model->save(false);
        Yii::$app->session->setFlash('success','บันทึก snapshot สำหรับ CSA แล้ว โดยไม่แก้ Service Profile');
        return $this->redirect(['csa-view','id'=>$model->id]);
    }

    public function actionSaveStep(int $csa_id, ?int $id=null)
    {
        [$csa]=$this->editableCsa($csa_id);
        $model=$id?CsaStep::findOne(['id'=>$id,'csa_id'=>$csa->id]):new CsaStep(['csa_id'=>$csa->id]);
        if(!$model)throw new \yii\web\NotFoundHttpException('ไม่พบขั้นตอน');
        if(!$id){$model->ref=Yii::$app->security->generateRandomString(24);$model->sequence=(int)CsaStep::find()->where(['csa_id'=>$csa->id])->max('sequence')+10;}
        $model->name=trim((string)Yii::$app->request->post('name'));$model->detail=trim((string)Yii::$app->request->post('detail'))?:null;
        $model->responsible=trim((string)Yii::$app->request->post('responsible'))?:null;$model->duration=trim((string)Yii::$app->request->post('duration'))?:null;
        $model->control_point=trim((string)Yii::$app->request->post('control_point'))?:null;$this->touch($model);
        if(!$model->save())Yii::$app->session->setFlash('error',implode(' ',$model->getFirstErrors()));else Yii::$app->session->setFlash('success','บันทึกขั้นตอนแล้ว');
        return $this->redirect(['csa-view','id'=>$csa->id]);
    }

    public function actionDeleteStep(int $id)
    {
        $step=CsaStep::findOne($id);if(!$step)throw new \yii\web\NotFoundHttpException('ไม่พบขั้นตอน');[$csa]=$this->editableCsa((int)$step->csa_id);
        $step->delete();Yii::$app->session->setFlash('success','ลบขั้นตอนและความเสี่ยงภายในขั้นตอนแล้ว');return $this->redirect(['csa-view','id'=>$csa->id]);
    }

    public function actionSaveRisk(int $step_id, ?int $id=null)
    {
        $step=CsaStep::findOne($step_id);if(!$step)throw new \yii\web\NotFoundHttpException('ไม่พบขั้นตอน');[$csa]=$this->editableCsa((int)$step->csa_id);
        $risk=$id?CsaRisk::findOne(['id'=>$id,'step_id'=>$step->id]):new CsaRisk(['csa_id'=>$csa->id,'step_id'=>$step->id]);if(!$risk)throw new \yii\web\NotFoundHttpException('ไม่พบความเสี่ยง');
        $name=trim((string)Yii::$app->request->post('name'));$adequacy=trim((string)Yii::$app->request->post('adequacy',CsaRisk::ADEQUACY_NOT_ASSESSED));
        if($name===''||!array_key_exists($adequacy,CsaRisk::adequacyLabels())){Yii::$app->session->setFlash('error','กรุณาระบุชื่อและผลประเมินความเสี่ยงให้ครบ');return $this->redirect(['csa-view','id'=>$csa->id,'risk_error_id'=>$risk->id?:'new','open_step_id'=>$step->id]);}
        $planAction=trim((string)Yii::$app->request->post('plan_action'));$planResponsible=trim((string)Yii::$app->request->post('plan_responsible'));$planDue=trim((string)Yii::$app->request->post('plan_due_date'));
        if($adequacy===CsaRisk::ADEQUACY_INADEQUATE&&($planAction===''||$planResponsible===''||$planDue==='')){Yii::$app->session->setFlash('error','เมื่อควบคุมไม่เพียงพอ ต้องระบุวิธีแก้ไข ผู้รับผิดชอบ และกำหนดเสร็จ');return $this->redirect(['csa-view','id'=>$csa->id,'risk_error_id'=>$risk->id?:'new','open_step_id'=>$step->id]);}
        $tx=Yii::$app->db->beginTransaction();try{
            if(!$id){$risk->ref=Yii::$app->security->generateRandomString(24);$risk->sequence=(int)CsaRisk::find()->where(['step_id'=>$step->id])->max('sequence')+10;}
            $risk->name=$name;$risk->cause=trim((string)Yii::$app->request->post('cause'))?:null;$risk->impact=trim((string)Yii::$app->request->post('impact'))?:null;
            $risk->likelihood_score=$this->score(Yii::$app->request->post('likelihood_score'));$risk->impact_score=$this->score(Yii::$app->request->post('impact_score'));
            $risk->residual_risk=trim((string)Yii::$app->request->post('residual_risk'))?:null;$risk->adequacy=$adequacy;$this->touch($risk);$risk->save(false);
            $control=RiskControl::find()->where(['risk_id'=>$risk->id])->orderBy(['sequence'=>SORT_ASC])->one();$controlText=trim((string)Yii::$app->request->post('control_description'));
            if($controlText!==''){$control=$control?:new RiskControl(['ref'=>Yii::$app->security->generateRandomString(24),'risk_id'=>$risk->id,'sequence'=>10]);$control->description=$controlText;$control->control_type=trim((string)Yii::$app->request->post('control_type'))?:null;$control->responsible=trim((string)Yii::$app->request->post('control_responsible'))?:null;$this->touch($control);$control->save(false);}
            $assessment=ControlAssessment::findOne(['risk_id'=>$risk->id])?:new ControlAssessment(['ref'=>Yii::$app->security->generateRandomString(24),'risk_id'=>$risk->id]);$assessment->adequacy=$adequacy;$assessment->reason=trim((string)Yii::$app->request->post('assessment_reason'))?:null;$assessment->assessed_at=date('Y-m-d H:i:s');$assessment->assessed_by=Yii::$app->user->id;$this->touch($assessment);$assessment->save(false);
            if($adequacy===CsaRisk::ADEQUACY_INADEQUATE){$plan=ImprovementPlan::find()->where(['risk_id'=>$risk->id])->orderBy(['id'=>SORT_ASC])->one()?:new ImprovementPlan(['ref'=>Yii::$app->security->generateRandomString(24),'risk_id'=>$risk->id,'status'=>'planned']);$plan->action=$planAction;$plan->responsible=$planResponsible;$plan->due_date=$planDue;$this->touch($plan);$plan->save(false);}
            $step->updateAttributes(['has_risk'=>1,'updated_at'=>date('Y-m-d H:i:s'),'updated_by'=>Yii::$app->user->id]);$tx->commit();Yii::$app->session->setFlash('success','บันทึกความเสี่ยงและการควบคุมแล้ว');
        }catch(\Throwable $e){$tx->rollBack();throw $e;}return $this->redirect(['csa-view','id'=>$csa->id]);
    }

    public function actionDeleteRisk(int $id)
    {
        $risk=CsaRisk::findOne($id);if(!$risk)throw new \yii\web\NotFoundHttpException('ไม่พบความเสี่ยง');$step=$risk->step_id;[$csa]=$this->editableCsa((int)$risk->csa_id);$risk->delete();
        $has=CsaRisk::find()->where(['step_id'=>$step])->exists();CsaStep::updateAll(['has_risk'=>$has?1:0],['id'=>$step]);Yii::$app->session->setFlash('success','ลบความเสี่ยงแล้ว');return $this->redirect(['csa-view','id'=>$csa->id]);
    }

    public function actionConfirmCsa(int $id)
    {
        $model=$this->workflowCsa($id);$access=new \app\modules\iacRisk\services\CsaAccessService();if(!$access->canConfirm($model))throw new ForbiddenHttpException('ไม่มีสิทธิ์ยืนยัน CSA');
        $readiness=(new \app\modules\iacRisk\services\CsaReadinessService())->inspect($model);if(!$readiness['ready']){Yii::$app->session->setFlash('error',implode(' ', $readiness['errors']));return $this->redirect(['csa-view','id'=>$model->id]);}
        $tx=Yii::$app->db->beginTransaction();
        try {
            $from=$model->status;$model->status=Csa::STATUS_AUTHOR_CONFIRMED;$model->author_confirmed_at=date('Y-m-d H:i:s');$model->author_confirmed_by=Yii::$app->user->id;$model->return_note=null;$this->touch($model);$model->save(false);$this->logCsaState($model,'author_confirmed',$from);$tx->commit();
        } catch (\Throwable $e) {
            $tx->rollBack();
            throw $e;
        }
        Yii::$app->session->setFlash('success','ผู้จัดทำยืนยัน CSA แล้ว');return $this->redirect(['csa-view','id'=>$model->id]);
    }

    public function actionSendCsaHead(int $id)
    {
        $model=$this->workflowCsa($id);if(!(new \app\modules\iacRisk\services\CsaAccessService())->canSendHead($model))throw new ForbiddenHttpException('ไม่มีสิทธิ์ส่งหัวหน้าหน่วยงาน');$from=$model->status;
        $this->saveCsaTransition($model,Csa::STATUS_HEAD_PENDING,'sent_to_head',$from);
        Yii::$app->session->setFlash('success','ส่ง CSA ให้หัวหน้าหน่วยงานรับรองแล้ว');return $this->redirect(['csa-view','id'=>$model->id]);
    }

    public function actionApproveCsa(int $id)
    {
        $model=$this->workflowCsa($id);if(!(new \app\modules\iacRisk\services\CsaAccessService())->canHeadAct($model))throw new ForbiddenHttpException('ไม่มีสิทธิ์รับรอง CSA');$from=$model->status;$model->head_approved_at=date('Y-m-d H:i:s');$model->head_approved_by=Yii::$app->user->id;$model->return_note=null;
        $this->saveCsaTransition($model,Csa::STATUS_HEAD_APPROVED,'head_approved',$from,null,static function(Csa $csa): void {(new RiskRegisterService())->syncApprovedCsa($csa);});
        Yii::$app->session->setFlash('success','หัวหน้าหน่วยงานรับรอง CSA แล้ว');return $this->redirect(['csa-view','id'=>$model->id]);
    }

    public function actionReturnCsa(int $id)
    {
        $model=$this->workflowCsa($id);if(!(new \app\modules\iacRisk\services\CsaAccessService())->canHeadAct($model))throw new ForbiddenHttpException('ไม่มีสิทธิ์ส่งกลับ CSA');$note=trim((string)Yii::$app->request->post('return_note'));if($note==='')throw new \yii\web\BadRequestHttpException('กรุณาระบุเหตุผลที่ส่งกลับ');$from=$model->status;$model->return_note=$note;
        $this->saveCsaTransition($model,Csa::STATUS_RETURNED,'returned',$from,$note);
        Yii::$app->session->setFlash('success','ส่งกลับให้ผู้จัดทำแก้ไขแล้ว');return $this->redirect(['csa-view','id'=>$model->id]);
    }
    public function actionRisks()
    {
        $context=(new ContextService())->resolve();$hospitalId=(int)$context['hospitalId'];$fiscalYearId=(int)$context['fiscalYearId'];$orgUnitId=(int)$context['orgUnitId'];
        $query=RiskRegister::find()->with(['csa','csaRisk','orgUnit'])->where(['hospital_id'=>$hospitalId ?: 0,'fiscal_year_id'=>$fiscalYearId ?: 0]);
        if($orgUnitId)$query->andWhere(['org_unit_id'=>$orgUnitId]);elseif(!($context['canScopeAllUnits']??false))$query->andWhere('0=1');
        $riskLevel=trim((string)Yii::$app->request->get('risk_level'));$levelRanges=['low'=>[1,3],'moderate'=>[4,9],'high'=>[10,16],'very_high'=>[17,25]];if(isset($levelRanges[$riskLevel]))$query->andWhere(['between',new \yii\db\Expression('likelihood_score * impact_score'),$levelRanges[$riskLevel][0],$levelRanges[$riskLevel][1]]);else $riskLevel='';
        $models=$query->orderBy(['org_unit_id'=>SORT_ASC,'source_type'=>SORT_ASC,'id'=>SORT_ASC])->all();$canEdit=$orgUnitId>0&&$this->canManageRiskUnit($orgUnitId,(int)($context['fiscalYear']?->fiscal_year?:0));
        return $this->render('risks',compact('context','models','canEdit','riskLevel'));
    }

    public function actionSaveManualRisk(?int $id=null)
    {
        $context=(new ContextService())->resolve();$model=$id?RiskRegister::findOne(['id'=>$id,'source_type'=>RiskRegister::SOURCE_MANUAL]):null;if($id&&!$model)throw new \yii\web\NotFoundHttpException('ไม่พบความเสี่ยงที่เพิ่มเอง');$orgUnitId=$model?(int)$model->org_unit_id:(int)$context['orgUnitId'];$fiscalYear=$model?(int)$model->fiscal_year:(int)($context['fiscalYear']?->fiscal_year?:0);
        if(!$orgUnitId||!$this->canManageRiskUnit($orgUnitId,$fiscalYear))throw new ForbiddenHttpException('ไม่มีสิทธิ์แก้ไขบัญชีความเสี่ยงของหน่วยงานนี้');
        $model=$model?:new RiskRegister(['ref'=>Yii::$app->security->generateRandomString(24),'source_type'=>RiskRegister::SOURCE_MANUAL,'hospital_id'=>$context['hospitalId'],'fiscal_year_id'=>$context['fiscalYearId'],'fiscal_year'=>$fiscalYear,'org_unit_id'=>$orgUnitId,'status'=>RiskRegister::STATUS_ACTIVE]);
        $model->risk_name=trim((string)Yii::$app->request->post('risk_name'));$model->mission_objective=trim((string)Yii::$app->request->post('mission_objective'))?:null;$model->cause=trim((string)Yii::$app->request->post('cause'))?:null;$model->impact=trim((string)Yii::$app->request->post('impact'))?:null;$model->likelihood_score=$this->score(Yii::$app->request->post('likelihood_score'));$model->impact_score=$this->score(Yii::$app->request->post('impact_score'));$model->adequacy=trim((string)Yii::$app->request->post('adequacy'))?:null;$model->residual_risk=trim((string)Yii::$app->request->post('residual_risk'))?:null;$model->existing_control=trim((string)Yii::$app->request->post('existing_control'))?:null;$model->improvement_plan=trim((string)Yii::$app->request->post('improvement_plan'))?:null;$model->responsible_person=trim((string)Yii::$app->request->post('responsible_person'))?:null;$this->touch($model);
        if(!$model->save())Yii::$app->session->setFlash('error',implode(' ',$model->getFirstErrors()));else Yii::$app->session->setFlash('success','บันทึกความเสี่ยงนอกกระบวนงานแล้ว');return $this->redirect(array_merge(['risks'],ContextService::query($context)));
    }

    public function actionDeleteManualRisk(int $id)
    {
        $model=RiskRegister::findOne(['id'=>$id,'source_type'=>RiskRegister::SOURCE_MANUAL]);if(!$model)throw new \yii\web\NotFoundHttpException('ไม่พบความเสี่ยงที่เพิ่มเอง');if(!$this->canManageRiskUnit((int)$model->org_unit_id,(int)$model->fiscal_year))throw new ForbiddenHttpException('ไม่มีสิทธิ์ลบรายการนี้');$model->delete();Yii::$app->session->setFlash('success','ลบความเสี่ยงนอกกระบวนงานแล้ว');return $this->redirect(array_merge(['risks'],ContextService::query((new ContextService())->resolve())));
    }
    public function actionPk1()
    {
        $context=(new ContextService())->resolve();$model=Pk1::find()->with(['hospital','signer'])->where(['hospital_id'=>(int)$context['hospitalId'],'fiscal_year_id'=>(int)$context['fiscalYearId']])->one();$canEdit=(bool)($context['canScopeAllUnits']??false);return $this->render('pk1',compact('context','model','canEdit'));
    }
    public function actionCreatePk1()
    {
        $context=(new ContextService())->resolve();if(!($context['canScopeAllUnits']??false))throw new ForbiddenHttpException('เฉพาะทีมประสานหรือผู้ดูแลระบบเท่านั้นที่เริ่มจัดทำ ปค.1 ได้');$year=(int)($context['fiscalYear']?->fiscal_year?:0);if(!(int)$context['hospitalId']||!(int)$context['fiscalYearId']||!$year)throw new \yii\web\BadRequestHttpException('กรุณาเลือกโรงพยาบาลและปีงบประมาณ');(new \app\modules\iacRisk\services\Pk1Service())->create((int)$context['hospitalId'],(int)$context['fiscalYearId'],$year);Yii::$app->session->setFlash('success','เริ่มแบบ ปค.1 แล้ว');return $this->redirect(array_merge(['pk1'],ContextService::query($context)));
    }
    public function actionSavePk1(int $id)
    {
        $context=(new ContextService())->resolve();if(!($context['canScopeAllUnits']??false))throw new ForbiddenHttpException('ไม่มีสิทธิ์แก้ไข ปค.1');$model=Pk1::findOne(['id'=>$id,'hospital_id'=>(int)$context['hospitalId']]);if(!$model)throw new \yii\web\NotFoundHttpException('ไม่พบ ปค.1');$type=(string)Yii::$app->request->post('signature_type','system');$type=in_array($type,['canvas','system'],true)?$type:'system';$data=trim((string)Yii::$app->request->post('signature_data',''));if($type==='canvas'&&$data!==''&&!preg_match('#^data:image/(png|jpeg);base64,[A-Za-z0-9+/=]+$#',$data))throw new \yii\web\BadRequestHttpException('ข้อมูลลายเซ็นไม่ถูกต้อง');if(strlen($data)>3000000)throw new \yii\web\BadRequestHttpException('ภาพลายเซ็นมีขนาดใหญ่เกินไป');$model->recipient=trim((string)Yii::$app->request->post('recipient'));$model->assessment_text=trim((string)Yii::$app->request->post('assessment_text'));$model->conclusion_text=trim((string)Yii::$app->request->post('conclusion_text'));$model->weakness_text=trim((string)Yii::$app->request->post('weakness_text'))?:null;$model->signer_name=trim((string)Yii::$app->request->post('signer_name'))?:null;$model->signer_position=trim((string)Yii::$app->request->post('signer_position'))?:null;$model->signature_type=$type;$model->signature_data=$type==='canvas'?($data?:null):null;$this->touch($model);if(!$model->save())Yii::$app->session->setFlash('error',implode(' ',$model->getFirstErrors()));else Yii::$app->session->setFlash('success','บันทึก ปค.1 แล้ว');return $this->redirect(array_merge(['pk1'],ContextService::query($context)));
    }
    public function actionPk1Docx(int $id)
    {
        $context=(new ContextService())->resolve();$model=Pk1::find()->with(['hospital','signer'])->where(['id'=>$id,'hospital_id'=>(int)$context['hospitalId']])->one();if(!$model)throw new \yii\web\NotFoundHttpException('ไม่พบ ปค.1');$word=new \PhpOffice\PhpWord\PhpWord();$word->setDefaultFontName('TH Sarabun New');$word->setDefaultFontSize(16);$section=$word->addSection(['paperSize'=>'A4','marginTop'=>900,'marginRight'=>1000,'marginBottom'=>900,'marginLeft'=>1000]);$center=['alignment'=>\PhpOffice\PhpWord\SimpleType\Jc::CENTER,'spaceAfter'=>0];$right=['alignment'=>\PhpOffice\PhpWord\SimpleType\Jc::RIGHT];$justify=['alignment'=>\PhpOffice\PhpWord\SimpleType\Jc::BOTH,'indentation'=>['firstLine'=>850],'spaceAfter'=>150];$section->addText('หนังสือรับรองการประเมินผลการควบคุมภายใน',['bold'=>true,'size'=>18],$center);$section->addText('(แบบ ปค.1)',['bold'=>true,'size'=>18],$center);$section->addTextBreak();$section->addText('เรียน  '.$model->recipient,['bold'=>true]);$section->addTextBreak();$section->addText($model->assessment_text,null,$justify);$section->addText($model->conclusion_text,null,$justify);if($model->weakness_text)$section->addText($model->weakness_text,null,$justify);$section->addTextBreak();$source=$this->signatureSourceForWord($model);if($source)$section->addImage($source,['width'=>120,'height'=>45,'alignment'=>\PhpOffice\PhpWord\SimpleType\Jc::RIGHT]);else $section->addText('ลงชื่อ ..............................................................',null,$right);$section->addText('('.($model->signer_name?:'ผู้อำนวยการโรงพยาบาล').')',null,$right);$section->addText($model->signer_position?:'ผู้อำนวยการโรงพยาบาล',null,$right);$tmp=Yii::getAlias('@runtime/pk1_'.$model->id.'_'.uniqid().'.docx');\PhpOffice\PhpWord\IOFactory::createWriter($word,'Word2007')->save($tmp);$content=file_get_contents($tmp);@unlink($tmp);return Yii::$app->response->sendContentAsFile($content,'PK1_'.$model->fiscal_year.'.docx',['mimeType'=>'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);
    }
    public function actionPk1Pdf(int $id)
    {
        $context=(new ContextService())->resolve();$model=Pk1::find()->with(['hospital','signer'])->where(['id'=>$id,'hospital_id'=>(int)$context['hospitalId']])->one();if(!$model)throw new \yii\web\NotFoundHttpException('ไม่พบ ปค.1');$fontPath=Yii::getAlias('@webroot/fonts/THSarabunNew');$dc=(new \Mpdf\Config\ConfigVariables())->getDefaults();$df=(new \Mpdf\Config\FontVariables())->getDefaults();$mpdf=new \Mpdf\Mpdf(['mode'=>'utf-8','format'=>'A4','margin_left'=>18,'margin_right'=>18,'margin_top'=>18,'margin_bottom'=>18,'fontDir'=>array_merge($dc['fontDir'],[$fontPath]),'fontdata'=>$df['fontdata']+['thsarabunnew'=>['R'=>'THSarabunNew.ttf','B'=>'THSarabunNew-Bold.ttf','I'=>'THSarabunNew-Italic.ttf','BI'=>'THSarabunNew BoldItalic.ttf']],'default_font'=>'thsarabunnew','tempDir'=>Yii::getAlias('@runtime/mpdf')]);$mpdf->SetTitle('ปค.1 '.$model->fiscal_year);$mpdf->WriteHTML($this->renderPartial('_pk1_pdf',['model'=>$model,'signatureDataUri'=>$this->signatureDataUri($model)]));return $mpdf->Output('PK1_'.$model->fiscal_year.'.pdf',\Mpdf\Output\Destination::INLINE);
    }
    public function actionPk4()
    {
        $context=(new ContextService())->resolve();$query=Pk4::find()->with(['items','orgUnit'])->where(['hospital_id'=>(int)$context['hospitalId'],'fiscal_year_id'=>(int)$context['fiscalYearId']]);if($context['orgUnitId'])$query->andWhere(['org_unit_id'=>(int)$context['orgUnitId']]);elseif(!($context['canScopeAllUnits']??false))$query->andWhere('0=1');$models=$query->orderBy(['org_unit_id'=>SORT_ASC])->all();$selected=count($models)===1?$models[0]:null;$canEdit=(int)$context['orgUnitId']>0&&$this->canManageRiskUnit((int)$context['orgUnitId'],(int)($context['fiscalYear']?->fiscal_year?:0));return $this->render('pk4',compact('context','models','selected','canEdit'));
    }
    public function actionCreatePk4()
    {
        $context=(new ContextService())->resolve();$org=(int)$context['orgUnitId'];$year=(int)($context['fiscalYear']?->fiscal_year?:0);if(!$org||!$this->canManageRiskUnit($org,$year))throw new ForbiddenHttpException('ไม่มีสิทธิ์เริ่ม ปค.4');(new \app\modules\iacRisk\services\Pk4Service())->create((int)$context['hospitalId'],(int)$context['fiscalYearId'],$year,$org);Yii::$app->session->setFlash('success','เริ่มแบบ ปค.4 แล้ว');return $this->redirect(array_merge(['pk4'],ContextService::query($context)));
    }
    public function actionSavePk4(int $id)
    {
        $model=Pk4::find()->with('items')->where(['id'=>$id])->one();if(!$model)throw new \yii\web\NotFoundHttpException('ไม่พบ ปค.4');if(!$this->canManageRiskUnit((int)$model->org_unit_id,(int)$model->fiscal_year))throw new ForbiddenHttpException('ไม่มีสิทธิ์แก้ไข ปค.4');
        $values=(array)Yii::$app->request->post('items',[]);$signatureType=(string)Yii::$app->request->post('signature_type','system');$signatureType=in_array($signatureType,['canvas','system'],true)?$signatureType:'system';$signatureData=trim((string)Yii::$app->request->post('signature_data',''));
        if($signatureType==='canvas'&&$signatureData!==''&&!preg_match('#^data:image/(png|jpeg);base64,[A-Za-z0-9+/=]+$#',$signatureData))throw new \yii\web\BadRequestHttpException('ข้อมูลลายเซ็นไม่ถูกต้อง');if(strlen($signatureData)>3000000)throw new \yii\web\BadRequestHttpException('ภาพลายเซ็นมีขนาดใหญ่เกินไป');
        $tx=Yii::$app->db->beginTransaction();try{foreach($model->items as $item){$item->evaluation_summary=trim((string)($values[$item->component_code]??''))?:null;$this->touch($item);$item->save(false);}$model->summary=trim((string)Yii::$app->request->post('summary'))?:null;$model->signer_name=trim((string)Yii::$app->request->post('signer_name'))?:null;$model->signer_position=trim((string)Yii::$app->request->post('signer_position'))?:null;$model->signature_type=$signatureType;$model->signature_data=$signatureType==='canvas'?($signatureData?:null):null;$this->touch($model);$model->save(false);$tx->commit();Yii::$app->session->setFlash('success','บันทึก ปค.4 แล้ว');}catch(\Throwable $e){$tx->rollBack();throw $e;}return $this->redirect(array_merge(['pk4'],ContextService::query((new ContextService())->resolve())));
    }
    public function actionPk4Docx(int $id)
    {
        $model=Pk4::find()->with(['items','orgUnit','signer'])->where(['id'=>$id])->one();
        if(!$model)throw new \yii\web\NotFoundHttpException('ไม่พบ ปค.4');
        $phpWord=new \PhpOffice\PhpWord\PhpWord();$phpWord->setDefaultFontName('TH Sarabun New');$phpWord->setDefaultFontSize(16);
        $section=$phpWord->addSection(['paperSize'=>'A4','marginTop'=>850,'marginRight'=>850,'marginBottom'=>850,'marginLeft'=>850]);$center=['alignment'=>\PhpOffice\PhpWord\SimpleType\Jc::CENTER,'spaceAfter'=>0];$right=['alignment'=>\PhpOffice\PhpWord\SimpleType\Jc::RIGHT];
        $section->addText($model->orgUnit?->name?:'หน่วยงาน',['bold'=>true,'size'=>18],$center);$section->addText('รายงานผลการประเมินองค์ประกอบของการควบคุมภายใน (แบบ ปค.4)',['bold'=>true,'size'=>18],$center);$section->addText('สำหรับปีสิ้นสุดวันที่ 30 กันยายน '.$model->fiscal_year,null,$center);$section->addTextBreak();
        $table=$section->addTable(['borderSize'=>6,'borderColor'=>'000000','cellMargin'=>100,'layout'=>\PhpOffice\PhpWord\Style\Table::LAYOUT_FIXED]);$table->addRow();$table->addCell(3600)->addText('องค์ประกอบการควบคุมภายใน (1)',['bold'=>true],$center);$table->addCell(6500)->addText('ผลการประเมิน / ข้อสรุป (2)',['bold'=>true],$center);foreach($model->items as $item){$table->addRow();$table->addCell(3600)->addText($item->component_name,['bold'=>true]);$table->addCell(6500)->addText($item->evaluation_summary?:'');}
        $section->addTextBreak();$section->addText('สรุปผลการประเมิน',['bold'=>true]);$section->addText($model->summary?:'');$section->addTextBreak();
        $signatureSource=$this->signatureSourceForWord($model);if($signatureSource){$section->addImage($signatureSource,['width'=>120,'height'=>45,'alignment'=>\PhpOffice\PhpWord\SimpleType\Jc::RIGHT]);}else{$section->addText('ลงชื่อ ..............................................................',null,$right);}$section->addText('('.($model->signer_name?:'หัวหน้าหน่วยงาน').')',null,$right);$section->addText($model->signer_position?:'หัวหน้าหน่วยงาน',null,$right);
        $tmp=Yii::getAlias('@runtime/pk4_'.$model->id.'_'.uniqid().'.docx');\PhpOffice\PhpWord\IOFactory::createWriter($phpWord,'Word2007')->save($tmp);$content=file_get_contents($tmp);@unlink($tmp);return Yii::$app->response->sendContentAsFile($content,'PK4_'.$model->fiscal_year.'_unit_'.$model->org_unit_id.'.docx',['mimeType'=>'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);
    }
    public function actionPk4Pdf(int $id)
    {
        $model=Pk4::find()->with(['items','orgUnit','signer'])->where(['id'=>$id])->one();if(!$model)throw new \yii\web\NotFoundHttpException('ไม่พบ ปค.4');
        $fontPath=Yii::getAlias('@webroot/fonts/THSarabunNew');$defaultConfig=(new \Mpdf\Config\ConfigVariables())->getDefaults();$defaultFontConfig=(new \Mpdf\Config\FontVariables())->getDefaults();$mpdf=new \Mpdf\Mpdf(['mode'=>'utf-8','format'=>'A4','orientation'=>'P','margin_left'=>12,'margin_right'=>12,'margin_top'=>12,'margin_bottom'=>15,'fontDir'=>array_merge($defaultConfig['fontDir'],[$fontPath]),'fontdata'=>$defaultFontConfig['fontdata']+['thsarabunnew'=>['R'=>'THSarabunNew.ttf','B'=>'THSarabunNew-Bold.ttf','I'=>'THSarabunNew-Italic.ttf','BI'=>'THSarabunNew BoldItalic.ttf']],'default_font'=>'thsarabunnew','tempDir'=>Yii::getAlias('@runtime/mpdf')]);
        $signatureDataUri=$this->signatureDataUri($model);
        $mpdf->SetTitle('ปค.4 '.$model->fiscal_year);$mpdf->SetHTMLFooter('<div style="border-top:.2mm solid #777;text-align:right;font-size:10pt;color:#555">หน้า {PAGENO} จาก {nbpg}</div>');$mpdf->WriteHTML($this->renderPartial('_pk4_pdf',['model'=>$model,'signatureDataUri'=>$signatureDataUri]));return $mpdf->Output('PK4_'.$model->fiscal_year.'_unit_'.$model->org_unit_id.'.pdf',\Mpdf\Output\Destination::INLINE);
    }
    public function actionPk5()
    {
        $context=(new ContextService())->resolve();$service=new Pk5Service();$rows=$service->rows((int)$context['hospitalId'],(int)$context['fiscalYearId'],(int)$context['orgUnitId']?:null);$errors=$service->errors($rows);return $this->render('pk5',compact('context','rows','errors'));
    }
    public function actionPk5Pdf()
    {
        $context=(new ContextService())->resolve();$service=new Pk5Service();$rows=$service->rows((int)$context['hospitalId'],(int)$context['fiscalYearId'],(int)$context['orgUnitId']?:null);$errors=$service->errors($rows);if(!$rows)throw new \yii\web\BadRequestHttpException('ยังไม่มีข้อมูลสำหรับจัดทำ ปค.5');if($errors)throw new \yii\web\BadRequestHttpException(implode(' ',$errors));
        $hospital=\app\modules\iacRisk\models\Hospital::findOne((int)$context['hospitalId']);$period=null;foreach($context['periods'] as $item)if((int)$item->id===(int)$context['periodId']){$period=$item;break;}$unitIds=array_values(array_unique(array_map(static fn($row)=>(int)$row->org_unit_id,$rows)));$pk4Models=Pk4::find()->with('signer')->where(['hospital_id'=>(int)$context['hospitalId'],'fiscal_year_id'=>(int)$context['fiscalYearId'],'org_unit_id'=>$unitIds])->all();$signers=[];foreach($pk4Models as $pk4)$signers[(int)$pk4->org_unit_id]=['name'=>$pk4->signer_name,'position'=>$pk4->signer_position,'signature'=>$this->signatureDataUri($pk4)];
        $year=(int)($context['fiscalYear']?->fiscal_year?:0);$fontPath=Yii::getAlias('@webroot/fonts/THSarabunNew');$dc=(new \Mpdf\Config\ConfigVariables())->getDefaults();$df=(new \Mpdf\Config\FontVariables())->getDefaults();$mpdf=new \Mpdf\Mpdf(['mode'=>'utf-8','format'=>'A4-L','orientation'=>'L','margin_left'=>7,'margin_right'=>7,'margin_top'=>8,'margin_bottom'=>10,'fontDir'=>array_merge($dc['fontDir'],[$fontPath]),'fontdata'=>$df['fontdata']+['thsarabunnew'=>['R'=>'THSarabunNew.ttf','B'=>'THSarabunNew-Bold.ttf','I'=>'THSarabunNew-Italic.ttf','BI'=>'THSarabunNew BoldItalic.ttf']],'default_font'=>'thsarabunnew','tempDir'=>Yii::getAlias('@runtime/mpdf')]);$mpdf->SetTitle('ปค.5 '.$year);$mpdf->SetHTMLFooter('<div style="text-align:right;font-size:9pt">หน้า {PAGENO} จาก {nbpg}</div>');$mpdf->WriteHTML($this->renderPartial('_pk5_pdf',['rows'=>$rows,'hospitalName'=>$hospital?->name?:'โรงพยาบาล','fiscalYear'=>$year,'period'=>$period,'signers'=>$signers]));return $mpdf->Output('PK5_'.$year.((int)$context['orgUnitId']?'_unit_'.(int)$context['orgUnitId']:'_organization').'.pdf',\Mpdf\Output\Destination::INLINE);
    }
    public function actionTracking()
    {
        $context=(new ContextService())->resolve();$query=RiskFollowup::find()->with(['orgUnit','period'])->where(['hospital_id'=>(int)$context['hospitalId'],'fiscal_year_id'=>(int)$context['fiscalYearId'],'reporting_period_id'=>(int)$context['periodId']]);if($context['orgUnitId'])$query->andWhere(['org_unit_id'=>(int)$context['orgUnitId']]);elseif(!($context['canScopeAllUnits']??false))$query->andWhere('0=1');$items=$query->orderBy(['org_unit_id'=>SORT_ASC,'sequence'=>SORT_ASC])->all();$canEdit=(int)$context['orgUnitId']>0&&$this->canManageRiskUnit((int)$context['orgUnitId'],(int)($context['fiscalYear']?->fiscal_year?:0));$canExport=(bool)($context['canScopeAllUnits']??false)&&!empty($items);return $this->render('tracking',compact('context','items','canEdit','canExport'));
    }
    public function actionCreateFollowup()
    {
        $context=(new ContextService())->resolve();$unit=(int)$context['orgUnitId'];$year=(int)($context['fiscalYear']?->fiscal_year?:0);if(!$unit||!(int)$context['periodId']||!$this->canManageRiskUnit($unit,$year))throw new ForbiddenHttpException('ไม่มีสิทธิ์เริ่มติดตามของหน่วยงานนี้');try{$count=(new RiskFollowupService())->createSnapshot((int)$context['hospitalId'],(int)$context['fiscalYearId'],(int)$context['periodId'],$unit);Yii::$app->session->setFlash('success',$count?'สร้างรายการติดตาม '.$count.' รายการแล้ว':'มีรายการติดตามของรอบนี้แล้ว');}catch(\DomainException $e){Yii::$app->session->setFlash('error',$e->getMessage());}return $this->redirect(array_merge(['tracking'],ContextService::query($context)));
    }
    public function actionSaveFollowup()
    {
        $context=(new ContextService())->resolve();$unit=(int)$context['orgUnitId'];$year=(int)($context['fiscalYear']?->fiscal_year?:0);if(!$unit||!$this->canManageRiskUnit($unit,$year))throw new ForbiddenHttpException('ไม่มีสิทธิ์บันทึกผลติดตาม');$values=(array)Yii::$app->request->post('followups',[]);$models=RiskFollowup::find()->where(['hospital_id'=>(int)$context['hospitalId'],'fiscal_year_id'=>(int)$context['fiscalYearId'],'reporting_period_id'=>(int)$context['periodId'],'org_unit_id'=>$unit])->all();$tx=Yii::$app->db->beginTransaction();try{foreach($models as $model){$row=(array)($values[$model->id]??[]);$status=(string)($row['status_code']??RiskFollowup::NOT_STARTED);$model->status_code=array_key_exists($status,RiskFollowup::statusLabels())?$status:RiskFollowup::NOT_STARTED;$model->followup_method=trim((string)($row['followup_method']??''))?:null;$model->result_summary=trim((string)($row['result_summary']??''))?:null;$model->comment=trim((string)($row['comment']??''))?:null;$this->touch($model);$model->save(false);}$tx->commit();Yii::$app->session->setFlash('success','บันทึกผลติดตามแล้ว');}catch(\Throwable $e){$tx->rollBack();throw $e;}return $this->redirect(array_merge(['tracking'],ContextService::query($context)));
    }
    public function actionTrackingPdf()
    {
        $context=(new ContextService())->resolve();if(!($context['canScopeAllUnits']??false))throw new ForbiddenHttpException('เฉพาะผู้ดูแลภาพรวมองค์กรเท่านั้นที่ส่งออกรายงานรวมได้');$items=RiskFollowup::find()->with(['orgUnit','period'])->where(['hospital_id'=>(int)$context['hospitalId'],'fiscal_year_id'=>(int)$context['fiscalYearId'],'reporting_period_id'=>(int)$context['periodId']])->orderBy(['org_unit_id'=>SORT_ASC,'sequence'=>SORT_ASC])->all();if(!$items)throw new \yii\web\BadRequestHttpException('ยังไม่มีข้อมูลติดตามของรอบนี้');$hospital=\app\modules\iacRisk\models\Hospital::findOne((int)$context['hospitalId']);$director=SiteHelper::viewDirector();$signature=null;$employee=!empty($director['id'])?\app\modules\hr\models\Employees::findOne((int)$director['id']):null;$path=$employee?->signature();if($path&&is_file($path)){$mime=(new \finfo(FILEINFO_MIME_TYPE))->file($path)?:'image/png';$signature='data:'.$mime.';base64,'.base64_encode(file_get_contents($path));}$period=$items[0]->period;$fontPath=Yii::getAlias('@webroot/fonts/THSarabunNew');$dc=(new \Mpdf\Config\ConfigVariables())->getDefaults();$df=(new \Mpdf\Config\FontVariables())->getDefaults();$mpdf=new \Mpdf\Mpdf(['mode'=>'utf-8','format'=>'A4-L','orientation'=>'L','margin_left'=>7,'margin_right'=>7,'margin_top'=>8,'margin_bottom'=>10,'fontDir'=>array_merge($dc['fontDir'],[$fontPath]),'fontdata'=>$df['fontdata']+['thsarabunnew'=>['R'=>'THSarabunNew.ttf','B'=>'THSarabunNew-Bold.ttf','I'=>'THSarabunNew-Italic.ttf','BI'=>'THSarabunNew BoldItalic.ttf']],'default_font'=>'thsarabunnew','tempDir'=>Yii::getAlias('@runtime/mpdf')]);$mpdf->SetTitle('แบบติดตาม ปค.5');$mpdf->SetHTMLFooter('<div style="text-align:right;font-size:9pt">หน้า {PAGENO} จาก {nbpg}</div>');$mpdf->WriteHTML($this->renderPartial('_tracking_pdf',['items'=>$items,'hospitalName'=>$hospital?->name?:'โรงพยาบาล','period'=>$period,'director'=>$director,'signature'=>$signature]));return $mpdf->Output('PK5_followup_period_'.$period->id.'_organization.pdf',\Mpdf\Output\Destination::INLINE);
    }
    public function actionReports()
    {
        $context=(new ContextService())->resolve();$query=RiskReport::find()->with(['items','orgUnit','period'])->where(['hospital_id'=>(int)$context['hospitalId'],'fiscal_year_id'=>(int)$context['fiscalYearId']]);if($context['periodId'])$query->andWhere(['reporting_period_id'=>(int)$context['periodId']]);if($context['orgUnitId'])$query->andWhere(['org_unit_id'=>(int)$context['orgUnitId']]);elseif(!($context['canScopeAllUnits']??false))$query->andWhere('0=1');$reports=$query->orderBy(['org_unit_id'=>SORT_ASC,'revision_no'=>SORT_DESC])->all();$canCreate=(int)$context['orgUnitId']>0&&(int)$context['periodId']>0;$latest=$reports[0]??null;return $this->render('reports',compact('context','reports','canCreate','latest'));
    }
    public function actionCreateRiskReport()
    {
        $context=(new ContextService())->resolve();$unit=(int)$context['orgUnitId'];$year=(int)($context['fiscalYear']?->fiscal_year?:0);$probe=new RiskReport(['org_unit_id'=>$unit]);if(!$unit||!(int)$context['periodId']||!(new RiskReportAccessService())->canPrepare($probe,$year))throw new ForbiddenHttpException('ไม่มีสิทธิ์สร้างรายงานของหน่วยงานนี้');try{(new RiskReportService())->create((int)$context['hospitalId'],(int)$context['fiscalYearId'],(int)$context['periodId'],$unit);Yii::$app->session->setFlash('success','สร้าง Snapshot รายงาน ปค.5 แล้ว');}catch(\DomainException $e){Yii::$app->session->setFlash('error',$e->getMessage());}return $this->redirect(array_merge(['reports'],ContextService::query($context)));
    }
    public function actionSubmitRiskReport(int $id)
    {
        $report=RiskReport::findOne($id);$year=(int)$report?->period?->fiscalYear?->fiscal_year;if(!$report||!(new RiskReportAccessService())->canPrepare($report,$year))throw new ForbiddenHttpException('ไม่มีสิทธิ์ส่งรายงาน');if(!in_array($report->status,[RiskReport::STATUS_DRAFT,RiskReport::STATUS_RETURNED],true))throw new \yii\web\BadRequestHttpException('สถานะรายงานไม่อนุญาตให้ส่ง');$report->status=RiskReport::STATUS_SUBMITTED;$report->submitted_at=date('Y-m-d H:i:s');$report->submitted_by=Yii::$app->user->id;$report->return_note=null;$this->touch($report);$report->save(false);Yii::$app->session->setFlash('success','ส่งรายงานให้หัวหน้าหน่วยงานแล้ว');return $this->redirect(array_merge(['reports'],ContextService::query((new ContextService())->resolve())));
    }
    public function actionApproveRiskReport(int $id)
    {
        $report=RiskReport::findOne($id);if(!$report||$report->status!==RiskReport::STATUS_SUBMITTED||!(new RiskReportAccessService())->canApprove($report))throw new ForbiddenHttpException('ไม่มีสิทธิ์รับรองรายงาน');$report->status=RiskReport::STATUS_APPROVED;$report->approved_at=date('Y-m-d H:i:s');$report->approved_by=Yii::$app->user->id;$this->touch($report);$report->save(false);Yii::$app->session->setFlash('success','หัวหน้าหน่วยงานรับรองรายงานแล้ว');return $this->redirect(array_merge(['reports'],ContextService::query((new ContextService())->resolve())));
    }
    public function actionReturnRiskReport(int $id)
    {
        $report=RiskReport::findOne($id);if(!$report||$report->status!==RiskReport::STATUS_SUBMITTED||!(new RiskReportAccessService())->canApprove($report))throw new ForbiddenHttpException('ไม่มีสิทธิ์ส่งกลับรายงาน');$report->status=RiskReport::STATUS_RETURNED;$report->returned_at=date('Y-m-d H:i:s');$report->returned_by=Yii::$app->user->id;$report->return_note=trim((string)Yii::$app->request->post('return_note'))?:'กรุณาตรวจสอบและแก้ไขข้อมูล';$this->touch($report);$report->save(false);Yii::$app->session->setFlash('success','ส่งกลับรายงานแล้ว');return $this->redirect(array_merge(['reports'],ContextService::query((new ContextService())->resolve())));
    }
    public function actionRiskReportPdf(int $id)
    {
        $report=RiskReport::find()->with(['items','orgUnit','period.fiscalYear'])->where(['id'=>$id])->one();if(!$report||$report->status!==RiskReport::STATUS_APPROVED)throw new \yii\web\NotFoundHttpException('ไม่พบรายงานฉบับรับรอง');$year=(int)$report->period?->fiscalYear?->fiscal_year;$access=new RiskReportAccessService();if(!$access->canPrepare($report,$year)&&!$access->canApprove($report))throw new ForbiddenHttpException('ไม่มีสิทธิ์ดูรายงานของหน่วยงานนี้');$hospital=\app\modules\iacRisk\models\Hospital::findOne($report->hospital_id);$fontPath=Yii::getAlias('@webroot/fonts/THSarabunNew');$dc=(new \Mpdf\Config\ConfigVariables())->getDefaults();$df=(new \Mpdf\Config\FontVariables())->getDefaults();$mpdf=new \Mpdf\Mpdf(['mode'=>'utf-8','format'=>'A4-L','orientation'=>'L','margin_left'=>7,'margin_right'=>7,'margin_top'=>8,'margin_bottom'=>10,'fontDir'=>array_merge($dc['fontDir'],[$fontPath]),'fontdata'=>$df['fontdata']+['thsarabunnew'=>['R'=>'THSarabunNew.ttf','B'=>'THSarabunNew-Bold.ttf','I'=>'THSarabunNew-Italic.ttf','BI'=>'THSarabunNew BoldItalic.ttf']],'default_font'=>'thsarabunnew','tempDir'=>Yii::getAlias('@runtime/mpdf')]);$mpdf->SetTitle('ปค.5 ฉบับรับรอง');$mpdf->SetHTMLFooter('<div style="text-align:right;font-size:9pt">หน้า {PAGENO} จาก {nbpg}</div>');$mpdf->WriteHTML($this->renderPartial('_risk_report_pdf',['report'=>$report,'hospitalName'=>$hospital?->name?:'โรงพยาบาล']));return $mpdf->Output('PK5_certified_period_'.$report->reporting_period_id.'_unit_'.$report->org_unit_id.'_rev_'.$report->revision_no.'.pdf',\Mpdf\Output\Destination::INLINE);
    }
    public function actionHistory() { return $this->placeholder('ประวัติ', 'history'); }

    private function signatureDataUri($model): ?string
    {
        if($model->signature_type==='canvas'&&$model->signature_data)return $model->signature_data;if($model->signature_type!=='system')return null;$path=$model->signer?->signature();if(!$path||!is_file($path))return null;$mime=(new \finfo(FILEINFO_MIME_TYPE))->file($path)?:'image/png';return 'data:'.$mime.';base64,'.base64_encode(file_get_contents($path));
    }

    private function signatureSourceForWord($model): ?string
    {
        if($model->signature_type==='canvas'&&$model->signature_data){$parts=explode(',',$model->signature_data,2);return isset($parts[1])?(base64_decode($parts[1],true)?:null):null;}if($model->signature_type!=='system')return null;$path=$model->signer?->signature();return $path&&is_file($path)?$path:null;
    }

    private function placeholder(string $title, string $active)
    {
        return $this->render('placeholder', ['context' => (new ContextService())->resolve(), 'title' => $title, 'active' => $active]);
    }

    private function editableCsa(int $id): array
    {
        $model=Csa::find()->with('processVersion.profile')->where(['id'=>$id])->one();if(!$model||!($model->processVersion?->profile))throw new \yii\web\NotFoundHttpException('ไม่พบ CSA');
        if(!in_array($model->status,[Csa::STATUS_DRAFT,Csa::STATUS_RETURNED],true))throw new ForbiddenHttpException('CSA สถานะนี้ถูกล็อกการแก้ไข');
        $profile=$model->processVersion->profile;$access=new \app\modules\serviceProfile\services\AccessService();
        if(!$access->canEdit($profile)&&!Yii::$app->user->can('iacRiskCoordinate')&&!Yii::$app->user->can('iacRiskAdmin'))throw new ForbiddenHttpException('คุณไม่มีสิทธิ์แก้ CSA นี้');
        return [$model,$profile];
    }

    private function touch($model): void
    {
        $now=date('Y-m-d H:i:s');$uid=Yii::$app->user->isGuest?null:(int)Yii::$app->user->id;
        if($model->isNewRecord){$model->created_at=$now;$model->created_by=$uid;}$model->updated_at=$now;$model->updated_by=$uid;
    }

    private function score($value): ?int
    {
        $score=(int)$value;return $score>=1&&$score<=5?$score:null;
    }

    private function workflowCsa(int $id): Csa
    {
        $model=Csa::find()->with(['processVersion.profile','steps.risks.controls','steps.risks.plans'])->where(['id'=>$id])->one();if(!$model||!($model->processVersion?->profile))throw new \yii\web\NotFoundHttpException('ไม่พบ CSA');return $model;
    }

    private function logCsaState(Csa $model,string $action,string $from,?string $message=null): void
    {
        $message=$message??['author_confirmed'=>'ผู้จัดทำยืนยัน CSA แล้ว','sent_to_head'=>'ส่ง CSA ให้หัวหน้าหน่วยงานแล้ว','head_approved'=>'หัวหน้าหน่วยงานรับรอง CSA แล้ว'][$action]??null;
        (new ActivityService())->log(['hospital_id'=>$model->hospital_id,'fiscal_year_id'=>$model->fiscal_year_id,'org_unit_id'=>$model->org_unit_id,'entity_type'=>'csa','entity_id'=>$model->id,'action'=>$action,'from_status'=>$from,'to_status'=>$model->status,'message'=>$message]);
    }

    private function saveCsaTransition(Csa $model,string $status,string $action,string $from,?string $message=null,?callable $afterSave=null): void
    {
        $tx=Yii::$app->db->beginTransaction();
        try {
            $model->status=$status;$this->touch($model);$model->save(false);if($afterSave)$afterSave($model);$this->logCsaState($model,$action,$from,$message);$tx->commit();
        } catch (\Throwable $e) {
            $tx->rollBack();throw $e;
        }
    }

    private function canManageRiskUnit(int $orgUnitId,int $fiscalYear): bool
    {
        if($this->access->canScopeAllUnits())return true;$employee=$this->access->employee();if(!$employee||!$fiscalYear)return false;$unit=(new OwnerDirectoryService())->orgUnitForDepartment($employee->department?(int)$employee->department:null,$fiscalYear);return $unit&&(int)$unit->id===$orgUnitId;
    }
}
