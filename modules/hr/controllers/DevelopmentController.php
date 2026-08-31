<?php

namespace app\modules\hr\controllers;

use Yii;
use DateTime;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\Response;
use yii\db\Expression;
use setasign\Fpdi\Fpdi;
use yii\web\Controller;
use app\models\Categorise;
use yii\filters\VerbFilter;
use yii\helpers\FileHelper;
use app\components\AppHelper;
use app\components\SiteHelper;
use app\components\UserHelper;
use app\components\ThaiDateHelper;
use yii\web\NotFoundHttpException;
use app\components\DateFilterHelper;
use yii\helpers\ArrayHelper;
use app\modules\hr\models\Development;
use app\modules\hr\models\DevelopmentDetail;
use app\modules\hr\models\DevelopmentSummary;
use app\modules\hr\models\DevelopmentDocument;
use app\modules\approve\models\Approve;
use app\modules\hr\models\Organization;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use app\modules\hr\models\DevelopmentSearch;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use app\modules\filemanager\components\FileManagerHelper;
use app\modules\hr\helpers\PdfCoordinateHelper;
use app\modules\hr\components\DevelopmentDocumentCatalog;
use app\modules\hr\components\DevelopmentDocumentBuilder;
use app\modules\purchase\components\DocRenderer;
use app\modules\purchase\models\DocTemplate;
use app\modules\pdfTemplate\models\PdfTemplate;
use app\modules\pdfTemplate\services\PdfTemplateService;

/**
 * DevelopmentController implements the CRUD actions for Development model.
 */
