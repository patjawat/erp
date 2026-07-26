<?php

namespace app\modules\hr\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\ModalHelper;
use yii\web\NotFoundHttpException;
use app\modules\hr\models\Employees;
use app\modules\hr\models\EmployeeDetail;

/**
 * HealthController implements the CRUD actions for EmployeeDetail model.
 */
class HealthController extends Controller
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
     * Lists all EmployeeDetail models.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->redirect(['/health/health-screen/index']);
    }

    public function actionDashboard()
    {
        return $this->redirect(['/health/default/index']);
    }


    /**
     * Displays a single EmployeeDetail model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $this->layout = '@app/views/layouts/none';
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }


    /**
     * Creates a new EmployeeDetail model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {

        $id = $this->request->get('id');
        $emp_id = $this->request->get('emp_id');
        $name = $this->request->get('name');
        $emp = Employees::findOne($emp_id);

        $model = new EmployeeDetail([
            'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
            'emp_id' => $emp_id,
            'name' => $name,
            'data_json' => [
                'fullname' => $emp->fullname,
                'age' => $emp->age,
                'gender' => $emp->gender
            ]
        ]);


        if ($this->request->isPost) {
            if ($this->request->isPost && $model->load($this->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;

                $array2 = [
                    'screening_date' => isset($model->data_json['screening_date']) ? AppHelper::DateToDb($model->data_json['screening_date']) : '',
                ];
                $model->data_json = ArrayHelper::merge($model->data_json, $array2);
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
                'content' => $this->renderAjax('create', [
                    'model' => $model,
                ]),
                'footer' => ModalHelper::modalFooterSaveClose()
            ];
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing EmployeeDetail model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $this->layout = '@app/views/layouts/none';
        $model = $this->findModel($id);
        $arrayUpdate = [
            'screening_date' => isset($model->data_json['screening_date']) ? AppHelper::convertToThai($model->data_json['screening_date']) : '',
        ];

        $model->data_json = ArrayHelper::merge($model->data_json, $arrayUpdate);
        if ($this->request->isPost) {
            if ($this->request->isPost && $model->load($this->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                $array2 = [
                    'screening_date' => isset($model->data_json['screening_date']) ? AppHelper::DateToDb($model->data_json['screening_date']) : '',
                ];
                $model->data_json = ArrayHelper::merge($model->data_json, $array2);
                $model->save(false);
                return [
                    'status' => 'success',
                    'message' => 'บันทึกข้อมูลสำเร็จ',
                ];
            }
        }

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => 'สร้างข้อมูลประวัติการตรวจสุขภาพ',
                'content' => $this->renderAjax('update', [
                    'model' => $model,
                ]),
                'footer' => ModalHelper::modalFooterSaveClose()
            ];
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing EmployeeDetail model.
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
     * Finds the EmployeeDetail model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return EmployeeDetail the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = EmployeeDetail::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
