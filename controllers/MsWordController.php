<?php

namespace app\controllers;

use app\components\Processor;
use app\components\SiteHelper;
use app\modules\am\components\AssetHelper;
use app\modules\am\models\Asset;
use yii;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\Response;
use PhpOffice\PhpWord\Settings;


class MsWordController extends \yii\web\Controller

{
    public function actionIndex()
    {
        return $this->render('index');
    }


    protected function findModel($id)
    {
        if (($model = Asset::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    //ทะเบียนทรัพย์สิน
    public function actionAsset()
    {
        \Yii::$app->response->format = yii\web\Response::FORMAT_RAW;
        \Yii::$app->response->headers->add('content-type', 'text/html');

        $id = $this->request->get('id');
        $user = Yii::$app->user->id;
        
        $comanyName = SiteHelper::getInfo()['company_name'] != '' ? (' | ' . SiteHelper::getInfo()['company_name']) : '';
        Settings::setTempDir(Yii::getAlias('@webroot').'/msword/temp/'); //กำหนด folder temp สำหรับ windows server ที่ permission denied temp (อย่ายลืมสร้างใน project ล่ะ)
        $templateProcessor = new Processor(Yii::getAlias('@webroot') . '/msword/asset.docx'); //เลือกไฟล์ template ที่เราสร้างไว้
        
        //ถ้ามี ID ส่งมาให้แสดงจาก Database
        // if (isset($id)) {
            $model = $this->findModel($id);
            $number = $this->request->get('number');
            $vendor = $model->getVendor();
            $venrorAddress = isset($vendor->data_json['address']) ? $vendor->data_json['address'] : '-';
            $venrorPhone = isset($vendor->data_json['phone']) ? $vendor->data_json['phone'] : '-';
            $templateProcessor->setValue('title', 'ทะเบียนทรัพย์สิน');
            $templateProcessor->setValue('org_name', $model->department_name);
            $templateProcessor->setValue('department', $comanyName);
            $templateProcessor->setValue('asset_type', $model->AssetTypeName()); // ประเภท
            $templateProcessor->setValue('asset_fsn', $model->code); // หมายเลขครุภัณฑ์
            $templateProcessor->setValue('feature', $model->asset_option); //ลักษณะ/คุณสมบัติ
            $templateProcessor->setValue('design', 'รุ่น/แบบ');
            $templateProcessor->setValue('asset_add', $model->department_name); //สถานที่ตั้ง/หน่วยงานที่รับผิดชอบ
            $templateProcessor->setValue('vendor', $model->vendor_name); //ชื่อผู้ขาย/ผู้รับจ้าง/ผู้บริจาค
            $templateProcessor->setValue('vendor_add', $venrorAddress); //ที่อยู่ของผู้ขาย
            $templateProcessor->setValue('vendor_tel', $venrorPhone); //หมายเลขโทรศัพท์ของผู้ขาย
            $templateProcessor->setValue('budget_type', $model->budget_type); //ประเภทเงิน
            $templateProcessor->setValue('method', $model->method_get); //วิธีการได้มา
            
            $datas = AssetHelper::Depreciation($model->id, $number);
            
            $templateProcessor->cloneRow('price', count($datas));
            // $templateProcessor->cloneRow('price', 10);
            $i = 1;
            foreach ($datas as $data) {
                $templateProcessor->setValue('recive_date#' . $i, Yii::$app->thaiFormatter->asDate($data['date'], 'medium')); //วันที่รับเข้า
                $templateProcessor->setValue('doc_number#' . $i, ''); //เลขที่เอกสารแสดงการได้มาของทรัพย์สิน
                $templateProcessor->setValue('asset_name#' . $i, $model->AssetitemName()); //ชื่อหรือชนิดของทรัพย์สิน
                $templateProcessor->setValue('amount#' . $i, ''); //จำนวน
                $templateProcessor->setValue('price_unit#' . $i, number_format($data['price'], 2)); //จำนวนเงินที่แสดงถึงราคาต่อหน่วย
                $templateProcessor->setValue('price#' . $i, number_format($data['price'], 2)); //จำนวนเงินรวมของทรัพย์
                $templateProcessor->setValue('asset_life#' . $i, $data['service_life']); //อายุการใช้งาน
                $templateProcessor->setValue('deprate#' . $i, $data['depreciation']); //ระบุอัตราค่าเสื่อมราคาของทรัพย์สิน
                $templateProcessor->setValue('deprate_year#' . $i, $model->price / $model->data_json['service_life']); //ค่าเสื่อมราคาประจำปี
                $templateProcessor->setValue('accdep#' . $i, $data['total_price2']); //จำนวนเงินค่าเสื่อมราคาที่สะสม
                $templateProcessor->setValue('pro_value#' . $i, $data['total']); //มูลค่าสุทธิ
                $templateProcessor->setValue('remart#' . $i, ''); //หมายเหตุ
                $i++;
            }
            // } else {
                //     // ถ้าไม่มี ID ส่งมาให้แสดงข้อมูลตัวอย่าง
                //     $templateProcessor->setValue('title', 'ทะเบียนคุมทรัพย์สิน');
                //     $templateProcessor->setValue('org_name', '');
                //     $templateProcessor->setValue('department', $comanyName);
                //     $templateProcessor->setValue('asset_type', 'ประเภทของทรัพย์สิน'); // ประเภท
                //     $templateProcessor->setValue('asset_fsn', 'หมายเลขครุภัณฑ์'); // หมายเลขครุภัณฑ์
                //     $templateProcessor->setValue('feature', 'ลักษณะ/คุณสมบัติ'); //ลักษณะ/คุณสมบัติ
                //     $templateProcessor->setValue('design', 'รุ่น/แบบ');
                //     $templateProcessor->setValue('asset_add', 'สถานที่ตั้ง/หน่วยงานที่รับผิดชอบ'); //สถานที่ตั้ง/หน่วยงานที่รับผิดชอบ
                //     $templateProcessor->setValue('vendor', 'สถานที่ตั้ง/หน่วยงานที่รับผิดชอบ'); //c/ผู้รับจ้าง/ผู้บริจาค
                //     $templateProcessor->setValue('vendor_add', 'ที่อยู่'); //ที่อยู่ของผู้ขาย
                //     $templateProcessor->setValue('vendor_tel', 'หมายเลขโทรศัพท์'); //หมายเลขโทรศัพท์ของผู้ขาย
                //     $templateProcessor->setValue('budget_type', 'ประเภทเงิน'); //ประเภทเงิน
                //     $templateProcessor->setValue('method', 'วิธีการได้มา'); //วิธีการได้มา
                // }
                
                $filename = 'asset_result-'.$number.'.docx';
                
                $templateProcessor->saveAs(Yii::getAlias('@webroot') . '/msword/results/'.$filename); //สั่งให้บันทึกข้อมูลลงไฟล์ใหม่
                
                if ($this->request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return [
                    'title' =>Html::a('<i class="fa-solid fa-cloud-arrow-down"></i> ดาวน์โหลดเอกสาร', Url::to(Yii::getAlias('@web') . '/msword/results/'.$filename), ['class' => 'btn btn-primary text-center mb-3','target' => '_blank','onclick' =>'return closeModal()']),
                    'content' => $this->renderAjax('word', ['filename' => $filename]),
                ];
            } else {
                echo '<p>';
                echo Html::a('ดาวน์โหลดเอกสาร', Url::to(Yii::getAlias('@web') . '/msword/results/asset_result.docx'), ['class' => 'btn btn-info']); //สร้าง link download
                echo '</p>';
                // echo '<iframe src="https://view.officeapps.live.com/op/embed.aspx?src='.Url::to(Yii::getAlias('@web').'/msword/temp/asset_result.docx', true).'&embedded=true"  style="position: absolute;width:99%; height: 90%;border: none;"></iframe>';
                echo '<iframe src="https://docs.google.com/viewerng/viewer?url=' . Url::to(Yii::getAlias('@web') . '/msword/temp/asset_result.docx', true) . '&embedded=true"  style="position: absolute;width:100%; height: 100%;border: none;"></iframe>';
            }
        
    }

    public function actionPurchase_1()
    {
        $id = $this->request->get('id');
        $user = Yii::$app->user->id;
        $filename = 'ขออนุมัติแต่งตั้ง กก. กำหนดรายละเอียด';

        $templateProcessor = new Processor(Yii::getAlias('@webroot') . '/msword/purchase_1.docx'); //เลือกไฟล์ template ที่เราสร้างไว้
        $templateProcessor->setValue('title', 'ขออนุมัติแต่งตั้ง กก. กำหนดรายละเอียด');
        $templateProcessor->saveAs(Yii::getAlias('@webroot') . '/msword/temp/' . $filename . '.docx'); //สั่งให้บันทึกข้อมูลลงไฟล์ใหม่

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'org_name_full' => 'รายละเอียดโรงพยาบาล',
                'doc_number' => 'เลขที่เอกสาร',
                'date' => 'วันที่',
                'doc_title' => 'ขออนุมัติแต่งตั้งคณะกรรมการกำหนดรายละเอียดคุณลักษณะเฉพาะ',
                'org_name' => 'ชื่อโรงพยาบาล',
                'suptype' => 'ประเภททรัพย์สิน',
                'budget_year' => 'ปีงบประมาณ',
                'budget_amount' => 'วงเงินงบประมาณ',
                'budget_letter' => 'วงเงินงบประมาณเป็นตัวอักษร',
                'board' => 'คณะกรรมการกำหนดรายละเอียด',
                'emp_name' => 'เจ้าหน้าที่พนักงาน',
                'emp_position' => 'ตำแหน่งเจ้าหน้าที่',
                'emphead_name' => 'หัวหน้าเจ้าหน้าที่',
                'emphead_position' => 'ตำแหน่งหัวหน้าเจ้าหน้าที่',
                'director_name' => 'ผู้อำนวยการโรงพยาบาล',
                'org_name' => 'ชื่อโรงพยาบาล',
                'content' => $this->renderAjax('word', ['filename' => $filename]),
            ];
        } else {
            return $this->render('word', ['filename' => $filename]);
        }
    }

    public function actionPurchase_2()
    {
        $id = $this->request->get('id');
        $user = Yii::$app->user->id;
        $filename = 'ขอความเห็นชอบและรายงานผล';

        $templateProcessor = new Processor(Yii::getAlias('@webroot') . '/msword/purchase_2.docx'); //เลือกไฟล์ template ที่เราสร้างไว้
        $templateProcessor->setValue('title', 'ขอความเห็นชอบและรายงานผล');
        $templateProcessor->saveAs(Yii::getAlias('@webroot') . '/msword/temp/' . $filename . '.docx'); //สั่งให้บันทึกข้อมูลลงไฟล์ใหม่

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'org_name_full' => 'รายละเอียดชื่อโรงพยาบาลเต็ม',
                'doc_number' => 'เลขหนังสือ',
                'date' => 'วันที่',
                'doc_title' => 'หัวข้ออนุมัติการแต่งตั้ง',
                'org_name' => 'ชื่อโรงพยาบาล',
                'director_name' => 'ชื่อผู้อำนวยการ',
                'director_nameposition' => 'ตำแหน่งผู้อำนวยการ',
                'povice' => 'จังหวัด',
                'content' => $this->renderAjax('word', ['filename' => $filename]),
            ];
        } else {
            return $this->render('word', ['filename' => $filename]);
        }
    }

    public function actionPurchase_3()
    {
        $id = $this->request->get('id');
        $user = Yii::$app->user->id;
        $filename = 'ขออนุมัติจัดซื้อจัดจ้าง';

        $templateProcessor = new Processor(Yii::getAlias('@webroot') . '/msword/purchase_3.docx'); //เลือกไฟล์ template ที่เราสร้างไว้
        $templateProcessor->setValue('title', 'ขออนุมัติจัดซื้อจัดจ้าง');
        $templateProcessor->saveAs(Yii::getAlias('@webroot') . '/msword/temp/' . $filename . '.docx'); //สั่งให้บันทึกข้อมูลลงไฟล์ใหม่

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'org_name_full' => 'รายละเอียดชื่อโรงพยาบาลเต็ม',
                'doc_number' => 'เลขหนังสือ',
                'date' => 'เลขหนังสือ',
                'org_name' => 'ชื่อโรงพาบาล',
                'department' => 'ฝ่ายหน่วยงาน',
                'asset_detail' => 'รายละเอียดวัสดุ',
                'remark' => 'เหตุผล',
                'emp_name' => 'ผู้อนุมัติ',
                'emp_position' => 'ตำแหน่งผู้อนุมัติ',
                'director_name' => 'ผู้อำนวยการ',
                'director_position' => 'ตำแหน่งผู้อำนวยการ',
                'content' => $this->renderAjax('word', ['filename' => $filename]),
            ];
        } else {
            return $this->render('word', ['filename' => $filename]);
        }
    }

    public function actionPurchase_4()
    {
        $id = $this->request->get('id');
        $user = Yii::$app->user->id;
        $filename = 'รายการคุณลักษณะพัสดุ';

        $templateProcessor = new Processor(Yii::getAlias('@webroot') . '/msword/purchase_4.docx'); //เลือกไฟล์ template ที่เราสร้างไว้
        $templateProcessor->setValue('title', 'รายการคุณลักษณะพัสดุ');
        $templateProcessor->saveAs(Yii::getAlias('@webroot') . '/msword/temp/' . $filename . '.docx'); //สั่งให้บันทึกข้อมูลลงไฟล์ใหม่

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'number' => 'ลำดับ',
                'asset_detail' => 'รายละเอียดขอบเขตงาน',
                'amount' => 'จำนวน',
                'unit' => 'หน่วย',
                'priceunit' => 'ราคา/หน่วย',
                'sum' => 'ราคารวม',
                'content' => $this->renderAjax('word', ['filename' => $filename]),
            ];
        } else {
            return $this->render('word', ['filename' => $filename]);
        }
    }

    public function actionPurchase_5()
    {
        $id = $this->request->get('id');
        $user = Yii::$app->user->id;
        $filename = 'รายงานขอซื้อขอจ้าง';

        $templateProcessor = new Processor(Yii::getAlias('@webroot') . '/msword/purchase_4.docx'); //เลือกไฟล์ template ที่เราสร้างไว้
        $templateProcessor->setValue('title', 'รายงานขอซื้อขอจ้าง');
        $templateProcessor->saveAs(Yii::getAlias('@webroot') . '/msword/temp/' . $filename . '.docx'); //สั่งให้บันทึกข้อมูลลงไฟล์ใหม่

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'org_name_full' => 'รายละเอียดชื่อโรงพยาบาลเต็ม',
                'doc_number' => 'เลขหนังสือ',
                'date' => 'วันที่',
                'org_name' => 'ชื่อโรงพยาบาล',
                'budget_type' => 'ประเภทเงินงบประมาณ',
                'sup_detail' => 'รายละเอียดพัสดุ',
                'detail' => 'เหตุผลของการจัดซื้อ',
                'amount' => 'จำนวน',
                'price' => 'ราคา',
                'price_character' => 'ราคาตัวอักษร',
                'sup_detailfull' => 'รายละเอียดของพัสดุ',
                'board' => 'คณะกรรมการตรวจรับพัสดุ',
                'emp_name' => 'เจ้าหน้าที่พนักงาน',
                'emp_position' => 'ตำแหน่งเจ้าหน้าที่',
                'emphead_name' => 'หัวหน้าเจ้าหน้าที่',
                'emphead_position' => 'ตำแหน่งหัวหน้าเจ้าหน้าที่',
                'director_name' => 'ผู้อำนวยการโรงพยาบาล',
                'content' => $this->renderAjax('word', ['filename' => $filename]),
            ];
        } else {
            return $this->render('word', ['filename' => $filename]);
        }
    }
    public function actionPurchase_6()
    {
        $id = $this->request->get('id');
        $user = Yii::$app->user->id;
        $filename = 'รายงานผลการตรวจรับพัสดุ';

        $templateProcessor = new Processor(Yii::getAlias('@webroot') . '/msword/purchase_6.docx'); //เลือกไฟล์ template ที่เราสร้างไว้
        $templateProcessor->setValue('title', 'รายงานผลการตรวจรับพัสดุ');
        $templateProcessor->saveAs(Yii::getAlias('@webroot') . '/msword/temp/' . $filename . '.docx'); //สั่งให้บันทึกข้อมูลลงไฟล์ใหม่

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'org_name_full' => 'รายละเอียดชื่อโรงพยาบาลเต็ม',
                'doc_number' => 'เลขหนังสือ',
                'date' => 'วันที่',
                'sup_detail' => 'รายละเอียดพัสดุ',
                'org_name' => 'ชื่อโรงพยาบาล',
                'amount' => 'จำนวน',
                'price' => 'ราคา',
                'price_character' => 'ราคาตัวอักษร',
                'bill_number' => 'เลขใบสั่งซื้อ',
                'bill_datebegin' => 'ใบสั่งซื้อลงวันที่เริ่ม',
                'bill_dateend' => 'ใบสั่งซื้อลงวันที่สิ้นสุด',
                'budget_type' => 'ประเภทเงินงบ',
                'check_date' => 'วันที่ตรวจรับ',
                'check_time' => 'เวลาตรวจรับ',
                'emp_name' => 'เจ้าหน้าที่พนักงาน',
                'emp_position' => 'ตำแหน่งเจ้าหน้าที่',
                'emphead_name' => 'หัวหน้าเจ้าหน้าที่',
                'emphead_position' => 'ตำแหน่งหัวหน้าเจ้าหน้าที่',
                'director_name' => 'ผู้อำนวยการโรงพยาบาล',
                'provice' => 'จังหวัด',
                'content' => $this->renderAjax('word', ['filename' => $filename]),
            ];
        } else {
            return $this->render('word', ['filename' => $filename]);
        }
    }

    public function actionPurchase_7()
    {
        $id = $this->request->get('id');
        $user = Yii::$app->user->id;
        $filename = 'ประกาศผู้ชนะการเสนอราคา';

        $templateProcessor = new Processor(Yii::getAlias('@webroot') . '/msword/purchase_7.docx'); //เลือกไฟล์ template ที่เราสร้างไว้
        $templateProcessor->setValue('title', 'ประกาศผู้ชนะการเสนอราคา');
        $templateProcessor->saveAs(Yii::getAlias('@webroot') . '/msword/temp/' . $filename . '.docx'); //สั่งให้บันทึกข้อมูลลงไฟล์ใหม่

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'sup_type' => 'ประเภทพัสดุ',
                'org_name' => 'ชื่อโรงพยาบาล',
                'project_name' => 'ชื่อโปรเจค',
                'budget_name' => 'ประเภทงบประมาณ',
                'vendor_name' => 'ชื่อจำแทนจำหน่าย',
                'price' => 'ราคา',
                'price_character' => 'ราคาตัวอักษร',
                'director_name' => 'ผู้อำนวยการโรงพยาบาล',
                'date' => 'วันที่ประกาศ',
                'content' => $this->renderAjax('word', ['filename' => $filename]),
            ];
        } else {
            return $this->render('word', ['filename' => $filename]);
        }
    }

    public function actionPurchase_8()
    {
        $id = $this->request->get('id');
        $user = Yii::$app->user->id;
        $filename = 'ใบสั่งซื้อสั่งจ้าง';

        $templateProcessor = new Processor(Yii::getAlias('@webroot') . '/msword/purchase_8.docx'); //เลือกไฟล์ template ที่เราสร้างไว้
        $templateProcessor->setValue('title', 'ใบสั่งซื้อสั่งจ้าง');
        $templateProcessor->saveAs(Yii::getAlias('@webroot') . '/msword/temp/' . $filename . '.docx'); //สั่งให้บันทึกข้อมูลลงไฟล์ใหม่

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => '<i class="fa-solid fa-calendar-check"></i>ใบสั่งซื้อสั่งจ้าง',
                'content' => $this->renderAjax('word', ['filename' => $filename]),
            ];
        } else {
            return $this->render('word', ['filename' => $filename]);
        }
    }

    public function actionPurchase_9()
    {
        $id = $this->request->get('id');
        $user = Yii::$app->user->id;
        $filename = 'ใบตรวจรับการจัดซื้อจัดจ้าง';

        $templateProcessor = new Processor(Yii::getAlias('@webroot') . '/msword/purchase_9.docx'); //เลือกไฟล์ template ที่เราสร้างไว้
        $templateProcessor->setValue('title', 'ใบตรวจรับการจัดซื้อ/จัดจ้าง');
        $templateProcessor->saveAs(Yii::getAlias('@webroot') . '/msword/temp/' . $filename . '.docx'); //สั่งให้บันทึกข้อมูลลงไฟล์ใหม่

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'date' => 'วันที่',
                'bill_number' => 'ใบสั่งซื้อเลขที่',
                'bill_date' => 'ใบสั่งซื้อลงวันที่',
                'provice' => 'จังหวัด',
                'org_name' => 'ชื่อโรงพยาบาล',
                'vendor_name' => 'ชื่อโรงพยาบาล',
                'sup_detail' => 'รายละเอียดพัสดุ',
                'amount' => 'จำนวน',
                'budget_type' => 'ประเภทงบ',
                'price' => 'ราคา',
                'price_character' => 'ราคาตัวอักษร',
                'president' => 'ประธาน',
                'director_one' => 'กรรามการ 1',
                'director_two' => 'กรรามการ 2',
                'content' => $this->renderAjax('word', ['filename' => $filename]),
            ];
        } else {
            return $this->render('word', ['filename' => $filename]);
        }
    }

    public function actionPurchase_10()
    {
        $id = $this->request->get('id');
        $user = Yii::$app->user->id;
        $filename = 'รายงานผลการตรวจรับ';

        $templateProcessor = new Processor(Yii::getAlias('@webroot') . '/msword/purchase_10.docx'); //เลือกไฟล์ template ที่เราสร้างไว้
        $templateProcessor->setValue('title', 'รายงานผลการตรวจรับ');
        $templateProcessor->saveAs(Yii::getAlias('@webroot') . '/msword/temp/' . $filename . '.docx'); //สั่งให้บันทึกข้อมูลลงไฟล์ใหม่

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => '<i class="fa-solid fa-calendar-check"></i>รายงานผลการตรวจรับ',
                'content' => $this->renderAjax('word', ['filename' => $filename]),
            ];
        } else {
            return $this->render('word', ['filename' => $filename]);
        }
    }

    public function actionPurchase_11()
    {
        $id = $this->request->get('id');
        $user = Yii::$app->user->id;
        $filename = 'แบบแสดงความบริสุทธิ์ใจ';

        $templateProcessor = new Processor(Yii::getAlias('@webroot') . '/msword/purchase_11.docx'); //เลือกไฟล์ template ที่เราสร้างไว้
        $templateProcessor->setValue('title', 'แบบแสดงความบริสุทธิ์ใจ');
        $templateProcessor->saveAs(Yii::getAlias('@webroot') . '/msword/temp/' . $filename . '.docx'); //สั่งให้บันทึกข้อมูลลงไฟล์ใหม่

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => '<i class="fa-solid fa-calendar-check"></i>แบบแสดงความบริสุทธิ์ใจ',
                'content' => $this->renderAjax('word', ['filename' => $filename]),
            ];
        } else {
            return $this->render('word', ['filename' => $filename]);
        }
    }

    public function actionPurchase_12()
    {
        $id = $this->request->get('id');
        $user = Yii::$app->user->id;
        $filename = 'ขออนุมัติจ่ายเงินบำรุง';

        $templateProcessor = new Processor(Yii::getAlias('@webroot') . '/msword/purchase_12.docx'); //เลือกไฟล์ template ที่เราสร้างไว้
        $templateProcessor->setValue('title', 'ขออนุมัติจ่ายเงินบำรุง');
        $templateProcessor->saveAs(Yii::getAlias('@webroot') . '/msword/temp/' . $filename . '.docx'); //สั่งให้บันทึกข้อมูลลงไฟล์ใหม่

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => '<i class="fa-solid fa-calendar-check"></i>ขออนุมัติจ่ายเงินบำรุง',
                'content' => $this->renderAjax('word', ['filename' => $filename]),
            ];
        } else {

        }
    }
    //ตัวอย่าง/
    public function actionBill()
    {
        $id = $this->request->get('id');
        $user = Yii::$app->user->id;
        $filename = 'บันทึกข้อความ';

        $templateProcessor = new Processor(Yii::getAlias('@webroot') . '/msword/template_example.docx'); //เลือกไฟล์ template ที่เราสร้างไว้
        $templateProcessor->setValue('title', 'ตัวอย่าง');
        $templateProcessor->saveAs(Yii::getAlias('@webroot') . '/msword/temp/' . $filename . '.docx'); //สั่งให้บันทึกข้อมูลลงไฟล์ใหม่

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => '<i class="fa-solid fa-calendar-check"></i> ใบขอซื้อ',
                'content' => $this->renderAjax('word', ['filename' => $filename]),
            ];
        } else {

            return $this->render('word', ['filename' => $filename]);
        }
    }

}
