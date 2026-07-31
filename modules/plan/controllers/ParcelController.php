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
use app\modules\plan\models\PlanOrderItem;
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
        $dataProvider->query->orderBy(['id' => SORT_DESC]);

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



    /**
     * ดึงรายการวัสดุจากการเบิกใช้ (OUT) ของหน่วยงาน ปีงบก่อนหน้า
     * เฉลี่ยจำนวนเป็นรายเดือน (÷12) + ราคาต่อหน่วยล่าสุด
     * ที่มา: stock_events (header name='order' OUT + line 'order_item'); หน่วยงาน = ผู้ทำรายการ (emp -> department)
     */
    public function actionPullConsumption()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $dept  = (int) ($this->request->post('department_id') ?: $this->request->get('department_id'));
        $atype = (string) ($this->request->post('asset_type_id') ?: $this->request->get('asset_type_id'));
        $year  = (int) ($this->request->post('thai_year') ?: $this->request->get('thai_year'));
        $includeChildren = (int) ($this->request->post('include_children') ?: $this->request->get('include_children'));

        if (!$dept || $atype === '' || !$year) {
            return ['status' => 'error', 'message' => 'กรุณาเลือกหน่วยงาน ประเภทวัสดุ และปีงบประมาณ'];
        }
        $prevYear = $year - 1;

        // ขอบเขตหน่วยงาน: หน่วยเดียว หรือ รวมหน่วยย่อยทั้งหมดใต้กลุ่มงาน (nested-set)
        $deptIds = [$dept];
        $childCount = 0;
        if ($includeChildren) {
            $node = (new \yii\db\Query())->select(['root', 'lft', 'rgt'])->from('tree')->where(['id' => $dept])->one();
            if ($node) {
                $children = (new \yii\db\Query())->select('id')->from('tree')
                    ->where(['root' => $node['root']])
                    ->andWhere(['>', 'lft', (int) $node['lft']])
                    ->andWhere(['<', 'rgt', (int) $node['rgt']])
                    ->column();
                $deptIds = array_merge($deptIds, $children);
                $childCount = count($children);
            }
        }
        $deptIds = array_values(array_unique(array_map('intval', $deptIds)));
        $deptIn = implode(',', $deptIds);

        $sql = "
            SELECT it.asset_item AS code,
                   COALESCE(ci.title, it.asset_item) AS name,
                   SUM(it.qty) AS qty_year,
                   ROUND(SUM(it.qty) / 12, 2) AS per_month,
                   CAST(SUBSTRING_INDEX(GROUP_CONCAT(it.unit_price ORDER BY o.movement_date DESC), ',', 1) AS DECIMAL(15,2)) AS last_price
            FROM stock_events o
            JOIN stock_events it ON it.category_id = o.id AND it.name = 'order_item'
            JOIN employees e ON e.id = o.emp_id
            LEFT JOIN categorise ci ON ci.code = it.asset_item AND ci.name = 'asset_item'
            WHERE o.name = 'order' AND o.transaction_type = 'OUT'
              AND o.thai_year = :y AND o.asset_type_id = :a
              AND e.department IN ($deptIn)
            GROUP BY it.asset_item, name
            HAVING qty_year > 0
            ORDER BY qty_year DESC
        ";
        $rows = Yii::$app->db->createCommand($sql, [':y' => $prevYear, ':a' => $atype])->queryAll();

        return [
            'status'      => 'success',
            'prev_year'   => $prevYear,
            'count'       => count($rows),
            'child_count' => $childCount,
            'items'       => $rows,
        ];
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
        'thai_year'        => (AppHelper::YearBudget() + 1),
        'plan_group_id'    => 'parcel',   // Default to material type
        'plan_type_id'     => 'INV',
        'plan_category_id' => 'INV_01',
    ]);

    $items = []; // ไม่มีรายการเดิม

           if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                try {
                    if ($model->save(false)) {
                        $postItems = Yii::$app->request->post('items', []);
                        foreach ($postItems as $item) {
                            if (!empty($item['item_name'])) {
                                $pi = new PlanOrderItem();
                                $pi->plan_order_id = $model->id;
                                $pi->item_name = $item['item_name'];
                                $pi->qty = (int)$item['qty'];
                                $pi->unit_price = (float)$item['unit_price'];
                                $pi->save(false);
                            }
                        }
                    }
                } catch (\Throwable $th) {
                    //throw $th;
                }
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

    return $this->render('create', [
        'model' => $model,
        'items' => $items,
    ]);
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
            PlanOrderItem::deleteAll(['plan_order_id' => $model->id]);
            $postItems = Yii::$app->request->post('items', []);
            foreach ($postItems as $item) {
                if (!empty($item['item_name'])) {
                    $pi = new PlanOrderItem();
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