class DevelopmentController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                        'document-save' => ['POST'],
                        'document-reset' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Development models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $me = UserHelper::GetEmployee();
        $leaveFilterStatusModel = Categorise::findOne(['name' => 'hr_development_filter_status', 'emp_id' => $me->id]);
        $searchModel = new DevelopmentSearch([
            'q_status' => $leaveFilterStatusModel->data_json ?? [],
        ]);

        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->joinWith([
            'developmentDetail',
            'createdByEmp' => function ($q) {
                $q->alias('created_by_emp');
            }
        ]);
        $dataProvider->query->andFilterWhere(['development.status' => $searchModel->q_status]);
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'topic', $searchModel->q],
            ['like', 'development.emp_id', $searchModel->emp_id],
            ['like', new \yii\db\Expression("JSON_UNQUOTE(JSON_EXTRACT(development.data_json, '$.location'))"), $searchModel->q],
        ]);

        $dataProvider->query->andFilterWhere(['development_detail.emp_id' => $searchModel->emp_id]);


        $dataProvider->query->andFilterWhere(['>=', 'date_start', AppHelper::convertToGregorian($searchModel->date_start)])->andFilterWhere(['<=', 'date_end', AppHelper::convertToGregorian($searchModel->date_end)]);

        $org1 = Organization::findOne($searchModel->q_department);
        // ถ้ามีกลุ่มย่อย
        if (isset($org1) && $org1->lvl == 1) {
            $sql = 'SELECT t1.id, t1.root, t1.lft, t1.rgt, t1.lvl, t1.name, t1.icon
             FROM tree t1
             JOIN tree t2 ON t1.lft BETWEEN t2.lft AND t2.rgt AND t1.lvl = t2.lvl + 1
             WHERE t2.name = :name;';
            $querys = Yii::$app
                ->db
                ->createCommand($sql)
                ->bindValue(':name', $org1->name)
                ->queryAll();
            $arrDepartment = [];
            foreach ($querys as $tree) {
                $arrDepartment[] = $tree['id'];
            }
            if (count($arrDepartment) > 0) {
                $dataProvider->query->andWhere(['in', 'created_by_emp.department', $arrDepartment]);
            } else {
                $dataProvider->query->andFilterWhere(['created_by_emp.department' => $searchModel->q_department]);
            }
        } else {
            $dataProvider->query->andFilterWhere(['created_by_emp.department' => $searchModel->q_department]);
        }


        $dataProvider->query->orderBy(['date_start' => SORT_DESC, 'id' => SORT_DESC]);
        $dataProvider->query->groupBy('development_detail.id');
        // ทะเบียนแสดงคอลัมน์สถานะสรุปผลต่อแถว — โหลดล่วงหน้ากันยิง query รายแถว
        $dataProvider->query->with('summaryReport');

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionDashboard()
    {
        $lastDay = (new DateTime(date('Y-m-d')))->modify('last day of this month')->format('Y-m-d');
        $status = $this->request->get('status');
        $searchModel = new DevelopmentSearch([
            'thai_year' => AppHelper::YearBudget(),
            'date_start' => AppHelper::convertToThai(date('Y-m') . '-01'),
            'date_end' => AppHelper::convertToThai($lastDay),
            'status' =>   $status ? [$status] : ['Pending']
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        return $this->render('dashboard', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * ศูนย์รวมการสร้างและพิมพ์เอกสารค่าใช้จ่ายในการเดินทางไปราชการ
     *
     * หน้านี้เป็นจุดเชื่อมสำหรับระบบแม่แบบเอกสารที่จะพัฒนาต่อ โดยยังไม่ผูก
     * business logic กับโมดูลพัสดุหรือสร้างข้อมูลเอกสารจนกว่าจะได้แม่แบบจริง
     */
    public function actionDocument()
    {
        $developments = Development::find()
            ->where(['deleted_at' => null])
            ->with(['createdByEmp', 'document'])
            ->orderBy(['date_start' => SORT_DESC, 'id' => SORT_DESC])
            ->limit(200)
            ->all();

        $developmentOptions = [];
        foreach ($developments as $development) {
            $employee = $development->createdByEmp;
            $name = $employee && method_exists($employee, 'fullname') ? $employee->fullname() : '';
            $developmentOptions[(int) $development->id] = trim(
                '#' . $development->id . ' · ' . $development->topic
                . ($name !== '' ? ' · ' . $name : '')
                . ($development->date_start ? ' · ' . $development->showDateRange() : '')
            );
        }

        return $this->render('document', [
            'documentTypes' => DevelopmentDocumentCatalog::all(),
            'developmentOptions' => $developmentOptions,
            'defaultDevelopmentId' => $developmentOptions !== [] ? array_key_first($developmentOptions) : null,
        ]);
    }

    /** สร้างหรือเปิด snapshot เอกสารของทะเบียนที่เลือก */
    public function actionDocumentOpen($development_id, $code)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $type = DevelopmentDocumentCatalog::find((string) $code);
        if ($type === null || $type['status'] !== DevelopmentDocumentCatalog::STATUS_SOURCE_READY) {
            return ['status' => 'error', 'message' => 'เอกสารประเภทนี้ยังไม่มีแม่แบบพร้อมใช้งาน'];
        }

        $development = Development::find()
            ->where(['id' => (int) $development_id, 'deleted_at' => null])
            ->with(['createdByEmp', 'document'])
            ->one();
        if ($development === null) {
            return ['status' => 'error', 'message' => 'ไม่พบทะเบียนการเดินทางที่เลือก'];
        }

        $document = DevelopmentDocument::findOne([
            'development_id' => $development->id,
            'template_code' => (string) $code,
            'deleted_at' => null,
        ]);

        if ($document === null) {
            $document = new DevelopmentDocument([
                'development_id' => (int) $development->id,
                'template_code' => (string) $code,
                'title' => (string) $type['name'] . ' · ' . $development->topic,
                'ref_type' => 'none',
                'ref_id' => null,
                'thai_year' => (int) $development->thai_year,
                'doc_date' => date('Y-m-d'),
                'body_html' => DevelopmentDocumentBuilder::build((string) $code, $development),
                'orientation' => $code === 'travel_expense_8708_part_2' ? 'landscape' : 'portrait',
                'emblem' => $code === 'travel_expense_8708_part_1' ? DocTemplate::EMBLEM_NONE : DocTemplate::EMBLEM_SMALL,
                'font_size' => 14,
                'margin_json' => ['top' => 15, 'right' => 15, 'bottom' => 15, 'left' => 20],
                'status' => DevelopmentDocument::STATUS_DRAFT,
                'emp_id' => is_numeric($development->emp_id) ? (int) $development->emp_id : null,
                'data_json' => ['source_updated_at' => $development->updated_at],
            ]);
            if (!$document->save()) {
                return [
                    'status' => 'error',
                    'message' => 'สร้างเอกสารไม่สำเร็จ: ' . implode(' ', array_merge(...array_values($document->getErrors()))),
                ];
            }
        } elseif ($this->upgradeTravelExpensePartOne($document, $development)) {
            $document->save(false);
        }

        return $this->developmentDocumentEditorResponse($document);
    }

    /** บันทึกเนื้อหาจาก editor แบบ autosave */
    public function actionDocumentSave($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $document = $this->findDevelopmentDocument($id);
        if ($document->status === DevelopmentDocument::STATUS_FINAL) {
            return ['status' => 'error', 'message' => 'เอกสารฉบับนี้ถูกล็อกแล้ว'];
        }

        $post = $this->request->post();
        $document->body_html = array_key_exists('body_html', $post)
            ? DocRenderer::normalize((string) $post['body_html'])
            : $document->body_html;
        if (array_key_exists('font_size', $post)) {
            $document->font_size = (int) $post['font_size'];
        }
        if (array_key_exists('emblem', $post) && array_key_exists((string) $post['emblem'], DocTemplate::emblemList())) {
            $document->emblem = (string) $post['emblem'];
        }

        if (!$document->save()) {
            return ['status' => 'error', 'message' => 'บันทึกไม่สำเร็จ'];
        }

        return ['status' => 'success', 'message' => 'บันทึกร่างแล้ว'];
    }

    /** ดึงข้อมูลทะเบียนล่าสุดกลับมาทับ snapshot หลังผู้ใช้ยืนยัน */
    public function actionDocumentReset($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $document = $this->findDevelopmentDocument($id);
        $development = Development::find()->where(['id' => $document->development_id])->with(['createdByEmp', 'document'])->one();
        if ($development === null) {
            return ['status' => 'error', 'message' => 'ไม่พบทะเบียนต้นทาง'];
        }

        $document->body_html = DevelopmentDocumentBuilder::build($document->template_code, $development);
        $document->data_json = ['source_updated_at' => $development->updated_at];
        if (!$document->save()) {
            return ['status' => 'error', 'message' => 'รีเซ็ตเอกสารไม่สำเร็จ'];
        }

        return [
            'status' => 'success',
            'message' => 'ดึงข้อมูลจากทะเบียนกลับมาแล้ว',
            'body_html' => DocRenderer::body($document),
        ];
    }

    /** ตัวอย่าง PDF จาก snapshot ล่าสุด */
    public function actionDocumentPrint($id)
    {
        $document = $this->findDevelopmentDocument($id);
        $document->markPrinted();

        return Yii::$app->response->sendContentAsFile(
            DocRenderer::pdf($document),
            $document->safeFileName('pdf'),
            ['mimeType' => 'application/pdf', 'inline' => true]
        );
    }

    private function developmentDocumentEditorResponse(DevelopmentDocument $document): array
    {
        $routes = [
            'save' => ['/hr/development/document-save', 'id' => $document->id],
            'reset' => ['/hr/development/document-reset', 'id' => $document->id],
            'print' => ['/hr/development/document-print', 'id' => $document->id],
        ];

        return [
            'status' => 'success',
            'title' => '<i class="bi bi-file-earmark-text me-1"></i>' . Html::encode($document->title)
                . ' <span class="badge bg-warning-subtle text-warning-emphasis ms-1">แก้ไขได้</span>',
            'content' => $this->renderAjax('@app/modules/purchase/views/doc/editor', [
                'model' => $document,
                'routes' => $routes,
            ]),
            'footer' => $this->renderAjax('@app/modules/purchase/views/doc/_editor_footer', [
                'model' => $document,
                'routes' => $routes,
                'showWord' => false,
            ]),
            'initCallback' => 'erpDocEditorInit',
        ];
    }

    private function findDevelopmentDocument($id): DevelopmentDocument
    {
        $document = DevelopmentDocument::findOne(['id' => (int) $id, 'deleted_at' => null]);
        if ($document === null) {
            throw new NotFoundHttpException('ไม่พบเอกสารที่ต้องการ');
        }

        return $document;
    }

    /** เปลี่ยน snapshot รุ่นทดลองให้เป็นแบบ 8708 ส่วนที่ 1 ฉบับสองหน้าตามต้นฉบับ */
    private function upgradeTravelExpensePartOne(DevelopmentDocument $document, Development $development): bool
    {
        if ($document->template_code !== 'travel_expense_8708_part_1') {
            return false;
        }

        if (
            strpos((string) $document->body_html, 'd-8708-part1-v8') !== false
            && strpos((string) $document->body_html, 'd-doc-page') !== false
        ) {
            return false;
        }

        $document->body_html = DevelopmentDocumentBuilder::build('travel_expense_8708_part_1', $development);
        $document->emblem = DocTemplate::EMBLEM_NONE;
        return true;
    }

    /**
     * ศูนย์รวมรายงานการเดินทางไปราชการ
     */
    public function actionReport()
    {
        return $this->render('report');
    }

    /**
     * Displays a single Development model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('view', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('view', [
                'model' => $model,
            ]);
        }
    }

    /**
     * ฟอร์มสรุปผลประชุม/อบรม ของใบไปราชการหนึ่งใบ (เปิดเป็น modal จากทะเบียน)
     *
     * เจ้าของใบและคณะเดินทางแก้ได้ตลอด แม้ผู้รับทราบจะกดรับทราบไปแล้ว
     * คนอื่นเปิดดูได้อย่างเดียว ผู้ที่ถูกกำหนดให้รับทราบจะเห็นปุ่ม «รับทราบ»
     *
     * @param int $id Development ID
     */
    public function actionSummary($id)
    {
        $model = $this->findModel($id);
        $me = UserHelper::GetEmployee();
        $canEdit = $model->canEditSummary($me->id ?? null);

        $summary = $model->summaryReport;
        if (!$summary && $canEdit) {
            // สร้างฉบับร่างตอนเปิดฟอร์มครั้งแรก เพราะ widget อัปโหลดไฟล์ต้องมี ref ตั้งแต่ตอน render
            $summary = new DevelopmentSummary([
                'development_id' => $model->id,
                'status' => DevelopmentSummary::STATUS_DRAFT,
                'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
            ]);
            $summary->save(false);
            $model->populateRelation('summaryReport', $summary);
        }

        if ($this->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if (!$canEdit || !$summary) {
                return ['status' => 'error', 'message' => 'เฉพาะผู้ขอและคณะเดินทางเท่านั้นที่บันทึกสรุปผลได้'];
            }

            $isSubmit = $this->request->post('do') === 'submit';
            $summary->scenario = $isSubmit ? DevelopmentSummary::SCENARIO_SUBMIT : DevelopmentSummary::SCENARIO_DEFAULT;
            $summary->load($this->request->post());

            if (!$summary->validate()) {
                return [
                    'status' => 'error',
                    'message' => implode(' ', $summary->getFirstErrors()),
                ];
            }

            $acknowledgerIds = array_values(array_filter((array) $this->request->post('acknowledgers', [])));
            if ($isSubmit && empty($acknowledgerIds)) {
                return ['status' => 'error', 'message' => 'กรุณาเลือกผู้รับทราบอย่างน้อย 1 คน'];
            }

            if ($isSubmit && $summary->status === DevelopmentSummary::STATUS_DRAFT) {
                $summary->status = DevelopmentSummary::STATUS_SUBMITTED;
                $summary->submitted_at = date('Y-m-d H:i:s');
                $summary->submitted_by = Yii::$app->user->id;
            }
            $summary->save(false);

            $this->syncSummaryAcknowledgers($summary, $acknowledgerIds);
            $summary->refreshStatus();

            $message = $isSubmit ? 'ส่งสรุปผลให้ผู้รับทราบเรียบร้อยแล้ว' : 'บันทึกสรุปผลเรียบร้อยแล้ว';
            if ($isSubmit) {
                $notified = $summary->notifyAcknowledgers();
                $message .= ' แจ้งเตือนผู้รับทราบ ' . $notified . ' คน';
            }

            return [
                'status' => 'success',
                'message' => $message,
            ];
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title', '<i class="bi bi-journal-check"></i> สรุปผลประชุม/อบรม'),
                'content' => $this->renderAjax('_form_summary', [
                    'model' => $model,
                    'summary' => $summary,
                    'canEdit' => $canEdit,
                    'me' => $me,
                ]),
            ];
        }

        return $this->render('_form_summary', [
            'model' => $model,
            'summary' => $summary,
            'canEdit' => $canEdit,
            'me' => $me,
        ]);
    }

    /**
     * ผู้รับทราบกดรับทราบสรุปผล
     *
     * @param int $id Development ID
     */
    public function actionSummaryAcknowledge($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);
        $summary = $model->summaryReport;
        $me = UserHelper::GetEmployee();

        if (!$summary || $summary->status === DevelopmentSummary::STATUS_DRAFT) {
            return ['status' => 'error', 'message' => 'ยังไม่มีสรุปผลที่ส่งให้รับทราบ'];
        }

        $row = Approve::find()
            ->where([
                'name' => DevelopmentSummary::APPROVE_NAME,
                'from_id' => $model->id,
                'emp_id' => $me->id ?? 0,
            ])
            ->one();
        if (!$row) {
            return ['status' => 'error', 'message' => 'คุณไม่ได้อยู่ในรายชื่อผู้รับทราบของสรุปผลนี้'];
        }

        $comment = trim((string) $this->request->post('comment', ''));
        $row->status = 'Pass';
        $row->comment = $comment;
        $row->data_json = ArrayHelper::merge((array) $row->data_json, ['acknowledged_at' => date('Y-m-d H:i:s')]);
        $row->save(false);

        $summary->refreshStatus();
        $summary->notifySubmitterAcknowledged($me, $comment);

        return ['status' => 'success', 'message' => 'บันทึกการรับทราบเรียบร้อยแล้ว'];
    }

    /**
     * ปรับรายชื่อผู้รับทราบให้ตรงกับที่เลือกไว้ โดยไม่ลบสถานะของคนที่กดรับทราบไปแล้ว
     *
     * @param string[] $empIds emp_id ที่เลือกในฟอร์ม
     */
    protected function syncSummaryAcknowledgers(DevelopmentSummary $summary, array $empIds): void
    {
        $existing = [];
        foreach ($summary->getAcknowledgers() as $row) {
            $existing[(string) $row->emp_id] = $row;
        }

        foreach (array_values($empIds) as $index => $empId) {
            $empId = (string) $empId;
            if (isset($existing[$empId])) {
                $row = $existing[$empId];
                $row->level = $index + 1;
                $row->save(false);
                unset($existing[$empId]);
                continue;
            }
            $row = new Approve([
                'name' => DevelopmentSummary::APPROVE_NAME,
                'from_id' => $summary->development_id,
                'emp_id' => (int) $empId,
                'level' => $index + 1,
                'status' => 'Pending',
            ]);
            $row->save(false);
        }

        // คนที่ถูกเอาออกจากรายชื่อ ลบทิ้ง (รวมถึงคนที่กดรับทราบแล้ว เพราะผู้บันทึกตั้งใจเอาออก)
        foreach ($existing as $row) {
            $row->delete();
        }
    }

    /**
     * Creates a new Development model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Development();

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $model->status = 'Pending';
                if ($model->save()) {
                    $this->syncTravelPartyMembers($model, $this->request->post('member_emp_ids', []));
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Development model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = Development::find()->where(['id' => $id])->one();
        if ($model === null) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        $dateStart2 = $model->date_start;

        $model->date_start = $model->date_start ? AppHelper::convertToThai($model->date_start) : null;
        $model->date_end = $model->date_end ? AppHelper::convertToThai($model->date_end) : null;
        $model->vehicle_date_start = $model->vehicle_date_start ? AppHelper::convertToThai($model->vehicle_date_start) : null;
        $model->vehicle_date_end = $model->vehicle_date_end ? AppHelper::convertToThai($model->vehicle_date_end) : null;

        if ($this->request->isPost) {
            $loaded = $model->load($this->request->post());
            if ($loaded) {
                try {
                    $model->date_start = $model->date_start ? AppHelper::convertToGregorian($model->date_start) : null;
                    $model->date_end = $model->date_end ? AppHelper::convertToGregorian($model->date_end) : null;
                    $model->vehicle_date_start = $model->vehicle_date_start ? AppHelper::convertToGregorian($model->vehicle_date_start) : null;
                    $model->vehicle_date_end = $model->vehicle_date_end ? AppHelper::convertToGregorian($model->vehicle_date_end) : null;
                } catch (\Throwable $th) {
                }
                if ($model->save()) {
                    $this->syncTravelPartyMembers($model, $this->request->post('member_emp_ids', []));
                    Yii::$app->session->setFlash('success', 'บันทึกข้อมูลการเดินทางเรียบร้อยแล้ว');
                    return $this->redirect('index');
                }

                // คืนรูปแบบวันที่สำหรับแสดงค่าที่ผู้ใช้กรอก เมื่อ validation ไม่ผ่าน
                $model->date_start = $model->date_start ? AppHelper::convertToThai($model->date_start) : null;
                $model->date_end = $model->date_end ? AppHelper::convertToThai($model->date_end) : null;
                $model->vehicle_date_start = $model->vehicle_date_start ? AppHelper::convertToThai($model->vehicle_date_start) : null;
                $model->vehicle_date_end = $model->vehicle_date_end ? AppHelper::convertToThai($model->vehicle_date_end) : null;
            }
        }

        // return $this->render('_form_dev', [
        return $this->render('_form', [
            'model' => $model,
            'dateStart2' => $dateStart2
        ]);
    }

    /**
     * Sync คณะเดินทาง (DevelopmentDetail name=member) กับรายการ member_emp_ids จากฟอร์ม
     * @param Development $model
     * @param array $memberEmpIds รหัสพนักงานที่เลือก (จาก member_emp_ids[])
     */
    protected function syncTravelPartyMembers(Development $model, $memberEmpIds)
    {
        if (!is_array($memberEmpIds)) {
            $memberEmpIds = [];
        }
        $memberEmpIds = array_values(array_unique(array_filter(array_map('trim', $memberEmpIds))));

        $existing = DevelopmentDetail::find()
            ->where(['development_id' => $model->id, 'name' => 'member'])
            ->all();

        $existingIds = array_map(function ($d) {
            return $d->emp_id;
        }, $existing);

        foreach ($existing as $detail) {
            if (!in_array($detail->emp_id, $memberEmpIds, true)) {
                $detail->delete();
            }
        }

        foreach ($memberEmpIds as $empId) {
            if (in_array($empId, $existingIds, true)) {
                continue;
            }
            $detail = new DevelopmentDetail();
            $detail->development_id = (int) $model->id;
            $detail->name = 'member';
            $detail->emp_id = $empId;
            $detail->save(false);
        }
    }

    // ทดสอบ form
    public function actionUpdateDev($id)
    {
        $model = $this->findModel($id);
        $model->date_start = $model->date_start ? AppHelper::convertToThai($model->date_start) : null;
        $model->date_end = $model->date_end ? AppHelper::convertToThai($model->date_end) : null;
        $model->vehicle_date_start = $model->vehicle_date_start ? AppHelper::convertToThai($model->vehicle_date_start) : null;
        $model->vehicle_date_end = $model->vehicle_date_end ? AppHelper::convertToThai($model->vehicle_date_end) : null;

        if ($this->request->isPost && $model->load($this->request->post())) {
            try {
                $model->date_start = $model->date_start ? AppHelper::convertToGregorian($model->date_start) : null;
                $model->date_end = $model->date_end ? AppHelper::convertToGregorian($model->date_end) : null;
                $model->vehicle_date_start = $model->vehicle_date_start ? AppHelper::convertToGregorian($model->vehicle_date_start) : null;
                $model->vehicle_date_end = $model->vehicle_date_end ? AppHelper::convertToGregorian($model->vehicle_date_end) : null;
            } catch (\Throwable $th) {
            }

            $model->save();

            return $this->redirect('index');
        }

        return $this->render('_form_dev', [
            // return $this->render('_form', [
            'model' => $model,
        ]);
    }


    public function actionCheck($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('check', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('check', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Development model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    public function actionCancel($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);
        $model->status = 'Cancel';
        $model->save();
        return [
            'status' => 'success',
            'message' => 'ยกเลิกการขอไปราชการเรียบร้อยแล้ว',
        ];
    }


    public function actionFormPdf()
    {
        $check = Categorise::findOne(['name' => 'form_development_pdf']);

        $model = $check ? $check : new Categorise;
        $model->name = 'form_development_pdf';
        if ($this->request->isPost && $model->load($this->request->post())) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            $model->save();
            return [
                'status' => 'success'
            ];
        }

        return $this->render('_form_pdf', ['model' => $model]);
    }

    private function t($text)
    {
        return iconv('UTF-8', 'cp874', $text ?? '');
    }


    protected function GetInfo()
    {
        $info = SiteHelper::getInfo();
        return [
            'company_full' => $info['company_name'] . ' ' . $info['address'],  // ที่อยู่
            'company_name' => $info['company_name'],  // ชื่อหน่วยงาน
            'doc_number' => $info['doc_number'],  // ชื่อหน่วยงาน
            'leader_fullname' => $info['leader_fullname'],  //
            'leader_position' => $info['leader_position'],  //
            'address' => $info['address'],  // ที่อยู่
            'phone' => $info['phone'],  // โทรศัพท์
            'province' => $info['province'],  // ที่อยู่
            'director_name' => $info['director_name'],  // ชื่อผู้บริหาร ผอ.
            'director_fullname' => SiteHelper::viewDirector()['fullname'],  // ชื่อผู้บริหาร ผอ.
            'director_position' => $info['director_position'],  // ตำแหน่งของ ผอ.
            'director' => $info['director'],  // ตำแหน่งของ ผอ.
            'director_type' => $info['director_type']  // ประเภทตำแหน่งของ ผอ.
        ];
    }




    /**
     * ระบบตั้งค่า template รายงานขอไปราชการ — อยู่ภายในโมดูล HR
     */
    public function actionPdfEditor()
    {
        $model = Categorise::findOne(['name' => 'form_development_pdf']);
        if (!$model) {
            $model = new Categorise();
            $model->name = 'form_development_pdf';
            $model->save(false);
        }
        $templatePath = $model->ref ? FileManagerHelper::getFileFormRef($model->ref) : null;
        $hasTemplate = $templatePath && is_file($templatePath);
        $templateUrl = $hasTemplate ? Url::to(['/hr/development/serve-template']) : null;

        return $this->render('pdf_template', [
            'model' => $model,
            'hasTemplate' => $hasTemplate,
            'templateUrl' => $templateUrl,
        ]);
    }

    /**
     * ส่งไฟล์ PDF เทมเพลต (ใช้ใน iframe / กำหนดตำแหน่ง)
     */
    public function actionServeTemplate()
    {
        $layout = Categorise::findOne(['name' => 'form_development_pdf']);
        if (!$layout || !$layout->ref) {
            throw new NotFoundHttpException('ไม่พบข้อมูลเทมเพลต');
        }
        $templateFile = FileManagerHelper::getFileFormRef($layout->ref);
        if (!$templateFile || !is_file($templateFile)) {
            throw new NotFoundHttpException('ไม่พบไฟล์เทมเพลต');
        }
        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->set('Content-Type', 'application/pdf');
        Yii::$app->response->headers->set('Content-Disposition', 'inline; filename="template-development.pdf"');
        Yii::$app->response->content = file_get_contents($templateFile);
        return Yii::$app->response;
    }

    /**
     * ดาวน์โหลดไฟล์เทมเพลต PDF ต้นฉบับ (attachment)
     */
    public function actionDownloadTemplate()
    {
        $layout = Categorise::findOne(['name' => 'form_development_pdf']);
        if (!$layout || !$layout->ref) {
            throw new NotFoundHttpException('ไม่พบข้อมูลเทมเพลต');
        }
        $templateFile = FileManagerHelper::getFileFormRef($layout->ref);
        if (!$templateFile || !is_file($templateFile)) {
            throw new NotFoundHttpException('ไม่พบไฟล์เทมเพลต');
        }
        $filename = 'template-report-official-travel-' . date('Y-m-d') . '.pdf';
        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->set('Content-Type', 'application/pdf');
        Yii::$app->response->headers->set('Content-Disposition', 'attachment; filename="' . addslashes($filename) . '"');
        Yii::$app->response->content = file_get_contents($templateFile);
        return Yii::$app->response;
    }

    /**
     * กำหนดตำแหน่งข้อมูลบน PDF — อยู่ภายในโมดูล HR (รูปแบบเดียวกับ leave: เลือกชุดข้อมูล, checkbox แสดง, ขนาด, ความหนา, format วันที่, ลากวาง)
     */
    public function actionPdfPositions()
    {
        $model = Categorise::findOne(['name' => 'form_development_pdf']);
        if (!$model) {
            return $this->redirect(['/hr/development/pdf-editor']);
        }
        $templatePath = $model->ref ? FileManagerHelper::getFileFormRef($model->ref) : null;
        if (!$templatePath || !is_file($templatePath)) {
            Yii::$app->session->setFlash('warning', 'กรุณาอัปโหลดเทมเพลต PDF ก่อน');
            return $this->redirect(['/hr/development/pdf-editor']);
        }
        $config = $this->getDevelopmentFormConfig();
        $items = $this->getDevelopmentFormItems();
        $fieldLabels = $this->getDevelopmentDefaultFields();
        $signatureKeys = $this->getDevelopmentSignatureKeys();

        return $this->render('pdf_positions', [
            'model' => $model,
            'config' => $config,
            'items' => $items,
            'fieldLabels' => $fieldLabels,
            'signatureKeys' => $signatureKeys,
            'templateUrl' => Url::to(['/hr/development/serve-template']),
        ]);
    }

    /**
     * บันทึกตำแหน่งจากฟอร์มกำหนดตำแหน่ง (JSON POST).
     * เก็บพิกัด X,Y เป็นค่าสัมพัทธ์ (0.00–1.00) ไม่ขึ้นกับความละเอียดจอ — resolution-independent.
     */
    public function actionSavePositions()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $positions = Yii::$app->request->post('positions', []);
        $dateFormat = Yii::$app->request->post('date_format');
        $body = [];
        if (Yii::$app->request->getIsPost()) {
            $raw = Yii::$app->request->getRawBody();
            if ($raw && is_string($raw) && preg_match('/^\s*\{/', $raw)) {
                $body = json_decode($raw, true) ?: [];
                if (!empty($body['positions'])) {
                    $positions = $body['positions'];
                }
                if (isset($body['date_format']) && $body['date_format'] !== '') {
                    $dateFormat = (string) $body['date_format'];
                }
            }
        }
        if (!is_array($positions)) {
            return ['success' => false, 'message' => 'ข้อมูลไม่ถูกต้อง'];
        }
        $defaults = $this->getDevelopmentDefaultFields();
        $sigKeys = $this->getDevelopmentSignatureKeys();
        $config = $this->getDevelopmentFormConfig();
        if ($dateFormat !== null && in_array($dateFormat, ['short', 'medium', 'long', 'numeric'], true)) {
            $config['date_format'] = $dateFormat;
        }
        $items = [];
        foreach ($positions as $itemId => $pos) {
            if (!is_array($pos)) {
                continue;
            }
            $key = isset($pos['key']) ? (string) $pos['key'] : '';
            if ($key === '' || !isset($defaults[$key])) {
                continue;
            }
            $row = [
                'id' => $itemId,
                'key' => $key,
                'x' => PdfCoordinateHelper::validateAndNormalizeCoordinate((float) ($pos['x'] ?? 0)),
                'y' => PdfCoordinateHelper::validateAndNormalizeCoordinate((float) ($pos['y'] ?? 0)),
                'fontSize' => (int) ($pos['fontSize'] ?? 15),
                'bold' => (int) ($pos['bold'] ?? 0),
                'enabled' => (int) ($pos['enabled'] ?? 1),
            ];
            if (in_array($key, $sigKeys, true)) {
                $row['width'] = (float) ($pos['width'] ?? $defaults[$key]['width'] ?? 35);
                $row['height'] = (float) ($pos['height'] ?? $defaults[$key]['height'] ?? 15);
            }
            $items[] = $row;
        }
        $config['items'] = $items;
        // พิกัดและรูปแบบตัวอักษรส่วนรายชื่อคณะเดินทาง — เก็บเป็นค่าสัมพัทธ์ 0–1
        $memberKeys = ['member_fullname_start_x', 'member_fullname_start_y', 'member_position_start_x', 'member_position_start_y', 'line_spacing', 'member_font_size', 'member_bold'];
        foreach ($memberKeys as $key) {
            $v = $body[$key] ?? Yii::$app->request->post($key);
            if ($v !== null && $v !== '') {
                if ($key === 'member_font_size') {
                    $config[$key] = (int) $v;
                } elseif ($key === 'member_bold') {
                    $config[$key] = (int) $v;
                } elseif (in_array($key, ['member_fullname_start_x', 'member_position_start_x'], true)) {
                    $val = (float) $v;
                    $config[$key] = $val <= 1.0 ? PdfCoordinateHelper::validateAndNormalizeCoordinate($val) : PdfCoordinateHelper::validateAndNormalizeCoordinate($val / PdfCoordinateHelper::A4_WIDTH_MM);
                } elseif (in_array($key, ['member_fullname_start_y', 'member_position_start_y'], true)) {
                    $val = (float) $v;
                    $config[$key] = $val <= 1.0 ? PdfCoordinateHelper::validateAndNormalizeCoordinate($val) : PdfCoordinateHelper::validateAndNormalizeCoordinate($val / PdfCoordinateHelper::A4_HEIGHT_MM);
                } else {
                    $config[$key] = (float) $v;
                }
            }
        }
        $cat = $this->getDevelopmentConfigRecord();
        $existing = is_string($cat->data_json) ? json_decode($cat->data_json, true) : $cat->data_json;
        if (is_array($existing)) {
            foreach (['member_fullname_start_x', 'member_fullname_start_y', 'member_position_start_x', 'member_position_start_y', 'line_spacing', 'member_font_size', 'member_bold'] as $key) {
                if (!isset($config[$key]) && isset($existing[$key])) {
                    $config[$key] = $existing[$key];
                }
            }
        }
        $cat->data_json = json_encode($config);
        if ($cat->save(false)) {
            return ['success' => true];
        }
        return ['success' => false, 'message' => 'บันทึกไม่สำเร็จ'];
    }

    /**
     * ส่งออกการตั้งค่า PDF template (JSON) — ดาวน์โหลดไฟล์
     */
    public function actionExportPdfSettings()
    {
        $config = $this->getDevelopmentFormConfig();
        $json = json_encode($config, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $filename = 'development-pdf-settings-' . date('Y-m-d-His') . '.json';
        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->set('Content-Type', 'application/json; charset=utf-8');
        Yii::$app->response->headers->set('Content-Disposition', 'attachment; filename="' . addslashes($filename) . '"');
        return $json;
    }

    /**
     * นำเข้าการตั้งค่า PDF template จากไฟล์ JSON (POST เท่านั้น)
     */
    public function actionImportPdfSettings()
    {
        if (!Yii::$app->request->isPost) {
            return $this->redirect(['/hr/development/pdf-positions']);
        }
        $file = \yii\web\UploadedFile::getInstanceByName('settings_file');
        if (!$file || $file->error !== UPLOAD_ERR_OK) {
            Yii::$app->session->setFlash('error', 'กรุณาเลือกไฟล์ JSON ที่ส่งออกจากการตั้งค่า');
            return $this->redirect(['/hr/development/pdf-positions']);
        }
        $content = file_get_contents($file->tempName);
        $data = json_decode($content, true);
        if (!is_array($data)) {
            Yii::$app->session->setFlash('error', 'รูปแบบไฟล์ไม่ถูกต้อง (ต้องเป็น JSON)');
            return $this->redirect(['/hr/development/pdf-positions']);
        }
        $defaults = $this->getDevelopmentDefaultFields();
        $sigKeys = $this->getDevelopmentSignatureKeys();
        if (!empty($data['items']) && is_array($data['items'])) {
            $validItems = [];
            foreach ($data['items'] as $item) {
                $key = $item['key'] ?? '';
                if ($key === '' || !isset($defaults[$key])) {
                    continue;
                }
                $x = (float)($item['x'] ?? 0);
                $y = (float)($item['y'] ?? 0);
                $row = [
                    'id' => $item['id'] ?? uniqid('item_'),
                    'key' => $key,
                    'x' => $x <= 1 ? PdfCoordinateHelper::validateAndNormalizeCoordinate($x) : PdfCoordinateHelper::validateAndNormalizeCoordinate($x / PdfCoordinateHelper::A4_WIDTH_MM),
                    'y' => $y <= 1 ? PdfCoordinateHelper::validateAndNormalizeCoordinate($y) : PdfCoordinateHelper::validateAndNormalizeCoordinate($y / PdfCoordinateHelper::A4_HEIGHT_MM),
                    'fontSize' => (int)($item['fontSize'] ?? 15),
                    'bold' => (int)($item['bold'] ?? 0),
                    'enabled' => isset($item['enabled']) ? (int)$item['enabled'] : 1,
                ];
                if (in_array($key, $sigKeys, true)) {
                    $row['width'] = (float)($item['width'] ?? $defaults[$key]['width'] ?? 35);
                    $row['height'] = (float)($item['height'] ?? $defaults[$key]['height'] ?? 15);
                }
                $validItems[] = $row;
            }
            $data['items'] = $validItems;
        }
        foreach (['member_fullname_start_x', 'member_position_start_x'] as $kx) {
            if (isset($data[$kx])) {
                $v = (float)$data[$kx];
                $data[$kx] = $v <= 1 ? PdfCoordinateHelper::validateAndNormalizeCoordinate($v) : PdfCoordinateHelper::validateAndNormalizeCoordinate($v / PdfCoordinateHelper::A4_WIDTH_MM);
            }
        }
        foreach (['member_fullname_start_y', 'member_position_start_y'] as $ky) {
            if (isset($data[$ky])) {
                $v = (float)$data[$ky];
                $data[$ky] = $v <= 1 ? PdfCoordinateHelper::validateAndNormalizeCoordinate($v) : PdfCoordinateHelper::validateAndNormalizeCoordinate($v / PdfCoordinateHelper::A4_HEIGHT_MM);
            }
        }
        $allowedKeys = [
            'items',
            'date_format',
            'member_fullname_start_x',
            'member_fullname_start_y',
            'member_position_start_x',
            'member_position_start_y',
            'line_spacing',
            'member_font_size',
            'member_bold',
        ];
        $imported = array_intersect_key($data, array_fill_keys($allowedKeys, true));
        if (empty($imported)) {
            Yii::$app->session->setFlash('error', 'ไฟล์ไม่มีข้อมูลการตั้งค่าที่รองรับ');
            return $this->redirect(['/hr/development/pdf-positions']);
        }
        $existing = $this->getDevelopmentFormConfig();
        $config = array_merge($existing, $imported);
        $cat = $this->getDevelopmentConfigRecord();
        $cat->data_json = json_encode($config);
        if ($cat->save(false)) {
            Yii::$app->session->setFlash('success', 'นำเข้าการตั้งค่าเรียบร้อย');
        } else {
            Yii::$app->session->setFlash('error', 'บันทึกการตั้งค่าไม่สำเร็จ');
        }
        return $this->redirect(['/hr/development/pdf-positions']);
    }

    /** คืน data_json ของ Development เป็น array */
    protected function developmentDataJson(Development $model): array
    {
        $v = $model->data_json;
        return is_array($v) ? $v : (json_decode((string) $v, true) ?: []);
    }

    protected function resolvePdfEmployeeTypeName($employee): string
    {
        if (!$employee) {
            return '';
        }

        foreach (['employeeTypeName', 'positionTypeName'] as $method) {
            if (!method_exists($employee, $method)) {
                continue;
            }
            $value = $employee->$method();
            $text = trim((string) ($value === false ? '' : $value));
            if ($text !== '' && $text !== '-') {
                return $text;
            }
        }

        return '';
    }

    /**
     * รายการผู้อนุมัติจากตาราง approve (ชื่อนามสกุล ตำแหน่ง วันที่อนุมัติ path ลายเซ็น) สำหรับใส่ใน PDF.
     * คืน array แบบแบน: approver_1_fullname, approver_1_position, approver_1_approve_date, approver_1_signature, ...
     */
    protected function getDevelopmentApproversData(Development $model): array
    {
        $out = [];
        $approves = Approve::find()
            ->where(['from_id' => (string) $model->id, 'name' => 'development'])
            ->andWhere(['not in', 'status', ['None', 'Pending']])
            ->orderBy(['level' => SORT_ASC])
            ->with('employee')
            ->all();
        $n = 1;
        foreach ($approves as $a) {
            $emp = $a->employee;
            $fullname = $emp ? (!empty($emp->fullname) ? $emp->fullname : ($emp->prefix . ' ' . $emp->fname . ' ' . $emp->lname)) : '-';
            $position = $emp && method_exists($emp, 'positionName') ? ($emp->positionName() ?? '-') : '-';
            $employeeType = $this->resolvePdfEmployeeTypeName($emp);
            $dataJson = is_array($a->data_json) ? $a->data_json : (json_decode((string) $a->data_json, true) ?: []);
            $approveDate = isset($dataJson['approve_date']) ? (string) $dataJson['approve_date'] : '';
            $signaturePath = $emp && method_exists($emp, 'SignatureFilePath') ? ($emp->SignatureFilePath() ?? '') : '';
            if ($signaturePath !== '' && !is_file($signaturePath)) {
                $signaturePath = '';
            }
            $out['approver_' . $n . '_fullname'] = $fullname;
            $out['approver_' . $n . '_position'] = $position;
            $out['approver_' . $n . '_employee_type'] = $employeeType;
            $out['approver_' . $n . '_approve_date'] = $approveDate;
            $out['approver_' . $n . '_signature'] = $signaturePath;
            $out['approver_' . $n . '_status'] = (string) ($a->status ?? '');
            $n++;
            if ($n > 4) {
                break;
            }
        }
        return $out;
    }

    /**
     * รายการคณะเดินทางเป็น array สำหรับ loop ใน PDF: [ ['fullname' => ..., 'position' => ...], ... ]
     */
    protected function getDevelopmentTravelPartyMembersArray(Development $model): array
    {
        $list = [];
        foreach ($model->listMemberForPdf() as $detail) {
            $emp = $detail->emp;
            $dataJson = is_array($detail->data_json) ? $detail->data_json : (json_decode((string) $detail->data_json, true) ?: []);
            $label = trim((string) ($dataJson['label'] ?? ''));
            $fullname = $emp ? (!empty($emp->fullname) ? $emp->fullname : ($emp->prefix . ' ' . $emp->fname . ' ' . $emp->lname)) : ($label ?: '-');
            $position = $emp && method_exists($emp, 'positionName') ? ($emp->positionName() ?? '-') : '-';
            $list[] = [
                'fullname' => $fullname,
                'position' => $position,
                'employee_type' => $this->resolvePdfEmployeeTypeName($emp),
            ];
        }
        return $list;
    }

    /** รวมค่าใช้จ่ายทุกหมวด (ประมาณค่าใช้จ่ายในฟอร์ม + รายการ expense_type เดิม) สำหรับใส่ใน PDF */
    protected function getDevelopmentTotalExpense(Development $model): string
    {
        return number_format($model->totalEstimatedCost(true), 0);
    }

    /**
     * นับจำนวนวันจาก date_start ถึง date_end (รวมวันต้นและวันปลาย) สำหรับใส่ใน PDF ฟิลด์ «นับวัน».
     * ตัวอย่าง 16/01/2569 ถึง 18/01/2569 = 3 วัน
     * @return string จำนวนวัน เช่น "1", "3" หรือ "-" ถ้าไม่มีข้อมูล
     */
    protected function getDevelopmentTripDays(Development $model): string
    {
        $dateStart = $model->date_start ? trim((string) $model->date_start) : '';
        $dateEnd = $model->date_end ? trim((string) $model->date_end) : '';
        if ($dateStart === '' || $dateEnd === '') {
            return '-';
        }
        if (strpos($dateStart, '/') !== false) {
            $dateStart = AppHelper::convertToGregorian($dateStart) ?: $dateStart;
        }
        if (strpos($dateEnd, '/') !== false) {
            $dateEnd = AppHelper::convertToGregorian($dateEnd) ?: $dateEnd;
        }
        try {
            $s = new \DateTime($dateStart);
            $e = new \DateTime($dateEnd);
            $days = $s->diff($e)->days + 1;
            return (string) (int) $days;
        } catch (\Throwable $e) {
            return '-';
        }
    }

    /**
     * คืนยอดแสดงตามประเภท (ค่าลงทะเบียน, ค่าที่พัก, ค่ายานพาหนะ, ค่าเบี้ยเลี้ยง, ค่าอื่น ๆ) สำหรับใส่ใน PDF.
     * หมวดที่ไม่มียอดจะคืนค่าว่าง เพื่อไม่ให้ PDF พิมพ์เลข 0 เต็มฟอร์ม
     */
    protected function getDevelopmentExpenseAmountsByCategory(Development $model): array
    {
        $formatted = [];
        foreach ($model->estimatedCostAmounts(true) as $key => $amount) {
            $formatted[$key] = $amount > 0 ? number_format($amount, 0) : '';
        }
        return $formatted;
    }

    /** ชุดฟิลด์เริ่มต้นสำหรับใบขอไปราชการ (ให้เลือกได้ในหน้ากำหนดตำแหน่ง) */
    protected function getDevelopmentDefaultFields(): array
    {
        return [
            'company_name' => ['label' => 'ส่วนราชการ', 'x' => 30, 'y' => 40, 'fontSize' => 14, 'bold' => 0, 'enabled' => 1],
            'doc_number' => ['label' => 'เลขที่', 'x' => 30, 'y' => 40, 'fontSize' => 14, 'bold' => 0, 'enabled' => 1],
            'doc_date' => ['label' => 'วันที่', 'x' => 120, 'y' => 40, 'fontSize' => 14, 'bold' => 0, 'enabled' => 1],
            'fullname' => ['label' => 'ชื่อ-นามสกุลผู้ขอ', 'x' => 30, 'y' => 55, 'fontSize' => 15, 'bold' => 0, 'enabled' => 1],
            'position' => ['label' => 'ตำแหน่ง', 'x' => 30, 'y' => 62, 'fontSize' => 14, 'bold' => 0, 'enabled' => 1],
            'topic' => ['label' => 'หัวข้อ/เรื่อง', 'x' => 30, 'y' => 75, 'fontSize' => 15, 'bold' => 0, 'enabled' => 1],
            'location' => ['label' => 'สถานที่', 'x' => 30, 'y' => 90, 'fontSize' => 14, 'bold' => 0, 'enabled' => 1],
            'date_start' => ['label' => 'ตั้งแต่วันที่', 'x' => 30, 'y' => 102, 'fontSize' => 14, 'bold' => 0, 'enabled' => 1],
            'date_end' => ['label' => 'ถึงวันที่', 'x' => 100, 'y' => 102, 'fontSize' => 14, 'bold' => 0, 'enabled' => 1],
            'vehicle_date_start' => ['label' => 'วันออกเดินทาง', 'x' => 30, 'y' => 112, 'fontSize' => 14, 'bold' => 0, 'enabled' => 1],
            'vehicle_time_start' => ['label' => 'เวลาออก', 'x' => 80, 'y' => 112, 'fontSize' => 14, 'bold' => 0, 'enabled' => 1],
            'vehicle_date_end' => ['label' => 'วันกลับ', 'x' => 100, 'y' => 112, 'fontSize' => 14, 'bold' => 0, 'enabled' => 1],
            'vehicle_time_end' => ['label' => 'เวลากลับ', 'x' => 150, 'y' => 112, 'fontSize' => 14, 'bold' => 0, 'enabled' => 1],
            'claim_type_name' => ['label' => 'ประเภทค่าใช้จ่าย', 'x' => 30, 'y' => 122, 'fontSize' => 14, 'bold' => 0, 'enabled' => 1],
            'total_days' => ['label' => 'จำนวนวัน', 'x' => 30, 'y' => 132, 'fontSize' => 14, 'bold' => 0, 'enabled' => 1],
            'distance' => ['label' => 'ระยะทาง', 'x' => 100, 'y' => 132, 'fontSize' => 14, 'bold' => 0, 'enabled' => 1],
            'vehicle_type' => ['label' => 'เดินทางโดย', 'x' => 100, 'y' => 122, 'fontSize' => 14, 'bold' => 0, 'enabled' => 1],
            'requester_signature' => ['label' => 'ลายเซ็นต์ผู้ขอ', 'x' => 30, 'y' => 170, 'fontSize' => 12, 'bold' => 0, 'enabled' => 1, 'width' => 35, 'height' => 15],
            'assigned_to' => ['label' => 'ผู้ปฏิบัติหน้าที่แทน', 'x' => 30, 'y' => 180, 'fontSize' => 14, 'bold' => 0, 'enabled' => 1],
            'assigned_to_position' => ['label' => 'ตำแหน่งผู้ปฏิบัติแทน', 'x' => 30, 'y' => 188, 'fontSize' => 14, 'bold' => 0, 'enabled' => 1],
            'assigned_to_signature' => ['label' => 'ลายเซ็นผู้ปฏิบัติแทน', 'x' => 30, 'y' => 220, 'fontSize' => 12, 'bold' => 0, 'enabled' => 1, 'width' => 35, 'height' => 15],
            'leader_group_fullname' => ['label' => 'ชื่อ-นามสกุลหัวหน้ากลุ่มงาน', 'x' => 30, 'y' => 195, 'fontSize' => 12, 'bold' => 0, 'enabled' => 1],
            'leader_group_position' => ['label' => 'ตำแหน่งหัวหน้ากลุ่มงาน', 'x' => 30, 'y' => 202, 'fontSize' => 12, 'bold' => 0, 'enabled' => 1],
            'leader_group_signature' => ['label' => 'ลายเซ็นหัวหน้ากลุ่มงาน', 'x' => 100, 'y' => 195, 'fontSize' => 12, 'bold' => 0, 'enabled' => 1, 'width' => 35, 'height' => 15],
            'approve_status' => ['label' => 'คำสั่ง (อนุมัติ/ไม่อนุมัติ)', 'x' => 30, 'y' => 218, 'fontSize' => 12, 'bold' => 0, 'enabled' => 1],
            'leader_fullname' => ['label' => 'ชื่อผอ.', 'x' => 100, 'y' => 220, 'fontSize' => 12, 'bold' => 0, 'enabled' => 1],
            'leader_date' => ['label' => 'วันที่อนุมัติ', 'x' => 100, 'y' => 228, 'fontSize' => 12, 'bold' => 0, 'enabled' => 1],
            'approve_date' => ['label' => 'วันที่ลงนาม', 'x' => 30, 'y' => 250, 'fontSize' => 12, 'bold' => 0, 'enabled' => 1],
            'signature_approver' => ['label' => 'ลายเซ็นผอ.', 'x' => 30, 'y' => 250, 'fontSize' => 12, 'bold' => 0, 'enabled' => 1, 'width' => 35, 'height' => 15],
        ];
    }

    protected function getDevelopmentSignatureKeys(): array
    {
        return ['requester_signature', 'assigned_to_signature', 'leader_group_signature', 'signature_approver'];
    }

    protected function getDevelopmentConfigRecord(): Categorise
    {
        $cat = Categorise::findOne(['name' => 'form_development_pdf']);
        if (!$cat) {
            $cat = new Categorise();
            $cat->name = 'form_development_pdf';
            $cat->save(false);
        }
        $decoded = is_string($cat->data_json) ? json_decode($cat->data_json, true) : $cat->data_json;
        if (!is_array($decoded) || empty($decoded['items'])) {
            $defaults = $this->getDevelopmentDefaultFields();
            $sigKeys = $this->getDevelopmentSignatureKeys();
            $items = [];
            foreach ($defaults as $key => $def) {
                $row = [
                    'id' => 'legacy_' . $key,
                    'key' => $key,
                    'x' => (float) ($def['x'] ?? 0),
                    'y' => (float) ($def['y'] ?? 0),
                    'fontSize' => (int) ($def['fontSize'] ?? 15),
                    'bold' => (int) ($def['bold'] ?? 0),
                    'enabled' => (int) ($def['enabled'] ?? 1),
                ];
                if (in_array($key, $sigKeys, true)) {
                    $row['width'] = (float) ($def['width'] ?? 35);
                    $row['height'] = (float) ($def['height'] ?? 15);
                }
                $items[] = $row;
            }
            $cat->data_json = json_encode([
                'items' => $items,
                'date_format' => 'medium',
                'member_fullname_start_x' => 25,
                'member_fullname_start_y' => 85,
                'member_position_start_x' => 100,
                'member_position_start_y' => 85,
                'line_spacing' => 5.5,
                'member_font_size' => 14,
                'member_bold' => 0,
            ]);
            $cat->save(false);
            $cat->refresh();
        }
        return $cat;
    }

    protected function getDevelopmentFormConfig(): array
    {
        $cat = $this->getDevelopmentConfigRecord();
        $json = $cat->data_json;
        if (is_string($json)) {
            $json = json_decode($json, true);
        }
        return is_array($json) ? $json : [];
    }

    protected function getDevelopmentFormItems(): array
    {
        $config = $this->getDevelopmentFormConfig();
        $defaults = $this->getDevelopmentDefaultFields();
        $sigKeys = $this->getDevelopmentSignatureKeys();
        if (!empty($config['items'])) {
            $list = [];
            foreach ($config['items'] as $item) {
                $key = $item['key'] ?? '';
                if ($key === 'travel_party' || $key === 'fullname_signature' || $key === 'position_signature') {
                    continue;
                }
                $row = [
                    'id' => $item['id'] ?? uniqid('item_'),
                    'key' => $key,
                    'x' => (float) ($item['x'] ?? 0),
                    'y' => (float) ($item['y'] ?? 0),
                    'fontSize' => (int) ($item['fontSize'] ?? 15),
                    'bold' => !empty($item['bold']),
                    'enabled' => isset($item['enabled']) ? (int) $item['enabled'] : 1,
                    'label' => $defaults[$key]['label'] ?? $key,
                ];
                if (in_array($key, $sigKeys, true)) {
                    $row['width'] = (float) ($item['width'] ?? $defaults[$key]['width'] ?? 35);
                    $row['height'] = (float) ($item['height'] ?? $defaults[$key]['height'] ?? 15);
                }
                $list[] = $row;
            }
            return $list;
        }
        $items = [];
        foreach ($defaults as $key => $f) {
            $row = [
                'id' => 'legacy_' . $key,
                'key' => $key,
                'x' => (float) ($f['x'] ?? 0),
                'y' => (float) ($f['y'] ?? 0),
                'fontSize' => (int) ($f['fontSize'] ?? 15),
                'bold' => !empty($f['bold']),
                'enabled' => (int) ($f['enabled'] ?? 1),
                'label' => $f['label'] ?? $key,
            ];
            if (in_array($key, $sigKeys, true)) {
                $row['width'] = (float) ($f['width'] ?? 35);
                $row['height'] = (float) ($f['height'] ?? 15);
            }
            $items[] = $row;
        }
        return $items;
    }


    /**
     * พิมพ์ใบขอไปราชการเป็น PDF (ใช้เทมเพลตจากโมดูล pdf-template + ข้อมูลจริงจาก DB).
     *
     * @param int $id Development ID
     * @return \yii\web\Response binary PDF
     * @throws NotFoundHttpException เมื่อไม่พบรายการหรือไม่มีเทมเพลต
     */
    public function actionPrint($id)
    {
        $model = Development::find()->where(['id' => (int) $id])->with('createdByEmp', 'assignedTo', 'document')->one();
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบรายการขอไปราชการ');
        }

        $pdfTemplate = PdfTemplate::findForContext(PdfTemplate::CONTEXT_DEVELOPMENT);
        if (!$pdfTemplate) {
            throw new NotFoundHttpException('ยังไม่มีเทมเพลต PDF กรุณาเพิ่มและตั้งค่าเทมเพลตที่ /pdf-template');
        }

        $templateService = new PdfTemplateService();
        $templatePath = $templateService->getTemplateFilePath($pdfTemplate);
        if ($templatePath === null || !is_file($templatePath)) {
            throw new NotFoundHttpException('ไม่พบไฟล์เทมเพลต PDF กรุณาอัปโหลดที่ /pdf-template');
        }

        $data = $this->buildDevelopmentTemplateData($model);

        Yii::info('HR actionPrint: id=' . $id . ', officer=' . $data['officer_name'] . ', topic=' . ($data['topic'] ?? ''), __METHOD__);

        $pdfBinary = $templateService->generatePdfWithData((int) $pdfTemplate->id, $data);

        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->set('Content-Type', 'application/pdf');
        Yii::$app->response->headers->set('Content-Disposition', 'inline; filename="report-' . $model->id . '.pdf"');
        Yii::$app->response->headers->set('X-PDF-Source', 'hr-development-print');
        Yii::$app->response->headers->set('X-PDF-Officer', substr($data['officer_name'], 0, 50));
        Yii::$app->response->content = $pdfBinary;
        return Yii::$app->response;
    }

    /**
     * พิมพ์ใบขอใช้รถยนต์ส่วนตัวเดินทางไปราชการเป็น PDF
     * ใช้เทมเพลตที่ตั้งค่า use_for_context = booking.vehicle.official ที่ /pdf-template (strict — ไม่ fallback).
     *
     * @param int $id Development ID
     * @return \yii\web\Response binary PDF
     * @throws NotFoundHttpException เมื่อไม่พบรายการหรือยังไม่ตั้งค่าเทมเพลต
     */
    public function actionPrintPersonalVehicle($id)
    {
        $model = Development::find()->where(['id' => (int) $id])->with('createdByEmp', 'assignedTo', 'document')->one();
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบรายการขอไปราชการ');
        }

        $pdfTemplate = PdfTemplate::find()
            ->where(['use_for_context' => PdfTemplate::CONTEXT_BOOKING_VEHICLE_OFFICIAL])
            ->one();
        if (!$pdfTemplate) {
            throw new NotFoundHttpException('ยังไม่ได้ตั้งค่าเทมเพลตสำหรับ «ใบขอใช้รถยนต์ส่วนตัวเดินทางไปราชการ» — โปรดไปที่ /pdf-template แล้วเลือกเทมเพลตให้กับ context "booking.vehicle.official"');
        }

        $templateService = new PdfTemplateService();
        $templatePath = $templateService->getTemplateFilePath($pdfTemplate);
        if ($templatePath === null || !is_file($templatePath)) {
            throw new NotFoundHttpException('ไม่พบไฟล์เทมเพลต PDF ของใบขอใช้รถยนต์ส่วนตัว กรุณาอัปโหลดที่ /pdf-template');
        }

        $data = $this->buildDevelopmentTemplateData($model);
        $pdfBinary = $templateService->generatePdfWithData((int) $pdfTemplate->id, $data);

        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->set('Content-Type', 'application/pdf');
        Yii::$app->response->headers->set('Content-Disposition', 'inline; filename="personal-vehicle-' . $model->id . '.pdf"');
        Yii::$app->response->headers->set('X-PDF-Source', 'hr-development-print-personal-vehicle');
        Yii::$app->response->content = $pdfBinary;
        return Yii::$app->response;
    }

    /**
     * สร้าง array ข้อมูลสำหรับใส่ลง PDF เทมเพลต (ใช้ทั้งใน actionPrint, actionPrintPersonalVehicle, actionPrintData).
     */
    protected function buildDevelopmentTemplateData(Development $model): array
    {
        $dataJson = $this->developmentDataJson($model);
        $emp = $model->createdByEmp;
        $officerName = '-';
        $officerPosition = '-';
        $officerEmployeeType = '';
        $officerSignature = '';
        if ($emp) {
            $officerName = !empty($emp->fullname) ? $emp->fullname : (method_exists($emp, 'fullname') ? $emp->fullname() : ($emp->prefix . ' ' . $emp->fname . ' ' . $emp->lname));
            $officerPosition = method_exists($emp, 'positionName') ? ($emp->positionName() ?? '-') : '-';
            $officerEmployeeType = $this->resolvePdfEmployeeTypeName($emp);
            $sig = method_exists($emp, 'SignatureFilePath') ? ($emp->SignatureFilePath() ?? '') : '';
            $officerSignature = ($sig !== '' && is_file($sig)) ? $sig : '';
        }

        $assignee = $model->assignedTo;
        $assignedToFullname = '-';
        $assignedToPosition = '-';
        $assignedToEmployeeType = '';
        $assignedToSignature = '';
        if ($assignee) {
            $assignedToFullname = !empty($assignee->fullname) ? $assignee->fullname : ($assignee->prefix . ' ' . $assignee->fname . ' ' . $assignee->lname);
            $assignedToPosition = method_exists($assignee, 'positionName') ? ($assignee->positionName() ?? '-') : '-';
            $assignedToEmployeeType = $this->resolvePdfEmployeeTypeName($assignee);
            $sig = method_exists($assignee, 'SignatureFilePath') ? ($assignee->SignatureFilePath() ?? '') : '';
            $assignedToSignature = ($sig !== '' && is_file($sig)) ? $sig : '';
        }

        $info = SiteHelper::getInfo();
        $doc = $model->document;
        return array_merge([
            'organization_name' => (string) ($info['company_name'] ?? '-'),
            'reference_document' => $doc ? (string) ($doc->topic ?? '') : '',
            'document_number' => (string) ($dataJson['doc_number'] ?? $info['doc_number'] ?? '-'),
            'thai_year' => (string) (int) $model->thai_year,
            'custom_text' => (string) ($dataJson['custom_text'] ?? ''),
            'employee_type' => $officerEmployeeType,
            'officer_name' => $officerName,
            'officer_position' => $officerPosition,
            'officer_employee_type' => $officerEmployeeType,
            'officer_signature' => $officerSignature,
            'assigned_to_fullname' => $assignedToFullname,
            'assigned_to_position' => $assignedToPosition,
            'assigned_to_employee_type' => $assignedToEmployeeType,
            'assigned_to_signature' => $assignedToSignature,
            'document_date' => date('Y-m-d'),
            'topic' => (string) ($model->topic ?? ''),
            'location' => (string) ($dataJson['location'] ?? '-'),
            'location_org' => (string) ($dataJson['location_org'] ?? ''),
            'province_name' => (string) ($dataJson['province_name'] ?? ''),
            'vehicle_type_title' => $model->vehicleType ? (string) $model->vehicleType->title : '-',
            'license_plate' => (string) ($dataJson['license_plate'] ?? ''),
            'distance' => (string) ($dataJson['distance'] ?? ''),
            'total_expense' => $this->getDevelopmentTotalExpense($model),
        ], $this->getDevelopmentExpenseAmountsByCategory($model), [
            'date_start' => $model->date_start ? (string) $model->date_start : '',
            'date_end' => $model->date_end ? (string) $model->date_end : '',
            'vehicle_date_start' => $model->vehicle_date_start ? (string) $model->vehicle_date_start : '',
            'vehicle_date_end' => $model->vehicle_date_end ? (string) $model->vehicle_date_end : '',
            'vehicle_time_start' => (string) ($dataJson['vehicle_time_start'] ?? ''),
            'vehicle_time_end' => (string) ($dataJson['vehicle_time_end'] ?? ''),
            'trip_days' => $this->getDevelopmentTripDays($model),
            'travel_party' => (string) ($dataJson['travel_party'] ?? ''),
        ], $this->getDevelopmentApproversData($model), [
            'travel_party_members' => $this->getDevelopmentTravelPartyMembersArray($model),
        ]);
    }

    /**
     * คืนข้อมูลที่จะใส่ใน PDF เป็น JSON (ให้ editor ที่ /pdf-template ดึงไปแสดงตอนตั้งค่า).
     */
    public function actionPrintData($id)
    {
        $model = Development::find()->where(['id' => (int) $id])->with('createdByEmp', 'assignedTo', 'document')->one();
        if (!$model) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['error' => 'ไม่พบรายการ'];
        }
        Yii::$app->response->format = Response::FORMAT_JSON;
        Yii::$app->response->headers->set('Access-Control-Allow-Origin', Yii::$app->request->origin ?? '*');
        return $this->buildDevelopmentTemplateData($model);
    }

    public function actionPrintBackup($id)
    {
        $model = $this->findModel($id);

        // ถ้ามีเทมเพลตจากโมดูล pdf-template ให้ใช้พิมพ์จากนั้น (ตั้งค่าได้ที่ /pdf-template)
        $pdfTemplate = PdfTemplate::find()->orderBy(['id' => SORT_ASC])->one();
        if ($pdfTemplate) {
            $pdfTemplateService = new PdfTemplateService();
            $templatePath = $pdfTemplateService->getTemplateFilePath($pdfTemplate);
            if ($templatePath && is_file($templatePath)) {
                $info = $this->GetInfo();
                $data = [
                    'officer_name' => $model->createdByEmp ? $model->createdByEmp->fullname : '-',
                    'document_date' => ThaiDateHelper::formatThaiDate(date('Y-m-d'), 'medium'),
                    'topic' => (string) $model->topic,
                    'location' => (string) ($this->developmentDataJson($model)['location'] ?? '-'),
                    'date_start' => ThaiDateHelper::formatThaiDate($model->date_start ?? '', 'medium'),
                    'date_end' => ThaiDateHelper::formatThaiDate($model->date_end ?? '', 'medium'),
                ];
                try {
                    $pdfBinary = $pdfTemplateService->generatePdfWithData((int) $pdfTemplate->id, $data);
                    Yii::$app->response->format = Response::FORMAT_RAW;
                    Yii::$app->response->headers->set('Content-Type', 'application/pdf');
                    Yii::$app->response->headers->set('Content-Disposition', 'inline; filename="report-' . $model->id . '.pdf"');
                    Yii::$app->response->content = $pdfBinary;
                    return Yii::$app->response;
                } catch (\Throwable $e) {
                    // ถ้าเจนจาก pdf-template ไม่ได้ ใช้ flow เดิมด้านล่าง
                }
            }
        }

        $formName = 'form_development_pdf';
        $layout = Categorise::findOne(['name' => $formName]);

        if (!$layout) {
            throw new NotFoundHttpException("ไม่พบข้อมูลเลย์เอาต์สำหรับฟอร์ม: $formName");
        }

        $templateFile = $layout->ref ? FileManagerHelper::getFileFormRef($layout->ref) : null;
        if (!$templateFile || !is_file($templateFile)) {
            Yii::$app->session->setFlash('error', 'ไม่พบไฟล์เทมเพลต PDF กรุณาอัปโหลดเทมเพลตที่หน้าตั้งค่า Template รายงานขอไปราชการ หรือใช้เทมเพลตจาก /pdf-template');
            return $this->redirect(['pdf-editor']);
        }
        // 3. เริ่มต้นสร้าง PDF ด้วย FPDI
        if (!defined('FPDF_FONTPATH')) {
            define('FPDF_FONTPATH', Yii::getAlias('@webroot/fonts/'));
        }
        $pdf = new Fpdi();
        // ตั้งค่าฟอนต์ไทย (ไฟล์ .php อยู่ที่ web/fonts/)
        $pdf->AddFont('THSarabunNew', '', 'THSarabunNew.json');
        $pdf->AddFont('THSarabunNew', 'B', 'THSarabunNew Bold.json');

        // โหลดเทมเพลตหน้าแรก
        $pdf->setSourceFile($templateFile);
        $tplIdx = $pdf->importPage(1);
        $size = $pdf->getTemplateSize($tplIdx);

        // เพิ่มหน้าตามขนาดต้นฉบับ (ปกติเป็น A4: 210×297 mm) — FPDI SetXY ใช้หน่วย mm
        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
        $pdf->useTemplate($tplIdx);

        $paperW = (float) ($size['width'] ?? PdfCoordinateHelper::A4_WIDTH_MM);
        $paperH = (float) ($size['height'] ?? PdfCoordinateHelper::A4_HEIGHT_MM);
        $offsetX = 0;
        $offsetY = 0;

        if (Yii::$app->request->get('debug')) {
            $cx = $paperW * 0.5;
            $cy = $paperH * 0.5;
            $pdf->SetDrawColor(255, 0, 0);
            $pdf->SetLineWidth(0.3);
            $pdf->Line($cx - 5, $cy, $cx + 5, $cy);
            $pdf->Line($cx, $cy - 5, $cx, $cy + 5);
        }

        $info = $this->GetInfo();
        $dataJson = $layout->data_json ?? [];
        if (is_string($dataJson)) {
            $dataJson = json_decode($dataJson, true) ?: [];
        }
        // แปลงค่าสัมพัทธ์ (0–1) หรือ legacy mm เป็น mm สำหรับ FPDI
        $sigKeys = $this->getDevelopmentSignatureKeys();
        if (!empty($dataJson['items'])) {
            $flat = [];
            $positionsByKey = [];
            foreach ($dataJson['items'] as $item) {
                $k = $item['key'] ?? '';
                if ($k === '' || (isset($item['enabled']) && (int) $item['enabled'] === 0)) {
                    continue;
                }
                $storedX = (float) ($item['x'] ?? 0);
                $storedY = (float) ($item['y'] ?? 0);
                $mm = PdfCoordinateHelper::normalizedOrMmToMm($storedX, $storedY, $paperW, $paperH);
                $pos = [
                    'x' => $mm['x'],
                    'y' => $mm['y'],
                    'fontSize' => (int) ($item['fontSize'] ?? 15),
                    'bold' => !empty($item['bold']) ? 1 : 0,
                ];
                if (in_array($k, $sigKeys, true)) {
                    $pos['width'] = (float) ($item['width'] ?? 35);
                    $pos['height'] = (float) ($item['height'] ?? 15);
                }
                $positionsByKey[$k][] = $pos;
                if (!isset($flat[$k . '_x'])) {
                    $flat[$k . '_x'] = $pos['x'];
                    $flat[$k . '_y'] = $pos['y'];
                    $flat[$k . '_fontSize'] = $pos['fontSize'];
                    $flat[$k . '_bold'] = $pos['bold'];
                    if (isset($pos['width'])) {
                        $flat[$k . '_width'] = $pos['width'];
                        $flat[$k . '_height'] = $pos['height'];
                    }
                }
            }
            $dataJson = array_merge($dataJson, $flat);
            $dataJson['_positionsByKey'] = $positionsByKey;
        } else {
            $defaults = $this->getDevelopmentDefaultFields();
            foreach ($defaults as $key => $def) {
                $xKey = $key . '_x';
                $yKey = $key . '_y';
                if (!isset($dataJson[$xKey]) || !isset($dataJson[$yKey])) {
                    $defMm = PdfCoordinateHelper::normalizedOrMmToMm((float)($def['x'] ?? 0), (float)($def['y'] ?? 0), $paperW, $paperH);
                    $dataJson[$xKey] = $defMm['x'];
                    $dataJson[$yKey] = $defMm['y'];
                    $dataJson[$key . '_fontSize'] = (int) ($def['fontSize'] ?? 15);
                    $dataJson[$key . '_bold'] = !empty($def['bold']) ? 1 : 0;
                    if (in_array($key, $sigKeys, true)) {
                        $dataJson[$key . '_width'] = (float) ($def['width'] ?? 35);
                        $dataJson[$key . '_height'] = (float) ($def['height'] ?? 15);
                    }
                }
            }
        }
        // เมื่อมี items จากหน้ากำหนดตำแหน่ง จะไม่เติม default ให้ฟิลด์ที่ไม่อยู่ในรายการ (ฟิลด์ที่ลบแล้วจะไม่แสดงใน PDF)

        // ผู้อนุมัติระดับ 3 (ทั้งอนุมัติและไม่อนุมัติ) — ใช้แสดงชื่อ วันที่ และคำสั่ง
        $leader = Approve::find()
            ->where(['name' => 'development', 'from_id' => $model->id, 'level' => 3])
            ->andWhere(['in', 'status', ['Pass', 'Reject', 'Approve']])
            ->orderBy(['updated_at' => SORT_DESC])
            ->one();
        if (!$leader) {
            $leader = Approve::findOne(['name' => 'development', 'from_id' => $model->id, 'level' => 3]);
        }

        // เขียนข้อความลงพิกัด (หน่วย mm). Stored ratio = top-left of text; SetXY uses same (no line-height offset) for UI/PDF parity.
        $writeText = function ($key, $text, $fontSizeDefault = 13, $styleDefault = '') use ($pdf, $dataJson, $offsetX, $offsetY) {
            $positionsByKey = $dataJson['_positionsByKey'] ?? null;
            if (!empty($positionsByKey[$key])) {
                foreach ($positionsByKey[$key] as $pos) {
                    $fontSize = (int) ($pos['fontSize'] ?? $fontSizeDefault);
                    $style = !empty($pos['bold']) ? 'B' : $styleDefault;
                    $pdf->SetFont('THSarabunNew', $style, $fontSize);
                    $x = (float)$pos['x'] + $offsetX;
                    $y = (float)$pos['y'] + $offsetY;
                    $pdf->SetXY($x, $y);
                    $pdf->Write(0, iconv('UTF-8', 'cp874', (string)$text));
                }
                return;
            }
            $xKey = $key . '_x';
            $yKey = $key . '_y';
            if (isset($dataJson[$xKey]) && isset($dataJson[$yKey])) {
                $fontSize = (int) ($dataJson[$key . '_fontSize'] ?? $fontSizeDefault);
                $style = !empty($dataJson[$key . '_bold']) ? 'B' : $styleDefault;
                $pdf->SetFont('THSarabunNew', $style, $fontSize);
                $x = (float)$dataJson[$xKey] + $offsetX;
                $y = (float)$dataJson[$yKey] + $offsetY;
                $pdf->SetXY($x, $y);
                $pdf->Write(0, iconv('UTF-8', 'cp874', (string)$text));
            }
        };

        // --- เริ่มพิมพ์ฟิลด์ต่างๆ ---

        // --- รูปลายเซ็น (จะวาดหลัง writeText ทั้งหมด ด้านล่าง) ---

        // --- ลายเซ็นต์ผู้ปฏิบัติหน้าที่แทน ---
        // try {

        //     $assignedToSig = $model->assignedTo?->SignatureFilePath();
        //     if ($createdSig) {
        //         // พิกัด XY ดึงมาจาก data_json ตามที่คุณทำไว้
        //         $key = 'assigned_to_signature_img';
        //         $x = ((float)$dataJson[$key . '_x'] * $ptToMm) + $offsetX;
        //         $y = ((float)$dataJson[$key . '_y'] * $ptToMm) + $offsetY;

        //         // แทรกรูปลง PDF โดยใช้ Path ตรงๆ (ไม่ต้องผ่าน URL)
        //         // ปรับ $y - 12 เพื่อให้รูปอยู่เหนือชื่อ
        //         $pdf->Image($assignedToSig, $x, $y, 20, 0);
        //     }
        // } catch (\Throwable $th) {

        // }

        // --- ลายเซ็นต์ หัวหน้าเจ้าหน้าที่. ---
        // try {
        //     $leaderSig = SiteHelper::getInfo()['leader_signature_path'];
        //     if ($leaderSig) {
        //         // พิกัด XY ดึงมาจาก data_json ตามที่คุณทำไว้
        //         $key = 'leader_signature_img';
        //         $x = ((float)$dataJson[$key . '_x'] * $ptToMm) + $offsetX;
        //         $y = ((float)$dataJson[$key . '_y'] * $ptToMm) + $offsetY;

        //         // แทรกรูปลง PDF โดยใช้ Path ตรงๆ (ไม่ต้องผ่าน URL)
        //         // ปรับ $y - 12 เพื่อให้รูปอยู่เหนือชื่อ
        //         $pdf->Image($leaderSig, $x, $y, 20, 0);
        //     }
        // } catch (\Throwable $th) {

        // }

        // --- ลายเซ็นต์ ผอ. ---
        // if ($model->status == 'Approve') {

        //     $directorSig = \Yii::$app->site::viewDirector()['signature'];
        //     if ($directorSig) {
        //         // พิกัด XY ดึงมาจาก data_json ตามที่คุณทำไว้
        //         $key = 'director_signature_img';
        //         $x = ((float)$dataJson[$key . '_x'] * $ptToMm) + $offsetX;
        //         $y = ((float)$dataJson[$key . '_y'] * $ptToMm) + $offsetY;

        //         // แทรกรูปลง PDF โดยใช้ Path ตรงๆ (ไม่ต้องผ่าน URL)
        //         // ปรับ $y - 12 เพื่อให้รูปอยู่เหนือชื่อ
        //         $pdf->Image($directorSig, $x, $y, 20, 0);
        //     }
        // }



        // ส่วนราชการ
        $writeText('company_name', $info['company_name'] ?? '-');
        // เลขที่หนังสือ (ที่)
        $writeText('doc_number', $info['doc_number']);
        // วันที่
        $writeText('doc_date', ThaiDateHelper::formatThaiDate(date('Y-m-d'), 'medium'));
        //ด้วยข้าพเจ้า
        $writeText('fullname', $model->createdByEmp?->fullname ?? '-');
        $writeText('position', $model->createdByEmp?->positionName() ?? '-');

        $writeText('topic', $model->topic);
        $writeText('travel_party', $model->data_json['travel_party'] ?? '-');
        $writeText('location', $model->data_json['location'] ?? '-');
        $writeText('date_start',  ThaiDateHelper::formatThaiDate($model->date_start, 'medium'));
        $writeText('date_end',  ThaiDateHelper::formatThaiDate($model->date_end, 'medium'));
        $writeText('vehicle_date_start',  ThaiDateHelper::formatThaiDate($model->vehicle_date_start, 'medium'));
        $writeText('vehicle_time_start',  $model->data_json['vehicle_time_start']);
        $writeText('vehicle_date_end',  ThaiDateHelper::formatThaiDate($model->vehicle_date_end, 'medium'));
        $writeText('vehicle_time_end',  $model->data_json['vehicle_time_end']);
        $writeText('claim_type_name',  $model->data_json['claim_type_name']);
        $writeText('total_days',  $this->getTotalDays($model->date_start, $model->date_end));
        $distanceVal = isset($model->data_json['distance']) && (string) $model->data_json['distance'] !== '' ? ($model->data_json['distance'] . ' กม.') : '-';
        $writeText('distance', $distanceVal);
        $writeText('vehicle_type', ($model->vehicleType?->title ?? '-'));
        $writeText('assigned_to', ($model->assignedTo?->fullname ?? '-'));
        $writeText('assigned_to_position', ($model->assignedTo?->positionName() ?? '-'));
        $writeText('assigned_to_signature', ($model->assignedTo?->fullname ?? '-'));
        $leaderGroupEmp = $model->leader_group_id ? \app\modules\hr\models\Employees::findOne($model->leader_group_id) : null;
        $writeText('leader_group_fullname', $leaderGroupEmp ? ($leaderGroupEmp->fullname() ?? '-') : '-');
        $writeText('leader_group_position', $leaderGroupEmp && method_exists($leaderGroupEmp, 'positionName') ? ($leaderGroupEmp->positionName() ?? '-') : '-');
        $approveStatusText = '-';
        if ($leader) {
            $approveStatusText = ($leader->status === 'Reject') ? 'ไม่อนุมัติ' : (in_array($leader->status, ['Pass', 'Approve'], true) ? 'อนุมัติ' : '-');
        }
        $writeText('approve_status', $approveStatusText);
        $writeText('leader_fullname', $leader && isset($leader->employee) ? ($leader->employee->fullname ?? '-') : '-');
        $writeText('leader_date', $leader && isset($leader->data_json['approve_date']) ? (ThaiDateHelper::formatThaiDate($leader->data_json['approve_date'], 'medium') ?? '-') : '-');

        $writeText('approve_date', (ThaiDateHelper::formatThaiDate($model->approveDate()) ?? '-'));

        // --- รูปลายเซ็น — รองรับฟิลด์เดียวกันหลายจุด (วาดรูปที่ทุกจุด) ---
        $drawSignatureImage = function ($key, $filePath) use ($pdf, $dataJson, $offsetX, $offsetY) {
            if (!$filePath || !is_file($filePath)) {
                return;
            }
            $positionsByKey = $dataJson['_positionsByKey'] ?? null;
            if (!empty($positionsByKey[$key])) {
                foreach ($positionsByKey[$key] as $pos) {
                    $x = (float)$pos['x'] + $offsetX;
                    $y = (float)$pos['y'] + $offsetY;
                    $w = (float)($pos['width'] ?? 35);
                    $h = (float)($pos['height'] ?? 15);
                    try {
                        $pdf->Image($filePath, $x, $y, $w, $h);
                    } catch (\Throwable $e) {
                        // ข้ามถ้าแทรกรูปไม่ได้
                    }
                }
                return;
            }
            $xKey = $key . '_x';
            $yKey = $key . '_y';
            $wKey = $key . '_width';
            $hKey = $key . '_height';
            if (!isset($dataJson[$xKey], $dataJson[$yKey])) {
                return;
            }
            $x = (float)$dataJson[$xKey] + $offsetX;
            $y = (float)$dataJson[$yKey] + $offsetY;
            $w = (float)($dataJson[$wKey] ?? 35);
            $h = (float)($dataJson[$hKey] ?? 15);
            try {
                $pdf->Image($filePath, $x, $y, $w, $h);
            } catch (\Throwable $e) {
                // ข้ามถ้าแทรกรูปไม่ได้
            }
        };
        $drawSignatureImage('requester_signature', $model->createdByEmp?->SignatureFilePath());
        $drawSignatureImage('assigned_to_signature', $model->assignedTo?->SignatureFilePath());
        if ($leaderGroupEmp) {
            $drawSignatureImage('leader_group_signature', $leaderGroupEmp->SignatureFilePath());
        }
        if ($leader && isset($leader->employee)) {
            $drawSignatureImage('signature_approver', $leader->employee->SignatureFilePath());
        }

        // ส่วนคณะเดินทาง: แปลงค่าสัมพัทธ์ (0–1) หรือ legacy mm เป็น mm
        $memberNameMm = PdfCoordinateHelper::normalizedOrMmToMm(
            (float)($dataJson['member_fullname_start_x'] ?? 25 / PdfCoordinateHelper::A4_WIDTH_MM),
            (float)($dataJson['member_fullname_start_y'] ?? 85 / PdfCoordinateHelper::A4_HEIGHT_MM),
            $paperW,
            $paperH
        );
        $memberPosMm = PdfCoordinateHelper::normalizedOrMmToMm(
            (float)($dataJson['member_position_start_x'] ?? 100 / PdfCoordinateHelper::A4_WIDTH_MM),
            (float)($dataJson['member_position_start_y'] ?? 85 / PdfCoordinateHelper::A4_HEIGHT_MM),
            $paperW,
            $paperH
        );
        $startX = $memberNameMm['x'];
        $startY = $memberNameMm['y'];
        $startPositionX = $memberPosMm['x'];
        $startPositionY = $memberPosMm['y'];
        $lineSpacing = (float)($dataJson['line_spacing'] ?? 5.5);

        $members = $model->listMemberForPdf();
        $memberFontSize = (int)($dataJson['member_font_size'] ?? 14);
        $memberStyle = !empty($dataJson['member_bold']) ? 'B' : '';
        $pdf->SetFont('THSarabunNew', $memberStyle, $memberFontSize);
        $index = 0;
        foreach ($members as $memberItem) {
            $emp = $memberItem->emp;
            $name = $emp ? $emp->fullname() : (trim((string)($memberItem->data_json['label'] ?? '')) ?: ((string)$memberItem->emp_id ?: '-'));
            $position = $emp ? $emp->positionName() : ($memberItem->data_json['position_name_text'] ?? '-');

            $x = $startX + $offsetX;
            $y = $startY + $offsetY + ($index * $lineSpacing);
            $xPosition = $startPositionX + $offsetX;
            $yPosition = $startPositionY + $offsetY + ($index * $lineSpacing);

            $pdf->SetXY($x, $y);
            $displayText = ($index + 1) . '. ' . $name;
            $pdf->Write(0, iconv('UTF-8', 'cp874//IGNORE', $displayText));

            $pdf->SetXY($xPosition, $yPosition);
            $pdf->Write(0, iconv('UTF-8', 'cp874//IGNORE', $position));

            $index++;
        }
        // 6. ส่งออกไฟล์
        return $pdf->Output('I', 'Filled_Form_' . $id . '.pdf');
    }



    // ตัวอย่างใน Controller หรือ Model
    public function getTotalDays($startDate, $endDate)
    {
        $s = new \DateTime($startDate);
        $e = new \DateTime($endDate);
        return $s->diff($e)->days + 1;
    }


    // ส่งออกรายการวันลา
    public function actionExportExcel()
    {
        $me = UserHelper::GetEmployee();
        $leaveFilterStatusModel = Categorise::findOne(['name' => 'hr_development_filter_status', 'emp_id' => $me->id]);
        $searchModel = new DevelopmentSearch([
            'q_status' => $leaveFilterStatusModel->data_json ?? [],
        ]);

        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->joinWith([
            'developmentDetail',
            'createdByEmp' => function ($q) {
                $q->alias('created_by_emp');
            }
        ]);
        $dataProvider->query->andFilterWhere(['development.status' => $searchModel->q_status]);
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'topic', $searchModel->q],
            ['like', 'development.emp_id', $searchModel->emp_id],
            ['like', new \yii\db\Expression("JSON_UNQUOTE(JSON_EXTRACT(development.data_json, '$.location'))"), $searchModel->q],
        ]);

        $dataProvider->query->andFilterWhere(['development_detail.emp_id' => $searchModel->emp_id]);


        $dataProvider->query->andFilterWhere(['>=', 'date_start', AppHelper::convertToGregorian($searchModel->date_start)])->andFilterWhere(['<=', 'date_end', AppHelper::convertToGregorian($searchModel->date_end)]);

        $org1 = Organization::findOne($searchModel->q_department);
        // ถ้ามีกลุ่มย่อย
        if (isset($org1) && $org1->lvl == 1) {
            $sql = 'SELECT t1.id, t1.root, t1.lft, t1.rgt, t1.lvl, t1.name, t1.icon
             FROM tree t1
             JOIN tree t2 ON t1.lft BETWEEN t2.lft AND t2.rgt AND t1.lvl = t2.lvl + 1
             WHERE t2.name = :name;';
            $querys = Yii::$app
                ->db
                ->createCommand($sql)
                ->bindValue(':name', $org1->name)
                ->queryAll();
            $arrDepartment = [];
            foreach ($querys as $tree) {
                $arrDepartment[] = $tree['id'];
            }
            if (count($arrDepartment) > 0) {
                $dataProvider->query->andWhere(['in', 'created_by_emp.department', $arrDepartment]);
            } else {
                $dataProvider->query->andFilterWhere(['created_by_emp.department' => $searchModel->q_department]);
            }
        } else {
            $dataProvider->query->andFilterWhere(['created_by_emp.department' => $searchModel->q_department]);
        }


        $dataProvider->query->orderBy(['date_start' => SORT_DESC, 'id' => SORT_DESC]);
        $dataProvider->query->groupBy('development_detail.id');

        $dataProvider->pagination = false;


        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // --- 1. จัดการส่วนหัวรายงาน (A1) ---
        $sheet->mergeCells('A1:M1'); // ขยาย Merge Cells เป็น I1 -> J1 เพื่อให้ครอบคลุมคอลัมน์ "จำนวนวันลา"
        $dateStart = AppHelper::convertToGregorian($searchModel->date_start);
        $dateEnd = AppHelper::convertToGregorian($searchModel->date_end);
        $dateReport = ThaiDateHelper::formatThaiDateRange($dateStart, $dateEnd, 'long', 'short');

        $reportTitle = 'ทะเบียนอบรม/ประชุม/ดูงาน วันที่ ' . $dateReport;
        $sheet->setCellValue('A1', $reportTitle);

        $sheet->getStyle('A1')->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'font' => [
                'name' => 'TH Sarabun New',
                'size' => 16,
                'bold' => true,
            ],
        ]);

        // --- 2. จัดการหัวตาราง (Row 2) โดยใช้ Array Map และ Loop ---

        // กำหนดคอลัมน์, ชื่อหัวตาราง, และความกว้าง
        $headerConfig = [
            'A' => ['title' => 'ลำดับ', 'width' => 6],
            'B' => ['title' => 'เลขบัตรประชาชน', 'width' => 13],
            'C' => ['title' => 'จำนวนวัน', 'width' => 10],
            'D' => ['title' => 'ชื่อ-นามสกุล', 'width' => 30],
            'E' => ['title' => 'หน่วยงานผู้ขอ', 'width' => 30],
            'F' => ['title' => 'หน่วยงานที่จัด', 'width' => 30],
            'G' => ['title' => 'สถานที่จัด', 'width' => 30],
            'H' => ['title' => 'จังหวัด', 'width' => 30],
            'I' => ['title' => 'ตั้งแต่วันที่', 'width' => 20], // เพิ่มใหม่
            'J' => ['title' => 'ถึงวันที่', 'width' => 15],
            'K' => ['title' => 'ประเภทการพัฒนา', 'width' => 35],
            'L' => ['title' => 'หัวข้อการไป', 'width' => 200],
        ];

        // กำหนด Style สำหรับหัวตารางทั้งหมด (เพื่อให้โค้ดสั้นลง)
        $headerStyle = [
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'font' => [
                'name' => 'TH Sarabun New',
                'size' => 16,
                'bold' => true,
                'italic' => false,
            ],
            // เพิ่ม Border ให้ส่วนหัวตาราง เพื่อให้สอดคล้องกับข้อมูลที่คุณทำในลูปก่อนหน้า
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => Color::COLOR_BLACK],
                ],
            ],
            // เพิ่ม Fill สีพื้นหลัง หากต้องการ
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D9E1F2'], // สีพื้นหลังหัวตารางที่แตกต่างจากข้อมูล
            ],
        ];

        // Loop เพื่อใส่ค่าและกำหนดความกว้าง
        foreach ($headerConfig as $col => $config) {
            $cell = $col . '2';
            $sheet->setCellValue($cell, $config['title']);
            $sheet->getColumnDimension($col)->setWidth($config['width']);
        }

        // Apply Style ทีเดียวทั้งช่วง A2:J2
        $sheet->getStyle('A2:L2')->applyFromArray($headerStyle);

        $StartRowSheet = 3;
        $numRow = $StartRowSheet; // เริ่มนับแถวที่ 3

        // --- 1. ใส่ข้อมูล (ให้เร็วที่สุด) ---
        foreach ($dataProvider->getModels() as $key => $item) {
            // ใส่ข้อมูลอย่างเดียว ห้ามจัดรูปแบบในลูปนี้!
            $sheet->setCellValue('A' . $numRow, ($key + 1));
            $sheet->setCellValue('B' . $numRow, $item->createdByEmp->cid);
            $sheet->setCellValue('C' . $numRow, $this->getTotalDays($item->date_start, $item->date_end));
            $sheet->setCellValue('D' . $numRow, $item->createdByEmp->fullname());
            $sheet->setCellValue('E' . $numRow, $item->createdByEmp->departmentName());
            $sheet->setCellValue('F' . $numRow, isset($item->data_json['location_org']) ? $item->data_json['location_org'] : 'ไม่ระบุ');
            $sheet->setCellValue('G' . $numRow, isset($item->data_json['location']) ? $item->data_json['location'] : 'ไม่ระบุ');
            $sheet->setCellValue('H' . $numRow, isset($item->data_json['province_name']) ? $item->data_json['province_name'] : 'ไม่ระบุ');
            $sheet->setCellValue('I' . $numRow, AppHelper::convertToThai($item->date_start));
            $sheet->setCellValue('J' . $numRow, AppHelper::convertToThai($item->date_end));
            $sheet->setCellValue('K' . $numRow, isset($item->data_json['development_type_name']) ? $item->data_json['development_type_name'] : 'ไม่ระบุ');
            $sheet->setCellValue('L' . $numRow, $item->topic);
            $numRow++; // เลื่อนไปแถวถัดไป
        }

        // กำหนดแถวสุดท้ายที่เขียนข้อมูล
        $lastRow = $numRow - 1;
        $dataRange = 'A' . $StartRowSheet . ':M' . $lastRow;

        // --- 2. จัดรูปแบบทีละมากๆ (หลังจบลูป) ---
        if ($lastRow >= $StartRowSheet) {

            // **จัดรูปแบบพื้นฐาน (ฟอนต์, ขอบ, สีพื้นหลัง) ทั้งหมดในครั้งเดียว**
            $sheet->getStyle($dataRange)->applyFromArray([
                'font' => [
                    'name' => 'TH Sarabun New',
                    'size' => 14,
                    'bold' => false,
                    'italic' => false,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => Color::COLOR_BLACK],
                    ],
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '8DB4E2'],
                ],
            ]);

            // **จัดตำแหน่งกึ่งกลาง (Horizontal Center)**
            $centerColumns = ['A', 'C', 'I', 'J', 'K'];
            foreach ($centerColumns as $col) {
                $sheet->getStyle($col . $StartRowSheet . ':' . $col . $lastRow)
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }

            // **จัดตำแหน่งชิดซ้าย (Horizontal Left)**
            $leftColumns = ['B', 'D'];
            foreach ($leftColumns as $col) {
                $sheet->getStyle($col . $StartRowSheet . ':' . $col . $lastRow)
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            }
        }

        $writer = new Xlsx($spreadsheet);

        // 1. กำหนด Alias ไปที่ runtime และสร้างโฟลเดอร์ย่อยถ้าจำเป็น
        $runtimePath = Yii::getAlias('@runtime/export');
        if (!is_dir($runtimePath)) {
            FileHelper::createDirectory($runtimePath, 0775, true);
        }

        // 2. ตั้งชื่อไฟล์ (แนะนำให้เติม timestamp เพื่อป้องกันไฟล์ซ้ำกรณีโหลดพร้อมกันหลายคน)
        $fileName = 'export-development-' . date('Ymd-His') . '.xlsx';
        $filePath = $runtimePath . '/' . $fileName;

        // 3. บันทึกไฟล์ลงใน runtime
        $writer->save($filePath);

        // 4. ตรวจสอบและส่งไฟล์
        if (file_exists($filePath)) {
            // ใช้ส่งไฟล์และตั้งชื่อที่จะให้ User เห็นตอนเซฟ
            return Yii::$app->response->sendFile($filePath, $fileName)
                ->on(\yii\web\Response::EVENT_AFTER_SEND, function ($event) use ($filePath) {
                    // ลบไฟล์ทิ้งทันทีหลังจากส่งเสร็จ เพื่อไม่ให้หนักเครื่อง (Optional)
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                });
        } else {
            throw new \yii\web\NotFoundHttpException('ไม่พบไฟล์ที่ต้องการดาวน์โหลด');
        }
    }


    /**
     * Finds the Development model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Development the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Development::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
