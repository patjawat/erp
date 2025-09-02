<?php

namespace app\modules\planv2\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use app\models\Categorise;
use yii\filters\VerbFilter;
use app\components\AppHelper;
use yii\web\NotFoundHttpException;
use app\modules\planv2\models\PlanItem;
use app\modules\planv2\models\PlanOrder;
use app\modules\planv2\models\PlanOrderSearch;

/**
 * PersonnelController implements the CRUD actions for PlanOrder model.
 */
class PersonnelController extends Controller
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
         $dataProvider->query->andFilterWhere(['plan_group_id' => 'personnel']);

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
            'plan_group_id' => 'personnel', // Default to material type
            'plan_category_id' => 'PE',
        ]);

        if ($model->load(Yii::$app->request->post())) {

            $model->save(false);
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', ['model' => $model]);
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

        if ($this->request->isPost && $model->load($this->request->post())) {
             Yii::$app->response->format = Response::FORMAT_JSON;
             $model->save(false);
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'items' => $items
        ]);
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



    public function actionGetPlanItem()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $planCategoryId = $parents[0];

                    $out = Categorise::find()
                        ->where(['category_id' => $planCategoryId, 'name' => 'new_plan_item'])
                        ->select(['code as id', 'title as name'])
                        ->asArray()
                        ->all();
        
                return ['output' => $out, 'selected' => ''];
            }
        }
        return ['output' => '', 'selected' => ''];
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
