<?php

namespace app\modules\helpdesk2\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\Response;
use yii\db\Expression;
use yii\data\ActiveDataProvider;
use app\models\Categorise;
use app\components\AppHelper;
use app\components\SiteHelper;
use app\components\UserHelper;
use app\modules\am\models\Asset;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use app\modules\helpdesk2\models\Helpdesk;
use app\modules\helpdesk2\models\HelpdeskSearch;
use app\modules\helpdesk2\models\HelpdeskDetail;
use app\modules\filemanager\components\FileManagerHelper;
use app\modules\pdfTemplate\models\PdfTemplate;
use app\modules\pdfTemplate\services\PdfTemplateService;
use app\modules\hr\models\Employees;



class ServiceController extends \yii\web\Controller
{
    /**
     * NEW version of repair request form.
     * Route: /helpdesk/service/create-v2
     */
    public function actionCreateV2()
    {
        $me = UserHelper::GetEmployee();
        $model = new Helpdesk([
            'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
            'emp_id' => $me->id,
            'asset_number' => $this->request->get('asset_number'),
            'name' => 'repair',
        ]);

        if ($this->request->isPost && $model->load($this->request->post())) {
            try {
                $model->request_repair_date = AppHelper::convertToGregorian($model->request_repair_date);
            } catch (\Throwable $th) {
            }

            $saveMode = (string) $this->request->post('save_mode', 'submit');
            $dataJson = $model->data_json;
            if (!is_array($dataJson)) {
                $dataJson = [];
            }
            if ($saveMode === 'draft') {
                $dataJson['draft'] = 1;
            } else {
                unset($dataJson['draft']);
            }
            $model->data_json = $dataJson;

            // default status
            $model->status = $model->status ?: 'pending';

            // generate repair number (only if not already set)
            if (empty($model->repair_number)) {
                switch ((string) $model->repair_group) {
                    case '1':
                        $depCode = 'GEN';
                        break;
                    case '2':
                        $depCode = 'IT';
                        break;
                    case '3':
                        $depCode = 'MED';
                        break;
                    default:
                        $depCode = '';
                        break;
                }
                $model->repair_number = $model->HelpdeskGenNumber($depCode);
            }

            if ($model->save()) {
                return $this->redirect(['view-v2', 'id' => $model->id]);
            }
        }

        return $this->render('create-v2', [
            'model' => $model,
        ]);
    }

    /**
     * Ajax validator for create-v2 form.
     */
    public function actionCreateV2Validator()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new Helpdesk();

        if ($this->request->isPost && $model->load($this->request->post())) {
            $model->title == '' ? $model->addError('title', 'ต้องระบุหัวข้อ/อาการ...') : null;
            ($model->data_json['urgency'] ?? '') === '' ? $model->addError('data_json[urgency]', 'ต้องระบุความเร่งด่วน...') : null;
            ($model->data_json['location'] ?? '') === '' ? $model->addError('data_json[location]', 'ต้องระบุสถานที่...') : null;
            $model->repair_group == '' ? $model->addError('repair_group', 'ต้องระบุแผนกช่าง...') : null;

            $result = [];
            foreach ($model->getErrors() as $attribute => $errors) {
                $result[\yii\helpers\Html::getInputId($model, $attribute)] = $errors;
            }

            if (!empty($result)) {
                return $this->asJson($result);
            }
        }

