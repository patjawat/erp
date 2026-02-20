<?php

namespace app\modules\health\models;

use Yii;

/**
 * This is the model class for table "health_lab".
 *
 * @property int $id
 * @property string $lab_code รหัสห้องปฏิบัติการ
 * @property string $lab_name ชื่อห้องปฏิบัติการ
 * @property float $lab_price ราคาห้องปฏิบัติการ
 * @property string|null $lab_type ประเภทห้องปฏิบัติการ
 * @property string|null $age_condition_type ประเภทเงื่อนไขอายุ (all|gte|lte|gt|lt|between)
 * @property int|null $age_condition_value ค่าอายุ (ปี)
 * @property int|null $age_condition_value_2 ค่าอายุที่สอง (ปี) สำหรับ between
 * @property string|null $gender_condition เงื่อนไขเพศ (all|male|female)
 * @property string|null $data_json data_json
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 * @property string|null $deleted_at วันที่ลบ
 * @property int|null $deleted_by ผู้ลบ
 */
class HealthLab extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'health_lab';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['lab_type', 'data_json', 'created_at', 'updated_at', 'created_by', 'updated_by', 'deleted_at', 'deleted_by'], 'default', 'value' => null],
            [['lab_code', 'lab_name', 'lab_price'], 'required'],
            [['lab_price'], 'number'],
            [['data_json', 'created_at', 'updated_at', 'deleted_at', 'age_condition_type', 'age_condition_value', 'age_condition_value_2', 'gender_condition'], 'safe'],
            [['created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['lab_code', 'lab_name', 'lab_type'], 'string', 'max' => 255],
            [['age_condition_type', 'gender_condition'], 'string', 'max' => 20],
            [['age_condition_type', 'gender_condition'], 'default', 'value' => 'all'],
            [['age_condition_value', 'age_condition_value_2'], 'integer', 'min' => 0],
            [['age_condition_value', 'age_condition_value_2'], 'default', 'value' => null],
            [['lab_code'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'lab_code' => 'Lab Code',
            'lab_name' => 'Lab Name',
            'lab_price' => 'Lab Price',
            'lab_type' => 'Lab Type',
            'age_condition_type' => 'ประเภทเงื่อนไขอายุ',
            'age_condition_value' => 'ค่าอายุ (ปี)',
            'age_condition_value_2' => 'ค่าอายุที่สอง (ปี)',
            'gender_condition' => 'เงื่อนไขเพศ',
            'data_json' => 'Data Json',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'deleted_at' => 'Deleted At',
            'deleted_by' => 'Deleted By',
        ];
    }

    /** ประเภทเงื่อนไขอายุสำหรับ dropdown */
    public static function ageConditionTypeOptions()
    {
        return [
            'all' => 'ทุกคน',
            'gte' => 'มากกว่าหรือเท่ากับ',
            'lte' => 'น้อยกว่าหรือเท่ากับ',
            'gt' => 'มากกว่า',
            'lt' => 'น้อยกว่า',
            'between' => 'ระหว่าง',
        ];
    }

    /** แปลงเงื่อนไขอายุเป็นข้อความสำหรับแสดง */
    public function getAgeConditionLabel()
    {
        $type = $this->age_condition_type ?? 'all';
        if ($type === 'all') {
            return 'ทุกคน';
        }
        $v = (int)$this->age_condition_value;
        $v2 = (int)$this->age_condition_value_2;
        $labels = [
            'gte' => "≥ {$v} ปี",
            'lte' => "≤ {$v} ปี",
            'gt' => "> {$v} ปี",
            'lt' => "< {$v} ปี",
            'between' => ($v && $v2) ? "{$v}–{$v2} ปี" : '-',
        ];
        return $labels[$type] ?? 'ทุกคน';
    }

    /**
     * ตรวจว่าอายุ (ปี) ตรงตามเงื่อนไขของรายการ Lab นี้หรือไม่
     * @param int $age อายุเป็นปี
     * @return bool
     */
    public function matchAgeCondition($age)
    {
        $type = $this->age_condition_type ?? 'all';
        if ($type === 'all') {
            return true;
        }
        $v = (int)$this->age_condition_value;
        $v2 = (int)$this->age_condition_value_2;
        $age = (int)$age;
        switch ($type) {
            case 'gte': return $age >= $v;
            case 'lte': return $age <= $v;
            case 'gt':  return $age > $v;
            case 'lt':  return $age < $v;
            case 'between': return $v2 && $age >= $v && $age <= $v2;
            default: return true;
        }
    }

}
