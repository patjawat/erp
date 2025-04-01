<?php

/**
 * @see http://www.yiiframework.com/
 *
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\helpers\BaseConsole;

/**
 * update แก้ไขรายการตำแหน่ให้เป็นล่าสุด.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 *
 * @since 2.0
 */
class UpdateTableController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     *
     * @return int Exit code
     */
    public function actionIndex()
    {
        // if (BaseConsole::confirm("Are you sure?")) {
        // SQL query ที่ต้องการรัน
        $sqlUpdateAuthItems = 'INSERT INTO auth_item (type, name,description) VALUES (:type, :name, :description) ON DUPLICATE KEY UPDATE name = VALUES(name), type = VALUES(type), description = VALUES(description)';
        $sqlUpdateChild = 'INSERT INTO auth_item_child (parent, child) VALUES (:parent, :child) ON DUPLICATE KEY UPDATE child = VALUES(child), parent = VALUES(parent)';

        // update route
        foreach ($this->RouteList() as $authItem) {
            \Yii::$app->db->createCommand($sqlUpdateAuthItems, [
                ':name' => $authItem['name'],
                ':type' => $authItem['type'],
                ':description' => $authItem['description'],
            ])->execute();
        }
        echo "update Route Success\n";

        // update route
        foreach ($this->ChildList() as $childItem) {
            \Yii::$app->db->createCommand($sqlUpdateChild, [
                ':parent' => $childItem['parent'],
                ':child' => $childItem['child'],
            ])->execute();
        }
        echo "update Child Success\n";

        // }
    }

    public static function RouteList()
    {
        return [
            ['name' => 'admin', 'type' => 1, 'description' => 'ผู้ดูแลระบบ'],
            ['name' => 'computer', 'type' => 1, 'description' => 'ศูนย์คอม'],
            ['name' => 'computer_ma', 'type' => 1, 'description' => 'หัวหน้าศูนย์คอม'],
            ['name' => 'director', 'type' => 1, 'description' => 'ผู้อำนวยการ'],
            ['name' => 'hr', 'type' => 1, 'description' => 'เจ้าหน้าที่ฝ่ายบุคคล'],
            ['name' => 'medical', 'type' => 1, 'description' => 'ศูนย์เครื่องมือแพทย์'],
            ['name' => 'medical_ma', 'type' => 1, 'description' => ''],
            ['name' => 'purchase', 'type' => 1, 'description' => 'เจ้าหน้าที่จัดซื้อ'],
            ['name' => 'technician', 'type' => 1, 'description' => 'งานซ่อมบำรุง'],
            ['name' => 'technician_ma', 'type' => 1, 'description' => 'หัวหน้างานซ่อมบำรุง'],
            ['name' => 'user', 'type' => 1, 'description' => 'ผู้ใช้งานทั่วไป'],
            ['name' => 'warehouse', 'type' => 1, 'description' => 'ผู้จัดการคลัง'],
            ['name' => 'branch', 'type' => 1, 'description' => 'สาขา'],
            ['name' => 'inventory', 'type' => 1, 'description' => 'ระบบคลัง'],
            ['name' => 'sm', 'type' => 1, 'description' => 'บริหารพัสดุ'],
            ['name' => 'leave', 'type' => 1, 'description' => 'ระบบลา'],
            ['name' => 'document', 'type' => 1, 'description' => 'ระบบสารบรรณ'],
            ['name' => 'asset', 'type' => 1, 'description' => 'ระบบทรัพย์สิน'],
            ['name' => 'driver', 'type' => 1, 'description' => 'ระบบยานพาหนะ'],
            ['name' => 'meeting', 'type' => 1, 'description' => 'ระบบห้องประชุม'],
            ['name' => '/*', 'type' => 2, 'description' => ''],
            

            // ยานพาหนะ
            ['name' => '/booking/driver/*', 'type' => 2,'description' =>''],
            // ห้องประชุม
            ['name' => '/booking/meeting/*', 'type' => 2,'description' =>''],

            ['name' => '/dms/*', 'type' => 2,'description' =>''],
            ['name' => '/dms/dashboard', 'type' => 2,'description' =>'Dashboard'],
            ['name' => '/dms/documents', 'type' => 2,'description' =>'หนังสือรับ'],
            ['name' => '/dms/documents/create', 'type' => 2,'description' =>'ออกเลขหนังสือ'],
            ['name' => '/dms/documents/update', 'type' => 2,'description' =>'แก้ไขหนังสือ'],
            ['name' => '/dms/documents/view', 'type' => 2,'description' =>'แสดงหนังสือ'],
            ['name' => '/dms/documents/delete', 'type' => 2,'description' =>'ลบหนังสือ'],
            ['name' => '/am/asset/*', 'type' => 2,'description' =>''],
            ['name' => '/am/asset/depreciation', 'type' => 2,'description' =>''],
            ['name' => '/am/asset/index', 'type' => 2,'description' =>''],
            ['name' => '/am/asset/qrcode', 'type' => 2,'description' =>''],
            ['name' => '/am/asset/view', 'type' => 2,'description' =>''],
            ['name' => '/am/asset/update', 'type' => 2,'description' =>''],
            ['name' => '/am/default/index', 'type' => 2,'description' =>''],
            ['name' => '/depdrop/*', 'type' => 2,'description' =>''],
            ['name' => '/employees/*', 'type' => 2,'description' =>''],
            ['name' => '/helpdesk/computer/*', 'type' => 2,'description' =>''],
            ['name' => '/helpdesk/default/repair-select', 'type' => 2,'description' =>''],
            ['name' => '/helpdesk/general/*', 'type' => 2,'description' =>''],
            ['name' => '/helpdesk/medical/*', 'type' => 2,'description' =>''],
            ['name' => '/helpdesk/repair/create', 'type' => 2,'description' =>''],
            ['name' => '/helpdesk/repair/timeline', 'type' => 2,'description' =>''],
            ['name' => '/helpdesk/stock/*', 'type' => 2,'description' =>''],
            ['name' => '/helpdesk/repair/summary', 'type' => 2,'description' =>''],
            ['name' => '/helpdesk/repair/user-request-order', 'type' => 2,'description' =>''],
            ['name' => '/helpdesk/repair/user-job', 'type' => 2,'description' =>''],
            ['name' => '/helpdesk/repair/list-accept', 'type' => 2,'description' =>''],
            ['name' => '/helpdesk/repair/view', 'type' => 2,'description' =>''],
            ['name' => '/helpdesk/repair/accept-job', 'type' => 2,'description' =>''],
            ['name' => '/helpdesk/repair/switch-group', 'type' => 2,'description' =>''],
            ['name' => '/helpdesk/repair/cancel-job', 'type' => 2,'description' =>''],
            ['name' => '/helpdesk/repair/add-part', 'type' => 2,'description' =>''],
            ['name' => '/helpdesk/repair/update', 'type' => 2,'description' =>''],
            ['name' => '/helpdesk/repair/rating', 'type' => 2,'description' =>''],
            ['name' => '/helpdesk/repair/*', 'type' => 2,'description' =>''],
            ['name' => '/hr/default/index', 'type' => 2,'description' =>''],
            ['name' => '/hr/employees/view', 'type' => 2,'description' =>''],
            //Router
            ['name' => '/hr/leave/*', 'type' => 2,'description' =>''],
            ['name' => '/hr/leave/create', 'type' => 2,'description' =>''],
            ['name' => '/hr/leave/update', 'type' => 2,'description' =>''],
            ['name' => '/hr/leave/view', 'type' => 2,'description' =>''],
            ['name' => '/hr/leave/view-history', 'type' => 2,'description' =>''],
            ['name' => '/inventory/*', 'type' => 2,'description' =>''],
            // ['name' => '/inventory/default/product-summary', 'type' => 2,'description' =>''],
            // ['name' => '/inventory/default/warehouse', 'type' => 2,'description' =>''],
            // ['name' => '/inventory/stock-out/index', 'type' => 2,'description' =>''],
            // ['name' => '/inventory/stock-out/stock', 'type' => 2,'description' =>''],
            // ['name' => '/inventory/stock/view-chart-total', 'type' => 2,'description' =>''],
            // ['name' => '/inventory/stock/warehouse', 'type' => 2,'description' =>''],
            // ['name' => '/inventory/warehouse/index', 'type' => 2,'description' =>''],
            // ['name' => '/inventory/warehouse/list-order-request', 'type' => 2,'description' =>''],
            // ['name' => '/inventory/warehouse/view', 'type' => 2,'description' =>''],
            // ['name' => '/inventory/main-stock/store', 'type' => 2,'description' =>''],
            // ['name' => '/inventory/main-stock/create', 'type' => 2,'description' =>''],
            // ['name' => '/inventory/stock-order', 'type' => 2,'description' =>''],
            ['name' => '/me/*', 'type' => 2,'description' =>''],
            ['name' => '/profile/*', 'type' => 2,'description' =>''],
            
            //ขัดซื้อ
            ['name' => '/purchase/*', 'type' => 2,'description' =>''],
            ['name' => '/purchase/document/download-file', 'type' => 2,'description' =>''],
            ['name' => '/purchase/gr-order/update', 'type' => 2,'description' =>''],
            ['name' => '/purchase/order-item/committee', 'type' => 2,'description' =>''],
            ['name' => '/purchase/order-item/committee-detail', 'type' => 2,'description' =>''],
            ['name' => '/purchase/order-item/create', 'type' => 2,'description' =>''],
            ['name' => '/purchase/order-item/delete', 'type' => 2,'description' =>''],
            ['name' => '/purchase/order-item/update', 'type' => 2,'description' =>''],
            ['name' => '/purchase/order/delete-item', 'type' => 2,'description' =>''],
            ['name' => '/purchase/order/document', 'type' => 2,'description' =>''],
            ['name' => '/purchase/order/product-list', 'type' => 2,'description' =>''],
            ['name' => '/purchase/order/update-item', 'type' => 2,'description' =>''],
            ['name' => '/purchase/order/view', 'type' => 2,'description' =>''],
            ['name' => '/purchase/po-order/create', 'type' => 2,'description' =>''],
            ['name' => '/purchase/po-order/index', 'type' => 2,'description' =>''],
            ['name' => '/purchase/po-order/update', 'type' => 2,'description' =>''],
            ['name' => '/purchase/pq-order/index', 'type' => 2,'description' =>''],
            ['name' => '/purchase/pq-order/update', 'type' => 2,'description' =>''],
            ['name' => '/purchase/pr-order/checker-confirm', 'type' => 2,'description' =>''],
            ['name' => '/purchase/pr-order/create', 'type' => 2,'description' =>''],
            ['name' => '/purchase/pr-order/director-confirm', 'type' => 2,'description' =>''],
            ['name' => '/purchase/pr-order/index', 'type' => 2,'description' =>''],
            ['name' => '/purchase/pr-order/leader-confirm', 'type' => 2,'description' =>''],
            ['name' => '/purchase/pr-order/update', 'type' => 2,'description' =>''],
            ['name' => '/settings/*', 'type' => 2,'description' =>''],
            ['name' => '/site/*', 'type' => 2,'description' =>''],
            
            ['name' => '/sm/*', 'type' => 2,'description' =>''],
            ['name' => '/sm/default/accept-pr-order', 'type' => 2,'description' =>''],
            ['name' => '/sm/default/budget-chart', 'type' => 2,'description' =>''],
            ['name' => '/sm/default/chart', 'type' => 2,'description' =>''],
            ['name' => '/sm/default/index', 'type' => 2,'description' =>''],
            ['name' => '/sm/default/pq-order', 'type' => 2,'description' =>''],
            ['name' => '/sm/default/pr-order', 'type' => 2,'description' =>''],
            ['name' => '/usermanager/*', 'type' => 2,'description' =>''],
            ['name' => '/warehouse/*', 'type' => 2,'description' =>''],
            ['name' => 'purchase/po-order/index', 'type' => 2,'description' =>''],
            ['name' => '/ms-word/*', 'type' => 2,'description' =>''],
        ];
    }

    //กำหนดสิทธิให้กับแต่ละกลุ่ม
    public static function ChildList()
    {
        return [
            ['child' => '/*', 'parent' => 'admin'],
            ['child' => 'document', 'parent' => 'admin'],
            ['child' => 'computer', 'parent' => 'admin'],
            ['child' => 'computer_ma', 'parent' => 'admin'],
            ['child' => 'director', 'parent' => 'admin'],
            ['child' => 'hr', 'parent' => 'admin'],
            ['child' => 'medical', 'parent' => 'admin'],
            ['child' => 'medical_ma', 'parent'  => 'admin'],
            ['child' => 'purchase', 'parent' => 'admin'],
            ['child' => 'technician', 'parent'  => 'admin'],
            ['child' => 'technician_ma', 'parent' => 'admin'],
            ['child' => 'user', 'parent' => 'admin'],
            ['child' => 'warehouse', 'parent'  => 'admin'],
            ['child' => 'branch', 'parent'  => 'admin'],
            ['child' => 'inventory', 'parent'  => 'admin'],
            ['child' => 'sm', 'parent'  => 'admin'],
            ['child' => 'leave', 'parent'  => 'admin'],
            ['child' => 'document', 'parent' => 'admin'],
            ['child' => 'asset', 'parent'  => 'admin'],
            ['child' => 'driver', 'parent'  => 'admin'],
            ['child' => 'meeting', 'parent'  => 'admin'],

            // ยานพาหนะ
            ['child' => '/booking/driver/*', 'parent' => 'driver'],
            // ระบบห้องประชุม
            ['child' => '/booking/meeting/*', 'parent' => 'meeting'],
            
            // ระบบสารบรรณ
            ['child' => '/dms/*', 'parent' => 'document'],
            
            ['child' => '/am/asset/*', 'parent' => 'user'],
            ['child' => '/am/asset/depreciation', 'parent' => 'user'],
            ['child' => '/am/asset/index', 'parent' => 'user'],
            ['child' => '/am/asset/qrcode', 'parent' => 'user'],
            ['child' => '/am/asset/view', 'parent' => 'user'],
            ['child' => '/am/asset/update', 'parent' => 'user'],
            ['child' => '/am/default/index', 'parent' => 'user'],
            ['child' => '/depdrop/*', 'parent' => 'user'],
            // ซ่อมบำรุง
            ['child' => '/helpdesk/computer/*', 'parent' => 'computer'],
            ['child' => '/helpdesk/default/repair-select', 'parent' => 'user'],
            ['child' => '/helpdesk/general/*', 'parent' => 'technician'],
            ['child' => '/helpdesk/medical/*', 'parent' => 'medical'],
            ['child' => '/helpdesk/repair/create', 'parent' => 'user'],
            ['child' => '/helpdesk/repair/timeline', 'parent' => 'user'],
            ['child' => '/helpdesk/repair/view', 'parent' => 'user'],
            ['child' => '/helpdesk/repair/rating', 'parent' => 'user'],
            ['child' => '/helpdesk/repair/summary', 'parent' => 'computer'],
            ['child' => '/helpdesk/repair/user-request-order', 'parent' => 'computer'],
            ['child' => '/helpdesk/repair/user-job', 'parent' => 'computer'],
            ['child' => '/helpdesk/repair/list-accept', 'parent' => 'computer'],
            ['child' => '/helpdesk/repair/view', 'parent' => 'computer'],
            ['child' => '/helpdesk/repair/accept-job', 'parent' => 'computer'],
            ['child' => '/helpdesk/repair/switch-group', 'parent' => 'computer'],
            ['child' => '/helpdesk/repair/cancel-job', 'parent' => 'computer'],
            ['child' => '/helpdesk/repair/add-part', 'parent' => 'computer'],
            ['child' => '/helpdesk/repair/update', 'parent' => 'computer'],
            ['child' => '/helpdesk/stock/*', 'parent' => 'computer'],
            ['child' => '/helpdesk/repair/*', 'parent' => 'computer'],
            ['child' => '/helpdesk/repair/summary', 'parent' => 'technician'],
            ['child' => '/helpdesk/repair/user-request-order', 'parent' => 'technician'],
            ['child' => '/helpdesk/repair/user-job', 'parent' => 'technician'],
            ['child' => '/helpdesk/repair/list-accept', 'parent' => 'technician'],
            ['child' => '/helpdesk/repair/view', 'parent' => 'technician'],
            ['child' => '/helpdesk/repair/accept-job', 'parent' => 'technician'],
            ['child' => '/helpdesk/repair/switch-group', 'parent' => 'technician'],
            ['child' => '/helpdesk/repair/cancel-job', 'parent' => 'technician'],
            ['child' => '/helpdesk/repair/add-part', 'parent' => 'technician'],
            ['child' => '/helpdesk/repair/update', 'parent' => 'technician'],
            ['child' => '/helpdesk/stock/*', 'parent' => 'technician'],
            ['child' => '/helpdesk/repair/*', 'parent' => 'technician'],
            ['child' => '/helpdesk/repair/summary', 'parent' => 'medical'],
            ['child' => '/helpdesk/repair/user-request-order', 'parent' => 'medical'],
            ['child' => '/helpdesk/repair/user-job', 'parent' => 'medical'],
            ['child' => '/helpdesk/repair/list-accept', 'parent' => 'medical'],
            ['child' => '/helpdesk/repair/view', 'parent' => 'medical'],
            ['child' => '/helpdesk/repair/accept-job', 'parent' => 'medical'],
            ['child' => '/helpdesk/repair/switch-group', 'parent' => 'medical'],
            ['child' => '/helpdesk/repair/cancel-job', 'parent' => 'medical'],
            ['child' => '/helpdesk/repair/add-part', 'parent' => 'medical'],
            ['child' => '/helpdesk/repair/update', 'parent' => 'medical'],
            ['child' => '/helpdesk/stock/*', 'parent' => 'medical'],
            ['child' => '/helpdesk/repair/*', 'parent' => 'medical'],
            ['child' => '/hr/default/index', 'parent' => 'user'],
            ['child' => '/hr/employees/view', 'parent' => 'user'],

            //ระบบลา
            ['child' => '/hr/leave/create', 'parent' => 'user'],
            ['child' => '/hr/leave/update', 'parent' => 'user'],
            ['child' => '/hr/leave/view', 'parent' => 'user'],
            ['child' => '/hr/leave/view-history', 'parent' => 'user'],
            ['child' => '/hr/leave/*', 'parent' => 'leave'],
            ['child' => '/inventory/default/index', 'parent' => 'user'],
            ['child' => '/inventory/default/product-summary', 'parent' => 'user'],
            ['child' => '/inventory/default/warehouse', 'parent' => 'user'],
            ['child' => '/inventory/stock-out/index', 'parent' => 'user'],
            ['child' => '/inventory/stock-out/stock', 'parent' => 'user'],
            ['child' => '/inventory/stock/view-chart-total', 'parent' => 'user'],
            ['child' => '/inventory/stock/warehouse', 'parent' => 'user'],
            ['child' => '/inventory/warehouse/index', 'parent' => 'user'],
            ['child' => '/inventory/warehouse/list-order-request', 'parent' => 'user'],
            ['child' => '/inventory/warehouse/view', 'parent' => 'user'],
            ['child' => '/inventory/stock/warehouse', 'parent' => 'branch'],
            ['child' => '/inventory/warehouse/index', 'parent' => 'branch'],
            ['child' => '/inventory/warehouse/list-order-request', 'parent' => 'branch'],
            ['child' => '/inventory/warehouse/view', 'parent' => 'branch'],
            ['child' => '/inventory/default/index', 'parent' => 'branch'],
            ['child' => '/inventory/*', 'parent' => 'inventory'],
            // ['child' => '/inventory/main-stock/create', 'parent' => 'inventory'],
            // ['child' => '/inventory/main-stock/create', 'parent' => 'inventory'],
            // ['child' => '/inventory/stock-order', 'parent' => 'inventory'],
            ['child' => '/me/*', 'parent' => 'user'],
            ['child' => '/ms-word/*', 'parent' => 'user'],
            ['child' => '/profile/*', 'parent' => 'user'],
            ['child' => '/purchase/*', 'parent' => 'purchase'],
            ['child' => '/purchase/document/download-file', 'parent' => 'purchase'],
            ['child' => '/purchase/gr-order/update', 'parent' => 'purchase'],
            ['child' => '/purchase/order-item/committee', 'parent' => 'purchase'],
            ['child' => '/purchase/order-item/committee-detail', 'parent' => 'purchase'],
            ['child' => '/purchase/order-item/create', 'parent' => 'purchase'],
            ['child' => '/purchase/order-item/delete', 'parent' => 'purchase'],
            ['child' => '/purchase/order-item/update', 'parent' => 'purchase'],
            ['child' => '/purchase/order/delete-item', 'parent' => 'purchase'],
            ['child' => '/purchase/order/document', 'parent' => 'purchase'],
            ['child' => '/purchase/order/product-list', 'parent' => 'user'],
            ['child' => '/purchase/order/update-item', 'parent' => 'purchase'],
            ['child' => '/purchase/order/view', 'parent' => 'purchase'],
            ['child' => '/purchase/po-order/create', 'parent' => 'purchase'],
            ['child' => '/purchase/po-order/index', 'parent' => 'purchase'],
            ['child' => '/purchase/po-order/update', 'parent' => 'purchase'],
            ['child' => '/purchase/pq-order/index', 'parent' => 'purchase'],
            ['child' => '/purchase/pq-order/update', 'parent' => 'purchase'],
            ['child' => '/purchase/pr-order/checker-confirm', 'parent' => 'purchase'],
            ['child' => '/purchase/pr-order/create', 'parent' => 'purchase'],
            ['child' => '/purchase/pr-order/create', 'parent' => 'user'],
            ['child' => '/purchase/pr-order/director-confirm', 'parent' => 'director'],
            ['child' => '/purchase/pr-order/index', 'parent' => 'purchase'],
            ['child' => '/purchase/pr-order/index', 'parent' => 'user'],
            ['child' => '/purchase/pr-order/leader-confirm', 'parent' => 'user'],
            ['child' => '/purchase/pr-order/update', 'parent' => 'purchase'],
            ['child' => '/purchase/pr-order/update', 'parent' => 'user'],
            ['child' => '/site/*', 'parent' => 'user'],
            ['child' => '/sm/*', 'parent' => 'sm'],
            ['child' => '/sm/default/accept-pr-order', 'parent' => 'user'],
            ['child' => '/sm/default/budget-chart', 'parent' => 'user'],
            ['child' => '/sm/default/chart', 'parent' => 'user'],
            ['child' => '/sm/default/index', 'parent' => 'user'],
            ['child' => '/sm/default/pq-order', 'parent' => 'user'],
            ['child' => '/sm/default/pr-order', 'parent' => 'user'],
            ['child' => '/warehouse/*', 'parent' => 'warehouse'],
            ['child' => 'purchase/po-order/index', 'parent' => 'purchase'],
        ];
    }

    public function actionBudgetGroup()
    {
        $check = Yii::$app->db->createCommand("select * from categorise where name ='budget_group'")->queryAll();
        if (count($check) == 0) {
            $sql = "INSERT INTO `categorise` (`code`, `name`, `title`, `active`) VALUES
                ('BG1', 'budget_group', 'งบบุคลากร', 1),
                ('BG2', 'budget_group', 'งบดำเนินงาน (ค่าตอนแทน)', 1),
                ('BG3', 'budget_group', 'งบดำเนินงาน (ค่าใช้สอย)', 1),
                ('BG4', 'budget_group', 'งบดำเนินงาน (ค่าวัสดุ)', 1),
                ('BG5', 'budget_group', 'งบดำเนินงาน (ค่าสาธารณูปโภค)', 1),
                ('BG6', 'budget_group', 'งบลงทุน (ค่าครุภัณฑ์)', 1),
                ('BG7', 'budget_group', 'งบลงทุน (ค่าที่ดินและสิ่งก่อสร้าง)', 1),
                ('BG8', 'budget_group', 'งบเงินอุดหนุน', 1),
                ('BG9', 'budget_group', 'งบรายจ่ายอื่น', 1),
                ('BG10', 'budget_group', 'งบค่าเสื่อม', 1);";
            Yii::$app->db->createCommand($sql)->execute();
            echo 'สำเร็จ';
        } else {
            echo 'มีข้อมูลแล้ว';
        }
        $this->createView();
    }

    public function actionCreateView()
    {
        try {
            Yii::$app->db->createCommand('DROP VIEW IF EXISTS `leave_summary`')->execute();
            echo "Drop leave_summary\n";
        } catch (\Throwable $th) {

        }
        try {
            Yii::$app->db->createCommand('DROP VIEW IF EXISTS `leave_summary_year`')->execute();
            echo "Drop leave_summary_year\n";
        } catch (\Throwable $th) {

        }
        try {
            Yii::$app->db->createCommand('DROP VIEW IF EXISTS `view_stock`')->execute();
            echo "Drop view_stock\n";
        } catch (\Throwable $th) {

        }
        try {
            Yii::$app->db->createCommand('DROP VIEW IF EXISTS `view_stock_transaction`')->execute();
            echo "Drop view_stock_transaction\n";
        } catch (\Throwable $th) {

        }

        $sqlViewStock = "CREATE VIEW view_stock as SELECT t.code as type_code ,t.title as asset_type_name,i.code as asset_item,i.title as asset_item_name,s.warehouse_id,w.warehouse_type,w.warehouse_name,s.qty,sum(s.qty*s.unit_price) as total_price FROM stock s 
                        INNER JOIN warehouses w ON w.id = s.warehouse_id
                        INNER JOIN categorise i ON i.code = s.asset_item AND i.name = 'asset_item'
                        INNER JOIN categorise t ON t.code = i.category_id AND t.name = 'asset_type'
                        GROUP BY w.id,t.code;";
        $createViewStock = Yii::$app->db->createCommand($sqlViewStock)->execute();
        echo "Create view_stock\n";

        $sqlViewStockTransation = "CREATE VIEW view_stock_transaction AS WITH t as (SELECT  t.title as asset_type,i.category_id,i.code as asset_item,i.title as asset_name,i.data_json->>'\$.unit' as unit,
                                    so.code,
                                    si.po_number,
                                    wf.warehouse_type as from_warehouse_type,
                                    wf.warehouse_name  as from_warehouse_name,
                                    w.warehouse_type,
                                    w.warehouse_name,
                                    si.transaction_type,
                                    so.order_status,
                                    so.warehouse_id,
                                    si.qty,
                                    si.unit_price,
                                    so.data_json->>'\$.receive_date' as receive_date,so.created_at,
                                    so.thai_year
                                    
                                FROM 
                                    stock_events so
                                    LEFT OUTER JOIN stock_events si 
                                        ON si.category_id = so.id AND si.name = 'order_item'
                                    LEFT OUTER JOIN categorise i 
                                        ON i.code = si.asset_item AND i.name = 'asset_item'
                                    LEFT OUTER JOIN categorise t 
                                        ON t.code = i.category_id AND t.name='asset_type'
                                    LEFT OUTER JOIN warehouses w 
                                        ON w.id = si.warehouse_id
                                    LEFT OUTER JOIN warehouses wf 
                                        ON wf.id = si.from_warehouse_id
                                WHERE i.category_id <> ''
                                ) SELECT *,(CASE 
                                        WHEN (t.transaction_type = 'IN') 
                                        THEN MONTH(t.receive_date)
                                        ELSE MONTH(t.created_at)
                                    END) AS order_month
                                    
                                FROM t;";
        $createStockTransation = Yii::$app->db->createCommand($sqlViewStockTransation)->execute();
        echo "Create view_stock_transaction\n";

        $createLeaveSummary = Yii::$app->db->createCommand("CREATE VIEW leave_summary AS
                            SELECT 
                                lt.code, 
                                lt.title, 
                                lt.active,
                                l.thai_year,
                                COUNT(CASE WHEN MONTH(l.date_start) = 1 THEN 1 END) AS m1,
                                COUNT(CASE WHEN MONTH(l.date_start) = 2 THEN 1 END) AS m2,
                                COUNT(CASE WHEN MONTH(l.date_start) = 3 THEN 1 END) AS m3,
                                COUNT(CASE WHEN MONTH(l.date_start) = 4 THEN 1 END) AS m4,
                                COUNT(CASE WHEN MONTH(l.date_start) = 5 THEN 1 END) AS m5,
                                COUNT(CASE WHEN MONTH(l.date_start) = 6 THEN 1 END) AS m6,
                                COUNT(CASE WHEN MONTH(l.date_start) = 7 THEN 1 END) AS m7,
                                COUNT(CASE WHEN MONTH(l.date_start) = 8 THEN 1 END) AS m8,
                                COUNT(CASE WHEN MONTH(l.date_start) = 9 THEN 1 END) AS m9,
                                COUNT(CASE WHEN MONTH(l.date_start) = 10 THEN 1 END) AS m10,
                                COUNT(CASE WHEN MONTH(l.date_start) = 11 THEN 1 END) AS m11,
                                COUNT(CASE WHEN MONTH(l.date_start) = 12 THEN 1 END) AS m12
                                FROM categorise lt
                                LEFT OUTER JOIN `leave` l 
                                    ON l.leave_type_id = lt.code 
                                WHERE lt.name = 'leave_type'
                                GROUP BY lt.code, l.thai_year")->execute();

        $createLeaveSummaryYear = Yii::$app->db->createCommand("CREATE VIEW leave_summary_year AS SELECT l.thai_year,COUNT(l.id) as total FROM categorise lt
                            LEFT OUTER JOIN `leave` l ON l.leave_type_id = lt.code 
                            WHERE lt.name = 'leave_type' AND l.thai_year IS NOT NULL
                            GROUP BY l.thai_year;")->execute();
        echo "Create leave_summary_year\n";

        // ALTER TABLE `documents_detail` ADD `bookmark` VARCHAR(1) NOT NULL DEFAULT 'N' AFTER `from_type`;

        
    }
}
