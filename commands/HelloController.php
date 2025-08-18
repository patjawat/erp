<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class HelloController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     * @return int Exit code
     */
    public function actionIndex($message = 'hello world')
    {
        echo $message . "\n";

        return ExitCode::OK;
    }

    public function actionInsertDemo()
    {
        $db = Yii::$app->db;

        for ($i = 0; $i < 5000; $i++) {
            $sql = "INSERT INTO `stock_events` (`name`, `code`, `transaction_type`, `asset_item`, `warehouse_id`, `vendor_id`, `po_number`, `from_warehouse_id`, `qty`, `total_price`, `unit_price`, `receive_type`, `movement_date`, `lot_number`, `category_id`, `order_status`, `checker`, `ref`, `data_json`, `thai_year`, `created_at`, `updated_at`, `created_by`, `updated_by`, `deleted_at`, `deleted_by`) VALUES
('order', 'RC-670207', 'IN', NULL, 1, '00', 'PO-670023', NULL, NULL, 387, NULL, NULL, '2024-07-23 21:50:44', NULL, '', 'pending', NULL, NULL, '{\"do_number\": \"1\"}', 2567, '2024-07-23 21:50:44', '2024-07-23 22:05:39', 1, 1, NULL, NULL),
('order_item', 'RC-670207', 'IN', '04-00054', 1, NULL, 'PO-670023', NULL, 132, NULL, 129, NULL, '2024-07-23 21:50:44', 'LOT67-00095', '1', 'pending', NULL, NULL, '{\"exp_date\": \"2024-08-17\", \"mfg_date\": \"2024-07-29\", \"item_type\": \"ยอดยกมา\"}', 2567, '2024-07-23 21:50:44', '2024-07-23 21:50:59', 1, 1, NULL, NULL),
('order', 'IN-670114', 'IN', NULL, 1, '00', NULL, NULL, NULL, NULL, NULL, NULL, '2024-07-23 22:07:22', NULL, '', 'pending', NULL, '_bso5hvlglnv4QgpcT3rlU', '{\"do_number\": \"12\", \"employee_fullname\": \"Administrator Lastname\", \"employee_position\": \"นักวิชาการคอมพิวเตอร์ (ระดับปฏิบัติการ)\", \"employee_department\": \"งานพัสดุ\"}', 2567, '2024-07-23 22:07:22', NULL, 1, 1, NULL, NULL),
('order', 'REQ-670051', 'OUT', NULL, 1, NULL, NULL, 2, NULL, NULL, NULL, NULL, '2024-07-23 22:19:20', NULL, '', 'success', '1', 'sXIBguqstvzlTr_YuIj6Yi', '{\"note\": \"1\", \"checker_comment\": \"\", \"checker_confirm\": \"Y\"}', 2567, '2024-07-23 22:19:20', '2024-07-23 22:21:59', 1, 1, NULL, NULL),
('order_item', 'REQ-670051', 'OUT', '04-00054', 1, NULL, NULL, 2, 1, 129, 129, NULL, '2024-07-23 22:19:33', 'LOT67-00095', '4', 'success', NULL, 'YY-i9MGt73YYxLp6CJ3F9J', '{\"req_qty\": \"1\", \"checker_confirm\": \"\"}', 2567, '2024-07-23 22:19:33', '2024-07-23 22:21:59', 1, 1, NULL, NULL),
('order', 'IN-670115', 'IN', NULL, 2, NULL, NULL, 1, NULL, NULL, NULL, NULL, '2024-07-23 22:21:59', NULL, 'REQ-670051', 'success', NULL, NULL, NULL, 2567, '2024-07-23 22:21:59', NULL, 1, 1, NULL, NULL),
('order_item', 'IN-670115', 'IN', '04-00054', 2, NULL, NULL, NULL, 131, NULL, 129, NULL, '2024-07-23 22:21:59', NULL, '6', NULL, NULL, NULL, NULL, 2567, '2024-07-23 22:21:59', NULL, 1, 1, NULL, NULL),
('order', 'IN-670116', 'IN', NULL, 3, '00', NULL, NULL, NULL, 480, NULL, NULL, '2024-07-23 22:35:35', NULL, '', 'success', NULL, 'z_LWtj1nOsTzk4cetiOu8a', '{\"do_number\": \"2\", \"employee_fullname\": \"Administrator Lastname\", \"employee_position\": \"นักวิชาการคอมพิวเตอร์ (ระดับปฏิบัติการ)\", \"employee_department\": \"งานพัสดุ\"}', 2567, '2024-07-23 22:35:35', '2024-07-23 22:35:51', 1, 1, NULL, NULL),
('order_item', 'IN-670116', 'IN', '19-00188', 3, NULL, NULL, NULL, 46, NULL, 120, NULL, '2024-07-23 22:35:49', 'LOT67-00096', '8', 'success', NULL, 'SiOzvnfYUQkX6l5OuHXvja', '{\"req_qty\": \"4\", \"exp_date\": \"2024-08-06\", \"mfg_date\": \"2024-07-29\", \"item_type\": \"ยอดยกมา\", \"employee_fullname\": \"Administrator Lastname\", \"employee_position\": \"นักวิชาการคอมพิวเตอร์ (ระดับปฏิบัติการ)\", \"employee_department\": \"งานพัสดุ\"}', 2567, '2024-07-23 22:35:49', NULL, 1, 1, NULL, NULL),
('order', 'REQ-670052', 'OUT', NULL, 3, NULL, NULL, 3, NULL, NULL, NULL, NULL, '2024-07-23 22:37:30', NULL, '', 'success', '1', NULL, '{\"note\": \"122\", \"checker_comment\": \"\", \"checker_confirm\": \"Y\"}', 2567, '2024-07-23 22:37:30', '2024-08-24 11:23:27', 1, 1, NULL, NULL),
('order_item', 'REQ-670052', 'OUT', '19-00188', 3, NULL, NULL, 3, 12, NULL, NULL, NULL, '2024-07-23 22:37:30', 'LOT67-00096', '10', 'success', NULL, NULL, '{\"req_qty\": 1}', 2567, '2024-07-23 22:37:30', '2024-07-23 22:37:44', 1, 1, NULL, NULL),
('order', 'REQ-670053', 'OUT', NULL, 1, NULL, NULL, 3, NULL, NULL, NULL, NULL, '2024-08-24 10:16:31', NULL, '', 'cancel', '1', 'ZylbFFvyR-4_v3GHZGvbGA', '{\"note\": \"22\", \"checker_comment\": \"\", \"checker_confirm\": \"N\"}', 2567, '2024-08-24 10:16:31', '2024-08-24 10:40:10', 1, 1, NULL, NULL),
('order_item', 'REQ-670053', 'OUT', '04-00054', 1, NULL, NULL, 306, NULL, NULL, NULL, NULL, '2024-08-24 10:16:41', NULL, '12', 'cancel', NULL, 'cYjrJwbfmKi3Ds7hW3A0LF', '{\"req_qty\": \"2\", \"checker_confirm\": \"\"}', 2567, '2024-08-24 10:16:41', '2024-08-24 10:16:44', 1, 1, NULL, NULL);";

            $db->createCommand($sql)->execute();
            echo  "success \n";
        }
    }



    public function actionPlanItem()
    {
        $count = (new \yii\db\Query())->from('categorise')->where(['name' => 'plan_item'])->count();
        if ($count == 0) {
            $items = [
                // ======================= รายจ่ายบุคลากร (PE) =======================
                ['code' => 'PE1',  'category_id' => 'PE',  'title' => 'ค่าจ้างลูกจ้างชั่วคราว / พนักงานกระทรวง','name' => 'plan_item'],
                ['code' => 'PE2',  'category_id' => 'PE',  'title' => 'ค่าล่วงเวลางานบริการ / งานสนับสนุน','name' => 'plan_item'],
                ['code' => 'PE3',  'category_id' => 'PE',  'title' => 'ค่าตอบแทนการปฏิบัติงานเวรผลัดบ่ายหรือผลัดดึกของเจ้าหน้าที่','name' => 'plan_item'],
                ['code' => 'PE4',  'category_id' => 'PE',  'title' => 'ค่าตอบแทนเงินเพิ่มพิเศษไม่ทำเวชปฏิบัติส่วนตัว หรือปฏิบัติงาน รพ.เอกชน','name' => 'plan_item'],
                ['code' => 'PE5',  'category_id' => 'PE',  'title' => 'ค่าตอบแทนเบี้ยเลี้ยงเหมาจ่าย (ฉ.11)','name' => 'plan_item'],
                ['code' => 'PE6',  'category_id' => 'PE',  'title' => 'ค่าตอบแทนตามผลการปฏิบัติงาน (ฉ.12)','name' => 'plan_item'],
                ['code' => 'PE7',  'category_id' => 'PE',  'title' => 'เงินเพิ่ม (พ.ต.ส)','name' => 'plan_item'],
                ['code' => 'PE8',  'category_id' => 'PE',  'title' => 'ค่าตอบแทนเจ้าหน้าที่ปฏิบัติงานของเจ้าหน้าที่ (นอกเวลา) ฉ5','name' => 'plan_item'],
                ['code' => 'PE9',  'category_id' => 'PE',  'title' => 'ค่าตอบแทนเจ้าหน้าที่ปฏิบัติงานในคลินิกพิเศษเฉพาะทางนอกเวลาราชการ (SMC)','name' => 'plan_item'],
                ['code' => 'PE10', 'category_id' => 'PE',  'title' => 'ค่าตอบแทนอื่น','name' => 'plan_item'],
                ['code' => 'PE11', 'category_id' => 'PE',  'title' => 'เงินค่าใช้จ่ายบุคลากรอื่น','name' => 'plan_item'],
                ['code' => 'PE12', 'category_id' => 'PE',  'title' => 'ค่าตอบแทนเบี้ยเลี้ยงเหมาจ่าย (ฉ.10)','name' => 'plan_item'],

                // ======================= รายจ่ายจากการดำเนินงาน (OE) =======================
                ['code' => 'OE1',  'category_id' => 'OE',  'title' => 'ค่ายา','name' => 'plan_item'],
                ['code' => 'OE2',  'category_id' => 'OE',  'title' => 'ค่าเวชภัณฑ์มิใช่ยา','name' => 'plan_item'],
                ['code' => 'OE3',  'category_id' => 'OE',  'title' => 'ค่าวัสดุ','name' => 'plan_item'],
                ['code' => 'OE4',  'category_id' => 'OE',  'title' => 'ค่าสาธารณูปโภค','name' => 'plan_item'],
                ['code' => 'OE5',  'category_id' => 'OE',  'title' => 'ค่าใช้สอย','name' => 'plan_item'],
                ['code' => 'OE6',  'category_id' => 'OE',  'title' => 'ค่าใช้จ่ายดำเนินงานอื่น','name' => 'plan_item'],

                // ======================= รายจ่ายลงทุน (CE) =======================
                ['code' => 'CE1',  'category_id' => 'CE',  'title' => 'ค่าครุภัณฑ์','name' => 'plan_item'],
                ['code' => 'CE2',  'category_id' => 'CE',  'title' => 'ค่าที่ดินและสิ่งก่อสร้าง','name' => 'plan_item'],
                ['code' => 'CE3',  'category_id' => 'CE',  'title' => 'ค่าครุภัณฑ์ต่ำกว่าเกณฑ์','name' => 'plan_item'],

                // ======================= รายจ่ายอื่น (OE-OTH) =======================
                ['code' => 'OE-OTH1', 'category_id' => 'OE-OTH', 'title' => 'รายจ่ายสนับสนุน รพ.สต. รพช. รพท. รพศ. สสอ. สสจ.','name' => 'plan_item'],
                ['code' => 'OE-OTH2', 'category_id' => 'OE-OTH', 'title' => 'รายจ่ายอื่นๆ','name' => 'plan_item'],
            ];



      foreach ($items as $item) {
            $exists = (new \yii\db\Query())
                ->from('categorise')
                ->where(['name' => 'plan_item', 'code' => $item['code']])
                ->exists();

            if (!$exists) {
                Yii::$app->db->createCommand()->insert('categorise', [
                    'name' => 'plan_item',
                    'code' => $item['code'],
                    'category_id' => $item['category_id'],
                    'title' => $item['title'],

                ])->execute();
            }
        }
        }
    }

}
