<?php

namespace app\modules\leave\controllers;

use Yii;
use DateTime;
use yii\web\Response;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use app\components\AppHelper;
use app\models\Calendar;
use app\models\CalendarSearch;

/**
 * วันหยุด — ย้ายจาก hr
 */
class HolidayController extends Controller
{
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        if (!Yii::$app->user->can('leave')) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์เข้าหน้าตั้งค่า');
        }
        return true;
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => ['delete' => ['POST']],
            ],
        ]);
    }

    public function actionIndex()
    {
        $searchModel = new CalendarSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andWhere(['name' => 'holiday']);
        $dataProvider->sort->defaultOrder = ['date_start' => SORT_DESC];
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        return $this->render('view', ['model' => $this->findModel($id)]);
    }

    public function actionValidator()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new Calendar();
        if ($this->request->isPost && $model->load($this->request->post())) {
            $requiredName = 'ต้องระบุ';
            preg_replace('/\D/', '', $model->date_start) == '' ? $model->addError('date_start', $requiredName) : null;
            $model->title == '' ? $model->addError('title', $requiredName) : null;
            $result = [];
            foreach ($model->getErrors() as $attribute => $errors) {
                $result[\yii\helpers\Html::getInputId($model, $attribute)] = $errors;
            }
            if (!empty($result)) {
                return $this->asJson($result);
            }
        }
    }

    public function actionCreate()
    {
        $model = new Calendar();
        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                $model->date_start = AppHelper::convertToGregorian($model->date_start);
                $model->save(false);
                return ['status' => 'success', 'container' => '#leave'];
            }
        } else {
            $model->loadDefaultValues();
            $model->thai_year = AppHelper::YearBudget();
        }
        if ($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('create', ['model' => $model]),
            ];
        }
        return $this->render('create', ['model' => $model]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if ($this->request->isPost && $model->load($this->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $model->date_start = AppHelper::convertToGregorian($model->date_start);
            $model->save();
            return ['status' => 'success', 'container' => '#leave'];
        }
        try {
            $model->date_start = AppHelper::convertToThai($model->date_start);
        } catch (\Throwable $th) {}
        if ($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('update', ['model' => $model]),
            ];
        }
        return $this->render('update', ['model' => $model]);
    }

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }

    public function actionSyncDate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $url = 'https://www.myhora.com/calendar/ical/holiday.aspx?latest.json';
        $json = @file_get_contents($url);
        if ($json === false || $json === '') {
            return ['status' => 'error', 'message' => 'ไม่สามารถดึงข้อมูลวันหยุดได้'];
        }
        $data = json_decode($json, true);
        // myhora บางช่วงส่ง JSON ผิดรูปแบบ (คั่นอ็อบเจ็กต์ใน VEVENT ด้วย }{ แทน },{)
        if (!is_array($data)) {
            $repaired = preg_replace('/}\s*{/', '},{', $json);
            $data = json_decode($repaired, true);
        }
        if (!is_array($data)) {
            return ['status' => 'error', 'message' => 'รูปแบบข้อมูลวันหยุดไม่ถูกต้อง'];
        }
        $vevents = $data['VCALENDAR'][0]['VEVENT'] ?? null;
        if (!is_array($vevents)) {
            return ['status' => 'error', 'message' => 'ไม่พบรายการวันหยุดในแหล่งข้อมูล'];
        }
        foreach ($vevents as $Calendar) {
            if (!is_array($Calendar)) {
                continue;
            }
            $dateString = $Calendar['DTSTART;VALUE=DATE'] ?? null;
            if ($dateString === null || $dateString === '') {
                continue;
            }
            $date = DateTime::createFromFormat('Ymd', (string) $dateString);
            if ($date === false) {
                continue;
            }
            $CalendarDate = $date->format('Y-m-d');
            $checkDay = Calendar::find()->where(['name' => 'holiday', 'date_start' => $CalendarDate])->one();
            if (!$checkDay) {
                $model = new Calendar();
                $model->title = $Calendar['SUMMARY'] ?? '';
                $model->name = 'holiday';
                $model->thai_year = AppHelper::YearBudget($CalendarDate);
                $model->date_start = $CalendarDate;
                $model->save();
            }
        }
        return ['status' => 'success', 'container' => '#leave'];
    }

    protected function findModel($id)
    {
        if (($model = Calendar::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
