<?php

namespace app\modules\inventory\models;

use Yii;

/**
 * This is the model class for table "whcheck".
 *
 * @property int $id
 * @property string|null $ref
 * @property string|null $check_code รหัส
 * @property string|null $check_date วันที่ตรวจรับ
 * @property string|null $check_type ประเภทการรับ
 * @property string|null $check_store คลัง
 * @property string|null $check_from รับจาก
 * @property string|null $check_hr เจ้าหน้าที่
 * @property string|null $check_status สถานะการตรวจรับ
 * @property string|null $data_json
 * @property string|null $updated_at วันเวลาแก้ไข
 * @property string|null $created_at วันเวลาสร้าง
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 */
class Whcheck extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'whcheck';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['check_date', 'data_json', 'updated_at', 'created_at'], 'safe'],
            [['created_by', 'updated_by'], 'integer'],
            [['ref', 'check_code', 'check_store', 'check_from', 'check_hr', 'check_status'], 'string', 'max' => 255],
            [['check_type'], 'string', 'max' => 20],
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
            'check_code' => 'รหัส',
            'check_date' => 'วันที่ตรวจรับ',
            'check_type' => 'ประเภทการรับ',
            'check_store' => 'คลัง',
            'check_from' => 'รับจาก',
            'check_hr' => 'เจ้าหน้าที่',
            'check_status' => 'สถานะการตรวจรับ',
            'data_json' => 'Data Json',
            'updated_at' => 'วันเวลาแก้ไข',
            'created_at' => 'วันเวลาสร้าง',
            'created_by' => 'ผู้สร้าง',
            'updated_by' => 'ผู้แก้ไข',
        ];
    }
}
