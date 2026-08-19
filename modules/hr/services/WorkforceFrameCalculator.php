<?php

namespace app\modules\hr\services;

use app\modules\hr\helpers\OrgRollupHelper;
use app\modules\hr\models\EmployeePosition;
use app\modules\hr\models\EmployeeType;
use app\modules\hr\models\WorkforceFrame;
use app\modules\hr\models\WorkforcePositionMap;
use app\modules\hr\models\WorkforceProfile;
use app\modules\hr\models\WorkforceStandardLine;
use app\modules\hr\models\WorkforceStandardRule;

/**
 * คำนวณกรอบอัตรากำลังจากเกณฑ์ สป.สธ. + ตัวขับเคลื่อนของโรงพยาบาล
 *
 * หลักสำคัญ: ทุกตัวเลขที่คืนออกไปต้องมี "ที่มา" ติดมาด้วยเสมอ (calc)
 * กรอบที่อธิบายกับ สสจ. ไม่ได้ ถือว่าใช้ไม่ได้
 *
 * สิ่งที่ตั้งใจไม่ทำ: ไม่เดาค่าที่ไม่มีในเกณฑ์ ถ้าขาดตัวขับเคลื่อนหรือเกณฑ์ยังไม่ยืนยัน
 * จะคืนสถานะบอกเหตุผล ไม่คืนเลข 0 เพราะ 0 แปลว่า "ไม่มีกรอบ" ซึ่งคนละเรื่องกับ "คำนวณไม่ได้"
 */
class WorkforceFrameCalculator
{
    /** คำนวณได้ครบ */
    public const STATUS_CALCULATED = 'calculated';
    /** เกณฑ์ให้คำนวณ FTE เอง ระบบคำนวณแทนไม่ได้ */
    public const STATUS_NEEDS_FTE = 'needs_fte';
    /** สูตรพร้อม แต่ยังไม่ได้กรอกตัวขับเคลื่อน */
    public const STATUS_MISSING_DRIVER = 'missing_driver';
    /** สูตรยังขาดพารามิเตอร์ที่เอกสารไม่ได้ระบุ */
    public const STATUS_NEEDS_PARAMETER = 'needs_parameter';
    /** คิดที่ระดับ CUP ต้องหักส่วนของลูกข่ายก่อน */
    public const STATUS_CUP_SPLIT = 'cup_split_pending';
    /** เกณฑ์ระบุว่าขนาดนี้ไม่มีกรอบสายงานนี้ */
    public const STATUS_NOT_ELIGIBLE = 'not_eligible';
    /** ยังไม่ได้ยืนยันจากเอกสารว่ามีกรอบหรือไม่ */
    public const STATUS_UNVERIFIED = 'unverified';
    /** ผู้ใช้กรอก FTE เอง ตามที่เกณฑ์กำหนดให้ รพ. คำนวณ */
    public const STATUS_MANUAL_FTE = 'manual_fte';
    /** กรอกทับค่าที่ระบบคำนวณได้ พร้อมเหตุผล */
    public const STATUS_OVERRIDE = 'override';

    public const STATUS_LABELS = [
        self::STATUS_CALCULATED => 'คำนวณจากเกณฑ์',
        self::STATUS_MANUAL_FTE => 'FTE ที่กรอก',
        self::STATUS_OVERRIDE => 'กรอกทับ',
        self::STATUS_NEEDS_FTE => 'ต้องกรอก FTE',
        self::STATUS_MISSING_DRIVER => 'ยังไม่ได้กรอกตัวเลขที่สูตรใช้',
        self::STATUS_NEEDS_PARAMETER => 'เกณฑ์ยังไม่ครบพารามิเตอร์',
        self::STATUS_CUP_SPLIT => 'รอแบ่งกรอบจากระดับ CUP',
        self::STATUS_NOT_ELIGIBLE => 'ขนาดนี้ไม่มีกรอบ',
        self::STATUS_UNVERIFIED => 'เกณฑ์ยังไม่ยืนยัน',
    ];

    /** สถานะที่ถือว่ามีกรอบใช้งานได้แล้ว */
    public const STATUS_RESOLVED = [
        self::STATUS_CALCULATED,
        self::STATUS_MANUAL_FTE,
        self::STATUS_OVERRIDE,
    ];

