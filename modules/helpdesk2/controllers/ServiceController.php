<?php

namespace app\modules\helpdesk2\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\Response;
use app\models\Categorise;
use app\components\AppHelper;
use app\components\SiteHelper;
use app\components\UserHelper;
use app\modules\am\models\Asset;
use yii\web\NotFoundHttpException;
use app\modules\helpdesk2\models\Helpdesk;
use app\modules\helpdesk2\models\HelpdeskDetail;
use app\modules\filemanager\components\FileManagerHelper;



class ServiceController extends \yii\web\Controller
{
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
