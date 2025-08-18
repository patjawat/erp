<?php

namespace app\modules\plan\controllers;
use Yii;
use yii\helpers\Url;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\modules\plan\models\Plan;
use yii\web\NotFoundHttpException;
use app\modules\plan\models\PlanItem;
use app\modules\plan\models\PlanSearch;

/**
 * PlanController implements the CRUD actions for Plan model.
 */
class PlanController extends Controller
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
     * Lists all Plan models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new PlanSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Plan model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $items = $model->getPlanItems()->all(); // ดึงรายการ plan_item

        return $this->render('view', [
            'model' => $model,
            'items' => $items,
        ]);
    }

    /**
     * Creates a new Plan model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
public function actionCreate()
{
    $model = new Plan();
    $items = []; // ไม่มีรายการเดิม

    if ($model->load(Yii::$app->request->post()) && $model->save()) {
        $postItems = Yii::$app->request->post('items', []);
        foreach ($postItems as $item) {
            if (!empty($item['item_name'])) {
                $pi = new PlanItem();
                $pi->plan_id = $model->id;
                $pi->item_name = $item['item_name'];
                $pi->quantity = (int)$item['quantity'];
                $pi->unit_price = (float)$item['unit_price'];
                $pi->save();
            }
        }
        return $this->redirect(['view','id'=>$model->id]);
    }

    return $this->render('create', ['model'=>$model, 'items'=>$items]);
}


    /**
     * Updates an existing Plan model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
public function actionUpdate($id)
{
    $model = $this->findModel($id); // โหลดแผนหลัก
    $items = $model->getPlanItems()->all(); // โหลดรายการเดิม

    if ($model->load(Yii::$app->request->post())) {

        $postItems = Yii::$app->request->post('items', []);

        // $model->updated_at = time();
        if ($model->save()) {

            // ลบรายการเก่าของแผน
            \app\modules\plan\models\PlanItem::deleteAll(['plan_id' => $model->id]);

            // บันทึกรายการใหม่
            foreach ($postItems as $item) {
                if (!empty($item['item_name'])) {
                    $pi = new \app\modules\plan\models\PlanItem();
                    $pi->plan_id = $model->id;
                    $pi->item_name = $item['item_name'];
                    $pi->quantity = (int)$item['quantity'];
                    $pi->unit_price = (float)$item['unit_price'];
                    $pi->save();
                }
            }
            return [
                'status' => 'success',
                'url' => Url::to(['view', 'id' => $model->id])
            ];
        }
    }

    return $this->render('update', [
        'model' => $model,
        'items' => $items,
    ]);
}


    /**
     * Deletes an existing Plan model.
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
     * Finds the Plan model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Plan the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Plan::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