    private WorkforceProfile $profile;
    private array $rules;
    private array $positionToLine;
    private array $matrix;
    private array $countsInFrameTypes;
    private array $saved;

    private function __construct(WorkforceProfile $profile)
    {
        $this->profile = $profile;
        $this->rules = $profile->level_code
            ? WorkforceStandardRule::mapForLevel((string) $profile->level_code)
            : [];
        $this->positionToLine = WorkforcePositionMap::positionToLine();
        $this->matrix = OrgRollupHelper::headcountMatrix();
        $this->countsInFrameTypes = self::frameCountingTypeIds();
        $this->saved = WorkforceFrame::hospitalMap((int) $profile->thai_year);
    }

    public static function forYear(int $thaiYear): self
    {
        return new self(WorkforceProfile::forYear($thaiYear));
    }

    public function profile(): WorkforceProfile
    {
        return $this->profile;
    }

    /**
     * ประเภทการจ้างที่เกณฑ์ให้นับรวมในกรอบ — อ่านจากฐานข้อมูล ไม่ hardcode id
     *
     * @return int[]
     */
    public static function frameCountingTypeIds(): array
    {
        $query = EmployeeType::find()->select('id');

        if (EmployeeType::getTableSchema()->getColumn('counts_in_frame') !== null) {
            $query->where(['counts_in_frame' => 1]);
        }

        return array_map('intval', $query->column());
    }

    /**
     * ผลคำนวณรายสายงานมาตรฐาน — หนึ่งแถวต่อหนึ่งสายงานที่เกี่ยวข้องกับโรงพยาบาลนี้
     *
     * กรอบจากสูตรเป็นตัวเลขระดับโรงพยาบาล ไม่ใช่ระดับหน่วยงาน
     * การกระจายลงหน่วยเป็นการตัดสินใจของ รพ. ไม่ใช่ของเกณฑ์ จึงไม่คำนวณให้
     */
    public function results(): array
    {
        $headcount = $this->headcountByLine();
        $rows = [];

        foreach (WorkforceStandardLine::currentEdition() as $line) {
            $lineId = (int) $line->id;
            $rule = $this->rules[$lineId] ?? null;
            $actual = $headcount[$lineId] ?? ['in_frame' => 0, 'outsource' => 0, 'positions' => [], 'by_type' => []];

            $evaluation = $this->evaluate($line, $rule);

            // สายงานที่ไม่มีกรอบและไม่มีคน ไม่ต้องรกตาราง
            if ($evaluation['status'] === self::STATUS_NOT_ELIGIBLE
                && $actual['in_frame'] === 0 && $actual['outsource'] === 0) {
                continue;
            }

            $evaluation = $this->applySaved($lineId, $evaluation);

            $frame = $evaluation['frame'];
            $rows[] = [
                'line' => $line,
                'rule' => $rule,
                'saved' => $this->saved[$lineId] ?? null,
                'status' => $evaluation['status'],
                'frame' => $frame,
                'frame_min' => $rule?->min_qty !== null ? (float) $rule->min_qty : null,
                'frame_max' => $rule?->max_qty !== null ? (float) $rule->max_qty : null,
                'calc' => $evaluation['calc'],
                'in_frame' => $actual['in_frame'],
                'outsource' => $actual['outsource'],
                'positions' => $actual['positions'],
                'by_type' => $actual['by_type'],
                'gap' => $frame === null ? null : round($frame - $actual['in_frame'], 2),
            ];
        }

        return $rows;
    }

