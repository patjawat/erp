<?php

namespace app\modules\sm\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\Response;
use yii\web\Controller;
use app\models\Categorise;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\components\SiteHelper;
use yii\web\NotFoundHttpException;
use app\modules\sm\models\AssetItem;
use app\modules\sm\models\AssetItemSearch;
use nickdenry\grid\toggle\actions\ToggleAction;


/**
 * AssetitemController implements the CRUD actions for Assetitem model.
 */
class AssetItemController extends Controller
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

    public function beforeAction($action)
    {

        if ($this->request->get('view')) {
            SiteHelper::setDisplay($this->request->get('view'));
        }

        return parent::beforeAction($action);
    }
    public function actionSetting()
    {
        $code = $this->request->get('code');
        $title = $this->request->get('title');
        $name = $this->request->get('name');

        $searchModel = new AssetItemSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->where(['name' => 'asset_group','active' => true]);


        return $this->render('setting', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'title' => $title,
            'code' => $code,
            'name' => $name
        ]);
    }


    /**
     * Lists all Assetitem models.
     *
     * @return string
     */
    public function actionIndex()
    {

        $searchModel = new AssetItemSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'asset_item']);
        $dataProvider->query->andFilterWhere(['group_id' => 'EQUIP']);
        $dataProvider->query->andFilterWhere(['active' => true]);
        $q = trim($searchModel->q ?? '');
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'title', $q],
            ['like', 'code', $q],
            ['like', new \yii\db\Expression("JSON_EXTRACT(data_json, '\$.unit')"), $q],
            ['like', new \yii\db\Expression("JSON_EXTRACT(data_json, '\$.price')"), $q],
            ['like', new \yii\db\Expression("JSON_EXTRACT(data_json, '\$.fsn')"), $q],
        ]);

        $duplicateCodeSql = <<<SQL
SELECT title, category_id, code, COUNT(id) AS total
FROM categorise
WHERE group_id = 'EQUIP'
    AND category_id IS NOT NULL
