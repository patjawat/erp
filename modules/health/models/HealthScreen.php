<?php

namespace app\modules\health\models;

use Yii;
use app\components\AppHelper;
use app\modules\hr\models\Employees;

/**
 * This is the model class for table "health_screen".
 *
 * @property int $id
 * @property int $thai_year ปีงบประมาณ
 * @property int $emp_id รหัสพนักงาน
 * @property string|null $date_checkup ข้อมูลการตรวจสุขภาพ
 * @property string|null $data_json data_json
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 * @property string|null $deleted_at วันที่ลบ
 * @property int|null $deleted_by ผู้ลบ
 */
class HealthScreen extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'health_screen';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['date_checkup', 'data_json', 'created_at', 'updated_at', 'created_by', 'updated_by', 'deleted_at', 'deleted_by'], 'default', 'value' => null],
            [['thai_year', 'emp_id','date_checkup','weight','height'], 'required'],
            [['thai_year', 'emp_id', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['date_checkup', 'data_json', 'created_at', 'updated_at', 'deleted_at','ref'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'thai_year' => 'ปีงบประมาณ',
            'emp_id' => 'รหัสพนักงาน',
            'date_checkup' => 'วันที่ตรวจสุขภาพ',
            'data_json' => 'ข้อมูล JSON',
            'weight' => 'น้ำหนัก',
            'height' => 'ส่วนสูง',
            'bmi' => 'BMI',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไข',
            'created_by' => 'ผู้สร้าง',
            'updated_by' => 'ผู้แก้ไข',
            'deleted_at' => 'วันที่ลบ',
            'deleted_by' => 'ผู้ลบ',
        ];
    }

        public function getEmployee()
    {
        return $this->hasOne(Employees::class, ['id' => 'emp_id']);
    }

    

            public function getBmiResult()
    {
        try {
            $bmi = $this->data_json['bmi'] ?? 0;
            return AppHelper::getBmiResult($bmi);
        } catch (\Throwable $th) {
            return NULL;
        }
    }

    public function getYearList()
{
    // ดึงค่า thai_year ที่ไม่ซ้ำกันจากฐานข้อมูลออกมา
    $years = self::find()
        ->select(['thai_year'])
        ->distinct()
        ->where(['not', ['thai_year' => null]])
        ->orderBy(['thai_year' => SORT_DESC])
        ->column();

    // จัดรูปแบบให้เป็น Array [2569 => '2569', 2568 => '2568'] สำหรับ Select2
    return array_combine($years, $years);
}

    
}
