<?php

namespace app\modules\helpdesk\controllers;

use Yii;
use app\modules\helpdesk\models\Helpdesk;
use app\modules\helpdesk\models\HelpdeskSearch;
use app\modules\am\models\Asset;
use app\components\UserHelper;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\helpers\ArrayHelper;
use app\modules\hr\models\Employees;


/**
 * RepairController implements the CRUD actions for Repair model.
 */
class RepairController extends Controller
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
     * Lists all Repair models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new HelpdeskSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'repair']);
        $dataProvider->query->andFilterWhere(['status' => 1]);

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => '',
                'content' => $this->renderAjax('index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ]),
            ];
        } else {
            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }

    public function actionListAccept()
    {
        $searchModel = new HelpdeskSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'repair']);
        $dataProvider->query->andFilterWhere(['in', 'status', [2,3]]);

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => '',
                'content' => $this->renderAjax('index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ]),
            ];
        } else {
            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }


    public function actionHistory()
    {
        $code = $this->request->get('code');
        $userId = Yii::$app->user->id;
        $searchModel = new HelpdeskSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        if(isset($code)){
            $dataProvider->query->andFilterWhere(['name' => 'repair', 'code' => $code]);
        }else{
            $dataProvider->query->andFilterWhere(['name' => 'repair', 'created_by' => $userId]);
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => '',
                'content' => $this->renderAjax('history', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ]),
            ];
        } else {
            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }

    /**
     * Displays a single Repair model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $title = $this->request->get('title');
        $model = $this->findModel($id);
        $asset = Asset::findOne(['code' => $model->code]);
        if($asset){
            return $this->render('view', [
                'model' => $model,
                'asset' => $asset
            ]);
        }else{
            return $this->render('view_general', [
                'model' => $model
            ]);
        }
    }

    public function actionViewTask($id)
    {
        $title = $this->request->get('title');
        $model = $this->findModel($id);
        $asset = Asset::findOne(['code' => $model->code]);
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $title,
                'content' => $this->renderAjax('view_task', ['model' => $model]),
            ];
        } else {
            if($asset){
                return $this->render('view', [
                    'model' => $model,
                    'asset' => $asset
                ]);
            }else{
                return $this->render('view_general', [
                    'model' => $model
                ]);
            }
        }
    }

    /**
     * Creates a new Repair model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $container  =  $this->request->get('container');
        $model = new Helpdesk([
            'name' => 'repair',
            'code' => $this->request->get('code'),
            'data_json' => [
                'send_type' => $this->request->get('send_type')
            ]
        ]);
        
        $model->ref = substr(Yii::$app->getSecurity()->generateRandomString(), 10);

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                $this->changAssetStatus($model->code);
                return [
                    'status' => 'success',
                    'container' =>  '#helpdesk-container',
                ];
            }
        } else {
            $model->loadDefaultValues();
        }

        return [
            'title' => $this->request->get('title'),
            'content' => $this->renderAjax('create', [
                'model' => $model,

            ]),
        ];
    }

    /**
     * Updates an existing Repair model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        
        $model = $this->findModel($id);
        $oldObj = $model->data_json;
        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                
            try {
                if($model->status == 4){
                    $asset = Asset::findOne(['code' => $model->code]);
                    $asset->asset_status = 1;
                    $asset->save();
                }
            } catch (\Throwable $th) {
                //throw $th;
            }
                
                // $model->data_json = ArrayHelper::merge($oldObj,$model->data_json);
                // return $model->data_json;

                $model->save();
                return [
                    'status' => 'success',
                    'container' => '#helpdesk-container',
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
    }else{
        return $this->render('update', ['model' => $model]);
    }
    }


        // ตรวจสอบความถูกต้อง
        public function actionCreateValidator()
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $model = new Helpdesk();
    
            if ($this->request->isPost && $model->load($this->request->post())) {
                $requiredName = "ต้องระบุ";
                    $model->data_json['title'] == "" ? $model->addError('data_json[title]', 'ต้องระบุอาการ...') : null;
                    $model->data_json['urgency'] == "" ? $model->addError('data_json[urgency]', 'ต้องระบุความเร่งด่วน...') : null;
                    $model->data_json['location'] == "" ? $model->addError('data_json[location]', 'ต้องระบุสถานะที่...') : null;
    
                foreach ($model->getErrors() as $attribute => $errors) {
                    $result[\yii\helpers\Html::getInputId($model, $attribute)] = $errors;
                }
                if (!empty($result)) {
                    return $this->asJson($result);
                }
            }
        }

    // ตรวจสอบความถูกต้อง
    public function actionCancelJobValidator()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new Helpdesk();

        if ($this->request->isPost && $model->load($this->request->post())) {
            $requiredName = "ต้องระบุ";
                $model->data_json['repair_note'] == "" ? $model->addError('data_json[repair_note]', 'ต้องระบุเหตุผล...') : null;

            foreach ($model->getErrors() as $attribute => $errors) {
                $result[\yii\helpers\Html::getInputId($model, $attribute)] = $errors;
            }
            if (!empty($result)) {
                return $this->asJson($result);
            }
        }
    }

    //ยกเลิกงานซ่อม
    public function actionCancelJob($id)
    {
        $model = $this->findModel($id);
        $user = UserHelper::GetEmployee();
        $oldObj = $model->data_json;
        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                $newObj = [
                    'cancel_name' => $user->fullname,
                    'repair_status' => 'ยกเลิกซ่อม'
                ];
                $model->data_json = ArrayHelper::merge($oldObj, $newObj);
                try {
                    $asset = Asset::findOne(['code' => $model->code]);
                    $asset->asset_status = 1;
                    $asset->save();
                } catch (\Throwable $th) {
                    //throw $th;
                }


                $model->save();
                return [
                    'status' => 'success',
                    'container' => '#helpdesk-container',
                ];
            }
        } else {
            $model->loadDefaultValues();
        }
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

        return [
            'title' => $this->request->get('title'),
            'content' => $this->renderAjax('_cancel_job', [
                'model' => $model,

            ]),
        ];
    }else{
        return $this->render('_cancel_job', ['model' => $model]);
    }
    }


    //รับเรื่อง
    public function actionAcceptJob($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);
        $user = UserHelper::GetEmployee();
        $model->updated_by = $user->id;
        $newObj = [
            'technician_name' => $user->fullname,
            'status_name' => 'รับเรื่อง'
        ];
        $model->data_json = ArrayHelper::merge($model->data_json, $newObj);
        $model->status = 2;
        $model->save();
        // return $model;

        return [
            'status' => 'success',
            'container' => '#helpdesk-container',
        ];
    }

    /**
     * Deletes an existing Repair model.
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
     * Finds the Repair model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Repair the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Helpdesk::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    protected function changAssetStatus($code)
    {
       $model = Asset::findOne(['code' => $code]);
        if($model){
            $model->asset_status = 5;
            $model->save();
        }
    }
}
