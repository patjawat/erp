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
use yii\helpers\ArrayHelper;
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
        if($searchModel){
            $dataProvider->query->andFilterWhere(['=', new Expression("JSON_EXTRACT(data_json, '$.thai_year')"), $searchModel->thai_year]);
        }else{
            $dataProvider->query->andFilterWhere(['=', new Expression("JSON_EXTRACT(data_json, '$.thai_year')"), AppHelper::YearBudget()]);
        }

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

        // ตรวจสอบความถูกต้อง
        public function actionValidator()
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $model = new Holiday();
    
            if ($this->request->isPost && $model->load($this->request->post())) {
                $requiredName = 'ต้องระบุ';
    
                if (isset($model->data_json['date'])) {
                    preg_replace('/\D/', '', $model->data_json['date']) == "" ? $model->addError('data_json[date]', $requiredName) : null;
                }
                $model->data_json['thai_year'] == '' ? $model->addError('data_json[thai_year]', $requiredName) : null;
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
     * Creates a new Holiday model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {

        $model = new Holiday();

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                $convertDate =[
                    'date' =>  AppHelper::convertToGregorian($model->data_json['date']),
                ];
                $model->data_json =  ArrayHelper::merge($model->data_json,$convertDate);
                $model->save(false);
                return [
                    'status' => 'success',
                    'container' => '#leave'
                ];
            }
        } else {
            $model->loadDefaultValues();
            $model->data_json = [
                'thai_year' => AppHelper::YearBudget()
            ];
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
        $oldObj = $model->data_json;
        if ($this->request->isPost && $model->load($this->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $convertDate =[
                'date' =>  AppHelper::convertToGregorian($model->data_json['date']),
            ];
            $model->data_json =  ArrayHelper::merge($oldObj,$model->data_json,$convertDate);
            $model->save();
            return [
                'status' => 'success',
                'container' => '#leave'
            ];
        }

        try {
            $convertDate = [
                'date' =>  AppHelper::convertToThai($model->data_json['date']),
            ];
            $model->data_json =  ArrayHelper::merge($oldObj,$model->data_json,$convertDate);
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
                    'thai_year' => (string) AppHelper::YearBudget($holidayDate),
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
