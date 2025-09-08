<?php

namespace app\modules\plan\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use kartik\form\ActiveForm;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use app\modules\plan\models\PlanItem;
use app\modules\plan\models\PlanCategory;
use app\modules\plan\models\PlanItemSearch;

/**
 * PlanItemController implements the CRUD actions for PlanItem model.
 */
class PlanItemController extends Controller
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
     * Lists all PlanItem models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new PlanItemSearch([
             'name' => 'plan_item'
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single PlanItem model.
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
     * Creates a new PlanItem model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    
    public function actionCreate()
    {
        $model = new PlanItem([
            'name' => 'plan_item'
        ]);

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            if ($model->load($this->request->post())) {
                if ($model->save()) {
                    return [
                        'status' => 'success',
                        'message' => 'บันทึกข้อมูลสำเร็จ',
                        'id' => $model->id,
                    ];
                } else {
                    // validation error
                    return ActiveForm::validate($model);
                }
            }

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('create', [
                    'model' => $model,
                ]),
            ];
        }

        // ถ้าไม่ใช่ ajax → render แบบปกติ
        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->plan_type_id = $model->planCategory->planType->code ?? null;

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            if ($model->load($this->request->post())) {
                if ($model->save()) {
                    return [
                        'status' => 'success',
                        'message' => 'แก้ไขข้อมูลสำเร็จ',
                        'id' => $model->id,
                    ];
                } else {
                    return ActiveForm::validate($model);
                }
            }

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('update', [
                    'model' => $model,
                ]),
            ];
        }

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionDelete($id)
    {
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $this->findModel($id)->delete();
            return $this->redirect(['index']);
        }
    }

        public function actionGetPlanCategory()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $categoryId = $parents[0];
                    $out = PlanCategory::find()
                        ->where(['category_id' => $categoryId, 'name' => 'plan_category'])
                        ->select(['code as id', 'title as name'])
                        ->asArray()
                        ->all();
                return ['output' => $out, 'selected' => ''];
            }
        }
        return ['output' => '', 'selected' => ''];
    }


        public function actionGenerateCode($category_id)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $nextCode = PlanItem::generateNextCode($category_id);

        return [
            'success' => true,
            'code' => $nextCode,
        ];
    }



    /**
     * Finds the PlanItem model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return PlanItem the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PlanItem::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
