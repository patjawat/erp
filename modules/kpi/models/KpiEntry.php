<?php

namespace app\modules\kpi\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * ผลงานรายงวดของ KPI (เดือน/ไตรมาส/ปี) ที่เจ้าของ KPI กรอกเอง
 *
 * @property int $id
 * @property int $kpi_item_id
 * @property string $period_type month / quarter / year
 * @property int $period_index month 1–12 (ต.ค.=1) / quarter 1–4 / year 1
 * @property float|null $value_num
 * @property string|null $value_text
 * @property int|null $recorded_by
 * @property string|null $recorded_at
 * @property string $confirm_status pending / confirmed / revise
 * @property KpiItem $item
 */
class KpiEntry extends ActiveRecord
{
    public const PERIOD_MONTH = 'month';
    public const PERIOD_QUARTER = 'quarter';
    public const PERIOD_YEAR = 'year';

    public const CONFIRM_PENDING = 'pending';
    public const CONFIRM_CONFIRMED = 'confirmed';
    public const CONFIRM_REVISE = 'revise';

    public static function tableName()
    {
        return '{{%kpi_entry}}';
    }

    public function rules()
    {
        return [
            [['kpi_item_id', 'period_type', 'period_index'], 'required'],
            [['kpi_item_id', 'period_index', 'recorded_by', 'confirmed_by'], 'integer'],
            [['value_num'], 'number'],
            [['value_text'], 'string'],
            [['recorded_at', 'confirmed_at', 'created_at', 'updated_at'], 'safe'],
            [['period_type'], 'in', 'range' => [self::PERIOD_MONTH, self::PERIOD_QUARTER, self::PERIOD_YEAR]],
            [['confirm_status'], 'in', 'range' => [self::CONFIRM_PENDING, self::CONFIRM_CONFIRMED, self::CONFIRM_REVISE]],
            [['confirm_status'], 'default', 'value' => self::CONFIRM_PENDING],
            [['kpi_item_id', 'period_type', 'period_index'], 'unique', 'targetAttribute' => ['kpi_item_id', 'period_type', 'period_index']],
            [['kpi_item_id'], 'exist', 'targetClass' => KpiItem::class, 'targetAttribute' => ['kpi_item_id' => 'id']],
        ];
    }

    public function getItem()
    {
        return $this->hasOne(KpiItem::class, ['id' => 'kpi_item_id']);
    }

    /**
     * แปลง period_index เชิงงบประมาณ (ต.ค.=1) เป็นเดือนปฏิทิน 1–12
     */
    public static function fiscalMonthToCalendar(int $fiscalIndex): int
    {
        // 1→ต.ค.(10) ... 3→ธ.ค.(12), 4→ม.ค.(1) ... 12→ก.ย.(9)
        return (($fiscalIndex + 8) % 12) + 1;
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $now = date('Y-m-d H:i:s');
            if ($insert) {
                $this->created_at = $now;
            }
            $this->updated_at = $now;
            return true;
        }
        return false;
    }
}
