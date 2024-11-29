<?php

namespace app\modules\hr\controllers;

use Yii;
use yii\helpers\Html;
use yii\web\Response;
use yii\db\Expression;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\hr\models\Leave;
use yii\web\NotFoundHttpException;
use app\modules\hr\models\LeaveStep;
use app\modules\hr\models\LeaveSearch;

/**
 * LeaveController implements the CRUD actions for Leave model.
 */
class LeaveController extends Controller
{
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

        $searchModel = new LeaveSearch([
            'thai_year' => AppHelper::YearBudget()
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', new Expression("JSON_EXTRACT(data_json, '$.reason')"), $searchModel->q],
        ]);
        
        if (!empty($searchModel->leave_type_id)) {
            $dataProvider->query->andFilterWhere(['in', 'leave_type_id', $searchModel->leave_type_id]);
        }
        
        // $dataProvider->sort->defaultOrder = ['id' => SORT_DESC];

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionDashbroad()
    {
        return $this->render('dashbroad');
    }
    
    public function actionMe()
    {

        $me = UserHelper::GetEmployee();
        $searchModel = new LeaveSearch([
            'emp_id' => $me->id
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', new Expression("JSON_EXTRACT(data_json, '$.reason')"), $searchModel->q],
        ]);
        $dataProvider->sort->defaultOrder = ['id' => SORT_DESC];
        $dataProvider->pagination->pageSize = 15;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionCalendar()
    {
        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('calendar', [
                ]),
            ];
        } else {
            return $this->render('calendar', [
            ]);
        }
    }

    public function actionHoliday()
    {
        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('holiday', [
                ]),
            ];
        } else {
            return $this->render('holiday');
        }
    }

    /**
     * Displays a single Leave model.
     *
     * @param int $id ID
     *
     * @return string
     *
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
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('type_select', [
                ]),
            ];
        } else {
            return $this->render('type_select', [
            ]);
        }
    }

    /**
     * Creates a new Leave model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return string|Response
     */
    public function actionCreate()
    {
        $me = UserHelper::GetEmployee();
        $leaveTypeId = $this->request->get('leave_type_id');
        $model = new Leave([
            'ref' => substr(\Yii::$app->getSecurity()->generateRandomString(), 10),
            'leave_type_id' => $leaveTypeId,
            'thai_year' => AppHelper::YearBudget(),
        ]);

        $model->data_json = [
            'title' => $this->request->get('title'),
            'address' => $model->CreateBy()->fulladdress,
            'leader' => $model->Approve()['leader']['id'],
            'leader_group' => $model->Approve()['leaderGroup']['id'],
            'leave_contact_phone' => $model->CreateBy()->phone,
            'director' => \Yii::$app->site::viewDirector()['id'],
            'director_fullname' => \Yii::$app->site::viewDirector()['fullname'],
        ];

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                \Yii::$app->response->format = Response::FORMAT_JSON;
                $model->status = 'Pending';
                $model->emp_id = $me->id;
                $model->thai_year = AppHelper::YearBudget();
                $model->date_start = AppHelper::convertToGregorian($model->date_start);
                $model->date_end = AppHelper::convertToGregorian($model->date_end);
                
                if($model->save()){
                    $model->createLeaveStep();
                }
                

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
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('create', [
                    'model' => $model,
                ]),
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
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new Leave();
        $requiredName = 'ต้องระบุ';
        if ($this->request->isPost && $model->load($this->request->post())) {
            if (isset($model->date_start)) {
                preg_replace('/\D/', '', $model->date_start) == '' ? $model->addError('date_start', $requiredName) : null;
            }
            if (isset($model->date_end)) {
                preg_replace('/\D/', '', $model->date_end) == '' ? $model->addError('date_end', $requiredName) : null;
            }
            $dateStart = preg_replace('/\D/', '', $model->date_start) !== '' ? AppHelper::convertToGregorian($model->date_start) : '';
            $dateEnd = preg_replace('/\D/', '', $model->date_end) !== '' ? AppHelper::convertToGregorian($model->date_end) : '';
            
            // if($dateStart > $dateEnd ){
            //     $model->addError('date_start', 'มากกว่าวันสุดท้าย');
            //     $model->addError('date_end', 'มากกว่าวันเริ่มต้น');
            // }

            $model->data_json['date_start_type'] == '' ? $model->addError('data_json[date_start_type]', $requiredName) : null;
            $model->data_json['date_end_type'] == '' ? $model->addError('data_json[date_end_type]', $requiredName) : null;
            $model->data_json['reason'] == '' ? $model->addError('data_json[reason]', $requiredName) : null;
            $model->data_json['phone'] == '' ? $model->addError('data_json[phone]', $requiredName) : null;
            $model->data_json['location'] == '' ? $model->addError('data_json[location]', $requiredName) : null;
            $model->data_json['address'] == '' ? $model->addError('data_json[address]', $requiredName) : null;
            $model->data_json['leave_work_send_id'] == '' ? $model->addError('data_json[leave_work_send_id]', $requiredName) : null;
            $model->data_json['leader'] == '' ? $model->addError('data_json[leader]', $requiredName) : null;
            $model->data_json['leader_group'] == '' ? $model->addError('data_json[leader_group]', $requiredName) : null;
            // $model->unit_price == "" ? $model->addError('unit_price', $requiredName) : null;
        }
        foreach ($model->getErrors() as $attribute => $errors) {
            $result[Html::getInputId($model, $attribute)] = $errors;
        }
        if (!empty($result)) {
            return $this->asJson($result);
        }
    }

    public function actionCalDays()
    {
        $date_start = preg_replace('/\D/', '', $this->request->get('date_start'));
        $date_end = preg_replace('/\D/', '', $this->request->get('date_end'));
        $dateStart = $date_start == '' ? '' : AppHelper::convertToGregorian($this->request->get('date_start'));
        $dateEnd = $date_end == '' ? '' : AppHelper::convertToGregorian($this->request->get('date_end'));
        // return $dateStart.' '.$dateEnd;
        $model = AppHelper::CalDay($dateStart, $dateEnd);

        \Yii::$app->response->format = Response::FORMAT_JSON;

        return [
            $model,
            'start' => $dateStart,
            'end' => $dateEnd,
        ];
    }

    /**
     * Updates an existing Leave model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param int $id ID
     *
     * @return string|Response
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->date_start = AppHelper::convertToThai($model->date_start);
        $model->date_end = AppHelper::convertToThai($model->date_end);

        if ($this->request->isPost && $model->load($this->request->post())) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            $model->date_start = AppHelper::convertToGregorian($model->date_start);
            $model->date_end = AppHelper::convertToGregorian($model->date_end);
            if($model->save()){
                return $this->redirect(['view', 'id' => $model->id]);
            }

            // return [
            //     'status' => 'success',
            //     'container' => '#leave'
            // ];
        }

        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('update', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    public function actionApprove($id)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $me = UserHelper::GetEmployee();
        $model = LeaveStep::findOne(["id" => $id, "emp_id" => $me->id]);
        $leave = Leave::findOne($model->leave_id);
        if(!$model)
        {
            return [
                'title' => 'แจ้งเตือน',
               'content' => '<h6 class="text-center">ไม่อนุญาติ</h6>',
            ];
        }
        if ($this->request->isPost && $model->load($this->request->post())) {
           
            if($model->save()){
                if($model->level == 3){
                    $leave->status = 'Allow';
                    $leave->save();
                    
                }else{
                    $leave->status = 'Checking';
                    $leave->save();
                    
                }
                
                return [
                    'status' => 'success',
                    'container' => '#leave',
                ];
            }
        }

            if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('form_approve', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('form_approve', [
                'model' => $model,
            ]);
        }
        
       
  

        
      
    }

    
    // การอนุมัติ
    // public function actionApprove($id)
    // {
    //     \Yii::$app->response->format = Response::FORMAT_JSON;
    //     $model = $this->findModel($id);
    //     $name = $this->request->get('name');
    //     $checker = $this->request->get('checker');
    //     $avatar = $model->Avatar($checker)['avatar'];
    //     $oldObj = $model->data_json;
    //     if ($this->request->isPost && $model->load($this->request->post())) {


    //         $model->data_json = ArrayHelper::merge($oldObj, $model->data_json);
    //         $model->save();

    //         return [
    //             'status' => 'success',
    //             'container' => '#leave',
    //         ];
    //     }
    //     if ($this->request->isAJax) {
    //         \Yii::$app->response->format = Response::FORMAT_JSON;

    //         return [
    //             'title' => $avatar,
    //             'content' => $this->renderAjax('form_approve', [
    //                 'model' => $model,
    //                 'name' => $name,
    //             ]),
    //         ];
    //     } else {
    //         return $this->render('form_approve', [
    //             'model' => $model,
    //             'name' => $name,
    //         ]);
    //     }
    // }

    // ตรวจสอบความถูกต้อง
    public function actionApproveValidator()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new LeaveStep();
        $requiredName = 'ต้องระบุ';
        if ($this->request->isPost && $model->load($this->request->post())) {
                $model->status == '' ? $model->addError('status', $requiredName) : null;

        }
        foreach ($model->getErrors() as $attribute => $errors) {
            $result[Html::getInputId($model, $attribute)] = $errors;
        }
        if (!empty($result)) {
            return $this->asJson($result);
        }
    }

    /**
     * Deletes an existing Leave model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param int $id ID
     *
     * @return Response
     *
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
     *
     * @param int $id ID
     *
     * @return Leave the loaded model
     *
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
