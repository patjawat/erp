<?php

namespace app\modules\kpi\models;

use app\modules\jd\models\JdEmployeeSection;
use Yii;
use yii\db\ActiveRecord;

/**
 * KPI แต่ละตัวในชุดประจำปี
 *
 * @property int $id
 * @property int $cycle_id
 * @property string $source_type jd / manual
 * @property int|null $source_jd_section_id
 * @property string $indicator
 * @property string|null $target_text
 * @property float|null $target_value
 * @property string|null $unit
 * @property string $value_type numeric / qualitative
 * @property string $frequency monthly / quarterly / yearly
 * @property float $weight
 * @property int $sort_order
 * @property string $status active / removed
 * @property KpiCycle $cycle
 * @property KpiEntry[] $entries
 * @property KpiItemScore[] $scores
 */
class KpiItem extends ActiveRecord
{
    public const SOURCE_JD = 'jd';
    public const SOURCE_MANUAL = 'manual';

    public const TYPE_NUMERIC = 'numeric';
    public const TYPE_QUALITATIVE = 'qualitative';

    public const FREQ_MONTHLY = 'monthly';
    public const FREQ_QUARTERLY = 'quarterly';
    public const FREQ_YEARLY = 'yearly';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_REMOVED = 'removed';

    public const AGG_SUM = 'sum';
    public const AGG_AVG = 'avg';
    public const AGG_MIN = 'min';
    public const AGG_MAX = 'max';
    public const AGG_LAST = 'last';

    public const DIR_ASC = 'asc';   // ผลงานมากขึ้น = ดีขึ้น
    public const DIR_DESC = 'desc'; // ผลงานน้อยลง = ดีขึ้น

    public static function aggregationLabels(): array
    {
        return [
            self::AGG_SUM => 'ผลรวม',
            self::AGG_AVG => 'ค่าเฉลี่ย',
            self::AGG_MIN => 'ค่าต่ำสุด',
            self::AGG_MAX => 'ค่าสูงสุด',
            self::AGG_LAST => 'ค่าล่าสุด',
        ];
    }

    public static function tableName()
    {
        return '{{%kpi_item}}';
    }

