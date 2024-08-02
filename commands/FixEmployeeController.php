<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use Yii;
use app\modules\hr\models\Employees;
use app\modules\hr\models\EmployeeDetail;
use app\modules\hr\models\Organization;
use yii\console\Controller;
use yii\helpers\BaseConsole;
use yii\helpers\ArrayHelper;
use app\models\Categorise;

/**
 * แก้ไขรหัสตำแหน่งใหม่ v2
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class FixEmployeeController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     * @return int Exit code
     */
    public function actionIndex()
    {
        if (BaseConsole::confirm("Are you sure?")) {
            $data = [];
            $employees = Employees::find()->all();
            foreach ($employees as $model) {
                if ($model->ref == '') {
                    $emp = Employees::findOne($model->id);
                    $emp->ref = substr(Yii::$app->getSecurity()->generateRandomString(), 10);
                    if($emp->save(false)){
                        echo $emp->fname . "\n";
                    }else{
                        echo 'ผิดพลาด'. "\n";

                    }
                    
                }
            }
        }
        // echo  $data;
    }
}
