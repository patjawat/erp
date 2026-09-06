<?php

namespace app\modules\helpdesk2\helpers;

use Yii;
use yii\helpers\Html;
use app\modules\helpdesk2\models\Helpdesk;
use app\modules\helpdesk2\models\HelpdeskSlaSetting;

class HelpdeskSlaHelper
{
    /**
     * Calculate SLA information for a ticket.
     *
     * @return array{
     *   deadline: string|null,
     *   secondsRemaining: int|null,
     *   status: string, // within|near|breached|no_sla
     *   priority: string|null
     * }
     */
    public static function calculate(Helpdesk $ticket): array
    {
        // ใช้ engine เดียวกับแดชบอร์ด: เกณฑ์เวลาตามกลุ่มงานซ่อม (slaResult ใช้ repair_group ในตัว)
        // + รู้เวลาปิดจริงจาก timeline (งานปิดทันเวลา = ภายใน SLA แม้เวลาผ่านไปนานแล้ว)
        $timeline = HelpdeskTimelineHelper::forTicket($ticket);
        $sla = self::slaResult($ticket, $timeline);

        $priority = ($ticket->data_json['urgency'] ?? null) !== null
            ? self::normalizeUrgencyValue($ticket->data_json['urgency'])
            : null;

        if (empty($sla['deadline'])) {
            return [
                'deadline' => null,
                'secondsRemaining' => null,
                'status' => 'no_sla',
                'priority' => $priority,
            ];
        }

        $deadlineTs = strtotime($sla['deadline']);
        $secondsRemaining = $deadlineTs !== false ? ($deadlineTs - time()) : null;

        // แปลงสถานะจาก engine (met/breached/pending) → คำศัพท์ป้าย (within/near/breached)
        if ($sla['status'] === 'breached') {
            $status = 'breached';
        } elseif ($sla['status'] === 'met') {
            $status = 'within'; // งานปิดทันเวลา
        } else {
            // งานเปิดที่ยังไม่เกิน — "ใกล้ครบกำหนด" เมื่อเหลือ ≤ 1 ชั่วโมง
            $status = ($secondsRemaining !== null && $secondsRemaining <= 3600) ? 'near' : 'within';
        }

        return [
            'deadline' => $sla['deadline'],
            'secondsRemaining' => $secondsRemaining,
            'status' => $status,
            'priority' => $priority,
        ];
    }

    /**
     * Return HTML badge for SLA status.
     */
    public static function renderBadge(Helpdesk $ticket): string
    {
        $info = self::calculate($ticket);

        if ($info['status'] === 'no_sla') {
            return '';
        }

        switch ($info['status']) {
            case 'within':
                $class = 'badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill fw-medium px-2 py-1';
                $label = 'ภายใน SLA';
                $icon = 'fa-regular fa-circle-check';
                break;
            case 'near':
                $class = 'badge bg-warning bg-opacity-10 text-warning border border-warning-subtle rounded-pill fw-medium px-2 py-1';
                $label = 'ใกล้ครบกำหนด SLA';
                $icon = 'fa-solid fa-triangle-exclamation';
                break;
            default:
                $class = 'badge bg-danger bg-opacity-10 text-danger border border-danger-subtle rounded-pill fw-medium px-2 py-1';
                $label = 'เกิน SLA';
                $icon = 'fa-solid fa-circle-exclamation';
                break;
        }

        return Html::tag(
            'span',
            '<i class="' . $icon . ' me-1"></i>' . $label,
            ['class' => $class]
        );
    }

    // ============================================================
    // SLA แบบ Service Catalog + Timeline (HAIT หมวด 4 ระดับ 2)
    // ============================================================

    /**
     * ระดับความเร่งด่วนแบบ normalize จากค่า urgency ดิบ (ค่าว่าง → medium)
     */
    public static function normalizeUrgencyValue($urgency): string
    {
        $key = strtolower(trim((string) $urgency));
        $map = ['1' => 'low', '2' => 'medium', '3' => 'high', '4' => 'critical'];
        if (isset($map[$key])) {
            return $map[$key];
        }
        if (in_array($key, ['low', 'medium', 'high', 'critical'], true)) {
            return $key;
        }
        return 'medium';
    }

    /**
     * หาว่างานตกอยู่ในรายการบริการใดของแคตตาล็อก (จับด้วย device_type_id)
     * งานที่จับไม่ได้ → รายการ 'other' (หรือรายการสุดท้ายเป็น fallback)
     *
     * @return array<string,mixed> catalog entry
     */
    public static function resolveServiceByType(?string $deviceType): array
    {
        $catalog = self::settingRecord()->getServiceCatalog();
        $deviceType = (string) ($deviceType ?? '');

        $fallback = null;
        foreach ($catalog as $entry) {
            if (($entry['code'] ?? '') === 'other') {
                $fallback = $entry;
            }
            $types = $entry['device_types'] ?? [];
            if (is_array($types) && $deviceType !== '' && in_array($deviceType, $types, true)) {
                return $entry;
            }
        }
        return $fallback ?? (end($catalog) ?: [
            'code' => 'other',
            'title' => 'อื่นๆ/ไม่ระบุประเภท',
            'response_min' => 30,
            'resolve_min' => 1440,
        ]);
    }

