<?php
namespace app\modules\hr\controllers;

use yii;
use DateTime;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\Response;
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

    
    // ลากิจส่วนตัว ลาป่วย ลาคลอดบุตร
    public function actionLeavelt1($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = Leave::findOne($id);
        $this->CreateDir($model->id);
        $title = 'LT1-ใบลากิจ';
        $result_name = $title .'-'.$model->id.'.docx';
        $word_name = 'LT1-ใบลากิจ.docx';

        @unlink(Yii::getAlias('@webroot') . '/msword/results/leave/' . $result_name);
        $templateProcessor = new Processor(Yii::getAlias('@webroot') . '/msword/leave/' . $word_name);  // เลือกไฟล์ template ที่เราสร้างไว้

        $dateStart = Yii::$app->thaiFormatter->asDate($model->date_start, 'long');
        $dateEnd = Yii::$app->thaiFormatter->asDate($model->date_end, 'long');
        $templateProcessor->setValue('org_name', $this->GetInfo()['company_name']);
        $templateProcessor->setValue('title',$model->leaveType->title);
        $createDate  = new DateTime($model->created_at);
        $templateProcessor->setValue('m', AppHelper::getMonthName($createDate->format('m')));
        $templateProcessor->setValue('y', $createDate->format('Y') + 543);
        $templateProcessor->setValue('d', $createDate->format('d'));
        $templateProcessor->setValue('director', $this->GetInfo()['director_fullname']);
        $templateProcessor->setValue('createDate', Yii::$app->thaiFormatter->asDate($model->created_at, 'long'));
        $templateProcessor->setValue('fullname', $model->employee->fullname);
        $templateProcessor->setValue('position', $model->employee->positionName());
        $templateProcessor->setValue('level_name', $model->employee->positionLevelName() ? 'ระดับ'.$model->employee->positionLevelName() : '');
        $templateProcessor->setValue('department', $model->employee->departmentName());
        $templateProcessor->setValue('dateStart', $dateStart);
        $templateProcessor->setValue('dateEnd', $dateEnd);
        $templateProcessor->setValue('reason', $model->reason);
        $templateProcessor->setValue('days', $model->total_days);
        $templateProcessor->setValue('address', $model->data_json['address']);
        $templateProcessor->setValue('checker1', $model->checkerName(1)['fullname']);
        $templateProcessor->setValue('position1', $model->checkerName(1)['position']);
        $templateProcessor->setValue('checker3', $model->checkerName(3)['fullname']);
        $templateProcessor->setValue('position3', $model->checkerName(3)['position']);
        $templateProcessor->setValue('status', $model->status == 'Approve' ? 'อนุญาต' : 'ไม่อนุญาต');
        
        $templateProcessor->saveAs(Yii::getAlias('@webroot') . '/msword/results/leave/' . $result_name);  // สั่งให้บันทึกข้อมูลลงไฟล์ใหม่
        return $this->redirect('https://docs.google.com/viewerng/viewer?url=' . Url::base('https') . '/msword/results/leave/' . $result_name);
        // return $this->Show($result_name);
    }

    // ใบพักผ่อน
    public function actionLeavelt4($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = Leave::findOne($id);
        $this->CreateDir($model->id);
        $title = 'LT4';
        $result_name = $title .'-'.$model->id.'.docx';
        $word_name = 'LT4-ใบลาพักผ่อน.docx';

        @unlink(Yii::getAlias('@webroot') . '/msword/results/leave/' . $result_name);
        $templateProcessor = new Processor(Yii::getAlias('@webroot') . '/msword/leave/' . $word_name);  // เลือกไฟล์ template ที่เราสร้างไว้

        $dateStart = Yii::$app->thaiFormatter->asDate($model->date_start, 'long');
        $dateEnd = Yii::$app->thaiFormatter->asDate($model->date_end, 'long');
        $templateProcessor->setValue('org_name', $this->GetInfo()['company_name']);
        $createDate  = new DateTime($model->created_at);
        $templateProcessor->setValue('m', AppHelper::getMonthName($createDate->format('m')));
        $templateProcessor->setValue('y', $createDate->format('Y') + 543);
        $templateProcessor->setValue('d', $createDate->format('d'));
        $templateProcessor->setValue('director', $this->GetInfo()['director_fullname']);
        $templateProcessor->setValue('createDate', Yii::$app->thaiFormatter->asDate($model->created_at, 'long'));
        $templateProcessor->setValue('fullname', '('.$model->employee->fullname.')');
        $templateProcessor->setValue('position', $model->employee->positionName());
        $templateProcessor->setValue('department', $model->employee->departmentName());
        $templateProcessor->setValue('dateStart', $dateStart);
        $templateProcessor->setValue('dateEnd', $dateEnd);
        $templateProcessor->setValue('days', $model->total_days);//จำนวนวันที่ลา
        $templateProcessor->setValue('last_days', $model->leaveLastDays()); //ลามาแล้ว
        $templateProcessor->setValue('total', $model->total_days); // รวมเป็น
        $templateProcessor->setValue('address', $model->data_json['address']);
        $templateProcessor->setValue('send', '('.$model->leaveWorkSend()['fullname'].')');
        $templateProcessor->setValue('sendPosition', $model->leaveWorkSend()['position']);
        $templateProcessor->setValue('approve1', '('.$model->checkerName(1)['fullname'].')');
        $templateProcessor->setValue('approveDate1', $model->checkerName(1)['approve_date']);
        $templateProcessor->setValue('position1', $model->checkerName(1)['position']);
        $templateProcessor->setValue('approve3', '('.$model->checkerName(3)['fullname'].')');
        $templateProcessor->setValue('approveDate3', $model->checkerName(3)['approve_date']);
        $templateProcessor->setValue('position3', $model->checkerName(3)['position']);
        $templateProcessor->setValue('status', $model->status == 'Approve' ? 'อนุญาต' : 'ไม่อนุญาต');
        // $templateProcessor->setValue('l_days', $model->data_json['leave_days']);

        
        
        $templateProcessor->saveAs(Yii::getAlias('@webroot') . '/msword/results/leave/' . $result_name);  // สั่งให้บันทึกข้อมูลลงไฟล์ใหม่
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
            'director_position' => $info['director_position']  // ตำแหน่งของ ผอ.
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
                'title' => Html::a('<i class="fa-solid fa-cloud-arrow-down"></i> ดาวน์โหลดเอกสาร', Url::to(Yii::getAlias('@web') . '/msword/results/' . $filename), ['class' => 'btn btn-primary text-center mb-3', 'target' => '_blank', 'onclick' => 'return closeModal()']),
                'content' => $this->renderAjax('@app/views/ms-word/word', ['filename' => $filename]),
            ];
        } else {
            echo '<p>';
            echo Html::a('ดาวน์โหลดเอกสาร', Url::to(Yii::getAlias('@web') . '/msword/results/asset_result.docx'), ['class' => 'btn btn-info']);  // สร้าง link download
            echo '</p>';
            // echo '<iframe src="https://view.officeapps.live.com/op/embed.aspx?src='.Url::to(Yii::getAlias('@web').'/msword/temp/asset_result.docx', true).'&embedded=true"  style="position: absolute;width:99%; height: 90%;border: none;"></iframe>';
            echo '<iframe src="https://docs.google.com/viewerng/viewer?url=' . Url::to(Yii::getAlias('@web') . '/msword/temp/asset_result.docx', true) . '&embedded=true"  style="position: absolute;width:100%; height: 100%;border: none;"></iframe>';
        }
    }
}
