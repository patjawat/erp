<?php

namespace app\modules\formtemplate\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use app\models\Categorise;
use yii\filters\VerbFilter;
use app\models\CategoriseSearch;
use yii\web\NotFoundHttpException;

/**
 * LeaveController implements the CRUD actions for Categorise model.
 */
class LayoutsController extends Controller
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

  public function actionGetLayout($formName)
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    $layout = Categorise::find()->where(['name' => $formName])->one();
    
    if (!$layout) {
        // Default layout
        return [
            [
                'field' => 'full_name',
                'x' => 273,
                'y' => 278,
                'fontSize' => 15
            ],
            [
                'field' => 'date',
                'x' => 100,
                'y' => 150,
                'fontSize' => 18
            ]
        ];
    }

    return $layout->data_json;
}

    public function actionSaveLayout()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $data = Yii::$app->request->post();
        $formName = $data['name'] ?? 'default';
        $layoutData = json_encode($data['layout'], JSON_UNESCAPED_UNICODE);

        $layout = Categorise::findOne(['name' => $formName]);
        if (!$layout) {
            $layout = new Categorise();
            $layout->name = $formName;
            $layout->created_at = date('Y-m-d H:i:s');
        }
        $layout->data_json = $layoutData;
        $layout->updated_at = date('Y-m-d H:i:s');
        $layout->save();

        return ['success' => true];
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
        $dataProvider->query->andFilterWhere(['name' => 'leave_form']);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
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
        $model = new Categorise(['name' => 'leave_form']);

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                $model->code = $model->NextId();
                $model->save();
                return [
                    'status' => 'success',
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
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
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
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
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
