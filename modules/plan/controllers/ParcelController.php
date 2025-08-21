<?php

namespace app\modules\plan\controllers;

use Yii;
use yii\helpers\Html;
use yii\web\Response;
use yii\web\Controller;
use app\models\Categorise;
use yii\filters\VerbFilter;
use app\components\AppHelper;
use yii\web\NotFoundHttpException;
use app\modules\plan\models\PlanItem;
use app\modules\plan\models\PlanOrder;
use app\modules\am\models\AssetItemSearch;
use app\modules\plan\models\PlanOrderSearch;

/**
 * ParcelController implements the CRUD actions for PlanOrder model.
 */
class ParcelController extends Controller
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
     * Lists all PlanOrder models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new PlanOrderSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['plan_group_id' => 'parcel']);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single PlanOrder model.
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


    public function actionGetAssetType()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $assetGroupId = $parents[0];
                $assetGroupMapId = Categorise::find()->where(['name' => 'plan_type', 'code' => $assetGroupId])->one();
                if (isset($assetGroupMapId->data_json['asset_group_id'])) {
                    $groupId = $assetGroupMapId->data_json['asset_group_id'];

                    $out = Categorise::find()
                        ->where(['group_id' => $groupId, 'name' => 'asset_type'])
                        ->select(['code as id', 'title as name'])
                        ->asArray()
                        ->all();
                } else {
                    $out = [];
                }
                return ['output' => $out, 'selected' => ''];
            }
        }
        return ['output' => '', 'selected' => ''];
    }


    public function actionValidator()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new PlanOrder();
        $requiredName = 'ต้องระบุ';
        if ($this->request->isPost && $model->load($this->request->post())) {


            $model->description == '' ? $model->addError('description', $requiredName) : null;
            $model->department_id == '' ? $model->addError('department_id', $requiredName) : null;
            // $model->data_json['date_end_type'] == '' ? $model->addError('data_json[date_end_type]', $requiredName) : null;
        }
        foreach ($model->getErrors() as $attribute => $errors) {
            $result[Html::getInputId($model, $attribute)] = $errors;
        }
        if (!empty($result)) {
            return $this->asJson($result);
        }
    }

    /**
     * Creates a new PlanOrder model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new PlanOrder([
            'thai_year' => (AppHelper::YearBudget() + 1),
            'plan_group_id' => 'parcel', // Default to material type
            'plan_category_id' => 'CE',
            // 'plan_type_id' => 'CE1',
        ]);
        $items = []; // ไม่มีรายการเดิม

        if ($model->load(Yii::$app->request->post())) {

            $model->save(false);

            $postItems = Yii::$app->request->post('items', []);
            foreach ($postItems as $item) {
                if (!empty($item['item_name'])) {
                    $pi = new PlanItem();
                    $pi->plan_order_id = $model->id;
                    $pi->item_name = $item['item_name'];
                    $pi->qty = (int)$item['qty'];
                    $pi->unit_price = (float)$item['unit_price'];
                    $pi->save(false);
                }
            }
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', ['model' => $model, 'items' => $items]);
    }
    /**
     * Updates an existing PlanOrder model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $items = $model->getPlanItems()->all(); // โหลดรายการเดิม
        if ($this->request->isPost && $model->load($this->request->post()) && $model->save(false)) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            PlanItem::deleteAll(['plan_order_id' => $model->id]);
            $postItems = Yii::$app->request->post('items', []);
            foreach ($postItems as $item) {
                if (!empty($item['item_name'])) {
                    $pi = new PlanItem();
                    $pi->plan_order_id = $model->id;
                    $pi->item_name = $item['item_name'];
                    $pi->qty = (int)$item['qty'];
                    $pi->unit_price = (float)$item['unit_price'];
                    $pi->save(false);
                }
            }

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'items' => $items
        ]);
    }

    /**
     * Deletes an existing PlanOrder model.
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

    public function actionGetAssetTypes()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $types = PlanOrder::find()->select(['id', 'title'])->asArray()->all();
        return $types;
    }

    public function actionListAssetItem()
    {
        $searchModel = new AssetItemSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['asset_type_id' => $this->request->get('asset_type_id')]);
        $dataProvider->query->andFilterWhere(['asset_category_id' => $this->request->get('asset_category_id')]);
        $dataProvider->query->andFilterWhere([
            'or',
            ['LIKE', 'code', $searchModel->q],
            ['LIKE', 'title', $searchModel->q],
        ]);
        if ($this->request->isAjax) {

            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('list_asset_item', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ]),
            ];
        } else {
            return $this->render('list_asset_item', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }
    /**
     * Finds the PlanOrder model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return PlanOrder the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PlanOrder::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
