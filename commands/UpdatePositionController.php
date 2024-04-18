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

/**
 * update แก้ไขรายการตำแหน่ให้เป็นล่าสุด
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class UpdatePositionController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     * @return int Exit code
     */
    public function actionIndex()
    {
        // if (BaseConsole::confirm("Are you sure?")) {
         
            $employees = Employees::find()->all();
            foreach ($employees as $emp) {
                echo $this->UpdatePosition($emp->id)."\n";
            }
        // }

    }

    public function UpdatePosition($emp_id)
    {
        try {

            $emp = Employees::findOne($emp_id);
            //หาตำแหน่งล่าสุดจากวันที่
            $model = EmployeeDetail::find()->where(['name' => 'position', 'emp_id' => $emp_id])
                ->orderBy(new \yii\db\Expression("JSON_EXTRACT(data_json, '$.date_start') desc"))->one();
            // get position By Tree diagram
            //$position = CategoriseHelper::TreeById($model->data_json['position_name']);

            // get poditionBy Category design
            if ($model) {
                        $department_name = Organization::findOne($model->data_json['department']);
                        $emp->position_name = $model->data_json['position_name'];
                        $emp->position_group = isset($model->data_json['position_group']) ? $model->data_json['position_group'] : null;
                        $emp->position_type =isset($model->data_json['position_type']) ? $model->data_json['position_type'] : null;
                        if(isset($model->data_json['position_type_text']) && $model->data_json['position_type_text'] != 'ข้าราชการ'){
                            $emp->position_level = null;
                        }
                        $array2 = [
                            'position_name_text' => isset($model->data_json['position_name_text']) ? $model->data_json['position_name_text'] : "",
                            'position_group' => isset($model->data_json['position_group_text']) ? $model->data_json['position_group_text'] : "",
                            'position_type' => isset($model->data_json['position_type_text']) ? $model->data_json['position_type_text'] : "",
                            'position_level_text' => isset($model->data_json['position_level_text']) ? $model->data_json['position_level_text'] : "",
                            'status_text' => isset($model->data_json['status_text']) ? $model->data_json['status_text'] : "",
                            'department_text' => $department_name ? $department_name->name : ''
                        ];
                        $emp->data_json = ArrayHelper::merge($emp->data_json, $array2);

                if (isset($model->data_json['status'])) {
                    $emp->status = $model->data_json['status'];
                }

                if (isset($model->data_json['position_number'])) {
                    $emp->position_number = $model->data_json['position_number'];
                }
                if (isset($model->data_json['position_type'])) {
                    $emp->position_type = $model->data_json['position_type'];
                }
                if (isset($model->data_json['position_level'])) {
                    $emp->position_level = $model->data_json['position_level'];
                }
                if (isset($model->data_json['salary'])) {
                    $emp->salary = $model->data_json['salary'];
                }
                $emp->department = $model->data_json['department'];
                // return $emp->position_name;
            } else {
                $emp->position_name = null;
                $emp->position_group = null;
                $emp->position_type = null;
                $emp->position_level = null;
                $array2 = [
                    'position_name_text' => '',
                    'position_group' => '',
                    'position_type' => ''
                ];
                $emp->data_json = ArrayHelper::merge($emp->data_json, $array2);
            }
            if($emp->save(false)){
                return $emp->fullname.' update สำเร็จ!';
            }else{
                return $emp->fullname.'  ผิดพลาด!';
            }
            return ;
            //code...
        } catch (\Throwable $th) {
            return throw $th;
            //     return 'not save';
        }
    }

}
