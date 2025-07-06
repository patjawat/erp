<?php

namespace app\modules\booking\controllers;
use Yii;
use DateTime;
use yii\helpers\Url;
use yii\web\Response;
use yii\web\Controller;
use yii\web\UrlManager;
use yii\filters\VerbFilter;
use app\components\AppHelper;
use yii\web\NotFoundHttpException;
use app\components\DateFilterHelper;
use app\modules\booking\models\Meeting;
use app\modules\booking\models\MeetingSearch;

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
        $lastDay = (new DateTime(date('Y-m-d')))->modify('last day of this month')->format('Y-m-d');
        $status = $this->request->get('status');
        $searchModel = new MeetingSearch([
            'thai_year' => AppHelper::YearBudget(),
            'date_start' => AppHelper::convertToThai(date('Y-m') . '-01'),
            'date_end' => AppHelper::convertToThai($lastDay),
            // 'status' =>  ['Pending'],
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        
         if ($searchModel->date_filter) {
            $range = DateFilterHelper::getRange($searchModel->date_filter);
            $searchModel->date_start = AppHelper::convertToThai($range[0]);
            $searchModel->date_end = AppHelper::convertToThai($range[1]);
        }
        if ($searchModel->thai_year !== '' && $searchModel->date_filter == '') {
            $searchModel->date_start = AppHelper::convertToThai(($searchModel->thai_year - 544) . '-10-01');
            $searchModel->date_end = AppHelper::convertToThai(($searchModel->thai_year - 543) . '-09-30');
        }
        $dataProvider->query
            ->andFilterWhere(['>=', 'date_start', AppHelper::convertToGregorian($searchModel->date_start)])
            ->andFilterWhere(['<=', 'date_end', AppHelper::convertToGregorian($searchModel->date_end)])
            ->orderBy(['date_start' => SORT_DESC]);


        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
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
             if ($this->request->isAJax) {
                 \Yii::$app->response->format = Response::FORMAT_JSON;
             return [
                 'title' => 'รายละเอียดการจอง',
                 'content' => $this->renderAjax('view', [
                     'model' => $model,
                     'action' => true
                 ]),
             ];
         } else {
             return $this->render('view', [
                 'model' => $model,
                 'action' => true
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
        $start = $this->request->get('start');
        $end = $this->request->get('end');

        // Convert start and end dates to the desired format
        $start = (new DateTime($start))->format('Y-m-d');
        $end = (new DateTime($end))->format('Y-m-d');

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $query = Meeting::find()
            ->andWhere(['between', 'date_start', $start, $end])
            ->andWhere(['or', ['status' => 'Pending'], ['status' => 'Pass']])
            ->orderBy(['id' => SORT_DESC]);

        $bookings = $query->all();
        $data = [];

        foreach ($bookings as $item) {
            $dateStart = Yii::$app->formatter->asDatetime(($item->date_start . ' ' . $item->time_start), "php:Y-m-d\TH:i:s");
            $dateEnd = Yii::$app->formatter->asDatetime(($item->date_end . ' ' . $item->time_end), "php:Y-m-d\TH:i:s");
            $data[] = [
                'id' => $item->id,
                'title' => $this->renderAjax('title', ['model' => $item, 'action' => false]),
                'start' => $dateStart,
                'end' => $dateStart,
                'extendedProps' => [
                    'room' => $item->room->title,
                    'dateTime' => $item->viewMeetingTime(),
                    'status' => $item->viewStatus()['view'],
                    'calendar_content' => $this->renderAjax('calendar_content', ['model' => $item, 'action' => false]),
                    'view' => $this->renderAjax('view', ['model' => $item, 'action' => false]),
                    'description' => 'คำอธิบาย',
                ],
                'className' => 'text-truncate px-2 border border-4 border-start border-top-0 border-end-0 border-bottom-0 border-' . $item->viewStatus()['color'],
                'description' => 'description for All Day Event',
                'textColor' => 'black',
                'backgroundColor' => '#3aa3e3',
            ];
        }

        return $data;
    }
        


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
