<?php

namespace app\modules\hr\models;

use Yii;

/**
 * This is the model class for table "development_detail".
 *
 * @property int $id
 * @property int $development_id ID ของการพัฒนา
 * @property string $name ชื่อของการเก็บข้อมูล
 * @property string $items_id รหัสรายการ
 * @property string $emp_id รหัสบุคลากร
 * @property int $qty จํานวน
 * @property float|null $price ราคา
 * @property string|null $data_json ยานพาหนะ
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 * @property string|null $deleted_at วันที่ลบ
 * @property int|null $deleted_by ผู้ลบ
 */
class DevelopmentDetail extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'development_detail';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['price', 'data_json', 'created_at', 'updated_at', 'created_by', 'updated_by', 'deleted_at', 'deleted_by'], 'default', 'value' => null],
            [['development_id', 'name', 'items_id', 'emp_id', 'qty'], 'required'],
            [['development_id', 'qty', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['price'], 'number'],
            [['data_json', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['name', 'items_id', 'emp_id'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'development_id' => 'ID ของการพัฒนา',
            'name' => 'ชื่อของการเก็บข้อมูล',
            'items_id' => 'รหัสรายการ',
            'emp_id' => 'รหัสบุคลากร',
            'qty' => 'จํานวน',
            'price' => 'ราคา',
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
