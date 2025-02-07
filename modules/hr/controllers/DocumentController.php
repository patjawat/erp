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
        $this->CreateDir($model->id);
        $title = 'LT1-ใบลากิจ';
        $result_name = $title . '-' . $model->id . '.docx';
        $word_name = 'LT1-ใบลากิจ.docx';

        @unlink(Yii::getAlias('@webroot') . '/msword/results/leave/' . $result_name);
        $templateProcessor = new Processor(Yii::getAlias('@webroot') . '/msword/leave/' . $word_name);  // เลือกไฟล์ template ที่เราสร้างไว้

        $dateStart = Yii::$app->thaiFormatter->asDate($model->date_start ?? '0000-00-00', 'long');
        $dateEnd = Yii::$app->thaiFormatter->asDate($model->date_end  ?? '0000-00-00', 'long');
        $lastDateStart = Yii::$app->thaiFormatter->asDate($model->LastDays()['data']->date_start  ?? '0000-00-00', 'long');
        $lastDateEnd = Yii::$app->thaiFormatter->asDate($model->LastDays()['data']->date_end  ?? '0000-00-00', 'long');
        $createDate = new DateTime($model->created_at  ?? '0000-00-00');
        // $data = [
        //     'word_name' => $word_name,
        //     'result_name' => $result_name,
        // ];
                $templateProcessor->setValue('org_name',$this->GetInfo()['company_name']);
                $templateProcessor->setValue('org_position','ตำแหน่งผู้อำนวนการ'.$this->GetInfo()['company_name']);
                $templateProcessor->setImg('sign_director', ['src' => $this->GetInfo()['director']->signature(), 'size' => [150,60]]); //ลายมือผู้ตรวจสอบ
                $templateProcessor->setValue('title', $model->leaveType->title);
                $templateProcessor->setValue('m', AppHelper::getMonthName($createDate->format('m')));
                $templateProcessor->setValue('y', $createDate->format('Y') + 543);
                $templateProcessor->setValue('d', $createDate->format('d'));
                $templateProcessor->setValue('director', $this->GetInfo()['director_fullname']);
                $templateProcessor->setValue('createDate', Yii::$app->thaiFormatter->asDate($model->created_at, 'long'));
                $templateProcessor->setValue('fullname', $model->employee->fullname);
                $templateProcessor->setImg('sign', ['src' => $model->employee->signature(), 'size' => [150,50]]); //ลายมือผู้ขอลา
                $templateProcessor->setValue('position', 'ตำแหน่ง'.$model->employee->positionName());
                $templateProcessor->setValue('level_name', $model->employee->positionLevelName() ? 'ระดับ' . $model->employee->positionLevelName() : '');
                $templateProcessor->setValue('department', $model->employee->departmentName());
                $templateProcessor->setValue('dateStart', $dateStart);
                $templateProcessor->setValue('dateEnd', $dateEnd);
                $templateProcessor->setValue('lastDateStart', $lastDateStart ?? '-');
                $templateProcessor->setValue('lastDateEnd', $lastDateStart ?? '-');
                $templateProcessor->setValue('lastDays', $model->LastDays()['data']->total_days ?? 0);
                $templateProcessor->setValue('reason', $model->reason);
                $templateProcessor->setValue('leaveType', $model->leaveType->title);
                $templateProcessor->setValue('days', $model->total_days);
                $templateProcessor->setValue('total', ($model->total_days+($model->LastDays()['data']->total_days ?? 0)));
                $templateProcessor->setValue('address', $model->data_json['address']);
                
                $templateProcessor->setValue('checker1', $model->checkerName(1)['fullname']);
                $templateProcessor->setValue('position1', 'ตำแหน่ง'.$model->checkerName(1)['position']);
                $templateProcessor->setValue('approve_date1', $model->checkerName(1)['approve_date']);
                $templateProcessor->setImg('sign1', ['src' => $model->checkerName(1)['employee']->signature(), 'size' => [150,60]]); //ลายมือผู้ตรวจสอบ
                
                $templateProcessor->setValue('checker3', '('.$model->checkerName(3)['fullname'].')');
                $templateProcessor->setImg('sign3', ['src' => $model->checkerName(1)['employee']->signature(), 'size' => [150,60]]); //ลายมือผู้ตรวจสอบ
                $templateProcessor->setValue('position3', 'ตำแหน่ง'.$model->checkerName(3)['position']);
                $templateProcessor->setValue('status', $model->status == 'Approve' ? 'อนุญาต' : 'ไม่อนุญาต');
                
                $filePath = Yii::getAlias('@webroot') . '/msword/results/leave/' . $result_name;
                $templateProcessor->saveAs($filePath);  // สั่งให้บันทึกข้อมูลลงไฟล์ใหม่
                if (file_exists($filePath)) {
                    return Yii::$app->response->sendFile($filePath);
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
        $createDate = new DateTime($model->created_at);
        $templateProcessor->setValue('org_name', $this->GetInfo()['company_name']);
        // $templateProcessor->setValue('m', AppHelper::getMonthName($createDate->format('m')));
        $templateProcessor->setValue('y', $createDate->format('Y') + 543);
        $templateProcessor->setValue('d', $createDate->format('d'));
        $templateProcessor->setValue('director', $this->GetInfo()['director_fullname']);
        $templateProcessor->setValue('createDate', Yii::$app->thaiFormatter->asDate($model->created_at, 'long'));
        $templateProcessor->setValue('fullname',  $model->employee->fullname);
        $templateProcessor->setValue('position', $model->employee->positionName());
        $templateProcessor->setValue('department', $model->employee->departmentName());
        $templateProcessor->setValue('dateStart', $dateStart);
        $templateProcessor->setValue('dateEnd', $dateEnd);
        $templateProcessor->setValue('days', $model->total_days);  // จำนวนวันที่ลา
        $templateProcessor->setValue('last_days', $model->LastDays()['sum_all']);  // ลามาแล้ว
        $templateProcessor->setValue('ld', $model->entitlements()->data_json['last_day'] ?? 0);  // วันละพักผ่อนสะสม
        $templateProcessor->setValue('sum', $model->entitlements()->days ?? 0);  // รวมวันลาพักผ่อนที่ใช้ได้
        $templateProcessor->setValue('total', $model->total_days);  // รวมเป็น
        $templateProcessor->setValue('address', $model->data_json['address']);
        // $templateProcessor->setValue('send', (isset($model->leaveWorkSend()->fullname) ?  '' : ('(' . $model->leaveWorkSend()->fullname . ')')));
        $templateProcessor->setValue('send',  $model->leaveWorkSend()->fullname);
        $templateProcessor->setValue('sendPosition', $model->leaveWorkSend()->positionName());
        $templateProcessor->setValue('approve1', '(' . $model->checkerName(1)['fullname'] . ')');
        $templateProcessor->setValue('approveDate1', $model->checkerName(1)['approve_date']);
        $templateProcessor->setValue('position1', $model->checkerName(1)['position']);
        $templateProcessor->setValue('approve3', $model->checkerName(3)['fullname'] == null ? '' : ('(' . $model->checkerName(3)['fullname'] . ')'));
        $templateProcessor->setValue('approveDate3', $model->checkerName(3)['approve_date']);
        $templateProcessor->setValue('position3', $model->checkerName(3)['position']);
        // $templateProcessor->setValue('position4', $model->checkerName(4)['position']);
        $templateProcessor->setValue('position4', 'ตำแหน่งผู้อำนวยการ'.$this->GetInfo()['company_name']);
        $templateProcessor->setValue('approveDate4', $model->checkerName(4)['approve_date']);
        $templateProcessor->setValue('status', $model->status == 'Approve' ? 'อนุญาต' : 'ไม่อนุญาต');
        $templateProcessor->setImg('sign', ['src' => $model->employee->signature(), 'size' => [150,60]]); //ลายมือผู้ขอลา
        $templateProcessor->setImg('sign1', ['src' => $model->checkerName(1)['employee']->signature(), 'size' => [150,60]]); //ลายมือผู้บังคับบัญชา
        $templateProcessor->setImg('sign3', ['src' => $model->checkerName(3)['employee']->signature(), 'size' => [150,60]]); //ลายมือผู้บังคับบัญชา
        $templateProcessor->setImg('sign_send', ['src' => $model->leaveWorkSend()->signature(), 'size' => [150,60]]); //ลายมือผู้ปฏิบัตรหน้าที่แทน
        $templateProcessor->setImg('sign_director', ['src' => $this->GetInfo()['director']->signature(), 'size' => [150,60]]); //ลายมือผู้อำนวยการ
        // $templateProcessor->setImageValue('sign', [
        //     'path' => $tempImagePath,
        //     'width' => 300, // กำหนดความกว้าง (px)
        //     'height' => 200, // กำหนดความสูง (px)
        // ]);
        // ob_end_flush();  // สิ้นสุดบัฟเฟอร์และส่ง headers
        // $templateProcessor->setValue('l_days', $model->data_json['leave_days']);
            // URL ของรูปภาพ
            // $imageUrl = 'https://www.programmerthailand.com/uploads/1/1515641097_wordtable1.jpg';

            // // ดาวน์โหลดรูปภาพจาก URL และบันทึกเป็นไฟล์ชั่วคราว
            // $tempImagePath = Yii::getAlias('@runtime/temp_image.jpg');
            // file_put_contents($tempImagePath, file_get_contents($imageUrl));

            // // แทรกรูปภาพในเอกสาร Word
            // $templateProcessor->setImageValue('sign', [
            //     'src' => $imageUrl,
            //     'width' => 300, // กำหนดความกว้าง (px)
            //     'height' => 200, // กำหนดความสูง (px)
            // ]);

        $filePath = Yii::getAlias('@webroot') . '/msword/results/leave/' . $result_name;
        $templateProcessor->saveAs($filePath);  // สั่งให้บันทึกข้อมูลลงไฟล์ใหม่
        if (file_exists($filePath)) {
            return Yii::$app->response->sendFile($filePath);
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
            'director' => $info['director']  // ตำแหน่งของ ผอ.
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

    // function สร้าง Word
    public function CreateFile($data)
    {
        $result_name = $data['result_name'];
        @unlink(Yii::getAlias('@webroot') . '/msword/results/leave/' . $result_name);
        $templateProcessor = new Processor(Yii::getAlias('@webroot') . '/msword/leave/' . $data['word_name']);  // เลือกไฟล์ template ที่เราสร้างไว้
        foreach ($data['items'] as $key => $value) {
            $templateProcessor->setValue($key, $value);
        }

        $templateProcessor->saveAs(Yii::getAlias('@webroot') . '/msword/results/leave/' . $result_name);  // สั่งให้บันทึกข้อมูลลงไฟล์ใหม่
        return $this->Show($result_name);
    }

    private function Show($filename)
    {
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'status' => 'success',
                'title' => Html::a('<i class="fa-solid fa-cloud-arrow-down"></i> ดาวน์โหลดเอกสาร', Url::to(Yii::getAlias('@web') . '/msword/results/leave/' . $filename), ['class' => 'btn btn-primary text-center mb-3', 'target' => '_blank', 'onclick' => 'return closeModal()']),
                // 'content' => $this->renderAjax('show', ['filename' => $filename]),
            ];
        } else {
            echo '<p>';
            echo Html::a('ดาวน์โหลดเอกสาร', Url::to(Yii::getAlias('@web') . '/msword/results/asset_result.docx'), ['class' => 'btn btn-info']);  // สร้าง link download
            echo '</p>';
            // echo '<iframe src="https://view.officeapps.live.com/op/embed.aspx?src='.Url::to(Yii::getAlias('@web').'/msword/temp/asset_result.docx', true).'&embedded=true"  style="position: absolute;width:99%; height: 90%;border: none;"></iframe>';
            echo '<iframe src="https://docs.google.com/viewerng/viewer?url=' . Url::to(Yii::getAlias('@web') . '/msword/results/leave/' . $filename, true) . '&embedded=true"  style="position: absolute;width:100%; height: 100%;border: none;"></iframe>';
        }
    }
}
