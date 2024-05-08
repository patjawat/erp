<?php

namespace app\modules\am\controllers;

use Yii;
use app\modules\sm\models\AssetType;
use app\modules\sm\models\AssetTypeSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use app\models\Categorise;

class SettingController extends \yii\web\Controller
{
    public function actionIndex()
    {
        $searchModel = new AssetTypeSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->where(['name' => 'new_asset_type']);
        $dataProvider->query->orderBy(new \yii\db\Expression("CAST(code as UNSIGNED) asc"));
        // $dataProvider->sort->defaultOrder = [new Expression("JSON_EXTRACT(CAST(code AS INTEGER))") => SORT_DESC];

        // $dataProviderGroup = $searchModel->search($this->request->queryParams);
        // $dataProviderGroup->query->andFilterWhere(['name' => 'asset_type', 'active' => true]);
        return $this->render('index', [
            'searchModel' => $searchModel,
            // 'dataProviderGroup' => $dataProviderGroup,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {

        $model = $this->findModel($id);
        $searchModel = new AssetTypeSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->where(['name' => 'asset_type', 'active' => true, 'category_id' => $model->code]);
        $dataProviderGroup = $searchModel->search($this->request->queryParams);
        $dataProviderGroup->query->where(['name' => 'asset_type','category_id' => $model->category_id, 'active' => true]);


        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => '<i class="fa-solid fa-eye"></i> แสดง',
                'content' => $this->renderAjax('view', [
                    'model' => $model,
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                    'dataProvider' => $dataProvider,
                    'dataProviderGroup' => $dataProviderGroup,
                ]),
            ];
        } else {
            return $this->render('view', [
                'model' => $model,
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'dataProvider' => $dataProvider,
                'dataProviderGroup' => $dataProviderGroup,

            ]);
        }
        // $small_model = Fsn::find()->where(['name' => 'asset_name','category_id'=>$model->code])->all();
        return $this->render('view', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'model' => $model,
            'btn' => true,

        ]);
    }

    protected function findModel($id)
    {
        if (($model = AssetType::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }


}
