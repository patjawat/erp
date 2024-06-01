<?php

namespace app\modules\sm\models;

use Yii;

/**
 * This is the model class for table "sup_vendor".
 *
 * @property int $id
 * @property string|null $ref
 * @property string|null $vendor_name ตัวแทนจำหน่าย
 * @property string|null $vendor_tel เบอร์โทร
 * @property string|null $vendor_add ที่อยู่
 * @property string|null $vendor_contact ผู้ติดต่อ
 * @property string|null $data_json
 * @property string|null $updated_at วันเวลาแก้ไข
 * @property string|null $created_at วันเวลาสร้าง
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 */
class SupVendor extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public $vendor_tel,$vendor_add,$vendor_contact;
    public static function tableName()
    {
        return 'sup_vendor';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['data_json', 'updated_at', 'created_at','vendor_tel','vendor_add','vendor_contact'], 'safe'],
            [['created_by', 'updated_by'], 'integer'],
            [['ref', 'vendor_name', 'vendor_tel', 'vendor_contact'], 'string', 'max' => 255],
            [['vendor_add'], 'string', 'max' => 600],
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
            'vendor_name' => 'ตัวแทนจำหน่าย',
            'vendor_tel' => 'เบอร์โทร',
            'vendor_add' => 'ที่อยู่',
            'vendor_contact' => 'ผู้ติดต่อ',
            'data_json' => 'Data Json',
            'updated_at' => 'วันเวลาแก้ไข',
            'created_at' => 'วันเวลาสร้าง',
            'created_by' => 'ผู้สร้าง',
            'updated_by' => 'ผู้แก้ไข',
        ];
    }

    public function afterFind()
    {

        $this->vendor_tel = isset($this->data_json['vendor_tel']) ? $this->data_json['vendor_tel'] : '-';
        $this->vendor_add = isset($this->data_json['vendor_add']) ? $this->data_json['vendor_add'] : '-';
        $this->vendor_contact = isset($this->data_json['vendor_contact']) ? $this->data_json['vendor_contact'] : '-';
        parent::afterFind();
    }
}
