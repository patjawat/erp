<?php

namespace app\modules\helpdesk\controllers;

use Yii;
use DateTime;
use yii\web\Response;
use yii\db\Expression;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use yii\web\NotFoundHttpException;
use app\modules\helpdesk\models\Helpdesk;
use app\modules\helpdesk\models\HelpdeskSearch;

class GeneralController extends \yii\web\Controller
{
    public function actionIndex()
    {

        $lastDay = (new DateTime(date('Y-m-d')))->modify('last day of this month')->format('Y-m-d');
        $searchModel = new HelpdeskSearch([
            'thai_year' => AppHelper::YearBudget(),
            'date_start' => AppHelper::convertToThai(date('Y-m') . '-01'),
            'date_end' => AppHelper::convertToThai($lastDay),
            'repair_group' => 1,
            'auth_item' => 'technician',
            'status' => [1,2,3]
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

        if ($searchModel->thai_year !== '' && $searchModel->thai_year !== null) {
            $searchModel->date_start = AppHelper::convertToThai(($searchModel->thai_year - 544) . '-10-01');
            $searchModel->date_end = AppHelper::convertToThai(($searchModel->thai_year - 543) . '-09-30');
        }
        
        try {
         
        $dateStart = AppHelper::convertToGregorian($searchModel->date_start);
        $dateEnd = AppHelper::convertToGregorian($searchModel->date_end);
        $dataProvider->query->andFilterWhere(['between', 'created_at', ($dateStart.' 00:00:00'), ($dateEnd.' 23:59:59')]);
        } catch (\Exception $e) {
            Yii::error("Error converting date: " . $e->getMessage());
        }
        $dataProvider->sort->defaultOrder = ['id' => SORT_DESC];
        $dataProvider->pagination->pageSize = 15;

            return $this->render('index', [
                'title' => 'ศูนย์งานซ่อมบำรุง',
                'icon' => '<i class="fa-solid fa-screwdriver-wrench fs-2"></i>',
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                
            ]);

    }

    public function actionDashboardDev()
    {
        $searchModel = new HelpdeskSearch([
            'thai_year' => AppHelper::YearBudget(),
            'repair_group' => 1,
            'auth_item' => 'technician'
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

            return $this->render('dashboard_dev', [
                'title' => 'ศูนย์งานซ่อมบำรุง',
                'icon' => '<i class="fa-solid fa-screwdriver-wrench fs-2"></i>',
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                
            ]);

        // return $this->render('dashboard');
    }

    public function actionDashboard()
    {
        $searchModel = new HelpdeskSearch([
            'thai_year' => AppHelper::YearBudget(),
            'repair_group' => 1,
            'auth_item' => 'technician'
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
            
            $model->data_json = ArrayHelper::merge($model->data_json, $old_json);

            if ($model->status == 4 && $model->code !== '' && $model->asset !== null) {
                $model->asset->asset_status = 1;
                $model->asset->save();
            }
            
            $model->save();
        return $this->redirect(['/helpdesk/general/index']);
        
    }

        return $this->render('_form', ['model' => $model]);
    }
    public function actionTechnician()
    {
        return $this->render('technician/index');
    }


    protected function findModel($id)
    {
        if (($model = Helpdesk::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    
}
