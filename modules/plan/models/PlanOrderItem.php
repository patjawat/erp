<?php

namespace app\modules\plan\models;

use Yii;
use app\models\Categorise;

/**
 * This is the model class for table "plan_item".
 *
 * @property int $id
 * @property int $plan_order_id รหัสแผน
 * @property string $title ชื่อวัสดุ/บุคลากร/ค่าใช้สอย
 * @property string $item_id รหัสที่ใช้เชื่อมกัน
 * @property string $item_name ชื่อการเชื่อมต่อ
 * @property int|null $qty จำนวน
 * @property float|null $unit_price ราคาต่อหน่วย
 * @property float|null $total_price ราคารวม
 * @property string|null $data_json ยานพาหนะ
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 * @property string|null $deleted_at วันที่ลบ
 * @property int|null $deleted_by ผู้ลบ
 */
class PlanOrderItem extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'plan_order_item';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['data_json', 'created_at', 'updated_at', 'created_by', 'updated_by', 'deleted_at', 'deleted_by'], 'default', 'value' => null],
            [['qty'], 'default', 'value' => 1],
            [['total_price'], 'default', 'value' => 0.00],
            [['plan_order_id', 'title', 'item_id', 'item_name'], 'required'],
            [['plan_order_id', 'qty', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['unit_price', 'total_price'], 'number'],
            [['data_json', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['title', 'item_id', 'item_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'plan_order_id' => 'Plan Order ID',
            'title' => 'Title',
            'item_id' => 'Item ID',
            'item_name' => 'Item Name',
            'qty' => 'Qty',
            'unit_price' => 'Unit Price',
            'total_price' => 'Total Price',
            'data_json' => 'Data Json',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'deleted_at' => 'Deleted At',
            'deleted_by' => 'Deleted By',
        ];
    }


    public function getPlanCategory()
    {
        return $this->hasOne(Categorise::class, ['code' => 'category_id'])->andOnCondition(['name' => 'plan_type']);
    }
    
}
