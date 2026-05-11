<?php

namespace app\modules\dms\controllers;

use Yii;
use DateTime;
use yii\web\Response;
use app\models\Uploads;
use yii\web\Controller;
use yii\bootstrap5\Html;
use app\models\Categorise;
use yii\filters\VerbFilter;
use app\components\AppHelper;
use app\components\PdfHelper;
use app\components\SiteHelper;
use app\components\UserHelper;
use app\modules\dms\components\WebhookSender;
use app\components\ThaiDateHelper;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use app\components\DateFilterHelper;
use app\modules\dms\models\Documents;
use app\modules\hr\models\Organization;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use app\modules\dms\models\DocumentSearch;
use PhpOffice\PhpSpreadsheet\Style\Border;
use app\modules\dms\models\DocumentsDetail;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use app\modules\filemanager\components\FileManagerHelper;
use yii\helpers\ArrayHelper;  // ค่าที่นำเข้าจาก component ที่เราเขียนเอง

/**
 * DocumentsController implements the CRUD actions for Documents model.
 */
class DocumentsController extends Controller
{
    private function isDeptHeadOrDeputy(?int $departmentId, ?int $empId): bool
    {
        if (($departmentId ?? 0) <= 0 || ($empId ?? 0) <= 0) {
            return false;
        }

        $org = Organization::findOne((int) $departmentId);
        if (!$org) {
            return false;
        }

        $dataJson = $org->data_json;
        if (is_string($dataJson)) {
            $decoded = json_decode($dataJson, true);
            $dataJson = is_array($decoded) ? $decoded : [];
        }
        if (!is_array($dataJson)) {
            $dataJson = [];
        }

        $leader1 = isset($dataJson['leader1']) && is_numeric($dataJson['leader1']) ? (int) $dataJson['leader1'] : 0;
        $leader2 = isset($dataJson['leader2']) && is_numeric($dataJson['leader2']) ? (int) $dataJson['leader2'] : 0;

        return in_array((int) $empId, [$leader1, $leader2], true);
    }

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
                    ],
                ],
            ]
        );
    }


    public function actionHook()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $docId = 67365;

        $model = Documents::findOne($docId);

        if ($model) {
            // เรียกส่งข้อมูลทีละหน่วยงาน
            $result = WebhookSender::sendToAgencies($model);

            if ($result) {

                return "ส่งข้อมูลหนังสือเลขที่ {$model->doc_number} สำเร็จ";
            } else {
                return "การส่งล้มเหลว ตรวจสอบ Log สำหรับรายละเอียด";
            }
        }

        return "ไม่พบข้อมูลหนังสือ ID: $docId";
    }

    public function actionListTopic()
    {

        $searchModel = new DocumentSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['document_group' => 'receive']);
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'topic', $searchModel->q],
        ]);
        if ($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => '<i class="fa-solid fa-magnifying-glass"></i> ค้นหาชื่อเรื่อง',
                'content' => $this->renderAjax('list_topic', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ]),
            ];
        }
    }


    public function actionListTopicData()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;

        $request = Yii::$app->request;
        $draw = $request->get('draw');
        $start = $request->get('start');
        $length = $request->get('length');
        $searchValue = $request->get('search')['value'];

        $query = Documents::find();

        if (!empty($searchValue)) {
            $query->andFilterWhere(['like', 'topic', $searchValue]);
        }

        $totalCount = $query->count();

        $data = $query
            ->offset($start)
            ->limit($length)
            ->all();

        $result = [];
        foreach ($data as $item) {
            $result[] = [
                'id' => $item->id,
                'topic' => $item->topic,
            ];
        }

        return [
            'draw' => intval($draw),
            'recordsTotal' => $totalCount,
            'recordsFiltered' => $totalCount,
            'data' => $result,
        ];
    }

    /**
     * Lists all Documents models.
     *
     * @return string
     */
    public function actionReceive()
    {
        $searchModel = new DocumentSearch([
            'date_filter' => 'today',
            'document_group' => 'receive',
        ]);

        $dataProvider = $this->listDocument($searchModel->search($this->request->queryParams), $searchModel, 'receive');

        if ($searchModel->date_filter) {
            $range = DateFilterHelper::getRange($searchModel->date_filter);
            $start = AppHelper::convertToGregorian($range[0]);
            $end = AppHelper::convertToGregorian($range[1]);
            $dataProvider->query->andFilterWhere(['between', 'doc_date', $start, $end]);
        }

        return $this->render('receive', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'action' => 'receive',
            'title' => 'หนังสือรับ'
        ]);
    }

    public function actionExport()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $searchModel = new DocumentSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        // ไม่ต้องใส่ pagination
        $dataProvider->pagination = false;

        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'topic', $searchModel->q],
            ['like', 'doc_number', $searchModel->q],
            ['like', 'doc_regis_number', $searchModel->q],
            ['like', new \yii\db\Expression("JSON_UNQUOTE(JSON_EXTRACT(data_json, '$.des'))"), $searchModel->q],
        ]);


        $dataProvider->query->andFilterWhere([
            'between',
            'doc_transactions_date',
            AppHelper::convertToGregorian($searchModel->date_start),
            AppHelper::convertToGregorian($searchModel->date_end)
        ]);

        $dataProvider->setSort(['defaultOrder' => [
            'doc_transactions_date' => SORT_ASC,
            'doc_regis_number' => SORT_ASC,
        ]]);

        switch ($searchModel->document_group) {
            case 'receive':
                $title = 'หนังสือรับ';
                break;
            case 'send':
                $title = 'หนังสือส่ง';
                break;
            case 'appointment':
                $title = 'หนังสือคำสั่ง';
                break;
            case 'announce':
                $title = 'หนังประกาศ/นโยบาย';
                break;
            default:
                $title = '';
                break;
        }

        $this->ExportExcel($dataProvider, $searchModel, $title);
    }

    /**
     * Update/เพิ่ม "ถึงหน่วยงาน" (department tags) สำหรับเอกสารเดิม
     * Route: /dms/documents/update-to-departments?id=XX
     *
     * ใช้ field เดิม `tags_department` (comma-separated ids) โดยอาศัยเมธอด
     * `Documents::UpdateDocumentTags()` เพื่อเขียน documents_detail ตามของเดิม
     */
    public function actionUpdateToDepartments($id)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;

        $emp = UserHelper::GetEmployee();
        $canByRole = Yii::$app->user->can('document');
        $canByOrg = $emp ? $this->isDeptHeadOrDeputy((int) $emp->department, (int) $emp->id) : false;
        if (!$canByRole && !$canByOrg) {
            Yii::$app->response->statusCode = 403;
            return [
                'status' => 'error',
                'message' => 'คุณไม่มีสิทธิ์อัปเดตถึงหน่วยงานเพิ่มเติม',
            ];
        }

        $model = $this->findModel((int) $id);

        $tagsDepartment = $this->request->post('tags_department', '');
        if (is_array($tagsDepartment)) {
            $tagsDepartment = implode(',', $tagsDepartment);
        }

        $model->tags_department = (string) $tagsDepartment;

        try {
            $model->UpdateDocumentTags();
        } catch (\Throwable $e) {
            return [
                'status' => 'error',
                'message' => 'ไม่สามารถอัปเดตถึงหน่วยงานได้',
            ];
        }

        // สถานะ DS2 เมื่อมีการส่งต่อหน่วยงาน (อิง logic เดิมใน actionUpdate)
        if ($model->status !== 'DS3' && $model->status !== 'DS4' && $model->tags_department !== '') {
            $model->status = 'DS2';
            $model->save(false);
        }

        return [
            'status' => 'success',
            'html' => $model->viewTagsDepartment(),
        ];
    }

    /**
     * ลบรายการส่งต่อ (documents_detail) เฉพาะของที่ตัวเองสร้าง
     */
    public function actionDeleteForwarding($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $detail = DocumentsDetail::findOne($id);
        if (!$detail) {
            Yii::$app->response->statusCode = 404;
            return ['status' => 'error', 'message' => 'ไม่พบรายการ'];
        }
        if ((int) $detail->created_by !== (int) Yii::$app->user->id) {
            Yii::$app->response->statusCode = 403;
            return ['status' => 'error', 'message' => 'ลบได้เฉพาะรายการที่ตัวเอง tag ไปเท่านั้น'];
        }
        $detail->delete();
        return ['status' => 'success'];
    }

    /**
     * แสดง partial ของ "การ์ดไทม์ไลน์เอกสาร" + สรุป "ส่งถึง" สำหรับเรียก AJAX มา refresh
     * คืน JSON: { card: html, summary: html }
     */
    public function actionForwardingCard($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel((int) $id);
        $emp = UserHelper::GetEmployee();
        $isDeptHeadOrDeputy = $emp ? $this->isDeptHeadOrDeputy((int) $emp->department, (int) $emp->id) : false;
        $canManageDepartmentExtra = Yii::$app->user->can('document') || $isDeptHeadOrDeputy;
        $currentDeptIds = DocumentsDetail::find()
            ->where(['document_id' => $model->id, 'name' => 'department'])
            ->select('to_id')
            ->column();
        $currentDeptIdsStr = array_map('strval', $currentDeptIds ?: []);

        $forwardedDeptIds = DocumentsDetail::find()
            ->select('to_id')
            ->where(['document_id' => $model->id])
            ->andWhere(['in', 'name', ['department', 'comment_dept']])
            ->andWhere(['not', ['to_id' => null]])
            ->andWhere(['<>', 'to_id', ''])
            ->distinct()
            ->column();
        $forwardedDepts = [];
        if (!empty($forwardedDeptIds)) {
            $forwardedDepts = Organization::find()
                ->where(['id' => $forwardedDeptIds])
                ->orderBy(['root' => SORT_ASC, 'lft' => SORT_ASC])
                ->all();
        }

        return [
            'card' => $this->renderPartial('_forwarding_card', [
                'model' => $model,
                'canManageDepartmentExtra' => $canManageDepartmentExtra,
                'currentDeptIdsStr' => $currentDeptIdsStr,
            ]),
            'summary' => $this->renderPartial('_forwarded_summary', [
                'forwardedDepts' => $forwardedDepts,
            ]),
        ];
    }

    /**
     * แก้ไขรายการส่งต่อ (เปลี่ยน to_id หรือ comment) เฉพาะของที่ตัวเองสร้าง
     * รับ POST: to_id, data_json[comment]
     */
    public function actionUpdateForwarding($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $detail = DocumentsDetail::findOne($id);
        if (!$detail) {
            Yii::$app->response->statusCode = 404;
            return ['status' => 'error', 'message' => 'ไม่พบรายการ'];
        }
        if ((int) $detail->created_by !== (int) Yii::$app->user->id) {
            Yii::$app->response->statusCode = 403;
            return ['status' => 'error', 'message' => 'แก้ไขได้เฉพาะรายการที่ตัวเอง tag ไปเท่านั้น'];
        }

        $toId = $this->request->post('to_id');
        $comment = $this->request->post('comment');
        if ($toId !== null && $toId !== '') {
            $detail->to_id = (string) $toId;
        }
        if ($comment !== null) {
            $dataJson = is_array($detail->data_json) ? $detail->data_json : [];
            $dataJson['comment'] = $comment;
            $detail->data_json = $dataJson;
        }
        if ($detail->save()) {
            return ['status' => 'success'];
        }
        Yii::$app->response->statusCode = 422;
        return ['status' => 'error', 'errors' => $detail->getErrors()];
    }

    protected function ExportExcel($dataProvider, $searchModel, $title)
    {
        // ดึงข้อมูลทั้งหมดจาก dataProvider
        $models = $dataProvider->getModels();
        //วันที่ข้อมูลรายงาน
        $dateStart = AppHelper::convertToGregorian($searchModel->date_start);
        $dateEnd = AppHelper::convertToGregorian($searchModel->date_end);
        $dateReport = ThaiDateHelper::formatThaiDateRange($dateStart, $dateEnd, 'long', 'short');

        // ถ้าไม่มีข้อมูล
        if (empty($models)) {
            Yii::$app->session->setFlash('error', 'ไม่พบข้อมูลสำหรับส่งออก');
            return $this->redirect([$searchModel->document_group]);
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->mergeCells('A1:D1');

        $rowTitle = 'A1';
        $dateStart = AppHelper::convertToGregorian($searchModel->date_start);
        $dateEnd = AppHelper::convertToGregorian($searchModel->date_end);
        $dateReport = ThaiDateHelper::formatThaiDateRange($dateStart, $dateEnd, 'long', 'short');

        $sheet->setCellValue($rowTitle, 'ทะเบียน' . $title . ' ปีงบประมาณ ' . $searchModel->thai_year . ' วันที่ ' . $dateReport);
        $sheet->getStyle($rowTitle)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($rowTitle)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($rowTitle)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);

        $sheet->setCellValue('A2', 'เลขหนังสือ');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A2')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A2')->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);
        $sheet->getColumnDimension('A')->setWidth(20);

        $sheet->setCellValue('B2', 'วันที่รับ');
        $sheet->getStyle('B2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B2')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('B2')->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);
        $sheet->getColumnDimension('B')->setWidth(20);

        $sheet->setCellValue('C2', 'เลขรับ');
        $sheet->getStyle('C2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C2')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('C2')->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);
        $sheet->getColumnDimension('C')->setWidth(20);

        $sheet->setCellValue('D2', 'ชื่อเรื่อง');
        $sheet->getStyle('D2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D2')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('D2')->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);
        $sheet->getColumnDimension('D')->setWidth(100);

        $sheet->setTitle('ทะเบียนหนังสือรับ');

        $StartRowSheet = 3;
        foreach ($models as $item) {
            $numRow = $StartRowSheet++;
            $sheet->setCellValue('A' . $numRow, $item->doc_number);
            $sheet->getStyle('A' . $numRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('A' . $numRow)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(false)->setItalic(false);
            $sheet->getStyle('A' . $numRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('A' . $numRow)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
            $sheet->getStyle('A' . $numRow)->getFill()->getStartColor()->setRGB('8DB4E2');

            $sheet->setCellValue('B' . $numRow, ThaiDateHelper::formatThaiDate($item->doc_transactions_date));
            $sheet->getStyle('B' . $numRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . $numRow)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(false)->setItalic(false);
            $sheet->getStyle('B' . $numRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('B' . $numRow)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
            $sheet->getStyle('B' . $numRow)->getFill()->getStartColor()->setRGB('8DB4E2');

            $sheet->setCellValue('C' . $numRow, $item->doc_regis_number);
            $sheet->getStyle('C' . $numRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . $numRow)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(false)->setItalic(false);
            $sheet->getStyle('C' . $numRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('C' . $numRow)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
            $sheet->getStyle('C' . $numRow)->getFill()->getStartColor()->setRGB('8DB4E2');

            $sheet->setCellValue('D' . $numRow, $item->topic);
            $sheet->getStyle('D' . $numRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('D' . $numRow)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(false)->setItalic(false);
            $sheet->getStyle('D' . $numRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('D' . $numRow)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
            $sheet->getStyle('D' . $numRow)->getFill()->getStartColor()->setRGB('8DB4E2');
        }

        $writer = new Xlsx($spreadsheet);
        $filePath = Yii::getAlias('@webroot') . '/downloads/report-document.xlsx';
        $writer->save($filePath);
        if (file_exists($filePath)) {
            return Yii::$app->response->sendFile($filePath);
        } else {
            throw new \yii\web\NotFoundHttpException('The file does not exist.');
        }
    }


    public function actionSend()
    {
        $searchModel = new DocumentSearch([
            'date_filter' => 'today',
            'document_group' => 'send',
        ]);

        $dataProvider = $this->listDocument($searchModel->search($this->request->queryParams), $searchModel, 'send');


        return $this->render('send', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionAppointment()
    {
        $searchModel = new DocumentSearch([
            'date_filter' => 'today',
            'thai_year' => AppHelper::YearBudget(),
            'document_group' => 'appointment',
            'document_type' => 'DT9',
        ]);

        $dataProvider = $this->listDocument($searchModel->search($this->request->queryParams), $searchModel, 'appointment');
        return $this->render('appointment', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionAnnounce()
    {
        $searchModel = new DocumentSearch([
            'date_filter' => 'today',
            'thai_year' => AppHelper::YearBudget(),
            'document_group' => 'announce',
            'document_type' => 'DT5',
        ]);
        $dataProvider = $this->listDocument($searchModel->search($this->request->queryParams), $searchModel, 'announce');
        return $this->render('announce', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * กำหนดการค้นหาและเรียงลำดับข้อมูลเอกสาร
     * @param \yii\data\ActiveDataProvider $dataProvider
     * @param DocumentSearch $searchModel
     * @param string|null $group
     * @return \yii\data\ActiveDataProvider
     */
    private function listDocument($dataProvider, $searchModel, $group = null)
    {
        if ($group) {
            $dataProvider->query->andFilterWhere(['document_group' => $group]);
        }

        $q = trim($searchModel->q ?? '');
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'topic', $q],
            ['like', 'doc_regis_number', $q],  // Fixed typo here
            ['like', 'doc_number', $q],
            ['like', new \yii\db\Expression("JSON_UNQUOTE(JSON_EXTRACT(documents.data_json, '$.des'))"), $q],
        ]);
        if ($searchModel->date_filter) {
            $range = DateFilterHelper::getRange($searchModel->date_filter);
            $searchModel->date_start = AppHelper::convertToThai($range[0]);
            $searchModel->date_end = AppHelper::convertToThai($range[1]);
        }

        $dataProvider->query->andFilterWhere([
            'between',
            'doc_transactions_date',
            AppHelper::convertToGregorian($searchModel->date_start),
            AppHelper::convertToGregorian($searchModel->date_end)
        ]);

        if ($searchModel->q_department) {
            $dataProvider->query->andFilterWhere(['like', 'tags_department', $searchModel->q_department]);
        }

        $dataProvider->setSort(['defaultOrder' => [
            'doc_transactions_date' => SORT_DESC,
            'doc_regis_number' => SORT_DESC,
        ]]);



        return $dataProvider;
    }




    /**
     * Displays a single Documents model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        // $this->layout = '@app/views/layouts/document';
        $this->layout = '@app/themes/v3/layouts/theme-v/document_layout';
        $model = $this->findModel($id);
        if ($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->renderAjax('view_title', ['model' => $model]),
                'content' => $this->renderAjax('view', [
                    'model' => $model,
                ])
            ];
        } else {
            return $this->render('view', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Creates a new Documents model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        // ถ้าเป็นหนังสทือราชการถ้ปีปัจจบัน
        $document_type =  $this->request->get('document_type');
        $model = new Documents([
            'thai_year' => (Date('Y') + 543),
            'document_group' => $this->request->get('document_group'),
            'document_type' => $document_type,
            'document_org' => $this->request->get('document_org'),
            'doc_number' => $this->request->get('doc_number'),
            'doc_speed' => $this->request->get('doc_speed'),
            'doc_date' => AppHelper::convertToThai($this->request->get('doc_date')) ??  '',
            'secret' => $this->request->get('secret'),
            'document_org' => $this->request->get('document_org'),
            'topic' => $this->request->get('topic'),
            'data_json' => [
                'request_id' => $this->request->get('request_id'),
                'file_name' => $this->request->get('file_name'),
                'hoscode' => $this->request->get('hoscode'),
                'hosname' => $this->request->get('hosname'),
            ]
        ]);
        //set Default
        $model->document_type = $document_type ? $document_type : 'DT1';
        $model->doc_speed = 'ปกติ';
        $model->secret = 'ปกติ';
        $model->doc_transactions_date = AppHelper::convertToThai(date('Y-m-d'));
        $dateTime = new DateTime();
        $time = $dateTime->format('H:i');
        $model->doc_time = $time;

        $model->doc_regis_number = $model->runNumber();
        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                \Yii::$app->response->format = Response::FORMAT_JSON;

                //กำหนดสถานะครั้งแรก
                if ($model->tags_department == "") {
                    $model->status = 'DS1';
                } else {
                    $model->status =  "DS2";
                }

                try {
                    $model->doc_date = AppHelper::convertToGregorian($model->doc_date);
                    $model->doc_transactions_date = AppHelper::convertToGregorian($model->doc_transactions_date);
                    if ($model->doc_expire !== '') {
                        $model->doc_expire = AppHelper::convertToGregorian($model->doc_expire);
                    } else {
                        $model->doc_expire = '';
                    }
                } catch (\Throwable $th) {
                    // throw $th;
                }

                $this->UpdateDocOrg($model);

                if ($model->save(false)) {

                    //เก็บคำที่ใช้ประจำ
                    $this->UpdateKeyWord($model->topic);
                    try {
                        //เก็บคำที่ใช้ประจำ
                        $this->UpdateKeyWord($model->data_json['des']);
                    } catch (\Throwable $th) {
                        //throw $th;
                    }

                    try {
                        if ($this->request->get('doc_number')) {
                            $this->moveFile($model);
                        }
                    } catch (\Throwable $th) {
                        //throw $th;
                    }

                    $model->UpdateDocumentTags();

                    //ถ้าเป็นหนังสือรับต้องประทับตรา
                    if ($model->document_group == "receive") {
                        PdfHelper::Stamp($model);

                        $requestId = $model->data_json['request_id'] ?? null;
                        $tempFile = $model->data_json['temp_path'] ?? null;

                        if ($requestId) {
                            WebhookSender::clearWebhookTempData($requestId);
                        }
                    }
                    // ถ้าเป็นการส่งหนังสือภายนอกให้ส่ง webhook
                    if ($model->document_group == "send") {
                        WebhookSender::sendToAgencies($model);
                    }
                } else {
                    return $model->getErrors();
                }
                // return $this->redirect(['/dms/documents/' . $model->document_group]);
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'status' => 'success'
                ];
            }
        } else {
            // $model->loadDefaultValues();

            $model->ref = substr(Yii::$app->getSecurity()->generateRandomString(), 10);
        }


        if ($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('create', [
                    'model' => $model,
                ])
            ];
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Documents model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $model->tags_department = implode(',',
            DocumentsDetail::find()
                ->select('to_id')
                ->where([
                    'document_id' => $model->id,
                    'name' => 'department'
                ])
                ->column()
        );

        $old_json = $model->data_json;
        try {
            $model->doc_expire = AppHelper::convertToThai($model->doc_expire);
            $model->doc_date = AppHelper::convertToThai($model->doc_date);
            $model->doc_transactions_date = AppHelper::convertToThai($model->doc_transactions_date);
        } catch (\Throwable $th) {
            // throw $th;
        }

        if ($this->request->isPost && $model->load($this->request->post())) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            try {
                $model->doc_date = AppHelper::convertToGregorian($model->doc_date);
                $model->doc_transactions_date = AppHelper::convertToGregorian($model->doc_transactions_date);
                if ($model->doc_expire !== '') {
                    $model->doc_expire = AppHelper::convertToGregorian($model->doc_expire);
                } else {
                    $model->doc_expire = '';
                }
            } catch (\Throwable $th) {
                // throw $th;
            }

            // if (!is_numeric($model->document_org)) {
            //     $model->document_org = $this->UpdateDocOrg($model);
            // }

            //ถ้ามีการแก้ไขส่งต่อหน่วยงาน
            if ($model->status !== "DS3" && $model->status !== "DS4" && $model->tags_department !== "") {
                $model->status =  "DS2";
            }

            $model->data_json = ArrayHelper::merge($old_json, $model->data_json);
            if ($model->save()) {
                //เก็บคำที่ใช้ประจำ
                $this->UpdateKeyWord($model->topic);
                try {
                    //เก็บคำที่ใช้ประจำ
                    $this->UpdateKeyWord($model->data_json['des']);
                } catch (\Throwable $th) {
                    //throw $th;
                }
                $model->UpdateDocumentTags();
                //ถ้าเป็นหนังสือส่ง
                if ($model->document_group == "send") {
                    WebhookSender::sendToAgencies($model);
                }

                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'status' => 'success',
                    'container' => '#document', // <-- แก้ให้ถูกต้อง
                ];
            } else {
                return $model->getErrors();
            }
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('update', [
                    'model' => $model,
                ])
            ];
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    //เก็บคำที่ใช้ประจำ
    public function UpdateKeyWord($keyword)
    {
        $variable =  explode(' ', $keyword);
        \Yii::$app->response->format = Response::FORMAT_JSON;
        foreach ($variable as $key => $value) {
            $check = Categorise::find()->where(['title' => $value])->one();
            if (!$check && $value !== "") {
                $newKeyword = new Categorise;
                $newKeyword->title = $value;
                $newKeyword->name = 'document_keyword';
                $newKeyword->save();
            }
        }
    }

    //ดึงข้อมูล keyword
    public function actionGetKeyword()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $query = Yii::$app->request->get('query', '');
        $trigger = Yii::$app->request->get('trigger', '');

        $keywords = Categorise::find()
            ->where(['name' => 'document_keyword'])
            ->andWhere(['like', 'title', $query])
            ->limit(10)
            ->all();

        $result = [];
        foreach ($keywords as $item) {
            $result[] = [
                'value' => $item->title,
                // 'label' => $item->title . ' (@' . $item->title . ')',
                // 'description' => $item->title
            ];
        }

        return [
            'success' => true,
            'data' => $result
        ];

        return $this->render('index');
    }

    //ย้าไฟล์จากหนังสือรอรับเข้าระบบ
    public function moveFile($model)
    {
        $filename  = $model->data_json['file_name'];
        $newUpload = new Uploads();
        $newUpload->ref = $model->ref;
        $newUpload->name = 'document';
        $newUpload->type = 'pdf';
        $newUpload->filename = '';
        $newUpload->file_name = $filename;
        $newUpload->real_filename = $filename;
        $newUpload->save(false);
        FileManagerHelper::CreateDir($model->ref);

        $sourcePath = Yii::getAlias('@runtime/webhooks/files/') . $filename;
        $targetDir = Yii::getAlias('@app/modules/filemanager/fileupload/' . $model->ref . '/');
        $targetPath = $targetDir . $filename;

        // ตรวจสอบว่าปลายทางมีไดเรกทอรีหรือยัง ถ้ายังไม่มีให้สร้าง
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0775, true);
        }

        // ย้ายไฟล์
        if (file_exists($sourcePath)) {
            // แปลง PDF ด้วย Ghostscript ก่อนย้าย
            $convertedPath = $targetDir . 'converted_' . $filename;
            $gsCmd = "gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dNOPAUSE -dBATCH -sOutputFile=" . escapeshellarg($convertedPath) . " " . escapeshellarg($sourcePath);
            exec($gsCmd, $output, $returnVar);

            if ($returnVar === 0 && file_exists($convertedPath)) {
                // ลบไฟล์ต้นฉบับหลังแปลงสำเร็จ
                unlink($sourcePath);
                // เปลี่ยนชื่อไฟล์ที่แปลงแล้วเป็นชื่อเดิม
                rename($convertedPath, $targetPath);

                // ลบ json object ที่ doc_number = $model->doc_number ในไฟล์ @app/doc_receive/data.json
                $jsonFile = Yii::getAlias('@app/doc_receive/data.json');
                $doc_number = $model->doc_number;
                if (file_exists($jsonFile) && is_writable($jsonFile)) {
                    $jsonData = file_get_contents($jsonFile);
                    $dataArr = json_decode($jsonData, true);
                    if (is_array($dataArr)) {
                        // ค้นหาและลบ object ที่ doc_number ตรงกับ $model->doc_number
                        $dataArr = array_values(array_filter($dataArr, function ($item) use ($doc_number) {
                            return !(isset($item['doc_number']) && $item['doc_number'] == $doc_number);
                        }));
                        // เขียนไฟล์แบบ atomic เพื่อป้องกัน permission issue
                        $tmpFile = $jsonFile . '.tmp';
                        file_put_contents($tmpFile, json_encode($dataArr, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
                        rename($tmpFile, $jsonFile);
                    }
                }
            } else {
                // ถ้าแปลงไม่สำเร็จ ให้ย้ายไฟล์ต้นฉบับตามปกติ
                rename($sourcePath, $targetPath);
            }
        }
    }

    public function actionTest()
    {
        $name = 'ให้ข้าราชการปฏิบัติราชการ';
        $jsonFile = Yii::getAlias('@app/doc_receive/data.json');
        if (file_exists($jsonFile)) {
            $jsonData = file_get_contents($jsonFile);
            $dataArr = json_decode($jsonData, true);
            if (is_array($dataArr)) {
                // ค้นหาและลบ object ที่ topic ตรงกับ $model->topic
                $dataArr = array_values(array_filter($dataArr, function ($item) use ($name) {
                    return isset($item['topic']) && $item['topic'] !== $name;
                }));
                file_put_contents($jsonFile, json_encode($dataArr, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            }
        }
    }


    /**
     * ปรับ document_org ให้เป็น string (code หรือ comma-separated codes)
     * รองรับทั้งค่าเดียว (string) และหลายหน่วยงาน (array จาก Select2 multiple)
     */
    protected function UpdateDocOrg($model)
    {
        $raw = $model->document_org;
        if (is_array($raw)) {
            $codes = [];
            foreach ($raw as $item) {
                $item = trim((string) $item);
                if ($item === '') {
                    continue;
                }
                $codes[] = is_numeric($item) ? $item : $this->getOrCreateDocOrgCode($item);
            }
            $model->document_org = implode(',', $codes);
            return;
        }

        $title = trim((string) $raw);
        if ($title === '') {
            return;
        }
        if (is_numeric($title)) {
            return;
        }

        $check = Categorise::find()->where(['name' => 'document_org', 'title' => $title])->one();
        if ($check) {
            $model->document_org = (string) $check->code;
            return;
        }

        $maxCode = Categorise::find()->select(['code' => new \yii\db\Expression('MAX(CAST(code AS UNSIGNED))')])->where(['like', 'name', 'document_org'])->scalar();
        $newModel = new Categorise();
        $newModel->code = (int) $maxCode + 1;
        $newModel->name = 'document_org';
        $newModel->title = $title;
        $newModel->save(false);
        $model->document_org = (string) $newModel->code;
    }

    /** หา code จาก title หรือสร้างรายการใหม่ใน categorise แล้วคืน code */
    protected function getOrCreateDocOrgCode($title)
    {
        $title = trim((string) $title);
        if ($title === '' || is_numeric($title)) {
            return $title;
        }
        $check = Categorise::find()->where(['name' => 'document_org', 'title' => $title])->one();
        if ($check) {
            return (string) $check->code;
        }
        $maxCode = Categorise::find()->select(['code' => new \yii\db\Expression('MAX(CAST(code AS UNSIGNED))')])->where(['like', 'name', 'document_org'])->scalar();
        $newModel = new Categorise();
        $newModel->code = (int) $maxCode + 1;
        $newModel->name = 'document_org';
        $newModel->title = $title;
        $newModel->save(false);
        return (string) $newModel->code;
    }

    // ตรวจสอบความถูกต้อง
    public function actionValidator()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new Documents();
        $requiredName = 'ต้องระบุ';
        if ($this->request->isPost && $model->load($this->request->post())) {
            if (isset($model->doc_date)) {
                preg_replace('/\D/', '', $model->doc_date) == '' ? $model->addError('doc_date', $requiredName) : null;
            }
            if (isset($model->doc_transactions_date)) {
                preg_replace('/\D/', '', $model->doc_transactions_date) == '' ? $model->addError('doc_transactions_date', $requiredName) : null;
            }

            // $docRegisNumber = Documents::find()->where(['document_group' => $model->document_group,'doc_regis_number' => $model->doc_regis_number,'thai_year' => $model->thai_year])->one();
            // if($docRegisNumber){
            //     if($docRegisNumber->id !== $model->id){
            //         $model->addError('doc_regis_number', 'เลขทะเบียนซ้ำ');
            //     }

            // }

            // $docNumber = Documents::find()->where(['document_group' => $model->document_group,'doc_number' => $model->doc_number,'thai_year' => $model->thai_year])->one();
            // if($docNumber){
            //     $model->addError('doc_number', 'เลขทะเบียนซ้ำ');
            // }

            //  $model->data_json['reason'] == '' ? $model->addError('data_json[reason]', $requiredName) : null;
        }
        foreach ($model->getErrors() as $attribute => $errors) {
            $result[Html::getInputId($model, $attribute)] = $errors;
        }
        if (!empty($result)) {
            return $this->asJson($result);
        }
    }

    // ตรวจสอบความถูกต้องของ comment
    public function actionCommentValidator()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new DocumentsDetail();
        $requiredName = 'ต้องระบุ';
        if ($this->request->isPost && $model->load($this->request->post())) {
            $model->data_json['comment'] == '' ? $model->addError('data_json[comment]', $requiredName) : null;
        }
        foreach ($model->getErrors() as $attribute => $errors) {
            $result[Html::getInputId($model, $attribute)] = $errors;
        }
        if (!empty($result)) {
            return $this->asJson($result);
        }
    }

    public function actionReqApprove()
    {
        if ($this->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $info = SiteHelper::getInfo();
            $directorId = (int) ($info['director_name'] ?? 0);
            $documentId = (int) $this->request->post('document_id');

            if ($directorId <= 0 || $documentId <= 0) {
                Yii::$app->response->statusCode = 422;
                return [
                    'status' => 'error',
                    'container' => '#document-tag',
                    'message' => 'ข้อมูลไม่ครบถ้วน',
                ];
            }

            $exists = DocumentsDetail::findOne([
                'document_id' => $documentId,
                'name' => 'req_approve',
                'to_id' => (string) $directorId,
            ]);
            if ($exists) {
                return [
                    'status' => 'error',
                    'container' => '#document-tag',
                ];
            }

            $model = new DocumentsDetail();
            $model->document_id = $documentId;
            $model->ref = $this->request->post('ref');
            $model->name = 'req_approve';
            $model->to_id = (string) $directorId;
            $model->to_type = 'employee';
            $model->data_json = ['req_approve_date' => date('Y-m-d H:i:s')];

            $document = Documents::findOne($documentId);
            if ($document) {
                $document->status = 'DS3';
                $document->save(false);
            }

            if ($model->save()) {
                return [
                    'title' => $this->request->get('title'),
                    'status' => 'success',
                    'container' => '#document-tag',
                ];
            }
        }
    }

    // แสดง File และแสดงความเห็น
    public function actionComment($id)
    {
        $emp = UserHelper::GetEmployee();
        $model = new DocumentsDetail([
            'document_id' => $id,
            'to_id' => $emp->id,
            'name' => 'comment'
        ]);

        if ($this->request->isPost && $model->load($this->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            try {
                //## ตรวจสอบสถานะส่งเสนอ ผอ.
                $director = SiteHelper::getInfo()['director']->id;
                //ตรวจว่ามีการ Tags ถึง ผอฬหรือไม่
                if (in_array($director, $model->tags_employee)) {
                    $docStatus =  $model->document;
                    $docStatus->status = 'DS3';
                    $docStatus->save(false);
                }
            } catch (\Throwable $th) {
                //throw $th;
            }

            if ($model->save()) {
                // บันทึก tag ไปยัง document
                $model->UpdateDocumentsDetail();

                return [
                    'status' => 'success'
                ];
                // ส่งข้อมูลกลับไปยังหน้า view เพื่อให้เห็นว่ามีการ comment เข้ามา'
                // return $this->redirect(['view', 'id' => $model->document_id]);
            }
        }
        if ($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('_form_comment', [
                    'model' => $model,
                ])
            ];
        } else {
            return $this->render('_form_comment', [
                'model' => $model,
            ]);
        }
    }

    public function actionUpdateComment($id)
    {
        $emp = UserHelper::GetEmployee();
        $model = DocumentsDetail::findOne($id);

        $tags = DocumentsDetail::find()->where(['name' => 'comment', 'document_id' => $model->document_id])->all();
        $list = ArrayHelper::map($tags, 'tag_id', 'tag_id');

        if ($this->request->isPost && $model->load($this->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($model->save()) {


                $model->UpdateDocumentsDetail();
                return [
                    'status' => 'success'
                ];
                // return [
                //     'status' => 'success',
                //     'data' => $model,
                // ];
            }
        }
        if ($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => 'xxx',
                'content' => $this->renderAjax('_form_comment', [
                    'model' => $model,
                ])
            ];
        } else {
            return $this->render('_form_comment', [
                'model' => $model,
            ]);
        }
    }

    public function actionDeleteComment($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = DocumentsDetail::findOne($id);
        if ($model->created_by == Yii::$app->user->id) {
            $model->delete();
            return [
                'status' => 'success',
                'data' => $model,
            ];
        } else {
            return [
                'status' => 'error',
            ];
        }
    }

    // แสดง File และแสดงความเห็น
    public function actionListComment($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => '<i class="fa-regular fa-comments fs-2"></i> การลงความเห็น',
                'content' => $this->renderAjax('list_comment', [
                    'model' => $model,
                ])
            ];
        } else {
            return $this->render('list_comment', [
                'model' => $model,
            ]);
        }
    }

    // แสดง File และแสดงความเห็น
    public function actionClipFile($id)
    {
        $model = $this->findModel($id);
        if ($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => '<i class="fas fa-share"></i> ส่งต่อ',
                'content' => $this->renderAjax('share_file', [
                    'model' => $model,
                ])
            ];
        } else {
            return $this->render('share_file', [
                'model' => $model,
            ]);
        }
    }

    public function actionShow($ref)
    {
        // $model = $this->findModel($id);
        if (!Yii::$app->user->isGuest) {
            $id = Yii::$app->request->get('id');
            $file_name = Yii::$app->request->get('file_name');
            $download = Yii::$app->request->get('download');
            $fileUpload = Uploads::findOne(['ref' => $ref]);
            $type = 'pdf';

            if ($file_name) {
                $filepath = Yii::getAlias('@runtime/webhooks/files/') . $file_name;
            } else if (!$fileUpload) {
                $filepath = Yii::getAlias('@webroot') . '/images/pdf-placeholder.pdf';
            } else {
                $filename = $fileUpload->real_filename;
                $filepath = FileManagerHelper::getUploadPath() . $fileUpload->ref . '/' . $filename;
            }


            if ((string) $download === '1') {
                return Yii::$app->response->sendFile($filepath, basename($filepath));
            }

            return Yii::$app->response->sendFile($filepath, basename($filepath), ['inline' => true]);
        } else {
            return false;
        }
    }

    protected function setHttpHeaders($type)
    {
        \Yii::$app->response->format = yii\web\Response::FORMAT_RAW;
        if ($type == 'png') {
            \Yii::$app->response->headers->add('content-type', 'image/png');
        }

        if ($type == 'pdf') {
            \Yii::$app->response->headers->add('content-type', 'application/pdf');
        }
    }

    public function actionUploadFile($ref)
    {
        if ($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => '<i class="fas fa-share"></i> อัพโหลดไฟล์',
                'content' => $this->renderAjax('_upload_file', [
                    'ref' => $ref,
                ])
            ];
        } else {
            return $this->render('_upload_file', [
                'ref' => $ref,
            ]);
        }
    }

    /**
     * Deletes an existing Documents model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $ref = $model->ref;
        if ($model->delete()) {
            FileManagerHelper::removeUploadDir($ref);
        }

        return $this->redirect([$model->document_group]);
    }


    /**
     * Finds the Documents model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Documents the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Documents::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionTags()
    {
        return $this->render('tags');
    }
    public function actionInfo()
    {
        return $this->render('info');
    }
}
