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
use app\components\ThaiDateHelper;
use yii\web\NotFoundHttpException;
use app\components\DateFilterHelper;
use app\modules\dms\models\Documents;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use app\modules\dms\models\DocumentSearch;
use app\modules\dms\models\DocumentsDetail;
use app\modules\filemanager\components\FileManagerHelper;
use yii\helpers\ArrayHelper;  // ค่าที่นำเข้าจาก component ที่เราเขียนเอง

/**
 * DocumentsController implements the CRUD actions for Documents model.
 */
class DocumentsController extends Controller
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
                    ],
                ],
            ]
        );
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
            'thai_year' => AppHelper::YearBudget(),
            'document_group' => 'receive',
        ]);

        $dataProvider = $this->listDocument($searchModel->search($this->request->queryParams), $searchModel, 'receive');

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
            ['like', 'doc_regis_number', $searchModel->q],
            ['like', new \yii\db\Expression("JSON_UNQUOTE(JSON_EXTRACT(data_json, '$.des'))"), $searchModel->q],
        ]);

        if ($searchModel->date_filter) {
            $range = DateFilterHelper::getRange($searchModel->date_filter);
            $searchModel->date_start = AppHelper::convertToThai($range[0]);
            $searchModel->date_end = AppHelper::convertToThai($range[1]);
        }

        if ($searchModel->thai_year !== '' && $searchModel->date_filter == '') {
            $searchModel->date_start = AppHelper::convertToThai(($searchModel->thai_year - 544) . '-10-01');
            $searchModel->date_end = AppHelper::convertToThai(($searchModel->thai_year - 543) . '-09-30');
        }
        $dataProvider->query->andFilterWhere([
            'between',
            'doc_transactions_date',
            AppHelper::convertToGregorian($searchModel->date_start),
            AppHelper::convertToGregorian($searchModel->date_end)
        ]);

        $dataProvider->setSort(['defaultOrder' => [
            'doc_regis_number' => SORT_DESC,
            'thai_year' => SORT_DESC,
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

        $this->ExportExcel($dataProvider, $searchModel,$title);
    }
    protected function ExportExcel($dataProvider, $searchModel,$title)
    {
        // ดึงข้อมูลทั้งหมดจาก dataProvider
        $models = $dataProvider->getModels();
          //วันที่ข้อมูลรายงาน
            $dateStart= AppHelper::convertToGregorian($searchModel->date_start);
            $dateEnd = AppHelper::convertToGregorian($searchModel->date_end);
            $dateReport = ThaiDateHelper::formatThaiDateRange($dateStart,$dateEnd, 'long', 'short');

        // ถ้าไม่มีข้อมูล
        if (empty($models)) {
            Yii::$app->session->setFlash('error', 'ไม่พบข้อมูลสำหรับส่งออก');
            return $this->redirect([$searchModel->document_group]);
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->mergeCells('A1:I1');

        $rowTitle = 'A1';
        $dateStart = AppHelper::convertToGregorian($searchModel->date_start);
        $dateEnd = AppHelper::convertToGregorian($searchModel->date_end);
        $dateReport = ThaiDateHelper::formatThaiDateRange($dateStart, $dateEnd, 'long', 'short');

        $sheet->setCellValue($rowTitle, 'ทะเบียน'.$title.' ปีงบประมาณ '.$searchModel->thai_year .' วันที่ '.$dateReport);
        $sheet->getStyle($rowTitle)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($rowTitle)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($rowTitle)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);
        $sheet->getColumnDimension('A')->setWidth(6);

        $rowA1 = 'A2';
        $sheet->setCellValue($rowA1, 'ลำดับ');
        $sheet->getStyle($rowA1)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($rowA1)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($rowA1)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);
        $sheet->getColumnDimension('A')->setWidth(6);

        $rowB1 = 'B2';
        $sheet->setCellValue($rowB1, 'สถานะ');
        $sheet->getStyle($rowB1)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($rowB1)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($rowB1)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);
        $sheet->getColumnDimension('B')->setWidth(20);

        $rowC1 = 'C2';
        $sheet->setCellValue($rowC1, 'เลขรับ');
        $sheet->getStyle($rowC1)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($rowC1)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($rowC1)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);
        $sheet->getColumnDimension('C')->setWidth(20);

        $rowD2 = 'D2';
        $sheet->setCellValue($rowD2, 'เลขหนังสือ');
        $sheet->getStyle($rowD2)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($rowD2)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($rowD2)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);
        $sheet->getColumnDimension('D')->setWidth(20);

        $rowE2 = 'E2';
        $sheet->setCellValue($rowE2, 'ชื่อเรื่อง');
        $sheet->getStyle($rowE2)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($rowE2)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($rowE2)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);
        $sheet->getColumnDimension('E')->setWidth(100);

        $rowF1 = 'F2';
        $sheet->setCellValue($rowF1, 'วันที่รับ');
        $sheet->getStyle($rowF1)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($rowF1)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($rowF1)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(true)->setItalic(false);
        $sheet->getColumnDimension('F')->setWidth(20);

        $sheet->setTitle('ทะเบียนหนังสือรับ');

        $StartRowSheet = 3;
        foreach ($models as $key => $item) {
            $numRow = $StartRowSheet++;
            $sheet->setCellValue('A' . $numRow, ($key + 1));
            $sheet->getStyle('A' . $numRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A' . $numRow)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(false)->setItalic(false);
            $sheet->getStyle('A' . $numRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('A' . $numRow)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
            $sheet->getStyle('A' . $numRow)->getFill()->getStartColor()->setRGB('8DB4E2');

            $sheet->setCellValue('B' . $numRow, $item->documentStatus->title ?? '-');
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

            $sheet->setCellValue('D' . $numRow, $item->doc_number);
            $sheet->getStyle('D' . $numRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('D' . $numRow)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(false)->setItalic(false);
            $sheet->getStyle('D' . $numRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('D' . $numRow)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
            $sheet->getStyle('D' . $numRow)->getFill()->getStartColor()->setRGB('8DB4E2');

            $sheet->setCellValue('E' . $numRow, $item->topic);
            $sheet->getStyle('E' . $numRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('E' . $numRow)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(false)->setItalic(false);
            $sheet->getStyle('E' . $numRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('E' . $numRow)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
            $sheet->getStyle('E' . $numRow)->getFill()->getStartColor()->setRGB('8DB4E2');


            $sheet->setCellValue('F' . $numRow,  ThaiDateHelper::formatThaiDate($item->doc_transactions_date));
            $sheet->getStyle('F' . $numRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . $numRow)->getFont()->setName('TH Sarabun New')->setSize(16)->setBold(false)->setItalic(false);
            $sheet->getStyle('F' . $numRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('F' . $numRow)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
            $sheet->getStyle('F' . $numRow)->getFill()->getStartColor()->setRGB('8DB4E2');
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
            'thai_year' => AppHelper::YearBudget(),
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
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'topic', $searchModel->q],
            ['like', 'doc_regis_number', $searchModel->q],
            ['like', new \yii\db\Expression("JSON_UNQUOTE(JSON_EXTRACT(data_json, '$.des'))"), $searchModel->q],
        ]);

        if ($searchModel->date_filter) {
            $range = DateFilterHelper::getRange($searchModel->date_filter);
            $searchModel->date_start = AppHelper::convertToThai($range[0]);
            $searchModel->date_end = AppHelper::convertToThai($range[1]);
        }

        if ($searchModel->thai_year !== '' && $searchModel->date_filter == '') {
            $searchModel->date_start = AppHelper::convertToThai(($searchModel->thai_year - 544) . '-10-01');
            $searchModel->date_end = AppHelper::convertToThai(($searchModel->thai_year - 543) . '-09-30');
        }

        $dataProvider->query->andFilterWhere([
            'between',
            'doc_transactions_date',
            AppHelper::convertToGregorian($searchModel->date_start),
            AppHelper::convertToGregorian($searchModel->date_end)
        ]);

        $dataProvider->setSort(['defaultOrder' => [
            'doc_regis_number' => SORT_DESC,
            'thai_year' => SORT_DESC,
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
            'doc_number' => $this->request->get('doc_number'),
            'doc_speed' => $this->request->get('doc_speed'),
            'secret' => $this->request->get('secret'),
            'document_org' => $this->request->get('document_org'),
            'topic' => $this->request->get('topic'),
            'data_json' => [
                'file_name' => $this->request->get('file_name')
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
        // End Set Default
        // $model->ref =  substr(\Yii::$app->getSecurity()->generateRandomString(), 10);

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

                if (!is_numeric($model->document_org)) {
                    $model->document_org = $this->UpdateDocOrg($model);
                }

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

            if (!is_numeric($model->document_org)) {
                $model->document_org = $this->UpdateDocOrg($model);
            }

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
                // return $this->redirect([$model->document_group]);
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

        $sourcePath = Yii::getAlias('@app/doc_receive/' . $filename);
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


    // ตรวจสอบว่ามีอุปกรณ์รายการใหม่หรือไม่
    protected function UpdateDocOrg($model)
    {
        // try {
        $title = $model->document_org;
        $check = Categorise::find()->where(['name' => 'document_org', 'title' => $title])->one();
        if (!$check) {
            $maxCode = Categorise::find()->select(['code' => new \yii\db\Expression('MAX(CAST(code AS UNSIGNED))')])->where(['like', 'name', 'document_org'])->scalar();
            $newModel = new Categorise();
            $newModel->code = ($maxCode + 1);
            $newModel->name = 'document_org';
            $newModel->title = $title;
            $newModel->save(false);
            return $newModel->code;
        }

        // } catch (\Throwable $th) {
        // }
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

            //## ตรวจสอบสถานะส่งเสนอ ผอ.
            $director = SiteHelper::getInfo()['director']->id;

            try {

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
            $fileUpload = Uploads::findOne(['ref' => $ref]);
            $type = 'pdf';

            if ($file_name) {
                $filepath = Yii::getAlias('@app') . '/doc_receive/' . $file_name;
            } else if (!$fileUpload) {
                $filepath = Yii::getAlias('@webroot') . '/images/pdf-placeholder.pdf';
            } else if (!$fileUpload && !file_exists($filepath)) {
                $filepath = Yii::getAlias('@webroot') . '/images/pdf-placeholder.pdf';
            } else {
                $filename = $fileUpload->real_filename;
                $filepath = FileManagerHelper::getUploadPath() . $fileUpload->ref . '/' . $filename;
            }


            $this->setHttpHeaders($type);
            \Yii::$app->response->data = file_get_contents($filepath);
            return \Yii::$app->response;
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
        $model = $this->findModel($id);
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
}
