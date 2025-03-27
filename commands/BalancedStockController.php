<?php
namespace app\commands;

use app\models\Uploads;
use yii\console\ExitCode;
use app\models\Categorise;
use yii\console\Controller;
use yii\helpers\FileHelper;
use yii\helpers\BaseConsole;
use app\components\AppHelper;
use app\modules\inventory\models\Stock;
use app\modules\inventory\models\StockEvent;
use app\modules\filemanager\components\FileManagerHelper;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 *
 * @since 2.0
 * คลังวัสดุเทคนิคการแพทย์
 */
class BalancedStockController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     *
     * @return int Exit code
     */
    public function actionIndex()
    {
            foreach(Stock::find()->where(['warehouse_id' => 1])->all() as $stock)
            {
                $sql = "WITH RankedEvents AS (
                        SELECT 
                            `t`.*, 
                            `o`.`category_id` AS `category_code`, 
                            `w`.`warehouse_name`, 
                            `o`.`code` AS `order_code`,  -- Alias the code column to avoid duplication
                            `o`.`data_json` AS `order_data_json`,  -- Alias the data_json column to avoid duplication
                            @running_total := IF(t.transaction_type = 'IN', @running_total + t.qty, @running_total - t.qty) AS total, 
                                    (t.unit_price * t.qty) AS total_price_t,  -- Alias the total_price column to avoid duplication
                                    ROW_NUMBER() OVER (PARTITION BY t.asset_item ORDER BY t.created_at DESC, t.id DESC) AS row_num
                                FROM `stock_events` `t`
                                LEFT JOIN `warehouses` `w` ON w.id = t.from_warehouse_id
                                LEFT JOIN `stock_events` `o` ON o.id = t.category_id AND o.name = 'order'
                                JOIN (SELECT @running_total := 0) `r`
                                WHERE 
                                    (`t`.`asset_item` = :asset_item)
                                    AND (`t`.`name` = 'order_item')
                                    AND (`t`.`warehouse_id` = :warehouse_id)
                                    AND (`t`.`order_status` = 'success')
                                    AND (`o`.`order_status` = 'success')
                            )
                            SELECT * 
                            FROM RankedEvents
                            WHERE row_num = 1;";
                            $querys = \Yii::$app->db->createCommand($sql, [
                                ':warehouse_id' => $stock->warehouse_id,
                                ':asset_item' => $stock->asset_item
                            ])->queryOne();
                            // if($stock->qty != $querys['total']){
                                
                                echo $stock->asset_item.' '.$stock->qty.' = '.$querys['total']."\n";
                                $stock->qty = $querys['total'];
                                $stock->unit_price = $querys['unit_price'];
                                $stock->save();
                            // }
                            // echo $stock->warehouse_id."\n";
                
            }
    }
}