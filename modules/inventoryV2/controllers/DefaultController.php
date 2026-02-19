<?php

namespace app\modules\inventoryV2\controllers;

use yii\web\Controller;

/**
 * Default controller for the `inventory-v2` module
 */
class DefaultController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

        public function actionMainDashboard()
    {
        return $this->render('main_dashboard');
    }

    //หน้ารับพัสดุเข้าคลัง
    public function actionStockInbound()
    {
        return $this->render('stock_inbound');
    }
    //หน้าบันทึกทะเบียนรับพัสดุ
    public function actionInboundRegistry()
    {
        return $this->render('inbound_registry');
    }
     //หน้าจ่ายพัสดุ รายการใบเบิกจากคลังย่อย
    public function actionStockIssueList()
    {
        return $this->render('stock_issue_list');
    }


    //หน้าปรับปรุงสต็อก
    public function actionStockAdjustment()
    {
        return $this->render('stock_adjustment');
    }

        //จัดการรายการวัสดุ
    public function actionProductList()
    {
        return $this->render('product_list');
    }

        public function actionStockCard()
    {
        return $this->render('stock_card');
    }

            public function actionSetting()
    {
        return $this->render('setting');
    }

    //#### ส่วนคลังย่อย
    //หน้าเบิกพัสดุจากคลังหลัก
    public function actionRequisition()
    {
        return $this->render('requisition');
    }
   
    public function actionStockIssue()
    {
        return $this->render('stock_issue');
    }
    public function actionSubStockIssue()
    {
        return $this->render('sub_stock_issue');
    }
    public function actionSubStockDashboard()
    {
        return $this->render('sub_stock_dashboard',[
            'departmentName' => 'แผนกไอที / ซ่อมบำรุง',
        ]);
    }
    public function actionSubStockReceiving()
    {
        return $this->render('sub_stock_receiving');
    }

    /**
     * หน้าเมนูนำทางระบบ - แสดงขั้นตอนการทำงาน
     */
    public function actionNavigation()
    {
        return $this->render('navigation');
    }
}
