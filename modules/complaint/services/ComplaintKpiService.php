<?php

namespace app\modules\complaint\services;

use app\modules\complaint\models\Complaint;
use app\modules\complaint\models\ComplaintIndicator;

/**
 * คำนวณตัวชี้วัด (CC01–CC07) + การกระจาย + แนวโน้มรายเดือน + SLA monitor
 * ของระบบรับเรื่องร้องเรียน สำหรับปีงบที่ระบุ
 *
 * โหลดเคสทั้งปี (ในขอบเขตสิทธิ์) มาครั้งเดียว แล้วคำนวณใน PHP — เคสต่อปีมีจำนวนไม่มาก
 * milestone (เริ่ม/ทบทวน/ตอบกลับ) จับจาก complaint_action.action_kind = start|review|reply
 */
class ComplaintKpiService
{
    /** ลำดับเดือนตามปีงบไทย (ต.ค.–ก.ย.) → [เลขเดือนปฏิทิน, ป้าย] */
    public const FISCAL_MONTHS = [
        [10, 'ต.ค.'], [11, 'พ.ย.'], [12, 'ธ.ค.'],
        [1, 'ม.ค.'], [2, 'ก.พ.'], [3, 'มี.ค.'],
        [4, 'เม.ย.'], [5, 'พ.ค.'], [6, 'มิ.ย.'],
        [7, 'ก.ค.'], [8, 'ส.ค.'], [9, 'ก.ย.'],
    ];

