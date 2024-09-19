<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "calendar".
 *
 * @property int $id
 * @property int|null $emp_id พนีกงาน
 * @property string|null $name ชื่อการเก็บ
 * @property string|null $title คำอธิบาย
 * @property string|null $date_start วันที่ลา
 * @property string|null $date_end ถึงวันที่
 * @property string|null $status สถานะ
 * @property string|null $data_json
 * @property int|null $thai_year ปีงบประมาณ
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 * @property string|null $deleted_at วันที่ลบ
 * @property int|null $deleted_by ผู้ลบ
 */
class Calendar extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'calendar';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['emp_id', 'thai_year', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['date_start', 'date_end', 'data_json', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['name', 'title', 'status'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'emp_id' => 'พนีกงาน',
            'name' => 'ชื่อการเก็บ',
            'title' => 'คำอธิบาย',
            'date_start' => 'วันที่ลา',
            'date_end' => 'ถึงวันที่',
            'status' => 'สถานะ',
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
