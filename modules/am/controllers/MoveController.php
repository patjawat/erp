<?php

namespace app\modules\am\controllers;

use Yii;
use yii\helpers\Html;
use yii\helpers\Json;
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
use app\modules\approveV2\models\Approve;
use app\modules\am\models\AssetDetailSearch;

/**
 * moveController implements the CRUD actions for AssetDetail model.
 */
class MoveController extends Controller
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
            'name' => 'asset-move',
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
            'footer' => Html::button('<i class="fa-solid fa-xmark"></i> ปิด', ['class' => 'btn btn-secondary pull-left', 'data-bs-dismiss' => "modal"]),
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
        $me = UserHelper::GetEmployee();
        $code = $this->request->get('code');
        $model = new AssetDetail([
            'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
            'code' => $code,
            'name' => 'asset-move'
        ]);

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {

                if (!empty($model->date_start)) {
                    $model->date_start = AppHelper::DateToDb($model->date_start);
                }
                $model->emp_id = $me->id;

                $asset = Asset::findOne(['code' => $model->code]);
                $model->asset_id = $asset->id ?? 0;
                $model->status = 'Pending';
                if ($model->save()) {
                    $newApprove = new Approve();
                    $newApprove->name = 'asset-move';
                    $newApprove->from_id = $model->id;
                    $newApprove->emp_id = $model->data_json['leader_id'] ?? 0;
                    $newApprove->status = 'Pending';
                    $newApprove->title = 'หน.เห็นชอบ';
                    $newApprove->data_json = ['label' => 'เห็นชอบ'];
                    $newApprove->level = 1;
                    $newApprove->save(false);
                };
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
        if (!empty($model->date_start)) {
            $model->date_start = AppHelper::ConvertToThai($model->date_start);
        }
        $oldJson = is_array($model->data_json) ? $model->data_json : (Json::decode($model->data_json, true) ?? []);
        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {

                if (!empty($model->date_start)) {
                    $model->date_start = AppHelper::DateToDb($model->date_start);
                }
                $newJson = ArrayHelper::merge($oldJson, $model->data_json);
                $model->data_json = $newJson;
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


    // ตรวจสอบความถูกต้อง
    public function actionValidator()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new AssetDetail();
        $requiredName = 'ต้องระบุ';
        if ($this->request->isPost && $model->load($this->request->post())) {

            $model->data_json['leader_id'] == "" ? $model->addError('data_json[leader_id]', $requiredName) : null;
            $model->data_json['location'] == "" ? $model->addError('data_json[location]', $requiredName) : null;
            $model->data_json['reason'] == "" ? $model->addError('data_json[reason]', $requiredName) : null;
            $model->date_start == "" ? $model->addError('date_start', $requiredName) : null;

            foreach ($model->getErrors() as $attribute => $errors) {
                $result[\yii\helpers\Html::getInputId($model, $attribute)] = $errors;
            }
            if (!empty($result)) {
                return $this->asJson($result);
            }
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
