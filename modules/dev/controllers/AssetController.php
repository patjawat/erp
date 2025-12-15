<?php

namespace app\modules\dev\controllers;

use Yii;
use yii\web\Controller;
use yii\base\DynamicModel;
use yii\data\ArrayDataProvider; // ⭐ เรียกใช้เพื่อทำระบบแบ่งหน้า

class AssetController extends Controller
{
    /**
     * แสดงหน้ารายการทรัพย์สิน (Table)
     * URL: index.php?r=dev/asset/index
     */
    public function actionIndex()
    {
        // 1. รับค่าคำค้นหา
        $search = Yii::$app->request->get('search');

        // 2. จำลองข้อมูล (Mock Data) ให้เหมือนในตารางที่คุณต้องการ
        $allData = [
            [
                'id' => 1, 
                'asset_code' => 'EQ-COM-66001', 
                'name' => 'เครื่องคอมพิวเตอร์ All-in-One', 
                'category' => 'คอมพิวเตอร์และอุปกรณ์', 
                'brand' => 'Dell', 
                'model' => 'Optiplex 7400',
                'dept' => 'ศูนย์คอมพิวเตอร์', 
                'received_date' => '2566-03-10', 
                'price' => 32500, 
                'status' => 'Normal'
            ],
            [
                'id' => 2, 
                'asset_code' => 'EQ-MED-65015', 
                'name' => 'เครื่องวัดความดันโลหิตแบบดิจิตอล', 
                'category' => 'ครุภัณฑ์การแพทย์', 
                'brand' => 'Omron', 
                'model' => 'HBP-1300',
                'dept' => 'ผู้ป่วยนอก (OPD)', 
                'received_date' => '2565-08-22', 
                'price' => 4500, 
                'status' => 'Repair'
            ],
            [
                'id' => 3, 
                'asset_code' => 'EQ-OFF-64055', 
                'name' => 'เก้าอี้สำนักงานพนักพิงสูง', 
                'category' => 'ครุภัณฑ์สำนักงาน', 
                'brand' => 'Modernform', 
                'model' => 'Series-X',
                'dept' => 'ฝ่ายบริหารงานทั่วไป', 
                'received_date' => '2564-01-15', 
                'price' => 3800, 
                'status' => 'Disposed'
            ],
            [
                'id' => 4, 
                'asset_code' => 'EQ-MED-67001', 
                'name' => 'เครื่องกระตุกหัวใจไฟฟ้า (AED)', 
                'category' => 'ครุภัณฑ์การแพทย์', 
                'brand' => 'Mindray', 
                'model' => 'BeneHeart C1A',
                'dept' => 'ห้องฉุกเฉิน (ER)', 
                'received_date' => '2567-01-20', 
                'price' => 45000, 
                'status' => 'Normal'
            ],
            // คุณสามารถเพิ่มข้อมูลจำลองเพิ่มตรงนี้ได้เรื่อยๆ เพื่อทดสอบ Pagination
        ];

        // 3. กรองข้อมูลถ้ามีการค้นหา (Search Logic)
        $filteredData = $allData;
        if (!empty($search)) {
            $filteredData = array_filter($allData, function ($item) use ($search) {
                return (stripos($item['name'], $search) !== false) || 
                       (stripos($item['asset_code'], $search) !== false);
            });
        }

        // 4. สร้าง DataProvider ส่งไปให้ View
        $dataProvider = new ArrayDataProvider([
            'allModels' => $filteredData,
            'pagination' => [
                'pageSize' => 10, // จำนวนรายการต่อหน้า
            ],
            'sort' => [
                'attributes' => ['id', 'name', 'price', 'received_date'],
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'search' => $search
        ]);
    }

    /**
     * แสดงหน้าดูรายละเอียด (Detail)
     */
    public function actionView($id = null)
    {
        // ในอนาคต: $model = Asset::findOne($id);
        return $this->render('view');
    }

    /**
     * หน้าเพิ่มรายการใหม่ (Create)
     */
    public function actionCreate()
    {
        // สร้าง Model จำลอง
        $model = new DynamicModel([
            'id', 'asset_code', 'category_id', 'name', 'brand', 'model', 
            'serial_no', 'location_id', 'received_date', 'price', 
            'life_year', 'supplier_id', 'budget_type', 'status', 'photo',
            'fsn_code', 'color', 'unit', 'checkin_date', 'warranty_date', 'responsible_person'
        ]);
        // กำหนดกฎการตรวจสอบข้อมูล (Validation Rules)
        $model->addRule(['name'], 'required');

        if ($this->request->isPost && $model->load($this->request->post())) {
            // จำลองการบันทึกสำเร็จ
            return $this->redirect(['index']); 
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * หน้าแก้ไขข้อมูล (Update)
     */
    public function actionUpdate($id)
    {
        // สร้าง Model จำลอง
        $model = new DynamicModel([
            'id', 'asset_code', 'category_id', 'name', 'brand', 'model', 
            'serial_no', 'location_id', 'received_date', 'price', 
            'life_year', 'supplier_id', 'budget_type', 'status', 'photo',
            'fsn_code', 'color', 'unit', 'checkin_date', 'warranty_date', 'responsible_person'
        ]);
        $model->addRule(['name'], 'required');

        // -- กำหนดข้อมูลเก่าให้โชว์ในฟอร์ม (Mock Data for Edit) --
        // (ในระบบจริงจะใช้ $model = Asset::findOne($id);)
        $model->id = $id;
        $model->asset_code = 'EQ-COM-66001';
        $model->name = 'เครื่องคอมพิวเตอร์ All-in-One';
        $model->brand = 'Dell';
        $model->model = 'Optiplex 7400';
        $model->serial_no = 'CN-0X5G9-74400';
        $model->price = 32500;
        $model->received_date = '2023-03-10';
        $model->life_year = 5;
        $model->status = 'Normal';
        $model->budget_type = 'งบลงทุน';
        $model->location_id = 'ศูนย์คอมพิวเตอร์';
        $model->category_id = '1';

        if ($this->request->isPost && $model->load($this->request->post())) {
            // จำลองการบันทึกสำเร็จ
            return $this->redirect(['index']); 
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * ลบรายการ (Delete)
     */
    public function actionDelete($id)
    {
        // ในอนาคต: $this->findModel($id)->delete();
        
        // จำลองว่าลบเสร็จแล้ว ให้รีเฟรชกลับไปหน้า Index
        return $this->redirect(['index']);
    }
}