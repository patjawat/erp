<?php

namespace app\controllers;

use app\components\AppHelper;
use app\components\Processor;
use app\components\SiteHelper;
use app\modules\am\components\AssetHelper;
use app\modules\am\models\Asset;
use app\modules\purchase\models\Order;
use PhpOffice\PhpWord\Settings;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\Response;
use yii;

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
            'company_address' => $info['address'],  // ที่อยู่
            'province' => $info['province'],  // ที่อยู่
            'director_name' => $info['director_name'],  // ชื่อผู้บริหาร ผอ.
            'director_position' => $info['director_position']  // ตำแหน่งของ ผอ.
        ];
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

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
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

    public function actionPurchase_1()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $id = $this->request->get('id');
        $model = $this->findOrderModel($id);
        $listBoards = Order::find()->where(['category_id' => $model->id, 'name' => 'board'])->all();
        $user = Yii::$app->user->id;
        $word_name = 'purchase_1.docx';
        $result_name = 'ขออนุมัติแต่งตั้ง กก. กำหนดรายละเอียด.docx';

        $templateProcessor = new Processor(Yii::getAlias('@webroot') . '/msword/' . $word_name);  // เลือกไฟล์ template ที่เราสร้างไว้
        // $data = [
        //     'word_name' => $word_name,
        //     'result_name' => $result_name,
        //     'items' => [
        @unlink(Yii::getAlias('@webroot') . '/msword/results/' . $result_name);

        $templateProcessor->setValue('title', 'ขออนุมัติแต่งตั้ง กก. กำหนดรายละเอียด');
        $templateProcessor->setValue('org_name_full', $this->getInfo()['company_full']);
        $templateProcessor->setValue('doc_number', 'เลขที่เอกสาร');
        $templateProcessor->setValue('date', isset($model->data_json['order_date']) ? (AppHelper::thainumDigit(Yii::$app->thaiFormatter->asDate($model->data_json['order_date'], 'medium'))) : '-');
        $templateProcessor->setValue('doc_title', 'ขออนุมัติแต่งตั้งคณะกรรมการกำหนดรายละเอียดคุณลักษณะเฉพาะ');
        $templateProcessor->setValue('org_name', $this->getInfo()['company_name']);
        $templateProcessor->setValue('suptype', (isset($model->data_json['product_type_name']) ? $model->data_json['product_type_name'] : '-'));
        $templateProcessor->setValue('budget_year', 'ปีงบประมาณ');
        $templateProcessor->setValue('budget_amount', number_format($model->SumPo(), 2));
        $templateProcessor->setValue('budget_letter', AppHelper::convertNumberToWords($model->SumPo(), 2));
        $templateProcessor->setValue('board', 'คณะกรรมการกำหนดรายละเอียด');
        $templateProcessor->setValue('emp_name', $model->getUserReq()['fullname']);
        $templateProcessor->setValue('emp_position', $model->getUserReq()['position_name']);
        $templateProcessor->setValue('emphead_name', $model->viewLeaderUser()['fullname']);
        $templateProcessor->setValue('emphead_position', $model->viewLeaderUser()['position_name']);
        $templateProcessor->setValue('director_name', $this->GetInfo()['director_name']);
        $templateProcessor->setValue('org_name', $this->GetInfo()['company_name']);

        $templateProcessor->cloneRow('board_name', count($listBoards));
        $i = 1;
        $num = 1;
        foreach ($listBoards as $board) {
            $templateProcessor->setValue('num#' . $i, AppHelper::thainumDigit($num++));
            $templateProcessor->setValue('board_name#' . $i, $board->data_json['board_fullname']);
            $templateProcessor->setValue('position_name#' . $i, $board->data_json['position_name']);
            $templateProcessor->setValue('board_position#' . $i, $board->data_json['board_position']);
            $i++;
        }

        $templateProcessor->saveAs(Yii::getAlias('@webroot') . '/msword/results/' . $result_name);  // สั่งให้บันทึกข้อมูลลงไฟล์ใหม่
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => Html::a('<i class="fa-solid fa-cloud-arrow-down"></i> ดาวน์โหลดเอกสาร', Url::to(Yii::getAlias('@web') . '/msword/results/' . $result_name), ['class' => 'btn btn-primary text-center mb-3', 'target' => '_blank', 'onclick' => 'return closeModal()']),
                'content' => $this->renderAjax('word', ['filename' => $result_name]),
            ];
        } else {
            echo '<p>';
            echo Html::a('ดาวน์โหลดเอกสาร', Url::to(Yii::getAlias('@web') . '/msword/results/' . $result_name), ['class' => 'btn btn-info']);  // สร้าง link download
            echo '</p>';
            echo '<iframe src="https://docs.google.com/viewerng/viewer?url=' . Url::to(Yii::getAlias('@web') . '/msword/temp/asset_result.docx', true) . '&embedded=true"  style="position: absolute;width:100%; height: 100%;border: none;"></iframe>';
        }
        // ],

        // 'items' => [
        //     'title' => 'ขออนุมัติแต่งตั้ง กก. กำหนดรายละเอียด',
        //     'org_name_full' => 'รายละเอียดโรงพยาบาล',
        //     'doc_number' => 'เลขที่เอกสาร',
        //     'date' => 'วันที่',
        //     'doc_title' => 'ขออนุมัติแต่งตั้งคณะกรรมการกำหนดรายละเอียดคุณลักษณะเฉพาะ',
        //     'org_name' => 'ชื่อโรงพยาบาล',
        //     'suptype' => 'ประเภททรัพย์สิน',
        //     'budget_year' => 'ปีงบประมาณ',
        //     'budget_amount' => 'วงเงินงบประมาณ',
        //     'budget_letter' => 'วงเงินงบประมาณเป็นตัวอักษร',
        //     'board' => 'คณะกรรมการกำหนดรายละเอียด',
        //     'emp_name' => 'เจ้าหน้าที่พนักงาน',
        //     'emp_position' => 'ตำแหน่งเจ้าหน้าที่',
        //     'emphead_name' => 'หัวหน้าเจ้าหน้าที่',
        //     'emphead_position' => 'ตำแหน่งหัวหน้าเจ้าหน้าที่',
        //     'director_name' => 'ผู้อำนวยการโรงพยาบาล',
        //     'org_name' => 'ชื่อโรงพยาบาล',
        // ]
        // ];

        // return $this->CreateFile($data);
    }

    public function actionPurchase_2()
    {
        $id = $this->request->get('id');
        $user = Yii::$app->user->id;
        $word_name = 'purchase_2.docx';
        $result_name = 'ขอความเห็นชอบและรายงานผล.docx';

        $data = [
            'word_name' => $word_name,
            'result_name' => $result_name,
            'items' => [
                'title' => 'ขอความเห็นชอบและรายงานผล',
                'org_name_full' => $this->GetInfo()['company_full'],
                'doc_number' => 'เลขหนังสือ',
                'date' => 'วันที่',
                'doc_title' => 'หัวข้ออนุมัติการแต่งตั้ง',
                'org_name' => $this->GetInfo()['company_name'],
                'director_name' => $this->GetInfo()['director_name'],
                'director_position' => $this->GetInfo()['director_position'],
                'province' => $this->GetInfo()['province'],
            ]
        ];
        return $this->CreateFile($data);
    }

    // ขออนุมัติจัดซื้อจัดจ้าง
    public function actionPurchase_3()
    {
        $id = $this->request->get('id');
        $model = $this->findOrderModel($id);
        $user = Yii::$app->user->id;
        $word_name = 'purchase_3.docx';
        $result_name = 'ขออนุมัติจัดซื้อจัดจ้าง.docx';
        $templateProcessor = new Processor(Yii::getAlias('@webroot') . '/msword/' . $word_name);  // เลือกไฟล์ template ที่เราสร้างไว้
        $templateProcessor->setValue('title', 'ขออนุมัติจัดซื้อจัดจ้าง');
        $templateProcessor->setValue('org_name_full', $this->GetInfo()['company_full']);
        $templateProcessor->setValue('director', $this->GetInfo()['director_name']);
        $templateProcessor->setValue('doc_number', 'เลขหนังสือ');
        $templateProcessor->setValue('date', 'เลขหนังสือ');
        $templateProcessor->setValue('org_name', $this->GetInfo()['company_name']);
        $templateProcessor->setValue('department', $model->getUserReq()['department']);
        $templateProcessor->setValue('asset_detail', 'รายละเอียดวัสดุ');
        $templateProcessor->setValue('comment', $model->data_json['comment']);
        $templateProcessor->setValue('emp_name', $model->viewLeaderUser()['fullname']);
        $templateProcessor->setValue('emp_position', $model->viewLeaderUser()['position_name']);
        $templateProcessor->setValue('director_name', $this->GetInfo()['director_name']);
        $templateProcessor->setValue('director_position', $this->GetInfo()['director_position']);

        $templateProcessor->cloneRow('item_name', count($model->ListOrderItems()));
        $i = 1;
        $num = 1;
        foreach ($model->ListOrderItems() as $item) {
            $templateProcessor->setValue('n#' . $i, AppHelper::thainumDigit($num++));
            $templateProcessor->setValue('item_name#' . $i, $item->product->title);
            $templateProcessor->setValue('qty#' . $i, number_format($item->price, 2));
            $templateProcessor->setValue('unit#' . $i, $item->product->data_json['unit']);
            $i++;
        }

        $templateProcessor->saveAs(Yii::getAlias('@webroot') . '/msword/results/' . $result_name);  // สั่งให้บันทึกข้อมูลลงไฟล์ใหม่
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => Html::a('<i class="fa-solid fa-cloud-arrow-down"></i> ดาวน์โหลดเอกสาร', Url::to(Yii::getAlias('@web') . '/msword/results/' . $result_name), ['class' => 'btn btn-primary text-center mb-3', 'target' => '_blank', 'onclick' => 'return closeModal()']),
                'content' => $this->renderAjax('word', ['filename' => $result_name]),
            ];
        } else {
            echo '<p>';
            echo Html::a('ดาวน์โหลดเอกสาร', Url::to(Yii::getAlias('@web') . '/msword/results/' . $result_name), ['class' => 'btn btn-info']);  // สร้าง link download
            echo '</p>';
            echo '<iframe src="https://docs.google.com/viewerng/viewer?url=' . Url::to(Yii::getAlias('@web') . '/msword/temp/asset_result.docx', true) . '&embedded=true"  style="position: absolute;width:100%; height: 100%;border: none;"></iframe>';
        }
    }

    public function actionPurchase_4()
    {
        $id = $this->request->get('id');
        $user = Yii::$app->user->id;

        $word_name = 'purchase_4.docx';
        $result_name = 'ขออนุมัติจัดซื้อจัดจ้าง.docx';
        $data = [
            'word_name' => $word_name,
            'result_name' => $result_name,
            'items' => [
                'number' => 'ลำดับ',
                'asset_detail' => 'รายละเอียดขอบเขตงาน',
                'amount' => 'จำนวน',
                'unit' => 'หน่วย',
                'priceunit' => 'ราคา/หน่วย',
                'sum' => 'ราคารวม',
            ]
        ];
        return $this->CreateFile($data);
    }

    public function actionPurchase_5()
    {
        $id = $this->request->get('id');
        $user = Yii::$app->user->id;

        $word_name = 'purchase_5.docx';
        $result_name = 'รายงานขอซื้อขอจ้าง.docx';
        $data = [
            'word_name' => $word_name,
            'result_name' => $result_name,
            'items' => [
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
            ]
        ];
        return $this->CreateFile($data);
    }

    public function actionPurchase_6()
    {
        $id = $this->request->get('id');
        $user = Yii::$app->user->id;

        $word_name = 'purchase_6.docx';
        $result_name = 'รายงานผลการตรวจรับพัสดุ.docx';
        $data = [
            'word_name' => $word_name,
            'result_name' => $result_name,
            'items' => [
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
            ]
        ];
        return $this->CreateFile($data);
    }

    public function actionPurchase_7()
    {
        $id = $this->request->get('id');
        $user = Yii::$app->user->id;
        $word_name = 'purchase_7.docx';
        $result_name = 'ประกาศผู้ชนะการเสนอราคา.docx';
        $data = [
            'word_name' => $word_name,
            'result_name' => $result_name,
            'items' => [
                'sup_type' => 'ประเภทพัสดุ',
                'org_name' => 'ชื่อโรงพยาบาล',
                'project_name' => 'ชื่อโปรเจค',
                'budget_name' => 'ประเภทงบประมาณ',
                'vendor_name' => 'ชื่อจำแทนจำหน่าย',
                'price' => 'ราคา',
                'price_character' => 'ราคาตัวอักษร',
                'director_name' => 'ผู้อำนวยการโรงพยาบาล',
                'date' => 'วันที่ประกาศ',
            ]
        ];
        return $this->CreateFile($data);
    }

    public function actionPurchase_8()
    {
        $id = $this->request->get('id');
        $user = Yii::$app->user->id;
        $word_name = 'purchase_8.docx';
        $result_name = 'ใบสั่งซื้อสั่งจ้าง.docx';
        $data = [
            'word_name' => $word_name,
            'result_name' => $result_name,
            'items' => [
                'title' => 'ใบสั่งซื้อสั่งจ้าง'
            ]
        ];
        return $this->CreateFile($data);
    }

    public function actionPurchase_9()
    {
        $id = $this->request->get('id');
        $user = Yii::$app->user->id;
        $word_name = 'purchase_8.docx';
        $result_name = 'ใบสั่งซื้อสั่งจ้าง.docx';
        $data = [
            'word_name' => $word_name,
            'result_name' => $result_name,
            'items' => [
                'title' => 'ใบตรวจรับการจัดซื้อ/จัดจ้าง'
            ]
        ];
        return $this->CreateFile($data);
    }

    public function actionPurchase_10()
    {
        $id = $this->request->get('id');
        $user = Yii::$app->user->id;
        $word_name = 'purchase_10.docx';
        $result_name = 'รายงานผลการตรวจรับ.docx';
        $data = [
            'word_name' => $word_name,
            'result_name' => $result_name,
            'items' => [
                'title' => 'รายงานผลการตรวจรับ'
            ]
        ];
        return $this->CreateFile($data);
    }

    public function actionPurchase_11()
    {
        $id = $this->request->get('id');
        $user = Yii::$app->user->id;
        $word_name = 'purchase_11.docx';
        $result_name = 'แบบแสดงความบริสุทธิ์ใจ.docx';
        $data = [
            'word_name' => $word_name,
            'result_name' => $result_name,
            'items' => [
                'title' => 'แบบแสดงความบริสุทธิ์ใจ'
            ]
        ];
        return $this->CreateFile($data);
    }

    public function actionPurchase_12()
    {
        $id = $this->request->get('id');
        $user = Yii::$app->user->id;
        $word_name = 'purchase_12.docx';
        $result_name = 'ขออนุมัติจ่ายเงินบำรุง.docx';
        $data = [
            'word_name' => $word_name,
            'result_name' => $result_name,
            'items' => [
                'title' => 'ขออนุมัติจ่ายเงินบำรุง'
            ]
        ];
        return $this->CreateFile($data);
    }

    // function สร้าง Word
    public function CreateFile($data)
    {
        $result_name = $data['result_name'];
        $templateProcessor = new Processor(Yii::getAlias('@webroot') . '/msword/' . $data['word_name']);  // เลือกไฟล์ template ที่เราสร้างไว้
        foreach ($data['items'] as $key => $value) {
            $templateProcessor->setValue($key, $value);
        }
        @unlink(Yii::getAlias('@webroot') . '/msword/results/' . $result_name);

        $templateProcessor->saveAs(Yii::getAlias('@webroot') . '/msword/results/' . $result_name);  // สั่งให้บันทึกข้อมูลลงไฟล์ใหม่
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => Html::a('<i class="fa-solid fa-cloud-arrow-down"></i> ดาวน์โหลดเอกสาร', Url::to(Yii::getAlias('@web') . '/msword/results/' . $result_name), ['class' => 'btn btn-primary text-center mb-3', 'target' => '_blank', 'onclick' => 'return closeModal()']),
                'content' => $this->renderAjax('word', ['filename' => $result_name]),
            ];
        } else {
            echo '<p>';
            echo Html::a('ดาวน์โหลดเอกสาร', Url::to(Yii::getAlias('@web') . '/msword/results/' . $result_name), ['class' => 'btn btn-info']);  // สร้าง link download
            echo '</p>';
            echo '<iframe src="https://docs.google.com/viewerng/viewer?url=' . Url::to(Yii::getAlias('@web') . '/msword/temp/asset_result.docx', true) . '&embedded=true"  style="position: absolute;width:100%; height: 100%;border: none;"></iframe>';
        }
    }
}
