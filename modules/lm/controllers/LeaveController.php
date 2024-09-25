<?php

namespace app\modules\lm\controllers;

use app\components\AppHelper;
use app\components\DayHelper;
use app\modules\lm\models\Leave;
use app\modules\lm\models\LeaveSearch;
use app\modules\lm\models\LeaveType;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use Yii;

/**
 * LeaveController implements the CRUD actions for Leave model.
 */
class LeaveController extends Controller
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
     * Lists all Leave models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new LeaveSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionCalendar()
    {
        if ($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('calendar', [

                ])
            ];
        } else {
            return $this->render('calendar', [

            ]);
        }
    }


    public function actionHoliday()
    {
        if ($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('holiday', [

                ])
            ];
        } else {
            return $this->render('holiday');
        }
    }

    /**
     * Displays a single Leave model.
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

    public function actionTypeSelect()
    {
        if ($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('type_select', [

                ])
            ];
        } else {
            return $this->render('type_select', [

            ]);
        }
    }
    /**
     * Creates a new Leave model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $leaveTypeId = $this->request->get('leave_type_id');
        $model = new Leave([
            'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
            'leave_type_id' => $leaveTypeId,
            'thai_year' => AppHelper::YearBudget()
        ]);

        $model->data_json = [
            'title' => $this->request->get('title'),
            'address' => $model->CreateBy()->fulladdress,
            'leader' => $model->Approve()['leader']['id'],
            'leader_group' => $model->Approve()['leaderGroup']['id'],
            'phone' => $model->CreateBy()->phone
        ];

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

                $model->thai_year = AppHelper::YearBudget();
                $model->date_start =  AppHelper::convertToGregorian($model->date_start);
                $model->date_end =  AppHelper::convertToGregorian($model->date_end);
                $model->save();
                return $this->redirect(['view', 'id' => $model->id]);
                // return [
                //     'status' => 'success',
                //     'container' => '#leave'
                // ];
            }
        } else {
            $model->loadDefaultValues();
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


    // ตรวจสอบความถูกต้อง
    public function actionCreateValidator()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new Leave();
        $requiredName = "ต้องระบุ";
        if ($this->request->isPost && $model->load($this->request->post())) {


            if (isset($model->data_json['date_start'])) {
                preg_replace('/\D/', '', $model->data_json['date_start']) == "" ? $model->addError('data_json[date_start]', $requiredName) : null;
            }
            if (isset($model->data_json['date_end'])) {
                preg_replace('/\D/', '', $model->data_json['date_end']) == "" ? $model->addError('data_json[date_end]', $requiredName) : null;
            }

            // $model->qty == "" ? $model->addError('qty', $requiredName) : null;
            // $model->unit_price == "" ? $model->addError('unit_price', $requiredName) : null;
        }
        foreach ($model->getErrors() as $attribute => $errors) {
            $result[\yii\helpers\Html::getInputId($model, $attribute)] = $errors;
        }
        if (!empty($result)) {
            return $this->asJson($result);
        }
    }


    public function actionCalDays()
    {
        $date_start = preg_replace('/\D/', '', $this->request->get('date_start'));
        $date_end = preg_replace('/\D/', '', $this->request->get('date_end'));
        $dateStart = $date_start =="" ? "" : AppHelper::convertToGregorian($this->request->get('date_start'));
        $dateEnd = $date_end =="" ? "" : AppHelper::convertToGregorian($this->request->get('date_end'));
        // return $dateStart.' '.$dateEnd;
        $model = AppHelper::CalDay($dateStart,$dateEnd);
      
        Yii::$app->response->format = Response::FORMAT_JSON;
       return [
        $model,
        'start' => $dateStart,
        'end' => $dateEnd
       ];
    }
    /**
     * Updates an existing Leave model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->date_start =  AppHelper::convertToThai($model->date_start);
        $model->date_end =  AppHelper::convertToThai($model->date_end);

        if ($this->request->isPost && $model->load($this->request->post()) ) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $model->date_start =  AppHelper::convertToGregorian($model->date_start);
            $model->date_end =  AppHelper::convertToGregorian($model->date_end);
            $model->save();
            return $this->redirect(['view', 'id' => $model->id]);
            // return [
            //     'status' => 'success',
            //     'container' => '#leave'
            // ];
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

    /**
     * Deletes an existing Leave model.
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
     * Finds the Leave model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Leave the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Leave::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
