<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "logs".
 *
 * @property string $id
 * @property string|null $data_json
 * @property string|null $name ชื่อการเก็บ
 * @property string|null $ref
 * @property int|null $thai_year ปีงบประมาณ
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 * @property string|null $deleted_at วันที่ลบ
 * @property int|null $deleted_by ผู้ลบ
 */
class Logs extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'logs';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['data_json', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['thai_year', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['id'], 'string', 'max' => 36],
            [['name', 'ref'], 'string', 'max' => 255],
            [['id'], 'unique'],
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
            'name' => 'ชื่อการเก็บ',
            'ref' => 'Ref',
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
