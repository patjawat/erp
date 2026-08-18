<?php

namespace app\modules\plan\controllers;

use Yii;
use yii\helpers\Html;
use yii\web\Response;
use yii\web\Controller;
use app\models\Categorise;
use yii\filters\VerbFilter;
use app\components\AppHelper;
use app\modules\plan\components\PlanHelper;
use yii\web\NotFoundHttpException;
use app\modules\plan\models\PlanOrderItem;
use app\modules\plan\models\PlanOrder;
use app\modules\am\models\AssetItemSearch;
use app\modules\inventoryV2\models\MaterialPlan;
use app\modules\inventoryV2\services\MaterialPlanForecastService;
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
        if (!$this->request->get('PlanOrderSearch')) {
            $searchModel->thai_year = PlanHelper::currentPlanYear(); // default = ปีที่เปิด (เลือกปีเก่าได้)
        }
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
     * ดึงรายการวัสดุที่คาดว่าหน่วยงานจะใช้ในปีงบที่ขอ ไปเติมตารางรายการของแผนงบประมาณ
     *
     * ที่มา: คลังวัสดุ inventoryV2 ผ่าน MaterialPlanForecastService — ยอดจ่ายจริงปีก่อนหน้า
     * ปรับเป็นอัตราเต็มปีเมื่อข้อมูลยังไม่ครบ 12 เดือน แล้วบวกเผื่อตามอัตรามาตรฐาน
     * หน่วยงาน = คลังปลายทางที่ของถูกจ่ายไปถึง ไม่ใช่แผนกของผู้ทำรายการ
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
        // ขอบเขตหน่วยงาน: หน่วยเดียว หรือ รวมหน่วยย่อยทั้งหมดใต้กลุ่มงาน (nested-set)
        $childIds = [];
        if ($includeChildren) {
            $node = (new \yii\db\Query())->select(['root', 'lft', 'rgt'])->from('tree')->where(['id' => $dept])->one();
            if ($node) {
                $childIds = (new \yii\db\Query())->select('id')->from('tree')
                    ->where(['root' => $node['root']])
                    ->andWhere(['>', 'lft', (int) $node['lft']])
                    ->andWhere(['<', 'rgt', (int) $node['rgt']])
                    ->column();
            }
        }

        // อัตราเผื่อมาจากแผนที่งานพัสดุบันทึกไว้สำหรับปีงบนั้น เพื่อให้ทุกหน่วยงานใช้ค่าเดียวกัน
        // ยังไม่มีแผนบันทึกไว้จึงถอยไปใช้ค่าตั้งต้น
        $growthPct = MaterialPlan::growthPctForYear($year, MaterialPlanForecastService::DEFAULT_GROWTH_PCT);

        // ประมาณการจากคลังวัสดุ (inventoryV2) แทนการอ่านยอดดิบจาก stock_events ของระบบเดิม
        // ซึ่งหยุดรับข้อมูลแล้ว ทำให้หน่วยงานได้ตัวเลขขาดไปเรื่อย ๆ
        $forecast = (new MaterialPlanForecastService())->forecastForOrganization(
            $dept,
            $year,
            $growthPct,
            $childIds,
            $atype
        );

        if ($forecast['unmapped']) {
            return [
                'status' => 'error',
                'message' => 'หน่วยงานนี้ยังไม่ได้ผูกกับคลังวัสดุ จึงคำนวณปริมาณการใช้ไม่ได้ — ตั้งค่าคลังของหน่วยงานก่อน',
            ];
        }

        return [
            'status'         => 'success',
            'prev_year'      => $forecast['base_year'],
            'count'          => count($forecast['items']),
            'child_count'    => count($childIds),
            'items'          => $forecast['items'],
            'months_covered' => $forecast['months_covered'],
            'annual_factor'  => $forecast['factor'],
            'growth_pct'     => $growthPct,
        ];
    }

    public function actionValidator()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new PlanOrder();
        $requiredName = 'ต้องระบุ';
        if ($this->request->isPost && $model->load($this->request->post())) {


            $model->description == '' ? $model->addError('description', $requiredName) : null;
            $model->plan_unit_id == '' ? $model->addError('plan_unit_id', $requiredName) : null;
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
        'thai_year'        => \app\modules\plan\components\PlanHelper::currentPlanYear(),
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
                            PlanOrderItem::saveParcelRow($model->id, (array) $item);
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
                PlanOrderItem::saveParcelRow($model->id, (array) $item);
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
