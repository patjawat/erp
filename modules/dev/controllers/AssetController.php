<?php

namespace app\modules\dev\controllers;

use Yii;
use yii\web\Controller;
use yii\base\DynamicModel;
use yii\data\ArrayDataProvider;

class AssetController extends Controller
{
    /**
     * แสดงหน้ารายการทรัพย์สิน (Table)
     */
    public function actionIndex()
    {
        // 1. รับค่าคำค้นหาและประเภท
        $search = Yii::$app->request->get('search');
        $type = Yii::$app->request->get('type', 'equipment');

        // 2. ดึงข้อมูล Mock Data
        $allData = $this->getMockData($type);

        // 3. กรองข้อมูล (Search Logic)
        $filteredData = $allData;
        if (!empty($search)) {
            $filteredData = array_filter($allData, function ($item) use ($search) {
                $keyword = strtolower($search);
                return (stripos($item['asset_code'], $keyword) !== false) ||
                       (isset($item['name']) && stripos($item['name'], $keyword) !== false) ||
                       (isset($item['location']) && stripos($item['location'], $keyword) !== false);
            });
        }

        // 4. สร้าง DataProvider
        $dataProvider = new ArrayDataProvider([
            'allModels' => $filteredData,
            'pagination' => ['pageSize' => 10],
            'sort' => ['attributes' => ['id', 'price', 'received_date']],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'search' => $search,
            'currentType' => $type 
        ]);
    }

    /**
     * แสดงหน้าดูรายละเอียด (Detail) พร้อมข้อมูล Tab ต่างๆ
     */
    public function actionView($id = null)
    {
        // 1. จำลองการค้นหาข้อมูลจาก Database (รวมทุกประเภทเพื่อค้นหา)
        $allAssets = array_merge(
            $this->getMockData('land'),
            $this->getMockData('building'),
            $this->getMockData('equipment')
        );
        
        $foundItem = null;
        foreach ($allAssets as $item) {
            if ($item['id'] == $id) {
                $foundItem = $item;
                break;
            }
        }

        // กรณีไม่เจอ ID (หรือกดเข้าแบบไม่มี ID) ให้ใช้ตัวแรกเป็น Default
        if (!$foundItem && !empty($allAssets)) {
            $foundItem = $allAssets[0];
        }

        // 2. สร้าง Model
        $model = new DynamicModel($foundItem);
        
        // Define attributes เพื่อป้องกัน Error กรณี Field ไม่ครบ
        $possibleAttributes = [
            'id', 'type', 'asset_code', 'name', 'price', 'status', 'received_date', 
            'location', 'acquire_method', 'photo', 'budget_type', 'supplier', 'responsible_person',
            'deed_no', 'area', // Land
            'type_name', 'build_year', 'floors', // Building
            'brand', 'model', 'serial_no', 'life_year', 'category', 'dept', // Equipment
            'fsn_code', 'color', 'unit', 'checkin_date', 'warranty_date'
        ];
        
        foreach ($possibleAttributes as $attr) {
            if (!isset($foundItem[$attr])) {
                $model->defineAttribute($attr, null);
            }
        }

        // 3. จำลองข้อมูล "ประวัติการซ่อมบำรุง"
        $maintenanceData = [];
        if ($model->type != 'land') {
            $maintenanceData = [
                ['date' => '2566-12-10', 'issue' => 'เปลี่ยนแบตเตอรี่', 'desc' => 'เปลี่ยนแบตเตอรี่เนื่องจากเสื่อมสภาพ', 'provider' => 'ร้านอมร อิเล็คโทรนิคส์', 'cost' => 450, 'status' => 'Completed'],
                ['date' => '2567-02-20', 'issue' => 'อัปเกรด RAM', 'desc' => 'เพิ่ม RAM จาก 8GB เป็น 16GB', 'provider' => 'JIB Computer Group', 'cost' => 1500, 'status' => 'Completed'],
                ['date' => '2567-05-15', 'issue' => 'ซ่อมหน้าจอแสดงผล', 'desc' => 'หน้าจอแสดงผลติดๆ ดับๆ ส่งซ่อมศูนย์', 'provider' => 'ศูนย์บริการ Dell Thailand', 'cost' => 1200, 'status' => 'In Progress'],
            ];
        }

        // 4. จำลองข้อมูล "เอกสารแนบ"
        $filesData = [
            ['name' => 'คู่มือการใช้งาน.pdf', 'size' => '2.5 MB', 'date' => $model->received_date, 'type' => 'pdf'],
            ['name' => 'ใบรับประกัน.jpg', 'size' => '500 KB', 'date' => $model->received_date, 'type' => 'image'],
            ['name' => 'เอกสารตรวจรับ.pdf', 'size' => '1.2 MB', 'date' => $model->received_date, 'type' => 'pdf'],
        ];

        return $this->render('view', [
            'model' => $model,
            'maintenanceData' => $maintenanceData,
            'filesData' => $filesData
        ]);
    }

    /**
     * หน้าเพิ่มข้อมูล (Create)
     */
    public function actionCreate()
    {
        $type = Yii::$app->request->get('type', 'equipment');
        
        // สร้าง Model เปล่าๆ พร้อมฟิลด์ครบถ้วน
        $model = new DynamicModel([
            'asset_code', 'name', 'price', 'type', 'received_date', 'photo', 'status',
            'deed_no', 'area', 'location', 
            'type_name', 'build_year', 'floors', 
            'brand', 'model', 'serial_no', 'category_id', 'life_year', 'supplier_id', 'budget_type', 
            'fsn_code', 'color', 'unit', 'checkin_date', 'warranty_date', 'responsible_person'
        ]);
        $model->type = $type;

        if ($this->request->isPost) {
            $postType = Yii::$app->request->post('type', 'equipment');
            return $this->redirect(['index', 'type' => $postType]); 
        }

        return $this->render('create', [
            'model' => $model,
            'type' => $type,
        ]);
    }

