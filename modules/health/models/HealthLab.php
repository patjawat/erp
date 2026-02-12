<?php

namespace app\modules\health\models;

use Yii;

/**
 * This is the model class for table "health_lab".
 *
 * @property int $lab_code
 * @property string $lab_name ชื่อห้องปฏิบัติการ
 * @property float $lab_price ราคาห้องปฏิบัติการ
 * @property string|null $lab_type ประเภทห้องปฏิบัติการ
 * @property string|null $data_json data_json
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 * @property string|null $deleted_at วันที่ลบ
 * @property int|null $deleted_by ผู้ลบ
 */
class HealthLab extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'health_lab';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['lab_type', 'data_json', 'created_at', 'updated_at', 'created_by', 'updated_by', 'deleted_at', 'deleted_by'], 'default', 'value' => null],
            [['lab_name', 'lab_price'], 'required'],
            [['lab_price'], 'number'],
            [['data_json', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['lab_name', 'lab_type'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'lab_code' => 'Lab Code',
            'lab_name' => 'Lab Name',
            'lab_price' => 'Lab Price',
            'lab_type' => 'Lab Type',
            'data_json' => 'Data Json',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'deleted_at' => 'Deleted At',
            'deleted_by' => 'Deleted By',
        ];
    }

}
