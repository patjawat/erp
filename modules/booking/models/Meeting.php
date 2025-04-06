<?php

namespace app\modules\booking\models;

use Yii;
use yii\helpers\ArrayHelper;
use app\modules\booking\models\Room;

/**
 * This is the model class for table "meeting".
 *
 * @property int $id
 * @property string|null $ref
 * @property string $code รหัส
 * @property string $room_id รหัส
 * @property string $title หัวข้อการประชุ
 * @property string $date_start เริ่มวันที่
 * @property string $date_end ถึงวันที่
 * @property string $time_start เริ่มเวลา
 * @property string $time_end ถึงเวลา
 * @property int $thai_year ปีงบประมาณ
 * @property int|null $document_id ตามหนังสือ
 * @property int|null $emp_number จำนวนผู้เข้าร่วมประชุม
 * @property string $urgent ความเร่งด่วน
 * @property string $status สถานะ
 * @property string $emp_id ผู้ขอ
 * @property string|null $data_json json
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 * @property string|null $deleted_at วันที่ลบ
 * @property int|null $deleted_by ผู้ลบ
 */
class Meeting extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'meeting';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['code', 'room_id', 'title', 'date_start', 'date_end', 'time_start', 'time_end', 'thai_year', 'urgent', 'status', 'emp_id'], 'required'],
            [['date_start', 'date_end', 'data_json', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['thai_year', 'document_id', 'emp_number', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['ref', 'code', 'room_id', 'title', 'time_start', 'time_end', 'urgent', 'status', 'emp_id'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ref' => 'Ref',
            'code' => 'รหัส',
            'room_id' => 'รหัส',
            'title' => 'หัวข้อการประชุ',
            'date_start' => 'เริ่มวันที่',
            'date_end' => 'ถึงวันที่',
            'time_start' => 'เริ่มเวลา',
            'time_end' => 'ถึงเวลา',
            'thai_year' => 'ปีงบประมาณ',
            'document_id' => 'ตามหนังสือ',
            'emp_number' => 'จำนวนผู้เข้าร่วมประชุม',
            'urgent' => 'ความเร่งด่วน',
            'status' => 'สถานะ',
            'emp_id' => 'ผู้ขอ',
            'data_json' => 'json',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไข',
            'created_by' => 'ผู้สร้าง',
            'updated_by' => 'ผู้แก้ไข',
            'deleted_at' => 'วันที่ลบ',
            'deleted_by' => 'ผู้ลบ',
        ];
    }


    public function listRooms()
    {
        $model = Room::find()->where(['name' => 'meeting_room'])->all();
        return ArrayHelper::map($model, 'code', 'title');
    }
}
