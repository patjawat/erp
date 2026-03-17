<?php

namespace app\modules\amSurvey\models;

use yii\base\Model;

/**
 * Form model for CSV import: เก็บค่า emp_id (ผู้สำรวจ) สำหรับใช้กับ input_emp
 */
class CsvImportForm extends Model
{
    /** รหัสบุคลากร (Employees.id) — ใช้แสดงใน input_emp แล้วแปลงเป็น user_id ตอนนำเข้า */
    public $emp_id;

    public function rules()
    {
        return [
            [['emp_id'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'emp_id' => 'ผู้สำรวจ',
        ];
    }
}
