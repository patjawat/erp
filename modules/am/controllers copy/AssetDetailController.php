<?php

namespace app\modules\am\controllers;

use app\components\AppHelper;
use app\modules\am\models\Asset;
use app\modules\am\models\AssetDetail;
use app\modules\am\models\AssetDetailSearch;
use app\modules\am\models\AssetItem;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use Yii;

/**
 * AssetDetailController implements the CRUD actions for AssetDetail model.
 */
class AssetDetailController extends Controller
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

    // ตรวจสอบความถูกต้อง
    public function actionValidator()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new AssetDetail();

        if ($this->request->isPost && $model->load($this->request->post())) {
            // return $model;
            $requiredName = 'ต้องระบุ';
            // ตรวจสอบการบำรุงรักษา MA
            if ($model->name == 'ma') {
                // $model->data_json['status'] == "" ? $model->addError('data_json[status]', 'สถานะต้องไม่ว่าง') : null;
                // $model->date_start == "" ? $model->addError('date_start', $requiredName) : null;
                // if (\DateTime::createFromFormat('d/m/Y', $model->date_start)->format('Y') < 2500) {
                //     $model->addError('date_start', "รูปแบบ พ.ศ.");
                // }
                // foreach ($model->ma as $index => $item) {
                //     $model->ma[$index]["item"] == "" ? $model->addError('ma[' . $index . '][item]', $requiredName) : null;
                //     $model->ma[$index]["ma_status"] == "" ? $model->addError('ma[' . $index . '][ma_status]', $requiredName) : null;
                // }
            }

            // ตรวจสอบข้อมูล พรบ./การต่อภาษี
            if ($model->name == 'tax_car') {
                $model->date_end == '__/__/____' ? $model->addError('date_end', $requiredName) : null;
                $model->date_start == '__/__/____' ? $model->addError('date_start', $requiredName) : null;
                $model->data_json['price'] == '' ? $model->addError('data_json[price]', $requiredName) : null;
                $model->data_json['price1'] == '' ? $model->addError('data_json[price1]', $requiredName) : null;

                $model->data_json['company1'] == '' ? $model->addError('data_json[company1]', $requiredName) : null;
                $model->data_json['number1'] == '' ? $model->addError('data_json[number1]', $requiredName) : null;
                $model->data_json['date_start1'] == '' ? $model->addError('data_json[date_start1]', $requiredName) : null;
                $model->data_json['date_end1'] == '' ? $model->addError('data_json[date_end1]', $requiredName) : null;
                $model->data_json['sale1'] == '' ? $model->addError('data_json[sale1]', $requiredName) : null;
                $model->data_json['phone1'] == '' ? $model->addError('data_json[phone1]', $requiredName) : null;
            }

            foreach ($model->getErrors() as $attribute => $errors) {
                $result[\yii\helpers\Html::getInputId($model, $attribute)] = $errors;
            }
            if (!empty($result)) {
                return $this->asJson($result);
            }
        }
    }

    /**
     * Lists all AssetDetail models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $title = $this->request->get('title');
        $name = $this->request->get('name');
        $id = $this->request->get('id');
        $code = $this->request->get('code');

        $searchModel = new AssetDetailSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->where(['name' => $name]);
        if ($name == 'tax_car') {
            $dataProvider->query->andWhere(['code' => $code]);
            $dataProvider->sort->defaultOrder = ['date_start' => SORT_DESC];
        }
        // Yii::$app->response->format = Response::FORMAT_JSON;
        // return AssetItem::find()->where(["code"=>Asset::find()->where(['id'=>421])->one()->asset_item])->one();
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $title,
                'content' => $this->renderAjax($name . '/index', [
                    'id' => $id,
                    'code' => $code,
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                    'model' => $id == ''
                        ? ''
                        : (Asset::find()->where(['id' => $id])->one() ? AssetItem::find()->where(['code' => Asset::find()->where(['id' => $id])->one()->asset_item])->one() : ''),
                    'model_asset' => $id == '' ? '' : Asset::find()->where(['id' => $id])->one(),
                    'id_category' => $id == ''
                        ? ''
                        : (Asset::find()->where(['id' => $id])->one() ? AssetItem::find()->where(['code' => Asset::find()->where(['id' => $id])->one()->asset_item])->one()->id : ''),
                ]),
            ];
        } else {
            return $this->render($name . '/index', [
                'id' => $id,
                'code' => $code,
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
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionCreate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $name = $this->request->get('name');
        $category_id = $this->request->get('category_id');
        $title = $this->request->get('title');
        $id = $this->request->get('id');

        $asset = Asset::findOne($id);

        $model = new AssetDetail([
            'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
            'name' => $name,
            'code' => $asset->code,
        ]);

        $old_data_json = $model->data_json;
        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $model->date_start = AppHelper::DateToDb($model->date_start);
                $model->date_end = AppHelper::DateToDb($model->date_end);

                // ถ้าเป็นประการต่อภาษี
                if ($model->name == 'tax_car') {
                    $carTaxObj = [
                        'date_start1' => AppHelper::DateToDb($model->data_json['date_start1']),
                        'date_end1' => AppHelper::DateToDb($model->data_json['date_end1']),
                        'date_start2' => AppHelper::DateToDb($model->data_json['date_start2']),
                        'date_end2' => AppHelper::DateToDb($model->data_json['date_end2']),
                    ];
                    $model->data_json = ArrayHelper::merge($model->data_json, $carTaxObj);
                }

                if ($model->save()) {
                    return [
                        'status' => 'success',
                        'container' => '#am-container',
                    ];
                } else {
                    return [
                        'status' => 'error',
                        'container' => '#am-container',
                    ];
                }
            }
        } else {
            $model->loadDefaultValues();
        }
        if ($name == 'tax_car') {
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax($name . '/create', [
                    'model' => $model,
                    'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
                ]),
            ];
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('create', [
                    'model' => $model,
                    'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
                ]),
            ];
        } else {
            return $this->render('create', [
                'model' => $model,
                'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
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
        $model = $this->findModel($id);
        $name = $this->request->get('name');
        $model->date_start = AppHelper::DateFormDb($model->date_start);
        $model->date_end = AppHelper::DateFormDb($model->date_end);

        // ถ้าเป็นประการต่อภาษีแปลงวันที่ให้อยู่ในรูปแบบ thai format ก่อน
        if ($model->name == 'tax_car') {
            $findCartex = [
                'date_start1' => AppHelper::DateFormDb($model->data_json['date_start1']),
                'date_end1' => AppHelper::DateFormDb($model->data_json['date_end1']),
                'date_start2' => AppHelper::DateFormDb($model->data_json['date_start2']),
                'date_end2' => AppHelper::DateFormDb($model->data_json['date_end2']),
            ];
            $model->data_json = ArrayHelper::merge($model->data_json, $findCartex);
        }

        // การบันทึก
        if ($this->request->isPost && $model->load($this->request->post())) {
            $model->date_start = AppHelper::DateToDb($model->date_start);
            $model->date_end = AppHelper::DateToDb($model->date_end);

            // ถ้าเป็นประการต่อภาษี
            if ($model->name == 'tax_car') {
                $carTaxObj = [
                    'date_start1' => AppHelper::DateToDb($model->data_json['date_start1']),
                    'date_end1' => AppHelper::DateToDb($model->data_json['date_end1']),
                    'date_start2' => AppHelper::DateToDb($model->data_json['date_start2']),
                    'date_end2' => AppHelper::DateToDb($model->data_json['date_end2']),
                ];
                $model->data_json = ArrayHelper::merge($model->data_json, $carTaxObj);
            }

            if ($model->save()) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'status' => 'success',
                    'container' => '#am-container',
                    'res' => $model,
                ];
            } else {
                return [
                    'status' => 'error',
                    'container' => '#am-container',
                ];
            }
        }
        if ($name == 'tax_car') {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax($name . '/update', [
                    'model' => $model,
                    'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
                ]),
            ];
        }
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => '<i class="fa-regular fa-pen-to-square me-1"></i>' . $this->request->get('title'),
                'content' => $this->renderAjax('update', [
                    'model' => $model,
                    'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
                ]),
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
        $model = $this->findModel($id)->delete();
        $container = $this->request->get('container');

        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'status' => 'success',
            'data' => $model,
            'container' => '#' . $container,
            'close' => true,
        ];
    }

    public function actionTest()
    {
        $model = new AssetDetail();
        Yii::$app->response->format = Response::FORMAT_JSON;
        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                // $item["items"] = CategoriseHelper::CategoriseByCodeName($model->category_id,"asset_item")->data_json["ma_items"][$item["items"]];
                // $model->data_json = $item;
                // $model->data_json["items"] = CategoriseHelper::CategoriseByCodeName($model->category_id,"asset_item")->one()->data_json["ma_items"][$model->data_json["item"]];
                return [
                    'res' => $model,
                ];
            }
        }
        return [
            'status' => 'success',
            'container' => '#am-container',
            'data' => $model,
        ];
    }

    public function actionViewHistoryMa()
    {
        $title = $this->request->get('title');
        $name = $this->request->get('name');
        $id_category = $this->request->get('id_category');
        $id = $this->request->get('id');
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = AssetDetail::findOne(['id' => $id]);
        return [
            'title' => $title,
            'content' => $this->renderAjax($name . '/view', [
                'model' => $model,
                'id_category' => $id_category,
            ]),
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
