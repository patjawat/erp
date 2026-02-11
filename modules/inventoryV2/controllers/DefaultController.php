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
    //หน้าปรับปรุงสต็อก
    public function actionStockAdjustment()
    {
        return $this->render('stock_adjustment');
    }

    //หน้าเบิกพัสดุ
    public function actionRequisition()
    {
        return $this->render('requisition');
    }
    //หน้าจ่ายพัสดุ
    public function actionStockIssueList()
    {
        return $this->render('stock_issue_list');
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
}
