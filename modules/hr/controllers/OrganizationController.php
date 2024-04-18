<?php

namespace app\modules\hr\controllers;

use app\models\Categorise;
use app\models\CategoriseSearch;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * WorkgroupController implements the CRUD actions for Categorise model.
 */
class OrganizationController extends Controller
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
     * Lists all Categorise models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new CategoriseSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->where(['name' => 'position_group']);


        $dataProvider->setSort([
            'defaultOrder' => [
                'code' => SORT_ASC,
                // 'service_start_time' => SORT_DESC
            ]
        ]);
        // $dataProvider->pagination->pageSize = 5;
        // return $this->render('index', [
            return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionPosition()
    {
        // $model = new Categorise();
        // return $this->render('setting',['model' => $model]);
        // return $this->render('position/index', ['model' => $model]);
        $name = $this->request->get('name');
        $title = $this->request->get('title');
        $searchModel = new CategoriseSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'poaition']);
        $dataProvider->pagination->pageSize = 10;

            if($this->request->isAjax){
                Yii::$app->response->format = Response::FORMAT_JSON;
                    return [
                        'title' => $title,
                        'name' => $name,
                        'content' => $this->renderAjax('position/index', [
                            'searchModel' => $searchModel,
                            'dataProvider' => $dataProvider,
                            ])
                        ];
                    }else{
                        // return $this->redirect(['/hr/categorise']);
                        return $this->render('position/index', [
                            'title' => $title,
                            // 'name' => $name,
                            'searchModel' => $searchModel,
                            'dataProvider' => $dataProvider,
                    ]);
                }

    }

    public function actionDiagram()
    {
        return $this->render('diagram/index');
    }

    public function actionDiagram2()
    {
        return $this->render('diagram2');
    }
    /**
     * Displays a single Categorise model.
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
     * Creates a new Categorise model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $name = $this->request->get('name');
        $code = $this->request->get('code');
        $model = new Categorise([
            'name' => $name,
            'category_id' => $code,
        ]);

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                // return $this->redirect(['index']);
                if ($this->request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return [
                        'status' => 'success',
                        'container' => '#hr-container',
                    ];
                }
            }
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => '<i class="fa-regular fa-pen-to-square"></i> สร้างใหม่',
                'content' => $this->renderAjax('create', [
                    'model' => $model,
                    'name' => $name,
                ]),
            ];
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Categorise model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            // return $this->redirect(['diagram', 'id' => $model->id]);
            if ($this->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'status' => 'success',
                    'container' => '#hr-container',
                ];
            }
        }
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข',
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
     * Deletes an existing Categorise model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $item = $this->findModel($id);
        $this->findModel($id)->delete();

                return [
                    'status' => 'success',
                    'data' => $item,
                    'container' => '#hr-container',
                 ];
    }

    /**
     * Finds the Categorise model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Categorise the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Categorise::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
