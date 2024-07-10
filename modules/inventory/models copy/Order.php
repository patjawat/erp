<?php

namespace app\modules\inventory\models;

use Yii;

/**
 * This is the model class for table "order".
 *
 * @property int $id
 * @property string|null $ref
 * @property string|null $name ชื่อตารางเก็บข้อมูล
 * @property string|null $category_id หมวดหมูหลักที่เก็บ
 * @property string|null $code รหัส
 * @property string|null $pr_number เลขที่ขอซื้อ
 * @property string|null $po_number ที่ที่สั่งซื้อ
 * @property int|null $item_id รายการที่เก็บ
 * @property float|null $price ราคา
 * @property int|null $amount จำนวน
 * @property int|null $status สถานะ
 * @property string|null $data_json
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 */
class Order extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'order';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['item_id', 'qty', 'status', 'created_by', 'updated_by'], 'integer'],
            [['price'], 'number'],
            [['data_json', 'created_at', 'updated_at'], 'safe'],
            [['ref', 'name', 'category_id', 'code', 'pr_number', 'po_number'], 'string', 'max' => 255],
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
            'name' => 'Name',
            'category_id' => 'Category ID',
            'code' => 'Code',
            'pr_number' => 'Pr Number',
            'po_number' => 'Po Number',
            'item_id' => 'Item ID',
            'price' => 'Price',
            'qty' => 'qty',
            'status' => 'Status',
            'data_json' => 'Data Json',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }
}
