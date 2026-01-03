<?php

namespace app\modules\hr\controllers;

use Yii;
use DateTime;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\Response;
use yii\web\Controller;
use app\models\Categorise;
use yii\filters\VerbFilter;
use app\components\AppHelper;
use app\components\UserHelper;
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
            if ($model->load($this->request->post()) ) {
                $model->status = 'Pending';
                if( $model->save()){
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


    public function actionPdfLayoutSetting()
    {
        $check = Categorise::findOne(['name' => 'development_pdf_layout']);

        $model = $check ? $check : new Categorise;
        $model->name = 'form_layout_service';
        if ($this->request->isPost && $model->load($this->request->post())) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            $model->save();
            return [
                'status' => 'success'
            ];
        }

        return $this->render('_form_setting', ['model' => $model]);
    }

    private function t($text)
    {
        return iconv('UTF-8', 'cp874', $text ?? '');
    }

    //แสดงตัวอย่างของการตั้งค่า
    public function actionPreviewSetting()
    {
        $formName = 'form_layout_service';
        $ref = substr(Yii::$app->getSecurity()->generateRandomString(), 10);
        $checkLayout = Categorise::findOne(['name' => $formName]);

        if (!$checkLayout) {
            $layout = new Categorise();
            $layout->name = $formName;
            $layout->ref = $ref;
            $layout->data_json = [
                "title_x" => "75",
                "title_y" => "63",
                "device_x" => "90",
                "device_y" => "51",
                "created_x" => "145",
                "created_y" => "39",
                "urgency_x" => "170",
                "urgency_y" => "75",
                "location_x" => "67",
                "location_y" => "69",
                "createdby_x" => "130",
                "createdby_y" => "87",
                "createtime_x" => "181",
                "createtime_y" => "27",
                "department_x" => "85",
                "department_y" => "45",
                "tech_receive_x" => "140",
                "tech_receive_y" => "140",
                "repair_number_x" => "165",
                "repair_number_y" => "13",
            ];
            $layout->save();
        } else {
            $layout = $checkLayout;
        }

        $pdf = new \setasign\Fpdi\Fpdi();
        $pdf->AddPage();

        // 1️⃣ กำหนด PDF Template ก่อน
        $templateFile = FileManagerHelper::getFileFormRef($layout->ref);
        if ($templateFile) {

            $pdf->setSourceFile($templateFile); // ต้องเรียกก่อน importPage()
            // สร้างออบเจกต์ PDF และโหลดไฟล์ต้นฉบับ
            $pdf->AddFont('THSarabunNew', '', 'THSarabunNew.php');
            $pdf->AddFont('THSarabunNew', 'B', 'THSarabunNew Bold.php');

            // 2️⃣ เลือกหน้าที่ต้องการ
            $tplIdx = $pdf->importPage(1);

            // 3️⃣ ใช้ template
            $pdf->useTemplate($tplIdx, 0, 0, 210);

            // 4️⃣ เขียนข้อความลงไป
            $pdf->SetFont('THSarabunNew', 'B', 13); // ใช้ขนาดฟอนต์ 13 pt



            // ส่วนราชการ
            $companyName = $this->GetInfo()['company_name'];
            $companyNameX = isset($layout->data_json['company_name_x']) ? (float)$layout->data_json['company_name_x'] : 0;
            $companyNameY = isset($layout->data_json['company_name_y']) ? (float)$layout->data_json['company_name_y'] : 0;
            $pdf->SetXY($companyNameX, $companyNameY);
            $pdf->Write(10,  $this->t($companyName));

            //แผนกงานซ่อม
            $tecDep = 'ซ่อมบำรุง';
            $tecDepNumberX = isset($layout->data_json['tecdev_number_x']) ? (float)$layout->data_json['tecdev_number_x'] : 0;
            $tecDepNumberY = isset($layout->data_json['tecdev_number_y']) ? (float)$layout->data_json['tecdev_number_y'] : 0;
            $pdf->SetXY($tecDepNumberX, $tecDepNumberY);
            $pdf->Write(10,  $this->t($tecDep));

            // เลขที่ใบแจ้งซ่อม
            $repairNumber = 'REP-56810-GEN-0001';
            $repairNumberX = isset($layout->data_json['repair_number_x']) ? (float)$layout->data_json['repair_number_x'] : 0;
            $repairNumberY = isset($layout->data_json['repair_number_y']) ? (float)$layout->data_json['repair_number_y'] : 0;
            $pdf->SetXY($repairNumberX, $repairNumberY);
            $pdf->Write(10,  $this->t($repairNumber));

            // รายละเอียดปัญหา
            $title = 'เปิดเครื่องไม่ติด';
            $titleX = isset($layout->data_json['title_x']) ? (float)$layout->data_json['title_x'] : 0;
            $titleY = isset($layout->data_json['title_y']) ? (float)$layout->data_json['title_y'] : 0;
            $pdf->SetXY($titleX, $titleY);
            $pdf->Write(10,  $this->t($title));

            //สถานที่
            $location = 'ห้อง IPD1';
            $locationX = isset($layout->data_json['location_x']) ? (float)$layout->data_json['location_x'] : 0;
            $locationY = isset($layout->data_json['location_y']) ? (float)$layout->data_json['location_y'] : 0;
            $pdf->SetXY($locationX, $locationY);
            $pdf->Write(10,  $this->t($location));

            //ฝ่ายงานที่ส่งซ่อม
            $department = 'กลุ่มการพยาบาล';
            $ldepartmentX = isset($layout->data_json['department_x']) ? (float)$layout->data_json['department_x'] : 0;
            $departmentY = isset($layout->data_json['department_y']) ? (float)$layout->data_json['department_y'] : 0;
            $pdf->SetXY($ldepartmentX, $departmentY);
            $pdf->Write(10,  $this->t($department));


            // ประเภทอุปกรณ์
            $deviceType = 'ระบบไฟฟ้า';
            $deviceX = isset($layout->data_json['device_x']) ? (float)$layout->data_json['device_x'] : 0;
            $deviceY = isset($layout->data_json['device_y']) ? (float)$layout->data_json['device_y'] : 0;
            $pdf->SetXY($deviceX, $deviceY);
            $pdf->Write(10,  $this->t($deviceType));

            // ผู้ส่งซ่อม
            $createdBy = 'นายสมชาย ใจดี';
            $createdByX = isset($layout->data_json['createdby_x']) ? (float)$layout->data_json['createdby_x'] : 0;
            $createdByY = isset($layout->data_json['createdby_y']) ? (float)$layout->data_json['createdby_y'] : 0;
            $pdf->SetXY($createdByX, $createdByY);
            $pdf->Write(10,  $this->t($createdBy));

            // วันที่ส่งซ่อม
            $createDate = '1 ตถลาคม 2569 11:00 น.';
            $createDateX = isset($layout->data_json['created_x']) ? (float)$layout->data_json['created_x'] : 0;
            $createDateY = isset($layout->data_json['created_y']) ? (float)$layout->data_json['created_y'] : 0;
            $pdf->SetXY($createDateX, $createDateY);
            $pdf->Write(10,  $this->t($createDate));


            // ช่างผู้รับงาน
            $techReceive = 'นายโชคดี มีชัย';
            $techReceiveX = isset($layout->data_json['tech_receive_x']) ? (float)$layout->data_json['tech_receive_x'] : 0;
            $techReceiveY = isset($layout->data_json['tech_receive_y']) ? (float)$layout->data_json['tech_receive_y'] : 0;
            $pdf->SetXY($techReceiveX, $techReceiveY);
            $pdf->Write(10,  $this->t($techReceive));

            $urgencyX = isset($layout->data_json['urgency_x']) ? (float)$layout->data_json['urgency_x'] : 0;
            $urgencyY = isset($layout->data_json['urgency_y']) ? (float)$layout->data_json['urgency_y'] : 0;
            $pdf->SetXY($urgencyX, $urgencyY);
            $pdf->Write(10,  $this->t('ด่วน'));


            // 5️⃣ Output PDF
            $pdf->Output('I', 'filled.pdf');
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
