<?php

namespace app\modules\iacRisk\controllers;

use app\modules\iacRisk\models\Activity;
use app\modules\iacRisk\models\Csa;
use app\modules\iacRisk\models\CsaStep;
use app\modules\iacRisk\models\CsaRisk;
use app\modules\iacRisk\models\RiskControl;
use app\modules\iacRisk\models\ControlAssessment;
use app\modules\iacRisk\models\ImprovementPlan;
use app\modules\iacRisk\models\ServiceProcessVersion;
use app\modules\iacRisk\services\AccessService;
use app\modules\iacRisk\services\ActivityService;
use app\modules\iacRisk\services\ContextService;
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
        $this->saveCsaTransition($model,Csa::STATUS_HEAD_APPROVED,'head_approved',$from);
        Yii::$app->session->setFlash('success','หัวหน้าหน่วยงานรับรอง CSA แล้ว');return $this->redirect(['csa-view','id'=>$model->id]);
    }

    public function actionReturnCsa(int $id)
    {
        $model=$this->workflowCsa($id);if(!(new \app\modules\iacRisk\services\CsaAccessService())->canHeadAct($model))throw new ForbiddenHttpException('ไม่มีสิทธิ์ส่งกลับ CSA');$note=trim((string)Yii::$app->request->post('return_note'));if($note==='')throw new \yii\web\BadRequestHttpException('กรุณาระบุเหตุผลที่ส่งกลับ');$from=$model->status;$model->return_note=$note;
        $this->saveCsaTransition($model,Csa::STATUS_RETURNED,'returned',$from,$note);
        Yii::$app->session->setFlash('success','ส่งกลับให้ผู้จัดทำแก้ไขแล้ว');return $this->redirect(['csa-view','id'=>$model->id]);
    }
    public function actionRisks() { return $this->placeholder('บัญชีความเสี่ยง', 'risks'); }
    public function actionPk4() { return $this->placeholder('ปค.4', 'pk4'); }
    public function actionPk5() { return $this->placeholder('ปค.5', 'pk5'); }
    public function actionTracking() { return $this->placeholder('ติดตามผล', 'tracking'); }
    public function actionHistory() { return $this->placeholder('ประวัติ', 'history'); }

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

    private function saveCsaTransition(Csa $model,string $status,string $action,string $from,?string $message=null): void
    {
        $tx=Yii::$app->db->beginTransaction();
        try {
            $model->status=$status;$this->touch($model);$model->save(false);$this->logCsaState($model,$action,$from,$message);$tx->commit();
        } catch (\Throwable $e) {
            $tx->rollBack();throw $e;
        }
    }
}
