<?php

namespace app\modules\kpi\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * คะแนน KPI แต่ละตัวต่อรอบสรุป (H1/H2/FULL) พร้อม snapshot ค่า ณ วันสรุป
 * เมื่อ status = confirmed แล้ว snapshot จะถูก freeze ไม่กระทบจากการแก้ kpi_item ภายหลัง
 *
 * @property int $id
 * @property int $kpi_item_id
 * @property string $round H1 / H2 / FULL
 * @property string|null $indicator_snapshot
 * @property string|null $target_snapshot
 * @property float|null $weight_snapshot
 * @property string|null $result_snapshot
 * @property string|null $self_result_text
 * @property float|null $achievement_pct
 * @property float|null $score
 * @property string $status draft / confirmed
 * @property KpiItem $item
 */
class KpiItemScore extends ActiveRecord
{
    public const ROUND_H1 = 'H1';     // ต.ค.–มี.ค.
    public const ROUND_H2 = 'H2';     // เม.ย.–ก.ย.
    public const ROUND_FULL = 'FULL'; // ทั้งปี

    public const STATUS_DRAFT = 'draft';
    public const STATUS_CONFIRMED = 'confirmed';

    public static function tableName()
    {
        return '{{%kpi_item_score}}';
    }

    public function rules()
    {
        return [
            [['kpi_item_id', 'round'], 'required'],
            [['kpi_item_id', 'confirmed_by'], 'integer'],
            [['weight_snapshot', 'achievement_pct', 'score'], 'number'],
            [['result_snapshot', 'self_result_text', 'note'], 'string'],
            [['confirmed_at', 'created_at', 'updated_at'], 'safe'],
            [['indicator_snapshot', 'target_snapshot'], 'string', 'max' => 500],
            [['round'], 'in', 'range' => [self::ROUND_H1, self::ROUND_H2, self::ROUND_FULL]],
            [['status'], 'in', 'range' => [self::STATUS_DRAFT, self::STATUS_CONFIRMED]],
            [['status'], 'default', 'value' => self::STATUS_DRAFT],
            [['kpi_item_id', 'round'], 'unique', 'targetAttribute' => ['kpi_item_id', 'round']],
            [['kpi_item_id'], 'exist', 'targetClass' => KpiItem::class, 'targetAttribute' => ['kpi_item_id' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'round' => 'รอบสรุป',
            'self_result_text' => 'สรุปผลตนเอง',
            'achievement_pct' => 'ร้อยละบรรลุเป้า',
            'score' => 'คะแนนถ่วงน้ำหนัก',
            'status' => 'สถานะ',
        ];
    }

    public function getItem()
    {
        return $this->hasOne(KpiItem::class, ['id' => 'kpi_item_id']);
    }

    public static function roundLabels(): array
    {
        return [
            self::ROUND_H1 => 'รอบ 6 เดือนแรก (ต.ค.–มี.ค.)',
            self::ROUND_H2 => 'รอบ 6 เดือนหลัง (เม.ย.–ก.ย.)',
            self::ROUND_FULL => 'สรุปทั้งปี',
        ];
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
