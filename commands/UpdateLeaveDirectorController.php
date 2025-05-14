<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use Yii;
use yii\db\Expression;
use yii\console\ExitCode;
use app\models\Categorise;
use yii\console\Controller;
use \yii\helpers\FileHelper;
use yii\helpers\ArrayHelper;
use yii\helpers\BaseConsole;
use app\components\AppHelper;
use yii\helpers\BaseFileHelper;
use app\modules\hr\models\Leave;
use app\modules\hr\models\Employees;
use app\modules\approve\models\Approve;
use app\modules\hr\models\LeaveEntitlements;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class UpdateLeaveDirectorController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     * @return int Exit code
     */
    public function actionIndex()
    {

        // update approve leave ที่เป็นค่า null
        $sql = "UPDATE `approve` SET emp_id = 19 WHERE `name` LIKE 'leave' AND `emp_id` IS NULL AND `level` = 4";
        $querys = Leave::find()->all();
        if (BaseConsole::confirm('การพัฒนา ' . count($querys) . ' รายการ ยืนยัน ??')) {
            $num = 1;
            $total = count($querys);

            foreach ($querys as $leave) {
                $checkApprove = Approve::find()->where(['name' => 'leave', 'from_id' => $leave->id, 'level' => 4])->one();
                if (!$checkApprove) {
                    $newApprove = new Approve();
                    $newApprove->name = 'leave';
                    $newApprove->from_id = $leave->id;
                    $newApprove->emp_id = 19;
                    $newApprove->level = 4;
                    $newApprove->status = 'Pending';
                    $newApprove->save(false);
                    echo 'Update Leave : ' . $num . '/' . $total . "\n";
                    $num++;
                }
            }
        }
    }
}
