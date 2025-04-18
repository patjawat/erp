<?php

/**
 * @see http://www.yiiframework.com/
 *
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

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
class ImportAppointmentController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     *
     * @return int Exit code
     */
    public function actionIndex($message = 'hello world')
    {
        echo $message . "\n";

        // $this->M7();
        return ExitCode::OK;
    }

    // วัสดุวิสำนักงาน
    public function actionM1()
    {
        $data = [
                ['topic' => 'กระดาษกาวขาว 1 นิ้ว', 'unit' => 'ม้วน','unit_price' =>45.00,'qty' =>0],
        ];
        $newItem = new Documents([
            'name' => 'appointment',
            'topic' => 'topic',
            'doc_date' => '',
            'doc_regis_number' => 1,
            'document_type' => 'DT9',
            'document_org' => 0,
            'doc_speed' => 'ปกติ',
            'secret' => 'ปกติ',
            'thai_year' => 2568,
        ]);
        $newItem->save(false);
    }

}
