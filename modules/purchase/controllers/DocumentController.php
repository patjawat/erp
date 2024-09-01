<?php

namespace app\modules\purchase\controllers;

use app\components\AppHelper;
use app\components\Processor;
use app\components\SiteHelper;
use yii\helpers\ArrayHelper;
use app\modules\am\components\AssetHelper;
use app\modules\am\models\Asset;
use app\modules\inventory\models\Stock;
use app\modules\purchase\models\Order;
use PhpOffice\PhpWord\Settings;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\Response;
use yii\helpers\BaseFileHelper;
use yii;
use yii\web\NotFoundHttpException;

class DocumentController extends \yii\web\Controller
{

    protected function findOrderModel($id)
    {
        if (($model = Order::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }

    // ดึงค่ากน่วยงาน
    protected function GetInfo()
    {
        $info = SiteHelper::getInfo();
        return [
            'company_full' => $info['company_name'] . ' ' . $info['address'],  // ที่อยู่
            'company_name' => $info['company_name'],  // ชื่อหน่วยงาน
            'doc_number' => $info['doc_number'],  // ชื่อหน่วยงาน
            'leader_fullname' => $info['leader_fullname'], //
            'leader_position' => $info['leader_position'], // 
            'address' => $info['address'],  // ที่อยู่
            'phone' => $info['phone'],  // โทรศัพท์
            'province' => $info['province'],  // ที่อยู่
            'director_name' => $info['director_name'],  // ชื่อผู้บริหาร ผอ.
            'director_fullname' => SiteHelper::viewDirector()['fullname'],  // ชื่อผู้บริหาร ผอ.
            'director_position' => $info['director_position']  // ตำแหน่งของ ผอ.
        ];
    }

    private function Show($filename)
    {
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'status' => 'success',
                'title' => Html::a('<i class="fa-solid fa-cloud-arrow-down"></i> ดาวน์โหลดเอกสาร', Url::to(Yii::getAlias('@web') . '/msword/results/purchase/' . $filename), ['class' => 'btn btn-primary text-center mb-3', 'target' => '_blank', 'onclick' => 'return closeModal()']),
                'content' => $this->renderAjax('word', ['filename' => $filename]),
            ];
        } else {
            echo '<p>';
            echo Html::a('ดาวน์โหลดเอกสาร', Url::to(Yii::getAlias('@web') . '/msword/results/asset_result.docx'), ['class' => 'btn btn-info']);  // สร้าง link download
            echo '</p>';
            // echo '<iframe src="https://view.officeapps.live.com/op/embed.aspx?src='.Url::to(Yii::getAlias('@web').'/msword/temp/asset_result.docx', true).'&embedded=true"  style="position: absolute;width:99%; height: 90%;border: none;"></iframe>';
            echo '<iframe src="https://docs.google.com/viewerng/viewer?url=' . Url::to(Yii::getAlias('@web') . '/msword/temp/asset_result.docx', true) . '&embedded=true"  style="position: absolute;width:100%; height: 100%;border: none;"></iframe>';
        }
    }



    // Action สำหรับสร้างไฟล์ ZIP
    // public function actionCreateZip($id)
    // {
    //     $filename = 'purchase-'.$id.'.zip';
    //     $sourcePath = Yii::getAlias('@app/web/msword/results/purchase/'.$id); // โฟลเดอร์ที่จะบีบอัด
    //     $zipPath = Yii::getAlias('@app/web/downloads/purchase/'.$filename); // ไฟล์ ZIP ที่จะสร้าง

    //     // เรียกใช้ Component สำหรับสร้างไฟล์ ZIP
    //     if (Yii::$app->zip->createZip($sourcePath, $zipPath)) {
    //         return $this->redirect(['download-zip', 'filename' => $filename]);
    //     } else {
    //         return 'Failed to create ZIP file.';
    //     }
    // }

    // Action สำหรับดาวน์โหลดไฟล์ ZIP
    // public function actionDownloadZip($filename)
    public function actionDownloadFile($id)
    {
        // Yii::$app->response->format = Response::FORMAT_JSON;

        // กำหนดเส้นทางของไฟล์ที่จะดาวน์โหลด
        // return $filename;
        $filename = 'purchase-'.$id.'.zip';
        $filePath = Yii::getAlias('@webroot/downloads/' . $filename);
        // $filePath = Yii::getAlias('@webroot/downloads/' . $x);
        // $filePath = Yii::getAlias('@app/web/downloads/' . $filename);

        // ตรวจสอบว่าไฟล์มีอยู่หรือไม่
        if (file_exists($filePath)) {
            return Yii::$app->response->sendFile($filePath);
        } else {
            return 'File not found.';
        }
    }

