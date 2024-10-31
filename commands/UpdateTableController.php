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
        $sqlUpdateAuthItems = 'INSERT INTO auth_item (type, name) VALUES (:type, :name) ON DUPLICATE KEY UPDATE name = VALUES(name), type = VALUES(type)';
        $sqlUpdateChild = 'INSERT INTO auth_item_child (parent, child) VALUES (:parent, :child) ON DUPLICATE KEY UPDATE child = VALUES(child), parent = VALUES(parent)';

        // update route
        foreach ($this->RouteList() as $authItem) {
            \Yii::$app->db->createCommand($sqlUpdateAuthItems, [
                ':name' => $authItem['name'],
                ':type' => $authItem['type'],
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
            ['name' => 'admin', 'type' => 1],
            ['name' => 'computer', 'type' => 1],
            ['name' => 'computer_ma', 'type' => 1],
            ['name' => 'director', 'type' => 1],
            ['name' => 'hr', 'type' => 1],
            ['name' => 'medical', 'type' => 1],
            ['name' => 'medical_ma', 'type' => 1],
            ['name' => 'purchase', 'type' => 1],
            ['name' => 'technician', 'type' => 1],
            ['name' => 'technician_ma', 'type' => 1],
            ['name' => 'user', 'type' => 1],
            ['name' => 'warehouse', 'type' => 1],
            ['name' => '/*', 'type' => 2],
            ['name' => '/am/asset/depreciation', 'type' => 2],
            ['name' => '/am/asset/index', 'type' => 2],
            ['name' => '/am/asset/qrcode', 'type' => 2],
            ['name' => '/am/asset/view', 'type' => 2],
            ['name' => '/am/default/index', 'type' => 2],
            ['name' => '/depdrop/*', 'type' => 2],
            ['name' => '/employees/*', 'type' => 2],
            ['name' => '/helpdesk/computer/*', 'type' => 2],
            ['name' => '/helpdesk/default/repair-select', 'type' => 2],
            ['name' => '/helpdesk/general/*', 'type' => 2],
            ['name' => '/helpdesk/medical/*', 'type' => 2],
            ['name' => '/helpdesk/repair/create', 'type' => 2],
            ['name' => '/helpdesk/repair/timeline', 'type' => 2],
            ['name' => '/hr/default/index', 'type' => 2],
            ['name' => '/hr/employees/view', 'type' => 2],
            ['name' => '/inventory/default/index', 'type' => 2],
            ['name' => '/inventory/default/product-summary', 'type' => 2],
            ['name' => '/inventory/default/warehouse', 'type' => 2],
            ['name' => '/inventory/stock-out/index', 'type' => 2],
            ['name' => '/inventory/stock-out/stock', 'type' => 2],
            ['name' => '/inventory/stock/view-chart-total', 'type' => 2],
            ['name' => '/inventory/stock/warehouse', 'type' => 2],
            ['name' => '/inventory/warehouse/index', 'type' => 2],
            ['name' => '/inventory/warehouse/list-order-request', 'type' => 2],
            ['name' => '/inventory/warehouse/view', 'type' => 2],
            ['name' => '/me/*', 'type' => 2],
            ['name' => '/profile/*', 'type' => 2],
            ['name' => '/purchase/document/download-file', 'type' => 2],
            ['name' => '/purchase/gr-order/update', 'type' => 2],
            ['name' => '/purchase/order-item/committee', 'type' => 2],
            ['name' => '/purchase/order-item/committee-detail', 'type' => 2],
            ['name' => '/purchase/order-item/create', 'type' => 2],
            ['name' => '/purchase/order-item/delete', 'type' => 2],
            ['name' => '/purchase/order-item/update', 'type' => 2],
            ['name' => '/purchase/order/delete-item', 'type' => 2],
            ['name' => '/purchase/order/document', 'type' => 2],
            ['name' => '/purchase/order/product-list', 'type' => 2],
            ['name' => '/purchase/order/update-item', 'type' => 2],
            ['name' => '/purchase/order/view', 'type' => 2],
            ['name' => '/purchase/po-order/create', 'type' => 2],
            ['name' => '/purchase/po-order/index', 'type' => 2],
            ['name' => '/purchase/po-order/update', 'type' => 2],
            ['name' => '/purchase/pq-order/index', 'type' => 2],
            ['name' => '/purchase/pq-order/update', 'type' => 2],
            ['name' => '/purchase/pr-order/checker-confirm', 'type' => 2],
            ['name' => '/purchase/pr-order/create', 'type' => 2],
            ['name' => '/purchase/pr-order/director-confirm', 'type' => 2],
            ['name' => '/purchase/pr-order/index', 'type' => 2],
            ['name' => '/purchase/pr-order/leader-confirm', 'type' => 2],
            ['name' => '/purchase/pr-order/update', 'type' => 2],
            ['name' => '/settings/*', 'type' => 2],
            ['name' => '/site/*', 'type' => 2],
            ['name' => '/sm/default/accept-pr-order', 'type' => 2],
            ['name' => '/sm/default/budget-chart', 'type' => 2],
            ['name' => '/sm/default/chart', 'type' => 2],
            ['name' => '/sm/default/index', 'type' => 2],
            ['name' => '/sm/default/pq-order', 'type' => 2],
            ['name' => '/sm/default/pr-order', 'type' => 2],
            ['name' => '/usermanager/*', 'type' => 2],
            ['name' => '/warehouse/*', 'type' => 2],
            ['name' => 'purchase/po-order/index', 'type' => 2],
            ['name' => '/ms-word/*', 'type' => 2],
        ];
    }

    public static function ChildList()
    {
        return [
            ['child' => '/*', 'parent' => 'admin'],
            ['child' => '/am/asset/depreciation', 'parent' => 'user'],
            ['child' => '/am/asset/index', 'parent' => 'user'],
            ['child' => '/am/asset/qrcode', 'parent' => 'user'],
            ['child' => '/am/asset/view', 'parent' => 'user'],
            ['child' => '/am/default/index', 'parent' => 'user'],
            ['child' => '/depdrop/*', 'parent' => 'user'],
            ['child' => '/helpdesk/computer/*', 'parent' => 'computer'],
            ['child' => '/helpdesk/default/repair-select', 'parent' => 'user'],
            ['child' => '/helpdesk/general/*', 'parent' => 'technician'],
            ['child' => '/helpdesk/medical/*', 'parent' => 'medical'],
            ['child' => '/helpdesk/repair/create', 'parent' => 'user'],
            ['child' => '/helpdesk/repair/timeline', 'parent' => 'user'],
            ['child' => '/hr/default/index', 'parent' => 'user'],
            ['child' => '/hr/employees/view', 'parent' => 'user'],
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
            ['child' => '/me/*', 'parent' => 'user'],
            ['child' => '/ms-word/*', 'parent' => 'user'],
            ['child' => '/profile/*', 'parent' => 'user'],
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
        if(count($check) == 0){
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
        }else{
            echo 'มีข้อมูลแล้ว';
        }
    }

}