    public function rules()
    {
        return [
            [['cycle_id', 'indicator'], 'required'],
            [['cycle_id', 'source_jd_section_id', 'sort_order', 'confirmed_by', 'removed_by', 'created_by', 'updated_by'], 'integer'],
            [['target_value', 'weight', 'level1', 'level2', 'level3', 'level4', 'level5'], 'number'],
            [['direction'], 'in', 'range' => [self::DIR_ASC, self::DIR_DESC]],
            [['direction'], 'default', 'value' => self::DIR_ASC],
            [['created_at', 'updated_at', 'confirmed_at', 'removed_at'], 'safe'],
            [['indicator', 'target_text'], 'string', 'max' => 500],
            [['removed_reason'], 'string', 'max' => 500],
            [['unit'], 'string', 'max' => 50],
            [['source_type'], 'in', 'range' => [self::SOURCE_JD, self::SOURCE_MANUAL]],
            [['value_type'], 'in', 'range' => [self::TYPE_NUMERIC, self::TYPE_QUALITATIVE]],
            [['frequency'], 'in', 'range' => [self::FREQ_MONTHLY, self::FREQ_QUARTERLY, self::FREQ_YEARLY]],
            [['aggregation'], 'in', 'range' => [self::AGG_SUM, self::AGG_AVG, self::AGG_MIN, self::AGG_MAX, self::AGG_LAST]],
            [['aggregation'], 'default', 'value' => self::AGG_AVG],
            [['status'], 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_REMOVED]],
            [['source_type'], 'default', 'value' => self::SOURCE_MANUAL],
            [['value_type'], 'default', 'value' => self::TYPE_NUMERIC],
            [['frequency'], 'default', 'value' => self::FREQ_MONTHLY],
            [['status'], 'default', 'value' => self::STATUS_ACTIVE],
            [['weight'], 'default', 'value' => 0],
            [['sort_order'], 'default', 'value' => 0],
            [['cycle_id'], 'exist', 'targetClass' => KpiCycle::class, 'targetAttribute' => ['cycle_id' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'indicator' => 'ชื่อตัวชี้วัด',
            'target_text' => 'เป้าหมาย',
            'target_value' => 'เป้าเชิงตัวเลข',
            'unit' => 'หน่วย',
            'value_type' => 'ชนิดผลงาน',
            'frequency' => 'ความถี่บันทึก',
            'weight' => 'น้ำหนัก (%)',
            'status' => 'สถานะ',
        ];
    }

    public function getCycle()
    {
        return $this->hasOne(KpiCycle::class, ['id' => 'cycle_id']);
    }

    public function getEntries()
    {
        return $this->hasMany(KpiEntry::class, ['kpi_item_id' => 'id']);
    }

    public function getScores()
    {
        return $this->hasMany(KpiItemScore::class, ['kpi_item_id' => 'id']);
    }

    public function getSourceSection()
    {
        return $this->hasOne(JdEmployeeSection::class, ['id' => 'source_jd_section_id']);
    }

    /**
     * สรุปผลรายปีจากค่ารายเดือน ตามวิธี aggregation
     * @param array<int, \app\modules\kpi\models\KpiEntry> $entriesByIndex คีย์ = fiscal index 1–12
     * @return array{value: float|null, count: int, pct: float|null}
     *   value = ผลสรุป, count = จำนวนเดือนที่มีข้อมูล, pct = ร้อยละเทียบเป้า (ถ้ามี target_value)
     */
    public function summarize(array $entriesByIndex): array
    {
        $vals = [];
        $lastVal = null;
        for ($fi = 1; $fi <= 12; $fi++) {
            $e = $entriesByIndex[$fi] ?? null;
            if ($e && $e->value_num !== null) {
                $vals[] = (float) $e->value_num;
                $lastVal = (float) $e->value_num;
            }
        }
        if ($vals === []) {
            return ['value' => null, 'count' => 0, 'pct' => null, 'level' => null];
        }
        switch ($this->aggregation) {
            case self::AGG_SUM:  $value = array_sum($vals); break;
            case self::AGG_MIN:  $value = min($vals); break;
            case self::AGG_MAX:  $value = max($vals); break;
            case self::AGG_LAST: $value = $lastVal; break;
            case self::AGG_AVG:
            default:             $value = array_sum($vals) / count($vals); break;
        }
        $pct = ($this->target_value !== null && (float) $this->target_value != 0.0)
            ? ($value / (float) $this->target_value) * 100
            : null;
        return ['value' => $value, 'count' => count($vals), 'pct' => $pct, 'level' => $this->levelFor($value)];
    }

    /** เกณฑ์ 5 ระดับ [1=>level1 … 5=>level5] (เฉพาะที่กรอกไว้) */
    public function levelThresholds(): array
    {
        $out = [];
        foreach ([1, 2, 3, 4, 5] as $l) {
            $v = $this->{'level' . $l};
            if ($v !== null && $v !== '') {
                $out[$l] = (float) $v;
            }
        }
        return $out;
    }

    public function hasLevels(): bool
    {
        return $this->levelThresholds() !== [];
    }

    /**
     * แปลงผลงานเป็นระดับ (0–5) ตามเกณฑ์และทิศทาง
     * asc: ผลงาน >= เกณฑ์ระดับ n → ได้ระดับ n (ระดับสูงสุดที่ผ่าน)
     * desc: ผลงาน <= เกณฑ์ระดับ n → ได้ระดับ n
     * คืน null ถ้ายังไม่ตั้งเกณฑ์ หรือไม่มีผลงาน
     */
    public function levelFor(?float $value): ?int
    {
        if ($value === null) {
            return null;
        }
        $thresholds = $this->levelThresholds();
        if ($thresholds === []) {
            return null;
        }
        $best = 0;
        foreach ($thresholds as $level => $th) {
            $pass = $this->direction === self::DIR_DESC ? ($value <= $th) : ($value >= $th);
            if ($pass) {
                $best = $level;
            }
        }
        return $best;
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $now = date('Y-m-d H:i:s');
            $uid = (Yii::$app->has('user') && !Yii::$app->user->isGuest) ? (int) Yii::$app->user->id : null;
            if ($insert) {
                $this->created_at = $now;
                $this->created_by = $uid;
            }
            $this->updated_at = $now;
            $this->updated_by = $uid;
            return true;
        }
        return false;
    }
}
