<?php

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
class OrderStatusController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     *
     * @return int Exit code
     */
    public function actionIndex()
    {
        $sql = "UPDATE `categorise` SET `data_json` = '{\r\n \"color\": \"danger\"\r\n}' WHERE name = 'order_status' AND `code` = 1;
        UPDATE `categorise` SET `data_json` = '{\r\n \"color\": \"warning\"\r\n}' WHERE name = 'order_status' AND `code` = 2;
        UPDATE `categorise` SET `data_json` = '{\r\n \"color\": \"primary\"\r\n}' WHERE name = 'order_status' AND `code` = 3;
        UPDATE `categorise` SET `data_json` = '{\r\n \"color\": \"info\"\r\n}' WHERE name = 'order_status' AND `code` = 4;
        UPDATE `categorise` SET `data_json` = '{\r\n \"color\": \"info\"\r\n}' WHERE name = 'order_status' AND `code` = 5;
        UPDATE `categorise` SET `data_json` = '{\r\n \"color\": \"success\"\r\n}' WHERE name = 'order_status' AND `code` = 6;";
        Yii::$app->db->createCommand($sql)->execute();

        echo "update  Success\n";
    }
}
