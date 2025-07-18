<?php

namespace app\modules\me\controllers;

use Yii;
use yii\web\Response;
use yii\db\Expression;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\am\models\Asset;
use app\components\DateFilterHelper;
use app\modules\helpdesk\models\Helpdesk;
use app\modules\helpdesk\models\HelpdeskSearch;

class RepairController extends Controller
{
    public function actionIndex()
    {
        $userId = \Yii::$app->user->id;
         $emp = UserHelper::GetEmployee();
        $searchModel = new HelpdeskSearch([
            'created_by' => $userId,
            'emp_id' => $emp->id,
            'date_filter' => 'this_month',
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'title', $searchModel->q],
            ['like', new Expression("JSON_EXTRACT(data_json, '\$.repair_note')"), $searchModel->q],
        ]);
        // $dataProvider->query->andFilterWhere(['between', 'created_at', '2024-01-01', '2024-01-03']);

             if ($searchModel->date_filter) {
            $range = DateFilterHelper::getRange($searchModel->date_filter);
            $searchModel->date_start = AppHelper::convertToThai($range[0]);
            $searchModel->date_end = AppHelper::convertToThai($range[1]);
        }
        $dataProvider->query->andFilterWhere(['between', new \yii\db\Expression('DATE(created_at)'), AppHelper::convertToGregorian($searchModel->date_start),AppHelper::convertToGregorian($searchModel->date_end)]);

        
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'summary' => $dataProvider->getTotalCount(),
        ]);
    }

    public function actionView($id)
    {
        // Yii::$app->response->format = Response::FORMAT_JSON;
        $title = $this->request->get('title');
        $model = $this->findModel($id);
        $asset = Asset::findOne(['code' => $model->code]);
        if ($model->code && isset($asset)) {
             if ($this->request->isAJax) {
                \Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'title' => '<i class="fa-solid fa-screwdriver-wrench"></i> ข้อมูลแจ้งซ่อม',
                    'content' => $this->renderAjax('view', [
                        'model' => $model,
                        'asset' => $asset
                    ]),
                ];
            }
        } else {
            if ($this->request->isAJax) {
                \Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'title' => '<i class="fa-solid fa-screwdriver-wrench"></i> ข้อมูลแจ้งซ่อม',
                    'content' => $this->renderAjax('view_general', [
                        'model' => $model,
                    ]),
                ];
            } else {
                return $this->render('view_general', [
                    'model' => $model,
                ]);
            }
        }
    }


    // แจ้งซ่อมใหม่
    public function actionCreate()
    {
        $model = new Helpdesk(['name' => 'repair']);
          if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;

                $model->save();

               return $this->redirect(['/me/repair']);
            }
        } else {
            $model->loadDefaultValues();
        }
        
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('create', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('create', ['model' => $model]);
        }
    }
    

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $oldObj = $model->data_json;
        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;

                try {
                    if ($model->status == 4) {
                        $asset = Asset::findOne(['code' => $model->code]);
                        $asset->asset_status = 1;
                        $asset->save();
                    }
                } catch (\Throwable $th) {
                    // throw $th;
                }

               $model->save();

               return $this->redirect(['/me/repair']);
            }
        } else {

            $model->loadDefaultValues();

        }
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('_form', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('_form', ['model' => $model]);
        }
    }
    

    protected function findModel($id)
    {
        if (($model = Helpdesk::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    
}