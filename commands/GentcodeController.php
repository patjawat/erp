<?php

/**
 * @see http://www.yiiframework.com/
 *
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use Yii;
use yii\console\ExitCode;
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
class GentcodeController extends Controller
{
    /**
     * แสดงรายการ Helpdesk ทั้งหมด
     *
     * รันด้วยคำสั่ง: php yii helpdesk/index
     */
    public function actionIndex()
    {
        foreach (Helpdesk::find()->where(['<>', 'repair_group', ''])->orderBy(['id' => SORT_ASC])->all() as $model) {
            $model->repair_number  = $this->code($model);
            $model->save();
        }

        // echo "Hello" . "\n";

        return ExitCode::OK;

    }

    public function Code($model)
    {
         switch ($model->repair_group) {
                    case '1':
                        $depCode = 'GEN';
                        break;

                    case '2':
                        $depCode = 'IT';
                        break;
                    case '3':
                        $depCode = 'MED';
                        break;

                    default:
                        $depCode = '';
                        break;
                }
               return  $model->ResetHelpdeskGenNumber($depCode);
    }
}