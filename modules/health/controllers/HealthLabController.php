<?php

namespace app\modules\health\controllers;

use Yii;
use yii\web\Controller;
use yii\bootstrap5\Html;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use app\modules\health\models\HealthLab;
use app\modules\health\models\HealthLabSearch;

/**
 * HealthLabController implements the CRUD actions for HealthLab model.
 */
class HealthLabController extends Controller
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
     * Lists all HealthLab models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new HealthLabSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single HealthLab model.
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
     * Creates a new HealthLab model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new HealthLab();

        if ($this->request->isPost) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

            if ($model->load($this->request->post())) {
                // จัดการข้อมูลก่อนบันทึก (ถ้ามี) เช่น Encode JSON
                if (is_array($model->data_json)) {
                    $model->data_json = json_encode($model->data_json);
                }

                if ($model->save()) {
                    return [
                        'forceReload' => '#pjax-container', // หรือ ID ที่คุณใช้ reload grid
                        'status' => 'success',
                        'message' => 'เพิ่มรายการ LAB เรียบร้อยแล้ว',
                    ];
                }
            }

            return [
                'status' => 'error',
                'message' => 'ไม่สามารถบันทึกข้อมูลได้',
            ];
        }
        // สำหรับการเปิด Modal ครั้งแรก
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return [
                'title' => 'เพิ่มรายการ LAB ใหม่',
                'size' => 'modal-lg', // ส่งขนาดไปด้วยตามที่ JS คุณรองรับ
                'content' => $this->renderAjax('create', [
                    'model' => $model,
                ]),
                'footer' =>
                Html::button('บันทึกข้อมูล', ['class' => 'btn btn-primary  form-submit', 'type' => 'submit', 'data' => ['id' => 'form']]) .
                    Html::button('ปิด', ['class' => 'btn btn-secondary', 'data-bs-dismiss' => 'modal'])
            ];
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing HealthLab model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if ($this->request->isPost) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

            if ($model->load($this->request->post())) {
                // จัดการข้อมูลก่อนบันทึก (ถ้ามี) เช่น Encode JSON
                if (is_array($model->data_json)) {
                    $model->data_json = json_encode($model->data_json);
                }

                if ($model->save()) {
                    return [
                        'forceReload' => '#pjax-container', // หรือ ID ที่คุณใช้ reload grid
                        'status' => 'success',
                        'message' => 'เพิ่มรายการ LAB เรียบร้อยแล้ว',
                    ];
                }
            }

            return [
                'status' => 'error',
                'message' => 'ไม่สามารถบันทึกข้อมูลได้',
            ];
        }
        // สำหรับการเปิด Modal ครั้งแรก
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return [
                'title' => 'เพิ่มรายการ LAB ใหม่',
                'size' => 'modal-lg', // ส่งขนาดไปด้วยตามที่ JS คุณรองรับ
                'content' => $this->renderAjax('create', [
                    'model' => $model,
                ]),
                'footer' =>
                Html::button('บันทึกข้อมูล', ['class' => 'btn btn-primary  form-submit', 'type' => 'submit', 'data' => ['id' => 'form']]) .
                    Html::button('ปิด', ['class' => 'btn btn-secondary', 'data-bs-dismiss' => 'modal'])
            ];
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing HealthLab model.
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
     * Finds the HealthLab model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return HealthLab the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = HealthLab::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
