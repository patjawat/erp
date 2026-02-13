<?php

namespace app\modules\me\controllers;

use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\health\models\HealthScreen;
use app\modules\health\models\HealthScreenSearch;
use app\modules\hr\models\EmployeeDetail;
use Yii;
use yii\helpers\Html;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class HealthController extends \yii\web\Controller
{

    public function actionIndex()
    {
        $me = UserHelper::GetEmployee();
        $searchModel = new HealthScreenSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andWhere(['emp_id' => $me->id]);

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => 'ข้อมูลประวัติการตรวจสุขภาพ',
                'content' => $this->renderAjax('index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ]),
            ];
        } else {
            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }


     public function actionCreate()
    {
        $me = UserHelper::GetEmployee();
        $model = new HealthScreen([
            'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
            'emp_id' => $me->id,
            'thai_year' => AppHelper::YearBudget(date('Y-m-d'))
        ]);

        if ($this->request->isPost) {
            if ($this->request->isPost && $model->load($this->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                $model->health_status = 'SCREEN';
                $model->date_checkup = isset($model->date_checkup) ? AppHelper::DateToDb($model->date_checkup) : '';

                $model->save(false);

                return [
                    'status' => 'success',
                    'message' => 'บันทึกข้อมูลสำเร็จ',
                ];
            }
        } else {
            $model->loadDefaultValues();
        }

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => 'สร้างข้อมูลประวัติการตรวจสุขภาพ',
                'content' => $this->renderAjax('@app/modules/health/views/health-screen/_form', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('@app/modules/health/views/health-screen/_form', [
                'model' => $model,
            ]);
        }
    }
    

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->date_checkup = isset($model->date_checkup) ? AppHelper::convertToThai($model->date_checkup) : '';

        if ($this->request->isPost) {
            if ($this->request->isPost && $model->load($this->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                $model->date_checkup = isset($model->date_checkup) ? AppHelper::DateToDb($model->date_checkup) : '';
                $model->save(false);

                return [
                    'status' => 'success',
                    'message' => 'บันทึกข้อมูลสำเร็จ',
                ];
            }
        } else {
            $model->loadDefaultValues();
        }

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => 'สร้างข้อมูลประวัติการตรวจสุขภาพ',
                'content' => $this->renderAjax('@app/modules/health/views/health-screen/_form', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('@app/modules/health/views/health-screen/_form', [
                'model' => $model,
            ]);
        }
    }
    

public function actionView($id)
{
    Yii::$app->response->format = Response::FORMAT_JSON;
    $model = $this->findModel($id);

    return [
        'title' => '<i class="fa-solid fa-heart-pulse"></i> ผลตรวจสุขภาพ',
        'content' => $this->renderAjax('view', [
            'model' => $model,
        ]),
        'footer' => Html::button('<i class="fa-solid fa-xmark"></i> ปิด', ['class' => 'btn btn-secondary pull-left', 'data-bs-dismiss' => "modal"])
    ];
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

        public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }


    protected function findModel($id)
    {
        if (($model = HealthScreen::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

}
