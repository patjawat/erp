<?php

namespace app\modules\me\controllers;

use Yii;
use DateTime;
use yii\helpers\Html;
use yii\web\Response;
use yii\db\Expression;
use app\models\Approve;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\hr\models\Leave;
use yii\web\NotFoundHttpException;
use app\modules\hr\models\LeaveSearch;
use app\modules\hr\models\LeavePermission;

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

        $me = UserHelper::GetEmployee();
        $lastDay = (new DateTime(date('Y-m-d')))->modify('last day of this month')->format('Y-m-d');
        $searchModel = new LeaveSearch([
            'emp_id' => $me->id,
            'thai_year' => AppHelper::YearBudget(),
            'date_start' => AppHelper::convertToThai(date('Y-m') . '-01'),
            'date_end' => AppHelper::convertToThai($lastDay),
            'status' => ['Pending','Checking','Verify','ReqCancel','Allow']
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->joinWith('employee');
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'cid', $searchModel->q],
            ['like', 'email', $searchModel->q],
            ['like', new Expression("concat(fname,' ',lname)"), $searchModel->q],
            ['like', new Expression("JSON_EXTRACT(leave.data_json, '$.reason')"), $searchModel->q],
            ['like', new Expression("JSON_EXTRACT(leave.data_json, '$.leave_work_send')"), $searchModel->q],
        ]);

        if ($searchModel->thai_year !== '' && $searchModel->thai_year !== null) {
            $searchModel->date_start = AppHelper::convertToThai(($searchModel->thai_year - 544) . '-10-01');
            $searchModel->date_end = AppHelper::convertToThai(($searchModel->thai_year - 543) . '-09-30');
        }
        
        try {
      
        $dateStart = AppHelper::convertToGregorian($searchModel->date_start);
        $dateEnd = AppHelper::convertToGregorian($searchModel->date_end);
        $dataProvider->query->andFilterWhere(['>=', 'date_start', $dateStart])->andFilterWhere(['<=', 'date_end', $dateEnd]);              
        } catch (\Throwable $th) {

        }
        if (!empty($searchModel->leave_type_id)) {
            $dataProvider->query->andFilterWhere(['in', 'leave_type_id', $searchModel->leave_type_id]);
        }
        if (!empty($searchModel->status)) {
            $dataProvider->query->andFilterWhere(['in', 'leave.status', $searchModel->status]);
        }
        $dataProvider->query->orderBy(['date_start' => SORT_DESC]);


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
        return $this->render('@app/modules/hr/views/leave/view', [
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
    // public function actionCreate()
    // {
    //     $leaveTypeId = $this->request->get('leave_type_id');
    //     $model = new Leave([
    //         'ref' => substr(\Yii::$app->getSecurity()->generateRandomString(), 10),
    //         'leave_type_id' => $leaveTypeId,
    //         'thai_year' => AppHelper::YearBudget(),
    //     ]);

    //     $model->data_json = [
    //         'title' => $this->request->get('title'),
    //         'address' => $model->CreateBy()->fulladdress,
    //         'leader' => $model->Approve()['leader']['id'],
    //         'leader_group' => $model->Approve()['leaderGroup']['id'],
    //         'phone' => $model->CreateBy()->phone,
    //         'director' => \Yii::$app->site::viewDirector()['id'],
    //         'director_fullname' => \Yii::$app->site::viewDirector()['fullname'],
    //     ];

    //     if ($this->request->isPost) {
    //         if ($model->load($this->request->post())) {
    //             \Yii::$app->response->format = Response::FORMAT_JSON;

    //             $model->thai_year = AppHelper::YearBudget();
    //             $model->date_start = AppHelper::convertToGregorian($model->date_start);
    //             $model->date_end = AppHelper::convertToGregorian($model->date_end);
    //             $model->save();

    //             return $this->redirect(['view', 'id' => $model->id]);
    //             // return [
    //             //     'status' => 'success',
    //             //     'container' => '#leave'
    //             // ];
    //         }
    //     } else {
    //         $model->loadDefaultValues();
    //     }
    //     if ($this->request->isAJax) {
    //         \Yii::$app->response->format = Response::FORMAT_JSON;

    //         return [
    //             'title' => $this->request->get('title'),
    //             'content' => $this->renderAjax('@app/modules/hr/views/leave/create', [
    //                 'model' => $model,
    //             ]),
    //         ];
    //     } else {
    //         return $this->render('create', [
    //             'model' => $model,
    //         ]);
    //     }
    // }

    public function actionCreate()
    {
        $me = UserHelper::GetEmployee();
        $leaveTypeId = $this->request->get('leave_type_id');
        $model = new Leave([
            'ref' => substr(\Yii::$app->getSecurity()->generateRandomString(), 10),
            'leave_type_id' => $leaveTypeId,
            'thai_year' => AppHelper::YearBudget(),
            'on_holidays' => 0
        ]);

        $model->data_json = [
            'title' => $this->request->get('title'),
            'address' => $model->CreateBy()->fulladdress,
            'phone' => $model->CreateBy()->phone,
            'approve_1' => $model->Approve()['approve_1']['id'],
            'approve_2' => $model->Approve()['approve_2']['id'],
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
                    $model->createApprove();
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
                'content' => $this->renderAjax('@app/modules/hr/views/leave/create', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('@app/modules/hr/views/leave/create', [
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

            $model->data_json['date_start_type'] == '' ? $model->addError('data_json[date_start_type]', $requiredName) : null;
            $model->data_json['date_end_type'] == '' ? $model->addError('data_json[date_end_type]', $requiredName) : null;
            $model->data_json['note'] == '' ? $model->addError('data_json[note]', $requiredName) : null;
            $model->data_json['phone'] == '' ? $model->addError('data_json[phone]', $requiredName) : null;
            $model->data_json['location'] == '' ? $model->addError('data_json[location]', $requiredName) : null;
            $model->data_json['address'] == '' ? $model->addError('data_json[address]', $requiredName) : null;
            $model->data_json['delegate'] == '' ? $model->addError('data_json[delegate]', $requiredName) : null;
            $model->data_json['leader'] == '' ? $model->addError('data_json[leader]', $requiredName) : null;
            $model->data_json['leader_group'] == '' ? $model->addError('data_json[leader_group]', $requiredName) : null;
            // $model->unit_price == "" ? $model->addError('unit_price', $requiredName) : null;
        }
        foreach ($model->getErrors() as $attribute => $errors) {
            $result[Yii\helpers\Html::getInputId($model, $attribute)] = $errors;
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
            $model->save();

            return $this->redirect(['view', 'id' => $model->id]);
            // return [
            //     'status' => 'success',
            //     'container' => '#leave'
            // ];
        }

        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('@app/modules/hr/views/leave/update', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('@app/modules/hr/views/leave/update', [
                'model' => $model,
            ]);
        }
    }

    public function actionApprove($id)
    {
        $me = UserHelper::GetEmployee();
        $model = Approve::findOne(["id" => $id, "emp_id" => $me->id]);
        $leave = Leave::findOne($model->from_id);
        if(!$model)
        {
            return [
                'title' => 'แจ้งเตือน',
                'content' => '<h6 class="text-center">ไม่อนุญาติ</h6>',
            ];
        }
        if ($this->request->isPost && $model->load($this->request->post())) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
           
            
            $approveDate = ["approve_date" => date('Y-m-d H:i:s')];
            $model->data_json = ArrayHelper::merge($model->data_json, $approveDate);
            if($model->level == 3){
                $model->emp_id = $me->id;
            }
            
            if($model->save()){
                $nextApprove = Approve::findOne(["from_id" => $model->from_id,'level' => ($model->level+1)]);
                if($nextApprove){
                    $nextApprove->status = 'Pending';
                    $nextApprove->save();
                }
                
                // ถ้า ผอ. อนุมัติ ให้สถานะการลาเป็น Allow
                if($model->level == 4){
                    $leave->status = 'Allow';
                    $leave->save();
                }else if($model->status == 'Reject')
                {
                    $leave->status = 'Reject';
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
                'title' => '<i class="bi bi-person-exclamation"></i> '.$this->request->get('title'),
                'content' => $this->renderAjax('@app/modules/hr/views/leave/form_approve', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('@app/modules/hr/views/leave/form_approve', [
                'model' => $model,
            ]);
        }
    }

    
    // ตรวจสอบความถูกต้อง
    public function actionApproveValidator()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new Approve();
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



    //ประวัติสิทวันลาสะสม
    public function actionPermission()
    {
        $me = UserHelper::GetEmployee();
        // $model = LeavePermission::find()->where(['emp_id' => $me->id])->all();
        $model = LeavePermission::find()->where(['emp_id' => 8])->orderBy([
            'thai_year' => SORT_DESC,
        ])->all();

        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('permission', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('permission', [
                'model' => $model,
            ]);
        }
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
