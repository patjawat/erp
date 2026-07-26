<?php

namespace app\modules\me\controllers;

use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\health\models\HealthScreen;
use app\modules\health\models\HealthScreenSearch;
use Yii;
use yii\helpers\Html;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class HealthController extends \yii\web\Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'roles' => ['@']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $me = $this->requireEmployee();
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
        $me = $this->requireEmployee();
        $model = new HealthScreen([
            'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
            'emp_id' => $me->id,
            'thai_year' => AppHelper::YearBudget(date('Y-m-d'))
        ]);

        if ($this->request->isPost) {
            if ($this->request->isPost && $model->load($this->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                $model->emp_id = (int) $me->id;
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
        $me = $this->requireEmployee();
        $model = $this->findModel($id);
        $model->date_checkup = isset($model->date_checkup) ? AppHelper::convertToThai($model->date_checkup) : '';

        if ($this->request->isPost) {
            if ($this->request->isPost && $model->load($this->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                $model->emp_id = (int) $me->id;
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
        'content' => $this->renderAjax('@app/modules/health/views/health-screen/view', [
            'model' => $model,
        ]),
        'footer' => Html::button('<i class="fa-solid fa-xmark"></i> ปิด', ['class' => 'btn btn-secondary pull-left', 'data-bs-dismiss' => "modal"])
    ];
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

        public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }


    protected function findModel($id)
    {
        $me = $this->requireEmployee();
        if (($model = HealthScreen::findOne(['id' => $id, 'emp_id' => (int) $me->id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    protected function requireEmployee()
    {
        $employee = UserHelper::GetEmployee();
        if ($employee === null) {
            throw new NotFoundHttpException('ไม่พบบัญชีบุคลากรของผู้ใช้งาน');
        }

        return $employee;
    }

}
