<?php

namespace app\modules\booking\controllers;

use app\components\AppHelper;
use app\components\DateFilterHelper;
use app\modules\booking\models\Meeting;
use app\modules\booking\models\MeetingSearch;
use app\modules\hr\models\Organization;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * MeetingController implements the CRUD actions for Meeting model.
 */
class MeetingController extends Controller
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

    public function actionExample()
    {
        return $this->render('example');
    }
    /**
     * Lists all Meeting models.
     *
     * @return string
     */
    public function actionDashboard()
    {
        $searchModel = new MeetingSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andWhere(['status' => 'Pending']);

        return $this->render('dashboard', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionIndex()
    {
        $searchModel = new MeetingSearch([
            'date_filter' => 'this_month'
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);

        [$dateStart, $dateEnd] = $this->resolveSearchDateRange($searchModel);

        // Meeting เก็บวันจริงไว้ที่ date_start วันเดียว
        if ($dateStart !== null) {
            $dataProvider->query->andFilterWhere(['>=', 'date_start', $dateStart]);
        }
        if ($dateEnd !== null) {
            $dataProvider->query->andFilterWhere(['<=', 'date_start', $dateEnd]);
        }

        $this->applyDepartmentFilter($dataProvider->query, $searchModel);

        $dataProvider->query->orderBy(['date_start' => SORT_DESC]);


        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * คืนช่วงวันที่สำหรับค้นหาในรูปแบบ Gregorian
     * - ถ้าผู้ใช้กรอก date_start/date_end เองให้ใช้ค่านั้น
     * - ถ้ายังไม่มีค่า ให้เติมจาก date_filter
     *
     * @return array{0:?string,1:?string}
     */
    private function resolveSearchDateRange(MeetingSearch $searchModel): array
    {
        $dateStart = trim((string) ($searchModel->date_start ?? ''));
        $dateEnd = trim((string) ($searchModel->date_end ?? ''));

        $dateStart = $dateStart !== '' ? AppHelper::convertToGregorian($dateStart) : null;
        $dateEnd = $dateEnd !== '' ? AppHelper::convertToGregorian($dateEnd) : null;

        if (($dateStart === null || $dateEnd === null) && trim((string) ($searchModel->date_filter ?? '')) !== '') {
            $range = DateFilterHelper::getRange((string) $searchModel->date_filter);
            if ($range !== null) {
                if ($dateStart === null) {
                    $dateStart = date('Y-m-d', strtotime($range[0]));
                    $searchModel->date_start = AppHelper::convertToThai($dateStart);
                }

                if ($dateEnd === null) {
                    $dateEnd = date('Y-m-d', strtotime($range[1]));
                    $searchModel->date_end = AppHelper::convertToThai($dateEnd);
                }
            }
        }

        return [$dateStart, $dateEnd];
    }

    /**
     * กรองรายการตามหน่วยงานของผู้ขอผ่าน relation employees.department
     * รองรับทั้งการเลือกหน่วยงานตรง ๆ และกรณีเลือกหน่วยงานแม่ที่มีหน่วยงานย่อย
     */
    private function applyDepartmentFilter($query, MeetingSearch $searchModel): void
    {
        $departmentId = trim((string) ($searchModel->q_department ?? ''));
        if ($departmentId === '') {
            return;
        }

        $query->joinWith(['employee']);

        $org = Organization::findOne($departmentId);
        if ($org && (int) $org->lvl === 1) {
            $sql = 'SELECT t1.id
                FROM tree t1
                JOIN tree t2 ON t1.lft BETWEEN t2.lft AND t2.rgt AND t1.lvl = t2.lvl + 1
                WHERE t2.name = :name;';
            $querys = Yii::$app->db->createCommand($sql)
                ->bindValue(':name', $org->name)
                ->queryColumn();

            $arrDepartment = array_values(array_filter(array_map('intval', $querys)));
            if (!empty($arrDepartment)) {
                $arrDepartment[] = (int) $departmentId;
                $query->andWhere(['in', 'employees.department', array_values(array_unique($arrDepartment))]);
                return;
            }
        }

        $query->andWhere(['employees.department' => (int) $departmentId]);
    }


    /**
     * Displays a single Meeting model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */

    public function actionView($id)
    {
        $model = $this->findModel($id);
        // return $this->asJson($model);
        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                // 'title' => 'คำขอใช้ห้องประชุมที่#'.$model->code,
                'title' => $model->getUserReq()['avatar'],
                'content' => $this->renderAjax('@app/modules/booking/views/meeting/view', [
                    'model' => $model,
                    'action' => false
                ]),
            ];
        } else {
            return $this->render('@app/modules/booking/views/meeting/view', [
                'model' => $model,
                'action' => false
            ]);
        }
    }

    /**
     * Creates a new Meeting model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Meeting();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Meeting model.
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

    /**
     * Deletes an existing Meeting model.
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

    public function actionCalendar()
    {
        return $this->render('calendar');
    }



    public function actionEvents()
    {
        $start = Yii::$app->formatter->asDate($this->request->get('start'), 'php:Y-m-d');
        $end =  Yii::$app->formatter->asDate($this->request->get('end'), 'php:Y-m-d');
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $bookings = Meeting::find()
            ->andWhere(['between', 'date_start', $start, $end])
            ->orderBy(['id' => SORT_DESC])
            ->all();
        $data = [];


        foreach ($bookings as $item) {
            try {

                $timeStart = $item->time_start ?? '00:00';
                $timeEnd = $item->time_end ?? '00:00';
                $dateStart = Yii::$app->formatter->asDatetime(($item->date_start . ' ' . $timeStart), "php:Y-m-d\TH:i");
                $dateEnd = Yii::$app->formatter->asDatetime(($item->date_start . ' ' . $timeEnd), "php:Y-m-d\TH:i");
                $data[] = [
                    'id'               => $item->id,
                    'title'            => $item->title,
                    'start'            => $dateStart,
                    // 'time_start' => $timeStart,
                    'end'            => $dateEnd,
                    'time_end' => $timeEnd,
                    'allDay' => false,
                    'source' => 'vehicle',
                    'extendedProps' => [
                        'title' => $this->renderAjax('@app/modules/booking/views/meeting/view_title', ['model' => $item]),
                        'code' => $item->code,
                        'color' => (isset($item->room) && isset($item->room->data_json['color'])) ? $item->room->data_json['color'] : '',
                    ],
                ];
                //code...
            } catch (\Throwable $th) {
                //throw $th;
            }
        }

        return  [
            'events' => $data
        ];
    }

    // public function actionEvents()
    // {
    //     $start = $this->request->get('start');
    //     $end = $this->request->get('end');

    //     // Convert start and end dates to the desired format
    //     $start = (new DateTime($start))->format('Y-m-d');
    //     $end = (new DateTime($end))->format('Y-m-d');

    //     \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    //     $query = Meeting::find()
    //         ->andWhere(['between', 'date_start', $start, $end])
    //         ->andWhere(['or', ['status' => 'Pending'], ['status' => 'Pass']])
    //         ->orderBy(['id' => SORT_DESC]);

    //     $bookings = $query->all();
    //     $data = [];

    //     foreach ($bookings as $item) {
    //         try {
    //         $dateStart = Yii::$app->formatter->asDatetime(($item->date_start . ' ' . $item->time_start), "php:Y-m-d\TH:i:s");
    //         $dateEnd = Yii::$app->formatter->asDatetime(($item->date_end . ' ' . $item->time_end), "php:Y-m-d\TH:i:s");
    //         $data[] = [
    //             'id' => $item->id,
    //             'title' => $this->renderAjax('view_title', ['model' => $item, 'action' => false]),
    //             'start' => $dateStart,
    //             'end' => $dateStart,
    //             'extendedProps' => [
    //                 'room' => $item->room->title,
    //                 'dateTime' => $item->viewMeetingTime(),
    //                 'status' => $item->viewStatus()['view'],
    //                 'calendar_content' => $this->renderAjax('calendar_content', ['model' => $item, 'action' => false]),
    //                 'view' => $this->renderAjax('view', ['model' => $item, 'action' => false]),
    //                 'description' => 'คำอธิบาย',
    //             ],
    //             'className' => 'text-truncate px-2 border border-4 border-start border-top-0 border-end-0 border-bottom-0 border-' . $item->viewStatus()['color'],
    //             'description' => 'description for All Day Event',
    //             'textColor' => 'black',
    //             'backgroundColor' => '#3aa3e3',
    //         ];
    //                         //code...
    //         } catch (\Throwable $th) {
    //             //throw $th;
    //         }
    //     }

    //     return $data;
    // }



    /**
     * Finds the Meeting model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Meeting the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Meeting::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
