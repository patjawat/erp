<?php

namespace app\modules\hr\models;

use Yii;

/**
 * This is the model class for table "leave_role".
 *
 * @property int $id
 * @property string|null $data_json
 * @property int $emp_id
 * @property int $thai_year
 * @property int $work_year อายุงาน
 * @property string|null $position_type_id ตำแหน่ง
 * @property int $max_point สิทธิสะสมวัน
 * @property int $point ยอดยกมา/วันลาคงเหลือ
 * @property int $point_use ใช้ไปแล้ว
 */
class LeaveRole extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'leave_role';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['data_json'], 'safe'],
            [['emp_id', 'thai_year', 'work_year', 'max_point', 'point', 'point_use'], 'required'],
            [['emp_id', 'thai_year', 'work_year', 'max_point', 'point', 'point_use'], 'integer'],
            [['position_type_id'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'data_json' => 'Data Json',
            'emp_id' => 'Emp ID',
            'thai_year' => 'Thai Year',
            'work_year' => 'อายุงาน',
            'position_type_id' => 'ตำแหน่ง',
            'max_point' => 'สิทธิสะสมวัน',
            'point' => 'ยอดยกมา/วันลาคงเหลือ',
            'point_use' => 'ใช้ไปแล้ว',
        ];
    }
}
