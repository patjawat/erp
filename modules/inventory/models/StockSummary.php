<?php

namespace app\modules\inventory\models;

use Yii;

/**
 * This is the model class for table "stock_summary".
 *
 * @property string|null $receive_date
 * @property int|null $receive_month
 * @property int|null $receive_year
 * @property string|null $type_code รหัส
 * @property string|null $title ชื่อ
 * @property string $warehouse_name ชื่อคลังสินค้า
 * @property string|null $transaction_type ธุรกรรม
 * @property float|null $po_total
 * @property float|null $total_on_warehouse
 * @property float|null $total
 */
class StockSummary extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public $q;
    public $thai_year;
    public $date_start;
    public $date_end;
    public $q_month;
    public static function tableName()
    {
        return 'stock_summary';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['receive_date', 'q', 'date_start','q_month','thai_year','receive_month','type_code'], 'safe'],
            // [['receive_month', 'receive_year'], 'integer'],
            // [['warehouse_name'], 'required'],
            // [['po_total', 'total_on_warehouse', 'total'], 'number'],
            // [['type_code', 'title', 'transaction_type'], 'string', 'max' => 255],
            // [['warehouse_name'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'receive_date' => 'Receive Date',
            'receive_month' => 'Receive Month',
            'receive_year' => 'Receive Year',
            'type_code' => 'รหัส',
            'title' => 'ชื่อ',
            'warehouse_name' => 'ชื่อคลังสินค้า',
            'transaction_type' => 'ธุรกรรม',
            'po_total' => 'Po Total',
            'total_on_warehouse' => 'Total On Warehouse',
            'total' => 'Total',
        ];
    }
}