    /**
     * @param int      $fy       ปีงบ (พ.ศ.)
     * @param int[]|null $scopeIds  จำกัดขอบเขต assigned_unit_id (null = ทุกหน่วย = manager)
     * @param int|null $userId   สำหรับกรณีไม่ใช่ manager ให้เห็นเรื่องที่ตนสร้างด้วย
     */
    public static function metrics(int $fy, ?array $scopeIds, ?int $userId): array
    {
        $query = Complaint::find()
            ->with(['actions', 'surveys', 'type', 'channel', 'assignedUnit'])
            ->where(['fiscal_year' => $fy]);
        if ($scopeIds !== null) {
            $query->andWhere(['or', ['assigned_unit_id' => $scopeIds ?: [-1]], ['created_by' => $userId ?? -1]]);
        }
        /** @var Complaint[] $cases */
        $cases = $query->all();

        $statusCount = array_fill_keys(array_keys(Complaint::statusLabels()), 0);
        $levelCount = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
        $typeCount = [];
        $channelCount = [];
        $monthCount = array_fill(0, 12, 0);

        $level3plus = 0;
        $level3plusRca = 0;

        // ผลรวมวัน (นับเฉพาะเคสที่มีวันตั้งต้น + วัน milestone)
        $sumStart = 0; $nStart = 0;
        $sumReview = 0; $nReview = 0;
        $sumReply = 0; $nReply = 0;
        $sumClose = 0; $nClose = 0;

        $sumSat = 0.0; $nSat = 0;

        $slaOverdue = ['respond' => [], 'review' => [], 'reply' => [], 'close' => []];

        $today = date('Y-m-d');

        foreach ($cases as $c) {
            $statusCount[$c->status] = ($statusCount[$c->status] ?? 0) + 1;
            if ($c->severity_level) {
                $levelCount[(int) $c->severity_level] = ($levelCount[(int) $c->severity_level] ?? 0) + 1;
            }
            if ($c->type) {
                $typeCount[$c->type->name] = ($typeCount[$c->type->name] ?? 0) + 1;
            }
            if ($c->channel) {
                $channelCount[$c->channel->name] = ($channelCount[$c->channel->name] ?? 0) + 1;
            }
            // แนวโน้มรายเดือน (ตามปีงบ)
            if ($c->complaint_date) {
                $m = (int) date('n', strtotime($c->complaint_date));
                foreach (self::FISCAL_MONTHS as $i => [$mm]) {
                    if ($mm === $m) { $monthCount[$i]++; break; }
                }
            }

            $isL3 = $c->severity_level && (int) $c->severity_level >= 3;

            // milestone dates จาก actions
            $firstByKind = self::firstActionDates($c);
            if ($isL3) {
                $level3plus++;
                if (isset($firstByKind['review']) || $c->need_rca) {
                    // ทำ RCA = มี action review หรืออย่างน้อยตั้งธง need_rca และมีการดำเนินงาน
                    if (isset($firstByKind['review'])) {
                        $level3plusRca++;
                    }
                }
            }

            if ($c->complaint_date) {
                $base = strtotime($c->complaint_date);
                if (isset($firstByKind['start'])) {
                    $sumStart += self::days($base, $firstByKind['start']); $nStart++;
                }
                if (isset($firstByKind['review'])) {
                    $sumReview += self::days($base, $firstByKind['review']); $nReview++;
                }
                if (isset($firstByKind['reply'])) {
                    $sumReply += self::days($base, $firstByKind['reply']); $nReply++;
                }
                if ($c->close_date && $c->status === Complaint::STATUS_CLOSED) {
                    $sumClose += self::days($base, $c->close_date); $nClose++;
                }
            }

            foreach ($c->surveys as $s) {
                if ($s->avg_score !== null) { $sumSat += (float) $s->avg_score; $nSat++; }
            }

            // SLA overdue (เฉพาะเคสที่ยังไม่ปิด/ไม่ตกไป)
            if (!$c->isFinal()) {
                if ($c->respond_due && $c->respond_due < $today && !isset($firstByKind['start'])) {
                    $slaOverdue['respond'][] = $c;
                }
                if ($c->review_due && $c->review_due < $today && !isset($firstByKind['review']) && $c->need_rca) {
                    $slaOverdue['review'][] = $c;
                }
                if ($c->reply_due && $c->reply_due < $today && !isset($firstByKind['reply'])) {
                    $slaOverdue['reply'][] = $c;
                }
                if ($c->close_due && $c->close_due < $today) {
                    $slaOverdue['close'][] = $c;
                }
            }
        }

        $visits = ComplaintIndicator::visitsFor($fy);
        $avg = static fn (int|float $sum, int $n): ?float => $n ? round($sum / $n, 1) : null;

        return [
            'fiscalYear' => $fy,
            'total' => count($cases),
            'visits' => $visits,
            'statusCount' => $statusCount,
            'levelCount' => $levelCount,
            'typeCount' => $typeCount,
            'channelCount' => $channelCount,
            'monthCount' => $monthCount,
            'monthLabels' => array_map(static fn ($m) => $m[1], self::FISCAL_MONTHS),
            'kpi' => [
                'CC01' => [
                    'label' => 'ข้อร้องเรียนระดับ 3+ ต่อ 10,000 visit',
                    'value' => $visits ? round($level3plus / $visits * 10000, 2) : null,
                    'raw' => $level3plus . ' เรื่อง / ' . number_format($visits) . ' visit',
                    'unit' => 'ต่อหมื่น',
                ],
                'CC02' => [
                    'label' => 'ร้อยละการทำ RCA (เคสระดับ 3+)',
                    'value' => $level3plus ? round($level3plusRca / $level3plus * 100, 1) : null,
                    'raw' => $level3plusRca . '/' . $level3plus . ' เคส',
                    'unit' => '%',
                ],
                'CC03' => ['label' => 'เฉลี่ยวันเริ่มดำเนินการ', 'value' => $avg($sumStart, $nStart), 'raw' => "$nStart เคส", 'unit' => 'วัน'],
                'CC04' => ['label' => 'เฉลี่ยวันทบทวน/RCA', 'value' => $avg($sumReview, $nReview), 'raw' => "$nReview เคส", 'unit' => 'วัน'],
                'CC05' => ['label' => 'เฉลี่ยวันตอบกลับ', 'value' => $avg($sumReply, $nReply), 'raw' => "$nReply เคส", 'unit' => 'วัน'],
                'CC06' => ['label' => 'เฉลี่ยวันปิดเคส', 'value' => $avg($sumClose, $nClose), 'raw' => "$nClose เคส", 'unit' => 'วัน'],
                'CC07' => ['label' => 'ความพึงพอใจเฉลี่ย', 'value' => $avg($sumSat, $nSat), 'raw' => "$nSat แบบสอบถาม", 'unit' => '/5'],
            ],
            'slaOverdue' => $slaOverdue,
            'slaOverdueTotal' => array_sum(array_map('count', $slaOverdue)),
        ];
    }

    /** วันแรกของแต่ละ milestone kind (start/review/reply/...) จาก actions */
    private static function firstActionDates(Complaint $c): array
    {
        $out = [];
        foreach ($c->actions as $a) {
            if (!$a->action_kind || !$a->action_date) {
                continue;
            }
            if (!isset($out[$a->action_kind]) || $a->action_date < $out[$a->action_kind]) {
                $out[$a->action_kind] = $a->action_date;
            }
        }
        return $out;
    }

    private static function days(int $baseTs, string $target): int
    {
        return (int) round((strtotime($target) - $baseTs) / 86400);
    }
}
