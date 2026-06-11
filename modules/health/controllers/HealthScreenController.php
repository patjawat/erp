<?php

namespace app\modules\health\controllers;

use app\components\AppHelper;
use app\modules\health\models\HealthLab;
use app\modules\health\models\HealthLabConfirm;
use app\modules\health\models\HealthScreen;
use app\modules\health\models\HealthScreenSearch;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;
use kartik\mpdf\Pdf;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * HealthScreenController implements the CRUD actions for HealthScreen model.
 */
class HealthScreenController extends Controller
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
     * Creates a new HealthScreen model.
     * @param int|null $emp_id รหัสพนักงาน (ส่งมาจาก URL ได้)
     * @return string|\yii\web\Response
     */
    public function actionCreate($emp_id = null)
    {
        $model = new HealthScreen();
        $model->emp_id = $emp_id;
        $model->thai_year = AppHelper::YearBudget(date('Y-m-d'));
        $model->date_checkup = AppHelper::convertToThai(date('Y-m-d'));
        $model->health_status = 'SCREEN';

        if ($this->request->isPost && $model->load($this->request->post())) {
            if (!empty($model->date_checkup)) {
                $model->date_checkup = AppHelper::DateToDb($model->date_checkup);
            }
            if ($model->weight && $model->height) {
                $hm = (float)$model->height / 100;
                $model->bmi = $hm > 0 ? round((float)$model->weight / ($hm * $hm), 1) : null;
            }
            $model->health_status = 'SCREEN';

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'บันทึกข้อมูลสุขภาพพนักงานเรียบร้อยแล้ว');
                return $this->redirect(['lab-confirm', 'id' => $model->id]);
            }
        }

          if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return [
                    'title' => '',
                    'content' => $this->renderAjax('create', [
                  'model' => $model,
                  ])
                ];

          }else{

              return $this->render('create', [
                  'model' => $model,
                  ]);
                  }
    }

    /**
     * Lists all HealthScreen models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new HealthScreenSearch(['thai_year' => AppHelper::YearBudget(date('Y-m-d'))]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->joinWith('employee');

        if ($searchModel->q_department) {
            $org1 = Organization::findOne($searchModel->q_department);

            if ($org1 && $org1->lvl == 1) {
                $cacheKey = 'org_child_' . $org1->id;
                $arrDepartment = Yii::$app->cache->get($cacheKey);
                if ($arrDepartment === false) {
                    $arrDepartment = Organization::find()
                        ->select('id')
                        ->where(['between', 'lft', $org1->lft, $org1->rgt])
                        ->column();
                    Yii::$app->cache->set($cacheKey, $arrDepartment, 3600);
                }

                // ✅ ใช้ emp_id จาก employees ที่อยู่ใน department เหล่านั้น
                $empIds = Employees::find()
                    ->select('id')
                    ->andWhere(['department' => $arrDepartment])
                    ->column();

                $dataProvider->query->andWhere(['in', 'emp_id', $empIds]);
            } else {
                $empIds = Employees::find()
                    ->select('id')
                    ->andWhere(['department' => $searchModel->q_department])
                    ->column();

                $dataProvider->query->andWhere(['in', 'emp_id', $empIds]);
            }
        }
        
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }



    /**
     * Displays a single HealthScreen model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }



    /**
     * Updates an existing HealthScreen model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }


    public function actionLabConfirm($id)
    {
        $model = $this->findModel($id); // ข้อมูลหลัก (HealthScreen)
        $labItems = HealthLabConfirm::find()->where(['lab_screen_id' => $id])->all();

        if (empty($labItems)) {
            // ดึงข้อมูลอายุและเพศของพนักงาน
            $employee = $model->employee;
            $employeeAge = (int)($employee->age ?? 0);
            $employeeGender = $employee->gender ?? '';
            
            // แปลงเพศจากภาษาไทยเป็นภาษาอังกฤษสำหรับการตรวจสอบ
            $genderMap = ['ชาย' => 'male', 'หญิง' => 'female'];
            $employeeGenderCode = $genderMap[$employeeGender] ?? '';
            
            // ดึงรายการ Lab ทั้งหมด
            $allLabs = HealthLab::find()->all();
            
            // กรองรายการ Lab ตามเงื่อนไขอายุและเพศ (แต่ละรายการกำหนดเองได้)
            $filteredLabs = [];
            foreach ($allLabs as $lab) {
                $ageMatch = $lab->matchAgeCondition($employeeAge);
                $genderMatch = ($lab->gender_condition === 'all' || $lab->gender_condition === $employeeGenderCode);
                if ($ageMatch && $genderMatch) {
                    $filteredLabs[] = $lab;
                }
            }
            
            if (!empty($filteredLabs)) {
                foreach ($filteredLabs as $lab) {
                    $labConfirm = new HealthLabConfirm();
                    $labConfirm->lab_screen_id = $id;
                    $labConfirm->lab_code = $lab->lab_code;
                    $labConfirm->lab_price = $lab->lab_price;
                    $labConfirm->qty = 1;
                    $labItems[] = $labConfirm;
                }
            } else {
                $labItems = [new HealthLabConfirm()];
            }
        }

        if (Yii::$app->request->isPost) {
            $postData = Yii::$app->request->post('HealthLabConfirm', []);
            // ใช้ Transaction เพื่อความปลอดภัยของข้อมูล
            $transaction = Yii::$app->db->beginTransaction();
            try {
            // โหลดและบันทึกวันที่นัดหมาย (HealthScreen)
            if ($model->load(Yii::$app->request->post()) && !empty($model->appointment_date)) {
                $model->appointment_date = AppHelper::DateToDb($model->appointment_date);
            }

            // เคลียร์ข้อมูลเก่าเฉพาะของเคสนี้
            HealthLabConfirm::deleteAll(['lab_screen_id' => $id]);

            foreach ($postData as $data) {
                if (!empty($data['lab_code'])) {
                    $entry = new HealthLabConfirm();
                    $entry->load($data, '');
                    $entry->lab_screen_id = $id;
                    $entry->qty = $entry->qty ?? 1;
                    if (!$entry->save(false)) {
                        throw new \Exception("บันทึกข้อมูลไม่สำเร็จ");
                    }
                }
            }
            if ($model->health_status == 'SCREEN') {
                $model->health_status = 'CONFIRM';
            }
            $model->save(false);

            $transaction->commit();
            Yii::$app->session->setFlash('success', 'ยืนยันผล LAB เรียบร้อยแล้ว');
            return $this->redirect(['index']);
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
            }
        }

        // แปลงวันที่นัดหมายเป็นรูปแบบไทยสำหรับแสดงในฟอร์ม
        if (!empty($model->appointment_date)) {
            $model->appointment_date = AppHelper::convertToThai($model->appointment_date);
        }

        return $this->render('lab_confirm', [
            'model' => $model,
            'labItems' => $labItems,
        ]);
    }


    public function actionPhysicalExam($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            // รองรับ AJAX request
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                
                // ข้อมูล data_json จะถูกส่งมาเป็น Array จากฟอร์ม
                // หากต้องการบันทึกลง MySQL คอลัมน์ JSON ตรงๆ Yii2 จะจัดการให้
                if ($model->health_status == 'CONFIRM') {
                    $model->health_status = 'SUCCESS';
                }

                if ($model->save()) {
                    return [
                        'status' => 'success',
                        'message' => 'บันทึกผลการตรวจร่างกายเรียบร้อยแล้ว',
                        'redirect_url' => \yii\helpers\Url::to(['index']),
                    ];
                } else {
                    return [
                        'status' => 'error',
                        'message' => 'ไม่สามารถบันทึกข้อมูลได้ กรุณาตรวจสอบข้อมูลอีกครั้ง',
                    ];
                }
            }
            
            // สำหรับ non-AJAX request (fallback)
            if ($model->health_status == 'CONFIRM') {
                $model->health_status = 'SUCCESS';
            }

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'บันทึกผลการตรวจร่างกายเรียบร้อยแล้ว');
                return $this->redirect(['index']);
            }
        }

        return $this->render('physical_exam', [
            'model' => $model,
        ]);
    }



public function actionPrint($id)

{
    $model = $this->findModel($id);
    
    // 1. เตรียม Path ของฟอนต์
    $fontPath = Yii::getAlias('@webroot/fonts/THSarabunNew');

    $pdf = new Pdf([
        'mode' => Pdf::MODE_UTF8,
        'format' => Pdf::FORMAT_A4,
        'orientation' => Pdf::ORIENT_PORTRAIT,
        'destination' => Pdf::DEST_BROWSER,
        'content' => $this->renderPartial('_print_pdf', ['model' => $model]),
        'cssFile' => '@webroot/css/kv-mpdf-bootstrap.css',
        'cssInline' => 'body { font-family: "thsarabunnew"; font-size: 16pt; }', 
        'options' => [
            'title' => 'ใบรับรองการตรวจสุขภาพ',
            // 2. ตั้งค่า Font ผ่าน Config Array
            'fontDir' => array_merge((new \Mpdf\Config\ConfigVariables())->getDefaults()['fontDir'], [
                $fontPath,
            ]),
            'fontdata' => array_merge((new \Mpdf\Config\FontVariables())->getDefaults()['fontdata'], [
                'thsarabunnew' => [
                    'R' => 'THSarabunNew.ttf',
                    'B' => 'THSarabunNew-Bold.ttf',
                    'I' => 'THSarabunNew-Italic.ttf',
                ]
            ]),
            'default_font' => 'thsarabunnew', // กำหนดเป็นฟอนต์หลัก
            'tempDir' => Yii::getAlias('@runtime/mpdf'),
        ],
    ]);

    return $pdf->render();
}


    /**
     * Deletes an existing HealthScreen model.
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


    /**
     * Ajax validation สำหรับฟอร์มคัดกรองสุขภาพ
     */
    public function actionValidator()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $model = new HealthScreen();
        if ($this->request->isPost && $model->load($this->request->post())) {
            return HealthScreen::getScreenFormValidationErrors($model);
        }
        return [];
    }




    /**
     * Finds the HealthScreen model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return HealthScreen the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = HealthScreen::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
