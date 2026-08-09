<?php

namespace app\modules\hr\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * รอบประเมินผลการปฏิบัติราชการ — 1 ปีงบประมาณมี 2 รอบ
 * รอบที่ 1 = ต.ค.–มี.ค. · รอบที่ 2 = เม.ย.–ก.ย.
 *
 * @property int $id
 * @property int $fiscal_year
 * @property int $round_no
 * @property string|null $start_date
 * @property string|null $end_date
 * @property string|null $due_date
 * @property string $status
 * @property float $weight_kpi
 * @property float $weight_core
 * @property float $weight_functional
 */
class AppraisalRound extends ActiveRecord
{
    public const STATUS_DRAFT = 'draft';   // HR กำลังเตรียม กำหนดผู้ประเมิน/ระดับ
    public const STATUS_OPEN = 'open';     // เปิดให้ผู้ประเมินเริ่มให้คะแนน
    public const STATUS_CLOSED = 'closed'; // ปิดรอบ ล็อกไม่ให้แก้

    public static function tableName()
    {
        return '{{%hr_appraisal_round}}';
    }

    public function rules()
    {
        return [
            [['fiscal_year', 'round_no'], 'required'],
            [['fiscal_year', 'round_no', 'created_by', 'updated_by'], 'integer'],
            [['round_no'], 'in', 'range' => [1, 2]],
            [['weight_kpi', 'weight_core', 'weight_functional'], 'number', 'min' => 0, 'max' => 100],
            [['note'], 'string'],
            [['start_date', 'end_date', 'due_date', 'opened_at', 'closed_at', 'created_at', 'updated_at'], 'safe'],
            [['status'], 'in', 'range' => [self::STATUS_DRAFT, self::STATUS_OPEN, self::STATUS_CLOSED]],
            [['status'], 'default', 'value' => self::STATUS_DRAFT],
            [['end_date'], 'compare', 'compareAttribute' => 'start_date', 'operator' => '>=',
                'message' => 'วันสิ้นสุดต้องไม่ก่อนวันเริ่มรอบ', 'skipOnEmpty' => true],
            [['round_no'], 'unique', 'targetAttribute' => ['fiscal_year', 'round_no'],
                'message' => 'ปีงบประมาณนี้มีรอบดังกล่าวอยู่แล้ว'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'fiscal_year' => 'ปีงบประมาณ',
            'round_no' => 'รอบที่',
            'start_date' => 'เริ่มรอบ',
            'end_date' => 'สิ้นสุดรอบ',
            'due_date' => 'กำหนดส่งผลประเมิน',
            'status' => 'สถานะ',
            'weight_kpi' => 'น้ำหนักผลสัมฤทธิ์ (%)',
            'weight_core' => 'น้ำหนัก Core (%)',
            'weight_functional' => 'น้ำหนัก Functional (%)',
            'note' => 'หมายเหตุ',
        ];
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_DRAFT => 'เตรียมการ',
            self::STATUS_OPEN => 'เปิดให้ประเมิน',
            self::STATUS_CLOSED => 'ปิดรอบแล้ว',
        ];
    }

    public function getStatusLabel(): string
    {
        return self::statusLabels()[$this->status] ?? (string) $this->status;
    }

    public static function roundLabels(): array
    {
        return [
            1 => 'รอบที่ 1 (ต.ค. – มี.ค.)',
            2 => 'รอบที่ 2 (เม.ย. – ก.ย.)',
        ];
    }

    public function getRoundLabel(): string
    {
        return self::roundLabels()[(int) $this->round_no] ?? ('รอบที่ ' . $this->round_no);
    }

    public function getTitle(): string
    {
        return $this->getRoundLabel() . ' ปีงบประมาณ ' . $this->fiscal_year;
    }

    /** ช่วงวันที่ตามปฏิทินงบประมาณ — ใช้เป็นค่าตั้งต้นตอนสร้างรอบใหม่ */
    public static function defaultDates(int $fiscalYear, int $roundNo): array
    {
        $ce = $fiscalYear - 543;
        return $roundNo === 1
            ? ['start' => ($ce - 1) . '-10-01', 'end' => $ce . '-03-31', 'due' => $ce . '-04-30']
            : ['start' => $ce . '-04-01', 'end' => $ce . '-09-30', 'due' => $ce . '-10-31'];
    }

    /** รอบของปีงบประมาณหนึ่ง เรียงรอบ 1 → 2 */
    public static function forYear(int $fiscalYear): array
    {
        return self::find()
            ->where(['fiscal_year' => $fiscalYear])
            ->orderBy(['round_no' => SORT_ASC])
            ->all();
    }

    /**
     * รอบที่ควรเปิดอยู่ตอนนี้ — เอารอบที่เปิดอยู่ก่อน ถ้าไม่มีก็เอารอบตามวันที่ปัจจุบัน
     */
    public static function currentFor(int $fiscalYear): ?self
    {
        $rounds = self::forYear($fiscalYear);
        if ($rounds === []) {
            return null;
        }
        foreach ($rounds as $round) {
            if ($round->status === self::STATUS_OPEN) {
                return $round;
            }
        }
        $today = date('Y-m-d');
        foreach ($rounds as $round) {
            if ($round->start_date && $round->end_date
                && $today >= $round->start_date && $today <= $round->end_date) {
                return $round;
            }
        }
        return end($rounds) ?: null;
    }

    public function isEditable(): bool
    {
        return $this->status !== self::STATUS_CLOSED;
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $now = date('Y-m-d H:i:s');
        $uid = (Yii::$app->has('user') && !Yii::$app->user->isGuest) ? (int) Yii::$app->user->id : null;
        if ($insert) {
            $this->created_at = $now;
            $this->created_by = $uid;
        }
        $this->updated_at = $now;
        $this->updated_by = $uid;

        if ($this->status === self::STATUS_OPEN && !$this->opened_at) {
            $this->opened_at = $now;
        }
        if ($this->status === self::STATUS_CLOSED && !$this->closed_at) {
            $this->closed_at = $now;
        }
        return true;
    }
}
