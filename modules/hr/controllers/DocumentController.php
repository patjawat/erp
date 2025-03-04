<?php
namespace app\modules\hr\controllers;

use yii;
use DateTime;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\Response;
use yii\helpers\FileHelper;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\Processor;
use app\components\SiteHelper;
use PhpOffice\PhpWord\Settings;
use yii\helpers\BaseFileHelper;
use app\modules\am\models\Asset;
use app\modules\hr\models\Leave;
use yii\web\NotFoundHttpException;

class DocumentController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

    // ลากิจส่วนตัว ลาป่วย ลาคลอดบุตร
    public function actionLeavelt1($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = Leave::findOne($id);
        // $this->CreateDir($model->id);
        $title = 'LT1-ใบลากิจ';
        $result_name = $title . '-' . $model->id . '.docx';
        // $result_name = $model->id . '.docx';
        $word_name = 'LT1-ใบลากิจ.docx';

        @unlink(Yii::getAlias('@webroot') . '/msword/results/leave/' . $result_name);
        $templateProcessor = new Processor(Yii::getAlias('@webroot') . '/msword/leave/' . $word_name);  // เลือกไฟล์ template ที่เราสร้างไว้

        try {
            $dateStart = Yii::$app->thaiFormatter->asDate($model->date_start ?? '0000-00-00', 'long');
        } catch (\Throwable $th) {
            $dateStart = '-';
        }
        try {
            $dateEnd = Yii::$app->thaiFormatter->asDate($model->date_end ?? '0000-00-00', 'long');
        } catch (\Throwable $th) {
            $dateEnd = '';
        }
        
        $lastDays = $model->LastDays();
        $lastDateStart = is_object($lastDays['data']) ? Yii::$app->thaiFormatter->asDate($lastDays['data']->date_start, 'long') : '-';
        $lastDateEnd = is_object($lastDays['data']) ? Yii::$app->thaiFormatter->asDate($lastDays['data']->date_end, 'long') : '-';

        $createDate = new DateTime($model->created_at ?? '-');
        $templateProcessor->setValue('org_name', $this->GetInfo()['company_name']);
        $templateProcessor->setValue('org_position', 'ผู้อำนวยการ' . $this->GetInfo()['company_name']);

        $templateProcessor->setValue('title', $model->leaveType->title);
        $templateProcessor->setValue('createDate', Yii::$app->thaiFormatter->asDate($model->created_at, 'long'));
        $templateProcessor->setValue('director', $this->GetInfo()['director_fullname']);
        $templateProcessor->setValue('createDate', Yii::$app->thaiFormatter->asDate($model->created_at, 'long'));

        $templateProcessor->setValue('level_name', $model->employee->positionLevelName() ? 'ระดับ' . $model->employee->positionLevelName() : '');
        $templateProcessor->setValue('department', $model->employee->departmentName());
        $templateProcessor->setValue('dateStart', $dateStart);
        $templateProcessor->setValue('dateEnd', $dateEnd);
        $templateProcessor->setValue('lastDateStart', $lastDateStart);
        $templateProcessor->setValue('lastDateEnd', $lastDateStart);
        $templateProcessor->setValue('lastDays', $model->LastDays()['data']->total_days ?? 0);
        $templateProcessor->setValue('reason', $model->reason);
        $templateProcessor->setValue('leaveType', $model->leaveType->title);
        $templateProcessor->setValue('days', $model->total_days);
        $templateProcessor->setValue('total', ($model->total_days + ($model->LastDays()['data']->total_days ?? 0)));
        $templateProcessor->setValue('address', $model->data_json['address']);
        $templateProcessor->setValue('status', $model->status == 'Allow' ? 'อนุญาต' : 'ไม่อนุญาต');

        // ชื่อผู้ขอลา
        $templateProcessor->setValue('emp_fullname', '( '.$model->employee->fullname.' )');
        $templateProcessor->setValue('emp_position', 'ตำแหน่ง' . $model->employee->positionName());
        try {
            $templateProcessor->setImg('emp_sign', ['src' => $model->employee->signature(), 'size' => [150, 50]]);  // ลายมือผู้ขอลา
        } catch (\Throwable $th) {
            $templateProcessor->setValue('emp_sign', '');  // ลายมือผู้ขอลา
        }

        // ###########################  การตรวจสอบอนุมัติ ######################################
        // หัวหน้ากลุ่มงาน ตรวจสสอบ level 2
        $templateProcessor->setValue('leader_fullname', $model->checkerName(2)['fullname']);
        $templateProcessor->setValue('leader_position', 'ตำแหน่ง' . $model->checkerName(2)['position']);
        $templateProcessor->setValue('leader_date', $model->checkerName(3)['approve_date']);
        try {
            $templateProcessor->setImg('leader_sign', ['src' => $model->checkerName(1)['employee']->signature(), 'size' => [150, 60]]);  // ลายมือหัวหน้างาน
        } catch (\Throwable $th) {
            $templateProcessor->setValue('leader_sign', '');
        }

        // เจ้าหน้าที่ HR ตรวจสสอบ Level 3
        $hr = $model->checkerName(3);
        $templateProcessor->setValue('hr_fullname', $hr['fullname']);
        $templateProcessor->setValue('hr_position', 'ตำแหน่ง' . $hr['position']);
        $templateProcessor->setValue('hr_date', $hr['approve_date']);
        try {
            $templateProcessor->setImg('hr_sign', ['src' => $model->checkerName(1)['employee']->signature(), 'size' => [150, 60]]);  // ลายมือผู้ตรวจสอบ
        } catch (\Throwable $th) {
            $templateProcessor->setValue('hr_sign', '');
        }

        // ผู้อำนวยการตรวจสอบ อนุมัติให้ลา/ไม่ให้ลา
        $dicrectorType = ($this->GetInfo()['director_type'] == 'รักษาการแทน' ? 'รักษาการแทน' : '');
        $templateProcessor->setValue('direc_fullname', $model->checkerName(4)['fullname']);
        $templateProcessor->setValue('direc_position', 'ตำแหน่ง' . $model->checkerName(4)['position'].$dicrectorType);
        // $templateProcessor->setValue('direc_position', 'ตำแหน่ง' . $model->checkerName(4)['position']).$dicrectorType;
        $templateProcessor->setValue('direc_date', $model->checkerName(3)['approve_date']);
        try {
            $templateProcessor->setImg('direc_sign', ['src' => $model->checkerName(4)['employee']->signature(), 'size' => [150, 60]]);  // ลายมือผู้ตรวจสอบ
            // $templateProcessor->setImg('direc_sign', ['src' => $this->GetInfo()['director']->signature(), 'size' => [150,60]]); //ลายมือผู้อำนวยการ
        } catch (\Throwable $th) {
            $templateProcessor->setValue('direc_sign', '');
        }

        $filePath = Yii::getAlias('@webroot') . '/msword/results/leave/' . $result_name;
        $templateProcessor->saveAs($filePath);  // สั่งให้บันทึกข้อมูลลงไฟล์ใหม่
        
        if (file_exists($filePath)) {
            return $this->Show($result_name);
            // return Yii::$app->response->sendFile($filePath);
        } else {
            throw new \yii\web\NotFoundHttpException('The file does not exist.');
        }

        // return $this->redirect('https://docs.google.com/viewerng/viewer?url=' . Url::base('https') . '/msword/results/leave/' . $result_name);
        // return $this->CreateFile($data);
    }

    // ใบพักผ่อน
    public function actionLeavelt4($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = Leave::findOne($id);
        // $this->CreateDir($model->id);
        $title = 'LT4';
        $result_name = $title . '-' . $model->id . '.docx';
        $word_name = 'LT4-ใบลาพักผ่อน.docx';

        @unlink(Yii::getAlias('@webroot') . '/msword/results/leave/' . $result_name);
        $templateProcessor = new Processor(Yii::getAlias('@webroot') . '/msword/leave/' . $word_name);  // เลือกไฟล์ template ที่เราสร้างไว้

        // return $model->checkerName(1)['employee']->signature();
        $dateStart = Yii::$app->thaiFormatter->asDate($model->date_start, 'long');
        $dateEnd = Yii::$app->thaiFormatter->asDate($model->date_end, 'long');

        $templateProcessor->setValue('org_name', $this->GetInfo()['company_name']);
        $templateProcessor->setValue('director', $this->GetInfo()['director_fullname']);
        $templateProcessor->setValue('createDate', Yii::$app->thaiFormatter->asDate($model->created_at, 'long'));
        $templateProcessor->setValue('emp_department', $model->employee->departmentName());
        $templateProcessor->setValue('dateStart', $dateStart);
        $templateProcessor->setValue('dateEnd', $dateEnd);
        $templateProcessor->setValue('days', $model->total_days);  // จำนวนวันที่ลา
        $templateProcessor->setValue('last_days', $model->LastDays()['sum_all']);  // ลามาแล้ว
        $templateProcessor->setValue('ld', $model->entitlements()->days ?? 0);  // วันละพักผ่อนสะสมประจำปี
        $templateProcessor->setValue('sum', $model->entitlements()->days ?? 0);  // รวมวันลาพักผ่อนที่ใช้ได้
        $templateProcessor->setValue('total', $model->total_days);  // รวมเป็น
        $templateProcessor->setValue('address', $model->data_json['address']);
        $templateProcessor->setValue('status', $model->status == 'Allow' ? 'อนุญาต' : 'ไม่อนุญาต');

        // ชื่อผู้ขอลา
        $templateProcessor->setValue('emp_fullname', $model->employee->fullname);
        $templateProcessor->setValue('emp_position', 'ตำแหน่ง' . $model->employee->positionName());
        try {
            $templateProcessor->setImg('emp_sign', ['src' => $model->employee->signature(), 'size' => [150, 50]]);  // ลายมือผู้ขอลา
        } catch (\Throwable $th) {
            $templateProcessor->setValue('emp_sign', '');  // ลายมือผู้ขอลา
        }

        // ชื่อผู้ปฏบัติหน้าที่แทน
        $templateProcessor->setValue('send_fullname', $model->leaveWorkSend()->fullname);
        $templateProcessor->setValue('send_position', 'ตำแหน่ง' . $model->leaveWorkSend()->positionName());
        try {
            $templateProcessor->setImg('send_sign', ['src' => $model->leaveWorkSend()->signature(), 'size' => [150, 50]]);  // ลายมือผู้ขอลา
        } catch (\Throwable $th) {
            $templateProcessor->setValue('send_sign', '');  // ลายมือผู้ขอลา
        }

        // ###########################  การตรวจสอบอนุมัติ ######################################
        // หัวหน้ากลุ่มงาน ตรวจสสอบ level 2
        $templateProcessor->setValue('leader_fullname', $model->checkerName(2)['fullname']);
        $templateProcessor->setValue('leader_position', 'ตำแหน่ง' . $model->checkerName(2)['position']);
        $templateProcessor->setValue('leader_date', $model->checkerName(3)['approve_date']);
        try {
            $templateProcessor->setImg('leader_sign', ['src' => $model->checkerName(1)['employee']->signature(), 'size' => [150, 60]]);  // ลายมือหัวหน้างาน
        } catch (\Throwable $th) {
            $templateProcessor->setValue('leader_sign', '');
        }

        // เจ้าหน้าที่ HR ตรวจสสอบ Level 3
        $hr = $model->checkerName(3);
        $templateProcessor->setValue('hr_fullname', $hr['fullname']);
        $templateProcessor->setValue('hr_position', 'ตำแหน่ง' . $hr['position']);
        $templateProcessor->setValue('hr_date', $hr['approve_date']);
        try {
            $templateProcessor->setImg('hr_sign', ['src' => $model->checkerName(1)['employee']->signature(), 'size' => [150, 60]]);  // ลายมือผู้ตรวจสอบ
        } catch (\Throwable $th) {
            $templateProcessor->setValue('hr_sign', '');
        }

        // ผู้อำนวยการตรวจสอบ อนุมัติให้ลา/ไม่ให้ลา
        $dicrectorType = ($this->GetInfo()['director_type'] == 'ผู้อำนวยการ' ? 'ผู้อำนวยการ' : 'รักษาการแทนผู้อำนวยการ');
        $templateProcessor->setValue('direc_fullname', $model->checkerName(4)['fullname']);
        $templateProcessor->setValue('direc_position', 'ตำแหน่ง' . $model->checkerName(4)['position'].$dicrectorType);
        $templateProcessor->setValue('direc_date', $model->checkerName(3)['approve_date']);
        try {
            $templateProcessor->setImg('direc_sign', ['src' => $model->checkerName(4)['employee']->signature(), 'size' => [150, 60]]);  // ลายมือผู้ตรวจสอบ
            // $templateProcessor->setImg('direc_sign', ['src' => $this->GetInfo()['director']->signature(), 'size' => [150,60]]); //ลายมือผู้อำนวยการ
        } catch (\Throwable $th) {
            $templateProcessor->setValue('direc_sign', '');
        }

        $filePath = Yii::getAlias('@webroot') . '/msword/results/leave/' . $result_name;
        $templateProcessor->saveAs($filePath);  // สั่งให้บันทึกข้อมูลลงไฟล์ใหม่
        if (file_exists($filePath)) {
            return $this->Show($result_name);
            // return Yii::$app->response->sendFile($filePath);
        } else {
            throw new \yii\web\NotFoundHttpException('The file does not exist.');
        }

        return $this->redirect('https://docs.google.com/viewerng/viewer?url=' . Url::base('https') . '/msword/results/leave/' . $result_name);
        // return $this->Show($result_name);
    }

    // ดึงค่ากน่วยงาน

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

    public static function CreateDir($folderName)
    {
        $downloadPath = Yii::getAlias('@app') . '/web/downloads';
        if ($downloadPath != null) {
            BaseFileHelper::createDirectory($downloadPath, 0777);
        }

        if ($folderName != null) {
            $basePath = Yii::getAlias('@app') . '/web/msword/results/';
            BaseFileHelper::createDirectory($basePath . $folderName, 0777);
        }
        return;
    }

    private function Show($filename)
    {
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'status' => 'success',
                'title' => Html::a($this->GetInfo()['director_type'].'<i class="fa-solid fa-cloud-arrow-down"></i> ดาวน์โหลดเอกสาร', Url::to(Yii::getAlias('@web') . '/msword/results/leave/' . $filename), ['class' => 'btn btn-primary text-center mb-3', 'target' => '_blank', 'onclick' => 'return closeModal()']),
                'content' => $this->renderAjax('show', ['filename' => $filename]),
            ];
        } else {
            echo '<p>';
            echo Html::a('ดาวน์โหลดเอกสาร', Url::to(Yii::getAlias('@web') . $filename), ['class' => 'btn btn-info']);  // สร้าง link download
            echo '</p>';
            // echo '<iframe src="https://view.officeapps.live.com/op/embed.aspx?src='.Url::to(Yii::getAlias('@web').'/msword/temp/asset_result.docx', true).'&embedded=true"  style="position: absolute;width:99%; height: 90%;border: none;"></iframe>';
            echo '<iframe src="https://docs.google.com/viewerng/viewer?url=' . Url::to(Yii::getAlias('@web') . $filename, true) . '&embedded=true"  style="position: absolute;width:100%; height: 100%;border: none;"></iframe>';
        }
    }
}