    public function actionDownload($id)
    {

        // $this->Purchase1($id);
        // $this->Purchase2($id);
        // $this->Purchase3($id);
        // $this->Purchase4($id);
        // $this->Purchase5($id);
        // $this->Purchase6($id);
        // $this->Purchase7($id);
        // $this->Purchase8($id);
        // $this->Purchase9($id);
        // $this->Purchase10($id);
        // $this->Purchase11($id);
        // $this->Purchase12($id);

        $filename = 'purchase-'.$id.'.zip';
        $sourcePath = Yii::getAlias('@app/web/msword/results/purchase/'.$id.'/'); // โฟลเดอร์ที่จะบีบอัด
        $zipPath = Yii::getAlias('@app/web/downloads/'.$filename); // ไฟล์ ZIP ที่จะสร้าง

        // เรียกใช้ Component สำหรับสร้างไฟล์ ZIP
        if (Yii::$app->zip->createZip($sourcePath, $zipPath)) {
            return $this->redirect(['/purchase/document/download-zip', 'filename' => $filename]);
            // return $this->redirect('downloads/purchase-2.zip');
        } else {
            return 'Failed to create ZIP file.';
        }

    }
    //ขออนุมัติแต่งตั้ง กก. กำหนดรายละเอียด
    protected function Purchase1($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = $this->findOrderModel($id);
        $oldObj = $model->data_json;
        if(!isset($model->data_json['committee_detail_date']) || $model->data_json['committee_detail_date'] == "")
        {
            $setDate = [
                'committee_detail_date' =>   date('Y-m-d'),
            ];
            $model->data_json =  ArrayHelper::merge($oldObj, $model->data_json, $setDate);
            $model->save(false);
        }

        $this->CreateDir($model->id);
        $result_name = $model->id . '/ขออนุมัติแต่งตั้ง กก. กำหนดรายละเอียด.docx';
        $word_name = 'purchase_1.docx';

            @unlink(Yii::getAlias('@webroot') . '/msword/results/purchase/' . $result_name);
            $templateProcessor = new Processor(Yii::getAlias('@webroot') . '/msword/' . $word_name);  // เลือกไฟล์ template ที่เราสร้างไว้

            $templateProcessor->setValue('title', 'ขออนุมัติแต่งตั้ง กก. กำหนดรายละเอียด');
            $templateProcessor->setValue('org_name_full', $this->getInfo()['company_full']);
            $templateProcessor->setValue('doc_number',  $this->getInfo()['doc_number']);
            // $templateProcessor->setValue('date', isset($model->data_json['order_date']) ? (AppHelper::thainumDigit(Yii::$app->thaiFormatter->asDate($model->data_json['order_date'], 'medium'))) : '-');
            $templateProcessor->setValue('date', isset($model->data_json['committee_detail_date']) ? (Yii::$app->thaiFormatter->asDate($model->data_json['committee_detail_date'], 'long')) : '-');
            $templateProcessor->setValue('doc_title', 'ขออนุมัติแต่งตั้งคณะกรรมการกำหนดรายละเอียดคุณลักษณะเฉพาะ');
            $templateProcessor->setValue('org_name', $this->getInfo()['company_name']);
            $templateProcessor->setValue('suptype', (isset($model->data_json['product_type_name']) ? $model->data_json['product_type_name'] : '-'));
            $templateProcessor->setValue('budget_year', 'ปีงบประมาณ');
            $templateProcessor->setValue('budget_amount', number_format($model->SumPo(), 2));
            $templateProcessor->setValue('budget_letter', AppHelper::convertNumberToWords($model->SumPo(), 2));
            $templateProcessor->setValue('board', 'คณะกรรมการกำหนดรายละเอียด');
            $templateProcessor->setValue('emp_name', $model->getUserReq()['fullname']);
            $templateProcessor->setValue('emp_position', $model->getUserReq()['position_name']);
            $templateProcessor->setValue('leader_fullname', $this->getInfo()['leader_fullname']);
            $templateProcessor->setValue('leader_position', $this->getInfo()['leader_position']);
            $templateProcessor->setValue('director_name', $this->GetInfo()['director_fullname']);
            $templateProcessor->setValue('org_name', $this->GetInfo()['company_name']);

            $templateProcessor->cloneRow('emp_fullname', count($model->ListCommitteeDetail()));
            $i = 1;
            $num = 1;
            foreach ($model->ListCommitteeDetail() as $board) {
                $templateProcessor->setValue('num#' . $i, $num++);
                $templateProcessor->setValue('emp_fullname#' . $i, $board->data_json['emp_fullname']);
                $templateProcessor->setValue('com_position#' . $i, $board->data_json['emp_position']);
                $templateProcessor->setValue('position#' . $i, $board->data_json['committee_name']);
                $i++;
            }

            $templateProcessor->saveAs(Yii::getAlias('@webroot') . '/msword/results/purchase/' . $result_name);  // สั่งให้บันทึกข้อมูลลงไฟล์ใหม่

       
    }
//ขอความเห็นชอบและรายงานผล
    protected function Purchase2($id)
    {
        $model = $this->findOrderModel($id);
        $oldObj = $model->data_json;
        if(!isset($model->data_json['apporve_report_date']) || $model->data_json['apporve_report_date'] == "")
        {
            $setDate = [
                'apporve_report_date' =>  date('Y-m-d'),
            ];
            $model->data_json =  ArrayHelper::merge($oldObj, $model->data_json, $setDate);
            $model->save(false);
        }

        $this->CreateDir($model->id);
        $result_name = $model->id . '/ขอความเห็นชอบและรายงานผล.docx';
        $word_name = 'purchase_2.docx';

            $data = [
                'word_name' => $word_name,
                'result_name' => $result_name,
                'items' => [
                    'title' => 'ขอความเห็นชอบและรายงานผล',
                    'org_name_full' => $this->GetInfo()['company_full'],
                    'doc_number' =>  $this->getInfo()['doc_number'],
                    'date' => isset($model->data_json['apporve_report_date']) ? (Yii::$app->thaiFormatter->asDate($model->data_json['apporve_report_date'], 'long')) : '-',
                    'doc_title' => 'หัวข้ออนุมัติการแต่งตั้ง',
                    'org_name' => $this->GetInfo()['company_name'],
                    'director_name' => SiteHelper::viewDirector()['fullname'],  // ชื่อผู้บริหาร ผอ.
                    'director_position' => $this->GetInfo()['director_position'],
                    'province' => $this->GetInfo()['province'],
                ]
            ];
             $this->CreateFile($data);
    }

