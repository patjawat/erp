<?php

namespace app\modules\inventoryV2\models;

use Yii;

/**
 * รายการวัสดุหนึ่งบรรทัดในแผนวัสดุประจำปีที่บันทึกไว้
 *
 * เก็บตัวเลขที่ตัดสินใจแล้วทั้งชุด ไม่ใช่เก็บแค่ input แล้วคำนวณใหม่
 * เพราะฉบับที่ส่ง สสจ. ต้องอ่านย้อนได้เหมือนเดิมเสมอ แม้ประวัติการเบิกจะเปลี่ยนไปภายหลัง
 *
 * @property int $id
 * @property int $material_plan_id
 * @property string $item_code
 * @property string|null $item_name
 * @property string|null $category_id
 * @property string|null $category_title
 * @property string|null $unit_name
 * @property float $actual_usage ยอดใช้จริงเท่าที่เก็บได้ในปีฐาน
 * @property float $annual_usage ยอดใช้ปรับเป็นเต็มปีแล้ว
 * @property float $forecast_qty ประมาณการใช้
 * @property float $opening_qty ยอดคงคลัง
 * @property float $plan_qty ประมาณการจัดซื้อ
 * @property float $unit_price
 * @property string|null $price_source
 * @property float $plan_value
 * @property float $q1_qty
 * @property float $q2_qty
 * @property float $q3_qty
 * @property float $q4_qty
 * @property bool $is_manual
 * @property bool $is_adjusted
 * @property array|null $data_json
 */
class MaterialPlanItem extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'material_plan_item';
    }

    public function rules()
    {
        return [
            [['material_plan_id', 'item_code'], 'required'],
            [['material_plan_id', 'created_by'], 'integer'],
            [
                [
                    'actual_usage', 'annual_usage', 'forecast_qty', 'opening_qty', 'plan_qty',
                    'unit_price', 'plan_value', 'q1_qty', 'q2_qty', 'q3_qty', 'q4_qty',
                ],
                'number',
            ],
            [['is_manual', 'is_adjusted'], 'boolean'],
            [['data_json', 'created_at'], 'safe'],
            [['item_code', 'category_id'], 'string', 'max' => 50],
            [['item_name', 'category_title'], 'string', 'max' => 255],
            [['unit_name'], 'string', 'max' => 100],
            [['price_source'], 'string', 'max' => 20],
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ($insert) {
            $this->created_at = date('Y-m-d H:i:s');
            $this->created_by = Yii::$app->user->id;
        }

        return true;
    }

    /**
     * แปลงแถวที่หน้าจอคำนวณได้ ให้อยู่ในรูปที่บันทึกลงตารางได้
     *
     * @param array $row แถวจาก MaterialPlanForecastService::buildRows()
     */
    public static function attributesFromRow(int $planId, array $row): array
    {
        $quarters = array_values((array) ($row['quarters'] ?? [0, 0, 0, 0]));

        return [
            'material_plan_id' => $planId,
            'item_code' => (string) $row['item_code'],
            'item_name' => (string) ($row['item_name'] ?? ''),
            'category_id' => (string) ($row['category_id'] ?? ''),
            'category_title' => (string) ($row['category_title'] ?? ''),
            'unit_name' => (string) ($row['unit_name'] ?? ''),
            'actual_usage' => (float) ($row['actual_usage'] ?? 0),
            'annual_usage' => (float) ($row['annual_usage'] ?? 0),
            'forecast_qty' => (float) ($row['forecast_qty'] ?? 0),
            'opening_qty' => (float) ($row['opening_qty'] ?? 0),
            'plan_qty' => (float) ($row['plan_qty'] ?? 0),
            'unit_price' => (float) ($row['unit_price'] ?? 0),
            'price_source' => (string) ($row['price_source'] ?? ''),
            'plan_value' => (float) ($row['plan_value'] ?? 0),
            'q1_qty' => (float) ($quarters[0] ?? 0),
            'q2_qty' => (float) ($quarters[1] ?? 0),
            'q3_qty' => (float) ($quarters[2] ?? 0),
            'q4_qty' => (float) ($quarters[3] ?? 0),
            'is_manual' => !empty($row['is_manual']),
            'is_adjusted' => !empty($row['is_adjusted']),
            'data_json' => ['history' => (array) ($row['history'] ?? [])],
        ];
    }

    /**
     * แปลงกลับเป็นรูปแบบที่หน้าจอและตัวส่งออก Excel ใช้ เพื่อให้แผนที่บันทึกแล้ว
     * แสดงผลด้วยโค้ดชุดเดียวกับตอนคำนวณสด
     */
    public function toRow(): array
    {
        $quarters = [(float) $this->q1_qty, (float) $this->q2_qty, (float) $this->q3_qty, (float) $this->q4_qty];
        $unitPrice = (float) $this->unit_price;

        return [
            'item_code' => $this->item_code,
            'item_name' => (string) $this->item_name,
            'category_id' => (string) $this->category_id,
            'category_title' => (string) $this->category_title,
            'unit_name' => (string) $this->unit_name,
            'history' => (array) (($this->data_json['history'] ?? [])),
            'actual_usage' => (float) $this->actual_usage,
            'annual_usage' => (int) round((float) $this->annual_usage),
            'forecast_qty' => (int) round((float) $this->forecast_qty),
            'opening_qty' => (float) $this->opening_qty,
            'plan_qty' => (int) round((float) $this->plan_qty),
            'unit_price' => $unitPrice,
            'price_source' => (string) $this->price_source,
            'plan_value' => (float) $this->plan_value,
            'quarters' => array_map(static fn ($q) => (int) round($q), $quarters),
            'quarter_values' => array_map(static fn ($q) => round($q * $unitPrice, 2), $quarters),
            'is_manual' => (bool) $this->is_manual,
            'is_adjusted' => (bool) $this->is_adjusted,
        ];
    }
}