    /**
     * หน้าแก้ไขข้อมูล (Update)
     */
    public function actionUpdate($id)
    {
        // 1. รับค่า type จาก URL
        $type = Yii::$app->request->get('type', 'equipment');

        // 2. ค้นหาข้อมูลจาก Mock Data ตาม ID และ Type
        $allAssets = $this->getMockData($type);
        $foundItem = null;
        
        foreach ($allAssets as $item) {
            if ($item['id'] == $id) {
                $foundItem = $item;
                break;
            }
        }

        // กรณีไม่เจอข้อมูล ให้สร้าง Array ว่างเพื่อป้องกัน Error
        if (!$foundItem) {
            $foundItem = [];
        }

        // 3. สร้าง Model และใส่ข้อมูลที่เจอลงไป
        $model = new DynamicModel($foundItem);
        
        // Define attributes ให้ครบทุกตัว
        $possibleAttributes = [
            'id', 'type', 'asset_code', 'name', 'price', 'status', 'received_date', 
            'location', 'acquire_method', 'photo', 'budget_type', 'supplier', 'responsible_person',
            'deed_no', 'area', // Land
            'type_name', 'build_year', 'floors', // Building
            'brand', 'model', 'serial_no', 'life_year', 'category', 'dept', // Equipment
            'fsn_code', 'color', 'unit', 'checkin_date', 'warranty_date', 'category_id', 'supplier_id'
        ];
        
        foreach ($possibleAttributes as $attr) {
            if (!isset($foundItem[$attr])) {
                $model->defineAttribute($attr, null);
            }
        }
        
        // บังคับกำหนด Type ให้ Model (สำคัญมากสำหรับ Form)
        $model->type = $type;

        if ($this->request->isPost) {
            $postType = Yii::$app->request->post('type', 'equipment');
            return $this->redirect(['index', 'type' => $postType]); 
        }

        return $this->render('update', [
            'model' => $model,
            'type' => $type,
        ]);
    }

    /**
     * ลบข้อมูล (Delete)
     */
    public function actionDelete($id)
    {
        $type = Yii::$app->request->get('type', 'equipment');
        return $this->redirect(['index', 'type' => $type]);
    }

    /**
     * ฟังก์ชันจำลองข้อมูล (Mock Data)
     */
    private function getMockData($type) {
        if ($type == 'land') {
            return [
                ['id' => 101, 'type' => 'land', 'asset_code' => 'L-2540-001', 'name' => 'ที่ดิน ต.ในเมือง', 'deed_no' => '12345', 'location' => 'ต.ในเมือง อ.เมือง', 'area' => '5-2-50 ไร่', 'acquire_method' => 'รับบริจาค', 'price' => 5000000, 'status' => 'Normal', 'received_date' => '2540-05-15'],
                ['id' => 102, 'type' => 'land', 'asset_code' => 'L-2555-002', 'name' => 'ที่ดิน ต.หนองหอย', 'deed_no' => '67890', 'location' => 'ต.หนองหอย อ.เมือง', 'area' => '2-0-0 ไร่', 'acquire_method' => 'ซื้อ', 'price' => 2500000, 'status' => 'Normal', 'received_date' => '2555-11-20'],
            ];
        } elseif ($type == 'building') {
            return [
                ['id' => 201, 'type' => 'building', 'asset_code' => 'B-2542-001', 'name' => 'อาคารผู้ป่วยนอก (OPD)', 'type_name' => 'ตึกคอนกรีต', 'location' => 'โซน A', 'build_year' => '2542', 'floors' => 4, 'area' => '1,200 ตร.ม.', 'price' => 15000000, 'status' => 'Normal', 'received_date' => '2542-01-01'],
            ];
        } else {
            return [
                ['id' => 1, 'type' => 'equipment', 'asset_code' => 'EQ-COM-66001', 'name' => 'เครื่องคอมพิวเตอร์ All-in-One', 'category' => 'คอมพิวเตอร์และอุปกรณ์', 'brand' => 'Dell', 'model' => 'Optiplex 7400', 'serial_no' => 'CN-0X5G9', 'location' => 'ศูนย์คอมพิวเตอร์', 'dept' => 'ศูนย์คอมพิวเตอร์', 'price' => 32500, 'status' => 'Normal', 'received_date' => '2566-03-10', 'life_year' => 5],
                ['id' => 2, 'type' => 'equipment', 'asset_code' => 'EQ-MED-65015', 'name' => 'เครื่องวัดความดันโลหิต', 'category' => 'ครุภัณฑ์การแพทย์', 'brand' => 'Omron', 'model' => 'HBP-1300', 'serial_no' => 'SN-12345', 'location' => 'ผู้ป่วยนอก (OPD)', 'dept' => 'ผู้ป่วยนอก (OPD)', 'price' => 4500, 'status' => 'Repair', 'received_date' => '2565-08-22', 'life_year' => 5],
                ['id' => 3, 'type' => 'equipment', 'asset_code' => 'EQ-OFF-64055', 'name' => 'เก้าอี้สำนักงานพนักพิงสูง', 'category' => 'ครุภัณฑ์สำนักงาน', 'brand' => 'Modernform', 'model' => 'Series-X', 'serial_no' => '-', 'location' => 'ฝ่ายบริหารงานทั่วไป', 'dept' => 'ฝ่ายบริหารงานทั่วไป', 'price' => 3800, 'status' => 'Disposed', 'received_date' => '2564-01-15', 'life_year' => 5],
            ];
        }
    }
}