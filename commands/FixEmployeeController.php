<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use Yii;
use DirectoryIterator;
use app\models\Categorise;
use yii\console\Controller;
use yii\helpers\ArrayHelper;
use yii\helpers\BaseConsole;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;
use app\modules\hr\models\EmployeeDetail;
use app\modules\filemanager\models\Uploads;

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
        // if (BaseConsole::confirm('Are you sure?')) {
            $this->Backup();
            $data = [];
            $employees = Employees::find()->all();
            foreach ($employees as $model) {
                $this->UpdatePosition($model);
            }
        // }
    }

    public static  function Backup(){

            $timestamp = date('Y_m_d_His'); // ใช้ timestamp เดียวกันเพื่อลดความซ้ำซ้อน
            $tables = ['employees', 'employee_detail'];
        
            foreach ($tables as $table) {
                $backupTable = "backup_{$timestamp}_{$table}";
                $sql = "CREATE TABLE {$backupTable} AS SELECT * FROM {$table}";
        
                try {
                    Yii::$app->db->createCommand($sql)->execute();
                    Yii::info("Backup created: {$backupTable}", __METHOD__);
                    echo "Backup successful: {$backupTable}\n";
                } catch (\Throwable $th) {
                    Yii::error("Backup failed for table: {$table}. Error: " . $th->getMessage(), __METHOD__);
                    echo "Failed to create backup for: {$table}\n";
                }
            }
    }
    public static function UpdatePosition($data)
    {
        try {
            $emp = Employees::findOne($data->id);
            if (!$emp) {
                throw new \yii\base\Exception('Employee not found.');
            }

            // หาตำแหน่งล่าสุดจากวันที่
            $model = EmployeeDetail::find()
                ->where(['name' => 'position', 'emp_id' => $data->id])
                ->orderBy(new \yii\db\Expression("JSON_EXTRACT(data_json, '\$.date_start') DESC"))
                ->one();

            if ($model) {
                $data_json = is_array($model->data_json) ? $model->data_json : json_decode($model->data_json, true);

                // หาชื่อแผนก
                $department_id = $data_json['department'] ?? null;
                $department_name = $department_id ? Organization::findOne($department_id) : null;

                // กำหนดค่าให้พนักงาน
                $emp->setAttributes([
                    'position_name' => $data_json['position_name'] ?? null,
                    'position_group' => $data_json['position_group'] ?? null,
                    'position_type' => $data_json['position_type'] ?? null,
                    'position_level' => ($data_json['position_type_text'] ?? '') !== 'ข้าราชการ' ? null : ($data_json['position_level'] ?? null),
                    'status' => $data_json['status'] ?? null,
                    'position_number' => $data_json['position_number'] ?? null,
                    'salary' => $data_json['salary'] ?? null,
                    'department' => $department_id,
                ], false);

                // อัปเดตข้อมูล JSON
                $array2 = [
                    'position_name_text' => $data_json['position_name_text'] ?? '',
                    'position_group' => $data_json['position_group_text'] ?? '',
                    'position_type' => $data_json['position_type_text'] ?? '',
                    'position_level_text' => $data_json['position_level_text'] ?? '',
                    'status_text' => $data_json['status_text'] ?? '',
                    'department_text' => $department_name->name ?? '',
                ];
                $emp->data_json = ArrayHelper::merge($emp->data_json, $array2);
            } else {
                // ล้างค่าหากไม่มีตำแหน่ง
                $emp->setAttributes([
                    'position_name' => null,
                    'position_group' => null,
                    'position_type' => null,
                    'position_level' => null,
                ], false);

                // ล้างค่า JSON
                $emp->data_json = ArrayHelper::merge($emp->data_json, [
                    'position_name_text' => '',
                    'position_group' => '',
                    'position_type' => '',
                ]);
            }

            // บันทึกข้อมูลพนักงาน
            if ($emp->save(false)) {
                echo $emp->fullname, "\n";
            } else {
                throw new \yii\base\Exception('Failed to save employee data.');
            }
        } catch (\Throwable $th) {
            Yii::error($th->getMessage(), __METHOD__);
            throw $th;
        }
    }
}