    // ขออนุมัติจัดซื้อจัดจ้าง
    protected function Purchase3($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = $this->findOrderModel($id);
        $this->CreateDir($model->id);
        $result_name = $model->id . '/ขออนุมัติจัดซื้อจัดจ้าง.docx';
        $word_name = 'purchase_3.docx';
        @unlink(Yii::getAlias('@webroot') . '/msword/results/purchase/' . $result_name);


        $templateProcessor = new Processor(Yii::getAlias('@webroot') . '/msword/' . $word_name);  // เลือกไฟล์ template ที่เราสร้างไว้
        $templateProcessor->setValue('title', 'ขออนุมัติจัดซื้อจัดจ้าง');
        $templateProcessor->setValue('director_fullname', SiteHelper::viewDirector()['fullname']);  // ชื่อผู้บริหาร ผอ.
        $templateProcessor->setValue('doc_number', $this->getInfo()['doc_number']);
        // $templateProcessor->setValue('date', isset($model->data_json['order_date']) ? (AppHelper::thainumDigit(Yii::$app->thaiFormatter->asDate($model->data_json['order_date'], 'medium'))) : '-');
        $templateProcessor->setValue('date', isset($model->data_json['pr_create_date']) ? (Yii::$app->thaiFormatter->asDate($model->data_json['pr_create_date'], 'long')) : '-');
        $templateProcessor->setValue('org_name', $this->GetInfo()['company_name']);
        $templateProcessor->setValue('department', $model->getUserReq()['department']);
        $templateProcessor->setValue('asset_detail', 'รายละเอียดวัสดุ');
        $templateProcessor->setValue('comment', $model->data_json['comment']);
        $templateProcessor->setValue('emp_name', $model->viewLeaderUser()['fullname']);
        $templateProcessor->setValue('emp_position', $model->viewLeaderUser()['position_name']);
        $templateProcessor->setValue('director_name', $this->GetInfo()['director_name']);
        $templateProcessor->setValue('director_position', $this->GetInfo()['director_position']);
        $templateProcessor->setValue('price_amount', number_format($model->sumPo(), 2) . ' บาท');

        $templateProcessor->cloneRow('item_name', count($model->ListOrderItems()));
        $i = 1;
        $num = 1;
        foreach ($model->ListOrderItems() as $item) {
            $templateProcessor->setValue('n#' . $i, $num++);
            $templateProcessor->setValue('item_name#' . $i, $item->product->title);
            $templateProcessor->setValue('qty#' . $i, $item->qty);
            $templateProcessor->setValue('unit#' . $i, isset($item->product->data_json['unit']) ? $item->product->data_json['unit'] : '-');
            $i++;
        }

        $templateProcessor->saveAs(Yii::getAlias('@webroot') . '/msword/results/purchase/' . $result_name);  // สั่งให้บันทึกข้อมูลลงไฟล์ใหม่
    }
    // รายละเอียดขอบเขตงานหรือรายละเอียดคุณลักษณะของพัสดุที่จะซื้อหรือจ้าง
    protected function Purchase4($id)
    {
        $model = $this->findOrderModel($id);
        $this->CreateDir($model->id);
        $result_name = $model->id . '/ขอบเขตงานหรือรายละเอียดคุณลักษณะ.docx';
        $word_name = 'purchase_4.docx';

        @unlink(Yii::getAlias('@webroot') . '/msword/results/purchase/' . $result_name);
        $templateProcessor = new Processor(Yii::getAlias('@webroot') . '/msword/' . $word_name);  // เลือกไฟล์ template ที่เราสร้างไว้
        $templateProcessor->setValue('po_number', $model->po_number);
        $templateProcessor->setValue('po_date', Yii::$app->thaiFormatter->asDate($model->data_json['po_date'], 'long'));
        $templateProcessor->setValue('number', 'ลำดับ');
        $templateProcessor->setValue('amount', 'จำนวน');
        $templateProcessor->cloneRow('item_name', count($model->ListOrderItems()));
        $i = 1;
        $num = 1;
        foreach ($model->ListOrderItems() as $item) {
            $templateProcessor->setValue('n#' . $i, $num++);
            $templateProcessor->setValue('item_name#' . $i, $item->product->title);
            $templateProcessor->setValue('qty#' . $i, $item->qty);
            $templateProcessor->setValue('price#' . $i, number_format($item->qty, 2));
            $templateProcessor->setValue('sum#' . $i, number_format(($item->qty * $item->price), 2));
            $templateProcessor->setValue('unit#' . $i, isset($item->product->data_json['unit']) ? $item->product->data_json['unit'] : '-');
            $i++;
        }
        $templateProcessor->saveAs(Yii::getAlias('@webroot') . '/msword/results/purchase/' . $result_name);  // สั่งให้บันทึกข้อมูลลงไฟล์ใหม่
    }

