<?php

namespace app\modules\booking\models;

use Yii;

/**
 * This is the model class for table "booking_detail".
 *
 * @property int $id
 * @property string|null $ref
 * @property string|null $bookig_id เชื่อมกับตารางหลัก
 * @property string|null $name ชื่อการเก็บข้อมุล,ผู้ร่วมเดินทาง,บุคคลอื่นร่วมเดินทาง,งานมอบหมาย
 * @property string|null $data_json ยานพาหนะ
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 * @property string|null $deleted_at วันที่ลบ
 * @property int|null $deleted_by ผู้ลบ
 */
class BookingDetail extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'booking_detail';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['data_json', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['ref', 'bookig_id', 'name'], 'string', 'max' => 255],
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
            'bookig_id' => 'เชื่อมกับตารางหลัก',
            'name' => 'ชื่อการเก็บข้อมุล,ผู้ร่วมเดินทาง,บุคคลอื่นร่วมเดินทาง,งานมอบหมาย',
            'data_json' => 'ยานพาหนะ',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไข',
            'created_by' => 'ผู้สร้าง',
            'updated_by' => 'ผู้แก้ไข',
            'deleted_at' => 'วันที่ลบ',
            'deleted_by' => 'ผู้ลบ',
        ];
    }
}
