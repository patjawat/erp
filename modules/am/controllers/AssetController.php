<?php

namespace app\modules\am\controllers;

use yii;
use yii\helpers\Json;
use yii\web\Response;
use yii\db\Expression;
use yii\web\Controller;
use yii\web\UploadedFile;
use app\models\Categorise;
use app\models\UploadForm;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\SiteHelper;
use app\components\UserHelper;
use app\components\AssetHelper;
use app\modules\am\models\Asset;
use app\modules\sm\models\Vendor;
use ruskid\csvimporter\CSVReader;
use yii\web\NotFoundHttpException;
use ruskid\csvimporter\CSVImporter;
use app\components\CategoriseHelper;
use app\modules\hr\models\UploadCsv;
use app\modules\am\models\AssetSearch;
use app\modules\hr\models\Organization;
use ruskid\csvimporter\MultipleImportStrategy;

/**
 * AssetController implements the CRUD actions for Asset model.
 */
class AssetController extends Controller
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
     * Lists all Asset models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new AssetSearch([
             'asset_group' => 'EQUIP'
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->leftJoin('categorise at', 'at.code=asset.fsn_number');
        // Join กับ relation assetItem (ควรมีใน model Asset เช่น getAssetItem())
        $dataProvider->query->joinWith('assetItem');
        $dataProvider->query->andWhere('asset.deleted_at IS NULL');
         $dataProvider->query->andFilterWhere(['asset_items.asset_type_id' => $searchModel->asset_type_id]);
         $dataProvider->query->andFilterWhere(['asset_items.asset_category_id' => $searchModel->asset_category_id]);

        if (!isset($this->request->queryParams['AssetSearch'])) {
            // หายังไม่มีการค้นหาใดๆ ให้ แสดงเฉพาะทรัพย์สินที่ตัวเองรับผิดชอบ
            $user = UserHelper::GetEmployee();
            $dataProvider->query->andFilterWhere(['owner' => $user->cid]);
        } else {
            $dataProvider->query->andFilterWhere(['like', new Expression("JSON_EXTRACT(asset.data_json, '\$.budget_type')"), $searchModel->budget_type]);
            $dataProvider->query->andFilterWhere(['like', new Expression("JSON_EXTRACT(asset.data_json, '\$.method_get')"), $searchModel->method_get]);
            $dataProvider->query->andFilterWhere(['like', new Expression("JSON_EXTRACT(asset.data_json, '\$.po_number')"), $searchModel->po_number]);
            $dataProvider->query->andFilterWhere(['receive_date' => AppHelper::DateToDb($searchModel->q_receive_date)]);

            // ค้นหาคามกลุ่มโครงสร้าง
            $org1 = Organization::findOne($searchModel->q_department);
            // ถ้ามรกลุ่มย่อย
            if (isset($searchModel->q_department) && isset($org1) && $org1->lvl == 1) {
                $sql = 'SELECT t1.id, t1.root, t1.lft, t1.rgt, t1.lvl, t1.name, t1.icon
            FROM tree t1
            JOIN tree t2 ON t1.lft BETWEEN t2.lft AND t2.rgt AND t1.lvl = t2.lvl + 1
            WHERE t2.name = :name;';
                $querys = Yii::$app
                    ->db
                    ->createCommand($sql)
                    ->bindValue(':name', $org1->name)
                    ->queryAll();
                $arrDepartment = [];
                foreach ($querys as $tree) {
                    $arrDepartment[] = $tree['id'];
                }
                if (count($arrDepartment) > 0) {
                    $dataProvider->query->andWhere(['in', 'department', $arrDepartment]);
                }
            } else {
                $dataProvider->query->andFilterWhere(['department' => $searchModel->q_department]);
            }
            // จบการค้นหา

            $dataProvider->query->andFilterWhere(['at.category_id' => $searchModel->asset_type]);
            $dataProvider->query->andFilterWhere([
                'or',
                ['LIKE', 'asset.code', $searchModel->q],
                ['LIKE', new Expression("JSON_EXTRACT(asset.data_json, '\$.asset_name')"), $searchModel->q],
            ]);

            // ค้นหาตามอายุ
            if ($searchModel->price1 && !$searchModel->price2) {
                $dataProvider->query->andWhere(new \yii\db\Expression('price = ' . $searchModel->price1));
            }
            // ค้นหาระหว่างช่วงอายุ
            if ($searchModel->price1 && $searchModel->price2) {
                $dataProvider->query->andWhere(new \yii\db\Expression('price BETWEEN ' . $searchModel->price1 . ' AND ' . $searchModel->price2));
            }

            $dataProvider->setSort([
                'defaultOrder' => [
                    'code' => 'SORT_DESC',
                    'receive_date' => 'SORT_DESC',
                    // 'service_start_time' => SORT_DESC
                ],
            ]);

            if ($this->request->get('view')) {
                SiteHelper::setDisplay($this->request->get('view'));
            }
        }
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    // ตรวจสอบความถูกต้อง
    public function actionValidator()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new Asset();
        $requiredName = 'ต้องระบุ';
        if ($this->request->isPost && $model->load($this->request->post())) {
            $fsnAuto = $this->request->post('Asset');
            // ตรวจระหัสซ้ำ
            $checkCode = Asset::find()
                ->where(['code' => $model->code])
                ->andWhere(['<>', 'ref', $model->ref])
                ->andWhere(['not', ['code' => null]])
                ->andWhere(['not', ['code' => '']])
                ->one();
            //  return $checkCode;

            if ($checkCode) {
                $codeStatus = true;
            } else {
                $codeStatus = false;
            }

            //  return $model;
            // ตรวจสอลการลงปีงบประมาณ
            // return $model;

            if ($model->asset_group != 1 && $model->asset_group != 2) {  // ถ้าเป็นที่ดินไม่ต้องตรวจสอบปีงบประมาณ
                $model->data_json['budget_type'] == '' ? $model->addError('data_json[budget_type]', $requiredName) : null;
                $model->on_year == '' ? $model->addError('on_year', $requiredName) : null;
                $model->purchase == '' ? $model->addError('purchase', $requiredName) : null;
                $model->data_json['method_get'] == '' ? $model->addError('data_json[method_get]', $requiredName) : null;
                $model->data_json['vendor_id'] == '' ? $model->addError('data_json[vendor_id]', $requiredName) : null;

                $codeStatus ? $model->addError('code', 'หมายเลขครุภัณฑ์ซ้ำ') : null;

                // ถ้าสร้างรหัสอัตโนมัติ
                if (!isset($fsnAuto['fsn_auto']) || $fsnAuto['fsn_auto'] == '1') {
                }
                // ถ้ากำหนดรหัวเอง
                if (isset($fsnAuto['fsn_auto']) && $fsnAuto['fsn_auto'] == '0') {
                    $model->code == '' ? $model->addError('code', $requiredName) : null;
                }
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
     * Displays a single Asset model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        // Yii::$app->response->format = Response::FORMAT_JSON;

        $model = $this->findModel($id);
        // return $model->device_items;
        // $ids = ArrayHelper::getColumn($model->device_items, 'id');

        $searchModel = new AssetSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->leftJoin('categorise at', 'at.code=asset.asset_item');
        $dataProvider->query->andWhere(['in', 'asset.code', $model->device_items != null ? $model->device_items : '']);

        return $this->render('view', [
            'model' => $model,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionDepreciation($id)
    {
        $model = $this->findModel($id);
        $asset_name = isset($model->data_json['asset_name']) ? 'ค่าเสื่อมราคา' . $model->data_json['asset_name'] : '-';
        $title = $this->request->get('title') . isset($model->data_json['asset_name']) ? $model->data_json['asset_name'] : '-';
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => '<i class="fa-solid fa-chart-line"></i> ' . $asset_name,
                'content' => $this->renderAjax('depreciation_list', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('depreciation_list', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Creates a new Asset model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Asset([
            'asset_group' => 'EQUIP',
            'asset_item' => 0,
            'asset_status' => 0,
            'price' => 0,
            'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
        ]);

        $old_data_json = $model->data_json;
        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                $model->receive_date = AppHelper::DateToDb($model->receive_date);

                $convert_date = [
                    'expire_date' => AppHelper::DateToDb($model->data_json['expire_date']),
                    'inspection_date' => AppHelper::DateToDb($model->data_json['inspection_date']),
                ];

                $model->data_json = ArrayHelper::merge($old_data_json, $model->data_json,$convert_date);

                if ($model->save()) {
                    return $this->redirect(['view', 'id' => $model->id]);
                } else {
                    return $model->getErrors();
                }
                // return $this->redirect(['index']);
            }
        } else {
            // return $model->getErrors();
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Asset model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->receive_date = AppHelper::DateFormDb($model->receive_date);
        if ($model->ref == '') {
            $model->ref = substr(Yii::$app->getSecurity()->generateRandomString(), 10);
        }
        if (isset($model->data_json['item_options'])) {
            $model->item_options = $model->data_json['item_options'];
        }
        $old_data_json = $model->data_json;

        if ($this->request->isPost && $model->load($this->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $model->receive_date = AppHelper::DateToDb($model->receive_date);


                $convert_date = [
                    'expire_date' => AppHelper::DateToDb($model->data_json['expire_date']),
                    'inspection_date' => AppHelper::DateToDb($model->data_json['inspection_date']),
                ];


            $model->data_json = ArrayHelper::merge($old_data_json, $model->data_json,$convert_date);
            if ($model->save()) {
                $model->updateFsn();
                $this->CheckUpdateData($model);
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return $model->getErrors();
            }
        }

        return $this->render('update', [
            'model' => $model,
            // 'group' => $model->asset_group
        ]);
    }

    // update Spect ที่เป็น Cmputer
    public function actionUpdateComputer($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $title = $this->request->get('title');
        $model = $this->findModel($id);

        $old_data_json = $model->data_json;

        if ($this->request->isPost && $model->load($this->request->post())) {
            $model->data_json = ArrayHelper::merge($old_data_json, $model->data_json);
            // return $model->data_json;
            if ($model->save()) {
                $this->CheckUpdateData($model);
                return [
                    'status' => 'success',
                    'container' => '#am-container',
                    'close' => true
                ];
            } else {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return $model->getErrors();
            }
        }
        return [
            'title' => $title,
            'content' => $this->renderAjax('is_computer/_form_computer', ['model' => $model]),
        ];
    }

    private static function CheckUpdateData($model)
    {
        try {
        // บึนทึกยี่ห้ออัตโนมัติ
        $brand = $model->data_json['brand'];
        $modelBrand = Categorise::findOne(['name' => 'brand', 'title' => $brand]);
        if (!$modelBrand) {
            $modelBrandNew = new Categorise(['name' => 'brand', 'code' => $brand, 'title' => $brand]);
            $modelBrandNew->save();
        }

        } catch (\Throwable $th) {
    //throw $th;
}
        try {
        // บึนทึกรุ่นอัตโนมัติ
        $asset_model = $model->data_json['asset_model'];
        $assetModel = Categorise::findOne(['name' => 'asset_model', 'title' => $asset_model]);
        if (!$assetModel) {
            $assetModel = new Categorise(['name' => 'asset_model', 'code' => $asset_model, 'title' => $asset_model]);
            $assetModel->save();
        }
        } catch (\Throwable $th) {
    //throw $th;
}
try {
        // บึนทึกรุ่นอัตโนมัติ
        $os = $model->data_json['os'];
        $osModel = Categorise::findOne(['name' => 'os', 'title' => $os]);
        if (!$osModel) {
            $osModel = new Categorise(['name' => 'os', 'code' => $os, 'title' => $os]);
            $osModel->save();
        }
} catch (\Throwable $th) {
    //throw $th;
}

try {

        // บึนทึก CPU
        $cpu = $model->data_json['cpu'];
        $cpuModel = Categorise::findOne(['name' => 'cpu', 'title' => $cpu]);
        if (!$cpuModel) {
            $cpuModel = new Categorise(['name' => 'cpu', 'code' => $cpu, 'title' => $cpu]);
            $cpuModel->save();
        }
} catch (\Throwable $th) {
    //throw $th;
}
    }

    public function actionQrcode()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = $this->request->get('id');
        $model = $this->findModel($id);
        return [
            'title' => '<i class="fa-solid fa-qrcode"></i> QR-Code',
            'content' => $this->renderAjax('qr-code/view_qrcode', ['model' => $model]),
        ];
    }

    // ตั้งค่าขนาดกระดาษ qr-code
    public function actionQrcodeSetting()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = $this->request->get('id');
        $title = $this->request->get('title');
        return [
            'title' => $title,
            'content' => $this->renderAjax('qr-code/setting_qrcode'),
        ];
    }

    /**
     * Deletes an existing Asset model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if(!Yii::$app->user->can('admin')){
            return [
                'status' => 'error',
                'message' => 'ไม่มีสิทธิลบข้อมูล'
               ];
        }
        $model = $this->findModel($id);
        // return $model->deleted_at;
        // ตรวจสอบว่าถูกลบไปแล้วหรือยัง
        if ($model->deleted_at !== null) {
            return [
                'status' => 'error',
                'message' => 'รายการนี้ถูกลบไปแล้ว'
               ];
        }

        // ทำ Soft Delete
        $model->deleted_at = new Expression('NOW()');
        $model->deleted_by = Yii::$app->user->id;

        if ($model->save(false)) {
           return [
            'status' => 'success',
            'message' => 'ลบข้อมูลสำเร็จ'
           ];
        } else {
            return [
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการลบข้อมูล'
               ];
        }

    }

    // รายการที่ตกค้างหรือข้อมูลไม่ครบ
    public function actionOmit()
    {
        $sql = "SELECT a.id,g.title as group_name,a.code as asset_code,a.data_json->>'\$.asset_name' as asset_name,t.code as type_code,t.title as type_title,i.code as item_code,i.title as item_title FROM asset a
         LEFT JOIN categorise i ON i.code = a.asset_item AND i.name = 'asset_item'
         LEFT JOIN categorise t ON t.code = i.category_id AND t.name = 'asset_type'
         LEFT JOIN categorise g ON g.code = a.asset_group AND g.name = 'asset_group'
         WHERE a.asset_group <> 1 AND t.code IS NULL
         LIMIT 10000;";

        $models = Yii::$app->db->createCommand($sql)->queryAll();
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => 'รายการที่ยังไม่สมบรูณ์หรือข้อมูลไม่ครบ <code>' . count($models) . '</code> รายการ',
                'content' => $this->renderAjax('omit', ['models' => $models])
            ];
        } else {
            return $this->render('omit', ['models' => $models]);
        }
    }

    /**
     * Finds the Asset model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Asset the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Asset::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionSetting()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $name = $this->request->get('name');
        $data = $this->request->get('val');
        return $name . $data;
    }

    // เลือกการบันทึกทรัพย์สิน
    public function actionSelectType()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return [
            'title' => 'เลือกกลุ่ม',
            'content' => $this->renderAjax('select_type'),
        ];
    }

    public function actionImportCsv()
    {
        $model = new UploadForm();

        if (Yii::$app->request->isPost) {
            $model->csvFile = UploadedFile::getInstance($model, 'csvFile');
            if ($model->validate()) {
                $filePath = 'import-csv/' . $model->csvFile->baseName . '.' . $model->csvFile->extension;
                $model->csvFile->saveAs($filePath);

                // เรียกใช้ฟังก์ชันนำเข้าข้อมูล
                $this->importCsvToDatabase($filePath);
                Yii::$app->session->setFlash('success', 'CSV imported successfully.');
                return $this->render('import_csv', ['model' => $model, 'status' => 'success']);
            }
        }

        return $this->render('import_csv', ['model' => $model, 'status' => false]);
    }

    private function importCsvToDatabase($filePath)
    {
        // สร้าง prefix โดยใช้วันที่และเวลาปัจจุบัน
        $prefix = 'backup_' . date('Y_m_d_His') . '_asset';
        // สร้างคำสั่ง SQL
        $sql = "CREATE TABLE {$prefix} AS SELECT * FROM asset";
        // ดำเนินการคำสั่ง SQL
        Yii::$app->db->createCommand($sql)->execute();

        if (($handle = fopen($filePath, 'r')) !== false) {
            $firstLine = true;
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                if ($firstLine) {
                    $firstLine = false;  // Skip header row
                    continue;
                }

                // ตรวจสอบว่ามีข้อมูลในฐานข้อมูลหรือไม่ ถ้าไม่มีให้เพิ่มข้อมูลลงไป
                $assetType = isset($data[0]) ? $data[0] : null;
                $assetItemName = isset($data[1]) ? $data[1] : null;
                $assetCode = isset($data[2]) ? $data[2] : null;
                $vendorName = isset($data[4]) ? $data[4] : null;
                $methodGet = isset($data[9]) ? $data[9] : null;
                $purchaseText = isset($data[10]) ? $data[10] : null;
                $budgetType = isset($data[11]) ? $data[11] : null;
                $departmentName = isset($data[7]) ? $data[7] : null;
                $onYear = isset($data[8]) ? $data[8] : null;
                $price = isset($data[12]) ? $data[12] : null;
                $receiveDate = isset($data[6]) ? $data[6] : null;
                $assetStatus = isset($data[17]) ? $data[17] : null;

                $assetItem = AssetHelper::CheckAssetItem($assetType, $assetCode, $assetItemName);

                $checkAsset = $assetCode ? Asset::find()->where(['code' => $assetCode])->one() : null;

                // สมมติว่าคุณมี Model ชื่อ YourModel
                $model = $checkAsset ?? new Asset();
                $model->asset_group = 3;
                $model->asset_item = isset($assetItem->code) ? $assetItem->code : null;
                $model->code = $assetCode;
                $model->data_json = [
                    'vendor_id' => $vendorName ? AssetHelper::findByName('vendor', $vendorName) : null,
                    'method_get' => $methodGet ? AssetHelper::findByName('method_get', $methodGet) : null,
                    'budget_type' => $budgetType ? AssetHelper::findByName('budget_type', $budgetType) : null,
                    'asset_type_text' => $assetType,
                    'method_get_text' => $methodGet,
                    'purchase_text' => $purchaseText,
                    'budget_type_text' => $budgetType,
                    'department_name' => $departmentName,
                ];
                $model->purchase = $purchaseText ? AssetHelper::findByName('purchase', $purchaseText) : null;  // วิธีการได้มา
                $model->on_year = $onYear;
                $model->price = $price ? AppHelper::formatNumber($price) : 0;  // ราคา
                $model->receive_date = $receiveDate ? AppHelper::convertToYMD($receiveDate) : null;  // วันที่รับเข้า
                $model->asset_status = $assetStatus ? AssetHelper::findByName('asset_status', $assetStatus) : null;  // วิธีการได้มา

                $org = $departmentName ? Organization::find()->where(['name' => $departmentName])->one() : null;
                $model->department = $org && isset($org->id) ? $org->id : 0;  // หน่วยงานภายในตามโครงสร้าง

                // เพิ่มคอลัมน์ตามไฟล์ CSV
                $model->save(false);
            }

            fclose($handle);
        }
    }

    // public function actionImportCsv()
    // {
    //     $current_data;
    //     $model = new UploadCsv([
    //         'name' => 'vendor',
    //         'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
    //     ]);
    //     $basePath = Yii::getAlias('@app/web/import-csv/');
    //     AppHelper::CreateDir($basePath);

    //     $error = [];
    //     if ($model->load(Yii::$app->request->post())) {
    //         $model->file = UploadedFile::getInstance($model, 'file');
    //         $model->file->saveAs($basePath . $model->file->name);

    //         $importer = new CSVImporter();
    //         $filename = $basePath . $model->file->name;
    //         $importer->setData(new CSVReader([
    //             'filename' => $filename,
    //             'tableName' => Asset::tableName(),
    //             'fgetcsvOptions' => [
    //                 'delimiter' => ';',
    //             ],
    //         ]));

    //         for ($x = 1; $x <= count($importer->getData()); $x++) {
    //             $data_check_error = $importer->getData()[$x][0];
    //             $data_check_error = AppHelper::GetDataCsv(explode(',', $data_check_error));

    //             if (CategoriseHelper::TitleAndName($data_check_error[10], "purchase")->one() == null) {
    //                 $codeP = CategoriseHelper::CodePurchase();
    //                 $model_assetItemx = new Vendor([
    //                     "code" => (string) $codeP,
    //                     "name" => "purchase",
    //                     "title" => $data_check_error[10],
    //                 ]);
    //                 $model_assetItemx->save();
    //             }
    //             if (CategoriseHelper::TitleAndName($data_check_error[1], "asset_item")->one() == null) {

    //                 $model_assetItem = new Vendor([
    //                     "code" => substr($data_check_error[2], 0, 13),
    //                     "name" => "asset_item",
    //                     "title" => $data_check_error[1],
    //                 ]);
    //                 $model_assetItem->save();
    //             }
    //             if ($data_check_error[4] != "ไม่ระบุ") {
    //                 if (CategoriseHelper::TitleAndName($data_check_error[4], "vendor")->one() == null) {

    //                     $model_Vendor = new Vendor([
    //                         "code" => $data_check_error[5],
    //                         "name" => "vendor",
    //                         "title" => $data_check_error[4],
    //                     ]);
    //                     $model_Vendor->save();
    //                 }
    //             }
    //         }
    //         if (empty($error)) {
    //             $numberRowsAffected = $importer->import(new MultipleImportStrategy([
    //                 'tableName' => Asset::tableName(), // change your model names accordingly
    //                 'configs' => [
    //                     [
    //                         'attribute' => 'ref',
    //                         'value' => function ($data) {
    //                             return substr(Yii::$app->getSecurity()->generateRandomString(), 10);
    //                         },
    //                     ],
    //                     [
    //                         //ประเภทครุภัณฑ์
    //                         'attribute' => 'asset_group',
    //                         'value' => function ($data) {
    //                             return 3;
    //                         },
    //                     ],
    //                     [
    //                         //หมายเลขครุภัณฑ์
    //                         'attribute' => 'asset_item',
    //                         'allowEmptyValues' => false,
    //                         'value' => function ($data) {
    //                             $data = explode(',', $data[0]);
    //                             $data = AppHelper::GetDataCsv($data);
    //                             return substr($data[2], 0, 13);
    //                         },
    //                     ],
    //                     [
    //                         //หมายเลขครุภัณฑ์
    //                         'attribute' => 'code',
    //                         'allowEmptyValues' => false,
    //                         'value' => function ($data) {
    //                             $data = explode(',', $data[0]);
    //                             $data = AppHelper::GetDataCsv($data);
    //                             return $data[2];
    //                         },
    //                     ],
    //                     [
    //                         'attribute' => 'purchase',
    //                         'allowEmptyValues' => false,
    //                         'value' => function ($data) {
    //                             $data = explode(',', $data[0]);
    //                             $data = AppHelper::GetDataCsv($data);
    //                             if ($data[10] == "เฉพาะเจาะจง") {
    //                                 return 1;
    //                             } elseif ($data[10] == "บริจาค") {
    //                                 return 2;
    //                             } elseif ($data[10] == "ตกลงราคา") {
    //                                 return 3;
    //                             }
    //                             return;
    //                         },
    //                     ],
    //                     [
    //                         //วันที่รับเข้า
    //                         'attribute' => 'receive_date',
    //                         'allowEmptyValues' => false,
    //                         'value' => function ($data) {
    //                             $data = explode(',', $data[0]);
    //                             $data = AppHelper::GetDataCsv($data);
    //                             return date('Y-m-d', strtotime($data[7]));
    //                         },
    //                     ],
    //                     [
    //                         //วันที่รับเข้า
    //                         'attribute' => 'on_year',
    //                         'allowEmptyValues' => false,
    //                         'value' => function ($data) {
    //                             $data = explode(',', $data[0]);
    //                             $data = AppHelper::GetDataCsv($data);
    //                             return $data[8];
    //                         },
    //                     ],
    //                     [
    //                         //ราคา
    //                         'attribute' => 'price',
    //                         'allowEmptyValues' => false,
    //                         'value' => function ($data) {
    //                             $data = explode(',', $data[0]);
    //                             $data = AppHelper::GetDataCsv($data);
    //                             return $data[12] == "" ? 0 : $data[12];
    //                         },
    //                     ],
    //                     [
    //                         //หน่วยงานภายในตามโครงสร้าง
    //                         'attribute' => 'department',
    //                         'allowEmptyValues' => false,
    //                         'value' => function ($data) {
    //                             $data = explode(',', $data[0]);
    //                             $data = AppHelper::GetDataCsv($data);
    //                             $organization = Organization::find()->where(['name' => $data[7]])->one();
    //                             return $organization ? $organization->id : null;
    //                         },
    //                     ],
    //                     [
    //                         //สถานะ
    //                         'attribute' => 'asset_status',
    //                         'allowEmptyValues' => false,
    //                         'value' => function ($data) {
    //                             $data = explode(',', $data[0]);
    //                             $data = AppHelper::GetDataCsv($data);
    //                             $status_data = CategoriseHelper::Title($data[18])->one();
    //                             return $status_data ? $status_data->code : null;
    //                         },
    //                     ],
    //                     [
    //                         'attribute' => 'data_json',
    //                         'value' => function ($data) {
    //                             $data = explode(',', $data[0]);
    //                             $data = AppHelper::GetDataCsv($data);
    //                             $vendor_id = CategoriseHelper::Title($data[4])->one();
    //                             $purchase = CategoriseHelper::Title($data[10])->one();
    //                             $method_get = CategoriseHelper::Title($data[9])->one();
    //                             $budget_type = CategoriseHelper::Title($data[11])->one();
    //                             $jsonData = [
    //                                 //'vendor_id' => "test_vendor_id",
    //                                 //ผู้แทนจำหน่าย
    //                                 'vendor_id' => $data[4] == "ไม่ระบุ" ? 00 : ($vendor_id ? $vendor_id->code : null),
    //                                 'vendor' => $vendor_id,
    //                                 //เลขครุภัณฑ์เดิม
    //                                 'fsn_old' => $data[3],
    //                                 //'purchase' => "test_purchase_code",
    //                                 //การจัดซื้อ
    //                                 'purchase' => $purchase ? $purchase->code : null,
    //                                 'purchase_text' => $purchase ? $data[10] : null,
    //                                 //'method_get' => "test_method_get_code",
    //                                 //วิธีได้มา
    //                                 'method_get' => $method_get ? $method_get->code : null,
    //                                 'method_get_text' => $method_get ? $data[9] : null,
    //                                 //'budget_type' => "budget_type",
    //                                 //ประเภทเงิน
    //                                 'budget_type' => $budget_type ? $budget_type->code : null,
    //                                 'budget_type_text' => $budget_type ? $data[11] : null,
    //                                 //ปีงบประมาณ
    //                                 'on_year' => $data[8],
    //                                 //รายละเอียดยี่ห้อครุภัณฑ์
    //                                 'detail' => $data[13],
    //                                 //S/N
    //                                 'serial_number' => $data[14],
    //                                 //หน่วยนับ
    //                                 'unit' => $data[15],
    //                                 //วันหมดประกัน
    //                                 'expire_date' => $data[16],
    //                                 //สถานะ
    //                                 'status_name' => $data[17],
    //                                 //ชื่อครุภัณฑ์
    //                                 'asset_name' => $data[1],
    //                                 //ประเภท
    //                                 'asset_type' => $data[0],
    //                                 //หน่วยงานภายในตามโครงสร้างครับ
    //                                 'department_name' => $data[7],

    //                                 'asset_type_text' => $data[0],
    //                             ];

    //                             return Json::encode($jsonData);
    //                         },
    //                     ],

    //                 ],

    //             ]));
    //             unlink($filename);
    //             Yii::$app->session->setFlash('data', [
    //                 'status' => true,
    //                 'error' => $error,
    //             ]);
    //             return $this->redirect(['import-status']);
    //         } else {
    //             unlink($filename);
    //             Yii::$app->session->setFlash('data', [
    //                 'status' => false,
    //                 'error' => $error,
    //             ]);
    //             return $this->redirect(['import-status']);
    //         }

    //         // return var_dump($importer->getData());
    //     } else {
    //         return $this->render('import_csv',
    //             ['model' => $model,
    //                 'error' => $error,
    //                 'success' => false]);
    //     }
    // }

    public function actionImportStatus()
    {
        $data = Yii::$app->session->getFlash('data', []);
        $status = isset($data['status']) ? $data['status'] : false;
        $error = isset($data['error']) ? $data['error'] : [];
        return $this->render('import-status', [
            'status' => $status,
            'error' => $error,
        ]);
    }

    public function actionGetTableDprice()
    {
        return $this->render('extableDprice', [
            // ปี ราคา ค่าเสื่อม
            'data' => AppHelper::GetDepreciation(5, 10000, 40),
        ]);
    }
}
