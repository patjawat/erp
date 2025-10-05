<?php

namespace app\modules\helpdesk2\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\Response;
use setasign\Fpdi\Fpdi;
use app\models\Categorise;
use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\am\models\Asset;
use app\modules\filemanager\components\FileManagerHelper;
use yii\web\NotFoundHttpException;
use app\modules\helpdesk2\models\Helpdesk;
use app\modules\helpdesk2\models\HelpdeskDetail;



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
        //บันทึกเปลี่ยนสถานะและออกเลขใขรับซ่อม
        $model = $this->findModel($id);
        $model->status = 'receive';
        //ออกระหัสรับงานซ่อม
        $model->receive_date = date('Y-m-d H:i:s');
        $model->save();

        //เขียนลงบน timeline
        $serviceRecord = new HelpdeskDetail;
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

    public function actionPrint($id)
    {

        $model = $this->findModel($id);
        $formName = 'form_layout_service';
        $ref = substr(Yii::$app->getSecurity()->generateRandomString(), 10);
        $checkLayout = Categorise::findOne(['name' => $formName]);
        if($checkLayout){
            $layout = $checkLayout;
        }else{
            $layout = new Categorise();
            $layout->name = $formName;
            $layout->ref = $ref;
            $layout->save();
        }



        $urgency = isset($model->data_json['urgency']) ? $model->data_json['urgency'] : '';
        $urgencyX = isset($layout->data_json['urgency_x']) ? (float)$layout->data_json['urgency_x'] : 0;
        $urgencyY = isset($layout->data_json['urgency_y']) ? (float)$layout->data_json['urgency_y'] : 0;



        $pdf = new \setasign\Fpdi\Fpdi();
        $pdf->AddPage();
        // 1️⃣ กำหนด PDF Template ก่อน
        $templateUrl = Url::to(['/dms/documents/show', 'ref' => $model->ref]);

        $templateFile = FileManagerHelper::getFileFormRef($layout->ref);
        if($templateFile){

  
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

        // รายละเอียดปัญหา
        $title = $model->title;
        $titleX = isset($layout->data_json['title_x']) ? (float)$layout->data_json['title_x'] : 0;
        $titleY = isset($layout->data_json['title_y']) ? (float)$layout->data_json['title_y'] : 0;
        $pdf->SetXY($titleX, $titleY);
        $pdf->Write(10,  $this->t($title));

        //สถานที่
        $location = isset($model->data_json['location']) ? $model->data_json['location'] : '';
        $locationX = isset($layout->data_json['location_x']) ? (float)$layout->data_json['location_x'] : 0;
        $locationy = isset($layout->data_json['location_y']) ? (float)$layout->data_json['location_y'] : 0;
        $pdf->SetXY($locationX, $locationy);
        $pdf->Write(10,  $this->t($location));


        // ประเภทอุปกรณ์
        $deviceType = $model->deviceType->title ?? '-';
        $locationX = isset($layout->data_json['device_x']) ? (float)$layout->data_json['device_x'] : 0;
        $locationy = isset($layout->data_json['device_y']) ? (float)$layout->data_json['device_y'] : 0;
        $pdf->SetXY($locationX, $locationy);
        $pdf->Write(10,  $this->t($deviceType));

        // ผู้ส่งซ่อม
        $createdBy = $model->emp->fullname ?? '-';
        $createdByX = isset($layout->data_json['createdby_x']) ? (float)$layout->data_json['createdby_x'] : 0;
        $createdByY = isset($layout->data_json['createdby_y']) ? (float)$layout->data_json['createdby_y'] : 0;
        $pdf->SetXY($createdByX, $createdByY);
        $pdf->Write(10,  $this->t($createdBy));

        $createDate = $model->viewCreated()['full'];
        $createDateX = isset($layout->data_json['created_x']) ? (float)$layout->data_json['created_x'] : 0;
        $createDateY = isset($layout->data_json['created_y']) ? (float)$layout->data_json['created_y'] : 0;
        $pdf->SetXY($createDateX, $createDateY);
        $pdf->Write(10,  $this->t($createDate));



        
        $pdf->SetXY($urgencyX, $urgencyY);
        $pdf->Write(10,  $this->t($model->viewUrgent()['title']));


        $pdf->SetXY(59, 40);
        $pdf->MultiCell(100, 8, $this->t($model->data_json['note']));

        // 5️⃣ Output PDF
        $pdf->Output('I', 'filled.pdf');
    }

    }



    protected function findModel($id)
    {
        if (($model = Helpdesk::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
