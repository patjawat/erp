<?php

namespace app\modules\hr\controllers;

use Yii;
use app\models\Categorise;
use app\models\CategoriseSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * PositionController implements the CRUD actions for Categorise model.
 */
class PositionController extends Controller
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
        $dataProvider->query->andFilterWhere(['name' => 'position_group']);
        $dataProvider->query->andFilterWhere(['category_id' => $this->request->get('code')]);
        // $dataProvider->query->orderBy(['code' => 'desc']);
        $dataProvider->setSort(['defaultOrder' => ['code'=>SORT_ASC]]);
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
        $model = $this->findModel($id);
        $searchModel = new CategoriseSearch();
        $dataProviderPositionGroup = $searchModel->search($this->request->queryParams);
        $dataProviderPositionGroup->query->andFilterWhere(['name' => 'position_group']);
        $dataProviderPositionGroup->query->andFilterWhere(['category_id' => $model->category_id]);

        $dataProviderPositionName = $searchModel->search($this->request->queryParams);
        $dataProviderPositionName->query->andFilterWhere(['name' => 'position_name']);
        $dataProviderPositionName->query->andFilterWhere(['category_id' => $model->code]);

        if($this->request->isAjax){
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => 'แสดง',
                'content' => $this->renderAjax('view', [
                    'model' => $model,
                    'searchModel' => $searchModel,
                    'dataProviderPositionGroup' => $dataProviderPositionGroup,
                    'dataProviderPositionName' => $dataProviderPositionName,
                ])
            ];
        }else{   
            return $this->render('view', [
                'model' => $model,
                'searchModel' => $searchModel,
                'dataProviderPositionGroup' => $dataProviderPositionGroup,
                'dataProviderPositionName' => $dataProviderPositionName,
            ]);
        }
    }

    /**
     * Creates a new Categorise model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $category_id = $this->request->get('category_id');
        $name = $this->request->get('name');

if($name == 'position_type'){
    $sql = "SELECT CAST(SUBSTRING_INDEX(code, 'T',-1) AS UNSIGNED)+1 FROM `categorise` WHERE name = 'position_type' ORDER BY CAST(SUBSTRING_INDEX(code, 'T',-1) AS UNSIGNED) DESC LIMIT 1;";
    $query = Yii::$app->db
    ->createCommand($sql)
    ->queryScalar();
    $code = 'PT'.$query;
}

if($name == 'position_group'){
        //สร้าง code ของ ตำแหน่ง
        $sql = "SELECT CAST(SUBSTRING_INDEX(code, 'G',-1) AS UNSIGNED)+1 FROM `categorise` WHERE `category_id` = :category_id ORDER BY CAST(SUBSTRING_INDEX(code, 'G',-1) AS UNSIGNED) DESC LIMIT 1;";
        $query = Yii::$app->db
        ->createCommand($sql)
        ->bindValue(':category_id', $category_id)
        ->queryScalar();

        $code = $category_id.'PG'.($query != '' ? $query : 1);
        // จบการสร้าง code ###
}

if($name == 'position_name'){
    //สร้าง code ของ ตำแหน่ง
    $sql = "SELECT CAST(SUBSTRING_INDEX(code, 'N',-1) AS UNSIGNED)+1 FROM `categorise` WHERE `category_id` = :category_id ORDER BY CAST(SUBSTRING_INDEX(code, 'N',-1) AS UNSIGNED) DESC LIMIT 1;";
    $query = Yii::$app->db
    ->createCommand($sql)
    ->bindValue(':category_id', $category_id)
    ->queryScalar();
    $code = $category_id.'PN'.($query != '' ? $query : 1);
    // จบการสร้าง code ###
}

        $model = new Categorise([
            'code' => $code,
            'name' => $name,
            'category_id' => $category_id
        ]);

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                if($this->request->isAjax){
                    Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'status' => 'success',
                    'container' => '#hr-container'
                    // 'url' => Url::to(['/hr/categorise','name' => $model->name,'title' => $title])
                ];
            }
            }
        } else {
            $model->loadDefaultValues();
        }

        if($this->request->isAjax){
            Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'title' => $this->request->get('title'),
                    'content' => $this->renderAjax('create', [
                        'model' => $model,
                        // 'category_id' => $category_id
                    ])
                ];
            }else{
                return $this->render('create', [
                    'model' => $model,
                    // 'category_id' => $category_id
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
        $name = $this->request->get('name');
        $title = $this->request->get('title');
        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            if($this->request->isAjax){
                Yii::$app->response->format = Response::FORMAT_JSON;
                // return $this->goBack();
                    return [
                        'status' => 'success',
                        'container' => '#hr-container'
                    ];
                }else{
                    return $this->render('index', [
                        'title' => $title,
                        'name' => $name,
                        'model' => $model,
                    ]);
                }
        }
        if($this->request->isAjax){
            Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'title' => $title,
                    'content' => $this->renderAjax('create', [
                        'model' => $model,
                    ])
                ];
            }else{
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
