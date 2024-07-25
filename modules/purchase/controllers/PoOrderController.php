<?php

namespace app\modules\purchase\controllers;

use app\modules\purchase\models\Order;
use app\modules\purchase\models\OrderSearch;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use Yii;

/**
 * PoOrderController implements the CRUD actions for Order model.
 */
class PoOrderController extends Controller
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
     * Lists all Order models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new OrderSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andwhere(['is not', 'po_number', null]);
        $dataProvider->query->andFilterwhere(['name' => 'order']);

        return $this->render('@app/modules/purchase/views/order/index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Order model.
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
     * Creates a new Order model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $id = $this->request->get('id');
        $model = $this->findModel($id);
        // $model = new Order([
        //     'name' => $this->request->get('name'),
        //     'status' => $this->request->get('status'),
        //     // 'name' => 'order',
        //     'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
        // ]);

        $thaiYear = substr((date('Y') + 543), 2);
        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                // $model->code = \mdm\autonumber\AutoNumber::generate('PR-' . $thaiYear . '????');
                $model->save(false);
                return $this->redirect(['/purchase/po-order']);
            } else {
                return false;
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
     * Updates an existing Order model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $oldObj = $model->data_json;
        $thaiYear = substr((date('Y') + 543), 2);
        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                if ($model->po_number == '') {
                    $model->po_number = \mdm\autonumber\AutoNumber::generate('PO-' . $thaiYear . '????');
                }  // validate all models
                $model->data_json = ArrayHelper::merge(
                    $oldObj,
                    $model->data_json,
                );
                // return $model->data_json;
                $model->status = 4;
                $model->save(false);

                //  update pr po pq on items
                $sql = "UPDATE `order` SET  pr_number = :pr_number,pq_number = :pq_number,po_number = :po_number WHERE name = 'order_item' AND category_id = :category_id";
                $command = \Yii::$app
                    ->db
                    ->createCommand($sql)
                    ->bindValues([':pr_number' => $model->pr_number])
                    ->bindValues([':pq_number' => $model->pq_number])
                    ->bindValues([':po_number' => $model->po_number])
                    ->bindValues([':category_id' => $model->id])
                    ->execute();

                 return $this->redirect(['/purchase/po-order']);;
                // return [
                //     'status' => 'success',
                //     'container' => '#purchase-container',
                // ];
            } else {
                return false;
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

    /**
     * Deletes an existing Order model.
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
     * Finds the Order model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Order the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Order::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
