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
class FixPositionController extends Controller
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
            $empDetails = EmployeeDetail::find()->where(['name' => 'position'])->all();
            foreach ($empDetails as $model) {
                $type = isset($model->data_json['position_type_text']) ? $model->data_json['position_type_text'] : "";
                $group = isset($model->data_json['position_group_text']) ? $model->data_json['position_group_text'] : "";
                $name = isset($model->data_json['position_name_text']) ? $model->data_json['position_name_text'] : "";
                $updateDetail = EmployeeDetail::findOne($model->id);
                if($group !=="" && $name !== ""){

                    
                    $newUpdate =   $this->Position($type,$group,$name);
                    $array2 = [
                        'position_name' => $newUpdate['position_name'],
                        'position_group' => $newUpdate['position_group'],
                        'position_type' => $newUpdate['position_type'],
                    ];
                    
                    $updateDetail->data_json = ArrayHelper::merge($updateDetail->data_json, $array2);
                    if($updateDetail->save(false)){
                        echo "=";
                    }else{
                        echo "#";
                    }
                    
                }
                    
            }
            // echo  $data;
        }

    }

    public static function  Position($positionType,$positionGroup,$positionName)
    {
        $type = Categorise::findOne([
            'name' => 'position_type',
            'title' => $positionType
        ]);
        $group = Categorise::findOne([
            'name' => 'position_group',
            'title' => $positionGroup
        ]);
        $name = Categorise::findOne([
            'name' => 'position_name',
            'title' => $positionName
        ]);
        return [
            'position_type' => isset($type)  ? $type->code : '',
            'position_group' => isset($group) ? $group->code : '',
            'position_name' => isset($name) ? $name->code : ''
        ];
    }

    

}
