<?php

namespace app\modules\ha12\models;

use app\modules\hr\models\Organization;

/**
 * รอบสรุปและประเมินโดย PCT
 *
 * @property int $id
 * @property int $fiscal_year
 * @property string $period_type  month|quarter|year
 * @property int|null $period_no  เดือนปฏิทิน 1-12 | ไตรมาส 1-4 | year=null
 * @property string $period_start
 * @property string $period_end
 * @property int|null $scope_unit_id
 * @property string|null $title
 * @property string $status  open|closed
 * @property string|null $reopen_reason
 * @property string|null $note
 * @property string $ref
 */
class Ha12Round extends Ha12ActiveRecord
{
    public const STATUS_OPEN = 'open';
    public const STATUS_CLOSED = 'closed';

    public const TYPE_MONTH = 'month';
    public const TYPE_QUARTER = 'quarter';
    public const TYPE_YEAR = 'year';

    /** เดือนปฏิทิน (คู่มือ: M10 = ต.ค.) */
    public const MONTH_LABELS = [
        1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน', 5 => 'พฤษภาคม', 6 => 'มิถุนายน',
        7 => 'กรกฎาคม', 8 => 'สิงหาคม', 9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม',
    ];

    public const QUARTER_LABELS = [
        1 => 'ไตรมาส 1 (ต.ค.–ธ.ค.)', 2 => 'ไตรมาส 2 (ม.ค.–มี.ค.)',
        3 => 'ไตรมาส 3 (เม.ย.–มิ.ย.)', 4 => 'ไตรมาส 4 (ก.ค.–ก.ย.)',
    ];

    public static function tableName(): string
    {
        return '{{%ha12_round}}';
    }

    public function rules(): array
    {
        return [
            [['fiscal_year', 'period_type'], 'required'],
            [['fiscal_year', 'period_no', 'scope_unit_id'], 'integer'],
            [['period_type'], 'in', 'range' => [self::TYPE_MONTH, self::TYPE_QUARTER, self::TYPE_YEAR]],
            [['period_start', 'period_end'], 'date', 'format' => 'php:Y-m-d'],
            [['title'], 'string', 'max' => 255],
            [['reopen_reason', 'note'], 'string'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'fiscal_year' => 'ปีงบประมาณ',
            'period_type' => 'ประเภทรอบ',
            'period_no' => 'ช่วง',
            'scope_unit_id' => 'ขอบเขตหน่วยงาน',
            'title' => 'ชื่อรอบ',
        ];
    }

    /**
     * คำนวณช่วงวันที่ (ค.ศ. Y-m-d) จากปีงบ + ประเภท + ช่วง
     * ปีงบไทย: 1 ต.ค.(base-1) ถึง 30 ก.ย.(base) โดย base = fy - 543
     *
     * @return array{0:string,1:string} [start, end]
     */
    public static function computePeriod(int $fiscalYear, string $type, ?int $no): array
    {
        $base = $fiscalYear - 543;                 // ค.ศ. ของช่วง ม.ค.-ก.ย.
        $lastDay = static fn (int $y, int $m): string => date('Y-m-d', strtotime(sprintf('%04d-%02d-01', $y, $m) . ' +1 month -1 day'));

        if ($type === self::TYPE_MONTH && $no) {
            $year = $no >= 10 ? $base - 1 : $base;   // ต.ค.-ธ.ค. อยู่ปีก่อน
            return [sprintf('%04d-%02d-01', $year, $no), $lastDay($year, $no)];
        }
        if ($type === self::TYPE_QUARTER && $no) {
            $map = [
                1 => [$base - 1, 10, $base - 1, 12],
                2 => [$base, 1, $base, 3],
                3 => [$base, 4, $base, 6],
                4 => [$base, 7, $base, 9],
            ];
            [$sy, $sm, $ey, $em] = $map[$no] ?? $map[1];
            return [sprintf('%04d-%02d-01', $sy, $sm), $lastDay($ey, $em)];
        }
        // ทั้งปีงบ
        return [sprintf('%04d-10-01', $base - 1), sprintf('%04d-09-30', $base)];
    }

    /** ป้ายชื่อช่วง */
    public function periodLabel(): string
    {
        if ($this->period_type === self::TYPE_MONTH) {
            return (self::MONTH_LABELS[$this->period_no] ?? '') . ' ' . $this->fiscal_year;
        }
        if ($this->period_type === self::TYPE_QUARTER) {
            return (self::QUARTER_LABELS[$this->period_no] ?? '') . ' ปีงบ ' . $this->fiscal_year;
        }
        return 'ทั้งปีงบประมาณ ' . $this->fiscal_year;
    }

    public function displayTitle(): string
    {
        return $this->title ?: $this->periodLabel();
    }

    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }

    public function getScopeUnit()
    {
        return $this->hasOne(Organization::class, ['id' => 'scope_unit_id']);
    }

    public function getAssessments()
    {
        return $this->hasMany(Ha12Assessment::class, ['round_id' => 'id']);
    }
}