    protected function Purchase5($id)
    {
        $model = $this->findOrderModel($id);
        $oldObj = $model->data_json;
        if(!isset($model->data_json['purchase_report_date']) || $model->data_json['purchase_report_date'] == "")
        {
            $setDate = [
                'purchase_report_date' =>   date('Y-m-d'),
            ];
            $model->data_json =  ArrayHelper::merge($oldObj, $model->data_json, $setDate);
            $model->save(false);
        }

        $this->CreateDir($model->id);
        $result_name = $model->id . '/รายงานขอซื้อขอจ้าง.docx';
        $word_name = 'purchase_5.docx';
            @unlink(Yii::getAlias('@webroot') . '/msword/results/purchase/' . $result_name);
            $templateProcessor = new Processor(Yii::getAlias('@webroot') . '/msword/' . $word_name);  // เลือกไฟล์ template ที่เราสร้างไว้
            $templateProcessor->setValue('org_name_full', $this->GetInfo()['company_full']);
            $templateProcessor->setValue('doc_number', $this->getInfo()['doc_number']);
            $templateProcessor->setValue('date', isset($model->data_json['purchase_report_date']) ? (Yii::$app->thaiFormatter->asDate($model->data_json['purchase_report_date'], 'long')) : '-');
            $templateProcessor->setValue('org_name', $this->GetInfo()['company_name']);
            $templateProcessor->setValue('budget_type', $model->data_json['pq_budget_type_name']);
            $templateProcessor->setValue('order_type', $model->data_json['order_type_name']);
            $templateProcessor->setValue('sup_detail', 'รายละเอียดพัสดุ');
            $templateProcessor->setValue('detail', $model->data_json['comment']);
            $templateProcessor->setValue('amount', 'จำนวน');
            $templateProcessor->setValue('price', number_format($model->sumPo(), 2));
            $templateProcessor->setValue('price_character', AppHelper::convertNumberToWords($model->SumPo(), 2));
            $templateProcessor->setValue('sup_detailfull', 'รายละเอียดของพัสดุ');
            $templateProcessor->setValue('board', 'คณะกรรมการตรวจรับพัสดุ');
            $templateProcessor->setValue('me_name', $model->getMe()['fullname']);
            $templateProcessor->setValue('me_position', $model->getMe()['position']);
            $templateProcessor->setValue('emphead_name', $this->getInfo()['leader_fullname']);
            $templateProcessor->setValue('emphead_position', $this->getInfo()['leader_position']);
            $templateProcessor->setValue('director_name', $this->GetInfo()['director_fullname']);

            $templateProcessor->cloneRow('emp_fullname', count($model->ListCommittee()));
            $i = 1;
            $num = 1;
            foreach ($model->ListCommitteeDetail() as $board) {
                $templateProcessor->setValue('num#' . $i, $num++);
                $templateProcessor->setValue('emp_fullname#' . $i, $board->data_json['emp_fullname']);
                $templateProcessor->setValue('com_position#' . $i, $board->data_json['emp_position']);
                $templateProcessor->setValue('position#' . $i, $board->data_json['committee_name']);
                $i++;
            }

            $templateProcessor->saveAs(Yii::getAlias('@webroot') . '/msword/results/purchase/' . $result_name);  // สั่งให้บันทึกข้อมูลลงไฟล์ใหม่
 
    }

