<?php

namespace app\modules\leave\models;

use Yii;

/**
 * This is the model class for table "leave_entitlements".
 *
 * @property int $id
 * @property int|null $emp_id บุคลากร
 * @property int|null $leave_type_id ประเภทการลา 
 * @property int|null $days_available จำนวนวันที่มีสิทธิลา
 * @property string|null $data_json
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 * @property string|null $deleted_at วันที่ลบ
 * @property int|null $deleted_by ผู้ลบ
 */
class LeaveEntitlements extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'leave_entitlements';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['emp_id', 'leave_type_id', 'days_available', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['data_json', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'emp_id' => 'บุคลากร',
            'leave_type_id' => 'ประเภทการลา ',
            'days_available' => 'จำนวนวันที่มีสิทธิลา',
            'data_json' => 'Data Json',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไข',
            'created_by' => 'ผู้สร้าง',
            'updated_by' => 'ผู้แก้ไข',
            'deleted_at' => 'วันที่ลบ',
            'deleted_by' => 'ผู้ลบ',
        ];
    }
}
