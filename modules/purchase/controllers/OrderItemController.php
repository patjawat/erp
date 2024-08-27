<?php

namespace app\modules\purchase\controllers;

use app\modules\purchase\models\Order;
use app\modules\purchase\models\OrderSearch;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use Yii;

/**
 * OrderItemController implements the CRUD actions for Order model.
 */
class OrderItemController extends Controller
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
        $requiredName = "ต้องระบุ";
        if ($this->request->isPost && $model->load($this->request->post())) {
            $checkEmp = Order::find()
            ->andwhere(['name' => $model->name,'category_id' => $model->category_id])
            ->andwhere(['=', 'JSON_UNQUOTE(JSON_EXTRACT(data_json, "$.employee_id"))', $model->data_json['employee_id']])
            ->one();


            if($model->action == 'create' && $checkEmp){
               $model->addError('data_json[employee_id]', 'กรรมการต้องไม่ซ้ำ');
            }


            if (isset($model->data_json['employee_id'])) {
                    $model->data_json['employee_id']  == "" ? $model->addError('data_json[employee_id]', $requiredName) : null;
            }

            if (isset($model->data_json['committee'])) {
                $model->data_json['committee']  == "" ? $model->addError('data_json[committee]', $requiredName) : null;
            }

            
        }
        foreach ($model->getErrors() as $attribute => $errors) {
            $result[\yii\helpers\Html::getInputId($model, $attribute)] = $errors;
        }
        if (!empty($result)) {
            return $this->asJson($result);
        }
    }


    //แสดงคณะกรรมการตรวจรับพัสดุ
    public function actionCommittee()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $category_id = $this->request->get('category_id');

        $listcommittee = Order::find()
            ->where(['name' => 'committee','category_id' => $category_id])
            ->orderBy(new \yii\db\Expression("JSON_EXTRACT(data_json, '\$.committee') asc"))
            ->all();
        return [
            'title' => $this->request->get('title'),
            'content' => $this->renderAjax('list_committee',['listcommittee' => $listcommittee]),
        ];
    }

        //คณะกรรมการตรวจรับพัสดุ
        public function actionCommitteeDetail()
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('list_committee_detail'),
            ];
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
        $model = new Order([
            'category_id' => $this->request->get('id'),
            'name' => $this->request->get('name'),
            'action' => 'create'
        ]);

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'title' => $this->request->get('title'),
                    'status' => 'success',
                    'container' => '#purchase-container',
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
     * Updates an existing Order model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                // return [
                //     'title' => $this->request->get('title'),
                //     'content' => $this->renderAjax('list_'.$model->name),
                //     'status' => 'success',
                // ];
                return [
                    'title' => $this->request->get('title'),
                    'status' => 'success',
                    'container' => '#' . $model->name,
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
     * Deletes an existing Order model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $container = $this->request->get('container');
        $url = $this->request->get('url');
        $model = $this->findModel($id);
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model->delete();
        return [
            'status' => 'success',
            'container' => '#committee',
            'url' => $url
        ];
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
