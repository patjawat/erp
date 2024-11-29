<?php

namespace app\modules\lm\controllers;

use app\components\AppHelper;
use app\models\CalendarSearch;
use app\models\Calendar;
use DateTime;
use yii\db\Expression;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\Response;

/**
 * CalendarController implements the CRUD actions for Calendar model.
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
     * Lists all Calendar models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new CalendarSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andWhere(['name' => 'holiday']);
        if($searchModel){
            $dataProvider->query->andFilterWhere(['=','thai_year', $searchModel->thai_year]);
        }else{
            $dataProvider->query->andFilterWhere(['=','thai_year', AppHelper::YearBudget()]);
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Calendar model.
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

        // ตรวจสอบความถูกต้อง
        public function actionValidator()
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $model = new Calendar();
    
            if ($this->request->isPost && $model->load($this->request->post())) {
                $requiredName = 'ต้องระบุ';
    
                preg_replace('/\D/', '',$model->date_start) == '' ? $model->addError('date_start', $requiredName) : null;
                $model->thai_year == '' ? $model->addError('thai_year', $requiredName) : null;
                $model->title == '' ? $model->addError('title', $requiredName) : null;
    
                foreach ($model->getErrors() as $attribute => $errors) {
                    $result[\yii\helpers\Html::getInputId($model, $attribute)] = $errors;
                }
                if (!empty($result)) {
                    return $this->asJson($result);
                }
            }
        }

        
    /**
     * Creates a new Calendar model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {

        $model = new Calendar();

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                $model->date_start = AppHelper::convertToGregorian( $model->date_start);
                $model->save(false);
                return [
                    'status' => 'success',
                    'container' => '#leave'
                ];
            }
        } else {
            $model->loadDefaultValues();
            $model->thai_year = AppHelper::YearBudget();
            
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
     * Updates an existing Calendar model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        
        if ($this->request->isPost && $model->load($this->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $model->date_start = AppHelper::convertToGregorian( $model->date_start);
            $model->save();
            return [
                'status' => 'success',
                'container' => '#leave'
            ];
        }
        
        try {
            $model->date_start = AppHelper::convertToThai( $model->date_start);

        } catch (\Throwable $th) {
            //throw $th;
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
     * Deletes an existing Calendar model.
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
            foreach ($data['VCALENDAR'][0]['VEVENT'] as $Calendar) {
                $dateString =  $Calendar['DTSTART;VALUE=DATE'];
                $date = DateTime::createFromFormat('Ymd', $dateString);
                // แปลงเป็นรูปแบบ Y-m-d
                $CalendarDate = $date->format('Y-m-d');

                $checkDay = Calendar::find()
                ->where(['name' => 'holiday','date_start' => $CalendarDate])
                ->one();
                if(!$checkDay){
                    $model =  new Calendar;
                    $model->title = $Calendar['SUMMARY'];
                    $model->name = 'holiday';
                    $model->thai_year = AppHelper::YearBudget($CalendarDate);
                    $model->date_start = $CalendarDate;
                   
                    $model->save();
                }
            }
            return [
                'status' => 'success',
                'container' => '#lm'
            ];
    }


    protected function findModel($id)
    {
        if (($model = Calendar::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
