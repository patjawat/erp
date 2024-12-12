<?php

namespace app\modules\hr\controllers;

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
use app\modules\hr\models\LeaveStep;
use app\modules\hr\models\LeaveSearch;
use app\modules\hr\models\Organization;
use app\modules\hr\models\LeavePermission;
use app\modules\hr\models\LeaveSummarySearch;

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
        $lastDay = (new DateTime(date('Y-m-d')))->modify('last day of this month')->format('Y-m-d');
        $searchModel = new LeaveSearch([
            'thai_year' => AppHelper::YearBudget(),
            'date_start' => AppHelper::convertToThai(date('Y-m') . '-01'),
            'date_end' => AppHelper::convertToThai($lastDay)
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
        
        $dateStart = AppHelper::convertToGregorian($searchModel->date_start);
        $dateEnd = AppHelper::convertToGregorian($searchModel->date_end);
        $dataProvider->query->andFilterWhere(['>=', 'date_start', $dateStart])
          ->andFilterWhere(['<=', 'date_end', $dateEnd]);
        
        if (!empty($searchModel->leave_type_id)) {
            $dataProvider->query->andFilterWhere(['in', 'leave_type_id', $searchModel->leave_type_id]);
        }
        
        // search employee department
         // ค้นหาคามกลุ่มโครงสร้าง
         $org1 = Organization::findOne($searchModel->q_department);
         // ถ้ามีกลุ่มย่อย
         if (isset($org1) && $org1->lvl == 1) {
             $sql = 'SELECT t1.id, t1.root, t1.lft, t1.rgt, t1.lvl, t1.name, t1.icon
             FROM tree t1
             JOIN tree t2 ON t1.lft BETWEEN t2.lft AND t2.rgt AND t1.lvl = t2.lvl + 1
             WHERE t2.name = :name;';
             $querys = Yii::$app
                 ->db
                 ->createCommand($sql)
                 ->bindValue(':name', $org1->name)
                 ->queryAll();
             $arrDepartment = [];
             foreach ($querys as $tree) {
                 $arrDepartment[] = $tree['id'];
             }
             if (count($arrDepartment) > 0) {
                 $dataProvider->query->andWhere(['in', 'department', $arrDepartment]);
             } else {
                 $dataProvider->query->andFilterWhere(['department' => $searchModel->q_department]);
             }
         } else {
             $dataProvider->query->andFilterWhere(['department' => $searchModel->q_department]);
         }
       
        
        $dataProvider->sort->defaultOrder = ['id' => SORT_DESC];

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            // 'dateStart' => $dateStart,
            // 'dateEnd' => $dateEnd,
        ]);
    }


    public function actionDashboard()
    {
        $searchModel = new LeaveSummarySearch([
            'thai_year' => AppHelper::YearBudget()
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->groupBy('code');
        return $this->render('dashboard/index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
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
            $model->data_json['approve_1'] == '' ? $model->addError('data_json[approve_1]', $requiredName) : null;
            $model->data_json['approve_2'] == '' ? $model->addError('data_json[approve_2]', $requiredName) : null;
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
        $date_start_type = (Float) ($this->request->get('date_start_type') == "1" ? 0 : 0.5);
        $date_end_type = (Float) ($this->request->get('date_end_type') == "1" ? 0 : 0.5);
        $auto = $this->request->get('auto');

        $date_start = preg_replace('/\D/', '', $this->request->get('date_start'));
        $date_end = preg_replace('/\D/', '', $this->request->get('date_end'));

        $dateStart = $date_start == '' ? '' : AppHelper::convertToGregorian($this->request->get('date_start'));
        $dateEnd = $date_end == '' ? '' : AppHelper::convertToGregorian($this->request->get('date_end'));
        // return $dateStart.' '.$dateEnd;
        $model = AppHelper::CalDay($dateStart, $dateEnd);
        
        
        \Yii::$app->response->format = Response::FORMAT_JSON;
        // return $auto;
        if($auto == "1"){
            $total = (($model['sunDay'] + $model['summaryDay'])-($date_start_type+$date_end_type));
        }else{
            $total = ($model['summaryDay']-($date_start_type+$date_end_type));
        }

        return [
            $model,
            'total' => $total,
            'start_type' => $date_start_type,
            'start_end' => $date_end_type,
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
        $me = UserHelper::GetEmployee();
        // $model = Approve::findOne(["id" => $id, "emp_id" => $me->id]);
        $model = Approve::findOne(["id" => $id]);
        $nextApprove = Approve::findOne(["from_id" => $model->from_id,'level' => ($model->level+1)]);
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
    public function actionCancel($id)
    {
        $model = $this->findModel($id);
        $model->status = "Cancel";
        $model->save();

        return $this->redirect(['view', 'id' => $model->id]);
    }

    public function actionReqCancel($id)
    {
        $model = $this->findModel($id);
        $model->status = "ReqCancel";
        $model->save();

        return $this->redirect(['view', 'id' => $model->id]);
    }

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