    protected function Purchase6($id)
    {
        $model = $this->findOrderModel($id);
        $oldObj = $model->data_json;
        if(!isset($model->data_json['purchase_report_order_date']) || $model->data_json['purchase_report_order_date'] == "")
        {
            $setDate = [
                'purchase_report_order_date' =>   date('Y-m-d'),
            ];
            $model->data_json =  ArrayHelper::merge($oldObj, $model->data_json, $setDate);
            $model->save(false);
        }
        $this->CreateDir($model->id);
        $result_name = $model->id . '/รายงานผลการตรวจรับพัสดุ.docx';
        $word_name = 'purchase_6.docx';

            $data = [
                'word_name' => $word_name,
                'result_name' => $result_name,
                'items' => [
                    'org_name' => $this->GetInfo()['company_name'],
                    'org_name_full' => $this->GetInfo()['company_full'],
                    'doc_number' => $this->getInfo()['doc_number'],
                    'date' => isset($model->data_json['purchase_report_order_date']) ? (Yii::$app->thaiFormatter->asDate($model->data_json['purchase_report_order_date'], 'long')) : '-',
                    'sup_detail' => 'รายละเอียดพัสดุ',
                    'amount' => $model->SumQty(),
                    'price' => number_format($model->calculateVAT()['priceAfterVAT'], 2), //'ราคา',
                    'price_character' => AppHelper::convertNumberToWords($model->calculateVAT()['priceAfterVAT'], 2), //'ราคาตัวอักษร',
                    'bill_number' => $model->po_number, //'เลขใบสั่งซื้อ',
                    'bill_datebegin' => Yii::$app->thaiFormatter->asDate($model->data_json['po_date'], 'long'), //'ใบสั่งซื้อลงวันที่เริ่ม',
                    'bill_dateend' => Yii::$app->thaiFormatter->asDate($model->data_json['po_expire_date'], 'long'),
                    'budget_type' => $model->data_json['pq_budget_type'], //'ประเภทเงินงบ',
                    'gr_date' => Yii::$app->thaiFormatter->asDate($model->data_json['gr_date'], 'long') . ' เวลา ' . explode(" ", $model->data_json['gr_date'])[1],
                    'me_name' => $model->getMe()['fullname'],
                    'me_position' => $model->getMe()['position'],
                    'emphead_name' => $this->getInfo()['leader_fullname'],
                    'emphead_position' => $this->getInfo()['leader_position'],
                    'director_name' => SiteHelper::viewDirector()['fullname'], //'ผู้อำนวยการโรงพยาบาล',
                    'province' => $this->GetInfo()['province']
                ]
            ];
             $this->CreateFile($data);
       
    }
    protected function Purchase7($id)
    {
        $model = $this->findOrderModel($id);
        $oldObj = $model->data_json;
        if(!isset($model->data_json['report_winner_date']) || $model->data_json['report_winner_date'] == "")
        {
            $setDate = [
                'report_winner_date' =>   date('Y-m-d'),
            ];
            $model->data_json =  ArrayHelper::merge($oldObj, $model->data_json, $setDate);
            $model->save(false);
        }
        $this->CreateDir($model->id);
        $result_name = $model->id . '/ประกาศผู้ชนะการเสนอราคา.docx';
        $word_name = 'purchase_7.docx';

            $data = [
                'word_name' => $word_name,
                'result_name' => $result_name,
                'items' => [
                    'sup_type' => $model->data_json['product_type_name'],
                    'org_name' => $this->GetInfo()['company_name'],
                    'project_name' =>  $model->data_json['pq_project_name'],
                    'budget_name' => $model->data_json['pq_budget_type_name'],
                    'vendor' => $model->vendor_name,
                    'price' => number_format($model->sumPo(), 2),
                    'price_character' => AppHelper::convertNumberToWords($model->SumPo(), 2),
                    'director_name' => SiteHelper::viewDirector()['fullname'], //'ผู้อำนวยการโรงพยาบาล',
                    'date' => isset($model->data_json['report_winner_date']) ? (Yii::$app->thaiFormatter->asDate($model->data_json['report_winner_date'], 'long')) : '-',
                ]
            ];
             $this->CreateFile($data);
      
    }

