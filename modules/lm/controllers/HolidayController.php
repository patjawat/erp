<?php

namespace app\modules\lm\controllers;

use app\components\AppHelper;
use app\modules\lm\models\Holiday;
use app\modules\lm\models\HolidaySearch;
use DateTime;
use yii\db\Expression;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use Yii;
use yii\web\Response;

/**
 * HolidayController implements the CRUD actions for Holiday model.
 */
class HolidayController extends Controller
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
     * Lists all Holiday models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new HolidaySearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andWhere(['name' => 'holiday']);
        $dataProvider->query->andWhere(['=', new Expression("JSON_EXTRACT(data_json, '$.thai_year')"), AppHelper::YearBudget()]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Holiday model.
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

    /**
     * Creates a new Holiday model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Holiday();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
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

    /**
     * Updates an existing Holiday model.
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
     * Deletes an existing Holiday model.
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


    public function actionSyncDate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $url = "https://www.myhora.com/calendar/ical/holiday.aspx?latest.json";
            // ดึงข้อมูล JSON จาก URL
            $json = file_get_contents($url);
            // แปลง JSON เป็น array
            $data = json_decode($json, true);
            foreach ($data['VCALENDAR'][0]['VEVENT'] as $holiday) {
                $dateString =  $holiday['DTSTART;VALUE=DATE'];
                $date = DateTime::createFromFormat('Ymd', $dateString);
                // แปลงเป็นรูปแบบ Y-m-d
                $holidayDate = $date->format('Y-m-d');

                $checkDay = Holiday::find()
                ->where(['name' => 'holiday','title' => $holiday['SUMMARY']])
                ->andWhere(['=', new Expression("JSON_EXTRACT(data_json, '$.date')"), $holidayDate])
                ->one();
                $model = $checkDay ? $checkDay : new Holiday;

                $model->title = $holiday['SUMMARY'];
                $model->name = 'holiday';
                $model->data_json = [
                    'thai_year' => AppHelper::YearBudget($holidayDate),
                    'date' => $holidayDate
                ];
                $model->save();
    }

    return [
        'status' => 'success',
        'container' => '#leave'
    ];
    }


    protected function findModel($id)
    {
        if (($model = Holiday::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
