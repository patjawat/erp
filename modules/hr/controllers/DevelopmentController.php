<?php

namespace app\modules\hr\controllers;

use Yii;
use DateTime;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\Response;
use setasign\Fpdi\Fpdi;
use yii\web\Controller;
use app\models\Categorise;
use yii\filters\VerbFilter;
use app\components\AppHelper;
use app\components\SiteHelper;
use app\components\UserHelper;
use app\components\ThaiDateHelper;
use yii\web\NotFoundHttpException;
use app\modules\hr\models\Development;
use app\modules\hr\models\DevelopmentSearch;
use app\modules\filemanager\components\FileManagerHelper;

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
        $dataProvider->query->joinWith('developmentDetail');
        $dataProvider->query->andFilterWhere(['status' => $searchModel->q_status]);
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'topic', $searchModel->q],
            ['like', 'development.emp_id', $searchModel->emp_id],
            ['like', new \yii\db\Expression("JSON_UNQUOTE(JSON_EXTRACT(development.data_json, '$.location'))"), $searchModel->q],
        ]);

        $dataProvider->query->andFilterWhere(['development_detail.emp_id' => $searchModel->emp_id]);


        $dataProvider->query->andFilterWhere(['>=', 'date_start', AppHelper::convertToGregorian($searchModel->date_start)])->andFilterWhere(['<=', 'date_end', AppHelper::convertToGregorian($searchModel->date_end)]);
        $dataProvider->query->orderBy(['date_start' => SORT_DESC, 'id' => SORT_DESC]);
        $dataProvider->query->groupBy('development_detail.id');

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
        $model = $this->findModel($id);
        $dateStart2 = $model->date_start;

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
            return $model->status;

            return $this->redirect('index');
        }

        // return $this->render('_form_dev', [
        return $this->render('_form', [
            'model' => $model,
            'dateStart2' => $dateStart2
        ]);
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




    public function actionPdfEditor()
    {
        $check = Categorise::findOne(['name' => 'form_development_pdf']);
        if (!$check) {
            $model =  new Categorise([
                'name' => 'form_development_pdf',
                'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10)
            ]);
            $model->save();
        } else {
            $model = $check;
        }

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ['status' => 'success', 'message' => 'บันทึกพิกัดเรียบร้อยแล้ว'];
        }
        return $this->render('pdf_editor', ['model' => $model]);
    }


    public function actionPrint($id)
    {
        // 1. ดึงข้อมูลหลักและค่าเลย์เอาต์
        $model = $this->findModel($id);
        $formName = 'form_development_pdf';
        $layout = Categorise::findOne(['name' => $formName]);

        if (!$layout) {
            throw new NotFoundHttpException("ไม่พบข้อมูลเลย์เอาต์สำหรับฟอร์ม: $formName");
        }

        // 2. ดึง Path ไฟล์เทมเพลต PDF
        $templateFile = FileManagerHelper::getFileFormRef($layout->ref);
        if (!$templateFile || !file_exists($templateFile)) {
            Yii::$app->session->setFlash('error', 'ไม่พบไฟล์เทมเพลต PDF ต้นฉบับ');
            return $this->redirect(['view', 'id' => $id]);
        }
        // 3. เริ่มต้นสร้าง PDF ด้วย FPDI
        $pdf = new Fpdi();
        // ตั้งค่าฟอนต์ไทย (ต้องมีไฟล์ .php และ .z ในโฟลเดอร์ฟอนต์ของ fpdf)
        $pdf->AddFont('THSarabunNew', '', 'THSarabunNew.php');
        $pdf->AddFont('THSarabunNew', 'B', 'THSarabunNew Bold.php');

        // โหลดเทมเพลตหน้าแรก
        $pdf->setSourceFile($templateFile);
        $tplIdx = $pdf->importPage(1);
        $size = $pdf->getTemplateSize($tplIdx);

        // เพิ่มหน้าตามขนาดต้นฉบับ (ปกติเป็น A4: 210x297 mm)
        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
        $pdf->useTemplate($tplIdx);
        /**
         * ตัวคูณแปลงค่าจาก Point (ที่เซฟจาก Designer) เป็น Millimeters
         * สูตร: (25.4 mm / 72 dpi) = 0.352777...
         */
        $ptToMm = 25.4 / 71;

        // Offset สำหรับปรับจูนหน้างาน (ถ้าพิมพ์แล้วเบี้ยวทั้งแผ่นให้แก้ตรงนี้)
        $offsetX = 0;
        $offsetY = 0;

        $info = $this->GetInfo();
        $dataJson = $layout->data_json ?? [];

        // ฟังก์ชันช่วยเขียนข้อความลงในพิกัด
        $writeText = function ($key, $text, $fontSize = 13, $style = '') use ($pdf, $dataJson, $ptToMm, $offsetX, $offsetY) {
            $xKey = $key . '_x';
            $yKey = $key . '_y';

            if (isset($dataJson[$xKey]) && isset($dataJson[$yKey])) {
                $pdf->SetFont('THSarabunNew', $style, $fontSize);

                // แปลงพิกัด Point จากฐานข้อมูล เป็น mm
                $x = ((float)$dataJson[$xKey] * $ptToMm) + $offsetX;
                $y = ((float)$dataJson[$yKey] * $ptToMm) + $offsetY;

                $pdf->SetXY($x, $y);
                $pdf->Write(0, iconv('UTF-8', 'cp874', (string)$text));
            }
        };

        // --- เริ่มพิมพ์ฟิลด์ต่างๆ ---

        // --- ลายเซ็นต์ผู้ขอ ---
        try {
  
        $createdSig = $model->createdByEmp?->SignatureFilePath();
        if ($createdSig) {
            // พิกัด XY ดึงมาจาก data_json ตามที่คุณทำไว้
            $key = 'fullname_signature_img';
            $x = ((float)$dataJson[$key . '_x'] * $ptToMm) + $offsetX;
            $y = ((float)$dataJson[$key . '_y'] * $ptToMm) + $offsetY;

            // แทรกรูปลง PDF โดยใช้ Path ตรงๆ (ไม่ต้องผ่าน URL)
            // ปรับ $y - 12 เพื่อให้รูปอยู่เหนือชื่อ
            $pdf->Image($createdSig, $x, $y, 20, 0);
        }
        } catch (\Throwable $th) {
            //throw $th;
        }

        // --- ลายเซ็นต์ผู้ปฏิบัติหน้าที่แทน ---
        try {

            $assignedToSig = $model->assignedTo?->SignatureFilePath();
            if ($createdSig) {
                // พิกัด XY ดึงมาจาก data_json ตามที่คุณทำไว้
                $key = 'assigned_to_signature_img';
                $x = ((float)$dataJson[$key . '_x'] * $ptToMm) + $offsetX;
                $y = ((float)$dataJson[$key . '_y'] * $ptToMm) + $offsetY;

                // แทรกรูปลง PDF โดยใช้ Path ตรงๆ (ไม่ต้องผ่าน URL)
                // ปรับ $y - 12 เพื่อให้รูปอยู่เหนือชื่อ
                $pdf->Image($assignedToSig, $x, $y, 20, 0);
            }
        } catch (\Throwable $th) {
            //throw $th;
        }

        // --- ลายเซ็นต์ หัวหน้าเจ้าหน้าที่. ---
        try {
            $leaderSig = SiteHelper::getInfo()['leader_signature_path'];
            if ($leaderSig) {
                // พิกัด XY ดึงมาจาก data_json ตามที่คุณทำไว้
                $key = 'leader_signature_img';
                $x = ((float)$dataJson[$key . '_x'] * $ptToMm) + $offsetX;
                $y = ((float)$dataJson[$key . '_y'] * $ptToMm) + $offsetY;

                // แทรกรูปลง PDF โดยใช้ Path ตรงๆ (ไม่ต้องผ่าน URL)
                // ปรับ $y - 12 เพื่อให้รูปอยู่เหนือชื่อ
                $pdf->Image($leaderSig, $x, $y, 20, 0);
            }
        } catch (\Throwable $th) {
            //throw $th;
        }

        // --- ลายเซ็นต์ ผอ. ---
        if ($model->status == 'Approve') {

            $directorSig = \Yii::$app->site::viewDirector()['signature'];
            if ($directorSig) {
                // พิกัด XY ดึงมาจาก data_json ตามที่คุณทำไว้
                $key = 'director_signature_img';
                $x = ((float)$dataJson[$key . '_x'] * $ptToMm) + $offsetX;
                $y = ((float)$dataJson[$key . '_y'] * $ptToMm) + $offsetY;

                // แทรกรูปลง PDF โดยใช้ Path ตรงๆ (ไม่ต้องผ่าน URL)
                // ปรับ $y - 12 เพื่อให้รูปอยู่เหนือชื่อ
                $pdf->Image($directorSig, $x, $y, 20, 0);
            }
        }



        // ส่วนราชการ
        $writeText('company_name', $info['company_name'] ?? '-');
        // เลขที่หนังสือ (ที่)
        $writeText('doc_number', $model->id);
        // วันที่
        $writeText('doc_date', ThaiDateHelper::formatThaiDate(date('Y-m-d'), 'medium'));
        //ด้วยข้าพเจ้า
        $writeText('fullname', $model->createdByEmp?->fullname ?? '-');
        $writeText('position', $model->createdByEmp?->positionName() ?? '-');
        $writeText('fullname_signature', $model->createdByEmp?->fullname ?? '-');
        $writeText('position_signature', 'ตำแหน่ง' . $model->createdByEmp?->positionName() ?? '-');



        $writeText('topic', $model->topic);
        $writeText('location', $model->data_json['location'] ?? '-');
        $writeText('date_start',  ThaiDateHelper::formatThaiDate($model->date_start, 'medium'));
        $writeText('date_end',  ThaiDateHelper::formatThaiDate($model->date_end, 'medium'));
        $writeText('vehicle_date_start',  ThaiDateHelper::formatThaiDate($model->vehicle_date_start, 'medium'));
        $writeText('vehicle_time_start',  $model->data_json['vehicle_time_start']);
        $writeText('vehicle_date_end',  ThaiDateHelper::formatThaiDate($model->vehicle_date_end, 'medium'));
        $writeText('vehicle_time_end',  $model->data_json['vehicle_time_end']);
        $writeText('claim_type_name',  $model->data_json['claim_type_name']);
        $writeText('total_days',  $this->getTotalDays($model->date_start, $model->date_end));
        $writeText('vehicle_type', ($model->vehicleType?->title ?? '-'));
        $writeText('assigned_to', ($model->assignedTo?->fullname ?? '-'));
        $writeText('assigned_to_position', ($model->assignedTo?->positionName() ?? '-'));
        $writeText('assigned_to_signature', ($model->assignedTo?->fullname ?? '-'));
        $writeText('approve_date', (ThaiDateHelper::formatThaiDate($model->approveDate()) ?? '-'));
        // 1. ดึงค่าพิกัดเริ่มต้นจาก JSON
        $startX = (float)($dataJson['member_fullname_start_x'] ?? 0);
        $startY = (float)($dataJson['member_fullname_start_y'] ?? 0);
        $startPositionX = (float)($dataJson['member_position_start_x'] ?? 0);
        $startPositionY = (float)($dataJson['member_fullname_start_y'] ?? 0);

        // 2. กำหนดระยะห่างระหว่างบรรทัด (หน่วยเป็น mm) 
        // โดยปกติฟอนต์ขนาด 14-16pt จะใช้ระยะห่างประมาณ 7-8 mm
        $lineSpacing = 5.5;

        $index = 0;
        foreach ($model->listMemberPrint() as $memberItem) {

            // คำนวณพิกัด: x คงที่, y เพิ่มขึ้นตามลำดับ index
            $x = ($startX * $ptToMm) + $offsetX;
            $y = (($startY * $ptToMm) + $offsetY) + ($index * $lineSpacing);

            $xPosition = ($startPositionX * $ptToMm) + $offsetX;
            $yPosition = (($startPositionY * $ptToMm) + $offsetY) + ($index * $lineSpacing);

            $pdf->SetXY($x, $y);


            // แสดงลำดับที่และชื่อ
            $displayText = ($index + 1) . ". " . ($memberItem->emp->fullname ?? '-');
            $pdf->Write(0, iconv('UTF-8', 'cp874', $displayText));

            $displayTextPosition =  ($memberItem->emp->positionName() ?? '-');
            $pdf->SetXY($xPosition, $yPosition);
            $pdf->Write(0, iconv('UTF-8', 'cp874', $displayTextPosition));

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