    protected function Purchase8($id)
    {
        // Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findOrderModel($id);
        $this->CreateDir($model->id);
        $result_name = $model->id . '/ใบสั่งซื้อสั่งจ้าง.docx';
        $word_name = 'purchase_8.docx';

        @unlink(Yii::getAlias('@webroot') . '/msword/results/purchase/' . $result_name);
        $templateProcessor = new Processor(Yii::getAlias('@webroot') . '/msword/' . $word_name);  // เลือกไฟล์ template ที่เราสร้างไว้
        $templateProcessor->setValue('title', 'ใบสั่งซื้อสั่งจ้าง');
        $templateProcessor->setValue(
            'phone',
            $this->GetInfo()['phone'],
        );

        $templateProcessor->setValue('doc_number',  $this->getInfo()['doc_number']); //ลย.0033.301/1578
        $templateProcessor->setValue('qr_number', isset($model->data_json['qr_number']) ? $model->data_json['qr_number'] : '');
        $templateProcessor->setValue('po_date', Yii::$app->thaiFormatter->asDate($model->data_json['po_date'], 'long'));
        $templateProcessor->setValue('vendor_name', $model->vendor_name);
        $templateProcessor->setValue('vendor_address', $model->vendor_address);
        $templateProcessor->setValue('credit', $model->data_json['credit_days']);
        $templateProcessor->setValue('deliveryDay', $model->deliveryDay());
        $templateProcessor->setValue('project_id', isset($model->data_json['pq_project_id']) ? $model->data_json['pq_project_id'] : ''); //เลขที่โครงการ
        $templateProcessor->setValue('delivery', Yii::$app->thaiFormatter->asDate($model->data_json['delivery_date'], 'long'));
        $templateProcessor->setValue('recipient', $model->data_json['po_recipient']);
        $templateProcessor->setValue('recipient_position', $model->data_json['po_recipient_position']);
        $templateProcessor->setValue('org_name', $this->GetInfo()['company_name']);
        $templateProcessor->setValue('province', $this->GetInfo()['province']);
        $templateProcessor->setValue('director_fullname', SiteHelper::viewDirector()['fullname']);  // ชื่อผู้บริหาร ผอ.

        $templateProcessor->setValue('address', $this->GetInfo()['address']);
        $templateProcessor->setValue('vendor_phone', $model->vendor_phone);
        $templateProcessor->setValue('tax', $model->vendor_id);
        $templateProcessor->setValue('account_name', $model->account_name);
        $templateProcessor->setValue('account_number',  $model->account_number);
        $templateProcessor->setValue('discount',  number_format($model->calculateVAT()['priceBeforeDiscount'], 2));
        $templateProcessor->setValue('vat',  number_format($model->calculateVAT()['vatAmount'], 2));
        $templateProcessor->setValue('total_price',  number_format($model->calculateVAT()['priceAfterVAT'], 2));
        $templateProcessor->setValue('total_price_text', AppHelper::convertNumberToWords($model->calculateVAT()['priceAfterVAT'], 2));
        $templateProcessor->cloneRow('asset_item', count($model->ListOrderItems()));
        $i = 1;
        $num = 1;
        foreach ($model->ListOrderItems() as $item) {
            $templateProcessor->setValue('n#' . $i, $num++);
            $templateProcessor->setValue('asset_item#' . $i, $item->product->title);
            $templateProcessor->setValue('qty#' . $i, $item->qty);
            $templateProcessor->setValue('price#' . $i, number_format($item->price, 2));
            $templateProcessor->setValue('sum#' . $i, number_format(($item->qty * $item->price), 2));
            $templateProcessor->setValue('unit#' . $i, isset($item->product->data_json['unit']) ? $item->product->data_json['unit'] : '-');
            $i++;
        }
        $templateProcessor->saveAs(Yii::getAlias('@webroot') . '/msword/results/purchase/' . $result_name);  // สั่งให้บันทึกข้อมูลลงไฟล์ใหม่
    }

