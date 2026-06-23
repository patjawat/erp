<?php

namespace app\modules\inventoryV2\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

/**
 * Model สำหรับตาราง stock_item_warehouse_setting
 *
 * เก็บค่า min_qty / max_qty ของวัสดุ "ต่อคลัง"
 * คลัง × วัสดุ ที่ไม่มี row = admin ยังไม่ได้ตั้งค่า → ไม่ alert critical
 *
 * @property int $id
 * @property string $item_code
 * @property int $warehouse_id
 * @property float $min_qty
 * @property float $max_qty
 * @property string|null $note
 * @property int $is_active
 * @property array|null $data_json
 * @property int|null $created_at
 * @property int|null $created_by
 * @property int|null $updated_at
 * @property int|null $updated_by
 *
 * @property StockItem $item
 * @property Warehouse $warehouse
 */
class StockItemWarehouseSetting extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'stock_item_warehouse_setting';
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
            BlameableBehavior::class,
        ];
    }

    public function rules()
    {
        return [
            [['item_code', 'warehouse_id', 'min_qty', 'max_qty'], 'required'],
            [['warehouse_id', 'is_active'], 'integer'],
            [['min_qty', 'max_qty'], 'number', 'min' => 0],
            [['data_json'], 'safe'],
            [['item_code'], 'string', 'max' => 50],
            [['note'], 'string', 'max' => 255],
            [['is_active'], 'default', 'value' => 1],
            [['min_qty', 'max_qty'], 'default', 'value' => 0],
            [['max_qty'], 'compare', 'compareAttribute' => 'min_qty', 'operator' => '>=', 'type' => 'number',
                'message' => 'max ต้องไม่น้อยกว่า min'],
            [['item_code', 'warehouse_id'], 'unique', 'targetAttribute' => ['item_code', 'warehouse_id'],
                'message' => 'มีการตั้งค่า min/max ของวัสดุนี้ในคลังนี้แล้ว'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'item_code' => 'รหัสวัสดุ',
            'warehouse_id' => 'คลัง',
            'min_qty' => 'จำนวนขั้นต่ำ',
            'max_qty' => 'จำนวนขั้นสูง',
            'note' => 'หมายเหตุ',
            'is_active' => 'เปิดใช้งาน',
        ];
    }

    public function getItem()
    {
        return $this->hasOne(StockItem::class, ['code' => 'item_code']);
    }

    public function getWarehouse()
    {
        return $this->hasOne(Warehouse::class, ['id' => 'warehouse_id']);
    }

    /**
     * ดึงค่า min/max ของ item + warehouse ถ้ามี (เป็น array หรือ null)
     */
    public static function findFor($item_code, $warehouse_id)
    {
        return static::find()
            ->where([
                'item_code' => $item_code,
                'warehouse_id' => $warehouse_id,
                'is_active' => 1,
            ])
            ->one();
    }
}