    /**
     * ค่าที่คนใส่ไว้ทับผลจากสูตร — FTE ที่กรอก และการกรอกทับพร้อมเหตุผล
     *
     * ค่าที่คำนวณได้เดิมยังเก็บไว้ในที่มา เพื่อให้เห็นว่ากรอกทับไปจากเท่าไร
     */
    private function applySaved(int $lineId, array $evaluation): array
    {
        $saved = $this->saved[$lineId] ?? null;

        if ($saved === null || !$saved->hasValue()) {
            return $evaluation;
        }

        $value = (float) $saved->frame_qty;

        if ($saved->source === WorkforceFrame::SOURCE_OVERRIDE) {
            $calc = $evaluation['calc'];
            $calc[] = ['ค่าเดิมจากเกณฑ์', $evaluation['frame'] === null ? 'คำนวณไม่ได้' : $this->fmt((float) $evaluation['frame'])];
            $calc[] = ['กรอกทับเป็น', $this->fmt($value)];
            $calc[] = ['เหตุผล', (string) $saved->override_reason];

            return $this->outcome($value, self::STATUS_OVERRIDE, $calc);
        }

        return $this->outcome($value, self::STATUS_MANUAL_FTE, [
            ['วิธีตามเกณฑ์', 'ให้โรงพยาบาลคำนวณ FTE เอง'],
            ['FTE ที่กรอก', $this->fmt($value)],
            ['หมายเหตุ', (string) ($saved->note ?: '—')],
        ]);
    }

    /** ตำแหน่งที่มีคนอยู่แต่ยังไม่ได้จับคู่กับเกณฑ์ — ต้องเห็น ไม่ใช่หายไปเงียบ ๆ */
    public function unmappedPositions(): array
    {
        $rows = [];
        foreach ($this->matrix as $item) {
            $positionId = (int) $item['position_id'];
            if (array_key_exists($positionId, $this->positionToLine)) {
                continue;
            }
            $rows[$positionId] = ($rows[$positionId] ?? 0) + (int) $item['count'];
        }

        if ($rows === []) {
            return [];
        }

        $titles = EmployeePosition::find()
            ->select(['id', 'title'])
            ->where(['id' => array_keys($rows)])
            ->indexBy('id')
            ->asArray()
            ->all();

        $result = [];
        foreach ($rows as $positionId => $count) {
            $result[] = [
                'position_id' => $positionId,
                'title' => $titles[$positionId]['title'] ?? ('#' . $positionId),
                'count' => $count,
            ];
        }

        usort($result, static fn ($a, $b) => $b['count'] <=> $a['count']);

        return $result;
    }

    /** ตำแหน่งที่ยืนยันแล้วว่าไม่มีสายงานตรงในเกณฑ์ */
    public function outOfScopePositions(): array
    {
        $counts = [];
        foreach ($this->matrix as $item) {
            $positionId = (int) $item['position_id'];
            if (!array_key_exists($positionId, $this->positionToLine) || $this->positionToLine[$positionId] !== null) {
                continue;
            }
            $counts[$positionId] = ($counts[$positionId] ?? 0) + (int) $item['count'];
        }

        if ($counts === []) {
            return [];
        }

        $titles = EmployeePosition::find()
            ->select(['id', 'title'])
            ->where(['id' => array_keys($counts)])
            ->indexBy('id')
            ->asArray()
            ->all();

        $result = [];
        foreach ($counts as $positionId => $count) {
            $result[] = ['title' => $titles[$positionId]['title'] ?? ('#' . $positionId), 'count' => $count];
        }

        usort($result, static fn ($a, $b) => $b['count'] <=> $a['count']);

        return $result;
    }

    /** รวมคนจริงเข้าเป็นรายสายงาน แยกถัง Back Office กับ Outsource ตามเกณฑ์ */
    private function headcountByLine(): array
    {
        $byLine = [];

        foreach ($this->matrix as $item) {
            $positionId = (int) $item['position_id'];
            $lineId = $this->positionToLine[$positionId] ?? null;

            if ($lineId === null) {
                continue; // ยังไม่จับคู่ หรือยืนยันว่าไม่มีในเกณฑ์
            }

            if (!isset($byLine[$lineId])) {
                $byLine[$lineId] = ['in_frame' => 0, 'outsource' => 0, 'positions' => [], 'by_type' => []];
            }

            $typeId = (int) $item['type_id'];
            $bucket = in_array($typeId, $this->countsInFrameTypes, true) ? 'in_frame' : 'outsource';
            $byLine[$lineId][$bucket] += (int) $item['count'];
            $byLine[$lineId]['positions'][$positionId] = ($byLine[$lineId]['positions'][$positionId] ?? 0) + (int) $item['count'];
            $byLine[$lineId]['by_type'][$typeId] = ($byLine[$lineId]['by_type'][$typeId] ?? 0) + (int) $item['count'];
        }

        return $byLine;
    }

