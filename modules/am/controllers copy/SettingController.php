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
        $dataProvider->query->andFilterWhere(['name' => 'asset_type','category_id' => 3]);
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
        $dataProvider->query->andFilterWhere(['name' => 'asset_item', 'active' => true, 'category_id' => $model->code]);

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

    public function actionOmit()
    {
         $sql_old = "SELECT i.id,g.title as group_name,a.code as asset_code,a.data_json->>'$.asset_name' as asset_name,t.code as type_code,t.title as type_title,i.code as item_code,i.title as item_title FROM asset a
         LEFT JOIN categorise i ON i.code = a.asset_item AND i.name = 'asset_item'
         LEFT JOIN categorise t ON t.code = i.category_id AND t.name = 'asset_type'
         LEFT JOIN categorise g ON g.code = a.asset_group AND g.name = 'asset_group'
         WHERE a.asset_group <> 1 AND t.code IS NULL
         LIMIT 10000;";

         $sql = "SELECT asset_item.id as asset_item_id,asset_item.code as asset_code,asset_item.title as asset_title,asset_type.code,asset_type.code as asset_code FROM categorise as asset_item
         LEFT JOIN categorise asset_type ON asset_type.code = asset_item.category_id AND asset_type.name = 'asset_type'
         WHERE  asset_item.name = 'asset_item' AND asset_type.code is NULL";
         
         $models = Yii::$app->db->createCommand($sql)->queryAll();
         if($this->request->isAjax)
         {
             Yii::$app->response->format = Response::FORMAT_JSON;
             return [
                 'title' => 'รายการที่ยังไม่ระบุประเภท',
                 'content' => $this->renderAjax('omit',['models' => $models])
                ];
            }else{
                return $this->render('omit',['models' => $models]);
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
                'group_id' => $assetType->category_id,
                'category_id' => $assetType->code,
                'data_json' => [
                    'service_life' => isset($assetType->data_json['service_life']) ? $assetType->data_json['service_life'] : '',
                    'depreciation' => isset($assetType->data_json['depreciation']) ? $assetType->data_json['service_life'] : '',
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
