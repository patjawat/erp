<?php

namespace app\modules\am\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\components\SiteHelper;
use app\models\CategoriseSearch;
use yii\web\NotFoundHttpException;
use nickdenry\grid\toggle\actions\ToggleAction;

/**
 * FsnController implements the CRUD actions for Fsn model.
 */
class AssetGroupController extends Controller
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

    public function actions()
    {
        return [
            'toggle' => [
                'class' => ToggleAction::class,
                'modelClass' => 'app\modules\am\models\Fsn', // Your model class
            ],
        ];
    }

    public function beforeAction($action)
    {

        // $visit_ = TCDSHelper::getVisit();
        // $hn = $visit_['hn'];
        // if (empty($hn)) {
        //     return $this->redirect(['/site/index']);
        // }
        // if(empty($vn)){
        //     return $this->redirect(['/nursescreen']);
        // }

        if ($this->request->get('view')) {
            SiteHelper::setDisplay($this->request->get('view'));
        }

        return parent::beforeAction($action);
    }

    /**
     * Lists all Fsn models.
     *
     * @return string
     */
    public function actionIndex()
    {
       
        $searchModel = new CategoriseSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'asset_group']);


        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,

        ]);
    }

        public function actionListFsn()
    {
        $searchModel = new FsnSearch();
         $dataProvider = $searchModel->search($this->request->queryParams);


 if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => '<i class="fa-solid fa-eye"></i> แสดง',
                'content' => $this->renderAjax('list_fsn', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]),
            ];
        } else {
            return $this->render('list_fsn', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,

        ]);
        }
       
    }

    /**
     * Displays a single Fsn model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        // $model = $model = Fsn::findOne(['code' => $id,'name' => $name]);
        // $searchModel = new FsnSearch();
        $model = $this->findModel($id);
        $searchModel = new FsnSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        // if (isset($group)) {
        //     $dataProvider->query->andFilterWhere(['category_id' => $group]);
        // }
        $dataProvider->query->where(['name' => 'asset_name', 'active' => true, 'category_id' => $model->code]);
        $dataProvider->query->andFilterWhere(['like', 'title', $searchModel->q]);

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => '<i class="fa-solid fa-eye"></i> แสดง',
                'content' => $this->renderAjax('view', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('view', [
                'model' => $model,

            ]);
        }
        // $small_model = Fsn::find()->where(['name' => 'asset_name','category_id'=>$model->code])->all();
        return $this->render('view', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'model' => $model,
            'btn' => true,

        ]);
    }
    public function actionUpdateStatusGroup()
    {
        // return $this->request->isPost;
        Yii::$app->response->format = Response::FORMAT_JSON;
        if ($this->request->isPost) {
            $id = $this->request->post('id');
            $active = $this->request->post('active');
            $model = Fsn::findOne(['id' => $id, 'name' => 'asset_type']);
            $model->active = $active == "true" ? 1 : 0;

            if ($model->save()) {
                if ($this->request->isAjax) {
                    return [
                        'status' => 'success',
                        'container' => '#am-container',
                    ];
                }
            }
        }

    }

    public function actionGroupSetting()
    {
        $searchModel = new FsnSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        $dataProvider->query->andFilterWhere(['name' => 'asset_type']);
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => 'กลุ่มของทรัพย์สิน',
                'content' => $this->renderAjax('group_setting', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ]),
            ];
        } else {
            return $this->render('group_setting', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }

    /**
     * Creates a new Fsn model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $name = $this->request->get('name');
        $category_id = $this->request->get('category_id');
        $title = $this->request->get('title');
        $name = $this->request->get('name');

        $model = new Fsn([
            'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
            'name' => $name,
            'category_id' => $category_id,
            'code' => $name == "asset_name" ? $category_id : null,
            'data_json' => ['title' => $title,'asset_type' => '2','asset_type_text' => 'ครุภัณฑ์'],
        ]);
        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return [
                    'status' => 'success',
                    'container' => '#am-container',
                ];
            }
        } else {
            $model->loadDefaultValues();
        }
        return [
            'title' => $this->request->get('title'),
            'content' => $this->renderAjax('create', [
                'model' => $model,
                'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
            ]),
        ];
        return $this->render('create', [
            'model' => $model,
        ]);
    }

    // public function actionCreate2($type_code,$id)
    // {
    //     Yii::$app->response->format = Response::FORMAT_JSON;
    //     $model = new Fsn();
    //     if ($this->request->isPost) {
    //         if ($model->load($this->request->post())) {
    //             //&&
    //             $model->code = $model->category_id ."-". $model->code;
    //             $model->save();
    //             return [
    //                 'status' => 'success',
    //                 'container' => '#am-container',
    //             ];
    //         }
    //     } else {
    //         $model->loadDefaultValues();
    //     }
    //     $model->category_id = $type_code;
    //     Yii::$app->response->format = Response::FORMAT_JSON;
    //     return [
    //         'title' => 'สร้างชนิดครุภัณฑ์ใหม่',
    //         'content' => $this->renderAjax('create2', [
    //             'model' => $model,
    //             'ref' => substr(Yii::$app->getSecurity()->generateRandomString(),10)
    //         ])
    //     ];
    // }

    /**
     * Updates an existing Fsn model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);
        // $model->data_json =  ['asset_type' => '2','asset_type_text' => 'ครุภัณฑ์'];

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            // return $model->save();
            return [
                'status' => 'success',
                'container' => '#am-container',
            ];
        } else {
            $model->loadDefaultValues();
        }

        return [
            'title' => $this->request->get('title'),
            'content' => $this->renderAjax('update', [
                'model' => $model,
            ]),
        ];
    }

    // public function actionUpdate2($id)
    // {
    //     Yii::$app->response->format = Response::FORMAT_JSON;
    //     $model = $this->findModel($id);

    //     if ($this->request->isPost && $model->load($this->request->post()) ) {
    //             $model->code = $model->category_id . $model->code;
    //             $model->save();
    //             return [
    //                 'status' => 'success',
    //                 'container' => '#am-container',
    //             ];
    //     }

    //     $model->code = substr($model->code, 5);
    //     Yii::$app->response->format = Response::FORMAT_JSON;
    //     return [
    //         'title' => 'แก้ไขชนิดครุภัณฑ์',
    //         'content' => $this->renderAjax('update2', [
    //             'model' => $model,
    //             'ref' => $model->ref
    //         ])
    //         ];
    // }

    // public function actionGroup()
    // {
    //     return $this->render('group');
    // }

    /**
     * Deletes an existing Fsn model.
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
     * Finds the Fsn model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Fsn the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Fsn::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    // ตรวจสอบความถูกต้อง
    public function actionValidator()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new Fsn();
        if ($this->request->isPost && $model->load($this->request->post())) {

            //  return  $model->ref;
            // ตรวจระหัสซ้ำ
                $checkCode = Fsn::find()->where(['code' => $model->code])
                ->andWhere(['<>','ref',$model->ref])
                ->one();

            if ($checkCode) {
                    $codeStatus = true;
            } else {
                $codeStatus = false;
            }
            // จบตรวจสอลรหัสซ้ำ

            $requiredName = "ต้องระบุ";
            //ตรวจสอบตำแหน่ง
            if ($model->name == "asset_group") {
                $model->data_json['depreciation'] == "" ? $model->addError('data_json[depreciation]', $requiredName) : null;
                $model->data_json['service_life'] == "" ? $model->addError('data_json[service_life]', $requiredName) : null; 
                    $checkCode ? $model->addError('code', 'รหัสน้ำถูกใช้แล้ว') : null;
            }

            if ($model->name == "asset_name") {
                // $model->data_json['asset_type'] == "" ? $model->addError('data_json[asset_type]', $requiredName) : null;
                // $model->category_id == "" ? $model->addError('category_id', $requiredName) : null;
                $codeStatus ? $model->addError('code', 'รหัสน้ำถูกใช้แล้ว') : null;
            }

            foreach ($model->getErrors() as $attribute => $errors) {
                $result[\yii\helpers\Html::getInputId($model, $attribute)] = $errors;
            }
            if (!empty($result)) {
                return $this->asJson($result);
            }
        }
    }

}