    /**
     * ประเมินกรอบของสายงานหนึ่ง คืนทั้งค่าและที่มา
     *
     * @return array{frame:float|null,status:string,calc:array}
     */
    private function evaluate(WorkforceStandardLine $line, ?WorkforceStandardRule $rule): array
    {
        if ($this->profile->level_code === null || $this->profile->level_code === '') {
            return $this->outcome(null, self::STATUS_UNVERIFIED, [
                ['ยังไม่ได้ตั้งระดับโรงพยาบาล', '—'],
            ]);
        }

        if ($rule === null || $rule->eligible === null) {
            return $this->outcome(null, self::STATUS_UNVERIFIED, [
                ['เกณฑ์ระดับ ' . $this->profile->level_code, 'ยังไม่ยืนยัน'],
            ]);
        }

        if ((int) $rule->eligible === 0) {
            return $this->outcome(0.0, self::STATUS_NOT_ELIGIBLE, [
                ['เกณฑ์ระดับ ' . $this->profile->level_code, 'ไม่มีกรอบสายงานนี้'],
            ]);
        }

        $formula = $this->decodeFormula($line);

        if ($line->method === 'population_based') {
            return $this->evaluatePopulation($line, $formula);
        }

        if ($line->method === 'ratio') {
            return $this->evaluateRatio($line, $formula);
        }

        return $this->outcome(null, self::STATUS_NEEDS_FTE, [
            ['วิธีตามเกณฑ์', $line->methodLabel()],
        ]);
    }

    private function evaluatePopulation(WorkforceStandardLine $line, array $formula): array
    {
        $per = (float) ($formula['per'] ?? 0);
        $population = $this->profile->catchment_population;

        if ($population === null || $per <= 0) {
            return $this->outcome(null, self::STATUS_MISSING_DRIVER, [
                ['ต้องใช้', 'ประชากรที่รับผิดชอบ'],
            ]);
        }

        $cupTotal = $population / $per;

        // เกณฑ์คิดที่ระดับ CUP แต่กรอบที่ต้องการเป็นของโรงพยาบาล
        // ต้องหักส่วนของ รพ.สต. ออกก่อน ซึ่งยังไม่ได้ seed เกณฑ์ รพ.สต. จึงยังหักไม่ได้
        if (($formula['scope'] ?? '') === 'cup') {
            return $this->outcome(null, self::STATUS_CUP_SPLIT, [
                ['ประชากรที่รับผิดชอบ (CUP)', number_format($population)],
                ['อัตราส่วนตามเกณฑ์', '1 : ' . number_format($per)],
                ['กรอบระดับ CUP', $this->fmt($cupTotal) . ' คน'],
                ['หักกรอบ รพ.สต. ตามเกณฑ์ รพ.สต.', 'ยังไม่ได้ seed เกณฑ์ รพ.สต.'],
            ]);
        }

        return $this->outcome($this->roundUp($cupTotal), self::STATUS_CALCULATED, [
            ['ประชากรที่รับผิดชอบ', number_format($population)],
            ['อัตราส่วนตามเกณฑ์', '1 : ' . number_format($per)],
            ['คำนวณ', $this->fmt($cupTotal) . ' → ปัดขึ้นเป็น ' . $this->roundUp($cupTotal)],
        ]);
    }