GROUP BY code, category_id
HAVING COUNT(id) > 1
SQL;
        try {
            $duplicateCodeSummary = Yii::$app->db->createCommand($duplicateCodeSql)->queryAll();
        } catch (\Throwable $e) {
            Yii::warning('Duplicate code summary query fallback used: ' . $e->getMessage(), __METHOD__);
            $duplicateCodeFallbackSql = <<<SQL
SELECT MIN(title) AS title, category_id, code, COUNT(id) AS total
FROM categorise
WHERE group_id = 'EQUIP'
    AND category_id IS NOT NULL
GROUP BY code, category_id
HAVING COUNT(id) > 1
SQL;
            $duplicateCodeSummary = Yii::$app->db->createCommand($duplicateCodeFallbackSql)->queryAll();
        }
        usort($duplicateCodeSummary, static function (array $a, array $b) {
            $totalCompare = (int) ($b['total'] ?? 0) <=> (int) ($a['total'] ?? 0);
            if ($totalCompare !== 0) {
                return $totalCompare;
            }

            return strcmp((string) ($a['code'] ?? ''), (string) ($b['code'] ?? ''));
        });

        $duplicateCodeGroupCount = count($duplicateCodeSummary);
        $duplicateCodeTotalCount = array_sum(array_map(static function (array $row): int {
            return (int) ($row['total'] ?? 0);
        }, $duplicateCodeSummary));
        $duplicateCodeExtraCount = array_sum(array_map(static function (array $row): int {
            return max(0, (int) ($row['total'] ?? 0) - 1);
        }, $duplicateCodeSummary));

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'duplicateCodeSummary' => $duplicateCodeSummary,
            'duplicateCodeGroupCount' => $duplicateCodeGroupCount,
            'duplicateCodeTotalCount' => $duplicateCodeTotalCount,
            'duplicateCodeExtraCount' => $duplicateCodeExtraCount,
        ]);
    }


    /**
     * Lists all Assetitem models.
     *
     * @return string
     */
    public function actionNext()
    {
        $group = $this->request->get('group');
        $title = $this->request->get('title');
        $name = $this->request->get('name');
        $model = Categorise::findOne(['name' => 'asset_type','category_id' => $group]);
        $searchModel = new AssetItemSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->where(['name' => 'asset_item','active' => true]);
        if($model){
            $dataProvider->query->andFilterWhere(['name' => 'asset_item','category_id' => $model->code, 'active' => true]);
            
        }
        $dataProviderGroup = $searchModel->search($this->request->queryParams);
        $dataProviderGroup->query->andFilterWhere(['name' => 'asset_type','category_id' => $group, 'active' => true]);


        return $this->render('index_copy', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'dataProviderGroup' => $dataProviderGroup,
            'title' => $title,
            'name' => $name,
            'model' => $model
        ]);
    }

    /**
     * Displays a single Assetitem model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionViewType($id)
    {

        $title = $this->request->get('title');
        $model = $this->findModel($id);
        $searchModel = new AssetItemSearch();

        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->where(['name' => 'asset_item', 'active' => true, 'category_id' => $model->code]);
        $dataProviderGroup = $searchModel->search($this->request->queryParams);
        $dataProviderGroup->query->where(['name' => 'asset_type','category_id' => $model->category_id, 'active' => true]);


        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => '<i class="fa-solid fa-eye"></i> แสดง',
                'content' => $this->renderAjax('view_type', [
                    'model' => $model,
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                    'dataProviderGroup' => $dataProviderGroup,
                ]),
            ];
        } else {
            return $this->render('view_type', [
                'model' => $model,
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'dataProviderGroup' => $dataProviderGroup,

            ]);
        }
        // $small_model = Fsn::find()->where(['name' => 'asset_name','category_id'=>$model->code])->all();
        return $this->render('view_type', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'model' => $model,
            'title' => $title ,

        ]);
    }

    public function actionView($id)
    {

        $model = $this->findModel($id);
        $searchModel = new AssetItemSearch();
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
                    'dataProviderGroup' => $dataProviderGroup,
                ]),
            ];
        } else {
            return $this->render('view', [
                'model' => $model,
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'dataProviderGroup' => $dataProviderGroup,

            ]);
        }
    }


    public function actionCreate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = new Assetitem([
            'group_id' => 'EQUIP',
            'name' => 'asset_item'
        ]);
        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $model->code = $model->nextCode();
                if ($model->save()) {
                    $this->UpdateUnit($model);
                    return [
                        'status' => 'success',
                        'container' => '#sm-container',
                    ];
                }
            }
        } else {
            $model->loadDefaultValues();
        }

        return [
            'title' => "สร้างครุภัณฑ์" ,
            'content' => $this->renderAjax('create', [
                'model' => $model,
                'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
            ]),
        ];
       
    }

    public function actionGetCategory()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return Categorise::findOne(["id" => $this->request->get('id'),"name"=>"asset_type"]);
    }



    public function actionCreateItem()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $id = $this->request->get('id');
        $itemType = $this->findModel($id);
        $name = $this->request->get('name');
        $type_code = $this->request->get('type_code');
        $title = $this->request->get('title');
        // return $itemType->code;
        
    
        $model = new Assetitem([
            'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
            'name' => $name,
            'category_id' => $itemType->code,
            'data_json' => ['title' => $title],
            'code' => ($name == 'asset_item' && $itemType->code == 2) ? $itemType->code : ''
        ]);
        if ($this->request->isPost) {
            
            if ($model->load($this->request->post()) && $model->save()) {
                $this->UpdateUnit($model);
                return [
                    'status' => 'success',
                    'container' => '#sm-container',
                ];
            }
        } else {
            $model->loadDefaultValues();
        }
        return [
            'title' => $title ,
            'content' => $this->renderAjax('create', [
                'model' => $model,
                'itemType' => $itemType,
                'title' => $title ,
                'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
            ]),
        ];
       
    }

    protected function UpdateUnit(AssetItem $model)
    {
        $dataJson = $model->data_json;
        if (is_string($dataJson) && $dataJson !== '') {
            $decoded = json_decode($dataJson, true);
            $dataJson = is_array($decoded) ? $decoded : [];
        } elseif (!is_array($dataJson)) {
            $dataJson = [];
        }

        $title = trim((string)($dataJson['unit'] ?? ''));
        if ($title === '') {
            return null;
        }

        $check = Categorise::find()->where(['name' => 'unit', 'title' => $title])->one();
        if (!$check) {
            $newModel = new Categorise();
            $newModel->name = 'unit';
            $newModel->title = $title;
            $newModel->save(false);
            return $newModel->title;
        }

        return $check->title;
    }
    
    /**
     * Updates an existing Assetitem model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);
        try {
        } catch (\Throwable $th) {
             $model->data_json = [
            'price' => $model->data_json['price'] ?? 0
        ];
        }
      

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            $this->UpdateUnit($model);
            return [
                'status' => 'success',
                'container' => '#sm-container',
            ];
        } else {
            $model->loadDefaultValues();
        }
        return [
            'title' => $this->request->get('title'),
            'content' => $this->renderAjax('update', [
                'model' => $model,
            ]),
        ];
    }

    /**
     * Deletes an existing Assetitem model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = $this->findModel($id);
        if($model->delete())
        {
            return [
                'status' => 'success',
                 'container' => '#sm-container',
            ];
        }

    }

    /**
     * Finds the Assetitem model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Assetitem the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Assetitem::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

       // ตรวจสอบความถูกต้อง
    public function actionValidator()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new AssetItem();
        $id = $this->request->get('id');
        $requiredName = 'ต้องระบุ';
        if ($this->request->isPost && $model->load($this->request->post())) {
            
            $code = trim((string) $model->code);
            $categoryId = trim((string) $model->category_id);


            if($id && $code !== '' && $categoryId !== ''){
                $checkCode = AssetItem::find()
                    ->where([
                        'code' => $code,
                        'group_id' => 'EQUIP',
                        'category_id' => $categoryId,
                    ])
                     ->andWhere(['<>', 'id', $model->id])
                    ->one();

                $checkCode ? $model->addError('code', 'รหัสซ้ำ') : null;
            }

            if ($code !== '' && $categoryId !== '') {
                $checkCode = AssetItem::find()
                    ->where([
                        'code' => $code,
                        'group_id' => 'EQUIP',
                        'category_id' => $categoryId,
                    ])
                    ->one();

                $checkCode ? $model->addError('code', 'รหัสซ้ำ') : null;
            }

            $model->title == '' ? $model->addError('title', $requiredName) : null;
            $model->group_id == '' ? $model->addError('group_id', $requiredName) : null;
            $model->category_id == '' ? $model->addError('category_id', $requiredName) : null;

            foreach ($model->getErrors() as $attribute => $errors) {
                $result[\yii\helpers\Html::getInputId($model, $attribute)] = $errors;
            }
            if (!empty($result)) {
                return $this->asJson($result);
            }
        }
    }

}