    //ใบตรวจรับสั่งซื้อสั่งจ้าง
    protected function Purchase9($id)
    {
        $model = $this->findOrderModel($id);
        $this->CreateDir($model->id);
        $result_name = $model->id . '/ใบตรวจรับสั่งซื้อสั่งจ้าง.docx';
        $word_name = 'purchase_9.docx';

        @unlink(Yii::getAlias('@webroot') . '/msword/results/purchase/' . $result_name);
        $templateProcessor = new Processor(Yii::getAlias('@webroot') . '/msword/' . $word_name);  // เลือกไฟล์ template ที่เราสร้างไว้
        $templateProcessor->setValue('title', 'ใบตรวจรับการจัดซื้อ/จัดจ้าง');
        $templateProcessor->setValue('date', isset($model->data_json['gr_date']) ? Yii::$app->thaiFormatter->asDate($model->data_json['gr_date'], 'long') . count($model->ListCommittee()) : '-');
        $templateProcessor->setValue('org_name', $this->GetInfo()['company_name']);
        $templateProcessor->setValue('po_number', $model->po_number);
        $templateProcessor->setValue('order_item_checker', isset($model->data_json['order_item_checker']) ? $model->data_json['order_item_checker'] : '-');
        $templateProcessor->setValue('fine', isset($model->data_json['fine']) ? $model->data_json['fine'] : '-');
        $templateProcessor->setValue('vendor_name', $model->vendor_name);
        $templateProcessor->setValue('po_date', Yii::$app->thaiFormatter->asDate($model->data_json['po_date'], 'long'));
        $templateProcessor->setValue('province', $this->GetInfo()['province']);
        $templateProcessor->setValue('asset_type', $model->assetType->title);
        $templateProcessor->setValue('amonth', $model->SumQty());
        $templateProcessor->setValue('budget_type', $model->data_json['pq_budget_type_name']);
        $templateProcessor->setValue('total_price', number_format($model->calculateVAT()['priceAfterVAT'], 2));
        $templateProcessor->setValue('total_price_text', AppHelper::convertNumberToWords($model->calculateVAT()['priceAfterVAT'], 2));
        $templateProcessor->cloneRow('item', count($model->ListCommittee()));
        $i = 1;
        $num = 1;
        foreach ($model->ListCommittee() as $item) {
            $templateProcessor->setValue('n#' . $i, $num++);
            $templateProcessor->setValue('item#' . $i, $item->data_json['committee_name']);
            $templateProcessor->setValue('item_name#' . $i, $item->ShowCommittee()['fullname']);
            $i++;
        }
        $templateProcessor->saveAs(Yii::getAlias('@webroot') . '/msword/results/purchase/' . $result_name);  // สั่งให้บันทึกข้อมูลลงไฟล์ใหม่
    }

    protected function Purchase10($id)
    {
        $model = $this->findOrderModel($id);
        $oldObj = $model->data_json;
        if(!isset($model->data_json['report_checker_date']) || $model->data_json['report_checker_date'] == "")
        {
            $setDate = [
                'report_checker_date' =>   date('Y-m-d'),
            ];
            $model->data_json =  ArrayHelper::merge($oldObj, $model->data_json, $setDate);
            $model->save(false);
        }
        $this->CreateDir($model->id);
        $result_name = $model->id . '/รายงานผลการตรวจรับ.docx';
        $word_name = 'purchase_10.docx';
            $data = [
                'word_name' => $word_name,
                'result_name' => $result_name,
                'items' => [
                    'title' => 'รายงานผลการตรวจรับ',
                    'doc_number' => $this->getInfo()['doc_number'],
                    'date' => Yii::$app->thaiFormatter->asDate($model->data_json['report_checker_date'], 'long'),
                    'org_name' => $this->GetInfo()['company_name'],
                    'org_name_full' => $this->GetInfo()['company_full'],
                    'order_type_name' => $model->data_json['order_type_name'],
                    'province' => $this->GetInfo()['province'],
                    'gr_date' => Yii::$app->thaiFormatter->asDate($model->data_json['gr_date'], 'long') . ' เวลา ' . explode(" ", $model->data_json['gr_date'])[1],
                    'po_date' => Yii::$app->thaiFormatter->asDate($model->data_json['po_date'], 'long'),
                    'po_expire' => Yii::$app->thaiFormatter->asDate($model->data_json['po_expire_date'], 'long'),
                    'qty' => $model->SumQty(),
                    'price' =>  number_format($model->calculateVAT()['priceAfterVAT'], 2),
                    'price_text' => AppHelper::convertNumberToWords($model->calculateVAT()['priceAfterVAT'], 2),
                    'po_number' => $model->po_number,
                    'director_name' => SiteHelper::viewDirector()['fullname'], // ชื่อผู้บริหาร ผอ.
                    'me' => $model->getMe()['fullname'],
                    'me_position' => $model->getMe()['position'],
                    'leader' => $model->getMe()['leader']['leader1_fullname'],
                    'leader_position' => $model->getMe()['leader']['leader1_position']
                ]
            ];
             $this->CreateFile($data);
       
    }

