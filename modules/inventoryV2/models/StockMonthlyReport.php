<?php

namespace app\modules\inventoryV2\models;

use Yii;

/**
 * รายงานประจำเดือนระดับรายการ (item) ต่อคลัง
 * @property int $id
 * @property int $report_year
 * @property int $report_month
 * @property int $warehouse_id
 * @property string $item_code
 * @property string|null $unit_name
 * @property float $opening_qty
 * @property float $opening_value
 * @property float $in_qty
 * @property float $in_value
 * @property float $adjust_in_qty ปรับยอดเพิ่ม (qty บวก)
 * @property float $adjust_in_value มูลค่าปรับยอดเพิ่ม
 * @property float $adjust_out_qty ปรับยอดลด (qty ลบ — เก็บเป็นค่าบวก)
 * @property float $adjust_out_value มูลค่าปรับยอดลด
 * @property float $out_sub_qty จ่ายส่วนของ รพ.aq
 * @property float $out_sub_value มูลค่าจ่ายส่วนของ รพ.aq
 * @property float $out_hosp_qty จ่ายส่วนของโรงพยาบาล
 * @property float $out_hosp_value มูลค่าจ่ายส่วนของโรงพยาบาล
 * @property float $total_out_qty
 * @property float $total_out_value
 * @property float $closing_qty
 * @property float $closing_value
 */
class StockMonthlyReport extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'stock_monthly_report';
    }

    public function rules()
    {
        return [
            [['report_year', 'report_month', 'warehouse_id', 'item_code'], 'required'],
            [['report_year', 'report_month', 'warehouse_id', 'created_at', 'created_by'], 'integer'],
            [['opening_qty', 'opening_value', 'in_qty', 'in_value', 'adjust_in_qty', 'adjust_in_value', 'adjust_out_qty', 'adjust_out_value', 'out_sub_qty', 'out_sub_value', 'out_hosp_qty', 'out_hosp_value', 'total_out_qty', 'total_out_value', 'closing_qty', 'closing_value'], 'number'],
            [['item_code'], 'string', 'max' => 50],
            [['unit_name'], 'string', 'max' => 100],
        ];
    }

    public function attributeLabels()
    {
        return [
            'report_year' => 'ปี',
            'report_month' => 'เดือน',
            'warehouse_id' => 'คลัง',
            'item_code' => 'รหัสพัสดุ',
            'opening_qty' => 'สินค้าคงเหลือ',
            'in_qty' => 'ซื้อระหว่างเดือน',
            'adjust_in_qty' => 'ปรับยอดเพิ่ม',
            'adjust_out_qty' => 'ปรับยอดลด',
            'out_sub_qty' => 'จ่ายส่วนของ รพ.aq',
            'out_hosp_qty' => 'จ่ายส่วนของโรงพยาบาล',
            'total_out_qty' => 'รวมจ่าย',
            'closing_qty' => 'ยอดยกไป',
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
}
