<?php

namespace app\modules\am\controllers;
use Yii;
use yii\web\Response;
use yii\web\Controller;
use app\models\Categorise;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use app\modules\am\models\AssetItem;
use app\modules\am\models\AssetItemSearch;

/**
 * AssetItemController implements the CRUD actions for AssetItem model.
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

    /**
     * Lists all AssetItem models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new AssetItemSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->orderBy(new \yii\db\Expression('CAST(SUBSTRING(i.code, 4) AS UNSIGNED)'));
        

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

        public function actionListItem()
    {
        $searchModel = new AssetItemSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere([
                'or',
                ['LIKE', 'code', $searchModel->q],
                ['LIKE', 'title', $searchModel->q],
            ]);
        if($this->request->isAjax){

        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'title' => $this->request->get('title'),
            'content' => $this->renderAjax('list_item', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]),
        ];

        }else{
            return $this->render('list_item', [
                            'searchModel' => $searchModel,
                            'dataProvider' => $dataProvider,
            ]);
        }
    }
    

    /**
     * Displays a single AssetItem model.
     * @param string $id รหัสทรัพย์สิน
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
       $model = $this->findModel($id);
       if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('view', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('view', [
                'model' => $model,

            ]);
        }
    }

    /**
     * Creates a new AssetItem model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new AssetItem([
            'group_id' => 'EQUIP',
            'name' => 'asset_item',
            'asset_type_id' => $this->request->get('asset_type_id'),
            'category_id' => $this->request->get('category_id'),
            'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10)
        ]);
        
        if ($this->request->isPost) {
            if ($model->load($this->request->post()) ) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                $model->code = $model->NextId();
                $model->save();
                return [
                    'status' => 'success',
                    'container' => '#sm-container',
                ];
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
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing AssetItem model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id รหัสทรัพย์สิน
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {

        $model = $this->findModel($id);
        //$model->asset_type_id = $model->type->code;
        //$model->category_id = $model->category->code;
        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'status' => 'success',
                    'container' => '#sm-container',
                ];
            }
        } else {
            $model->loadDefaultValues();
        }
        
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('update', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    public function actionGetAssetCategory()
        {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

            $out = [];
            if (isset($_POST['depdrop_parents'])) {
                $parents = $_POST['depdrop_parents'];
                if ($parents != null) {
                    $type_id = $parents[0];
                    $out = Categorise::find()
                        ->where(['category_id' => $type_id,'name' => 'asset_category'])
                        ->select(['code as id', 'title as name'])
                        ->asArray()
                        ->all();
                    return ['output' => $out, 'selected' => ''];
                }
            }
            return ['output' => '', 'selected' => ''];
        }


    /**
     * Deletes an existing AssetItem model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id รหัสทรัพย์สิน
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }



    // ตรวจสอบความถูกต้อง
    public function actionValidator()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new AssetItem();
        $requiredName = 'ต้องระบุ';
        if ($this->request->isPost && $model->load($this->request->post())) {

            $model->title == '' ? $model->addError('title', $requiredName) : null;
            $model->category_id == '' ? $model->addError('category_id', $requiredName) : null;
            $model->asset_type_id == '' ? $model->addError('asset_type_id', $requiredName) : null;
            foreach ($model->getErrors() as $attribute => $errors) {
                $result[\yii\helpers\Html::getInputId($model, $attribute)] = $errors;
            }
            if (!empty($result)) {
                return $this->asJson($result);
            }
        }
    }

    /**
     * Finds the AssetItem model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id รหัสทรัพย์สิน
     * @return AssetItem the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = AssetItem::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
