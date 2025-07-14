<?php

namespace app\modules\helpdesk\controllers;

use Yii;
use yii\web\Response;
use yii\db\Expression;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use yii\web\NotFoundHttpException;
use app\components\DateFilterHelper;
use app\modules\helpdesk\models\Helpdesk;
use app\modules\helpdesk\models\HelpdeskSearch;

class ComputerController extends \yii\web\Controller
{

    public function actionDemo()
    {
        return $this->render('demo');
    }
    public function actionIndex()
    {

        $searchModel = new HelpdeskSearch([
            'thai_year' => AppHelper::YearBudget(),
            'repair_group' => 2,
            'date_filter' => 'this_month',
            'status' => 1
            // 'auth_item' => 'computer',
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'repair']);
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'code', $searchModel->q],
            ['like', new Expression("JSON_EXTRACT(data_json, '$.title')"), $searchModel->q],
            ['like', new Expression("JSON_EXTRACT(data_json, '$.repair_note')"), $searchModel->q],
            ['like', new Expression("JSON_EXTRACT(data_json, '$.note')"), $searchModel->q],
        ]);
        $dataProvider->query->andFilterWhere(['=', new Expression("JSON_EXTRACT(data_json, '$.urgency')"), $searchModel->urgency]);

        if ($searchModel->date_filter) {
            $range = DateFilterHelper::getRange($searchModel->date_filter);
            $searchModel->date_start = AppHelper::convertToThai($range[0]);
            $searchModel->date_end = AppHelper::convertToThai($range[1]);
        }
        $dataProvider->query->andFilterWhere(['between', new \yii\db\Expression('DATE(created_at)'), AppHelper::convertToGregorian($searchModel->date_start),AppHelper::convertToGregorian($searchModel->date_end)]);

        // if ($searchModel->thai_year !== '' && $searchModel->date_filter == '') {
        //     $searchModel->date_start = AppHelper::convertToThai(($searchModel->thai_year - 544) . '-10-01');
        //     $searchModel->date_end = AppHelper::convertToThai(($searchModel->thai_year - 543) . '-09-30');
        // }


       
        
        $dataProvider->sort->defaultOrder = ['id' => SORT_DESC];
        $dataProvider->pagination->pageSize = 15;

            return $this->render('index', [
                'title' => 'ศูนย์งานซ่อมบำรุง',
                'icon' => '<i class="fa-solid fa-screwdriver-wrench fs-2"></i>',
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                
            ]);

    }

    public function actionDashboard()
    {
        $searchModel = new HelpdeskSearch([
            'thai_year' => AppHelper::YearBudget(),
            'repair_group' => 2,
            'auth_item' => 'computer'
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'repair']);
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'code', $searchModel->q],
            ['like', new Expression("JSON_EXTRACT(data_json, '$.title')"), $searchModel->q],
            ['like', new Expression("JSON_EXTRACT(data_json, '$.repair_note')"), $searchModel->q],
            ['like', new Expression("JSON_EXTRACT(data_json, '$.note')"), $searchModel->q],
        ]);
        $dataProvider->query->andFilterWhere(['=', new Expression("JSON_EXTRACT(data_json, '$.urgency')"), $searchModel->urgency]);
        $dataProvider->sort->defaultOrder = ['id' => SORT_DESC];
        $dataProvider->pagination->pageSize = 15;

            return $this->render('dashboard', [
                'title' => 'ศูนย์งานซ่อมบำรุง',
                'icon' => '<i class="fa-solid fa-screwdriver-wrench fs-2"></i>',
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                
            ]);

        // return $this->render('dashboard');
    }

    public function actionUpdate($id)
    {
        
      $model = $this->findModel($id);   
      $model->date_start = AppHelper::convertToThai($model->date_start);
        $model->date_end = AppHelper::convertToThai($model->date_end);
        $old_json = $model->data_json;
        if ($this->request->isPost && $model->load($this->request->post())) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            $model->date_start = !empty($model->date_start) ? AppHelper::convertToGregorian($model->date_start) : null;
            $model->date_end = !empty($model->date_end) ? AppHelper::convertToGregorian($model->date_end) : null;
            
            $model->data_json = ArrayHelper::merge($old_json,$model->data_json);

            if ($model->status == 4 && !empty($model->code)) {
                $model->asset->asset_status = 1;
            }
            $model->save();
        return $this->redirect(['/helpdesk/computer/index']);
        
    }

        return $this->render('_form', ['model' => $model]);
    }


    protected function findModel($id)
    {
        if (($model = Helpdesk::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    
}
