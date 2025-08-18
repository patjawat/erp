<?php

namespace app\modules\plan\models;

use Yii;

/**
 * This is the model class for table "plan_item".
 *
 * @property int $id
 * @property int $plan_id รหัสแผน
 * @property string $item_name ชื่อวัสดุ/บุคลากร/ค่าใช้สอย
 * @property int|null $quantity จำนวน
 * @property float|null $unit_price ราคาต่อหน่วย
 * @property float|null $total_price ราคารวม
 * @property string|null $data_json ยานพาหนะ
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 * @property string|null $deleted_at วันที่ลบ
 * @property int|null $deleted_by ผู้ลบ
 *
 * @property Plan $plan
 */
class PlanItem extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'plan_item';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['data_json', 'created_at', 'updated_at', 'created_by', 'updated_by', 'deleted_at', 'deleted_by'], 'default', 'value' => null],
            [['quantity'], 'default', 'value' => 1],
            [['total_price'], 'default', 'value' => 0.00],
            [['plan_id', 'item_name'], 'required'],
            [['plan_id', 'quantity', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['unit_price', 'total_price'], 'number'],
            [['data_json', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['item_name'], 'string', 'max' => 255],
            [['plan_id'], 'exist', 'skipOnError' => true, 'targetClass' => Plan::class, 'targetAttribute' => ['plan_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'plan_id' => 'Plan ID',
            'item_name' => 'Item Name',
            'quantity' => 'Quantity',
            'unit_price' => 'Unit Price',
            'total_price' => 'Total Price',
            'emp_id' => 'Emp ID',
            'data_json' => 'Data Json',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'deleted_at' => 'Deleted At',
            'deleted_by' => 'Deleted By',
        ];
    }

    /**
     * Gets query for [[Plan]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlan()
    {
        return $this->hasOne(Plan::class, ['id' => 'plan_id']);
    }

        public function beforeSave($insert)
    {
        $this->total_price = $this->quantity * $this->unit_price;
        return parent::beforeSave($insert);
    }

 

}
