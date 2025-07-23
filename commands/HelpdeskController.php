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
use app\modules\helpdesk2\models\Helpdesk;

/**
 * update แก้ไขรายการตำแหน่ให้เป็นล่าสุด.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 *
 * @since 2.0
 */
class HelpdeskController extends Controller
{
    /**
     * แสดงรายการ Helpdesk ทั้งหมด
     *
     * รันด้วยคำสั่ง: php yii helpdesk/index
     */
    public function actionIndex()
    {
        foreach (Helpdesk::find()->orderBy(['id' => SORT_DESC])->all() as $item) {
            echo $item->id . "\n";
        }

    }
}