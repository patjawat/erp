<?php

namespace app\modules\purchase\controllers;

use Yii;
use yii\web\Response;
use yii\db\Expression;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\am\models\Asset;
use app\modules\sm\models\Product;
use yii\web\NotFoundHttpException;
use app\modules\am\models\AssetItem;
use app\modules\hr\models\Employees;
use app\modules\am\models\AssetSearch;
use app\modules\purchase\models\Order;
use app\modules\sm\models\ProductSearch;
use app\modules\purchase\models\OrderSearch;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
// Microsoft Excel
use PhpOffice\PhpSpreadsheet\Style\Alignment;

/**
 * OrderController implements the CRUD actions for Order model.
 */
class OrderController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Order models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new OrderSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        if (!Yii::$app->user->can('purchase')) {
            $dataProvider->query->andFilterWhere(['created_by' => Yii::$app->user->id]);
        }
        $dataProvider->query->andFilterWhere(['name' => 'order']);
        if (!empty($searchModel->emp_id)) {
            $emp = Employees::findOne($searchModel->emp_id);
            if ($emp && $emp->user_id) {
                $dataProvider->query->andFilterWhere([
                    'or',
                    ['emp_id' => $searchModel->emp_id],
                    ['created_by' => $emp->user_id],
                ]);
            } else {
                $dataProvider->query->andFilterWhere(['emp_id' => $searchModel->emp_id]);
            }
        }
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'pr_number', $searchModel->q],
            ['like', 'pq_number', $searchModel->q],
            ['like', 'po_number', $searchModel->q],
            ['like', new Expression("JSON_EXTRACT(data_json, '$.total_price')"), $searchModel->q],
            ['like', new Expression("JSON_EXTRACT(data_json, '$.vendor_name')"), $searchModel->q],
        ]);
        $dataProvider->query->andFilterWhere(['=', new Expression("JSON_EXTRACT(data_json, '$.pq_purchase_type')"), $searchModel->pq_purchase_type]);
        $dataProvider->query->andFilterWhere(['=', new Expression("JSON_EXTRACT(data_json, '$.order_type_name')"), $searchModel->order_type_name]);
        $dataProvider->query->andFilterWhere(['=', new Expression("JSON_EXTRACT(data_json, '$.pq_budget_type')"), $searchModel->q_budget_type]);
        //ค้นหาช่วบงวันที่
        if ($searchModel->date_between) {
            try {
                $dateStart = AppHelper::convertToGregorian($searchModel->date_start);
                $dateEnd = AppHelper::convertToGregorian($searchModel->date_end);

                $jsonDateField = "DATE(JSON_UNQUOTE(JSON_EXTRACT(data_json, '$.\"{$searchModel->date_between}\"')))";

                $dataProvider->query->andFilterWhere([
                    'between',
                    new Expression($jsonDateField),
                    $dateStart,
                    $dateEnd,
                ]);
            } catch (\Throwable $th) {
                // handle error
            }
        }


        $dataProvider->query->orderBy(['created_at' => SORT_DESC]);

        /**
         * -------------------------
         * คำนวณผลรวมทั้งหมดด้วย clone query
         * -------------------------f
         */
        $sumTotal = 0;

        $sumQuery = clone $dataProvider->query;
        $sumQuery->orderBy(null); // ลบ order by ออก, สำคัญมาก

        foreach ($sumQuery->all() as $order) {
            $sumTotal += $order->calculateVAT()['priceAfterVAT'];
        }
        // การส่งออก
        $export = $this->request->get('export');

        if ($export == 1) {
            $dataProvider->pagination = false;
            return $this->ExportExcel($searchModel, $dataProvider);
        }


        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'sumTotal' => $sumTotal,
        ]);
    }


   private function ExportExcel($searchModel, $dataProvider)
{
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('รายงานจัดซื้อจัดจ้าง');

    // ตั้งค่า default font
    $spreadsheet->getDefaultStyle()
        ->getFont()
        ->setName('TH Sarabun New')
        ->setSize(16);

    // --------------------------------------------------
    // 1. หัวข้อรายงานหลัก (รวม A1:L2)
    // --------------------------------------------------
    $sheet->mergeCells('A1:L2');
    $sheet->setCellValue('A1', 'รายงานสรุปผลการดำเนินการจัดซื้อจัดจ้างตามพระราชบัญญัติการจัดซื้อจัดจ้างและการบริหารพัสดุภาครัฐ พ.ศ. 2560');

    $titleRange = 'A1:L2';
    $sheet->getStyle($titleRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle($titleRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
    $sheet->getStyle($titleRange)->getFont()->setBold(true)->setSize(18);
    $sheet->getStyle($titleRange)->getFill()->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FFFFD966'); // สีเหลืองอ่อน
    $sheet->getStyle($titleRange)->getBorders()->getAllBorders()
        ->setBorderStyle(Border::BORDER_THIN);


    // --------------------------------------------------
    // 2. หัวตาราง (เริ่มต้นที่แถวที่ 3)
    // --------------------------------------------------
    $startHeaderRow = 3; 

    // A3: วันที่ / เดือน / ปี
    $sheet->setCellValue('A' . $startHeaderRow, 'วันที่ / เดือน / ปี');

    // B3: งานที่จัดซื้อจัดจ้าง
    $sheet->setCellValue('B' . $startHeaderRow, 'งานที่จัดซื้อจัดจ้าง');

    // C3: วงเงินที่จะซื้อหรือจ้าง
    $sheet->setCellValue('C' . $startHeaderRow, 'วงเงินที่จะซื้อหรือจ้าง');

    // D3: ราคากลาง
    $sheet->setCellValue('D' . $startHeaderRow, 'ราคากลาง'); 

    // E3: วิธีซื้อหรือจ้าง
    $sheet->setCellValue('E' . $startHeaderRow, 'วิธีซื้อหรือจ้าง');

    // F3: รายชื่อผู้เสนอราคาและราคาที่เสนอ
    $sheet->setCellValue('F' . $startHeaderRow, 'รายชื่อผู้เสนอราคาและราคาที่เสนอ');

    // G3: ผู้ได้รับการคัดเลือกและราคาที่ตกลงซื้อหรือจ้าง
    $sheet->setCellValue('G' . $startHeaderRow, 'ผู้ได้รับการคัดเลือกและราคาที่ตกลงซื้อหรือจ้าง');

    // H3: เหตุผลที่คัดเลือกโดยสรุป
    $sheet->setCellValue('H' . $startHeaderRow, 'เหตุผลที่คัดเลือกโดยสรุป');

    // I3: เลขที่และวันที่ของหลักฐานที่ก่อให้เกิดภาระในการซื้อหรือจัดจ้าง
    // ทำการ Merge I3:L3
    $sheet->mergeCells('I3:L3'); 
    $sheet->setCellValue('I' . $startHeaderRow, 'เลขที่และวันที่ของหลักฐานที่ก่อให้เกิดภาระในการซื้อหรือจัดจ้าง');
    
    // ตั้งค่ารูปแบบหัวตาราง
    $headerRange = 'A3:L3'; 
    $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle($headerRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
    $sheet->getStyle($headerRange)->getFont()->setBold(true);
    $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FFCCE5FF');
    $sheet->getStyle($headerRange)->getBorders()->getAllBorders()
        ->setBorderStyle(Border::BORDER_THIN);

    // การจัดรูปแบบพิเศษสำหรับข้อความที่ยาวมาก (Wrap Text)
    $sheet->getStyle('F3')->getAlignment()->setWrapText(true);
    $sheet->getStyle('G3')->getAlignment()->setWrapText(true);
    $sheet->getStyle('I3')->getAlignment()->setWrapText(true);


    // --------------------------------------------------
    // 3. เนื้อหาตาราง (เริ่มต้นที่แถวที่ 4)
    // --------------------------------------------------
    $StartRow = 4; // เริ่มต้นแถวข้อมูลจริงที่แถว 4
    $row = 1;

    foreach ($dataProvider->getModels() as $key => $value) {
        $numRow = $StartRow++;

        // A: วันที่ / เดือน / ปี
        $sheet->setCellValue('A' . $numRow, isset($value->data_json['pr_create_date']) ? AppHelper::convertToThai($value->data_json['pr_create_date']) : '-');

        // B: งานที่จัดซื้อจัดจ้าง 
        $sheet->setCellValue('B' . $numRow, $value->assetType->title ?? '-'); // ต้องหาข้อมูลที่เหมาะสมมาใส่ (เช่น $value->project_name)

        // C: วงเงินที่จะซื้อหรือจ้าง (ราคารวม VAT)
        $sheet->setCellValue('C' . $numRow, $value->calculateVAT()['priceAfterVAT']);

        // D: ราคากลาง
        $sheet->setCellValue('D' . $numRow, ''); // ต้องหาข้อมูลที่เหมาะสมมาใส่ (เช่น $value->price_middle)

        // E: วิธีซื้อหรือจ้าง
        $sheet->setCellValue('E' . $numRow, $value->data_json['pq_purchase_type_name'] ?? '-');

        // F: รายชื่อผู้เสนอราคาและราคาที่เสนอ
        // ใช้ \n เพื่อขึ้นบรรทัดใหม่
        $sheet->setCellValue('F' . $numRow, ($value->vendor?->title ?? '-') . "\n" . "เสนอราคาเป็นเงิน " . number_format($value->calculateVAT()['priceAfterVAT'], 2) . ' บาท');
        
        // G: ผู้ได้รับการคัดเลือกและราคาที่ตกลงซื้อหรือจ้าง
        // ใช้ \n เพื่อขึ้นบรรทัดใหม่
        $sheet->setCellValue('G' . $numRow, ($value->vendor?->title ?? '-') . "\n" . "เสนอราคาเป็นเงิน " . number_format($value->calculateVAT()['priceAfterVAT'], 2) . ' บาท');

        // H: เหตุผลที่คัดเลือกโดยสรุป
        $sheet->setCellValue('H' . $numRow, ''); // ต้องหาข้อมูลที่เหมาะสมมาใส่

        // I: เลขที่หลักฐาน (กำหนดเองสำหรับคอลัมน์ I, J, K, L ที่อยู่ใต้หัวตารางเดียว)
        // I: เว้นว่าง (เพื่อจัดรูปแบบ)
        // J: เลขที่ทะเบียนคุม (pq_number)
        $sheet->setCellValue('J' . $numRow, $value->pq_number); 
        // K: เว้นว่าง (เพื่อจัดรูปแบบ)
        // L: วันที่ (po_date)
        $sheet->setCellValue('L' . $numRow, isset($value->data_json['po_date']) ? AppHelper::convertToThai($value->data_json['po_date'] ?? null) : ''); 
        
        // คอลัมน์ I จะถูกใช้สำหรับจัดตำแหน่งและเส้นขอบร่วมกับ J, K, L
    }

    // --------------------------------------------------
    // จัดตำแหน่งคอลัมน์
    // --------------------------------------------------
    $lastRow = $StartRow - 1;

    // A (วันที่) - กึ่งกลางแนวนอน/แนวตั้ง
    $sheet->getStyle("A4:A{$lastRow}")
        ->getAlignment()
        ->setVertical(Alignment::VERTICAL_CENTER)
        ->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // D, E (ราคากลาง, วิธีซื้อ) - กึ่งกลางแนวนอน/แนวตั้ง
    $sheet->getStyle("D4:E{$lastRow}")
        ->getAlignment()
        ->setVertical(Alignment::VERTICAL_CENTER)
        ->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // H, I, J, K, L (เหตุผล, หลักฐาน) - กึ่งกลางแนวนอน/แนวตั้ง
    // รวมช่วง H ถึง L เพื่อให้แน่ใจว่าทั้ง J และ L ที่มีข้อมูลถูกจัดตำแหน่ง
    $sheet->getStyle("H4:L{$lastRow}")
        ->getAlignment()
        ->setVertical(Alignment::VERTICAL_CENTER)
        ->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // B (งานที่จัดซื้อ) - ซ้ายแนวนอน / กึ่งกลางแนวตั้ง
    $sheet->getStyle("B4:B{$lastRow}")
        ->getAlignment()
        ->setVertical(Alignment::VERTICAL_CENTER)
        ->setHorizontal(Alignment::HORIZONTAL_LEFT);

    // C (วงเงิน) - ขวาแนวนอน / กึ่งกลางแนวตั้ง
    $styleC = $sheet->getStyle("C4:C{$lastRow}");
    $styleC->getAlignment()
        ->setVertical(Alignment::VERTICAL_CENTER)
        ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    $styleC->getFont()->setBold(true); // C: กำหนดให้เป็นตัวหนา

    // F, G = ซ้าย (ชื่อบริษัท/ราคา) และเปิด Wrap Text
    $sheet->getStyle("F4:G{$lastRow}")
        ->getAlignment()
        ->setHorizontal(Alignment::HORIZONTAL_LEFT)
        ->setVertical(Alignment::VERTICAL_CENTER)
        ->setWrapText(true);

    // J (เลขที่ทะเบียนคุม) - กำหนดให้เป็นตัวหนาเพิ่มเติม
    $sheet->getStyle("J4:J{$lastRow}")->getFont()->setBold(true);


    // --------------------------------------------------
    // ความกว้างคอลัมน์ (ปรับตามจำนวนและเนื้อหา)
    // --------------------------------------------------
    $widths = [
        'A' => 15, // วันที่ / เดือน / ปี
        'B' => 30, // งานที่จัดซื้อจัดจ้าง
        'C' => 15, // วงเงินที่จะซื้อหรือจ้าง
        'D' => 15, // ราคากลาง
        'E' => 15, // วิธีซื้อหรือจ้าง
        'F' => 35, // รายชื่อผู้เสนอราคาและราคาที่เสนอ
        'G' => 35, // ผู้ได้รับการคัดเลือกและราคาที่ตกลงซื้อหรือจ้าง
        'H' => 30, // เหตุผลที่คัดเลือกโดยสรุป
        'I' => 10, // ส่วนที่ถูกรวม (อาจใช้เป็นคอลัมน์ว่างสำหรับเลขที่/วันที่)
        'J' => 15, // เลขที่ทะเบียนคุม
        'K' => 5,  // คอลัมน์ว่างสำหรับเส้นแบ่ง
        'L' => 15, // วันที่หลักฐาน
    ];
    foreach ($widths as $col => $w) {
        $sheet->getColumnDimension($col)->setWidth($w);
    }

    // --------------------------------------------------
    // สร้างไฟล์ดาวน์โหลด
    // --------------------------------------------------
    try {
        $dateStart = $searchModel->date_start;
        $dateEnd = $searchModel->date_end;
    } catch (\Throwable $th) {
        $dateStart = '';
        $dateEnd = '';
    }


    $writer = new Xlsx($spreadsheet);
    $filePath = Yii::getAlias('@webroot') . '/downloads/purchas.xlsx';
    $writer->save($filePath);


    if (file_exists($filePath)) {
        return Yii::$app->response->sendFile($filePath);
    } else {
        throw new \yii\web\NotFoundHttpException('The file does not exist.');
    }
}

    /**
     * Displays a single Order model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {

        $me = UserHelper::GetEmployee();
        $model =  $this->findModel($id);
        \Yii::$app->session->set('order', $model);

        //ถ้าเป็นเจ้าหน้าที่จัดซื้อ
        if (Yii::$app->user->can('purchase')) {
            return $this->render('view', [
                'model' => $model,
            ]);
        } else {
            // ถ้าเป็น user ทั่วไป
            if (($model->created_by == Yii::$app->user->id)) {
                return $this->render('view', [
                    'model' => $model,
                ]);
            }
        }
    }

    /**
     * Creates a new Order model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Order();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Order model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            $order->data_json = ArrayHelper::merge($old, $newObj);
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }
    public function actionDiscount($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = $this->findModel($id);

        $old = $model->data_json;
        if ($this->request->isPost && $model->load($this->request->post())) {
            // $model->data_json = ArrayHelper::merge($model->data_json,$old);
            $model->data_json = ArrayHelper::merge($old, $model->data_json);
            $model->save();
            return [
                'status' => 'success',
                'container' => '#purchase-container',
                'model' => $model
            ];
        }
        return [
            'title' => $this->request->get('title'),
            'content' => $this->renderAjax('_form_discount', [
                'model' => $model,
            ]),
        ];
    }

    public function actionFormVat($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = $this->findModel($id);

        $old = $model->data_json;
        if ($this->request->isPost && $model->load($this->request->post())) {
            $model->data_json = ArrayHelper::merge($old, $model->data_json);
            $model->save();
            return [
                'status' => 'success',
                'container' => '#purchase-container',
                'model' => $model
            ];
        }
        return [
            'title' => $this->request->get('title'),
            'content' => $this->renderAjax('_form_vat', [
                'model' => $model,
            ]),
        ];
    }


    // อนุมัติตาม status
    public function actionConfirmStatus($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $status = $this->request->get('status');
        $thaiYear = substr(AppHelper::YearBudget(), 2);
        $model = $this->findModel($id);

        $oldObj = $model->data_json;
        if ($this->request->isPost) {
            $model->status = $status;
            // if ($model->load($this->request->post())) {
            $model->data_json = ArrayHelper::merge($oldObj, $model->data_json);
            // if ($model->status == 6) {
            // $model->code = \mdm\autonumber\AutoNumber::generate('PO-' . $thaiYear . '????');
            // }
            //$model->save(false);
            return [
                'status' => 'success',
                'container' => '#purchase-container',
                'model' => $model
            ];
            // } else {
            //     return false;
            // }
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('confirm_status', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('confirm_status', [
                'model' => $model,
            ]);
        }
    }

    public function actionProductList()
    {

        $order_id = $this->request->get('order_id');

        $model = Order::findOne($order_id);
        $searchModel = new ProductSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andfilterWhere(['!=', 'group_id', '0']);
        $dataProvider->query->andfilterWhere(['name' => 'asset_item']);

        if ($model->category_id == "") {
            $dataProvider->query->andFilterWhere(['category_id' => $searchModel->category_id]);
        } else {
            $dataProvider->query->andFilterWhere(['category_id' => $model->category_id]);
        }

         $q = trim($searchModel->q ?? '');
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'title', $q],
            ['like', 'code', $q],
            ['like', new \yii\db\Expression("JSON_EXTRACT(data_json, '\$.unit')"), $q],
            ['like', new \yii\db\Expression("JSON_EXTRACT(data_json, '\$.price')"), $q],
            ['like', new \yii\db\Expression("JSON_EXTRACT(data_json, '\$.fsn')"), $q],
        ]);

        $dataProvider->pagination->pageSize = 10;

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('product_list', [
                    'model' => $model,
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ]),
            ];
        } else {
            return $this->render('product_list', [
                'model' => $model,
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }

    // เพิ่มรายการวัสดุ
    public function actionAddItem()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $order_id = $this->request->get('order_id');
        $order = $this->findModel($order_id);
        $asset_item = $this->request->get('asset_item');
        $product = Product::find()
            ->andWhere(['name' => 'asset_item'])
            ->andWhere(['not in', 'group_id', ['0', null]])
            ->andWhere(['code' => $asset_item])
            ->one();

        $model = new Order([
            'group_id' => $product->group_id,
            'category_id' =>  $order_id,
            'name' => 'order_item',
            'asset_item' => $product->code,
            'qty' => 1,
            'price' => is_array($product->data_json['price'] ?? null)
            ? ($product->data_json['price']['value'] ?? 0)
            : ($product->data_json['price'] ?? 0),
            'asset_type' => $product->category_id,
            'pr_number' => $order->pr_number,
            'pq_number' => $order->pq_number,
            'po_number' => $order->po_number,
            'data_json' => [
                'asset_item_type_name' => $product->productType->title,
                'asset_item_type_name' => $product->productType->title,
                'asset_item_unit_name' => isset($product->data_json['unit']) ? $product->data_json['unit'] : null,
                'asset_item_name' => $product->title
            ]
        ]);


        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                if ($model->save()) {
                    // return $order->group_id;
                    //ถ้ายังไม่มีการจัดประเภทของ order
                    if ($order->group_id == null) {
                        $old = $order->data_json;
                        $newObj = $order->data_json = [
                            'order_type_name' => $product->productType->title,
                            'total_price' => $order->calculateVAT()['priceAfterVAT']
                        ];

                        $order->group_id = $product->group_id;
                        $order->category_id = $product->category_id;

                        $order->data_json = ArrayHelper::merge($old, $newObj);
                        $order->save(false);
                    }

                    return [
                        'status' => 'success',
                        'container' => '#purchase-container',
                    ];
                } else {
                    return $model->getErrors();
                }
            } else {
                return $model->getErrors();
                return false;
            }
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('_add_item', [
                    'model' => $model,
                    'product' => $product,
                    'order' => $order
                ]),
            ];
        } else {
            return $this->render('_add_item', [
                'model' => $model,
                'product' => $product,
                'order' => $order
            ]);
        }
    }

    public function actionUpdateItem($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = Order::findOne([
            'id' => $id,
            'name' => 'order_item'
        ]);
        $product = Product::findOne(['code' => $model->asset_item]);
        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                $model->save(false);

                //ถ้ามีการเปลี่ยนแปลงให้ update ราคารวมด้วย
                $order = $this->findModel($model->category_id);
                $old = $order->data_json;
                $newObj = $order->data_json = [
                    'total_price' => $order->calculateVAT()['priceAfterVAT']
                ];
                $order->data_json = ArrayHelper::merge($old, $newObj);
                $order->save();
                // End

                return [
                    'status' => 'success',
                    'container' => '#' . $model->name,
                ];
            } else {
                return false;
            }
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $product->title,
                'content' => $this->renderAjax('_add_item', [
                    'model' => $model,
                    'product' => $product
                ]),
            ];
        } else {
            return $this->render('_add_item', [
                'model' => $model,
                'product' => $product
            ]);
        }
    }

    public function actionDocument($id)
    {
        $model = $this->findModel($id);
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('document', ['model' => $model]),
            ];
        } else {
            return $this->render('document', ['model' => $model]);
        }
    }

    public function actionDeleteItem($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);

        if ($model->delete()) {
            $order = Order::findOne($model->category_id);
            if (count($order->ListOrderItems())  == 0) {
                $order->data_json =  ArrayHelper::merge($order->data_json, ['order_type_name' => '']);
                $order->group_id = NULL;
                $order->category_id = NULL;
                $order->save();
            }
            return [
                'status' => 'success',
                'container' => '#purchase-container',
            ];
        } else {
            return [
                'status' => 'error',
                'container' => '#purchase-container',
            ];
        }
    }

    /**
     * Deletes an existing Order model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */


    // ตรวจสอบความถูกต้อง
    public function actionCancelOrderValidator()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new Order();
        $requiredName = "ต้องระบุเหตุผล";
        if ($this->request->isPost && $model->load($this->request->post())) {

            if (isset($model->data_json['cancel_order_note'])) {
                $model->data_json['cancel_order_note'] == "" ? $model->addError('data_json[cancel_order_note]', $requiredName) : null;
            }
        }
        foreach ($model->getErrors() as $attribute => $errors) {
            $result[\yii\helpers\Html::getInputId($model, $attribute)] = $errors;
        }
        if (!empty($result)) {
            return $this->asJson($result);
        }
    }

    public function actionCancelOrder($id)
    {

        $model = $this->findModel($id);

        $oldObj = $model->data_json;
        if ($model->load($this->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $model->data_json = ArrayHelper::merge($oldObj, $model->data_json);
            $model->save(false);
            return [
                'status' => 'success',
                'container' => '#purchase-container',
                'model' => $model
            ];
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $model->getMe('ยกเลิอกรายการนี้')['avatar'],
                'content' => $this->renderAjax('_form_cancel_order', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('_form_cancel_order', [
                'model' => $model,
            ]);
        }

        //  $model = $this->findModel($id);
        // $model->deleted_at = Date('Y-m-d H:i:s');
        // $model->deleted_by =Yii::$app->user->id;
        // $model->save();
    }



    public  function actionRegisterAsset($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = Order::findOne($id);
        $owner = Employees::findOne(['user_id' => $model->created_by]);

        foreach ($model->ListOrderItems() as $item) {
            for ($x = 1; $x <= $item->qty; $x++) {
                // $assetItem = AssetItem::find()->where(['code' => $model->asset_item,'name' => 'asset_item','group_id' => 3])->one();
                $assetItem = AssetItem::find()->where(['code' => $item->asset_item, 'name' => 'asset_item', 'group_id' => 3])->one();
                $newAsset = new Asset([
                    'fsn_auto' => '1',
                    'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
                    "asset_group" =>  $model->group_id,
                    "asset_item" =>  $item->asset_item,
                    "code" =>  "5130-007-0004/67.02",
                    "fsn_number" =>  null,
                    "qty" =>  null,
                    "receive_date" =>  AppHelper::convertToGregorian($model->data_json['gr_date']),
                    "price" =>  $item->price,
                    "purchase" =>  $model->data_json['pq_purchase_type'],
                    "department" =>  $owner->department,
                    "owner" =>  $owner->cid,
                    "life" =>  null,
                    "on_year" =>  $model->thai_year,
                    "dep_id" =>  null,
                    "depre_type" =>  null,
                    "budget_year" =>  null,
                    "asset_status" =>  "1",
                    "data_json" =>  [
                        "detail" =>  null,
                        "fsn_old" =>  "",
                        "vendor_id" =>  $model->vendor_id,
                        "asset_name" =>  $assetItem->title,
                        "asset_type" =>  $assetItem->category_id,
                        "method_get" =>  $model->data_json['pq_method_get'],
                        "owner_name" =>  $owner->fullname,
                        "budget_type" =>  $model->data_json['pq_budget_type'],
                        // "decine_text"=>  "ครุภัณฑ์ไฟฟ้าและวิทยุ ",
                        // "decine_text"=>  $assetItem->AssetType->title,
                        // "decine_type"=>  "7",
                        "expire_date" =>  "",
                        "status_name" =>  "ปกติ",
                        "asset_option" =>  "",
                        "asset_group_name" =>  "ครุภัณฑ์",
                        "department_name_old" =>  "",
                        "po_number" =>  $model->po_number
                    ],
                    "device_items" =>  [""],
                ]);
                $newAsset->save(false);
            }
        }
        $model->status = 5;
        if ($model->save(false)) {
            return $this->redirect(['view', 'id' => $model->id]);
        }
    }


    public function actionViewAsset($po_number)
    {

        $searchModel = new AssetSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->leftJoin('categorise at', 'at.code=asset.asset_item');
        $dataProvider->query->andFilterWhere(['like', new Expression("JSON_EXTRACT(asset.data_json, '$.po_number')"), $po_number]);

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => 'รายการจากใบสั่งซื้อเลขที่ : ' . $po_number,
                'content' => $this->renderAjax('@app/modules/am/views/asset/show/grid', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ]),
            ];
        } else {
            return $this->render('@app/modules/am/views/asset/show/grid', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        return $this->redirect(['index']);
    }

    /**
     * Finds the Order model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Order the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Order::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
