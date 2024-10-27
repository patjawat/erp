<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;
use Yii;
use app\modules\inventory\models\StockEvent;
use app\components\AppHelper;
use yii\console\Controller;
use yii\helpers\BaseConsole;
use yii\helpers\ArrayHelper;

/**
 * update แปลงวันที่รับเข้ากับ พ.ศ.ให้เป็นปีงบประมาณ
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class StockThaiController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     * @return int Exit code
     */
    public function actionIndex()
    {
        if (BaseConsole::confirm("Are you sure?")) {
         
            $orders = StockEvent::find()->where(['name' => 'order','transaction_type' => 'IN'])->all();
            foreach ($orders as $order) {
                try {
                    $thai = AppHelper::YearBudget($order->data_json['receive_date']);
                    $model = StockEvent::findOne($order->id);

                    $model->thai_year = AppHelper::YearBudget($order->data_json['receive_date']);
                    if($model->save(false)){
                        // echo $model->id. "\n";
                        // echo 'Y';
                        // echo $model->thai_year. "\n";
                        $this->UpdateItem($model);
                    }else{
                        echo 'N';
                    }

                    
                } catch (\Throwable $th) {
                    echo $th. "\n";
                }
            }
        }

    }

    
public static function UpdateItem($order)
{
    $ordersItems = StockEvent::find()->where(['name' => 'order_item','category_id' => $order->id])->all();
    foreach ($ordersItems as $item) {
        try {
            echo $order->thai_year. "\n";
            $model = StockEvent::findOne($item->id);
            $model->thai_year = $order->thai_year;
            if($model->save(false)){
                echo $model->thai_year. "\n";
            }

        } catch (\Throwable $th) {
            echo $th. "\n";
        }
    }
}

}
