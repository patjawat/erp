<?php

namespace app\modules\plan\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\components\AppHelper;
use yii\web\NotFoundHttpException;
use app\modules\plan\models\PlanItem;
use app\modules\plan\models\PlanOrder;
use app\modules\plan\models\PlanOrderSearch;

/**
 * ExpensesController implements the CRUD actions for PlanOrder model.
 */
class ExpensesController extends Controller
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
         $dataProvider->query->andFilterWhere(['plan_group_id' => 'expenses']);

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

    /**
     * Creates a new PlanOrder model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new PlanOrder([
             'thai_year' => (AppHelper::YearBudget()+1),
            'plan_group_id' => 'expenses',
            'plan_category_id' => 'OE',//รายจ่ายจากการดำเนินงาน
            'plan_type_id' => 'OE5', //ค่าใช้สอย
        ]);
           $items = []; // ไม่มีรายการเดิม

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save(false)) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
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

        return $this->render('update', ['model' => $model, 'items' => $items]);
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
