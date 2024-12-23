<?php

namespace app\modules\hr\models;

use Yii;
use app\models\Categorise;

/**
 * This is the model class for table "leave_policies".
 *
 * @property int $id
 * @property string|null $position_type_id ประเภทตำแหน่ง
 * @property string|null $leave_type_id ประเภทการลา
 * @property float $month_of_service เดือนที่มีสิทธิลา
 * @property float $year_of_service ปีที่มีสิทธิลา
 * @property int $days สิทธิวันลา
 * @property int $max_days วันลาสะสมสูงสุด
 * @property int $accumulation สิทธิสะสมวันลา
 * @property string|null $additional_rules กฎเพิ่มเติม เช่น สิทธิสะสมเมื่อครบ 10 ปี
 * @property string|null $emp_id พนักงาน
 * @property int|null $leave_before_days จำนวนวันลาสะสม
 * @property string|null $data_json
 * @property int|null $thai_year ปีงบประมาณ
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 * @property string|null $deleted_at วันที่ลบ
 * @property int|null $deleted_by ผู้ลบ
 */
class LeavePolicies extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'leave_policies';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['month_of_service', 'year_of_service'], 'number'],
            [['days', 'max_days', 'accumulation'], 'required'],
            [['days', 'max_days', 'accumulation', 'leave_before_days', 'thai_year', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['data_json', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['position_type_id', 'leave_type_id', 'additional_rules', 'emp_id'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'position_type_id' => 'ประเภทตำแหน่ง',
            'leave_type_id' => 'ประเภทการลา',
            'month_of_service' => 'เดือนที่มีสิทธิลา',
            'year_of_service' => 'ปีที่มีสิทธิลา',
            'days' => 'สิทธิวันลา',
            'max_days' => 'วันลาสะสมสูงสุด',
            'accumulation' => 'สิทธิสะสมวันลา',
            'additional_rules' => 'กฎเพิ่มเติม เช่น สิทธิสะสมเมื่อครบ 10 ปี',
            'emp_id' => 'พนักงาน',
            'leave_before_days' => 'จำนวนวันลาสะสม',
            'data_json' => 'Data Json',
            'thai_year' => 'ปีงบประมาณ',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไข',
            'created_by' => 'ผู้สร้าง',
            'updated_by' => 'ผู้แก้ไข',
            'deleted_at' => 'วันที่ลบ',
            'deleted_by' => 'ผู้ลบ',
        ];
    }
    public function getPositionType()
    {
        return $this->hasOne(Categorise::class, ['code' => 'position_type_id'])->andOnCondition(['name' => 'position_type']);
    }
}
