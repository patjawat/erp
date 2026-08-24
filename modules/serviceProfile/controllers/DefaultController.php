<?php

namespace app\modules\serviceProfile\controllers;

use app\components\AppHelper;
use app\components\SiteHelper;
use app\components\UserHelper;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;
use app\modules\jd\components\RichText;
use app\modules\serviceProfile\forms\CreateProfileForm;
use app\modules\serviceProfile\models\ServiceProfile;
use app\modules\serviceProfile\models\ServiceProfileActivity;
use app\modules\serviceProfile\models\ServiceProfileAuthor;
use app\modules\serviceProfile\models\ServiceProfileSection;
use app\modules\serviceProfile\models\ServiceProfileSectionComment;
use app\modules\serviceProfile\models\ServiceProfileQualityReviewer;
use app\modules\serviceProfile\services\AccessService;
use app\modules\serviceProfile\services\ProfileService;
use app\modules\serviceProfile\services\WorkflowService;
use app\modules\serviceProfile\services\InboxService;
use app\modules\serviceProfile\services\ReadinessService;
use app\modules\serviceProfile\services\RevisionComparisonService;
use app\modules\serviceProfile\services\OwnerDirectoryService;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class DefaultController extends Controller
{
    private AccessService $access;

    public function init()
    {
        parent::init();
        $this->access = new AccessService();
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => ['class' => AccessControl::class, 'rules' => [['allow' => true, 'roles' => ['@']]]],
            'verbs' => ['class' => VerbFilter::class, 'actions' => [
                'submit' => ['POST'], 'review' => ['POST'], 'approve' => ['POST'],
                'acknowledge' => ['POST'], 'return' => ['POST'],
                'add-section-comment' => ['POST'], 'resolve-section-comment' => ['POST'],
            ]],
        ]);
    }

    public function actionIndex()
    {
        $query = ServiceProfile::find()->with(['authors.employee', 'approvals']);
        $employee = UserHelper::GetEmployee();
        $scope = trim((string) Yii::$app->request->get('scope', 'all'));
        $actionProfileIds = $employee ? (new InboxService())->actionRequiredProfileIds($employee) : [];
        if (!Yii::$app->user->can('serviceProfileAdmin')) {
            if (!$employee) $query->andWhere('0=1');
            else {
                $profileIds = ServiceProfileAuthor::find()->select('service_profile_id')->where(['employee_id' => $employee->id])->column();
                $profileIds = array_merge($profileIds, \app\modules\serviceProfile\models\ServiceProfileApproval::find()->select('service_profile_id')->where(['employee_id' => $employee->id])->column());
                $reviewOwnerIds = \app\modules\serviceProfile\models\ServiceProfileQualityReviewer::find()->select('owner_id')->where([
                    'owner_type' => 'department', 'employee_id' => $employee->id, 'active' => 1,
                ])->column();
                $query->andWhere(['or',
                    ['owner_type' => 'department', 'owner_id' => (int) $employee->department],
                    ['id' => array_values(array_unique(array_map('intval', $profileIds))) ?: [0]],
                    ['owner_type' => 'department', 'owner_id' => array_values(array_unique(array_map('intval', $reviewOwnerIds))) ?: [0]],
                ]);
            }
        }
        $year = (int) Yii::$app->request->get('fiscal_year', 0);
        $status = trim((string) Yii::$app->request->get('status', ''));
        if ($scope === 'action') $query->andWhere(['id' => $actionProfileIds ?: [0]]);
        if ($year > 0) $query->andWhere(['fiscal_year' => $year]);
        if ($status !== '') $query->andWhere(['status' => $status]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query->orderBy(['fiscal_year' => SORT_DESC, 'revision_no' => SORT_DESC]),
            'pagination' => ['pageSize' => 20],
        ]);
        return $this->render('index', ['dataProvider' => $dataProvider, 'year' => $year, 'status' => $status, 'scope' => $scope, 'actionCount' => count($actionProfileIds), 'canCreate' => $this->canCreateProfile($employee)]);
    }

    public function actionCreate()
    {
        $this->assertCanCreate();
        $employee = UserHelper::GetEmployee();
        $fiscalYear = (int) AppHelper::YearBudget();
        $directory = new OwnerDirectoryService();
        $defaultUnit = $directory->orgUnitForDepartment($employee?->department, $fiscalYear);
        $form = new CreateProfileForm([
            'owner_id' => $defaultUnit?->id,
            'fiscal_year' => $fiscalYear,
            'coordinator_id' => $employee?->id,
            'author_ids' => $employee ? [$employee->id] : [],
            'copy_latest' => 1,
        ]);
        if ($form->load(Yii::$app->request->post())) {
            try {
                $profile = (new ProfileService())->createDraft($form);
                Yii::$app->session->setFlash('success', 'สร้าง Service Profile ฉบับร่างแล้ว');
                return $this->redirect(['view', 'id' => $profile->id]);
            } catch (\Throwable $e) { $form->addError('owner_id', $e->getMessage()); }
        }
        return $this->render('create', [
            'model' => $form, 'ownerOptions' => $directory->ownerOptions((int) $form->fiscal_year, (int) $form->owner_id),
            'employeeOptions' => $directory->employeeOptions((int) $form->owner_id, (int) $form->fiscal_year),
        ]);
    }

    public function actionEmployees($owner_id, $fiscal_year = null)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return (new OwnerDirectoryService())->employeeOptions((int) $owner_id, (int) ($fiscal_year ?: AppHelper::YearBudget()));
    }

    public function actionOwners($fiscal_year = null)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return (new OwnerDirectoryService())->ownerOptions((int) ($fiscal_year ?: AppHelper::YearBudget()));
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);
        $this->assertView($model);
        $readiness = in_array($model->status, [ServiceProfile::STATUS_DRAFT, ServiceProfile::STATUS_RETURNED], true)
            ? (new ReadinessService())->inspect($model) : null;
        return $this->render('view', [
            'model' => $model, 'access' => $this->access,
            'currentEmployee' => UserHelper::GetEmployee(),
            'readiness' => $readiness,
        ]);
    }

    public function actionHistory($id)
    {
        $model = $this->findModel($id);
        $this->assertView($model);
        $dataProvider = new ActiveDataProvider([
            'query' => ServiceProfileActivity::find()->where(['service_profile_id' => $model->id])
                ->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC]),
            'pagination' => ['pageSize' => 20],
        ]);
        return $this->render('history', ['model' => $model, 'dataProvider' => $dataProvider]);
    }

    public function actionPdf($id)
    {
        $model = $this->findModel($id);
        $this->assertView($model);
        $fontPath = Yii::getAlias('@webroot/fonts/THSarabunNew');
        $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
        $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8', 'format' => 'A4', 'orientation' => 'P',
            'margin_left' => 12, 'margin_right' => 12, 'margin_top' => 10, 'margin_bottom' => 14,
            'fontDir' => array_merge($defaultConfig['fontDir'], [$fontPath]),
            'fontdata' => $defaultFontConfig['fontdata'] + ['thsarabunnew' => [
                'R' => 'THSarabunNew.ttf', 'B' => 'THSarabunNew-Bold.ttf', 'I' => 'THSarabunNew-Italic.ttf', 'BI' => 'THSarabunNew BoldItalic.ttf',
            ]],
            'default_font' => 'thsarabunnew', 'tempDir' => Yii::getAlias('@runtime/mpdf'),
        ]);
        if ($model->status !== ServiceProfile::STATUS_ACTIVE) {
            $mpdf->SetWatermarkText('ฉบับร่าง', 0.07);
            $mpdf->showWatermarkText = true;
        }
        $mpdf->SetTitle('Service Profile ' . $model->owner_name_snapshot . ' ' . $model->fiscal_year);
        $mpdf->SetAuthor((string) (SiteHelper::getInfo()['company_name'] ?? 'ERP Hospital'));
        $mpdf->SetHTMLFooter('<table style="width:100%;border-top:.3mm solid #777;font-size:10pt;color:#555"><tr><td>Service Profile Revision ' . (int) $model->revision_no . '</td><td style="text-align:right">หน้า {PAGENO} จาก {nbpg}</td></tr></table>');
        $mpdf->WriteHTML($this->renderPartial('_pdf', [
            'model' => $model,
            'siteInfo' => SiteHelper::getInfo(),
            'logoPath' => Yii::getAlias('@webroot/images/Logo-moph.png'),
            'coverData' => $this->buildPdfCoverData($model),
        ]));
        $safeName = preg_replace('/[^a-zA-Z0-9_-]+/', '_', 'Service_Profile_' . $model->owner_id . '_' . $model->fiscal_year . '_R' . $model->revision_no);
        return $mpdf->Output($safeName . '.pdf', \Mpdf\Output\Destination::INLINE);
    }

    public function actionCompare($id)
    {
        $model=$this->findModel($id);$this->assertView($model);
        $previous=$model->supersedes_id?ServiceProfile::find()->with('sections')->where(['id'=>$model->supersedes_id])->one():null;
        if(!$previous) throw new NotFoundHttpException('ไม่พบ Service Profile ฉบับก่อนสำหรับเปรียบเทียบ');
        $this->assertView($previous);
        return $this->render('compare',['model'=>$model,'previous'=>$previous,'comparison'=>(new RevisionComparisonService())->compare($model,$previous)]);
    }

    public function actionUpdateSection($id)
    {
        $section = ServiceProfileSection::find()->with('profile')->where(['id' => $id])->one();
        if (!$section) throw new NotFoundHttpException('ไม่พบหัวข้อ');
        if (!$this->access->canEdit($section->profile)) throw new NotFoundHttpException('ไม่มีสิทธิ์แก้ไขหัวข้อนี้');
        if ($section->load(Yii::$app->request->post())) {
            $section->content = RichText::sanitize((string) $section->content);
            $rawPayload = Yii::$app->request->post('section_payload');
            $payload = is_string($rawPayload) ? json_decode($rawPayload, true) : null;
            if (is_array($payload) && array_key_exists('items', $payload)) {
                $allowedColumns = array_keys(\app\modules\serviceProfile\services\SectionDefinitionService::columns($section->block_type));
                $items = [];
                foreach ((array) $payload['items'] as $item) {
                    if (!is_array($item)) continue;
                    $clean = [];
                    foreach ($allowedColumns as $key) $clean[$key] = RichText::sanitize((string) ($item[$key] ?? ''));
                    if (array_filter($clean, static fn($value) => trim(strip_tags((string) $value)) !== '')) $items[] = $clean;
                }
                $section->setData(['items' => $items]);
            }
            if ($section->save()) {
                (new ProfileService())->log($section->profile, 'section_updated', $section->profile->status, $section->profile->status, 'แก้ไขหัวข้อ ' . $section->title, $section->id);
                Yii::$app->session->setFlash('success', 'บันทึกหัวข้อแล้ว');
                return $this->redirect(['view', 'id' => $section->service_profile_id, '#' => 'section-' . $section->id]);
            }
        }
        return $this->render('update-section', ['model' => $section]);
    }

    public function actionAuthors($id)
    {
        $model = $this->findModel($id);
        if (!$this->access->canEdit($model) && !Yii::$app->user->can('serviceProfileAdmin')) throw new NotFoundHttpException('ไม่มีสิทธิ์กำหนดคณะผู้จัดทำ');
        $selected = array_map('intval', $model->getAuthors()->select('employee_id')->column());
        $coordinator = (int) $model->getAuthors()->select('employee_id')->where(['role' => ServiceProfileAuthor::ROLE_COORDINATOR])->scalar();
        if (Yii::$app->request->isPost) {
            $ids = array_values(array_unique(array_map('intval', (array) Yii::$app->request->post('author_ids', []))));
            $coordinator = (int) Yii::$app->request->post('coordinator_id');
            if ($coordinator <= 0 || !in_array($coordinator, $ids, true)) {
                Yii::$app->session->setFlash('error', 'ผู้ประสานหลักต้องอยู่ในคณะผู้จัดทำ');
            } else {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    ServiceProfileAuthor::deleteAll(['service_profile_id' => $model->id]);
                    foreach ($ids as $employeeId) {
                        $author = new ServiceProfileAuthor([
                            'service_profile_id' => $model->id, 'employee_id' => $employeeId,
                            'role' => $employeeId === $coordinator ? ServiceProfileAuthor::ROLE_COORDINATOR : ServiceProfileAuthor::ROLE_AUTHOR,
                            'assigned_at' => date('Y-m-d H:i:s'), 'assigned_by' => Yii::$app->user->id,
                        ]);
                        if (!$author->save()) throw new \RuntimeException(implode(' ', $author->getFirstErrors()));
                    }
                    $transaction->commit();
                } catch (\Throwable $e) {
                    $transaction->rollBack();
                    throw $e;
                }
                (new ProfileService())->log($model, 'authors_updated', $model->status, $model->status, 'ปรับคณะผู้จัดทำ');
                Yii::$app->session->setFlash('success', 'บันทึกคณะผู้จัดทำแล้ว');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }
        $unit = (new OwnerDirectoryService())->findOrgUnit($model->owner_type, (int) $model->owner_id, (int) $model->fiscal_year);
        return $this->render('authors', ['model' => $model, 'selected' => $selected, 'coordinator' => $coordinator, 'employeeOptions' => $unit ? (new OwnerDirectoryService())->employeeOptions((int) $unit->id, (int) $model->fiscal_year) : $this->employeeOptions((int) $model->owner_id)]);
    }

    public function actionAddSectionComment($id)
    {
        $model=$this->findModel($id);$employee=UserHelper::GetEmployee();
        if(!$employee||!$this->access->canReview($model))throw new NotFoundHttpException('ไม่มีสิทธิ์ให้ความคิดเห็น');
        $sectionId=(int)Yii::$app->request->post('section_id');$comment=trim((string)Yii::$app->request->post('comment'));
        $section=ServiceProfileSection::findOne(['id'=>$sectionId,'service_profile_id'=>$model->id]);
        if(!$section||$comment===''){Yii::$app->session->setFlash('error','กรุณาเลือกหัวข้อและระบุความคิดเห็น');return $this->redirect(['view','id'=>$model->id]);}
        $row=new ServiceProfileSectionComment(['service_profile_id'=>$model->id,'section_id'=>$section->id,'reviewer_employee_id'=>$employee->id,'comment'=>$comment,'status'=>ServiceProfileSectionComment::STATUS_OPEN,'created_at'=>date('Y-m-d H:i:s')]);
        if($row->save()){
            (new ProfileService())->log($model,'section_commented',$model->status,$model->status,'ให้ความคิดเห็นหัวข้อ '.$section->title,$section->id);
            $authors=[];foreach($model->getAuthors()->with('employee.user')->all() as $author)if($author->employee)$authors[]=$author->employee;
            \app\modules\serviceProfile\services\ServiceProfileTelegramService::notifyMany($authors,$model,'มีความคิดเห็นใหม่ใน Service Profile','หัวข้อ: '.$section->title."\n".$comment);
            Yii::$app->session->setFlash('success','บันทึกความคิดเห็นรายหัวข้อแล้ว');
        }
        return $this->redirect(['view','id'=>$model->id]);
    }

    public function actionResolveSectionComment($id)
    {
        $comment=ServiceProfileSectionComment::find()->with(['section.profile','reviewer.user'])->where(['id'=>$id])->one();
        if(!$comment||!$this->access->canEdit($comment->section->profile))throw new NotFoundHttpException('ไม่มีสิทธิ์ปิดความคิดเห็นนี้');
        $comment->status=ServiceProfileSectionComment::STATUS_RESOLVED;$comment->resolved_at=date('Y-m-d H:i:s');$comment->resolved_by_user_id=Yii::$app->user->id;$comment->save(false);
        (new ProfileService())->log($comment->section->profile,'section_comment_resolved',$comment->section->profile->status,$comment->section->profile->status,'แก้ไขความคิดเห็นหัวข้อ '.$comment->section->title,$comment->section_id);
        if($comment->reviewer)\app\modules\serviceProfile\services\ServiceProfileTelegramService::notify($comment->reviewer,$comment->section->profile,'ความคิดเห็น Service Profile ได้รับการแก้ไขแล้ว','หัวข้อ: '.$comment->section->title);
        Yii::$app->session->setFlash('success','ทำเครื่องหมายว่าแก้ไขความคิดเห็นแล้ว');return $this->redirect(['view','id'=>$comment->service_profile_id]);
    }

    public function actionSubmit($id)
    {
        $model = $this->findModel($id);
        if (!$this->access->canSubmit($model)) throw new NotFoundHttpException('เฉพาะผู้ประสานหลักเท่านั้นที่ส่งเอกสารได้');
        try { (new WorkflowService())->submit($model); Yii::$app->session->setFlash('success', 'ส่งให้ผู้แทนคุณภาพเห็นชอบแล้ว'); }
        catch (\Throwable $e) { Yii::$app->session->setFlash('error', $e->getMessage()); }
        return $this->redirect(['view', 'id' => $model->id]);
    }

    public function actionReview($id)
    {
        $model = $this->findModel($id); $employee = UserHelper::GetEmployee();
        if (!$this->access->canReview($model)) throw new NotFoundHttpException('ไม่มีสิทธิ์ตรวจเอกสารนี้');
        $decision = (string) Yii::$app->request->post('decision', 'commented');
        $comment = (string) Yii::$app->request->post('comment', '');
        try {
            $workflow = new WorkflowService();
            if ($decision === 'endorsed') {
                if (!$this->access->isLeadReviewer($model)) throw new \DomainException('เฉพาะผู้แทนคุณภาพหลักเท่านั้นที่เห็นชอบขั้นสุดท้ายได้');
                $workflow->endorse($model, $employee, $comment);
            } elseif ($decision === 'returned') {
                $workflow->returnForCorrection($model, $employee, $comment);
            } else $workflow->saveReview($model, $employee, 'commented', $comment);
            Yii::$app->session->setFlash('success', 'บันทึกผลการตรวจแล้ว');
        } catch (\Throwable $e) { Yii::$app->session->setFlash('error', $e->getMessage()); }
        return $this->redirect(['view', 'id' => $model->id]);
    }

    public function actionApprove($id)
    {
        $model = $this->findModel($id); $employee = UserHelper::GetEmployee();
        if (!$this->access->canActStage($model, \app\modules\serviceProfile\models\ServiceProfileApproval::STAGE_DIRECTOR)) throw new NotFoundHttpException('ไม่มีสิทธิ์อนุมัติเอกสารนี้');
        try { (new WorkflowService())->approve($model, $employee, (string) Yii::$app->request->post('comment')); Yii::$app->session->setFlash('success', 'อนุมัติเอกสารแล้ว'); }
        catch (\Throwable $e) { Yii::$app->session->setFlash('error', $e->getMessage()); }
        return $this->redirect(['view', 'id' => $model->id]);
    }

    public function actionReturn($id)
    {
        $model = $this->findModel($id); $employee = UserHelper::GetEmployee();
        try { (new WorkflowService())->returnForCorrection($model, $employee, (string) Yii::$app->request->post('comment')); Yii::$app->session->setFlash('success', 'ส่งกลับให้แก้ไขแล้ว'); }
        catch (\Throwable $e) { Yii::$app->session->setFlash('error', $e->getMessage()); }
        return $this->redirect(['view', 'id' => $model->id]);
    }

    public function actionAcknowledge($id)
    {
        $model = $this->findModel($id); $employee = UserHelper::GetEmployee();
        if (!$this->access->canActStage($model, \app\modules\serviceProfile\models\ServiceProfileApproval::STAGE_HEAD)) throw new NotFoundHttpException('ไม่มีสิทธิ์รับทราบเอกสารนี้');
        try { (new WorkflowService())->acknowledge($model, $employee, (string) Yii::$app->request->post('comment')); Yii::$app->session->setFlash('success', 'รับทราบและประกาศใช้ Service Profile แล้ว'); }
        catch (\Throwable $e) { Yii::$app->session->setFlash('error', $e->getMessage()); }
        return $this->redirect(['view', 'id' => $model->id]);
    }

    private function findModel($id): ServiceProfile
    {
        $model = ServiceProfile::find()->with(['sections', 'authors.employee', 'approvals.employee', 'reviews.reviewer', 'sectionComments.section', 'sectionComments.reviewer'])->where(['id' => $id])->one();
        if (!$model) throw new NotFoundHttpException('ไม่พบ Service Profile');
        return $model;
    }
    private function assertView(ServiceProfile $model): void { if (!$this->access->canView($model)) throw new NotFoundHttpException('ไม่พบ Service Profile'); }
    private function assertCanCreate(): void
    {
        if (!$this->canCreateProfile(UserHelper::GetEmployee())) throw new NotFoundHttpException('เฉพาะหัวหน้าหน่วยงานหรือผู้ดูแลระบบเท่านั้นที่สร้างฉบับใหม่ได้');
    }
    private function canCreateProfile(?Employees $employee): bool
    {
        if (Yii::$app->user->can('serviceProfileAdmin') || Yii::$app->user->can('serviceProfileTemplateManage')) return true;
        $owner = $employee ? Organization::findOne($employee->department) : null;
        return $employee && $owner && (int) ($owner->leader?->id) === (int) $employee->id;
    }
    private function employeeOptions(int $ownerId): array
    {
        $employees = Employees::find()->where(['status' => 1])->orderBy(['fname' => SORT_ASC])->all();
        return ArrayHelper::map($employees, 'id', static fn(Employees $employee) => $employee->fullname());
    }

    private function buildPdfCoverData(ServiceProfile $model): array
    {
        $unit = (new OwnerDirectoryService())->findOrgUnit($model->owner_type, (int) $model->owner_id, (int) $model->fiscal_year);
        $owner = $model->owner_type === 'department' ? Organization::findOne($model->owner_id) : null;
        $coordinator = null;
        $authors = [];
        foreach ($model->authors as $author) {
            $name = $author->employee?->fullname();
            if ($name) $authors[] = $name;
            if ($author->role === ServiceProfileAuthor::ROLE_COORDINATOR) $coordinator = $name;
        }
        $leadReviewer = ServiceProfileQualityReviewer::find()->with('employee')->where([
            'owner_type' => $model->owner_type, 'owner_id' => $model->owner_id,
            'active' => 1, 'is_lead' => 1,
        ])->one()?->employee?->fullname();
        $directorUserIds = (array) Yii::$app->db->createCommand(
            'SELECT user_id FROM {{%auth_assignment}} WHERE item_name=:permission',
            [':permission' => 'serviceProfileDirectorApprove']
        )->queryColumn();
        $director = $directorUserIds
            ? Employees::find()->where(['user_id' => $directorUserIds, 'status' => 1])->orderBy(['id' => SORT_ASC])->one()?->fullname()
            : null;
        $intro = $model->sections[0]->content ?? '';
        $intro = preg_replace('/\s+/u', ' ', trim(strip_tags((string) $intro)));
        if (mb_strlen($intro) > 430) $intro = mb_substr($intro, 0, 427) . '...';
        $preparedAt = $model->created_at ?: date('Y-m-d H:i:s');

        return [
            'document_name' => 'Service Profile - ' . $model->owner_name_snapshot,
            'document_code' => sprintf('SP-%03d-%d-R%d', (int) $model->owner_id, (int) $model->fiscal_year, (int) $model->revision_no),
            'owner_name' => $model->owner_name_snapshot,
            'organization_path' => $owner?->pathLabel(' / ') ?: ($unit?->name ?: $model->owner_name_snapshot),
            'description' => $intro ?: 'เอกสารแสดงบริบท ขอบเขตบริการ กระบวนการสำคัญ การบริหารความเสี่ยง ตัวชี้วัด และแผนพัฒนาคุณภาพของหน่วยงาน',
            'prepared_by' => $coordinator ?: ($authors[0] ?? 'คณะผู้จัดทำ Service Profile'),
            'reviewed_by' => $leadReviewer ?: 'ผู้แทนคุณภาพที่ได้รับมอบหมาย',
            'approved_by' => $director ?: 'ผู้อำนวยการ/ผู้บริหารที่รับผิดชอบ',
            'prepared_date' => Yii::$app->formatter->asDate($preparedAt, 'php:d/m/Y'),
            'reference_standard' => 'มาตรฐาน HA ฉบับที่ 6 และแผนพัฒนาระบบบริการสุขภาพ (Service Plan) รวมถึงกฎหมายและมาตรฐานที่เกี่ยวข้อง',
            'review_cycle' => 'อย่างน้อยปีละ 1 ครั้ง หรือเมื่อมีการเปลี่ยนแปลงสำคัญ',
            'distribution' => 'บุคลากรของหน่วยงานและผู้เกี่ยวข้องผ่านระบบเอกสารคุณภาพ',
        ];
    }

}
