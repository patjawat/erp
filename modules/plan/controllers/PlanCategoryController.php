<?php

namespace app\modules\plan\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use kartik\form\ActiveForm;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use app\modules\plan\models\PlanCategory;
use app\modules\plan\models\PlanCategorySearch;

/**
 * PlanCategoryController implements the CRUD actions for PlanCategory model.
 */
class PlanCategoryController extends Controller
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
     * Lists all PlanCategory models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new PlanCategorySearch([
            'name' => 'plan_category'
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single PlanCategory model.
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
     * Creates a new PlanCategory model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */

    public function actionCreate()
    {
        $model = new PlanCategory(['name' => 'plan_category']);

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
    public function actionGenerateCode($category_id)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $nextCode = PlanCategory::generateNextCode($category_id);

        return [
            'success' => true,
            'code' => $nextCode,
        ];
    }

    /**
     * Finds the PlanCategory model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return PlanCategory the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PlanCategory::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
