<?php

namespace app\modules\booking\models;

use Yii;

/**
 * This is the model class for table "meeting_detail".
 *
 * @property int $id
 * @property int $meeting_id ID ของการประชุม
 * @property string|null $data_json json
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 * @property string|null $deleted_at วันที่ลบ
 * @property int|null $deleted_by ผู้ลบ
 */
class MeetingDetail extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'meeting_detail';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['meeting_id'], 'required'],
            [['meeting_id', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
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
            'meeting_id' => 'ID ของการประชุม',
            'data_json' => 'json',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไข',
            'created_by' => 'ผู้สร้าง',
            'updated_by' => 'ผู้แก้ไข',
            'deleted_at' => 'วันที่ลบ',
            'deleted_by' => 'ผู้ลบ',
        ];
    }
}
