<?php

namespace app\modules\dev\controllers;

use yii\web\Controller;

class AssetController extends Controller
{
    /**
     * แสดงหน้ารายการทรัพย์สิน (Table)
     * URL: index.php?r=dev/asset/index
     */
    public function actionIndex()
    {
        // เรียกไฟล์ views/asset/index.php
        return $this->render('index');
    }

    /**
     * แสดงหน้าดูรายละเอียด (Detail)
     * URL: index.php?r=dev/asset/view&id=1
     * @param int $id รหัสทรัพย์สิน (ตอนนี้ยังไม่ได้ใช้จริง แต่ใส่เผื่อไว้ตามมาตรฐาน)
     */
    public function actionView($id = null)
    {
        // ในอนาคตเราจะเขียนโค้ดดึงข้อมูลจาก Database ตรงนี้
        // $model = Asset::findOne($id);
        
        // ตอนนี้เรียกไฟล์ views/asset/view.php เฉยๆ
        return $this->render('view');
    }
}