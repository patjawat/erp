<?php

namespace app\modules\warehouse\models;

use Yii;

/**
 * This is the model class for table "whsup".
 *
 * @property int $id
 * @property string|null $ref
 * @property string|null $sup_code รหัสวัสดุ
 * @property string|null $sup_detail รายละเอียด
 * @property string|null $sup_type ประเภท
 * @property string|null $sup_store คลัง
 * @property string|null $sup_unit คลัง
 * @property string|null $data_json
 * @property string|null $updated_at วันเวลาแก้ไข
 * @property string|null $created_at วันเวลาสร้าง
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 */
class Whsup extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'whsup';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['data_json', 'updated_at', 'created_at'], 'safe'],
            [['created_by', 'updated_by'], 'integer'],
            [['ref', 'sup_code', 'sup_type', 'sup_store'], 'string', 'max' => 255],
            [['sup_detail'], 'string', 'max' => 600],
            [['sup_unit'], 'string', 'max' => 20],
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
            'sup_code' => 'รหัสวัสดุ',
            'sup_detail' => 'รายละเอียด',
            'sup_type' => 'ประเภท',
            'sup_store' => 'คลัง',
            'sup_unit' => 'คลัง',
            'data_json' => 'Data Json',
            'updated_at' => 'วันเวลาแก้ไข',
            'created_at' => 'วันเวลาสร้าง',
            'created_by' => 'ผู้สร้าง',
            'updated_by' => 'ผู้แก้ไข',
        ];
    }
}