        return $this->asJson([]);
    }

    /**
     * NEW technician workflow (do not modify existing pages).
     * Route: /helpdesk/service/update-v2?id=XX
     */
    public function actionUpdateV2($id)
    {
        if (Yii::$app->user->isGuest) {
            throw new ForbiddenHttpException('Permission denied.');
        }

        $me = UserHelper::GetEmployee();
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post())) {
            // Keep system-compatible main status, store technician result in data_json.
            $dataJson = $model->data_json;
            if (!is_array($dataJson)) {
                $dataJson = [];
            }

            // แปลงเวลาเริ่ม/เสร็จจาก ว/ด/พ.ศ. เวลา เป็น Y-m-d H:i ก่อนบันทึก
            foreach (['start_at', 'finish_at'] as $key) {
                $v = $dataJson[$key] ?? '';
                if (is_string($v) && preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})\s+(\d{1,2}):(\d{2})/', $v, $m)) {
                    $dataJson[$key] = sprintf('%04d-%02d-%02d %02d:%02d', (int) $m[3] - 543, (int) $m[2], (int) $m[1], (int) $m[4], (int) $m[5]);
                }
            }

            $techStatus = (string) ($dataJson['tech_status_v2'] ?? '');
            if ($techStatus === 'completed') {
                $model->status = 'success';
            } elseif ($techStatus === 'cannot_repair') {
                $model->status = 'cancel';
            } else {
                $model->status = 'in_progress';
            }
            $model->data_json = $dataJson;

            if ($model->save()) {
                // Write technician log to timeline (service_record)
                $logTitle = (string) ($model->data_json['work_performed_v2'] ?? '');
                $logTitle = $logTitle !== '' ? $logTitle : 'อัปเดตการซ่อม (v2)';

                $log = new HelpdeskDetail();
                $log->helpdesk_id = $model->id;
                $log->name = 'service_record';
                $log->emp_id = $me->id ?? null;
                $log->status = 'อัปเดตงานซ่อม';
                $log->title = $logTitle;
                $log->data_json = [
                    'tech_status_v2' => $techStatus,
                    'date_start' => $model->date_start,
                    'date_end' => $model->date_end,
                ];
                $log->save(false);

                return $this->redirect(['view-v2', 'id' => $model->id]);
            }
        }

        return $this->render('update-v2', [
            'model' => $model,
            'me' => $me,
        ]);
    }

    /**
     * Technician worklist (V2).
     * Route: /helpdesk/service/technician-v2
     */
    public function actionTechnicianV2()
    {
        if (Yii::$app->user->isGuest) {
            throw new ForbiddenHttpException('Forbidden');
        }

        $searchModel = new HelpdeskSearch();
        $searchModel->load($this->request->get());

        $query = Helpdesk::find()
            ->where(['name' => 'repair'])
            ->andWhere(['status' => ['pending', 'receive', 'in_progress']]);

        if (trim((string) ($searchModel->q ?? '')) !== '') {
            $q = trim($searchModel->q);
            $query->andWhere([
                'or',
                ['like', 'repair_number', $q],
                ['like', 'title', $q],
                ['like', new Expression("JSON_UNQUOTE(JSON_EXTRACT(helpdesk.data_json, '$.location'))"), $q],
            ]);
        }
        if (trim((string) ($searchModel->status ?? '')) !== '') {
            $query->andWhere(['status' => $searchModel->status]);
        }
        if (trim((string) ($searchModel->urgency ?? '')) !== '') {
            $query->andFilterWhere(['=', new Expression("JSON_EXTRACT(helpdesk.data_json, '$.urgency')"), $searchModel->urgency]);
        }
        if (trim((string) ($searchModel->device_type_id ?? '')) !== '') {
            $query->andWhere(['device_type_id' => $searchModel->device_type_id]);
        }

        $query->orderBy(['updated_at' => SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
                'params' => $this->request->queryParams,
                'pageParam' => 'page',
            ],
            'sort' => false,
        ]);

        return $this->render('technician-v2', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);
        if ($this->request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('view', [
                    'model' => $model,
                ])
            ];
        } else {
            return $this->render('view', [
                'model' => $model,
            ]);
        }
    }

     public function actionViewV2($id)
    {
        $model = $this->findModel($id);
        if ($this->request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('view-v2', [
                    'model' => $model,
                ])
            ];
        } else {
            return $this->render('view-v2', [
                'model' => $model,
            ]);
        }
    }

    public function actionUpdate($id)
    {
        $me = UserHelper::GetEmployee();
        $model = $this->findModel($id);
        $model->request_repair_date = AppHelper::convertToThai($model->request_repair_date);
        if ($this->request->isPost) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            if ($model->load($this->request->post())) {
                try {
                    $model->request_repair_date = AppHelper::convertToGregorian($model->request_repair_date);
                } catch (\Throwable $th) {
                }

                if ($model->save()) {

                    return [
                        'status' => 'success'
                    ];
                }
            }
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('_form', [
                    'model' => $model,
                ])
            ];
        } else {
            return $this->render('_form', [
                'model' => $model,
            ]);
        }
    }

    public function UpdateAssetStatus($model)
    {
        if ($model->asset_number !== '' && $model->status == 'success') {
            $asset = Asset::findOne(['code' => $model->asset_number]);
            if ($asset) {
                $asset->asset_status = 1;
                return $asset->save(false);
            }
        }
        return false;
    }



    public function actionUpdateStatus($id)
    {
        $me = UserHelper::GetEmployee();
        $model = $this->findModel($id);
        if ($this->request->isPost) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            if ($model->load($this->request->post())) {

                if ($model->save()) {
                    $this->UpdateAssetStatus($model);
                    return [
                        'status' => 'success'
                    ];
                }
            }
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('_form_update_status', [
                    'model' => $model,
                ])
            ];
        } else {
            return $this->render('_form_update_status', [
                'model' => $model,
            ]);
        }
    }

    public function actionReceive($id)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $me = UserHelper::GetEmployee();
        //บันทึกเปลี่ยนสถานะและออกเลขใขรับซ่อม
        $model = $this->findModel($id);
        $model->status = 'receive';
        //ออกระหัสรับงานซ่อม
        $model->receive_date = date('Y-m-d H:i:s');
        $model->save();

        //เขียนลงบน timeline
        $serviceRecord = new HelpdeskDetail;
        $serviceRecord->emp_id = $me->id;
        $serviceRecord->helpdesk_id = $model->id;
        $serviceRecord->name = 'service_record';
        $serviceRecord->status = 'รับเรื่อง';
        $serviceRecord->title = 'รับเรื่องเรียบร้อยแล้วรอให้ช่างดำเนินการตรวจเช็ค';
        $serviceRecord->save();
        return ['status' => 'success'];
    }


    public function actionTechnician()
    {
        return $this->render('technician/index');
    }


    public function actionCancel($id)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);
        $model->status = 'Cancel';
        $model->save();
        return ['status' => 'success'];
    }
    public function actionDelete($id)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);
        $model->delete();
        return ['status' => 'success'];
    }

    public function actionFormLayoutServiceSetting()
    {
        $check = Categorise::findOne(['name' => 'form_layout_service']);

        $model = $check ? $check : new Categorise;
        $model->name = 'form_layout_service';
        if ($this->request->isPost && $model->load($this->request->post())) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            $model->save();
            return [
                'status' => 'success'
            ];
        }

        return $this->render('_form_layout_service_setting', ['model' => $model]);
    }

    private function t($text)
    {
        return iconv('UTF-8', 'cp874', $text ?? '');
    }

    //แสดงตัวอย่างของการตั้งค่า
    public function actionPreviewSetting()
    {
        $formName = 'form_layout_service';
        $ref = substr(Yii::$app->getSecurity()->generateRandomString(), 10);
        $checkLayout = Categorise::findOne(['name' => $formName]);

        if (!$checkLayout) {
            $layout = new Categorise();
            $layout->name = $formName;
            $layout->ref = $ref;
            $layout->data_json = [
                "title_x" => "75",
                "title_y" => "63",
                "device_x" => "90",
                "device_y" => "51",
                "created_x" => "145",
                "created_y" => "39",
                "urgency_x" => "170",
                "urgency_y" => "75",
                "location_x" => "67",
                "location_y" => "69",
                "createdby_x" => "130",
                "createdby_y" => "87",
                "createtime_x" => "181",
                "createtime_y" => "27",
                "department_x" => "85",
                "department_y" => "45",
                "tech_receive_x" => "140",
                "tech_receive_y" => "140",
                "repair_number_x" => "165",
                "repair_number_y" => "13",
            ];
            $layout->save();
        } else {
            $layout = $checkLayout;
        }

        $pdf = new \setasign\Fpdi\Fpdi();
        $pdf->AddPage();

        // 1️⃣ กำหนด PDF Template ก่อน
        $templateFile = FileManagerHelper::getFileFormRef($layout->ref);
        if ($templateFile) {

            $pdf->setSourceFile($templateFile); // ต้องเรียกก่อน importPage()
            // สร้างออบเจกต์ PDF และโหลดไฟล์ต้นฉบับ
            $pdf->AddFont('THSarabunNew', '', 'THSarabunNew.php');
            $pdf->AddFont('THSarabunNew', 'B', 'THSarabunNew Bold.php');

            // 2️⃣ เลือกหน้าที่ต้องการ
            $tplIdx = $pdf->importPage(1);

            // 3️⃣ ใช้ template
            $pdf->useTemplate($tplIdx, 0, 0, 210);

            // 4️⃣ เขียนข้อความลงไป
            $pdf->SetFont('THSarabunNew', 'B', 13); // ใช้ขนาดฟอนต์ 13 pt



            // ส่วนราชการ
            $companyName = $this->GetInfo()['company_name'];
            $companyNameX = isset($layout->data_json['company_name_x']) ? (float)$layout->data_json['company_name_x'] : 0;
            $companyNameY = isset($layout->data_json['company_name_y']) ? (float)$layout->data_json['company_name_y'] : 0;
            $pdf->SetXY($companyNameX, $companyNameY);
            $pdf->Write(10,  $this->t($companyName));

            //แผนกงานซ่อม
            $tecDep = 'ซ่อมบำรุง';
            $tecDepNumberX = isset($layout->data_json['tecdev_number_x']) ? (float)$layout->data_json['tecdev_number_x'] : 0;
            $tecDepNumberY = isset($layout->data_json['tecdev_number_y']) ? (float)$layout->data_json['tecdev_number_y'] : 0;
            $pdf->SetXY($tecDepNumberX, $tecDepNumberY);
            $pdf->Write(10,  $this->t($tecDep));

            // เลขที่ใบแจ้งซ่อม
            $repairNumber = 'REP-56810-GEN-0001';
            $repairNumberX = isset($layout->data_json['repair_number_x']) ? (float)$layout->data_json['repair_number_x'] : 0;
            $repairNumberY = isset($layout->data_json['repair_number_y']) ? (float)$layout->data_json['repair_number_y'] : 0;
            $pdf->SetXY($repairNumberX, $repairNumberY);
            $pdf->Write(10,  $this->t($repairNumber));

            // รายละเอียดปัญหา
            $title = 'เปิดเครื่องไม่ติด';
            $titleX = isset($layout->data_json['title_x']) ? (float)$layout->data_json['title_x'] : 0;
            $titleY = isset($layout->data_json['title_y']) ? (float)$layout->data_json['title_y'] : 0;
            $pdf->SetXY($titleX, $titleY);
            $pdf->Write(10,  $this->t($title));

            //สถานที่
            $location = 'ห้อง IPD1';
            $locationX = isset($layout->data_json['location_x']) ? (float)$layout->data_json['location_x'] : 0;
            $locationY = isset($layout->data_json['location_y']) ? (float)$layout->data_json['location_y'] : 0;
            $pdf->SetXY($locationX, $locationY);
            $pdf->Write(10,  $this->t($location));

            //ฝ่ายงานที่ส่งซ่อม
            $department = 'กลุ่มการพยาบาล';
            $ldepartmentX = isset($layout->data_json['department_x']) ? (float)$layout->data_json['department_x'] : 0;
            $departmentY = isset($layout->data_json['department_y']) ? (float)$layout->data_json['department_y'] : 0;
            $pdf->SetXY($ldepartmentX, $departmentY);
            $pdf->Write(10,  $this->t($department));


            // ประเภทอุปกรณ์
            $deviceType = 'ระบบไฟฟ้า';
            $deviceX = isset($layout->data_json['device_x']) ? (float)$layout->data_json['device_x'] : 0;
            $deviceY = isset($layout->data_json['device_y']) ? (float)$layout->data_json['device_y'] : 0;
            $pdf->SetXY($deviceX, $deviceY);
            $pdf->Write(10,  $this->t($deviceType));

            // ผู้ส่งซ่อม
            $createdBy = 'นายสมชาย ใจดี';
            $createdByX = isset($layout->data_json['createdby_x']) ? (float)$layout->data_json['createdby_x'] : 0;
            $createdByY = isset($layout->data_json['createdby_y']) ? (float)$layout->data_json['createdby_y'] : 0;
            $pdf->SetXY($createdByX, $createdByY);
            $pdf->Write(10,  $this->t($createdBy));

            // วันที่ส่งซ่อม
            $createDate = '1 ตถลาคม 2569 11:00 น.';
            $createDateX = isset($layout->data_json['created_x']) ? (float)$layout->data_json['created_x'] : 0;
            $createDateY = isset($layout->data_json['created_y']) ? (float)$layout->data_json['created_y'] : 0;
            $pdf->SetXY($createDateX, $createDateY);
            $pdf->Write(10,  $this->t($createDate));


            // ช่างผู้รับงาน
            $techReceive = 'นายโชคดี มีชัย';
            $techReceiveX = isset($layout->data_json['tech_receive_x']) ? (float)$layout->data_json['tech_receive_x'] : 0;
            $techReceiveY = isset($layout->data_json['tech_receive_y']) ? (float)$layout->data_json['tech_receive_y'] : 0;
            $pdf->SetXY($techReceiveX, $techReceiveY);
            $pdf->Write(10,  $this->t($techReceive));

            $urgencyX = isset($layout->data_json['urgency_x']) ? (float)$layout->data_json['urgency_x'] : 0;
            $urgencyY = isset($layout->data_json['urgency_y']) ? (float)$layout->data_json['urgency_y'] : 0;
            $pdf->SetXY($urgencyX, $urgencyY);
            $pdf->Write(10,  $this->t('ด่วน'));

            // $noteX = isset($layout->data_json['note_x']) ? (float)$layout->data_json['note_x'] : 0;
            // $noteY = isset($layout->data_json['note_y']) ? (float)$layout->data_json['note_y'] : 0;
            // $pdf->SetXY($noteX, $noteY);
            // $pdf->Write(10,  $this->t('ตัวอย่างระบุหมายเหตุในการแจ้งซ่อมเพิ่มเติม'));


            // 5️⃣ Output PDF
            $pdf->Output('I', 'filled.pdf');
        }
    }




    public function actionPrint($id)
    {

        $model = $this->findModel($id);
        $formName = 'form_layout_service';
        $ref = substr(Yii::$app->getSecurity()->generateRandomString(), 10);
        $checkLayout = Categorise::findOne(['name' => $formName]);

        if (!$checkLayout) {
            $layout = new Categorise();
            $layout->name = $formName;
            $layout->ref = $ref;
            $layout->data_json = [
                "title_x" => "75",
                "title_y" => "63",
                "device_x" => "90",
                "device_y" => "51",
                "created_x" => "145",
                "created_y" => "39",
                "urgency_x" => "170",
                "urgency_y" => "75",
                "location_x" => "67",
                "location_y" => "69",
                "createdby_x" => "130",
                "createdby_y" => "87",
                "createtime_x" => "181",
                "createtime_y" => "27",
                "department_x" => "85",
                "department_y" => "45",
                "tech_receive_x" => "140",
                "tech_receive_y" => "140",
                "repair_number_x" => "165",
                "repair_number_y" => "13",
            ];
            $layout->save();
        } else {
            $layout = $checkLayout;
        }





        $pdf = new \setasign\Fpdi\Fpdi();
        $pdf->AddPage();
        // 1️⃣ กำหนด PDF Template ก่อน

        $templateFile = FileManagerHelper::getFileFormRef($layout->ref);
        if ($templateFile) {


            $pdf->setSourceFile($templateFile); // ต้องเรียกก่อน importPage()
            // สร้างออบเจกต์ PDF และโหลดไฟล์ต้นฉบับ
            $pdf->AddFont('THSarabunNew', '', 'THSarabunNew.php');
            $pdf->AddFont('THSarabunNew', 'B', 'THSarabunNew Bold.php');

            // 2️⃣ เลือกหน้าที่ต้องการ
            $tplIdx = $pdf->importPage(1);

            // 3️⃣ ใช้ template
            $pdf->useTemplate($tplIdx, 0, 0, 210);

            // 4️⃣ เขียนข้อความลงไป
            $pdf->SetFont('THSarabunNew', 'B', 13); // ใช้ขนาดฟอนต์ 13 pt


            // ส่วนราชการ
            $companyName = $this->GetInfo()['company_name'];
            $companyNameX = isset($layout->data_json['company_name_x']) ? (float)$layout->data_json['company_name_x'] : 0;
            $companyNameY = isset($layout->data_json['company_name_y']) ? (float)$layout->data_json['company_name_y'] : 0;
            $pdf->SetXY($companyNameX, $companyNameY);
            $pdf->Write(10,  $this->t($companyName));

            //แผนกงานซ่อม
            switch ($model->repair_group) {
                case 1:
                    $tecDep = 'ศูนย์ซ่อมบำรุง';
                    break;
                case 2:
                    $tecDep = 'ศูนย์คอมพิวเตอร์';
                    break;
                case 2:
                    $tecDep = 'ศูนย์เครื่องมือแพทย์';
                    break;
                default:
                    $tecDep = '';
                    break;
            }
            $tecDepNumberX = isset($layout->data_json['tecdev_number_x']) ? (float)$layout->data_json['tecdev_number_x'] : 0;
            $tecDepNumberY = isset($layout->data_json['tecdev_number_y']) ? (float)$layout->data_json['tecdev_number_y'] : 0;
            $pdf->SetXY($tecDepNumberX, $tecDepNumberY);
            $pdf->Write(10,  $this->t($tecDep));


            // เลขที่ใบแจ้งซ่อม
            $repairNumber = $model->repair_number;
            $repairNumberX = isset($layout->data_json['repair_number_x']) ? (float)$layout->data_json['repair_number_x'] : 0;
            $repairNumberY = isset($layout->data_json['repair_number_y']) ? (float)$layout->data_json['repair_number_y'] : 0;
            $pdf->SetXY($repairNumberX, $repairNumberY);
            $pdf->Write(10,  $this->t($repairNumber));

            // รายละเอียดปัญหา
            $title = $model->title.(isset($model->data_json['note']) ? ' (**'.$model->data_json['note'].')' : '');
            $titleX = isset($layout->data_json['title_x']) ? (float)$layout->data_json['title_x'] : 0;
            $titleY = isset($layout->data_json['title_y']) ? (float)$layout->data_json['title_y'] : 0;
            $pdf->SetXY($titleX, $titleY);
            
            $pdf->Write(10,  $this->t($title));

            //สถานที่
            $location = isset($model->data_json['location']) ? $model->data_json['location'] : '';
            $locationX = isset($layout->data_json['location_x']) ? (float)$layout->data_json['location_x'] : 0;
            $locationY = isset($layout->data_json['location_y']) ? (float)$layout->data_json['location_y'] : 0;
            $pdf->SetXY($locationX, $locationY);
            $pdf->Write(10,  $this->t($location));

            //ฝ่ายงานที่ส่งซ่อม
            $department = $model->emp->departmentName() ?? '-';
            $ldepartmentX = isset($layout->data_json['department_x']) ? (float)$layout->data_json['department_x'] : 0;
            $departmentY = isset($layout->data_json['department_y']) ? (float)$layout->data_json['department_y'] : 0;
            $pdf->SetXY($ldepartmentX, $departmentY);
            $pdf->Write(10,  $this->t($department));

            // ประเภทอุปกรณ์
            $deviceType = $model->deviceType->title ?? '-';
            $deviceX = isset($layout->data_json['device_x']) ? (float)$layout->data_json['device_x'] : 0;
            $deviceY = isset($layout->data_json['device_y']) ? (float)$layout->data_json['device_y'] : 0;
            $pdf->SetXY($deviceX, $deviceY);
            $pdf->Write(10,  $this->t($deviceType));

            // ผู้ส่งซ่อม
            $createdBy = $model->emp->fullname ?? '-';
            $createdByX = isset($layout->data_json['createdby_x']) ? (float)$layout->data_json['createdby_x'] : 0;
            $createdByY = isset($layout->data_json['createdby_y']) ? (float)$layout->data_json['createdby_y'] : 0;
            $pdf->SetXY($createdByX, $createdByY);
            $pdf->Write(10,  $this->t($createdBy));

            // วันที่ส่งซ่อม
            $createDate = $model->viewCreated()['full'];
            $createDateX = isset($layout->data_json['created_x']) ? (float)$layout->data_json['created_x'] : 0;
            $createDateY = isset($layout->data_json['created_y']) ? (float)$layout->data_json['created_y'] : 0;
            $pdf->SetXY($createDateX, $createDateY);
            $pdf->Write(10,  $this->t($createDate));

            // ช่างผู้รับงาน
            $techReceive = $model->viewTechRevice()->fullname ?? '-';
            $techReceiveX = isset($layout->data_json['tech_receive_x']) ? (float)$layout->data_json['tech_receive_x'] : 0;
            $techReceiveY = isset($layout->data_json['tech_receive_y']) ? (float)$layout->data_json['tech_receive_y'] : 0;
            $pdf->SetXY($techReceiveX, $techReceiveY);
            $pdf->Write(10,  $this->t($techReceive));

            $urgencyX = isset($layout->data_json['urgency_x']) ? (float)$layout->data_json['urgency_x'] : 0;
            $urgencyY = isset($layout->data_json['urgency_y']) ? (float)$layout->data_json['urgency_y'] : 0;
            $pdf->SetXY($urgencyX, $urgencyY);
            $pdf->Write(10,  $this->t($model->viewUrgent()['title']));
            // หมายเหตุ
            // $pdf->SetXY(59, 40);
            // $pdf->MultiCell(100, 8, $this->t($model->data_json['note']));

            // 5️⃣ Output PDF
            $pdf->Output('I', 'filled.pdf');
        }
    }

    /**
     * พิมพ์ใบส่งซ่อม (PDF) ด้วยระบบ pdf-template (ตั้งค่าได้ที่ /pdf-template/template)
     * Context: helpdesk2.repair.notice
     */
    public function actionPrintSendRepairPdf($id)
    {
        $model = $this->findModel($id);

        $template = PdfTemplate::find()->where(['use_for_context' => PdfTemplate::CONTEXT_HELPDESK2_REPAIR_NOTICE])->one();
        if (!$template) {
            throw new NotFoundHttpException('ยังไม่ได้ตั้งค่าเทมเพลตใบส่งซ่อม กรุณาเลือกที่ /pdf-template/template');
        }

        $senderEmp = Employees::find()->where(['user_id' => $model->created_by])->one();

        $noticeDate = null;
        if (!empty($model->created_at)) {
            $noticeDate = substr((string) $model->created_at, 0, 10);
        }

        $sendRepairDate = null;
        if (is_array($model->data_json) && !empty($model->data_json['send_repair_date'])) {
            $sendRepairDate = (string) $model->data_json['send_repair_date'];
        }
        if (!$sendRepairDate && !empty($model->created_at)) {
            $sendRepairDate = substr((string) $model->created_at, 0, 10);
        }

        $data = [
            'repair_number' => (string) ($model->repair_number ?? ''),
            'title' => (string) ($model->title ?? ''),
            'device_type_name' => (string) ($model->deviceType?->title ?? ''),
            'asset_number' => (string) ($model->asset_number ?? ''),
            'notice_date' => (string) ($noticeDate ?? ''),
            'request_repair_date' => (string) ($model->request_repair_date ?? ''),
            'receive_date' => (string) ($model->receive_date ?? ''),
            'send_repair_date' => (string) ($sendRepairDate ?? ''),
            'repair_result' => (string) ($model->repair_result ?? ''),
            'repair_type' => (string) ($model->repair_type ?? ''),
            'status_title' => (string) ($model->repairStatus?->title ?? ''),
            'org_name' => (string) ($senderEmp ? $senderEmp->departmentName() : ''),
            'requester_fullname' => (string) ($senderEmp ? $senderEmp->fullname : ''),
            'requester_position' => (string) ($senderEmp && is_array($senderEmp->data_json) ? (($senderEmp->data_json['position_name_text'] ?? '') . ($senderEmp->data_json['position_level_text'] ?? '')) : ''),
            'requester_phone' => (string) ($senderEmp->phone ?? ''),
            'sender_signature' => $senderEmp ? $senderEmp->signature() : null,
            'location' => is_array($model->data_json) ? (string) ($model->data_json['location'] ?? '') : '',
            'problem_detail' => is_array($model->data_json) ? (string) ($model->data_json['problem_detail'] ?? '') : '',
            'solution_detail' => is_array($model->data_json) ? (string) ($model->data_json['solution_detail'] ?? '') : '',
            'remark' => is_array($model->data_json) ? (string) ($model->data_json['remark'] ?? '') : '',
        ];

        $service = new PdfTemplateService();
        $pdfBinary = $service->generatePdfWithData((int) $template->id, $data);

        $filename = 'send-repair-' . (int) $model->id . '.pdf';
        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->set('Content-Type', 'application/pdf');
        Yii::$app->response->headers->set('Content-Disposition', 'inline; filename="' . $filename . '"');
        Yii::$app->response->content = $pdfBinary;
        return Yii::$app->response;
    }


    protected function GetInfo()
    {
        $info = SiteHelper::getInfo();
        return [
            'company_full' => $info['company_name'] . ' ' . $info['address'],  // ที่อยู่
            'company_name' => $info['company_name'],  // ชื่อหน่วยงาน
            'doc_number' => $info['doc_number'],  // ชื่อหน่วยงาน
            'leader_fullname' => $info['leader_fullname'],  //
            'leader_position' => $info['leader_position'],  //
            'address' => $info['address'],  // ที่อยู่
            'phone' => $info['phone'],  // โทรศัพท์
            'province' => $info['province'],  // ที่อยู่
            'director_name' => $info['director_name'],  // ชื่อผู้บริหาร ผอ.
            'director_fullname' => SiteHelper::viewDirector()['fullname'],  // ชื่อผู้บริหาร ผอ.
            'director_position' => $info['director_position'],  // ตำแหน่งของ ผอ.
            'director' => $info['director'],  // ตำแหน่งของ ผอ.
            'director_type' => $info['director_type']  // ประเภทตำแหน่งของ ผอ.
        ];
    }


    protected function findModel($id)
    {
        if (($model = Helpdesk::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
