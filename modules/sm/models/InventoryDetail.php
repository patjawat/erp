<?php

namespace app\modules\sm\models;

use Yii;

/**
 * This is the model class for table "inventory_detail".
 *
 * @property int $id
 * @property string|null $ref
 * @property string|null $inventory_code
 * @property string|null $code
 * @property int|null $emp_id
 * @property string|null $asset_item รหัสทรัพย์สิน
 * @property string|null $name ชื่อการบันทึก
 * @property string|null $price ราคาต่อหน่วย
 * @property int|null $qty จำนวน
 * @property string|null $data_json
 * @property string|null $updated_at
 * @property string|null $created_at
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 */
class InventoryDetail extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'inventory_detail';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['emp_id', 'qty', 'created_by', 'updated_by'], 'integer'],
            [['data_json', 'updated_at', 'created_at'], 'safe'],
            [['ref', 'inventory_code', 'code', 'asset_item', 'name', 'price'], 'string', 'max' => 255],
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
            'inventory_code' => 'Inventory Code',
            'code' => 'Code',
            'emp_id' => 'Emp ID',
            'asset_item' => 'Asset Item',
            'name' => 'Name',
            'price' => 'Price',
            'qty' => 'Qty',
            'data_json' => 'Data Json',
            'updated_at' => 'Updated At',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }
}
