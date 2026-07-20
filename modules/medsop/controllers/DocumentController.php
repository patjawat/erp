<?php

namespace app\modules\medsop\controllers;

use app\modules\hr\models\Organization;
use app\modules\hr\models\Employees;
use app\modules\hr\models\TeamGroup;
use app\modules\hr\models\TeamGroupDetail;
use app\components\AppHelper;
use app\modules\medsop\models\Document;
use app\modules\medsop\models\DocumentSearch;
use app\modules\medsop\models\DocumentAudience;
use app\modules\medsop\models\DocumentAssignment;
use app\modules\medsop\models\MedSopSetting;
use app\modules\medsop\models\OrganizationSetting;
use app\modules\medsop\models\TeamSetting;
use app\modules\medsop\services\DocumentAccessService;
use app\modules\medsop\services\AudienceConfigurationService;
use app\modules\medsop\services\DocumentAssignmentService;
use app\modules\medsop\services\DocumentReadTrackingService;
use app\modules\medsop\services\DocumentService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Html;
use yii\helpers\FileHelper;
use yii\db\Expression;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

class DocumentController extends Controller
{
    private $accessService;

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [['allow' => true, 'roles' => ['@']]],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'publish' => ['POST'],
                    'audience-publish' => ['POST'],
                    'acknowledge' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $access = $this->access();
        $searchModel = new DocumentSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $access);
        $dataProvider->query->with(['steps.media']);
        $models = $dataProvider->getModels();
        $organizationIds = array_values(array_unique(array_filter(array_map(static function (Document $model) {
            return (int) $model->organization_id;
        }, $models))));
        $organizations = $organizationIds
            ? Organization::find()->where(['id' => $organizationIds])->indexBy('id')->all()
            : [];

        $optionQuery = Document::find()->alias('d')->andWhere(['d.deleted_at' => null]);
        $access->applyVisibleScope($optionQuery);
        $filterOrganizationIds = (clone $optionQuery)->select('d.organization_id')->distinct()->column();
        $filterCreatorIds = (clone $optionQuery)->select('d.created_emp_id')->andWhere(['not', ['d.created_emp_id' => null]])->distinct()->column();
        $categoryOptions = (clone $optionQuery)->select('d.category')->andWhere(['not', ['d.category' => null]])->distinct()->orderBy(['d.category' => SORT_ASC])->column();
        $filterOrganizations = $filterOrganizationIds ? Organization::find()->where(['id' => $filterOrganizationIds])->orderBy(['root' => SORT_ASC, 'lft' => SORT_ASC])->all() : [];
        $filterCreators = $filterCreatorIds ? Employees::find()->where(['id' => $filterCreatorIds])->orderBy(['fname' => SORT_ASC, 'lname' => SORT_ASC])->all() : [];
        $pageCreatorIds = array_values(array_unique(array_filter(array_map(static function (Document $document) { return (int) $document->created_emp_id; }, $models))));
        $creators = $pageCreatorIds ? Employees::find()->where(['id' => $pageCreatorIds])->indexBy('id')->all() : [];

        return $this->render('index', compact('searchModel', 'dataProvider', 'organizations', 'creators', 'filterOrganizations', 'filterCreators', 'categoryOptions', 'access'));
    }

    public function actionDashboard()
    {
        $access = $this->access();
        $query = Document::find()->alias('d')->andWhere(['d.deleted_at' => null, 'd.status' => Document::STATUS_PUBLISHED]);
        $access->applyVisibleScope($query);

        $statusCounts = (clone $query)->select(['status', 'total' => 'COUNT(*)'])->groupBy('status')->indexBy('status')->asArray()->all();
        $typeCounts = (clone $query)->select(['document_type', 'total' => 'COUNT(*)'])->groupBy('document_type')->indexBy('document_type')->asArray()->all();
        $organizationCounts = (clone $query)->select(['organization_id', 'total' => 'COUNT(*)'])->groupBy('organization_id')->orderBy(['total' => SORT_DESC])->limit(6)->asArray()->all();
        $organizationIds = array_column($organizationCounts, 'organization_id');
        $organizations = $organizationIds ? Organization::find()->where(['id' => $organizationIds])->indexBy('id')->all() : [];
        $recentDocuments = (clone $query)->orderBy(['d.updated_at' => SORT_DESC])->limit(7)->all();

        return $this->render('dashboard', compact('access', 'statusCounts', 'typeCounts', 'organizationCounts', 'organizations', 'recentDocuments'));
    }

    public function actionReport()
    {
        $access = $this->access();
        $query = Document::find()->alias('d')->andWhere(['d.deleted_at' => null]);
        $access->applyVisibleScope($query);
        $statusCounts = (clone $query)->select(['status', 'total' => 'COUNT(*)'])->groupBy('status')->indexBy('status')->asArray()->all();
        $typeCounts = (clone $query)->select(['document_type', 'total' => 'COUNT(*)'])->groupBy('document_type')->indexBy('document_type')->asArray()->all();
        $monthlyCounts = (clone $query)->select(['month' => "DATE_FORMAT(d.created_at, '%Y-%m')", 'total' => 'COUNT(*)'])
            ->andWhere(['>=', 'd.created_at', date('Y-m-01 00:00:00', strtotime('-11 months'))])
            ->groupBy(["DATE_FORMAT(d.created_at, '%Y-%m')"])->orderBy(['month' => SORT_ASC])->asArray()->all();

        $monthlyIndex = array_column($monthlyCounts, 'total', 'month');
        $monthlyCounts = [];
        for ($offset = 11; $offset >= 0; $offset--) {
            $month = date('Y-m', strtotime('-' . $offset . ' months'));
            $monthlyCounts[] = ['month' => $month, 'total' => (int) ($monthlyIndex[$month] ?? 0)];
        }

        $organizationCounts = (clone $query)
            ->select(['organization_id', 'total' => 'COUNT(*)'])
            ->groupBy('organization_id')
            ->orderBy(['total' => SORT_DESC])
            ->limit(6)
            ->asArray()
            ->all();
        $organizationIds = array_column($organizationCounts, 'organization_id');
        $organizations = $organizationIds
            ? Organization::find()->where(['id' => $organizationIds])->indexBy('id')->all()
            : [];
        $recentDocuments = (clone $query)
            ->with('organization')
            ->orderBy(['d.updated_at' => SORT_DESC])
            ->limit(8)
            ->all();
        $reviewDueCount = (int) (clone $query)
            ->andWhere(['d.status' => Document::STATUS_PUBLISHED])
            ->andWhere(['not', ['d.review_date' => null]])
            ->andWhere(['<=', 'd.review_date', date('Y-m-d', strtotime('+90 days'))])
            ->count();

        return $this->render('report', compact(
            'access',
            'statusCounts',
            'typeCounts',
            'monthlyCounts',
            'organizationCounts',
            'organizations',
            'recentDocuments',
            'reviewDueCount'
        ));
    }

    public function actionSetting()
    {
        $access = $this->access();
        $saveError = null;
        if (!$access->isAdmin()) {
            throw new ForbiddenHttpException('เฉพาะผู้ดูแลระบบเท่านั้นที่ตั้งค่า MedSOP ได้');
        }
        $organizations = Organization::find()->where(['active' => 1])->orderBy(['root' => SORT_ASC, 'lft' => SORT_ASC])->all();
        $organizationsById = [];
        foreach ($organizations as $organization) {
            $organizationsById[(int) $organization->id] = $organization;
        }
        $organizationSettings = OrganizationSetting::find()->indexBy('organization_id')->all();
        $teamGroups = TeamGroup::find()->where(['deleted_at' => null])->orderBy(['title' => SORT_ASC])->all();
        $teamGroupsById = [];
        foreach ($teamGroups as $teamGroup) {
            $teamGroupsById[(int) $teamGroup->id] = $teamGroup;
        }
        $teamSettings = TeamSetting::find()->indexBy('team_group_id')->all();

        if (Yii::$app->request->isPost) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $settings = (array) Yii::$app->request->post('settings', []);
                $prefixes = [
                    MedSopSetting::SOP_PREFIX => strtoupper(trim((string) ($settings['sop_prefix'] ?? 'SP'))),
                    MedSopSetting::WI_PREFIX => strtoupper(trim((string) ($settings['wi_prefix'] ?? 'WI'))),
                    MedSopSetting::CODE_PATTERN => trim((string) ($settings['code_pattern'] ?? '{type}-{org}-{year}-{sequence}')),
                ];
                $categories = array_values(array_unique(array_filter(array_map('trim', preg_split('/\R/u', (string) ($settings['document_categories'] ?? ''))))));
                $documentTypes = [];
                foreach (preg_split('/\R/u', (string) ($settings['document_types'] ?? '')) as $line) {
                    $parts = array_map('trim', explode('|', $line, 2));
                    $code = strtoupper((string) ($parts[0] ?? ''));
                    $label = (string) ($parts[1] ?? $code);
                    if ($code === '') continue;
                    if (!preg_match('/^[A-Z0-9_-]{1,10}$/', $code)) {
                        throw new \RuntimeException('รหัสประเภทเอกสารต้องเป็น A-Z, 0-9, ขีดกลาง หรือขีดล่าง ไม่เกิน 10 ตัว');
                    }
                    $documentTypes[$code] = $label !== '' ? $label : $code;
                }
                $statusLabels = array_values(array_unique(array_filter(array_map('trim', preg_split('/\R/u', (string) ($settings['announcement_statuses'] ?? ''))))));
                if ($documentTypes === [] || $categories === [] || $statusLabels === []) {
                    throw new \RuntimeException('กรุณากำหนดประเภท หมวดหมู่ และสถานะประกาศใช้อย่างน้อย 1 รายการ');
                }
                $usedTypes = Document::find()->select('document_type')->distinct()->column();
                $removedUsedTypes = array_diff($usedTypes, array_keys($documentTypes));
                if ($removedUsedTypes !== []) {
                    throw new \RuntimeException('ลบประเภทที่มีเอกสารใช้งานอยู่ไม่ได้: ' . implode(', ', $removedUsedTypes));
                }
                $announcementStatuses = [];
                $currentStatuses = MedSopSetting::listValue(MedSopSetting::ANNOUNCEMENT_STATUSES, []);
                foreach ($statusLabels as $index => $label) {
                    $existingKey = array_search($label, $currentStatuses, true);
                    $announcementStatuses[$existingKey !== false ? $existingKey : 'STATUS_' . ($index + 1)] = $label;
                }
                $prefixes[MedSopSetting::DOCUMENT_CATEGORIES] = json_encode($categories, JSON_UNESCAPED_UNICODE);
                $prefixes[MedSopSetting::DOCUMENT_TYPES] = json_encode($documentTypes, JSON_UNESCAPED_UNICODE);
                $prefixes[MedSopSetting::ANNOUNCEMENT_STATUSES] = json_encode($announcementStatuses, JSON_UNESCAPED_UNICODE);
                if (!preg_match('/^[A-Z0-9_-]{1,10}$/', $prefixes[MedSopSetting::SOP_PREFIX]) || !preg_match('/^[A-Z0-9_-]{1,10}$/', $prefixes[MedSopSetting::WI_PREFIX])) {
                    throw new \RuntimeException('อักษรย่อ SOP/WI ต้องเป็นอักษรอังกฤษตัวพิมพ์ใหญ่ ตัวเลข ขีดกลาง หรือขีดล่าง ไม่เกิน 10 ตัว');
                }
                foreach (['{type}', '{org}', '{year}', '{sequence}'] as $token) {
                    if (strpos($prefixes[MedSopSetting::CODE_PATTERN], $token) === false) {
                        throw new \RuntimeException('รูปแบบรหัสต้องมีตัวแปร ' . $token);
                    }
                }
                foreach ($prefixes as $key => $value) {
                    if (!MedSopSetting::setValue($key, $value)) {
                        throw new \RuntimeException('ไม่สามารถบันทึกรูปแบบรหัสเอกสารได้');
                    }
                }
                foreach ((array) Yii::$app->request->post('organizations', []) as $organizationId => $row) {
                    $organizationId = (int) $organizationId;
                    $model = $organizationSettings[$organizationId] ?? new OrganizationSetting(['organization_id' => $organizationId]);
                    $organizationCode = strtoupper(trim((string) ($row['code'] ?? '')));
                    $model->code = $organizationCode !== '' ? $organizationCode : null;
                    $organizationCategories = array_values(array_unique(array_filter(array_map('trim', preg_split('/\R/u', (string) ($row['document_categories'] ?? ''))))));
                    $model->document_categories = $organizationCategories ? json_encode($organizationCategories, JSON_UNESCAPED_UNICODE) : null;
                    $model->active = !empty($row['active']);
                    $model->updated_by = Yii::$app->user->id;
                    $model->updated_at = date('Y-m-d H:i:s');
                    if ($model->isNewRecord) {
                        $model->created_by = Yii::$app->user->id;
                        $model->created_at = date('Y-m-d H:i:s');
                    }
                    if (!$model->save()) {
                        $organizationName = isset($organizationsById[$organizationId])
                            ? $organizationsById[$organizationId]->name
                            : 'รหัสหน่วยงาน ' . $organizationId;
                        throw new \RuntimeException($organizationName . ': ' . implode(' ', $model->getFirstErrors()));
                    }
                }
                foreach ((array) Yii::$app->request->post('teams', []) as $teamGroupId => $row) {
                    $teamGroupId = (int) $teamGroupId;
                    $model = $teamSettings[$teamGroupId] ?? new TeamSetting(['team_group_id' => $teamGroupId]);
                    $teamCode = strtoupper(trim((string) ($row['code'] ?? '')));
                    $model->code = $teamCode !== '' ? $teamCode : null;
                    $teamCategories = array_values(array_unique(array_filter(array_map('trim', preg_split('/\R/u', (string) ($row['document_categories'] ?? ''))))));
                    $model->document_categories = $teamCategories ? json_encode($teamCategories, JSON_UNESCAPED_UNICODE) : null;
                    $model->leader_employee_id = !empty($row['leader_employee_id']) ? (int) $row['leader_employee_id'] : null;
                    $model->active = !empty($row['active']);
                    $model->updated_by = Yii::$app->user->id;
                    $model->updated_at = date('Y-m-d H:i:s');
                    if ($model->isNewRecord) {
                        $model->created_by = Yii::$app->user->id;
                        $model->created_at = date('Y-m-d H:i:s');
                    }
                    if (!$model->save()) {
                        $teamName = isset($teamGroupsById[$teamGroupId])
                            ? $teamGroupsById[$teamGroupId]->title
                            : 'รหัสทีม ' . $teamGroupId;
                        throw new \RuntimeException($teamName . ': ' . implode(' ', $model->getFirstErrors()));
                    }
                }
                $transaction->commit();
                Yii::$app->session->setFlash('success', 'บันทึกการตั้งค่า MedSOP แล้ว');
                return $this->redirect(['setting']);
            } catch (\Throwable $e) {
                if ($transaction->isActive) $transaction->rollBack();
                $saveError = $e->getMessage();
                Yii::error([
                    'message' => $e->getMessage(),
                    'exception' => get_class($e),
                    'trace' => $e->getTraceAsString(),
                ], 'medsop.setting.save');
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }

        $organizationLeaderIds = [];
        foreach ($organizations as $organization) {
            $data = is_array($organization->data_json)
                ? $organization->data_json
                : (json_decode((string) $organization->data_json, true) ?: []);
            $leaderId = (int) ($data['leader_1'] ?? $data['leader1'] ?? 0);
            if ($leaderId > 0) $organizationLeaderIds[(int) $organization->id] = $leaderId;
        }
        $teamChairIds = [];
        foreach ($teamSettings as $teamGroupId => $teamSetting) {
            if (!empty($teamSetting->leader_employee_id)) {
                $teamChairIds[(int) $teamGroupId] = (int) $teamSetting->leader_employee_id;
            }
        }
        $teamIds = array_keys($teamGroupsById);
        if ($teamIds !== []) {
            $committeeRows = TeamGroupDetail::find()
                ->where(['name' => 'committee', 'category_id' => $teamIds, 'deleted_at' => null])
                ->orderBy(['thai_year' => SORT_DESC, 'id' => SORT_DESC])
                ->all();
            foreach ($committeeRows as $committeeRow) {
                $teamId = (int) $committeeRow->category_id;
                if (isset($teamChairIds[$teamId])) continue;
                $data = is_array($committeeRow->data_json) ? $committeeRow->data_json : (json_decode((string) $committeeRow->data_json, true) ?: []);
                if ((string) ($data['committee_id'] ?? '') === '1' || mb_strpos((string) ($data['committee_name'] ?? ''), 'ประธาน') !== false) {
                    $teamChairIds[$teamId] = (int) $committeeRow->emp_id;
                }
            }
        }
        $responsibleEmployeeIds = array_values(array_unique(array_filter(array_merge(array_values($organizationLeaderIds), array_values($teamChairIds)))));
        $responsibleEmployees = $responsibleEmployeeIds
            ? Employees::find()->where(['id' => $responsibleEmployeeIds])->indexBy('id')->all()
            : [];
        return $this->render('setting', [
            'access' => $access,
            'organizations' => $organizations,
            'organizationSettings' => $organizationSettings,
            'sopPrefix' => MedSopSetting::value(MedSopSetting::SOP_PREFIX, 'SP'),
            'wiPrefix' => MedSopSetting::value(MedSopSetting::WI_PREFIX, 'WI'),
            'codePattern' => MedSopSetting::value(MedSopSetting::CODE_PATTERN, '{type}-{org}-{year}-{sequence}'),
            'documentTypes' => MedSopSetting::documentTypes(),
            'categories' => MedSopSetting::listValue(MedSopSetting::DOCUMENT_CATEGORIES, ['SOP', 'WI']),
            'announcementStatuses' => MedSopSetting::listValue(MedSopSetting::ANNOUNCEMENT_STATUSES, ['ACTIVE' => 'ประกาศใช้']),
            'teamGroups' => $teamGroups,
            'teamSettings' => $teamSettings,
            'organizationLeaderIds' => $organizationLeaderIds,
            'teamChairIds' => $teamChairIds,
            'responsibleEmployees' => $responsibleEmployees,
            'saveError' => $saveError,
        ]);
    }

    public function actionCoordinatorEmployees($q = '')
    {
        if (!$this->access()->isAdmin()) {
            throw new ForbiddenHttpException('เฉพาะผู้ดูแลระบบเท่านั้น');
        }
        Yii::$app->response->format = Response::FORMAT_JSON;
        $query = Employees::find()->where(['status' => 1]);
        $term = trim((string) $q);
        if ($term !== '') {
            $query->andWhere(['or', ['like', 'fname', $term], ['like', 'lname', $term]]);
        }
        $employees = $query->orderBy(['fname' => SORT_ASC, 'lname' => SORT_ASC])->limit(30)->all();
        return ['results' => array_map(static function (Employees $employee) {
            return [
                'id' => (int) $employee->id,
                'fullname' => $employee->fullname(),
                'position_name' => $employee->positionName(),
            ];
        }, $employees)];
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);
        $access = $this->access();
        if (!$access->canView($model)) {
            Yii::$app->response->statusCode = 403;
            return $this->render('access-denied', ['model' => $model]);
        }
        $assignment = null;
        $employee = $access->currentEmployee();
        if ($employee !== null && $model->status === Document::STATUS_PUBLISHED) {
            $assignment = DocumentAssignment::findOne([
                'document_id' => (int) $model->id,
                'revision_no' => (int) $model->current_revision,
                'employee_id' => (int) $employee->id,
            ]);
            if ($assignment !== null) {
                try {
                    (new DocumentReadTrackingService())->recordOpen($assignment, (int) $employee->id);
                } catch (\Throwable $e) {
                    Yii::error($e, __METHOD__);
                    Yii::$app->session->setFlash('error', 'ไม่สามารถบันทึกเวลาเปิดอ่านเอกสารได้');
                }
            }
        }
        $linkedWorkInstructions = [];
        if ($model->document_type === Document::TYPE_SOP) {
            $linkedWorkInstructionQuery = Document::find()->alias('d')
                ->andWhere([
                    'd.document_type' => Document::TYPE_WI,
                    'd.deleted_at' => null,
                ])
                ->andWhere(new Expression(
                    "JSON_CONTAINS(COALESCE(NULLIF(d.related_links, ''), '[]'), JSON_OBJECT('document_id', :sopId), '$')",
                    [':sopId' => (int) $model->id]
                ));
            $access->applyVisibleScope($linkedWorkInstructionQuery);
            $linkedWorkInstructions = $linkedWorkInstructionQuery
                ->orderBy(['d.document_no' => SORT_ASC])
                ->all();
        }

        return $this->render('view', [
            'model' => $model,
            'access' => $access,
            'linkedWorkInstructions' => $linkedWorkInstructions,
            'assignment' => $assignment,
            'assignmentEmployee' => $employee,
        ]);
    }

    public function actionAcknowledge($id)
    {
        $model = $this->findModel($id);
        $access = $this->access();
        $employee = $access->currentEmployee();
        if ($employee === null || !$access->canView($model)) {
            throw new ForbiddenHttpException('ไม่มีสิทธิ์รับทราบเอกสารรายการนี้');
        }

        $assignment = DocumentAssignment::findOne([
            'document_id' => (int) $model->id,
            'revision_no' => (int) $model->current_revision,
            'employee_id' => (int) $employee->id,
        ]);
        if ($assignment === null) {
            throw new NotFoundHttpException('ไม่พบรายการมอบหมายเอกสารสำหรับผู้ใช้งาน');
        }

        try {
            (new DocumentReadTrackingService())->acknowledge($assignment, (int) $employee->id);
            Yii::$app->session->setFlash('success', 'บันทึกการรับทราบเอกสารเรียบร้อยแล้ว');
        } catch (\Throwable $e) {
            Yii::error($e, __METHOD__);
            Yii::$app->session->setFlash('error', $e->getMessage());
        }
        return $this->redirect(['view', 'id' => $model->id, '#' => 'document-acknowledgement']);
    }

    public function actionSlides($id)
    {
        $model = $this->findModel($id);
        $access = $this->access();
        if (!$access->canView($model)) {
            throw new ForbiddenHttpException('ไม่มีสิทธิ์ดูเอกสารในรูปแบบสไลด์');
        }
        return $this->render('slides', compact('model', 'access'));
    }

    public function actionManualPdf($id)
    {
        $model = $this->findModel($id);
        if (!$this->access()->canView($model)) {
            throw new ForbiddenHttpException('ไม่มีสิทธิ์ส่งออกคู่มือเอกสารนี้');
        }

        $tempDir = Yii::getAlias('@runtime/mpdf');
        FileHelper::createDirectory($tempDir);
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8', 'format' => 'A4', 'orientation' => 'P',
            'tempDir' => $tempDir, 'default_font' => 'garuda',
            'margin_left' => 18, 'margin_right' => 18,
            'margin_top' => 16, 'margin_bottom' => 18,
        ]);
        $mpdf->SetTitle('คู่มือ ' . $model->document_no . ' ' . $model->title);
        $mpdf->SetAuthor('โรงพยาบาล');
        $mpdf->SetHTMLFooter('<div style="text-align:right;color:#64748b;font-size:9pt">' . Html::encode($model->document_no) . ' · หน้า {PAGENO} / {nbpg}</div>');
        $mpdf->WriteHTML($this->renderPartial('manual-pdf', ['model' => $model]));

        $filename = preg_replace('/[^A-Za-z0-9_-]+/', '-', $model->document_no) . '-manual.pdf';
        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->set('Content-Type', 'application/pdf');
        Yii::$app->response->headers->set('Content-Disposition', 'inline; filename="' . $filename . '"');
        return $mpdf->Output($filename, \Mpdf\Output\Destination::STRING_RETURN);
    }

    public function actionCreate()
    {
        $access = $this->access();
        if (!$access->canCreate()) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์สร้างเอกสาร');
        }
        $employee = $access->currentEmployee();
        $announcementStatuses = MedSopSetting::listValue(MedSopSetting::ANNOUNCEMENT_STATUSES, ['ACTIVE' => 'ประกาศใช้']);
        $model = new Document([
            'document_type' => Document::TYPE_SOP,
            'status' => Document::STATUS_DRAFT,
            'announcement_status' => array_key_first($announcementStatuses),
            'current_revision' => 1,
            'created_emp_id' => $employee ? $employee->id : null,
            'created_by' => Yii::$app->user->id,
            'updated_by' => Yii::$app->user->id,
        ]);
        return $this->saveForm($model);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if (!$this->access()->canUpdate($model)) {
            throw new ForbiddenHttpException('เอกสารสถานะนี้ไม่สามารถแก้ไขได้');
        }
        $model->updated_by = Yii::$app->user->id;
        return $this->saveForm($model);
    }

    public function actionAudience($id)
    {
        $model = $this->findModel($id);
        $access = $this->access();
        if (!$access->canUpdate($model)) {
            throw new ForbiddenHttpException('กำหนดผู้รับเอกสารได้เฉพาะฉบับร่างหรือเอกสารที่ส่งกลับแก้ไข');
        }

        $service = new AudienceConfigurationService();
        if (Yii::$app->request->isPost) {
            try {
                $service->save($model, (array) Yii::$app->request->post('audiences', []));
                if ((string) Yii::$app->request->post('audience_intent', 'draft') === 'publish') {
                    $recipientCount = $this->publishDocument($model);
                    Yii::$app->session->setFlash('success', 'บันทึกผู้รับและเผยแพร่เอกสารแล้ว ส่งให้ผู้รับ ' . number_format($recipientCount) . ' คน');
                    return $this->redirect(['view', 'id' => $model->id]);
                }
                Yii::$app->session->setFlash('success', 'บันทึกผู้มีสิทธิ์อ่านและรับทราบแล้ว');
                return $this->redirect(['audience', 'id' => $model->id]);
            } catch (\Throwable $e) {
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }

        $audiences = DocumentAudience::find()
            ->where(['document_id' => (int) $model->id])
            ->orderBy(['id' => SORT_ASC])
            ->all();
        $selectedOrganizations = [];
        $selectedTeams = [];
        $selectedEmployeeIds = [];
        foreach ($audiences as $audience) {
            if ($audience->audience_type === DocumentAudience::TYPE_ORGANIZATION) {
                $selectedOrganizations[(int) $audience->audience_id] = $audience;
            } elseif ($audience->audience_type === DocumentAudience::TYPE_TEAM_GROUP) {
                $selectedTeams[(int) $audience->audience_version_id] = $audience;
            } elseif ($audience->audience_type === DocumentAudience::TYPE_EMPLOYEE) {
                $selectedEmployeeIds[] = (int) $audience->audience_id;
            }
        }

        $organizations = Organization::find()
            ->where(['active' => 1])
            ->orderBy(['root' => SORT_ASC, 'lft' => SORT_ASC])
            ->all();
        $teamGroups = TeamGroup::find()
            ->where(['or', ['status' => 1], ['status' => null]])
            ->orderBy(['title' => SORT_ASC])
            ->all();
        $teamGroupIds = array_map(static function (TeamGroup $team): int {
            return (int) $team->id;
        }, $teamGroups);
        $appointments = $teamGroupIds ? TeamGroupDetail::find()
            ->where(['name' => 'appointment', 'category_id' => $teamGroupIds])
            ->orderBy(['thai_year' => SORT_DESC, 'id' => SORT_DESC])
            ->all() : [];
        $appointmentIds = array_map(static function (TeamGroupDetail $appointment): int {
            return (int) $appointment->id;
        }, $appointments);
        $teamMemberCounts = $appointmentIds ? TeamGroupDetail::find()
            ->select(['category_id', 'total' => 'COUNT(*)'])
            ->where(['name' => 'committee', 'category_id' => $appointmentIds])
            ->groupBy('category_id')
            ->indexBy('category_id')
            ->asArray()
            ->all() : [];
        $employees = Employees::find()
            ->with('empDepartment')
            ->where(['status' => 1])
            ->orderBy(['department' => SORT_ASC, 'fname' => SORT_ASC, 'lname' => SORT_ASC])
            ->all();

        return $this->render('audience', compact(
            'model',
            'access',
            'organizations',
            'teamGroups',
            'appointments',
            'employees',
            'selectedOrganizations',
            'selectedTeams',
            'selectedEmployeeIds',
            'teamMemberCounts'
        ));
    }

    public function actionAudiencePublish($id)
    {
        $model = $this->findModel($id);
        $access = $this->access();
        if (!$access->canUpdate($model)) {
            throw new ForbiddenHttpException('เอกสารสถานะนี้ไม่สามารถกำหนดผู้รับและเผยแพร่ได้');
        }

        try {
            (new AudienceConfigurationService())->save(
                $model,
                (array) Yii::$app->request->post('audiences', [])
            );
            $recipientCount = $this->publishDocument($model);
            Yii::$app->session->setFlash('success', 'บันทึกผู้รับและเผยแพร่เอกสารแล้ว ส่งให้ผู้รับ ' . number_format($recipientCount) . ' คน');
            return $this->redirect(['view', 'id' => $model->id]);
        } catch (\Throwable $e) {
            Yii::error($e, __METHOD__);
            Yii::$app->session->setFlash('error', $e->getMessage());
            return $this->redirect(['audience', 'id' => $model->id]);
        }
    }

    public function actionAudiencePreview($id)
    {
        $model = $this->findModel($id);
        if (!Yii::$app->request->isPost || !$this->access()->canUpdate($model)) {
            throw new ForbiddenHttpException('ไม่มีสิทธิ์ตรวจสอบรายชื่อผู้รับเอกสาร');
        }
        Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            $preview = (new AudienceConfigurationService())->preview(
                $model,
                (array) Yii::$app->request->post('audiences', [])
            );
            $employeeIds = array_keys($preview['recipients']);
            $employees = $employeeIds ? Employees::find()
                ->with('empDepartment')
                ->where(['id' => $employeeIds])
                ->orderBy(['fname' => SORT_ASC, 'lname' => SORT_ASC])
                ->all() : [];
            $items = [];
            foreach (array_slice($employees, 0, 12) as $employee) {
                $items[] = [
                    'id' => (int) $employee->id,
                    'name' => $employee->fullname(),
                    'department' => $employee->empDepartment ? $employee->empDepartment->name : 'ไม่ระบุหน่วยงาน',
                ];
            }
            return [
                'success' => true,
                'rules' => (int) $preview['rules'],
                'total' => count($preview['recipients']),
                'items' => $items,
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function actionPublish($id)
    {
        $model = $this->findModel($id);
        $access = $this->access();
        if (!$access->isAdmin() || !$model->isEditable()) {
            throw new ForbiddenHttpException('เอกสารสถานะนี้ไม่สามารถเผยแพร่ได้');
        }
        if (!DocumentAudience::find()->where(['document_id' => (int) $model->id])->exists()) {
            Yii::$app->session->setFlash('error', 'กรุณากำหนดและบันทึกผู้รับเอกสารก่อนเผยแพร่');
            return $this->redirect(['audience', 'id' => $model->id]);
        }

        try {
            $recipientCount = $this->publishDocument($model);
            Yii::$app->session->setFlash('success', 'เผยแพร่เอกสารแล้ว และส่งให้ผู้รับ ' . number_format($recipientCount) . ' คน');
            return $this->redirect(['view', 'id' => $model->id]);
        } catch (\Throwable $e) {
            Yii::error($e, __METHOD__);
            Yii::$app->session->setFlash('error', $e->getMessage());
            return $this->redirect(['audience', 'id' => $model->id]);
        }
    }

    private function publishDocument(Document $model): int
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $model->status = Document::STATUS_PUBLISHED;
            $model->published_by = (int) Yii::$app->user->id;
            $model->published_at = date('Y-m-d H:i:s');
            $model->updated_by = (int) Yii::$app->user->id;
            if (!$model->save(false, ['status', 'published_by', 'published_at', 'updated_by', 'updated_at'])) {
                throw new \RuntimeException('ไม่สามารถเปลี่ยนสถานะเอกสารเป็นเผยแพร่แล้วได้');
            }

            $recipientCount = (new DocumentAssignmentService())->assignPublishedRevision($model);
            if ($recipientCount < 1) {
                throw new \RuntimeException('ไม่พบรายชื่อบุคลากรจากผู้รับที่กำหนด กรุณาตรวจสอบหน่วยงาน ทีม หรือรายชื่อบุคลากร');
            }

            $transaction->commit();
            return $recipientCount;
        } catch (\Throwable $e) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }
            throw $e;
        }
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if (!Yii::$app->request->isPost || !$this->access()->isAdmin() || $model->status !== Document::STATUS_DRAFT) {
            throw new ForbiddenHttpException('ไม่สามารถลบเอกสารนี้ได้');
        }
        $model->deleted_at = date('Y-m-d H:i:s');
        $model->deleted_by = Yii::$app->user->id;
        $model->save(false, ['deleted_at', 'deleted_by', 'updated_at']);
        Yii::$app->session->setFlash('success', 'ลบฉบับร่างแล้ว');
        return $this->redirect(['index']);
    }

    private function saveForm(Document $model)
    {
        $stepRows = Yii::$app->request->post('steps', []);
        $mediaFiles = [];
        foreach ((array) $stepRows as $index => $row) {
            $mediaFiles[$index] = UploadedFile::getInstancesByName('steps[' . $index . '][media]');
        }
        $loaded = $model->load(Yii::$app->request->post());
        $relatedLinksValid = true;
        if ($loaded) {
            $reviewDateInput = (string) $model->review_date;
            $model->review_date = $reviewDateInput === '' ? null : AppHelper::convertToGregorian($reviewDateInput);
            try {
                $links = $this->normalizeRelatedDocumentRows(
                    (array) Yii::$app->request->post('related_links', []),
                    (int) $model->id
                );
                $model->related_links = $links ? json_encode($links, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null;
            } catch (\RuntimeException $e) {
                $relatedLinksValid = false;
                $model->addError('related_links', $e->getMessage());
            }
        }
        if ($loaded && $model->isNewRecord) {
            $model->document_no = $this->generateDocumentNo((string) $model->document_type, (int) $model->organization_id);
        }
        $coverFile = UploadedFile::getInstanceByName('cover_image');
        if ($loaded && $relatedLinksValid && (new DocumentService())->save($model, is_array($stepRows) ? $stepRows : [], $mediaFiles, $coverFile)) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'success' => true,
                    'message' => 'บันทึกเอกสารเรียบร้อยแล้ว',
                    'redirect' => \yii\helpers\Url::to(['view', 'id' => $model->id]),
                ];
            }
            Yii::$app->session->setFlash('success', 'บันทึกเอกสารเรียบร้อยแล้ว');
            return $this->redirect(['view', 'id' => $model->id]);
        }
        if (Yii::$app->request->isAjax && Yii::$app->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $validation = [];
            foreach ($model->getErrors() as $attribute => $errors) {
                $validation[Html::getInputId($model, $attribute)] = $errors;
            }
            return [
                'success' => false,
                'message' => $model->getFirstError('related_links') ?: 'ยังบันทึกไม่ได้ กรุณาตรวจสอบข้อมูลที่ระบุ',
                'validation' => $validation,
            ];
        }
        if (!Yii::$app->request->isPost && !$model->isNewRecord) {
            $stepRows = array_map(static function ($step) {
                $row = $step->toArray(['id', 'title', 'description', 'caution']);
                $row['media'] = $step->media;
                $row['related_links'] = $step->getRelatedLinkItems();
                return $row;
            }, $model->steps);
        }
        $formOrganizations = Organization::find()->where(['active' => 1])->orderBy(['root' => SORT_ASC, 'lft' => SORT_ASC])->all();
        $documentTypeCodes = array_keys(MedSopSetting::documentTypes());
        $filterCategories = static function (array $values) use ($documentTypeCodes, $model): array {
            $filtered = array_values(array_diff($values, $documentTypeCodes));
            if ($model->category && in_array($model->category, $values, true) && !in_array($model->category, $filtered, true)) {
                $filtered[] = $model->category;
            }
            return $filtered !== [] ? $filtered : array_values($values);
        };
        $defaultCategories = $filterCategories(MedSopSetting::listValue(MedSopSetting::DOCUMENT_CATEGORIES, ['SOP', 'WI']));
        $organizationCategoryMap = [];
        foreach (OrganizationSetting::find()->select(['organization_id', 'document_categories'])->all() as $organizationSetting) {
            $values = json_decode((string) $organizationSetting->document_categories, true);
            if (is_array($values) && $values !== []) $organizationCategoryMap[(int) $organizationSetting->organization_id] = $filterCategories(array_values($values));
        }
        $categories = $organizationCategoryMap[(int) $model->organization_id] ?? $defaultCategories;
        return $this->render('form', [
            'model' => $model,
            'stepRows' => $stepRows,
            'organizations' => $formOrganizations,
            'categories' => $categories,
            'defaultCategories' => $defaultCategories,
            'organizationCategoryMap' => $organizationCategoryMap,
            'announcementStatuses' => MedSopSetting::listValue(MedSopSetting::ANNOUNCEMENT_STATUSES, ['ACTIVE' => 'ประกาศใช้']),
            'relatedLinks' => $model->getRelatedLinkItems() ?: [['label' => '', 'url' => '']],
            'relatedDocuments' => $this->relatedDocuments($model),
        ]);
    }

    private function relatedDocuments(Document $current): array
    {
        $query = Document::find()->alias('d')->andWhere(['d.deleted_at' => null]);
        if (!$current->isNewRecord) {
            $query->andWhere(['<>', 'd.id', (int) $current->id]);
        }
        $this->access()->applyVisibleScope($query);
        return $query->orderBy(['d.document_type' => SORT_ASC, 'd.document_no' => SORT_ASC])->all();
    }

    private function normalizeRelatedDocumentRows(array $rows, int $currentDocumentId): array
    {
        $ids = [];
        foreach ($rows as $row) {
            $id = (int) ($row['document_id'] ?? 0);
            if ($id > 0 && $id !== $currentDocumentId) {
                $ids[$id] = $id;
            }
        }
        if ($ids === []) {
            return [];
        }
        $documents = Document::find()->where(['id' => array_values($ids), 'deleted_at' => null])->indexBy('id')->all();
        $links = [];
        foreach ($ids as $id) {
            if (!isset($documents[$id])) {
                throw new \RuntimeException('ไม่พบ SOP หรือ WI ที่เลือกเป็นเอกสารที่เกี่ยวข้อง');
            }
            $document = $documents[$id];
            $links[] = [
                'document_id' => $id,
                'label' => $document->document_no . ' · ' . $document->title,
                'url' => \yii\helpers\Url::to(['/medsop/document/view', 'id' => $id]),
            ];
        }
        return $links;
    }

    private function access(): DocumentAccessService
    {
        return $this->accessService ?: ($this->accessService = new DocumentAccessService());
    }

    private function findModel($id): Document
    {
        $model = Document::find()->with(['steps.media'])->where(['id' => $id, 'deleted_at' => null])->one();
        if ($model === null) {
            throw new NotFoundHttpException('ไม่พบเอกสาร');
        }
        return $model;
    }

    private function generateDocumentNo(string $type, int $organizationId): string
    {
        if ($type === Document::TYPE_WI) {
            $prefix = MedSopSetting::value(MedSopSetting::WI_PREFIX, 'WI');
        } elseif ($type === Document::TYPE_SOP) {
            $prefix = MedSopSetting::value(MedSopSetting::SOP_PREFIX, 'SP');
        } else {
            $prefix = $type;
        }
        $organizationSetting = OrganizationSetting::findOne($organizationId);
        $organizationCode = $organizationSetting && $organizationSetting->code ? $organizationSetting->code : 'ORG' . $organizationId;
        $year = (string) ((int) date('Y') + 543);
        $sequence = (int) Document::find()->where(['>=', 'created_at', date('Y-01-01 00:00:00')])->count() + 1;
        $pattern = MedSopSetting::value(MedSopSetting::CODE_PATTERN, '{type}-{org}-{year}-{sequence}');
        return strtr($pattern, [
            '{type}' => $prefix,
            '{org}' => $organizationCode,
            '{year}' => $year,
            '{sequence}' => str_pad((string) $sequence, 4, '0', STR_PAD_LEFT),
        ]);
    }
}
