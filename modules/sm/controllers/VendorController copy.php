<?php

namespace app\modules\sm\controllers;
use Yii;
use app\models\Categorise;
use app\models\CategoriseSearch;
use app\modules\sm\models\Vendor;
use app\modules\sm\models\VendorSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use app\modules\hr\models\UploadCsv;
use ruskid\csvimporter\CSVImporter;
use ruskid\csvimporter\CSVReader;
use ruskid\csvimporter\MultipleImportStrategy;
use yii\web\UploadedFile;
use yii\validators\DateValidator;


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
        $dataProvider->query->where(['name' => 'Vendor']);
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
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Vendor model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Categorise([
            'ref' => substr(Yii::$app->getSecurity()->generateRandomString(),10),
            'name' => 'vendor'
        ]);

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                if($this->request->isAjax){
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
                        return [
                            'status' => 'success',
                            'container' => '#sm-container'
                            
                        ];
            }
            }
        } else {
            $model->loadDefaultValues();
        }
        if($this->request->isAjax){
            Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'title' => '<i class="fa-regular fa-pen-to-square"></i> สร้างใหม่',
                    'content' => $this->renderAjax('create', [
                        'model' => $model,
                    ])
                ];
            }else{
                return $this->render('create', [
                    'model' => $model,
                ]);
            }
    }


    // ตรวจสอบความถูกต้อง
    public function actionValidator()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new Fsn();
        if ($this->request->isPost && $model->load($this->request->post())) {

            // ตรวจระหัสซ้ำ
            $checkCode = Fsn::find()->where(['name' => $model->name, 'code' => $model->code])
                ->andWhere(new \yii\db\Expression('JSON_EXTRACT(data_json, "$.asset_type") = "' . $model->data_json['asset_type'] . '"'))
                ->one();
            if ($checkCode) {
                if (($checkCode->ref == $model->ref)) {
                    $codeStatus = false;
                } else {
                    $codeStatus = true;
                }
            } else {
                $codeStatus = false;
            }
            // จบตรวจสอลรหัสซ้ำ

            $requiredName = "ต้องระบุ";
            //ตรวจสอบตำแหน่ง
            if ($model->name == "asset_group") {
                $model->data_json['depreciation'] == "" ? $model->addError('data_json[depreciation]', $requiredName) : null;
                $model->data_json['service_life'] == "" ? $model->addError('data_json[service_life]', $requiredName) : null;
                $checkCode ? $model->addError('code', 'รหัสน้ำถูกใช้แล้ว') : null;
            }

            if ($model->name == "asset_name") {
                $model->data_json['asset_type'] == "" ? $model->addError('data_json[asset_type]', $requiredName) : null;
                $model->category_id == "" ? $model->addError('category_id', $requiredName) : null;
                $codeStatus ? $model->addError('code', 'รหัสน้ำถูกใช้แล้ว') : null;
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
            if($this->request->isAjax){
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

                if($model->save()){ 
                    return [
                        'status' => 'success',
                        'container' => '#sm-container'
                    ];
                }
                }
        }else{
            if($this->request->isAjax){
                Yii::$app->response->format = Response::FORMAT_JSON;
                    return [
                        'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข',
                        'content' => $this->renderAjax('update', [
                            'model' => $model,
                        ])
                    ];
                }else{
                    return $this->render('update', [
                        'model' => $model,
                    ]);
                }
        }
    }


    public function actionImportCsv()
    {
        $model = new UploadCsv();
        $error = [];
        if ($model->load(Yii::$app->request->post())) {
            $model->file = UploadedFile::getInstance($model, 'file');
            $model->file->saveAs('@app/modules/hr/uploads/' . $model->file->name);

            $importer = new CSVImporter();
            $filename = Yii::getAlias('@app/modules/hr/uploads/' . $model->file->name);
            $importer->setData(new CSVReader([
                'filename' => $filename,
                'tableName' => Employees::tableName(),
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
                if (null != Employees::findOne(['cid' => $data_check_error[1]])) {
                    array_push($error, 'CID ' . $data_check_error[1] . ' อยู่ในระบบแล้ว');
                }
                if (strlen($data_check_error[1]) != 13) {
                    array_push($error, 'CID ' . $data_check_error[1] . ' ไม่ถูกต้อง (ต้องมี 13 หลัก)');
                }
                $validator = new DateValidator();
                $validator->format = 'yyyy-MM-dd';
                if ($validator->validate($data_check_error[6])) {
                } else {
                    array_push($error, 'วันเกิด ' . $data_check_error[6] . ' ไม่ถูกต้อง (1980-01-01)');
                }
                if (strlen($data_check_error[7]) != 10) {
                    array_push($error, 'เบอร์โทรศัพท์ ' . $data_check_error[7] . ' ไม่ถูกต้อง (ต้องมี 10 หลัก)');
                }
            }
            if (empty($error)) {
                $numberRowsAffected = $importer->import(new MultipleImportStrategy([
                    'tableName' => Employees::tableName(), // change your model names accordingly
                    'configs' => [
                        [
                            'attribute' => 'user_id',
                            'value' => function ($data) {
                                $data = explode(',', $data[0]);
                                return $data[0];
                            },
                        ],
                        [
                            'attribute' => 'cid',
                            'allowEmptyValues' => false,
                            'value' => function ($data) {
                                $data = explode(',', $data[0]);
                                return $data[1];
                            },
                        ],
                        [
                            'attribute' => 'gender',
                            'value' => function ($data) {
                                $data = explode(',', $data[0]);
                                return $data[2];
                            },
                        ],
                        [
                            'attribute' => 'prefix',
                            'value' => function ($data) {
                                $data = explode(',', $data[0]);
                                return $data[3];
                            },
                        ],
                        [
                            'attribute' => 'fname',
                            'value' => function ($data) {
                                $data = explode(',', $data[0]);
                                return $data[4];
                            },
                        ],
                        [
                            'attribute' => 'lname',
                            'value' => function ($data) {
                                $data = explode(',', $data[0]);
                                return $data[5];
                            },
                        ],
                        [
                            'attribute' => 'birthday',
                            'value' => function ($data) {
                                $data = explode(',', $data[0]);
                                return $data[6];
                            },
                        ],
                        [
                            'attribute' => 'phone',
                            'value' => function ($data) {
                                $data = explode(',', $data[0]);
                                return $data[7];
                            },
                        ],
                        [
                            'attribute' => 'email',
                            'value' => function ($data) {
                                $data = explode(',', $data[0]);
                                return $data[8];
                            },
                        ],
                        [
                            'attribute' => 'address',
                            'value' => function ($data) {
                                $data = explode(',', $data[0]);
                                return $data[9];
                            },
                        ],
                        [
                            'attribute' => 'zipcode',
                            'value' => function ($data) {
                                $data = explode(',', $data[0]);
                                return $data[10];
                            },
                        ],
                        [
                            'attribute' => 'position_name',
                            'value' => function ($data) {
                                $data = explode(',', $data[0]);
                                return $data[11];
                            },
                        ],
                        [
                            'attribute' => 'status',
                            'value' => function ($data) {
                                $data = explode(',', $data[0]);
                                return $data[12];
                            },
                        ],
                    ],

                ]));
                unlink($filename);
            } else {
                unlink($filename);
                return $this->render('import_csv',
                    ['model' => $model,
                        'error' => $error,
                        'success' => true]);
            }

            // return var_dump($importer->getData());
            return $this->render('import_csv',
                ['model' => $model,
                    'popup' => false]);
        } else {
            return $this->render('import_csv',
                ['model' => $model,
                    'error' => $error,
                    'success' => false]);
        }

        return [
            'title' => 'นำเข้า CSV',
            'content' => $this->renderAjax('import_csv',
                ['model' => $model,
                    'popup' => true]),
        ];

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
