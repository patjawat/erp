<?php
namespace app\modules\inventory\controllers;

use yii;
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
use yii\web\NotFoundHttpException;
use app\modules\purchase\models\Order;
use app\modules\inventory\models\Stock;
use app\modules\am\components\AssetHelper;
use app\modules\inventory\models\StockEvent;

class DocumentController extends \yii\web\Controller
{

    
    // ใบเบิกวัสดุ
    public function actionStockOrder($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = StockEvent::findOne($id);
        $this->CreateDir($model->id);
        $title = 'ใบเบิกวัสดุ';
        $result_name = $title . '.docx';
        $word_name = 'billofmaterials.docx';

        @unlink(Yii::getAlias('@webroot') . '/msword/results/inventory/' . $result_name);
        $templateProcessor = new Processor(Yii::getAlias('@webroot') . '/msword/' . $word_name);  // เลือกไฟล์ template ที่เราสร้างไว้

        $templateProcessor->setValue('title', 'ใบเบิกวัสดุ');
        // $templateProcessor->setValue('date', Yii::$app->thaiFormatter->asDateTime(date('Y-m-y'), 'php:d/m/Y'));
        $templateProcessor->setValue('date',  Yii::$app->thaiDate->toThaiDate($model->created_at, false, false));
        $templateProcessor->setValue('org_name_full', $this->getInfo()['company_full']);
        $templateProcessor->setValue('department',$model->CreateBy()['department']);
        $templateProcessor->setValue('number', $model->code);
        $templateProcessor->setValue('total', number_format($model->getTotalOrderPrice(), 2));
        // $templateProcessor->setValue('date', isset($model->data_json['order_date']) ? (AppHelper::thainumDigit(Yii::$app->thaiFormatter->asDate($model->data_json['order_date'], 'medium'))) : '-');
        // $templateProcessor->setValue('date', isset($model->data_json['committee_detail_date']) ? (Yii::$app->thaiFormatter->asDate($model->data_json['committee_detail_date'], 'long')) : '-');
        $templateProcessor->setValue('doc_title', 'ขออนุมัติแต่งตั้งคณะกรรมการกำหนดรายละเอียดคุณลักษณะเฉพาะ');

        $templateProcessor->setValue('drawer_name', $model->CreateBy()['fullname']);
        $templateProcessor->setValue('drawer_position', 'ตำแหน่ง'.$model->CreateBy()['position_name']);
        $templateProcessor->setValue('date_drawer', $model->viewCreatedAt());

        $templateProcessor->setValue('approve_name', $model->viewChecker()['fullname'] !='' ? $model->viewChecker()['fullname'] : '.................................................') ;
        $templateProcessor->setValue('a_position',  'ตำแหน่ง'.$model->viewChecker()['position']);
        $templateProcessor->setValue('approve_date',  $model->viewChecker()['approve_date'] !='' ? $model->viewChecker()['approve_date'] : '.................................................') ;
        $templateProcessor->setValue('recipientname', isset($model->data_json['recipient_fullname']) ? $model->data_json['recipient_fullname'] : '.................................................');
        $templateProcessor->setValue('r_position', isset($model->data_json['recipient_position']) ? 'ตำแหน่ง'.$model->data_json['recipient_position'] : 'ตำแหน่ง'.'.................................................');
        $templateProcessor->setValue('recipientdate',  isset($model->data_json['recipient_date']) ? Yii::$app->thaiDate->toThaiDate($model->data_json['recipient_date'], true, false) : '........................................');

        $templateProcessor->setValue('leader_fullname', $this->getInfo()['leader_fullname']);
        $templateProcessor->setValue('leader_position', 'ตำแหน่ง'.$this->getInfo()['leader_position']);

        $templateProcessor->setValue('director_name', $this->GetInfo()['director_fullname']);  // ผู้อำนวยการโรงพยาบาล
        $templateProcessor->setValue('org_name', 'ผู้อำนวยการ' . $this->GetInfo()['company_name']);  // ชื่อโรงพยาบาล

        // วันที่สั่งจ่าย
        try {
             $datetime = \Yii::$app->thaiDate->toThaiDate($model->data_json['player_date'], true, false);
            $templateProcessor->setValue('pay_date', $datetime);  // วันที่
        } catch (\Throwable $th) {
            $templateProcessor->setValue('pay_date', '-');  // วันที่
        }

        $templateProcessor->setValue('pay_name', $model->ShowPlayer()['fullname']);  // ผู้จ่าย
        $templateProcessor->setValue('pay_position', 'ตำแหน่ง'.$model->ShowPlayer()['position_name']);  // ผู้จ่าย

        $templateProcessor->cloneRow('detail', count($model->getItems()));
        $i = 1;
        $num = 1;
        foreach ($model->getItems() as $item) {
            $templateProcessor->setValue('no#' . $i, $num++);
            $templateProcessor->setValue('detail#' . $i, $item->product->title);
            $templateProcessor->setValue('unit#' . $i, $item->product->data_json['unit']);
            // $templateProcessor->setValue('qty#' . $i, $item->qty);
            $templateProcessor->setValue('rqty#' . $i, $item->data_json['req_qty']);
            $templateProcessor->setValue('qty#' . $i, ($model->order_status == 'success' ? $item->qty : '-'));
            $templateProcessor->setValue('unitprice#' . $i, $item->unit_price);
            $templateProcessor->setValue('sumprice#' . $i, number_format(($item->qty * $item->unit_price), 2));
            $i++;
        }

        $templateProcessor->saveAs(Yii::getAlias('@webroot') . '/msword/results/' . $result_name);  // สั่งให้บันทึกข้อมูลลงไฟล์ใหม่
        // return $this->redirect('https://docs.google.com/viewerng/viewer?url=' . Url::base('https') . '/msword/results/' . $result_name);
        return $this->Show($result_name);
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
