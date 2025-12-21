<?php

namespace app\modules\am\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\components\AppHelper;
use app\components\ModalHelper;
use app\modules\am\models\Asset;
use yii\web\NotFoundHttpException;
use app\modules\am\models\AssetDetail;
use app\modules\am\models\AssetDetailSearch;

/**
 * VehicleTaxController implements the CRUD actions for AssetDetail model.
 */
class VehicleTaxController extends Controller
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
        $code = $this->request->get('code');
        $searchModel = new AssetDetailSearch([
            'name' => 'vehicle_tax',
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
            'footer' => ModalHelper::modalFooterUpdateDeleteClose($id),
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
            'name' => 'vehicle_tax'
        ]);

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
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

        // แปลงวันที่หลักกลับเป็น d/m/Y (พ.ศ.)
        if ($model->date_start) {
            $model->date_start = AppHelper::ConvertToThai($model->date_start);
        }
        if ($model->date_end) {
            $model->date_end = AppHelper::ConvertToThai($model->date_end);
        }

        // แปลงวันที่ใน JSON กลับเป็น พ.ศ.
        if (is_array($model->data_json)) {
            $data = $model->data_json;
            foreach ($data as $key => $value) {
                // เช็คว่าเป็น format วันที่ของ DB (YYYY-MM-DD)
                if (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                    $data[$key] = AppHelper::ConvertToThai($value);
                }
            }
            $model->data_json = $data;
        }

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {

                if (!empty($model->date_start)) {
                    $model->date_start = AppHelper::DateToDb($model->date_start);
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
