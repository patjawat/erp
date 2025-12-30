<?php

namespace app\modules\am\controllers;

use Yii;
use yii\helpers\Html;
use yii\web\Response;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\UserHelper;
use app\components\ModalHelper;
use app\modules\am\models\Asset;
use yii\web\NotFoundHttpException;
use app\modules\am\models\AssetDetail;
use app\modules\am\models\AssetDetailSearch;

/**
 * borrowController implements the CRUD actions for AssetDetail model.
 */
class BorrowController extends Controller
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
     * Lists all AssetDetail models.
     *
     * @return string
     */
    public function actionIndex()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $code = $this->request->get('code');
        $searchModel = new AssetDetailSearch([
            'name' => 'borrow',
            'code' => $code,
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->setSort(['defaultOrder' => [
            'id' => SORT_DESC,
        ]]);


        if ($this->request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ])
            ];
        } else {
            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }

    /**
     * Displays a single AssetDetail model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);
        return [
            'title' => 'แสดงรายละเอียด',
            'content' => $this->renderAjax('view', [
                'model' => $model,

            ]),
            'footer' => Html::a('<i class="fa-regular fa-pen-to-square"></i>'.' แก้ไขใบคืน', ['/am/borrow/borrow-return', 'id' => $id,'title' => '<i class="fa-regular fa-pen-to-square"></i>'.' แก้ไข'], ['class' => 'btn btn-warning open-modal', 'data' => ['size' => 'modal-lg']]) .
            Html::button('<i class="fa-solid fa-xmark"></i> ปิด', ['class' => 'btn btn-secondary pull-left', 'data-bs-dismiss' => "modal"]),
        ];
    }

    /**
     * Creates a new AssetDetail model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $code = $this->request->get('code');
        $model = new AssetDetail([
            'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
            'code' => $code,
            'name' => 'borrow'
        ]);
        $model->emp_id = 8;

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $model->is_borrowed = true;

                if (!empty($model->date_start)) {
                    $model->date_start = AppHelper::DateToDb($model->date_start);
                }
                if (!empty($model->date_end)) {
                    $model->date_end = AppHelper::DateToDb($model->date_end);
                }
                $asset = Asset::findOne(['code' => $model->code]);
                $model->asset_id = $asset->id ?? 0;
                $model->save();
                return [
                    'status' => 'success'
                ];
            }
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAjax) {
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('create', [
                    'model' => $model,
                ]),
                'footer' =>  ModalHelper::modalFooterSaveClose(),
            ];
        } else {

            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing AssetDetail model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);
        $model->date_start = AppHelper::convertToThai($model->date_start);
        $model->date_end = AppHelper::convertToThai($model->date_end);


        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {

                if (!empty($model->date_start)) {
                    $model->date_start = AppHelper::DateToDb($model->date_start);
                }
                if (!empty($model->date_end)) {
                    $model->date_end = AppHelper::DateToDb($model->date_end);
                }
                $asset = Asset::findOne(['code' => $model->code]);
                $model->asset_id = $asset->id ?? 0;
                $model->save();
                return [
                    'status' => 'success'
                ];
            }
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAjax) {
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('update', [
                    'model' => $model,
                ]),
                'footer' =>  ModalHelper::modalFooterSaveClose(),
            ];
        } else {

            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    public function actionBorrowReturn($id)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);
        $model->date_start = AppHelper::convertToThai($model->date_start);
        $model->date_end = AppHelper::convertToThai($model->date_end);

        // แปลงวันที่ใน JSON
        $json = $model->data_json; // ดึง array ออกมาก่อน
        if (!empty($json['actual_date'])) {
            $json['actual_date'] = AppHelper::convertToThai($json['actual_date']);
        }
        $model->data_json = $json; // ใส่กลับเข้าไป

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $me = UserHelper::GetEmployee();
                $model->is_borrowed = false;
                $model->staff_id = $me->id;
                if (!empty($model->date_start)) {
                    $model->date_start = AppHelper::DateToDb($model->date_start);
                }
                if (!empty($model->date_end)) {
                    $model->date_end = AppHelper::DateToDb($model->date_end);
                }

                $asset = Asset::findOne(['code' => $model->code]);
                $model->asset_id = $asset->id ?? 0;
                $model->save();
                return [
                    'status' => 'success'
                ];
            }
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAjax) {
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('_form_borrow_return', [
                    'model' => $model,
                ]),
                'footer' =>  ModalHelper::modalFooterSaveClose(),
            ];
        } else {

            return $this->render('_form_borrow_return', [
                'model' => $model,
            ]);
        }
    }

    public function actionPrintReceipt($id)
    {
        $model = $this->findModel($id);
        return $this->render('print-receipt', [
            'model' => $model
        ]);
    }
    /**
     * Deletes an existing AssetDetail model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $this->findModel($id)->delete();
        return [
            'status' => 'success'
        ];
    }

    /**
     * Finds the AssetDetail model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return AssetDetail the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = AssetDetail::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