    protected function Purchase11($id)
    {

        $model = $this->findOrderModel($id);
        $this->CreateDir($model->id);
        $result_name = $model->id . '/แบบแสดงความบริสุทธิ์ใจ.docx';
        $word_name = 'purchase_11.docx';
        @unlink(Yii::getAlias('@webroot') . '/msword/results/purchase/' . $result_name);
        $templateProcessor = new Processor(Yii::getAlias('@webroot') . '/msword/' . $word_name);  // เลือกไฟล์ template ที่เราสร้างไว้

        $templateProcessor->setValue('title', 'แบบแสดงความบริสุทธิ์ใจ');
        $templateProcessor->setValue('me_name', $model->getMe()['fullname']);
        $templateProcessor->setValue('me_position', $model->getMe()['position']);
        $templateProcessor->setValue('emphead_name', $this->getInfo()['leader_fullname']);
        $templateProcessor->setValue('emphead_position', $this->getInfo()['leader_position']);
        $templateProcessor->cloneRow('emp_fullname', count($model->ListCommittee()));
        $templateProcessor->cloneRow('emp_fullname2', count($model->ListCommittee()));
        $i = 1;
        $num = 1;
        foreach ($model->ListCommittee() as $board) {
            $templateProcessor->setValue('num#' . $i, $num++);
            $templateProcessor->setValue('emp_fullname#' . $i, $board->data_json['emp_fullname']);
            $templateProcessor->setValue('com_position#' . $i, $board->data_json['emp_position']);
            $templateProcessor->setValue('position#' . $i, $board->data_json['committee_name']);

            $templateProcessor->setValue('num2#' . $i, $num++);
            $templateProcessor->setValue('emp_fullname2#' . $i, $board->data_json['emp_fullname']);
            $templateProcessor->setValue('com_position2#' . $i, $board->data_json['emp_position']);
            $templateProcessor->setValue('position2#' . $i, $board->data_json['committee_name']);
            $i++;
        }


        $templateProcessor->saveAs(Yii::getAlias('@webroot') . '/msword/results/purchase/' . $result_name);  // สั่งให้บันทึกข้อมูลลงไฟล์ใหม่
    }

    protected function Purchase12($id)
    {
        $model = $this->findOrderModel($id);
        $oldObj = $model->data_json;
        if(!isset($model->data_json['request_pay_date']) || $model->data_json['request_pay_date'] == "")
        {
            $setDate = [
                'request_pay_date' =>   date('Y-m-d'),
            ];
            $model->data_json =  ArrayHelper::merge($oldObj, $model->data_json, $setDate);
            $model->save(false);
        }
        $this->CreateDir($model->id);
        $result_name = $model->id . '/ขออนุมัติจ่ายเงินบำรุง.docx';
        $word_name = 'purchase_12.docx';
            $data = [
                'word_name' => $word_name,
                'result_name' => $result_name,
                'items' => [
                    'title' => 'ขออนุมัติจ่ายเงินบำรุง',
                    'doc_number' => $this->getInfo()['doc_number'],
                    'date' => isset($model->data_json['request_pay_date']) ? Yii::$app->thaiFormatter->asDate($model->data_json['request_pay_date'], 'long') : '',
                    'org_name' => $this->GetInfo()['company_name'],
                    'org_name_full' => $this->GetInfo()['company_full'],
                    'order_type_name' => $model->data_json['order_type_name'] . '(' . $model->data_json['pq_budget_type_name'] . ')',
                    'budget_type' => $model->data_json['pq_budget_type_name'],
                    'province' => $this->GetInfo()['province'],
                    'price' =>  number_format($model->calculateVAT()['priceAfterVAT'], 2),
                    'price_text' => AppHelper::convertNumberToWords($model->calculateVAT()['priceAfterVAT'], 2),
                    'director_name' => SiteHelper::viewDirector()['fullname'], // ชื่อผู้บริหาร ผอ.
                    'vendor' => $model->vendor_name,
                    'me' => $model->getMe()['fullname'],
                    'me_position' => $model->getMe()['position'],
                    'leader' => $this->getInfo()['leader_fullname'],
                    'leader_position' => $this->getInfo()['leader_position']
                ]
            ];
             $this->CreateFile($data);
       
    }
    
    // function สร้าง Word
    public function CreateFile($data)
    {
        $result_name = $data['result_name'];
        @unlink(Yii::getAlias('@webroot') . '/msword/results/purchase/' . $result_name);
        $templateProcessor = new Processor(Yii::getAlias('@webroot') . '/msword/' . $data['word_name']);  // เลือกไฟล์ template ที่เราสร้างไว้
        foreach ($data['items'] as $key => $value) {
            $templateProcessor->setValue($key, $value);
        }

        $templateProcessor->saveAs(Yii::getAlias('@webroot') . '/msword/results/purchase/' . $result_name);  // สั่งให้บันทึกข้อมูลลงไฟล์ใหม่
    }

    public static function CreateDir($folderName)
    {


        $downloadPath = Yii::getAlias('@app') . '/web/downloads';
        if ($downloadPath != null) {
            BaseFileHelper::createDirectory($downloadPath ,0777);
        }

        if ($folderName != null) {
            $basePath = Yii::getAlias('@app') . '/web/msword/results/purchase/';
            BaseFileHelper::createDirectory($basePath . $folderName, 0777);
        }
        return;
    }
}
