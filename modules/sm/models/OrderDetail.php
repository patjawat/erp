<?php

namespace app\modules\sm\models;

use Yii;

/**
 * This is the model class for table "order_detail".
 *
 * @property int $id
 * @property string|null $ref
 * @property string|null $order_id รหัส order
 * @property string|null $product_id รหัสทรัพย์สิน
 * @property string|null $price ราคาต่อหน่วย
 * @property int|null $amount จำนวน
 * @property string|null $data_json
 * @property string|null $updated_at
 * @property string|null $created_at
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 *
 * @property Order $order
 */
class OrderDetail extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'order_detail';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['amount', 'created_by', 'updated_by'], 'integer'],
            [['data_json', 'updated_at', 'created_at'], 'safe'],
            [['ref', 'product_id', 'price'], 'string', 'max' => 255],
            [['order_id'], 'string', 'max' => 100],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::class, 'targetAttribute' => ['order_id' => 'id']],
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
            'order_id' => 'Order ID',
            'product_id' => 'Product ID',
            'price' => 'Price',
            'amount' => 'Amount',
            'data_json' => 'Data Json',
            'updated_at' => 'Updated At',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[Order]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::class, ['id' => 'order_id']);
    }
}