    private function evaluateRatio(WorkforceStandardLine $line, array $formula): array
    {
        $calc = [];
        $total = 0.0;

        $terms = $formula['terms'] ?? [$formula];

        foreach ($terms as $term) {
            $driverKey = $term['driver'] ?? null;
            if ($driverKey === null) {
                continue;
            }

            $value = $this->profile->$driverKey ?? null;
            $label = WorkforceProfile::DRIVERS[$driverKey][0] ?? $driverKey;

            if ($value === null || $value === '') {
                return $this->outcome(null, self::STATUS_MISSING_DRIVER, [
                    ['ต้องใช้', $label],
                ]);
            }

            $value = (float) $value;

            if (isset($term['multiply'])) {
                // สูตรแบบเพดาน เช่น ขับรถ 70% ของจำนวนรถ — เกณฑ์ระบุว่า "ไม่เกิน"
                if (array_key_exists('shifts_per_day', $term) && $term['shifts_per_day'] === null) {
                    return $this->outcome(null, self::STATUS_NEEDS_PARAMETER, [
                        [$label, $this->fmt($value)],
                        ['เกณฑ์ระบุ', '1 จุด : 1 คน : 1 เวร 8 ชม.'],
                        ['ยังขาด', 'จำนวนเวรต่อวันของโรงพยาบาล'],
                    ]);
                }

                $multiplier = (float) $term['multiply'];
                // ตัวคูณต่ำกว่า 1 ในเกณฑ์เขียนเป็นเปอร์เซ็นต์ (ขับรถ 70%) ที่เหลือเป็นจำนวนเท่า (1 หอ : 4 คน)
                $multiplierLabel = $multiplier < 1
                    ? $this->fmt($multiplier * 100) . '%'
                    : $this->fmt($multiplier);

                $part = $value * $multiplier;
                $calc[] = [$label, $this->fmt($value)];
                $calc[] = ['คำนวณ ' . $this->fmt($value) . ' × ' . $multiplierLabel, $this->fmt($part)];
                $total += $part;
                continue;
            }

            $per = (float) ($term['per'] ?? 0);
            if ($per <= 0) {
                return $this->outcome(null, self::STATUS_NEEDS_PARAMETER, [
                    ['สูตรในเกณฑ์', 'ไม่มีตัวหาร'],
                ]);
            }

            $part = $value / $per;
            $calc[] = [$label, $this->fmt($value)];
            $calc[] = ['คำนวณ ' . $this->fmt($value) . ' ÷ ' . $this->fmt($per), $this->fmt($part)];
            $total += $part;
        }

        // เกณฑ์ขั้นต่ำที่ระบุเป็นตัวเลขตรง ๆ ทับผลจากสูตร
        foreach ($formula['floor'] ?? [] as $floor) {
            $driverKey = $formula['driver'] ?? null;
            $driverValue = $driverKey !== null ? $this->profile->$driverKey : null;
            if ($driverValue === null) {
                continue;
            }
            $driverValue = (float) $driverValue;

            $min = $floor['min'] ?? null;
            $max = $floor['max'] ?? null;
            $inRange = ($min === null || $driverValue >= $min) && ($max === null || $driverValue <= $max);

            if (!$inRange) {
                continue;
            }

            $floorQty = (float) $floor['min_qty'];
            if ($floorQty > $total) {
                $range = $min === null ? ('น้อยกว่า ' . ($max + 1)) : ($min . '–' . $max);
                $calc[] = ['เกณฑ์ขั้นต่ำเมื่อ ' . (WorkforceProfile::DRIVERS[$driverKey][0] ?? $driverKey) . ' ' . $range, $this->fmt($floorQty) . ' คน'];
                $total = $floorQty;
            }
            break;
        }

        $rounded = $this->roundUp($total);
        if (abs($rounded - $total) > 0.001) {
            $calc[] = ['ปัดขึ้นเป็นจำนวนคน', (string) $rounded];
        }

        return $this->outcome($rounded, self::STATUS_CALCULATED, $calc);
    }

    private function decodeFormula(WorkforceStandardLine $line): array
    {
        $formula = $line->formula_json;

        if (is_string($formula)) {
            $formula = json_decode($formula, true);
        }

        return is_array($formula) ? $formula : [];
    }

    /** กรอบเป็นจำนวนคน ปัดขึ้นเสมอ — 1.2 คน หมายถึงต้องมี 2 คนถึงจะครอบคลุมงาน */
    private function roundUp(float $value): float
    {
        return (float) ceil(round($value, 4));
    }

    private function fmt(float $value): string
    {
        return rtrim(rtrim(number_format($value, 2), '0'), '.');
    }

    private function outcome(?float $frame, string $status, array $calc): array
    {
        return ['frame' => $frame, 'status' => $status, 'calc' => $calc];
    }
}
