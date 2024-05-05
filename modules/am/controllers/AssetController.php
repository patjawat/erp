<?php

namespace app\modules\am\controllers;

use yii;
use app\modules\am\models\Asset;
use app\modules\am\models\AssetSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use app\modules\hr\models\UploadCsv;
use app\components\AppHelper;
use yii\web\UploadedFile;
use ruskid\csvimporter\CSVImporter;
use ruskid\csvimporter\CSVReader;
use ruskid\csvimporter\MultipleImportStrategy;
use app\components\CategoriseHelper;
use yii\helpers\Json;
use yii\db\Expression;
use chillerlan\QRCode\QRCode;
use yii\helpers\ArrayHelper;
use app\components\SiteHelper;
use app\modules\hr\models\Organization;
use app\modules\sm\models\Vendor;
use app\modules\sm\models\AseetItem;
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
        $assetType = $this->request->get('asset_group');
        $group = $this->request->get('group');
        $searchModel = new AssetSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->leftJoin('categorise at', 'at.code=asset.asset_item');
        $dataProvider->query->andFilterWhere(['like', new Expression("JSON_EXTRACT(asset.data_json, '$.budget_type')"), $searchModel->budget_type]);
        $dataProvider->query->andFilterWhere(['like', new Expression("JSON_EXTRACT(asset.data_json, '$.method_get')"), $searchModel->method_get]);
        // $dataProvider->query->andFilterWhere(['receive_date' => $searchModel->q_date]);
        $dataProvider->query->andFilterWhere(['receive_date' => AppHelper::DateToDb($searchModel->q_receive_date)]);
        // $dataProvider->query->andFilterWhere(['data_json' => $searchModel->asset_type]);

        // ค้นหาคามกลุ่มโครงสร้าง
        $org1 = Organization::findOne($searchModel->q_department);
        //ถ้ามรกลุ่มย่อย
        if(isset($searchModel->q_department) && isset($org1) && $org1->lvl == 1){
            $sql = "SELECT t1.id, t1.root, t1.lft, t1.rgt, t1.lvl, t1.name, t1.icon
            FROM tree t1
            JOIN tree t2 ON t1.lft BETWEEN t2.lft AND t2.rgt AND t1.lvl = t2.lvl + 1
            WHERE t2.name = :name;";
            $querys = Yii::$app->db->createCommand($sql)
            ->bindValue(':name', $org1->name)
            ->queryAll();
            $arrDepartment = [];
            foreach ($querys as $tree) {
                $arrDepartment[] = $tree['id'];
            }
            if(count($arrDepartment) > 0){
                $dataProvider->query->andWhere(['in', 'department', $arrDepartment]);
            }

        }else{
            $dataProvider->query->andFilterWhere(['department' => $searchModel->q_department]);
        }
        // จบการค้นหา
        
        $dataProvider->query->andFilterWhere(['at.category_id' => $searchModel->asset_type]);
        $dataProvider->query->andFilterWhere([
            'or',
            ['LIKE', 'asset.code', $searchModel->q],
            ['LIKE', new Expression("JSON_EXTRACT(asset.data_json, '$.asset_name')"), $searchModel->q],
        ]);

        //ค้นหาตามอายุ
        if($searchModel->price1 && !$searchModel->price2){
            $dataProvider->query->andWhere(new \yii\db\Expression('price = '.$searchModel->price1));
        }
         //ค้นหาระหว่างช่วงอายุ
        if($searchModel->price1 && $searchModel->price2){
            $dataProvider->query->andWhere(new \yii\db\Expression('price BETWEEN '.$searchModel->price1.' AND '.$searchModel->price2));
        }
        
        $dataProvider->setSort([
            'defaultOrder' => [
                'receive_date' => SORT_DESC,
                // 'service_start_time' => SORT_DESC
            ]
        ]);


        if ($this->request->get('view')) {
            SiteHelper::setDisplay($this->request->get('view'));
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
         $requiredName = "ต้องระบุ"; 
         if ($this->request->isPost && $model->load($this->request->post()))
          { 
            $fsnAuto = $this->request->post('Asset');
             // ตรวจระหัสซ้ำ
             $checkCode = Asset::find()->where(['code' => $model->code])
             ->andWhere(['<>','ref',$model->ref])
             ->andWhere(['not', ['code' => null]])
             ->andWhere(['not', ['code' => ""]])
             ->one();
            //  return $checkCode;

         if ($checkCode) {
                 $codeStatus = true;
         } else {
             $codeStatus = false;
         }

        //  return $model;
         //ตรวจสอลการลงปีงบประมาณ
        // return $model;


            if($model->asset_group != 1 && $model->asset_group != 2){ // ถ้าเป็นที่ดินไม่ต้องตรวจสอบปีงบประมาณ
                $model->data_json['budget_type'] == "" ? $model->addError('data_json[budget_type]',$requiredName) : null;
                $model->on_year == "" ? $model->addError('on_year',$requiredName) : null;
                $model->purchase == "" ? $model->addError('purchase',$requiredName) : null;
                $model->data_json['method_get'] == "" ? $model->addError('data_json[method_get]',$requiredName) : null;
                $model->data_json['vendor_id'] == "" ? $model->addError('data_json[vendor_id]',$requiredName) : null;
        
                $codeStatus ? $model->addError('code','หมายเลขครุภัณฑ์ซ้ำ') : null;

                // ถ้าสร้างรหัสอัตโนมัติ
                if(!isset($fsnAuto['fsn_auto']) || $fsnAuto['fsn_auto'] == "1"){
                }
                // ถ้ากำหนดรหัวเอง
                if(isset($fsnAuto['fsn_auto']) && $fsnAuto['fsn_auto'] == "0"){
                    $model->code == "" ? $model->addError('code', $requiredName) : null;
                }

            }

        //     if($model->data_json['budget_year']){
        //         $strYear = $model->data_json['budget_year'];
        //        //  แยกอักขะออก     
        //        $strYearResult = preg_replace('/[^A-Za-z0-9ก-ฮ\-]/', '', $strYear);
        //        //ต้องไม่น้อยกว่า 4 หลัก
        //        if(strlen($strYearResult) < 4){
        //            $model->addError('data_json[budget_year]','ปี พ.ศ. ต้องไม่น้อยกว่า 4 หลัก');
        //        }else{
        //            // ระบุให้ไม่เกินจริง
        //            $year = ($strYearResult - 543);
        //            if($year > date('Y')){
        //                $model->addError('data_json[budget_year]','ปี พ.ศ.ต้องไม่เกิน '.(date('Y')+543));
        //            }
        //        }
   
        //    }

            
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
        $dataProvider->query->andWhere(['in', 'asset.code', $model->device_items !=NULL ? $model->device_items : '']);
        
        return $this->render('view', [
            'model' => $model,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionDepreciation($id)
    {
        $model = $this->findModel($id);
        $asset_name = isset($model->data_json['asset_name']) ? 'ค่าเสื่อมราคา'.$model->data_json['asset_name'] : '-';
        $title = $this->request->get('title').isset($model->data_json['asset_name']) ? $model->data_json['asset_name'] : '-';
        if($this->request->isAjax)
        {

        
        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'title' =>  '<i class="fa-solid fa-chart-line"></i> '.$asset_name,
            'content' => $this->renderAjax('depreciation_list', [
            'model' => $model,
        ])
            ];
        }else{
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
    {   $group = $this->request->get('group');
        $model = new Asset([
            'asset_group' => $group,
            'asset_item' => 0,
            'asset_status' => 0,
            'price' => 0,
            'ref' => substr(Yii::$app->getSecurity()->generateRandomString(),10)
        ]);

        if ($this->request->isPost) {
            
            if ($model->load($this->request->post())) {
                $model->receive_date = AppHelper::DateToDb($model->receive_date);
                Yii::$app->response->format = Response::FORMAT_JSON;
                if($model->save())
                {
                    return $this->redirect(['view', 'id' => $model->id]);
                }else{
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
        if(isset($model->data_json["item_options"])){
            $model->item_options = $model->data_json["item_options"];
        }
        $model_old_data_json = $model->data_json;

        if ($this->request->isPost && $model->load($this->request->post()) ) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            if($model->save()){
                return $this->redirect(['view', 'id' => $model->id]);
            }else{
                Yii::$app->response->format = Response::FORMAT_JSON;
                return $model->getErrors();
            }
        }
        
        return $this->render('update', [
            'model' => $model,
            // 'group' => $model->asset_group 
        ]);
    }
    
//update Spect ที่เป็น Cmputer
    public function actionUpdateComputer($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $title = $this->request->get('title');
        $model = $this->findModel($id);

        $old_data_json = $model->data_json;

        if ($this->request->isPost && $model->load($this->request->post()) ) {
            $model->data_json = ArrayHelper::merge($old_data_json, $model->data_json);
            // return $model->data_json;
            if($model->save()){
                return $this->redirect(['view', 'id' => $model->id]);
            }else{
                Yii::$app->response->format = Response::FORMAT_JSON;
                return $model->getErrors();
            }
        }
        return [
            'title' => $title,
            'content' => $this->renderAjax('is_computer/_form_computer',['model' => $model])
        ];
    }


    public function actionQrcode(){
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = $this->request->get('id');
        $model = $this->findModel($id);
        $data = 'https://programmerthailand.com';
        $qr = new QRCode();

        // echo '<img src="'.$qr->render($data).'" />';
        return [
            'title' => '<i class="fa-solid fa-qrcode"></i> QR-Code',
            'content' => $this->renderAjax('qr_code',['model' => $model])
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
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
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
        return $name.$data;

    }
    

    //เลือกการบันทึกทรัพย์สิน
    public function actionSelectType()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

       return [
        'title' => 'เลือกกลุ่ม',
        'content' => $this->renderAjax('select_type')
       ];
       

    }
    


    public function actionImportCsv()
    {
        $current_data;
        $model = new UploadCsv([
            'name' => 'vendor',
            'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
        ]);
        $basePath = Yii::getAlias('@app/web/import-csv/');
        AppHelper::CreateDir($basePath);

        $error = [];
        if ($model->load(Yii::$app->request->post())) {
            $model->file = UploadedFile::getInstance($model, 'file');
            $model->file->saveAs($basePath. $model->file->name);

            $importer = new CSVImporter();
            $filename = $basePath . $model->file->name;
            $importer->setData(new CSVReader([
                'filename' => $filename,
                'tableName' => Asset::tableName(),
                'fgetcsvOptions' => [
                    'delimiter' => ';',
                ],
            ]));
/*         Yii::$app->response->format = Response::FORMAT_JSON;
        $list = explode(',', $importer->getData()[3][0]);
        $list[10] = preg_replace('/[^0-9.]/', '', explode(',', $importer->getData()[3][0])[10] . explode(',', $importer->getData()[3][0])[11] . explode(',', $importer->getData()[3][0])[12]);
        unset($list[11]);
        unset($list[12]);
        return explode(',', implode(",",$list)); */
        for ($x = 1; $x <= count($importer->getData()); $x++) {
                $data_check_error = $importer->getData()[$x][0];
                $data_check_error = AppHelper::GetDataCsv(explode(',', $data_check_error));
                
                if (CategoriseHelper::TitleAndName($data_check_error[10],"purchase")->one() == null){
                    $codeP =CategoriseHelper::CodePurchase();
                    $model_assetItemx = new Vendor([
                        "code" => (string) $codeP,
                        "name" => "purchase",
                        "title" => $data_check_error[10]
                    ]);
                    $model_assetItemx->save();
                }
                if (CategoriseHelper::TitleAndName($data_check_error[1],"asset_item")->one() == null){
                    
                    $model_assetItem = new Vendor([
                        "code" => substr($data_check_error[2], 0, 13),
                        "name" => "asset_item",
                        "title" => $data_check_error[1]
                    ]);
                    $model_assetItem->save();
                }
                if ($data_check_error[4] != "ไม่ระบุ"){
                    if (CategoriseHelper::TitleAndName($data_check_error[4],"vendor")->one() == null){

                        $model_Vendor = new Vendor([
                            "code" => $data_check_error[5],
                            "name" => "vendor",
                            "title" => $data_check_error[4]
                        ]);
                        $model_Vendor->save();
                    }
                }
/*                 if (count($data_check_error) != 17) {
                    array_push($error, 'ข้อมูลไม่ครบถ้วน');
                }  */
/*                 if (null != Vendor::findOne(['code' => $data_check_error[0]])) {
                    array_push($error, 'มีเลขประจำตัวผู้เสียภาษี ' . $data_check_error[0] . ' อยู่ในระบบแล้ว');
                }  */
/*                  if (empty(CategoriseHelper::Title($data_check_error[1])->one())) {
                    array_push($error, 'ไม่พบผู้แทนจำหน่าย ' . $data_check_error[1]);
                } */
/*                 if (!is_numeric($data_check_error[10])) {
                    array_push($error, 'ช่อง ' . $data_check_error[10] . ' ต้องใส่ข้อมูลเป็นตัวเลข');
                }
                if (!is_numeric($data_check_error[11])) {
                    array_push($error, 'ช่อง ' . $data_check_error[11] . ' ต้องใส่ข้อมูลเป็นตัวเลข');
                } */
            }  
/*             Yii::$app->response->format = Response::FORMAT_JSON;
            $list = explode(',', $importer->getData()[1][0]);
            return AppHelper::GetDataCsv($list); */
            if (empty($error)) {
                $numberRowsAffected = $importer->import(new MultipleImportStrategy([
                    'tableName' => Asset::tableName(), // change your model names accordingly
                    'configs' => [
                        [
                            'attribute' => 'ref',
                            'value' => function ($data) {
                                return substr(Yii::$app->getSecurity()->generateRandomString(), 10);
                            },
                        ],
                        [
                            //ประเภทครุภัณฑ์
                            'attribute' => 'asset_group',
                            'value' => function ($data) {
                                return 3;
                            },
                        ],
                        [
                            //หมายเลขครุภัณฑ์
                            'attribute' => 'asset_item',
                            'allowEmptyValues' => false,
                            'value' => function ($data) {
                                $data = explode(',', $data[0]);
                                $data = AppHelper::GetDataCsv($data);
                                return substr($data[2], 0, 13);
                            },
                        ],
                        [
                            //หมายเลขครุภัณฑ์
                            'attribute' => 'code',
                            'allowEmptyValues' => false,
                            'value' => function ($data) {
                                $data = explode(',', $data[0]);
                                $data = AppHelper::GetDataCsv($data);
                                return $data[2];
                            },
                        ],
                         [
                            'attribute' => 'purchase',
                            'allowEmptyValues' => false,
                            'value' => function ($data) {
                                $data = explode(',', $data[0]);
                                $data = AppHelper::GetDataCsv($data);
                                if ($data[10] == "เฉพาะเจาะจง"){
                                    return 1;
                                }elseif ($data[10] == "บริจาค"){
                                    return 2;
                                }elseif ($data[10] == "ตกลงราคา"){
                                    return 3;
                                }
                                return ;
                            }
                        ], 
                        [
                            //วันที่รับเข้า
                            'attribute' => 'receive_date',
                            'allowEmptyValues' => false,
                            'value' => function ($data) {
                                $data = explode(',', $data[0]);
                                $data = AppHelper::GetDataCsv($data);
                                return date('Y-m-d', strtotime($data[7]));
                            },
                        ],
                        [
                            //วันที่รับเข้า
                            'attribute' => 'on_year',
                            'allowEmptyValues' => false,
                            'value' => function ($data) {
                                $data = explode(',', $data[0]);
                                $data = AppHelper::GetDataCsv($data);
                                return $data[8];
                            },
                        ],
                        [
                            //ราคา
                            'attribute' => 'price',
                            'allowEmptyValues' => false,
                            'value' => function ($data) {
                                $data = explode(',', $data[0]);
                                $data = AppHelper::GetDataCsv($data);
                                return $data[12] == "" ? 0 : $data[12];
                            },
                        ],
                        [
                            //หน่วยงานภายในตามโครงสร้าง
                            'attribute' => 'department',
                            'allowEmptyValues' => false,
                            'value' => function ($data) {
                                $data = explode(',', $data[0]);
                                $data = AppHelper::GetDataCsv($data);
                                $organization = Organization::find()->where(['name' => $data[7]])->one();
                                return $organization ? $organization->id : null;
                            },
                        ], 
                        [
                            //สถานะ
                            'attribute' => 'asset_status',
                            'allowEmptyValues' => false,
                            'value' => function ($data) {
                                $data = explode(',', $data[0]);
                                $data = AppHelper::GetDataCsv($data);
                                $status_data = CategoriseHelper::Title($data[18])->one();
                                return $status_data ? $status_data->code : null;
                            },
                        ],
                        [
                            'attribute' => 'data_json',
                            'value' => function ($data) {
                                $data = explode(',', $data[0]);
                                $data = AppHelper::GetDataCsv($data);
                                $vendor_id = CategoriseHelper::Title($data[4])->one();
                                $purchase = CategoriseHelper::Title($data[10])->one();
                                $method_get = CategoriseHelper::Title($data[9])->one();
                                $budget_type = CategoriseHelper::Title($data[11])->one();
                                $jsonData = [
                                    //'vendor_id' => "test_vendor_id",
                                    //ผู้แทนจำหน่าย
                                    'vendor_id' => $data[4] == "ไม่ระบุ" ? 00 : ($vendor_id ? $vendor_id->code : null),
                                    'vendor' => $vendor_id,
                                    //เลขครุภัณฑ์เดิม
                                    'fsn_old' => $data[3],
                                    //'purchase' => "test_purchase_code",
                                    //การจัดซื้อ
                                    'purchase' => $purchase ? $purchase->code : null,
                                    'purchase_text' => $purchase ? $data[10] : null,
                                    //'method_get' => "test_method_get_code",
                                    //วิธีได้มา
                                    'method_get' => $method_get ? $method_get->code : null,
                                    'method_get_text' =>  $method_get ? $data[9] : null,
                                    //'budget_type' => "budget_type",
                                    //ประเภทเงิน
                                    'budget_type' => $budget_type ? $budget_type->code : null,
                                    'budget_type_text' => $budget_type ? $data[11] : null,
                                    //ปีงบประมาณ
                                    'on_year' => $data[8],
                                    //รายละเอียดยี่ห้อครุภัณฑ์
                                    'detail'=> $data[14],
                                    //S/N
                                    'serial_number' => $data[15],
                                    //หน่วยนับ
                                    'unit' => $data[16],
                                    //วันหมดประกัน
                                    'expire_date' => $data[17],
                                    //สถานะ
                                    'status_name' => $data[18],
                                    //ชื่อครุภัณฑ์
                                    'asset_name' => $data[1],
                                    //ประเภท
                                    'asset_type' => $data[0],
                                    //หน่วยงานภายในตามโครงสร้างครับ
                                    'department_name' => $data[7],

                                    'asset_type_text' => $data[0],
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
        return $this->render('import-status',[
            'status' => $status,
            'error' => $error
        ]);
    }

    public function actionGetTableDprice(){
        return $this->render("extableDprice",[
                                                //ปี ราคา ค่าเสื่อม
            "data"=> AppHelper::GetDepreciation(5,10000,40)
        ]);
    }
}
