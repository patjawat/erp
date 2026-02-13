<?php

namespace app\modules\health\controllers;

use app\components\AppHelper;
use app\modules\health\models\HealthLab;
use app\modules\health\models\HealthLabConfirm;
use app\modules\health\models\HealthScreen;
use app\modules\health\models\HealthScreenSearch;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

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
     * Lists all HealthScreen models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new HealthScreenSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

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
     * Creates a new HealthScreen model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
   

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
            $listLabItems = HealthLab::find()->all();
            if (!empty($listLabItems)) {
                foreach ($listLabItems as $lab) {
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
            if($model->health_status == 'SCREEN'){
                $model->health_status = 'CONFIRM';
                $model->save();
            }

            $transaction->commit();
            Yii::$app->session->setFlash('success', 'ยืนยันผล LAB เรียบร้อยแล้ว');
            return $this->redirect(['index']);
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
            }
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
            // ข้อมูล data_json จะถูกส่งมาเป็น Array จากฟอร์ม
            // หากต้องการบันทึกลง MySQL คอลัมน์ JSON ตรงๆ Yii2 จะจัดการให้
            if($model->health_status == 'CONFIRM'){
                $model->health_status = 'SUCCESS';
                $model->save();
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


    // ตรวจสอบความถูกต้อง
    public function actionValidator()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $model = new HealthScreen();
        $result = []; // เตรียมตัวแปรเก็บ Error

        if ($this->request->isPost && $model->load($this->request->post())) {
            $requiredName = 'ต้องระบุ';

            // รายการฟิลด์ที่ต้องการ Check
            $fields = [
                'smoking_status',
                'alcohol_status',
                'exercise_status',
                'food_taste',
                'driving_safety',
                'condom_usage',
            ];

            foreach ($fields as $field) {
                if (!isset($model->data_json[$field]) || $model->data_json[$field] === '') {
                    // สร้าง ID แบบเดียวกับที่ Yii2 ใช้ในหน้าเว็บเป๊ะๆ
                    $id = \yii\helpers\Html::getInputId($model, "data_json[$field]");
                    $result[$id] = [$requiredName];
                }
            }

            // เช็ค Checkbox
            if (empty($model->data_json['family_history'])) {
                $id = \yii\helpers\Html::getInputId($model, 'data_json[family_history]');
                $result[$id] = ['กรุณาเลือกอย่างน้อย 1 รายการ'];
            }

            return $result; // ส่ง Array ของ ID และ Message กลับไปตรงๆ
        }
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
