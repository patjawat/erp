<?php

namespace app\modules\purchase\controllers;

use app\models\Categorise;
use app\modules\am\models\Asset;
use app\modules\am\models\AssetItem;
use app\modules\hr\models\Employees;
use app\modules\purchase\models\Order;
use app\modules\purchase\models\OrderSearch;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use app\components\AppHelper;
use Yii;

/**
 * GrOrderController implements the CRUD actions for Order model.
 */
class GrOrderController extends Controller
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

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    // ตรวจสอบความถูกต้อง
    public function actionValidator()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new Order();

        if ($this->request->isPost && $model->load($this->request->post())) {
            $requiredName = 'ต้องระบุ';

            if (isset($model->data_json['gr_date'])) {
                preg_replace('/\D/', '', $model->data_json['gr_date']) == "" ? $model->addError('data_json[gr_date]', $requiredName) : null;
            }
            $model->data_json['gr_number'] == '' ? $model->addError('data_json[gr_number]', 'ต้องระบุอาการ...') : null;
            $model->data_json['order_item_checker'] == '' ? $model->addError('data_json[order_item_checker]', 'ต้องระบุอาการ...') : null;

            foreach ($model->getErrors() as $attribute => $errors) {
                $result[\yii\helpers\Html::getInputId($model, $attribute)] = $errors;
            }
            if (!empty($result)) {
                return $this->asJson($result);
            }
        }
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
        $model = new Order();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
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
        $thaiYear = substr((date('Y') + 543), 2);
        $oldObj = $model->data_json;
        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                if ($model->gr_number == '') {
                    $model->gr_number = \mdm\autonumber\AutoNumber::generate('GR-' . $thaiYear . '????');
                }  // validate all models
                
                $convertDate = [
                    'gr_date' =>  AppHelper::convertToGregorian($model->data_json['gr_date']),
                    'order_item_checker' => $model->data_json['order_item_checker']
                ];

                $model->data_json =  ArrayHelper::merge($oldObj,$convertDate,$model->data_json,);
                
                if($model->data_json['order_item_checker'] == 'Y'){
                    $model->status = 4;
                  
                }else{
                    // $model->status = 3;
                }
                $model->save(false);
                    return [
                        'status' => 'success',
                        'container' => '#purchase-container',
                    ];
    
            } else {
                return false;
            }
        } else {
            $model->loadDefaultValues();
            try {
                $model->data_json = [
                    'gr_date' =>  AppHelper::convertToThai($model->data_json['gr_date']),
                ];
            } catch (\Throwable $th) {
                //throw $th;
            }
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
