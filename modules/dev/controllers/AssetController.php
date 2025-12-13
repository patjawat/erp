<?php

namespace app\modules\dev\controllers;

use Yii;
use yii\web\Controller;
use yii\base\DynamicModel; // เรียกใช้ Model จำลอง (เพราะยังไม่มี Database จริง)

class AssetController extends Controller
{
    /**
     * แสดงหน้ารายการทรัพย์สิน (Table)
     * URL: index.php?r=dev/asset/index
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * แสดงหน้าดูรายละเอียด (Detail)
     * URL: index.php?r=dev/asset/view&id=1
     */
    public function actionView($id = null)
    {
        // ในอนาคต: $model = Asset::findOne($id);
        return $this->render('view');
    }

    /**
     * ⭐ สร้างใหม่: สำหรับหน้า "เพิ่มทรัพย์สิน"
     * URL: index.php?r=dev/asset/create
     */
    public function actionCreate()
    {
        // จำลอง Model เปล่าๆ ขึ้นมาเพื่อให้ Form ใช้งานได้
        $model = new DynamicModel([
            'id',
            'asset_code', 'category_id', 'name', 'brand', 'model', 
            'serial_no', 'location_id', 'received_date', 'price', 
            'life_year', 'supplier_id', 'budget_type', 'status', 'photo'
        ]);
        $model->addRule(['asset_code', 'name'], 'required');

        // ถ้ารับค่าจากฟอร์ม (POST)
        if ($this->request->isPost && $model->load($this->request->post())) {
            // โค้ดบันทึกข้อมูลจะอยู่ตรงนี้...
            // บันทึกเสร็จแล้วเด้งไปหน้า view
            return $this->redirect(['view', 'id' => 1]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * ⭐ แก้ไขข้อมูล: สำหรับปุ่มสีเหลือง
     * URL: index.php?r=dev/asset/update&id=1
     */
    public function actionUpdate($id)
    {
        // สร้าง Model จำลอง (Mockup)
        $model = new DynamicModel([
            'id', 'asset_code', 'category_id', 'name', 'brand', 'model', 
            'serial_no', 'location_id', 'received_date', 'price', 
            'life_year', 'supplier_id', 'budget_type', 'status', 'photo'
        ]);
        $model->addRule(['asset_code', 'name'], 'required');

        // -- กำหนดข้อมูลตัวอย่าง (Mock Data) --
        $model->id = $id; // ⭐ ต้องมีบรรทัดนี้ ไม่งั้น Error Unknown Property 'id'
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

        // ถ้ารับค่าจากฟอร์ม (POST)
        if ($this->request->isPost && $model->load($this->request->post())) {
            // โค้ดบันทึกการแก้ไข...
            return $this->redirect(['view', 'id' => $id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }
}