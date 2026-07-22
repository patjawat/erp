<?php

namespace app\modules\hr\controllers;

use app\components\AppHelper;
use app\components\LineMsg;
use app\components\UserHelper;
use app\models\UploadCsvForm;
use app\modules\dms\models\DocumentsDetail;
use app\modules\hr\models\EmployeeDetailSearch;
use app\modules\hr\models\EmployeePosition;
use app\modules\hr\models\Employees;
use app\modules\hr\models\EmployeesSearch;
use app\modules\hr\models\Organization;
use app\modules\hr\models\UploadCsv;
use app\modules\hr\helpers\EmployeeImportHelper;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use ruskid\csvimporter\CSVImporter;
use ruskid\csvimporter\CSVReader;
use ruskid\csvimporter\MultipleImportStrategy;
use Yii;
use yii\filters\VerbFilter;
use yii\validators\DateValidator;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

/**
 * EmployeesController implements the CRUD actions for Employees model.
 */
class EmployeesController extends Controller
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
                        'cancel' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Employees models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new EmployeesSearch([
            'branch' => 'MAIN'
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $notStatusParam = $this->request->get('not-status');

        if (isset($searchModel->user_register) && $searchModel->user_register == 0) {
            $dataProvider->query->andWhere(['user_id' => 0]);
        }
        if (isset($searchModel->user_register) && $searchModel->user_register == 1) {
            $dataProvider->query->andWhere(['!=', 'user_id', 0]);
        }
        $q = trim($searchModel->q ?? '');
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'cid', $q],
            ['like', 'email', $q],
            ['like', 'fname', $q],
            ['like', 'lname', $q],
            ['like', 'phone', $q],
            ['like', 'cid', $q],
            ['like', new \yii\db\Expression("CONCAT(prefix,fname, ' ', lname)"), $q],
        ]);

        $dataProvider->query->andWhere(['NOT', ['id' => 1]]);

        // ค้นหาคามกลุ่มโครงสร้าง
        $org1 = Organization::findOne($searchModel->q_department);
        // ถ้ามีกลุ่มย่อย
        if (isset($org1) && $org1->lvl == 1) {
            $sql = 'SELECT t1.id, t1.root, t1.lft, t1.rgt, t1.lvl, t1.name, t1.icon
            FROM tree t1
            JOIN tree t2 ON t1.lft BETWEEN t2.lft AND t2.rgt AND t1.lvl = t2.lvl + 1
            WHERE t2.id = :id;';
            $querys = Yii::$app
                ->db
                ->createCommand($sql)
                ->bindValue(':id', $org1->id)
                ->queryAll();
            $arrDepartment = [(int) $org1->id];
            foreach ($querys as $tree) {
                $arrDepartment[] = (int) $tree['id'];
            }

            if (count($arrDepartment) > 0) {
                $dataProvider->query->andWhere(['in', 'department', $arrDepartment]);
            } else {
                $dataProvider->query->andFilterWhere(['department' => $searchModel->q_department]);
            }
        } else {
            $dataProvider->query->andFilterWhere(['department' => $searchModel->q_department]);
        }
        // จบการค้นหา

        if (!$searchModel->status && $searchModel->all_status == 0) {
            $dataProvider->query->andWhere(['status' => 1]);
        }

        if (!$searchModel->status && $searchModel->all_status == 0) {
            if ($notStatusParam) {
                $dataProvider->query->andWhere(['is', 'status', new \yii\db\Expression('null')]);
            } else {
                // $dataProvider->query->andWhere(['NOT', ['status' => [5, 7, 8]]]);
            }
        }

        $dataProvider->query->andWhere(new \yii\db\Expression("CONCAT(fname,' ', lname) LIKE :term", [':term' => '%' . $searchModel->fullname . '%']));

        // ค้นหาตามอายุ
        if ($searchModel->range1 && !$searchModel->range2) {
            $dataProvider->query->andWhere(new \yii\db\Expression('TIMESTAMPDIFF(YEAR, birthday, NOW()) = ' . $searchModel->range1));
        }
        // ค้นหาระหว่างช่วงอายุ
        if ($searchModel->range1 && $searchModel->range2) {
            $dataProvider->query->andWhere(new \yii\db\Expression('TIMESTAMPDIFF(YEAR, birthday, NOW()) BETWEEN ' . $searchModel->range1 . ' AND ' . $searchModel->range2));
        }

        $dataProvider->query->orderBy(['id' => SORT_DESC]);
        $dataProvider->pagination->pageSize = 16;



        $sql = 'SELECT count(id) as total  FROM `employees` WHERE `status` IS NULL';
        $notStatus = Yii::$app->db->createCommand($sql)->queryScalar();

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'notStatus' => $notStatus

        ]);
    }


    public function actionIndexV2()
    {
        $searchModel = new EmployeesSearch([
            'branch' => 'MAIN'
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $notStatusParam = $this->request->get('not-status');

        if (isset($searchModel->user_register) && $searchModel->user_register == 0) {
            $dataProvider->query->andWhere(['user_id' => 0]);
        }
        if (isset($searchModel->user_register) && $searchModel->user_register == 1) {
            $dataProvider->query->andWhere(['!=', 'user_id', 0]);
        }

        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'cid', $searchModel->q],
            ['like', 'email', $searchModel->q],
            ['like', 'fname', $searchModel->q],
            ['like', 'lname', $searchModel->q],
        ]);

        $dataProvider->query->andWhere(['NOT', ['id' => 1]]);

        // ค้นหาคามกลุ่มโครงสร้าง
        $org1 = Organization::findOne($searchModel->q_department);
        // ถ้ามีกลุ่มย่อย
        if (isset($org1) && $org1->lvl == 1) {
            $sql = 'SELECT t1.id, t1.root, t1.lft, t1.rgt, t1.lvl, t1.name, t1.icon
            FROM tree t1
            JOIN tree t2 ON t1.lft BETWEEN t2.lft AND t2.rgt AND t1.lvl = t2.lvl + 1
            WHERE t2.id = :id;';
            $querys = Yii::$app
                ->db
                ->createCommand($sql)
                ->bindValue(':id', $org1->id)
                ->queryAll();
            $arrDepartment = [(int) $org1->id];
            foreach ($querys as $tree) {
                $arrDepartment[] = (int) $tree['id'];
            }
            // Yii::$app->response->format = Response::FORMAT_JSON;
            // $dataDepartment =  ArrayHelper::merge($arrDepartment,$org1->lft);
            // $arrDepartment;
            // $arrDepartment[]  = $org1->lft;

            if (count($arrDepartment) > 0) {
                $dataProvider->query->andWhere(['in', 'department', $arrDepartment]);
            } else {
                $dataProvider->query->andFilterWhere(['department' => $searchModel->q_department]);
            }
        } else {
            $dataProvider->query->andFilterWhere(['department' => $searchModel->q_department]);
        }
        // จบการค้นหา

        if (!$searchModel->status && $searchModel->all_status == 0) {
            $dataProvider->query->andWhere(['status' => 1]);
        }

        if (!$searchModel->status && $searchModel->all_status == 0) {
            if ($notStatusParam) {
                $dataProvider->query->andWhere(['is', 'status', new \yii\db\Expression('null')]);
            } else {
                // $dataProvider->query->andWhere(['NOT', ['status' => [5, 7, 8]]]);
            }
        }

        $dataProvider->query->andWhere(new \yii\db\Expression("CONCAT(fname,' ', lname) LIKE :term", [':term' => '%' . $searchModel->fullname . '%']));

        // ค้นหาตามอายุ
        if ($searchModel->range1 && !$searchModel->range2) {
            $dataProvider->query->andWhere(new \yii\db\Expression('TIMESTAMPDIFF(YEAR, birthday, NOW()) = ' . $searchModel->range1));
        }
        // ค้นหาระหว่างช่วงอายุ
        if ($searchModel->range1 && $searchModel->range2) {
            $dataProvider->query->andWhere(new \yii\db\Expression('TIMESTAMPDIFF(YEAR, birthday, NOW()) BETWEEN ' . $searchModel->range1 . ' AND ' . $searchModel->range2));
        }

        $dataProvider->pagination->pageSize = 16;



        $sql = 'SELECT count(id) as total  FROM `employees` WHERE `status` IS NULL';
        $notStatus = Yii::$app->db->createCommand($sql)->queryScalar();

        return $this->render('index_v2', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'notStatus' => $notStatus

        ]);
    }


    public function actionSendMsg($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);
        $line_id = $model->user->line_id;
        $approveId = 11396;
        // LineMsg::sendLeave($approveId,$line_id);
        $docModel = DocumentsDetail::findOne(2);
        LineMsg::sendDocument($docModel, $line_id);


        // $result = Yii::$app->LineMsg->sendLeave($userId);
        // return $result;
    }
    /**
     * Displays a single Employees model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $name = $this->request->get('name');
        $model = $this->findModel($id);
         $me = UserHelper::GetEmployee();
         
        if($model->id != $me->id && !Yii::$app->user->can('hr')){
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        $searchModel = new EmployeeDetailSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->where(['emp_id' => $model->id, 'name' => $name]);
        $dataProvider->query->orderBy(
            new \yii\db\Expression("JSON_EXTRACT(data_json, '\$.date_start') desc")
        );
        $dataProvider->pagination->pageSize = 8;

        return $this->render('view', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'model' => $model,
            'name' => $name,
        ]);
    }


    // ตรวจสอบความถูกต้อง
    public function actionCreateValidator()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new Employees();

        if ($this->request->isPost && $model->load($this->request->post())) {
            $requiredName = 'ต้องระบุ';

            $model->validateIdCard();
            $model->email == '' ? $model->addError('email', $requiredName) : null;

            $cid = str_replace('-', '', $model->cid);
            $checkCid = Employees::find()->andWhere(['cid' => $cid])->andWhere(['<>', 'ref', $model->ref])->one();
            $checkCid  ? $model->addError('cid', 'เลขบัตรประชาขนซ้ำ ' . $checkCid->fullname) : null;

            $checkPhone = Employees::find()->andWhere(['phone' => $model->phone])->andWhere(['<>', 'ref', $model->ref])->one();
            $checkPhone  ? $model->addError('phone', 'ซ้ำกับ ' . $checkPhone->fullname) : null;



            foreach ($model->getErrors() as $attribute => $errors) {
                $result[\yii\helpers\Html::getInputId($model, $attribute)] = $errors;
            }
            if (!empty($result)) {
                return $this->asJson($result);
            }
        }
    }




    /**
     * Creates a new Employees model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new Employees([
            'user_id' => 0,
            'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
        ]);

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                // return $this->redirect(['view', 'id' => $model->id]);
                if ($model->save()) {
                    return [
                        'status' => 'success',
                        'container' => '#hr-container',
                    ];
                } else {
                    return false;
                }
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

    public function actionUploadBasicDoc($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);
        return [
            'title' => '<i class="fa-solid fa-file-circle-check"></i>  แบบสารสนเทศเบื้องต้น',
            'content' => $this->renderAjax('upload_basic_doc', ['model' => $model]),
        ];
    }

    /**
     * Updates an existing Employees model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model_old_data_json = $model->data_json;
        $positionAttrs = ['position_type', 'position_name', 'position_level', 'position_number', 'department', 'salary'];
        $oldPositionValues = [];
        foreach ($positionAttrs as $attr) {
            $oldPositionValues[$attr] = $model->$attr;
        }
        $model->join_date = AppHelper::DateFormDb($model->join_date);
        if ($this->request->isPost && $model->load($this->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($model->join_date) {
                $model->join_date = AppHelper::DateToDb($model->join_date);
            }
            // คืนค่าตำแหน่ง/ประเภทตำแหน่งที่ฟอร์มไม่ได้ส่งมา (ฟิลด์อยู่ใน d-none อาจส่งค่าว่าง)
            foreach ($positionAttrs as $attr) {
                if ($model->$attr === '' || $model->$attr === null) {
                    $model->$attr = $oldPositionValues[$attr];
                }
            }
            // ป้องกันข้อมูลใน JSON เดิมถูกลบ (data_json จาก DB เป็น string ต้อง decode เป็น array ก่อน)
            $oldData = $model_old_data_json;
            if (is_string($oldData)) {
                $oldData = json_decode($oldData, true) ?? [];
            }
            if (!is_array($oldData)) {
                $oldData = [];
            }
            $newData = array_filter($model->data_json ?? [], fn($v) => $v !== null && $v !== '');
            $model->data_json = array_replace_recursive($oldData, $newData);

            if ($model->save()) {
                return [
                    'status' => 'success',
                    'container' => ''
                ];
            }

            // บันทึกไม่สำเร็จ (เช่น validation) ส่ง errors กลับเพื่อให้แสดงในฟอร์ม
            $errors = $model->getErrors();
            $msg = 'กรุณาตรวจสอบข้อมูลในฟอร์ม';
            if (!empty($errors)) {
                $lines = [];
                foreach ($errors as $attr => $messages) {
                    $label = $model->getAttributeLabel($attr);
                    $lines[] = $label . ': ' . implode(', ', $messages);
                }
                $msg .= "\n" . implode("\n", $lines);
            }
            return [
                'status' => 'error',
                'message' => $msg,
                'errors' => $errors,
            ];
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข',
                'content' => $this->renderAjax('update', [
                    'model' => $model,
                ]),
            ];
        } else {
            $model->cid = AppHelper::cidFormat($model->cid);
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Employees model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    //เพิ่มสถานะยกเลิกให้กับบุคลากร
   public function actionCancel($id)
{
    Yii::$app->response->format = Response::FORMAT_JSON;
    
    if ($this->request->isPost) {
        $model = $this->findModel($id);

        // ตรวจสอบว่าถ้าหา model ไม่เจอจะให้ทำอย่างไร (findModel ปกติจะ throw exception อยู่แล้ว)
        
        // Logic การสลับค่า (Toggle)
        if ($model->status === 'CANCEL') {
            $model->status = '0';
        } else {
            $model->status = 'CANCEL';
        }

        if ($model->save(false)) { // ใช้ false เพื่อข้ามการ validate ถ้ามั่นใจในค่าที่ส่ง
            return [
                'status' => 'success',
                'new_status' => $model->status // ส่งค่าใหม่กลับไปเช็คด้วยก็ดีครับ
            ];
        }
    }

    return [
        'status' => 'error',
        'message' => 'Invalid request'
    ];
}

    public function actionGetPositionName()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $typeId = null;
        $groupId = null;
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            $typeId = $parents[0] ?? null;
            $groupId = $parents[1] ?? null;
        }

        $out = [];
        foreach (EmployeePosition::listItems($typeId, $groupId) as $id => $name) {
            $out[] = [
                'id' => $id,
                'name' => $name,
            ];
        }

        return ['output' => $out, 'selected' => ''];
    }

    /**
     * Finds the Employees model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Employees the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Employees::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionEducationCreate($id)
    {
        $model = $this->findModel($id);
        // $education = $model->data_json['education'];

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                // return $model->data_json['education'];
                $stack = ['orange', 'banana'];
                array_push($model->education, 'apple', 'raspberry');
                return $stack;
                // return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => 'การศึกษา',
                'content' => $this->renderAjax('_form_education', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('_form_education', [
                'model' => $model,
            ]);
        }
    }

    /**
     * สร้างไฟล์ CSV template นำเข้าบุคลากร (หัวตารางตาม schema)
     * import จะ validate ทุกช่องกับข้อมูลจริงใน DB อยู่แล้ว จึงไม่ต้องมี dropdown ในไฟล์
     */
    public function actionImportTemplate()
    {
        $schema = EmployeeImportHelper::schema();
        $headers = array_map(fn($col) => $col['header'], $schema);

        $dir = Yii::getAlias('@runtime/export');
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $filePath = $dir . '/template-employee-import.csv';

        $handle = fopen($filePath, 'w');
        // BOM ให้ Excel เปิดภาษาไทยไม่เพี้ยน
        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, $headers);
        fclose($handle);

        return Yii::$app->response->sendFile($filePath, 'template-นำเข้าบุคลากร.csv');
    }

    public function actionImportCsv()
    {

        if ($this->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $filePath = Yii::$app->request->post('filePath');

            if (!$filePath || !file_exists($filePath)) {
                return ['status' => 'error', 'message' => 'ไม่พบไฟล์'];
            }

            $rows = $this->readRowsFromFile($filePath);
            if ($rows === null) {
                return ['status' => 'error', 'message' => 'อ่านไฟล์ไม่สำเร็จ (รองรับ .xlsx หรือ .csv)'];
            }

            // เฟส 1: validate + parse ทั้งไฟล์
            $parseResult = EmployeeImportHelper::parseRows($rows);
            if ($parseResult['fatal'] !== null) {
                return ['status' => 'error', 'message' => $parseResult['fatal']];
            }

            // เฟส 2: บันทึกเฉพาะแถวที่ผ่าน validate
            $apply = EmployeeImportHelper::applyRows($parseResult['parsed']);

            $rowErrors = $parseResult['errors'];
            $failures = $apply['failures'];
            $ok = empty($rowErrors) && empty($failures);

            $message = "นำเข้าสำเร็จ — เพิ่มใหม่ {$apply['inserted']} คน, อัปเดต {$apply['updated']} คน";
            if (!empty($rowErrors)) {
                $message .= " · ข้าม " . count($rowErrors) . " แถวที่ข้อมูลไม่ถูกต้อง";
            }
            if (!empty($failures)) {
                $message .= " · บันทึกไม่สำเร็จ " . count($failures) . " แถว";
            }

            return [
                'status' => $ok ? 'success' : 'warning',
                'message' => $message,
                'summary' => [
                    'inserted' => $apply['inserted'],
                    'updated' => $apply['updated'],
                    'skipped' => count($rowErrors),
                    'failed' => count($failures),
                ],
                'rowErrors' => $rowErrors,
                'failures' => $failures,
            ];
        }

        $model = new Employees;
        if ($this->request->isAjax) {

            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => 'การศึกษา',
                'content' => $this->renderAjax('_form_import_employee', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('_form_import_employee', [
                'model' => $model,
            ]);
        }
    }

    /**
     * อ่านไฟล์นำเข้า (.xlsx หรือ .csv) → array ของแถว (แถวแรก = หัวตาราง)
     * คืน null ถ้าอ่านไม่ได้
     */
    protected function readRowsFromFile($filePath)
    {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if ($ext === 'csv' || $ext === 'txt') {
            $rows = [];
            if (($handle = fopen($filePath, 'r')) !== false) {
                while (($data = fgetcsv($handle, 0, ',')) !== false) {
                    $rows[] = $data;
                }
                fclose($handle);
                return $rows;
            }
            return null;
        }

        // xlsx / xls
        try {
            $reader = IOFactory::createReaderForFile($filePath);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($filePath);
            return $spreadsheet->getActiveSheet()->toArray(null, true, false, false);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function actionImportPreview()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = new UploadCsvForm();
        $model->csvFile = UploadedFile::getInstanceByName('csvFile');

        if ($model && $model->validate()) {
            // บันทึกไฟล์ชั่วคราว
            $filePath = Yii::getAlias('@runtime') . '/import_' . time() . '.' . $model->csvFile->extension;
            $model->csvFile->saveAs($filePath);

            // อ่านทั้ง .xlsx และ .csv
            $previewData = $this->readRowsFromFile($filePath);
            if ($previewData === null) {
                return ['status' => 'error', 'message' => 'อ่านไฟล์ไม่สำเร็จ (รองรับ .xlsx หรือ .csv)'];
            }

            return [
                'status' => 'success',
                'preview' => $previewData,
                'filePath' => $filePath,
            ];
        }

        return [
            'status' => 'error',
            'errors' => $model->getErrors(),
        ];
    }


    public function actionImportCsvOld()
    {
        $model = new UploadCsv();
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
                'tableName' => Employees::tableName(),
                'fgetcsvOptions' => [
                    'delimiter' => ';',
                ],
            ]));
            for ($x = 1; $x <= count($importer->getData()); $x++) {
                $data_check_error = $importer->getData()[$x][0];
                $data_check_error = explode(',', $data_check_error);
                if (!empty($data_check_error[0])) {
                    if (count($data_check_error) != 10) {
                        array_push($error, 'ข้อมูลไม่ครบถ้วน');
                    }
                    if (null != Employees::findOne(['cid' => $data_check_error[0]])) {
                        array_push($error, '[ ' . $x . ' ]' . ' CID ' . $data_check_error[0] . ' อยู่ในระบบแล้ว');
                    }
                    if (strlen($data_check_error[0]) != 13) {
                        array_push($error, '[ ' . $x . ' ]' . ' CID ' . $data_check_error[0] . ' ไม่ถูกต้อง (ต้องมี 13 หลัก)');
                    }
                    $validator = new DateValidator();
                    $validator->format = 'yyyy-MM-dd';
                    if ($validator->validate($data_check_error[5])) {
                    } else {
                        array_push($error, '[ ' . $x . ' ]' . ' วันเกิด ' . $data_check_error[5] . ' ไม่ถูกต้อง (1980-01-01)');
                    }
                    // if (strlen($data_check_error[6]) == 9 and $data_check_error[6][0] != 0) {
                    // } elseif (strlen($data_check_error[6]) != 10) {
                    //     array_push($error, '[ ' . $x . ' ]' . ' เบอร์โทรศัพท์ ' . $data_check_error[6] . ' ไม่ถูกต้อง (ต้องมี 10 หลัก)');
                    // }
                }
            }
            if (empty($error)) {
                $numberRowsAffected = $importer->import(new MultipleImportStrategy([
                    'tableName' => Employees::tableName(),  // change your model names accordingly
                    'configs' => [
                        [
                            'attribute' => 'user_id',
                            'value' => function ($data) {
                                return 0;
                            },
                        ],
                        [
                            'attribute' => 'cid',
                            'allowEmptyValues' => false,
                            'value' => function ($data) {

                                return $data[0];
                            },
                        ],
                        [
                            'attribute' => 'gender',
                            'value' => function ($data) {

                                return $data[1];
                            },
                        ],
                        [
                            'attribute' => 'prefix',
                            'value' => function ($data) {

                                return $data[2];
                            },
                        ],
                        [
                            'attribute' => 'fname',
                            'value' => function ($data) {

                                return $data[3];
                            },
                        ],
                        [
                            'attribute' => 'lname',
                            'value' => function ($data) {

                                return $data[4];
                            },
                        ],
                        [
                            'attribute' => 'birthday',
                            'value' => function ($data) {

                                return $data[5];
                            },
                        ],
                        [
                            'attribute' => 'phone',
                            'value' => function ($data) {

                                return $data[6];
                            },
                        ],
                        [
                            'attribute' => 'email',
                            'value' => function ($data) {

                                return $data[7];
                            },
                        ],
                        [
                            'attribute' => 'address',
                            'value' => function ($data) {

                                return $data[8];
                            },
                        ],
                        [
                            'attribute' => 'zipcode',
                            'value' => function ($data) {
                                try {

                                    return (int) $data[9];
                                } catch (\Throwable $th) {
                                    return 0;
                                }
                            },
                        ],
                        [
                            'attribute' => 'position_name',
                            'value' => function ($data) {
                                return 0;
                            },
                        ],
                        [
                            'attribute' => 'status',
                            'value' => function ($data) {
                                return 0;
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
            return $this->render(
                'import_csv',
                [
                    'model' => $model,
                    'error' => $error,
                    'success' => false
                ]
            );
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

    public function actionExportExcel()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $searchModel = new EmployeesSearch([
            'branch' => 'MAIN'
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $notStatusParam = $this->request->get('not-status');

        if (isset($searchModel->user_register) && $searchModel->user_register == 0) {
            $dataProvider->query->andWhere(['user_id' => 0]);
        }
        if (isset($searchModel->user_register) && $searchModel->user_register == 1) {
            $dataProvider->query->andWhere(['!=', 'user_id', 0]);
        }

        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'cid', $searchModel->q],
            ['like', 'email', $searchModel->q],
            ['like', 'fname', $searchModel->q],
            ['like', 'lname', $searchModel->q],
        ]);

        $dataProvider->query->andWhere(['NOT', ['id' => 1]]);

        // ค้นหาคามกลุ่มโครงสร้าง
        $org1 = Organization::findOne($searchModel->q_department);
        // ถ้ามีกลุ่มย่อย
        if (isset($org1) && $org1->lvl == 1) {
            $sql = 'SELECT t1.id, t1.root, t1.lft, t1.rgt, t1.lvl, t1.name, t1.icon
            FROM tree t1
            JOIN tree t2 ON t1.lft BETWEEN t2.lft AND t2.rgt AND t1.lvl = t2.lvl + 1
            WHERE t2.id = :id;';
            $querys = Yii::$app
                ->db
                ->createCommand($sql)
                ->bindValue(':id', $org1->id)
                ->queryAll();
            $arrDepartment = [(int) $org1->id];
            foreach ($querys as $tree) {
                $arrDepartment[] = (int) $tree['id'];
            }
            // Yii::$app->response->format = Response::FORMAT_JSON;
            // $dataDepartment =  ArrayHelper::merge($arrDepartment,$org1->lft);
            // $arrDepartment;
            // $arrDepartment[]  = $org1->lft;

            if (count($arrDepartment) > 0) {
                $dataProvider->query->andWhere(['in', 'department', $arrDepartment]);
            } else {
                $dataProvider->query->andFilterWhere(['department' => $searchModel->q_department]);
            }
        } else {
            $dataProvider->query->andFilterWhere(['department' => $searchModel->q_department]);
        }
        // จบการค้นหา

        if (!$searchModel->status && $searchModel->all_status == 0) {
            $dataProvider->query->andWhere(['status' => 1]);
        }

        if (!$searchModel->status && $searchModel->all_status == 0) {
            if ($notStatusParam) {
                $dataProvider->query->andWhere(['is', 'status', new \yii\db\Expression('null')]);
            } else {
                // $dataProvider->query->andWhere(['NOT', ['status' => [5, 7, 8]]]);
            }
        }

        $dataProvider->query->andWhere(new \yii\db\Expression("CONCAT(fname,' ', lname) LIKE :term", [':term' => '%' . $searchModel->fullname . '%']));

        // ค้นหาตามอายุ
        if ($searchModel->range1 && !$searchModel->range2) {
            $dataProvider->query->andWhere(new \yii\db\Expression('TIMESTAMPDIFF(YEAR, birthday, NOW()) = ' . $searchModel->range1));
        }
        // ค้นหาระหว่างช่วงอายุ
        if ($searchModel->range1 && $searchModel->range2) {
            $dataProvider->query->andWhere(new \yii\db\Expression('TIMESTAMPDIFF(YEAR, birthday, NOW()) BETWEEN ' . $searchModel->range1 . ' AND ' . $searchModel->range2));
        }

        $dataProvider->pagination->pageSize = 30000;



        $sql = 'SELECT count(id) as total  FROM `employees` WHERE `status` IS NULL';
        $notStatus = Yii::$app->db->createCommand($sql)->queryScalar();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(18);
        $sheet->getColumnDimension('C')->setWidth(8);
        $sheet->getColumnDimension('D')->setWidth(9);
        $sheet->getColumnDimension('E')->setWidth(13);
        $sheet->getColumnDimension('F')->setWidth(13);
        $sheet->getColumnDimension('G')->setWidth(13);
        $sheet->getColumnDimension('H')->setWidth(8);
        $sheet->getColumnDimension('I')->setWidth(25);
        $sheet->getColumnDimension('J')->setWidth(50);
        $sheet->getColumnDimension('K')->setWidth(13);
        $sheet->getColumnDimension('L')->setWidth(13);
        $sheet->getColumnDimension('M')->setWidth(18);
        $sheet->getColumnDimension('N')->setWidth(13);
        $sheet->getColumnDimension('O')->setWidth(13);
        $sheet->getColumnDimension('P')->setWidth(15);
        $sheet->getColumnDimension('Q')->setWidth(13);
        $sheet->getColumnDimension('R')->setWidth(25);
        $sheet->getColumnDimension('S')->setWidth(13);
        $sheet->getColumnDimension('T')->setWidth(20);
        $sheet->getColumnDimension('U')->setWidth(15);
        $sheet->getColumnDimension('V')->setWidth(25);
        $sheet->getColumnDimension('W')->setWidth(18);
        $sheet->getColumnDimension('X')->setWidth(17);
        $sheet->getColumnDimension('Y')->setWidth(18);
        $sheet->getColumnDimension('Z')->setWidth(18);
        $sheet->getColumnDimension('AA')->setWidth(30);
        $sheet->getColumnDimension('AB')->setWidth(15);
        $sheet->getColumnDimension('AC')->setWidth(30);
        $sheet->getColumnDimension('AD')->setWidth(15);
        $sheet->getColumnDimension('AE')->setWidth(10);

        $setHeader = 'A1:AE1';
        $sheet->getStyle($setHeader)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($setHeader)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($setHeader)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($setHeader)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet->getStyle($setHeader)->getFill()->getStartColor()->setRGB('8DB4E2');
        $sheet->getStyle('A1:AE1')->getFont()->setBold(true)->setItalic(false);
        // ตั้งชื่อแผ่นงาน
        $sheet->setTitle('ข้อมูลบุคลากร');
        $sheet->setCellValue('A1', 'ลำดับ');
        $sheet->setCellValue('B1', 'เลขบัตรประชาชน');
        $sheet->setCellValue('C1', 'เพศ');
        $sheet->setCellValue('D1', 'คำนำหน้า');
        $sheet->setCellValue('E1', 'ชื่อ');
        $sheet->setCellValue('F1', 'นามสกุล');
        $sheet->setCellValue('G1', 'วันเกิด');
        $sheet->setCellValue('H1', 'อายุ');
        $sheet->setCellValue('I1', 'Email');
        $sheet->setCellValue('J1', 'ที่อยู่');
        $sheet->setCellValue('K1', 'รหัสไปรษณีย์');
        $sheet->setCellValue('L1', 'วันที่เริ่มงาน');
        $sheet->setCellValue('M1', 'อายุราชการ');
        $sheet->setCellValue('N1', 'เกษียณ');
        $sheet->setCellValue('O1', 'คงเหลือ/ปี');
        $sheet->setCellValue('P1', 'หมายเลขโทรศัพท์');
        $sheet->setCellValue('Q1', 'สถานะ');
        $sheet->setCellValue('R1', 'ตำแหน่งปัจจุบัน');
        $sheet->setCellValue('S1', 'วันที่แต่งตั้ง');
        $sheet->setCellValue('T1', 'รายการเคลื่อนไหว');
        $sheet->setCellValue('U1', 'เลขประจำตำแหน่ง');
        $sheet->setCellValue('V1', 'ประเภท');
        $sheet->setCellValue('W1', 'ระดับตำแหน่ง');
        $sheet->setCellValue('X1', 'ความเชี่ยวชาญ');
        $sheet->setCellValue('Y1', 'ประเภท/กลุ่มงาน');
        $sheet->setCellValue('Z1', 'เงินเดือน');
        $sheet->setCellValue('AA1', 'กลุ่มงาน');
        $sheet->setCellValue('AB1', 'หัวหน้ากลุ่มงาน');
        $sheet->setCellValue('AC1', 'หน่วยงาน');
        $sheet->setCellValue('AD1', 'หัวหน้าหน่วยงาน');
        $sheet->setCellValue('AE1', 'ลายเซ็น');
        $StartRowSheet = 2;
        // $dataItems = $this->findModelItem($params);
        foreach ($dataProvider->getModels() as $key => $value) {
            $numRow = $StartRowSheet++;
            // $a[] = ['B' => 'B'.$StartRow++];
            $sheet->setCellValue('A' . $numRow, $numRow);
            $sheet->setCellValue('B' . $numRow, $value->cid);
            $sheet->getStyle('B' . $numRow)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER);
            $sheet->setCellValue('C' . $numRow, $value->gender);
            $sheet->getStyle('C' . $numRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->setCellValue('D' . $numRow, $value->prefix);
            $sheet->setCellValue('E' . $numRow, $value->fname);
            $sheet->setCellValue('F' . $numRow, $value->lname);
            try {
                $sheet->setCellValue('G' . $numRow, Yii::$app->thaiFormatter->asDate(AppHelper::DateToDb($value->birthday), 'php:d/m/Y'));
            } catch (\Throwable $th) {
                $sheet->setCellValue('G' . $numRow, AppHelper::DateToDb($value->birthday));
            }
            $sheet->setCellValue('H' . $numRow, $value->age_y);
            $sheet->setCellValue('I' . $numRow, $value->email);
            $sheet->setCellValue('J' . $numRow, $value->address);
            $sheet->setCellValue('K' . $numRow, $value->zipcode);
            try {
                $sheet->setCellValue('L' . $numRow, Yii::$app->thaiFormatter->asDate($value->joinDate(), 'php:d/m/Y'));
            } catch (\Throwable $th) {
                $sheet->setCellValue('L' . $numRow, $value->joinDate());
            }
            $sheet->setCellValue('M' . $numRow, $value->workLife()['full']);
            try {
                $sheet->setCellValue('N' . $numRow, Yii::$app->thaiFormatter->asDate($value->year60(), 'php:d/m/Y'));
            } catch (\Throwable $th) {
                $sheet->setCellValue('N' . $numRow, $value->year60());
            }
            $sheet->setCellValue('O' . $numRow, $value->leftYear60());
            $sheet->setCellValue('P' . $numRow, $value->phone);
            $sheet->setCellValue('Q' . $numRow, $value->statusName());
            $sheet->setCellValue('R' . $numRow, $value->positionName(['icon' => false]));
            // ประวัติการดำรงตำแหน่งล่าสุด: ตั้งแต่วันที่ / รายการเคลื่อนไหว / เลขประจำตำแหน่ง
            $latestPosition = $value->latestPositionData();
            try {
                $sheet->setCellValue('S' . $numRow, $latestPosition['date_start'] ? Yii::$app->thaiFormatter->asDate($latestPosition['date_start'], 'php:d/m/Y') : '');
            } catch (\Throwable $th) {
                $sheet->setCellValue('S' . $numRow, $latestPosition['date_start']);
            }
            $sheet->setCellValue('T' . $numRow, $latestPosition['movement']);
            $sheet->setCellValue('U' . $numRow, $latestPosition['position_number']);
            $sheet->setCellValue('V' . $numRow, $value->positionTypeName());
            $sheet->setCellValue('W' . $numRow, $value->positionLevelName());
            $sheet->setCellValue('X' . $numRow, $value->expertiseName());
            $sheet->setCellValue('Y' . $numRow, $value->positionGroupName());
            $sheet->setCellValue('Z' . $numRow, $value->salary ? number_format($value->salary, 2) : '');
            $orgUnits = $value->orgUnits();
            $sheet->setCellValue('AA' . $numRow, $orgUnits['group'] ? $orgUnits['group']->name : '');
            $sheet->setCellValue('AB' . $numRow, $value->isOrgLeader($orgUnits['group']) ? 'Y' : '');
            $sheet->setCellValue('AC' . $numRow, $orgUnits['unit'] ? $orgUnits['unit']->name : '');
            $sheet->setCellValue('AD' . $numRow, $value->isOrgLeader($orgUnits['unit']) ? 'Y' : '');
            $sheet->setCellValue('AE' . $numRow, $value->signature() ? 'มี' : 'ไม่มี');
        }

        // จัดตำแหน่งข้อมูล (แถวข้อมูลเท่านั้น หัวตารางยังกึ่งกลางเหมือนเดิม)
        $lastRow = $StartRowSheet - 1;
        if ($lastRow >= 2) {
            // กึ่งกลาง: ลำดับ, เลขบัตร ปชช., เพศ, คำนำหน้า, วันเกิด, อายุ, รหัสไปรษณีย์, วันที่เริ่มงาน,
            //          คงเหลือ/ปี, หมายเลขโทรศัพท์, ตั้งแต่วันที่, เลขประจำตำแหน่ง, หัวหน้ากลุ่มงาน, หัวหน้าหน่วยงาน, ลายเซ็น
            $centerCols = ['A', 'B', 'C', 'D', 'G', 'H', 'K', 'L', 'O', 'P', 'S', 'U', 'AB', 'AD', 'AE'];
            foreach ($centerCols as $col) {
                $sheet->getStyle($col . '2:' . $col . $lastRow)
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }
            // ชิดขวา: เงินเดือน
            $sheet->getStyle('Z2:Z' . $lastRow)
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }

        $writer = new Xlsx($spreadsheet);
        $filePath = Yii::getAlias('@webroot') . '/downloads/บุคลากร.xlsx';
        $writer->save($filePath);  // สร้าง excel

        // if (file_exists($output_file)) {  // ตรวจสอบว่ามีไฟล์ หรือมีการสร้างไฟล์ แล้วหรือไม่
        //     echo Html::a('ดาวน์โหลดเอกสาร', Url::to(Yii::getAlias('@web') . '/myData.xlsx'), ['class' => 'btn btn-info', 'target' => '_blank']);  // สร้าง link download
        // }

        if (file_exists($filePath)) {
            return Yii::$app->response->sendFile($filePath, 'ข้อมูลบุคลากร.xlsx');
        } else {
            throw new \yii\web\NotFoundHttpException('The file does not exist.');
        }
    }
}
