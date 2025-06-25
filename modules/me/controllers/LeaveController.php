<?php

namespace app\modules\me\controllers;

use Yii;
use DateTime;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\Response;
use yii\db\Expression;
use yii\web\Controller;
use app\components\LineMsg;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\hr\models\Leave;
use yii\web\NotFoundHttpException;
use app\components\DateFilterHelper;
use app\models\Calendar;
use app\modules\hr\models\LeaveSearch;
use app\modules\approve\models\Approve;
use app\modules\hr\models\Organization;
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
        $status = $this->request->get('status');
        $searchModel = new LeaveSearch([
            'emp_id' => $me->id,
            'thai_year' => AppHelper::YearBudget(),
            'date_start' => AppHelper::convertToThai(date('Y-m') . '-01'),
            'date_end' => AppHelper::convertToThai($lastDay),
            // 'status' =>   $status ? [$status] : ['Pending']
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->joinWith('employee');

        $dataProvider->query->andFilterWhere(['emp_id' => $me->id]);
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'cid', $searchModel->q],
            ['like', 'email', $searchModel->q],

            ['like', new Expression("concat(fname,' ',lname)"), $searchModel->q],
            ['like', new Expression("JSON_EXTRACT(leave.data_json, '$.reason')"), $searchModel->q],
            ['like', new Expression("JSON_EXTRACT(leave.data_json, '$.leave_work_send')"), $searchModel->q],
        ]);

        if ($searchModel->date_filter) {
            $range = DateFilterHelper::getRange($searchModel->date_filter);
            $searchModel->date_start = AppHelper::convertToThai($range[0]);
            $searchModel->date_end = AppHelper::convertToThai($range[1]);
        }

        if ($searchModel->thai_year !== '' && $searchModel->date_filter == '') {
            $searchModel->date_start = AppHelper::convertToThai(($searchModel->thai_year - 544) . '-10-01');
            $searchModel->date_end = AppHelper::convertToThai(($searchModel->thai_year - 543) . '-09-30');
        }

        if (!$searchModel->date_filter && !$searchModel->thai_year) {
            $dateStart = AppHelper::convertToGregorian($searchModel->date_start);
            $dateEnd = AppHelper::convertToGregorian($searchModel->date_end);
            $dataProvider->query->andFilterWhere(['>=', 'date_start', $dateStart])->andFilterWhere(['<=', 'date_end', $dateEnd]);
        }



        if (!empty($searchModel->leave_type_id)) {
            $dataProvider->query->andFilterWhere(['in', 'leave_type_id', $searchModel->leave_type_id]);
        }
        // if (!empty($searchModel->status)) {
        //     $dataProvider->query->andFilterWhere(['in', 'leave.status', $searchModel->status]);
        // }
        if ($status) {
            $dataProvider->query->andFilterWhere(['leave.status' => $searchModel->status]);
        }



        // $dataProvider->sort->defaultOrder = ['date_start' => SORT_DESC];

        $dataProvider->setSort(['defaultOrder' => [
            // 'total_days' => SORT_DESC,
            'created_at' => SORT_DESC,
        ]]);


        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            // 'dateStart' => $dateStart,
            // 'dateEnd' => $dateEnd,
        ]);
    }


    //ลบได้
    public function actionIndexOld()
    {
        $me = UserHelper::GetEmployee();
        $lastDay = (new DateTime(date('Y-m-d')))->modify('last day of this month')->format('Y-m-d');
        $searchModel = new LeaveSearch([
            // 'emp_id' => $me->id,
            'thai_year' => AppHelper::YearBudget(),
            'date_start' => AppHelper::convertToThai(date('Y-m') . '-01'),
            'date_end' => AppHelper::convertToThai($lastDay),
            // 'status' => ['Pending', 'Checking', 'Verify', 'ReqCancel', 'Approve']
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->joinWith('employee');
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'cid', $searchModel->q],
            ['like', 'email', $searchModel->q],
            ['like', new Expression("concat(fname,' ',lname)"), $searchModel->q],
            ['like', new Expression("JSON_EXTRACT(leave.data_json, '\$.reason')"), $searchModel->q],
            ['like', new Expression("JSON_EXTRACT(leave.data_json, '\$.leave_work_send')"), $searchModel->q],
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

        $dataProvider->query->orderBy(['id' => SORT_DESC]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    //ขอยกเลิกวันลา
    public function actionReqCancel($id)
    {
        $me = UserHelper::GetEmployee();
        $model = $this->findModel($id);
        if ($this->request->isPost && $me->user_id == $model->created_by) {
            $model->status = "ReqCancel";
            $model->save();
            return $this->redirect(['/me/leave']);
        }
    }


    public function actionCalendar()
    {

        return $this->render('calendar');
    }

    //ทะเบียนแสดงบนปฏิทินการลา
    public function actionEvents()
    {
        $start = $this->request->get('start');
        $end = $this->request->get('end');
        $department = $this->request->get('department');

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $leaves = Leave::find();
        $leaves->joinWith('employee');
        $leaves->andWhere(['>=', 'date_start', $start])->andWhere(['<=', 'date_end', $end]);
        $leaves->orderBy(['id' => SORT_DESC]);

        if ($department) {
            $org1 = Organization::findOne($department);
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
                    $leaves->andWhere(['in', 'department', $arrDepartment]);
                } else {
                    $leaves->andWhere(['department' => $department]);
                }
            } else {
                $leaves->andWhere(['department' => $department]);
            }
        }

        $events = $leaves->all();

        $data = [];

        foreach ($events as $item) {
            $dateStart = Yii::$app->formatter->asDatetime(($item->date_start . ' 00:00'), "php:Y-m-d\TH:i");

            // เพิ่ม 1 วันให้ date_end
            $dateEndRaw = date('Y-m-d', strtotime($item->date_end . ' +1 day'));
            $dateEnd = Yii::$app->formatter->asDatetime(($dateEndRaw . ' 00:00'), "php:Y-m-d\TH:i");

            $color = '';
            if (isset($item->leaveType) && isset($item->leaveType->data_json['color'])) {
                $color = $item->leaveType->data_json['color'];
            }

            $data[] = [
                'id' => $item->id,
                'title' => isset($item->data_json['reason']) ? $item->data_json['reason'] : '',
                'start' => $dateStart,
                'end' => $dateEnd,
                'allDay' => false,
                'source' => 'leave',
                'extendedProps' => [
                    'title' => isset($item->data_json['reason']) ? $item->data_json['reason'] : '',
                    'avatar' => $this->renderAjax('@app/modules/hr/views/leave/calendar/leave-item', ['item' => $item]),
                    'department' => $item->employee->department ?? null,
                    'color' => '#009688',
                    // 'color' => (isset($item->data_json['color']) ?$item->data_json['color'] : '') ,
                ],
                'description' => 'description for All Day Event',
                'textColor' => 'black',
                'backgroundColor' => $color,
                'url' => Url::to(['/me/leave/view', 'id' => $item->id]),

            ];
        }

        $holidays = Calendar::find()->where(['name' => 'holiday'])
            ->andWhere(['between', 'date_start', $start, $end])->all();
        foreach ($holidays as $holiday) {
             $dateStart = Yii::$app->formatter->asDatetime(($holiday->date_start . ' 00:00'), "php:Y-m-d\TH:i");
            // เพิ่ม 1 วันให้ date_end
            $dateEndRaw = date('Y-m-d', strtotime($holiday->date_start . ' +1 day'));
            $dateEnd = Yii::$app->formatter->asDatetime(($dateEndRaw . ' 00:00'), "php:Y-m-d\TH:i");

            $data[] = [
                'id' => $holiday->id,
                'title' => $holiday->title,
                'start' => $dateStart,
                'end' => $dateEnd,
                'allDay' => false,
                 'source' => 'holiday',
                  'backgroundColor' => '#ff9800',
                'extendedProps' => [
                    'title' => 'ttt',
                    'avatar' => '<span class=""><i class="fa-regular fa-bell"></i>'.$holiday->title.'</span>',
                    'department' => '',
                    'color' => '',
                    // 'color' => (isset($item->data_json['color']) ?$item->data_json['color'] : '') ,
                ],

            ];
        }
        return $data;
    }


    public function actionHoliday()
    {
        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('holiday', []),
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
        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            $model = $this->findModel($id);
            return [
                'title' => $model->employee->getAvatar(false),
                'content' => $this->renderAjax('@app/modules/hr/views/leave/view', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('@app/modules/hr/views/leave/view', [
                'model' => $this->findModel($id),
            ]);
        }
    }

    public function actionTypeSelect()
    {
        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('type_select', []),
            ];
        } else {
            return $this->render('type_select', []);
        }
    }

    public function actionCreate()
    {
        $me = UserHelper::GetEmployee();
        $leaveTypeId = $this->request->get('leave_type_id');
        $dateStart = AppHelper::convertToThai($this->request->get('date_start')) ?? '';
        $dateEnd = AppHelper::convertToThai($this->request->get('date_end')) ?? '';
        $model = new Leave([
            'ref' => substr(\Yii::$app->getSecurity()->generateRandomString(), 10),
            'leave_type_id' => $leaveTypeId,
            'date_start' => $dateStart,
            'date_end' => $dateEnd,
            'thai_year' => AppHelper::YearBudget(),
            'on_holidays' => 0,
            'total_days' => 0
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

                if ($model->save()) {
                    $model->createApprove();
                }

                return $this->redirect(['/me/leave']);
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
        $me = UserHelper::GetEmployee();
        $model = $this->findModel($id);
        $model->date_start = AppHelper::convertToThai($model->date_start);
        $model->date_end = AppHelper::convertToThai($model->date_end);

        if ($this->request->isPost && $model->load($this->request->post())) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            $model->date_start = AppHelper::convertToGregorian($model->date_start);
            $model->date_end = AppHelper::convertToGregorian($model->date_end);
            $model->save();

            return $this->redirect(['/me/leave']);
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
        $model = Approve::findOne(['id' => $id, 'emp_id' => $me->id]);
        $model->status = Yii::$app->request->get('status');
        $old_json = $model->data_json;
        $leave = Leave::findOne($model->from_id);
        if (!$model) {
            return [
                'title' => 'แจ้งเตือน',
                'content' => '<h6 class="text-center">ไม่อนุญาต</h6>',
            ];
        }
        if ($this->request->isPost && $model->load($this->request->post())) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            $approveDate = ['approve_date' => date('Y-m-d H:i:s')];
            $model->data_json = ArrayHelper::merge($old_json, $model->data_json, $approveDate);
            if ($model->level == 3) {
                $model->emp_id = $me->id;
            }

            if ($model->save()) {



                $nextApprove = Approve::findOne(['from_id' => $model->from_id, 'level' => ($model->level + 1)]);
                if ($nextApprove && $model->status == 'Approve') {
                    $nextApprove->status = 'Pending';
                    $nextApprove->save();
                }

                $lineId = $model->leave->employee->user->line_id;

                // ถ้า ผอ. อนุมัติ ให้สถานะการลาเป็น Allow
                if ($model->level == 4 &&  $leave->status = 'Allow') {
                    $leave->status = 'Allow';
                    $leave->save();
                    $message = 'อนุมัติให้' . ($model->leave->leaveType->title ?? '-') . ' วันที่ ' . Yii::$app->thaiFormatter->asDate($leave->date_start, 'long') . ' ถึง ' . Yii::$app->thaiFormatter->asDate($leave->date_end, 'long');
                    LineMsg::sendMsg($lineId, $message);
                }

                if ($model->status == 'Reject') {
                    $leave->status = 'Reject';
                    $leave->save();

                    $message = 'ไม่' . $model->data_json['topic'] . 'ให้ลาเนื่องจาก' . $model->data_json['note'];
                    LineMsg::sendMsg($lineId, $message);
                }

                // else {
                //     $leave->status = 'Checking';
                //     $leave->save();
                // }

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

    // ประวัติสิทวันลาสะสม
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
