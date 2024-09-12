<?php

namespace app\modules\leave\models;

use Yii;

/**
 * This is the model class for table "leave_permission".
 *
 * @property int $id
 * @property int|null $leave_type_id ประเภทการลา
 * @property string|null $position_type_id ประเภทการลา
 * @property int|null $leave_days วันลา
 * @property int|null $leave_days_max สะสมวันลาได้ไม่เกิน
 * @property int|null $year_service อายุการทำงาน
 * @property int|null $point สะสมวันลา
 * @property int|null $point_days จำนวนวัน
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
            [['leave_type_id', 'leave_days', 'leave_days_max', 'year_service', 'point', 'point_days', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['data_json', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
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
            'leave_type_id' => 'ประเภทการลา',
            'position_type_id' => 'ประเภทการลา',
            'leave_days' => 'วันลา',
            'leave_days_max' => 'สะสมวันลาได้ไม่เกิน',
            'year_service' => 'อายุการทำงาน',
            'point' => 'สะสมวันลา',
            'point_days' => 'จำนวนวัน',
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
