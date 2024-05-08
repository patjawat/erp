<?php

namespace app\modules\am\controllers;

use Yii;
use app\modules\sm\models\AssetType;
use app\modules\sm\models\AssetTypeSearch;
use app\modules\am\models\AssetItem;
use app\modules\am\models\AssetItemSearch;
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
        $dataProvider->query->andFilterWhere(['name' => 'asset_type']);
        $dataProvider->query->orderBy(new \yii\db\Expression("SUBSTRING_INDEX(SUBSTRING_INDEX(CONCAT(code, '.'), '.', 1), '.', -1) + 0
        , SUBSTRING_INDEX(SUBSTRING_INDEX(CONCAT(code, '.'), '.', 2), '.', -1) + 0
        , SUBSTRING_INDEX(SUBSTRING_INDEX(CONCAT(code, '.'), '.', 3), '.', -1) + 0
        , SUBSTRING_INDEX(SUBSTRING_INDEX(CONCAT(code, '.'), '.', 4), '.', -1) + 0"));

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionItemsDetail()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = $this->request->post('expandRowKey');
        $typeModel = AssetType::findOne($id);
      
        $searchModel = new AssetTypeSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andWhere(['name' => 'asset_item','category_id' => $typeModel->code]);

        return $this->renderAjax('list_items', [
            'model' => $typeModel,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);
        $searchModel = new AssetTypeSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->where(['name' => 'asset_item', 'active' => true, 'category_id' => $model->code]);
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
                ]),
            ];
        } else {
            return $this->render('view', [
                'model' => $model,
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,

            ]);
        }
    }

    public function actionViewItem($id)
    {
        $model = $this->findModel($id);
      
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => '<i class="fa-solid fa-eye"></i> แสดง',
                'content' => $this->renderAjax('view_item', [
                    'model' => $model
                ]),
            ];
        } else {
            return $this->render('view_item', [
                'model' => $model,

            ]);
        }
    }


    public function actionCreate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = $this->request->get('id');
        $name = $this->request->get('name');
        $assetType = AssetType::findOne(['id' => $id]);

        if($name == 'asset_type'){
            $model = new AssetType([
                'name' => $name,
                ]);
        }else{
            $model = new AssetType([
                'name' => $name,
                'category_id' => $assetType->code,
                'data_json' => [
                    'service_life' => $assetType->data_json['service_life'],
                    'depreciation' => $assetType->data_json['depreciation'],
                    ]
                ]);
            }

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return [
                    'status' => 'success',
                    'container' => '#am-container',
                ];
            }
        } else {
            $model->loadDefaultValues();
        }

        return [
            'title' => $this->request->get('title'),
            'content' => $this->renderAjax('create',[
                'model' => $model,
                'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10)
            ])
        ];
    }

    /**
     * Updates an existing AssetType model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return [
                'status' => 'success',
                'container' => '#am-container',
            ];
        }

        return [
            'title' => $this->request->get('title'),
            'content' => $this->renderAjax('create',[
                'model' => $model,
                'ref' => $model->ref == '' ? substr(Yii::$app->getSecurity()->generateRandomString(), 10) : $model->ref
            ])
        ];

    }

    protected function findModel($id)
    {
        if (($model = AssetType::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }


    public function actionDelete($id)
    {
        $container = $this->request->get('container');
        $cache = $this->findModel($id);
        $model = $this->findModel($id)->delete();
        Yii::$app->response->format = Response::FORMAT_JSON;
        // return $cache;
        if($cache->name == "asset_type"){
            return $this->redirect(['/am/setting']);
        }else{
            return [
                'status' => 'success',
                'data' => $model,
                'container' => '#am-container',
                'close' => true,
            ];
        }
    }


}
