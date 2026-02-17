<?php

namespace app\modules\inventoryV2\models;

use Yii;

/**
 * This is the model class for table "stock_balance".
 *
 * @property int $id
 * @property int $item_code
 * @property int $warehouse_id
 * @property string|null $lot_number
 * @property float|null $balance_qty จำนวนคงเหลือที่ใช้ได้จริง
 * @property string|null $ref
 * @property string|null $data_json
 * @property int|null $created_at
 * @property int|null $created_by
 * @property int|null $updated_at
 * @property int|null $updated_by
 */
class StockBalance extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'stock_balance';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['lot_number', 'ref', 'data_json', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'default', 'value' => null],
            [['balance_qty'], 'default', 'value' => 0.00],
            [['item_code', 'warehouse_id'], 'required'],
            [[ 'warehouse_id', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['balance_qty'], 'number'],
            [['data_json'], 'safe'],
            [['lot_number'], 'string', 'max' => 100],
            [['ref'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'item_code' => 'Item ID',
            'warehouse_id' => 'Warehouse ID',
            'lot_number' => 'Lot Number',
            'balance_qty' => 'Balance Qty',
            'ref' => 'Ref',
            'data_json' => 'Data Json',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

}
