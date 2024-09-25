<?php

namespace app\modules\lm\models;

use Yii;

/**
 * This is the model class for table "leave_permission".
 *
 * @property int $id
 * @property string|null $emp_id พนักงาน
 * @property int|null $service_time อายุงาน
 * @property int|null $point จำนวนที่ลาได้
 * @property int|null $last_point สะสมวันลา
 * @property int|null $new_point ลาได้
 * @property string|null $data_json
 * @property int|null $thai_year ปีงบประมาณ
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 * @property string|null $deleted_at วันที่ลบ
 * @property int|null $deleted_by ผู้ลบ
 */
class LeavePermission extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'leave_permission';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['service_time', 'point', 'last_point', 'new_point', 'thai_year', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['data_json', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['emp_id'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'emp_id' => 'พนักงาน',
            'service_time' => 'อายุงาน',
            'point' => 'จำนวนที่ลาได้',
            'last_point' => 'สะสมวันลา',
            'new_point' => 'ลาได้',
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
}
