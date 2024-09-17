<?php

namespace app\modules\lm\models;

use Yii;

/**
 * This is the model class for table "leave_permission".
 *
 * @property int $id
 * @property int|null $leave_type_id ประเภทการลา
 * @property string|null $position_type_id ประเภทการลา
 * @property int|null $service_time อายุงาน
 * @property string|null $service_type ประเภทอายุงาน
 * @property int|null $point สะสมวันลา
 * @property int|null $days_point จำนวนวัน
 * @property string|null $data_json
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
            [['leave_type_id', 'service_time', 'point', 'days_point', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['data_json', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['position_type_id', 'service_type'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'leave_type_id' => 'ประเภทการลา',
            'position_type_id' => 'ประเภทการลา',
            'service_time' => 'อายุงาน',
            'service_type' => 'ประเภทอายุงาน',
            'point' => 'สะสมวันลา',
            'days_point' => 'จำนวนวัน',
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
