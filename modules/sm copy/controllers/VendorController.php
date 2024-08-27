<?php

namespace app\modules\sm\controllers;

use app\components\AppHelper;
use app\models\Categorise;
use app\modules\hr\models\UploadCsv;
use app\modules\sm\models\Vendor;
use app\modules\sm\models\VendorSearch;
use ruskid\csvimporter\CSVImporter;
use ruskid\csvimporter\CSVReader;
use ruskid\csvimporter\MultipleImportStrategy;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use yii\validators\DateValidator;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;
use Yii;

/**
 * VendorController implements the CRUD actions for Vendor model.
 */
class VendorController extends Controller
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
     * Lists all Vendor models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new VendorSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'vendor']);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Vendor model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => '<i class="fa-solid fa-eye"></i> ' . $model->title,
                'content' => $this->renderAjax('view', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('view', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Creates a new Vendor model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new Vendor([
            'name' => 'vendor',
            'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
        ]);

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return [
                    'status' => 'success',
                    'container' => '#sm-container',
                ];
            } else {
                return false;
            }
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAjax) {
            return [
                'title' => '<i class="fa-regular fa-pen-to-square"></i> สร้างใหม่',
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

    // ตรวจสอบความถูกต้อง
    public function actionValidator()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new Vendor();
        if ($this->request->isPost && $model->load($this->request->post())) {
            $requiredName = 'ต้องระบุ';
            // ตรวจสอบตำแหน่ง
            $model->code == '' ? $model->addError('code', $requiredName) : null;
            $model->title == '' ? $model->addError('title', $requiredName) : null;
            $model->data_json['address'] == '' ? $model->addError('data_json[address]', $requiredName) : null;
            // $model->data_json['phone'] == "" ? $model->addError('data_json[phone]', $requiredName) : null;
            $model->data_json['contact_name'] == '' ? $model->addError('data_json[contact_name]', $requiredName) : null;
            $model->data_json['bank_name'] == '' ? $model->addError('data_json[bank_name]', $requiredName) : null;
            $model->data_json['account_name'] == '' ? $model->addError('data_json[account_name]', $requiredName) : null;
            $model->data_json['account_number'] == '' ? $model->addError('data_json[account_number]', $requiredName) : null;

            foreach ($model->getErrors() as $attribute => $errors) {
                $result[\yii\helpers\Html::getInputId($model, $attribute)] = $errors;
            }
            if (!empty($result)) {
                return $this->asJson($result);
            }
        }
    }

    /**
     * Updates an existing Vendor model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post())) {
            // return $this->redirect(['view', 'id' => $model->id]);
            if ($this->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;

                $model->validate();
                $result = [];
                // The code below comes from ActiveForm::validate(). We do not need to validate the model
                // again, as it was already validated by save(). Just collect the messages.
                foreach ($model->getErrors() as $attribute => $errors) {
                    $result[\yii\helpers\Html::getInputId($model, $attribute)] = $errors;
                }
                if (!empty($result)) {
                    return $this->asJson($result);
                }

                if ($model->save()) {
                    return [
                        'status' => 'success',
                        'container' => '#sm-container',
                    ];
                }
            }
        } else {
            if ($this->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข',
                    'content' => $this->renderAjax('update', [
                        'model' => $model,
                    ]),
                ];
            } else {
                return $this->render('update', [
                    'model' => $model,
                ]);
            }
        }
    }

    public function actionImportCsv()
    {
        $model = new UploadCsv([
            'name' => 'vendor',
            'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
        ]);
        $basePath = Yii::getAlias('@app/web/import-csv/');
        AppHelper::CreateDir($basePath);

        $error = [];
        if ($model->load(Yii::$app->request->post())) {
            $model->file = UploadedFile::getInstance($model, 'file');
            $model->file->saveAs($basePath . $model->file->name);

            $importer = new CSVImporter();
            $filename = $basePath . $model->file->name;
            $importer->setData(new CSVReader([
                'filename' => $filename,
                'tableName' => Vendor::tableName(),
                'fgetcsvOptions' => [
                    'delimiter' => ';',
                ],
            ]));

            for ($x = 1; $x <= count($importer->getData()); $x++) {
                $data_check_error = $importer->getData()[$x][0];
                $data_check_error = explode(',', $data_check_error);
                if (count($data_check_error) != 13) {
                    array_push($error, 'ข้อมูลไม่ครบถ้วน');
                }
                if (null != Vendor::findOne(['code' => $data_check_error[0]])) {
                    array_push($error, 'มีเลขประจำตัวผู้เสียภาษี ' . $data_check_error[0] . ' อยู่ในระบบแล้ว');
                }
                if (strlen($data_check_error[2]) != 10) {
                    array_push($error, 'เบอร์โทรศัพท์ ' . $data_check_error[2] . ' ไม่ถูกต้อง (ต้องมี 10 หลัก)');
                }
            }
            if (empty($error)) {
                $numberRowsAffected = $importer->import(new MultipleImportStrategy([
                    'tableName' => Vendor::tableName(),  // change your model names accordingly
                    'configs' => [
                        [
                            'attribute' => 'name',
                            'value' => function ($data) {
                                return 'vendor';
                            },
                        ],
                        [
                            'attribute' => 'ref',
                            'value' => function ($data) {
                                return substr(Yii::$app->getSecurity()->generateRandomString(), 10);
                            },
                        ],
                        [
                            'attribute' => 'code',
                            'value' => function ($data) {
                                $data = explode(',', $data[0]);
                                return $data[0];
                            },
                        ],
                        [
                            'attribute' => 'title',
                            'allowEmptyValues' => false,
                            'value' => function ($data) {
                                $data = explode(',', $data[0]);
                                return $data[1];
                            },
                        ],
                        [
                            'attribute' => 'data_json',
                            'value' => function ($data) {
                                $data = explode(',', $data[0]);
                                $jsonData = [
                                    'fax' => $data[7],
                                    'phone' => $data[2],
                                    'email' => $data[3],
                                    'address' => $data[4],
                                    'bank_name' => $data[10],
                                    'account_name' => $data[8],
                                    'contact_name' => $data[6],
                                    'account_number' => $data[9],
                                ];

                                return Json::encode($jsonData);
                            },
                        ],
                    ],
                ]));
                unlink($filename);
                Yii::$app->session->setFlash('data', [
                    'status' => true,
                    'error' => $error,
                ]);
                return $this->redirect(['import-status']);
            } else {
                unlink($filename);
                Yii::$app->session->setFlash('data', [
                    'status' => false,
                    'error' => $error,
                ]);
                return $this->redirect(['import-status']);
            }

            // return var_dump($importer->getData());
        } else {
            return $this->render('import_csv',
                ['model' => $model,
                    'error' => $error,
                    'success' => false]);
        }
    }

    public function actionImportStatus()
    {
        $data = Yii::$app->session->getFlash('data', []);
        $status = isset($data['status']) ? $data['status'] : false;
        $error = isset($data['error']) ? $data['error'] : [];
        return $this->render('import-status', [
            'status' => $status,
            'error' => $error
        ]);
    }

    /**
     * Deletes an existing Vendor model.
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
     * Finds the Vendor model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Vendor the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Vendor::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
