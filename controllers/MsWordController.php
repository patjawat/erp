<?php

namespace app\controllers;

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
use yii;
use yii\web\NotFoundHttpException;

class MsWordController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

    protected function findAssetModel($id)
    {
        if (($model = Asset::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

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
                'title' => Html::a('<i class="fa-solid fa-cloud-arrow-down"></i> ดาวน์โหลดเอกสาร', Url::to(Yii::getAlias('@web') . '/msword/results/' . $filename), ['class' => 'btn btn-primary text-center mb-3', 'target' => '_blank', 'onclick' => 'return closeModal()']),
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

    // ทะเบียนทรัพย์สิน
    public function actionAsset()
    {
        \Yii::$app->response->format = yii\web\Response::FORMAT_RAW;
        \Yii::$app->response->headers->add('content-type', 'text/html');

        $id = $this->request->get('id');
        $user = Yii::$app->user->id;

        Settings::setTempDir(Yii::getAlias('@webroot') . '/msword/temp/');  // กำหนด folder temp สำหรับ windows server ที่ permission denied temp (อย่ายลืมสร้างใน project ล่ะ)
        $templateProcessor = new Processor(Yii::getAlias('@webroot') . '/msword/asset.docx');  // เลือกไฟล์ template ที่เราสร้างไว้

        // ถ้ามี ID ส่งมาให้แสดงจาก Database
        if (isset($id)) {
            $model = $this->findAssetModel($id);
            $number = $this->request->get('number');
            $date = $this->request->get('date');
            $vendor = $model->getVendor();
            $venrorAddress = isset($vendor->data_json['address']) ? $vendor->data_json['address'] : '-';
            $venrorPhone = isset($vendor->data_json['phone']) ? $vendor->data_json['phone'] : '-';
            $templateProcessor->setValue('title', 'ทะเบียนทรัพย์สิน');
            $templateProcessor->setValue('org_name', $model->department_name);
            $templateProcessor->setValue('department', $this->GetInfo()['company_name']);
            $templateProcessor->setValue('asset_type', $model->AssetTypeName());  // ประเภท
            $templateProcessor->setValue('asset_fsn', $model->code);  // หมายเลขครุภัณฑ์
            $templateProcessor->setValue('feature', $model->asset_option);  // ลักษณะ/คุณสมบัติ
            $templateProcessor->setValue('design', '-');
            $templateProcessor->setValue('asset_add', $model->department_name);  // สถานที่ตั้ง/หน่วยงานที่รับผิดชอบ
            // คำนามชื่อผู้ขาย/ผู้รับจ้าง/ผู้บริจาค
            switch ($model->method_get) {
                case 'บริจาค':
                    $vendor_prefix = 'ผู้บริจาค';
                    break;
                case 'ซื้อ':
                    $vendor_prefix = 'ผู้ขาย';
                    break;
                case 'จ้างก่อสร้าง':
                    $vendor_prefix = 'ผู้รับจ้าง';
                    break;
                case 'เช่า':
                    $vendor_prefix = 'ผู้ให้เช่า';
                    break;
                default:
                    $vendor_prefix = 'ชื่อผู้ขาย/ผู้รับจ้าง/ผู้บริจาค';
                    break;
            }
            $templateProcessor->setValue('vendor_prefix', $vendor_prefix);  // ำนามชื่อผู้ขาย/ผู้รับจ้าง/ผู้บริจาค
            $templateProcessor->setValue('vendor', $model->vendor_name);  // ชื่อผู้ขาย/ผู้รับจ้าง/ผู้บริจาค
            $templateProcessor->setValue('vendor_add', $venrorAddress);  // ที่อยู่ของผู้ขาย
            $templateProcessor->setValue('vendor_tel', $venrorPhone);  // หมายเลขโทรศัพท์ของผู้ขาย
            $templateProcessor->setValue('budget_type', $model->budget_type);  // ประเภทเงิน
            $templateProcessor->setValue('method', $model->purchase_text);  // วิธีการได้มา
            $templateProcessor->setValue('r_date', Yii::$app->thaiFormatter->asDate($model->receive_date, 'php:d/m/Y'));  // วิธีการได้มา
            $templateProcessor->setValue('asset_name1', $model->AssetitemName());  // ชื่อหรือชนิดของทรัพย์สิน
            $templateProcessor->setValue('amount', '1');  // จำนวน
            $templateProcessor->setValue('price_unit', number_format($model->price, 2));  // จำนวนเงินที่แสดงถึงราคาต่อหน่วย
            $templateProcessor->setValue('price', number_format($model->price, 2));  // จำนวนเงินที่แสดงถึงราคาต่อหน่วย
            $templateProcessor->setValue('life', $model->data_json['service_life']);  // อายุการใช้งาน
            $templateProcessor->setValue('dep_year', number_format($model->price / $model->data_json['service_life'], 2));  // ค่าเสื่อมต่อปี

            $datas = AssetHelper::Depreciation($model->id, $number);

            $templateProcessor->cloneRow('asset_name', count($datas));
            $i = 1;
            foreach ($datas as $data) {
                $templateProcessor->setValue('date#' . $i, Yii::$app->thaiFormatter->asDate($data['end_date'], 'php:d/m/Y'));  // วันที่รับเข้า
                $templateProcessor->setValue('doc_number#' . $i, '');  // เลขที่เอกสารแสดงการได้มาของทรัพย์สิน
                $templateProcessor->setValue('asset_name#' . $i, $model->AssetitemName());  // ชื่อหรือชนิดของทรัพย์สิน
                $templateProcessor->setValue('price_unit#' . $i, number_format($data['price'], 2));  // จำนวนเงินที่แสดงถึงราคาต่อหน่วย
                $templateProcessor->setValue('asset_life#' . $i, $data['service_life']);  // อายุการใช้งาน
                $templateProcessor->setValue('deprate#' . $i, $data['price_month']);  // ระบุอัตราค่าเสื่อมราคาของทรัพย์สิน
                $templateProcessor->setValue('accdep#' . $i, number_format($data['total_price'], 2));  // จำนวนเงินค่าเสื่อมราคาที่สะสม
                $templateProcessor->setValue('total#' . $i, number_format($data['total'], 2));  // มูลค่าสุทธิ
                $templateProcessor->setValue('remart#' . $i, '');  // หมายเหตุ
                $i++;
            }
            $filename = 'ค่าเสื่อม' . (isset($model->data_json['asset_name']) ? $model->data_json['asset_name'] : '-') . ' วันที่ ' . Yii::$app->thaiFormatter->asDate($date, 'medium') . '.docx';
        } else {
            // ถ้าไม่มี ID ส่งมาให้แสดงข้อมูลตัวอย่าง
            $templateProcessor->setValue('title', 'ทะเบียนคุมทรัพย์สิน');
            $templateProcessor->setValue('org_name', '');
            $templateProcessor->setValue('department', $comanyName);
            $templateProcessor->setValue('asset_type', 'ประเภทของทรัพย์สิน');  // ประเภท
            $templateProcessor->setValue('asset_fsn', 'หมายเลขครุภัณฑ์');  // หมายเลขครุภัณฑ์
            $templateProcessor->setValue('feature', 'ลักษณะ/คุณสมบัติ');  // ลักษณะ/คุณสมบัติ
            $templateProcessor->setValue('design', 'รุ่น/แบบ');
            $templateProcessor->setValue('asset_add', 'สถานที่ตั้ง/หน่วยงานที่รับผิดชอบ');  // สถานที่ตั้ง/หน่วยงานที่รับผิดชอบ
            $templateProcessor->setValue('vendor', 'สถานที่ตั้ง/หน่วยงานที่รับผิดชอบ');  // c/ผู้รับจ้าง/ผู้บริจาค
            $templateProcessor->setValue('vendor_add', 'ที่อยู่');  // ที่อยู่ของผู้ขาย
            $templateProcessor->setValue('vendor_tel', 'หมายเลขโทรศัพท์');  // หมายเลขโทรศัพท์ของผู้ขาย
            $templateProcessor->setValue('budget_type', 'ประเภทเงิน');  // ประเภทเงิน
            $templateProcessor->setValue('method', 'วิธีการได้มา');  // วิธีการได้มา
            $filename = 'asset_result-test.docx';
        }
        // try {
        @unlink(Yii::getAlias('@webroot') . '/msword/results/' . $filename);
        // } catch (\Throwable $th) {
        //     //throw $th;
        // }

        $templateProcessor->saveAs(Yii::getAlias('@webroot') . '/msword/results/' . $filename);  // สั่งให้บันทึกข้อมูลลงไฟล์ใหม่
        return $this->Show($filename);
    }


    public function actionPurchase_1()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $id = $this->request->get('id');
        $model = $this->findOrderModel($id);
        $oldObj = $model->data_json;

        if ($this->request->isPost && $model->load($this->request->post())) {

            $setDate = [
                'committee_detail_date' =>  AppHelper::convertToGregorian($model->set_date),
            ];
            $model->data_json =  ArrayHelper::merge($oldObj, $model->data_json, $setDate);
            $model->save(false);

            $listBoards = Order::find()->where(['category_id' => $model->id, 'name' => 'board_detail'])->all();
            $user = Yii::$app->user->id;
            $word_name = 'purchase_1.docx';
            $result_name = 'ขออนุมัติแต่งตั้ง กก. กำหนดรายละเอียด' . $model->pr_number . '.docx';
            @unlink(Yii::getAlias('@webroot') . '/msword/results/' . $result_name);
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

            $templateProcessor->saveAs(Yii::getAlias('@webroot') . '/msword/results/' . $result_name);  // สั่งให้บันทึกข้อมูลลงไฟล์ใหม่
            return $this->Show($result_name);
        } else {
            $model->loadDefaultValues();
        }

        try {
            $model->set_date  =  AppHelper::convertToThai($model->data_json['committee_detail_date']);
        } catch (\Throwable $th) {
            //throw $th;
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => 'วันที่ลงนาม',
                'content' => $this->renderAjax('_form', ['model' => $model]),
                'footer' => ''
            ];
        } else {

            return $this->render('_form', [
                'model' => $model,
            ]);
        }
    }

    public function actionPurchase_2()
    {
        $id = $this->request->get('id');
        $user = Yii::$app->user->id;
        $model = $this->findOrderModel($id);
        $oldObj = $model->data_json;

        if ($this->request->isPost && $model->load($this->request->post())) {

            $setDate = ['apporve_report_date' =>  AppHelper::convertToGregorian($model->set_date)];
            $model->data_json =  ArrayHelper::merge($oldObj, $model->data_json, $setDate);
            $model->save(false);

            $word_name = 'purchase_2.docx';
            $result_name = 'ขอความเห็นชอบและรายงานผล.docx';

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
            return $this->CreateFile($data);
        }

        try {
            $model->set_date  =  AppHelper::convertToThai($model->data_json['apporve_report_date']);
        } catch (\Throwable $th) {
            //throw $th;
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => 'ลงวันที่',
                'content' => $this->renderAjax('_form', ['model' => $model]),
                'footer' => ''
            ];
        } else {

            return $this->render('_form', [
                'model' => $model,
            ]);
        }
    }

    // ขออนุมัติจัดซื้อจัดจ้าง
    public function actionPurchase_3()
    {
        $id = $this->request->get('id');
        $model = $this->findOrderModel($id);
        $user = Yii::$app->user->id;
        $word_name = 'purchase_3.docx';
        $result_name = 'ขออนุมัติจัดซื้อจัดจ้าง.docx';
        @unlink(Yii::getAlias('@webroot') . '/msword/results/' . $result_name);
        
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

        $templateProcessor->saveAs(Yii::getAlias('@webroot') . '/msword/results/' . $result_name);  // สั่งให้บันทึกข้อมูลลงไฟล์ใหม่
        return $this->Show($result_name);
    }

    public function actionPurchase_4()
    {
        $id = $this->request->get('id');
        $user = Yii::$app->user->id;
        $model = $this->findOrderModel($id);

        $word_name = 'purchase_4.docx';
        $result_name = 'ขออนุมัติจัดซื้อจัดจ้าง.docx';
        @unlink(Yii::getAlias('@webroot') . '/msword/results/' . $result_name);
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
        $templateProcessor->saveAs(Yii::getAlias('@webroot') . '/msword/results/' . $result_name);  // สั่งให้บันทึกข้อมูลลงไฟล์ใหม่
        return $this->Show($result_name);
    }

    public function actionPurchase_5()
    {
        $id = $this->request->get('id');
        $user = Yii::$app->user->id;
        $model = $this->findOrderModel($id);

        $oldObj = $model->data_json;

        if ($this->request->isPost && $model->load($this->request->post())) {

            $setDate = ['purchase_report_date' =>  AppHelper::convertToGregorian($model->set_date)];
            $model->data_json =  ArrayHelper::merge($oldObj, $model->data_json, $setDate);
            $model->save(false);

            $word_name = 'purchase_5.docx';
            $result_name = 'รายงานขอซื้อขอจ้าง.docx';
            @unlink(Yii::getAlias('@webroot') . '/msword/results/' . $result_name);
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

            $templateProcessor->saveAs(Yii::getAlias('@webroot') . '/msword/results/' . $result_name);  // สั่งให้บันทึกข้อมูลลงไฟล์ใหม่
            return $this->Show($result_name);
        } else {
            $model->loadDefaultValues();
        }

        try {
            $model->set_date  =  AppHelper::convertToThai($model->data_json['purchase_report_date']);
        } catch (\Throwable $th) {
            //throw $th;
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => 'ลงวันที่',
                'content' => $this->renderAjax('_form', ['model' => $model]),
                'footer' => ''
            ];
        } else {

            return $this->render('_form', [
                'model' => $model,
            ]);
        }
    }

    public function actionPurchase_6()
    {
        $id = $this->request->get('id');
        $model = $this->findOrderModel($id);
        $oldObj = $model->data_json;
        if ($this->request->isPost && $model->load($this->request->post())) {

            $setDate = ['purchase_report_order_date' =>  AppHelper::convertToGregorian($model->set_date)];
            $model->data_json =  ArrayHelper::merge($oldObj, $model->data_json, $setDate);
            $model->save(false);

            $word_name = 'purchase_6.docx';
            $result_name = 'รายงานผลการตรวจรับพัสดุ.docx';
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
            return $this->CreateFile($data);
        } else {
            $model->loadDefaultValues();
        }

        try {
            $model->set_date  =  AppHelper::convertToThai($model->data_json['purchase_report_order_date']);
        } catch (\Throwable $th) {
            //throw $th;
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => 'ลงวันที่',
                'content' => $this->renderAjax('_form', ['model' => $model]),
                'footer' => ''
            ];
        } else {

            return $this->render('_form', [
                'model' => $model,
            ]);
        }
    }
    public function actionPurchase_7()
    {
        $id = $this->request->get('id');
        $user = Yii::$app->user->id;
        $model = $this->findOrderModel($id);
        $oldObj = $model->data_json;
        if ($this->request->isPost && $model->load($this->request->post())) {

            $setDate = ['report_winner_date' =>  AppHelper::convertToGregorian($model->set_date)];
            $model->data_json =  ArrayHelper::merge($oldObj, $model->data_json, $setDate);
            $model->save(false);


            $word_name = 'purchase_7.docx';
            $result_name = 'ประกาศผู้ชนะการเสนอราคา.docx';
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
            return $this->CreateFile($data);
        } else {
            $model->loadDefaultValues();
        }

        try {
            $model->set_date  =  AppHelper::convertToThai($model->data_json['report_winner_date']);
        } catch (\Throwable $th) {
            //throw $th;
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => 'ลงวันที่',
                'content' => $this->renderAjax('_form', ['model' => $model]),
                'footer' => ''
            ];
        } else {

            return $this->render('_form', [
                'model' => $model,
            ]);
        }
    }

    public function actionPurchase_8()
    {
        // Yii::$app->response->format = Response::FORMAT_JSON;
        $id = $this->request->get('id');
        $model = $this->findOrderModel($id);
        // return count($model->ListOrderItems());
        $user = Yii::$app->user->id;
        $word_name = 'purchase_8.docx';
        $result_name = 'ใบสั่งซื้อสั่งจ้าง.docx';
        @unlink(Yii::getAlias('@webroot') . '/msword/results/' . $result_name);
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
        $templateProcessor->saveAs(Yii::getAlias('@webroot') . '/msword/results/' . $result_name);  // สั่งให้บันทึกข้อมูลลงไฟล์ใหม่
        return $this->Show($result_name);
    }

    //ใบสั่งซื้อสั่งจ้าง
    public function actionPurchase_9()
    {
        $id = $this->request->get('id');
        $model = $this->findOrderModel($id);
        $word_name = 'purchase_9.docx';
        $result_name = 'ใบสั่งซื้อสั่งจ้าง.docx';
        @unlink(Yii::getAlias('@webroot') . '/msword/results/' . $result_name);
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
        $templateProcessor->saveAs(Yii::getAlias('@webroot') . '/msword/results/' . $result_name);  // สั่งให้บันทึกข้อมูลลงไฟล์ใหม่
        return $this->Show($result_name);
    }

    public function actionPurchase_10()
    {
        $id = $this->request->get('id');
        $model = $this->findOrderModel($id);
        $user = Yii::$app->user->id;

        $oldObj = $model->data_json;
        if ($this->request->isPost && $model->load($this->request->post())) {

            $setDate = ['report_checker_date' =>  AppHelper::convertToGregorian($model->set_date)];
            $model->data_json =  ArrayHelper::merge($oldObj, $model->data_json, $setDate);
            $model->save(false);


            $word_name = 'purchase_10.docx';
            $result_name = 'รายงานผลการตรวจรับ.docx';
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
            return $this->CreateFile($data);
        } else {
            $model->loadDefaultValues();
        }

        try {
            $model->set_date  =  AppHelper::convertToThai($model->data_json['report_checker_date']);
        } catch (\Throwable $th) {
            //throw $th;
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => 'ลงวันที่',
                'content' => $this->renderAjax('_form', ['model' => $model]),
                'footer' => ''
            ];
        } else {

            return $this->render('_form', [
                'model' => $model,
            ]);
        }
    }

    public function actionPurchase_11()
    {
        $id = $this->request->get('id');
        $user = Yii::$app->user->id;
        $model = $this->findOrderModel($id);
        $word_name = 'purchase_11.docx';
        $result_name = 'แบบแสดงความบริสุทธิ์ใจ.docx';
        @unlink(Yii::getAlias('@webroot') . '/msword/results/' . $result_name);
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

           
        $templateProcessor->saveAs(Yii::getAlias('@webroot') . '/msword/results/' . $result_name);  // สั่งให้บันทึกข้อมูลลงไฟล์ใหม่
            return $this->Show($result_name);
    }

    public function actionPurchase_12()
    {
        $id = $this->request->get('id');
        $model = $this->findOrderModel($id);
        $user = Yii::$app->user->id;

        $oldObj = $model->data_json;
        if ($this->request->isPost && $model->load($this->request->post())) {

            $setDate = ['request_pay_date' =>  AppHelper::convertToGregorian($model->set_date)];
            $model->data_json =  ArrayHelper::merge($oldObj, $model->data_json, $setDate);
            $model->save(false);

        $word_name = 'purchase_12.docx';
        $result_name = 'ขออนุมัติจ่ายเงินบำรุง.docx';
        $data = [
            'word_name' => $word_name,
            'result_name' => $result_name,
            'items' => [
                'title' => 'ขออนุมัติจ่ายเงินบำรุง',
                'doc_number' => $this->getInfo()['doc_number'],
                'date' => Yii::$app->thaiFormatter->asDate($model->data_json['request_pay_date'], 'long'),
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
        return $this->CreateFile($data);
    } else {
        $model->loadDefaultValues();
    }

    try {
        $model->set_date  =  AppHelper::convertToThai($model->data_json['request_pay_date']);
    } catch (\Throwable $th) {
        //throw $th;
    }

    if ($this->request->isAjax) {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'title' => 'ลงวันที่',
            'content' => $this->renderAjax('_form', ['model' => $model]),
            'footer' => ''
        ];
    } else {

        return $this->render('_form', [
            'model' => $model,
        ]);
    }
}
    public function actionStockcard()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $id = $this->request->get('id');
        $model = Stock::findOne($id);

        $sql = "SELECT x.*,(x.unit_price * qty) as total_price FROM(SELECT 
        t.*,o.category_id as category_code,t.data_json->>'$.employee_fullname' as fullname,
         w.warehouse_name,
          @running_total := IF(t.transaction_type = 'IN', @running_total + t.qty, @running_total - t.qty) AS total
      FROM 
          stock_events t
      JOIN 
          (SELECT @running_total := 0) r
      LEFT JOIN warehouses w ON w.id =  t.from_warehouse_id
      LEFT JOIN stock_events o ON o.id = t.category_id AND o.name = 'order'
          WHERE t.asset_item = :asset_item AND t.name = 'order_item' AND t.warehouse_id = :warehouse_id
      ORDER BY 
          t.created_at, t.id) as x";
        $stockList =   Yii::$app->db->createCommand($sql)
            ->bindValue(':asset_item', $model->asset_item)
            ->bindValue(':warehouse_id', $model->warehouse_id)
            ->queryAll();
        $user = Yii::$app->user->id;
        $word_name = 'stockcard.docx';
        $result_name = 'stock_result-' . $model->id . '.docx';
        @unlink(Yii::getAlias('@webroot') . '/msword/results/' . $result_name);
        $templateProcessor = new Processor(Yii::getAlias('@webroot') . '/msword/' . $word_name);  // เลือกไฟล์ template ที่เราสร้างไว้

        $templateProcessor->setValue('asset_name', $model->product->title);
        $templateProcessor->setValue('asset_type', $model->product->ViewTypeName()['title']);
        $templateProcessor->setValue('code', $model->asset_item);


        $templateProcessor->cloneRow('asset_item', count($stockList));
        $i = 1;
        $num = 1;
        foreach ($stockList as $item) {

            $templateProcessor->setValue('asset_item#' . $i, $item['asset_item']);
            $templateProcessor->setValue('created_at#' . $i, AppHelper::convertToThai($item['created_at']));
            $templateProcessor->setValue('created_name#' . $i, $item['fullname']);
            $templateProcessor->setValue('in#' . $i, $item['transaction_type'] == 'IN' ? $item['qty'] : '');
            $templateProcessor->setValue('out#' . $i, $item['transaction_type'] == 'OUT' ? $item['qty'] : '');
            $templateProcessor->setValue('total#' . $i, $item['total']);
            $i++;
        }

        $templateProcessor->saveAs(Yii::getAlias('@webroot') . '/msword/results/' . $result_name);  // สั่งให้บันทึกข้อมูลลงไฟล์ใหม่
        return $this->Show($result_name);
    }


    // function สร้าง Word
    public function CreateFile($data)
    {
        $result_name = $data['result_name'];
        @unlink(Yii::getAlias('@webroot') . '/msword/results/' . $result_name);
        $templateProcessor = new Processor(Yii::getAlias('@webroot') . '/msword/' . $data['word_name']);  // เลือกไฟล์ template ที่เราสร้างไว้
        foreach ($data['items'] as $key => $value) {
            $templateProcessor->setValue($key, $value);
        }
       

        $templateProcessor->saveAs(Yii::getAlias('@webroot') . '/msword/results/' . $result_name);  // สั่งให้บันทึกข้อมูลลงไฟล์ใหม่
        return $this->Show($result_name);
    }
}