    /**
     * เวลาแก้ไขที่รับประกันแบบมีผล (นาที) = เวลาแก้ไขฐาน × ตัวคูณความเร่งด่วน
     *
     * เวลาแก้ไขฐาน: ถ้ากลุ่มงานซ่อมมีค่ากำหนดไว้ (แพทย์/ซ่อมบำรุง) ใช้ค่าของกลุ่ม
     * ถ้าไม่มี (คอมพิวเตอร์/ไม่ระบุกลุ่ม) ใช้ resolve_min ของรายการบริการตาม device_type เดิม
     */
    public static function effectiveResolveMinutesFor(?string $deviceType, $urgency, ?int $repairGroup = null): float
    {
        $record = self::settingRecord();
        $groupBase = $record->groupResolveMin($repairGroup);
        if ($groupBase !== null) {
            $base = $groupBase;
        } else {
            $service = self::resolveServiceByType($deviceType);
            $base = (float) ($service['resolve_min'] ?? 1440);
        }
        $mult = $record->getUrgencyMultiplier();
        $u = self::normalizeUrgencyValue($urgency);
        $factor = isset($mult[$u]) ? (float) $mult[$u] : 1.0;
        return max(1.0, $base * $factor);
    }

    /**
     * ประเมินผล SLA จากข้อมูลดิบ (array) เพื่อใช้แบบ batch โดยไม่ต้องสร้างโมเดล
     * (เลี่ยง afterFind ที่ lazy-load asset → N+1)
     *
     * @param array $t ต้องมีคีย์: created_at, status, device_type_id, data_json(array), updated_at, receive_date
     * @param array<string,?string> $timeline ผลจาก HelpdeskTimelineHelper
     * @return array{service_code:string,service_title:string,resolve_minutes:float,actual_minutes:?float,deadline:?string,status:string}
     */
    public static function slaResultFromData(array $t, array $timeline, ?int $repairGroup = null): array
    {
        $deviceType = $t['device_type_id'] ?? null;
        $urgency = $t['data_json']['urgency'] ?? null;
        $service = self::resolveServiceByType($deviceType);
        $resolveMinutes = self::effectiveResolveMinutesFor($deviceType, $urgency, $repairGroup);

        $reported = $timeline['reported_at'] ?? ($t['created_at'] ?? null);
        $resolved = $timeline['resolved_at'] ?? null;

        return self::buildSlaResult($service, $resolveMinutes, $reported, $resolved);
    }

    /**
     * ประเมินผล SLA ของงานหนึ่งใบ (โมเดล) โดยใช้ timeline ที่ derive มาแล้ว
     *
     * @param array<string,?string> $timeline ผลจาก HelpdeskTimelineHelper::withFallback()
     * @return array{service_code:string,service_title:string,resolve_minutes:float,actual_minutes:?float,deadline:?string,status:string}
     */
    public static function slaResult(Helpdesk $ticket, array $timeline, ?int $repairGroup = null): array
    {
        $service = self::resolveServiceByType($ticket->device_type_id);
        $resolveMinutes = self::effectiveResolveMinutesFor(
            $ticket->device_type_id,
            $ticket->data_json['urgency'] ?? null,
            $repairGroup ?? $ticket->repair_group
        );

        $reported = $timeline['reported_at'] ?? ($ticket->created_at ?: null);
        $resolved = $timeline['resolved_at'] ?? null;

        return self::buildSlaResult($service, $resolveMinutes, $reported, $resolved);
    }

    /**
     * แกนคำนวณผล SLA ร่วมของทั้งแบบ array และแบบโมเดล
     *
     * @param array<string,mixed> $service catalog entry
     */
    private static function buildSlaResult(array $service, float $resolveMinutes, ?string $reported, ?string $resolved): array
    {
        $base = [
            'service_code' => (string) ($service['code'] ?? 'other'),
            'service_title' => (string) ($service['title'] ?? 'อื่นๆ'),
            'resolve_minutes' => $resolveMinutes,
            'actual_minutes' => null,
            'deadline' => null,
            'status' => 'no_sla',
        ];

        if (empty($reported)) {
            return $base;
        }

        try {
            $reportedTs = (new \DateTimeImmutable($reported))->getTimestamp();
        } catch (\Throwable $e) {
            return $base;
        }
        $deadlineTs = $reportedTs + (int) round($resolveMinutes * 60);
        $base['deadline'] = date('Y-m-d H:i:s', $deadlineTs);

        // งานที่ปิดแล้ว → เทียบเวลาจริงกับ SLA
        if (!empty($resolved)) {
            try {
                $resolvedTs = (new \DateTimeImmutable($resolved))->getTimestamp();
                $actual = max(0, $resolvedTs - $reportedTs) / 60.0;
                $base['actual_minutes'] = $actual;
                $base['status'] = $resolvedTs <= $deadlineTs ? 'met' : 'breached';
                return $base;
            } catch (\Throwable $e) {
                // ตกไปพิจารณาแบบงานเปิด
            }
        }

        // งานที่ยังไม่ปิด → เกินกำหนดหรือยังอยู่ในเวลา
        $now = time();
        $base['status'] = $now > $deadlineTs ? 'breached' : 'pending';
        return $base;
    }

    /**
     * โหลด (และ cache) เรคคอร์ดตั้งค่า SLA
     */
    private static function settingRecord(): HelpdeskSlaSetting
    {
        static $record = null;
        if ($record === null) {
            $record = HelpdeskSlaSetting::getRecord();
        }
        return $record;
    }
}

