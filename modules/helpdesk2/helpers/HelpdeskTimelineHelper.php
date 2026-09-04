<?php

namespace app\modules\helpdesk2\helpers;

use Yii;
use app\modules\helpdesk2\models\Helpdesk;

/**
 * สกัด "เส้นเวลา (timeline)" ของงานซ่อมจากบันทึกความเคลื่อนไหวใน `helpdesk_detail`
 * (name = 'service_record') เพื่อใช้คำนวณตัวชี้วัดตามมาตรฐาน HAIT หมวด 4
 * (Service Desk / SLA / Incident & Problem Management)
 *
 * หมุดเวลาที่สนใจ (อ้างอิงคู่มือ TMI Hospital IT Maturity Model บทที่ 4):
 *   - reported_at     : เวลารับแจ้ง (ใช้ helpdesk.created_at)
 *   - acknowledged_at : เวลารับเรื่อง (Service Desk รับงาน)  → ใช้คำนวณ MTTA
 *   - started_at      : เวลาเริ่มลงมือแก้ไข
 *   - resolved_at     : เวลาซ่อมเสร็จ                        → ใช้คำนวณ MTTR
 *   - delivered_at    : เวลาส่งมอบ/ปิดงาน
 *
 * ข้อมูล date_start / date_end ในตาราง helpdesk ปัจจุบันแทบว่างทั้งหมด (1/497 ใบ)
 * จึง derive จาก timeline แทน โดยไม่แตะฐานข้อมูล
 */
class HelpdeskTimelineHelper
{
    /** ชื่อ record ที่เก็บ timeline ในตาราง helpdesk_detail */
    public const DETAIL_NAME = 'service_record';

    /**
     * แผนที่ข้อความสถานะ (helpdesk_detail.status) → หมุดเวลา
     * ข้อความอ้างอิงจากค่าที่บันทึกจริงในระบบ + categorise(service_record_status)
     */
    private const MILESTONE_MAP = [
        'acknowledged' => ['รับเรื่อง'],
        'started'      => ['ส่งซ่อม', 'ดำเนินการซ่อม', 'ตรวจสอบปัญหาหน้างาน', 'ดำเนินการติดตั้ง', 'เช็คระยะ', 'ส่งซ่อมร้านค้าภายนอก'],
        'resolved'     => ['ซ่อมเสร็จ', 'ดำเนินการเสร็จสิ้น'],
        'delivered'    => ['ปิดงาน'],
    ];

    /**
     * สกัด timeline ของหลายงานพร้อมกันด้วย query เดียว (กัน N+1)
     *
     * @param int[] $ticketIds
     * @return array<int, array{
     *   reported_at:?string, acknowledged_at:?string, started_at:?string,
     *   resolved_at:?string, delivered_at:?string
     * }> map: helpdesk_id => หมุดเวลา
     */
    public static function forTicketIds(array $ticketIds): array
    {
        $ticketIds = array_values(array_unique(array_filter(array_map('intval', $ticketIds))));
        if (empty($ticketIds)) {
            return [];
        }

        $rows = (new \yii\db\Query())
            ->select(['helpdesk_id', 'status', 'created_at'])
            ->from('{{%helpdesk_detail}}')
            ->where(['name' => self::DETAIL_NAME])
            ->andWhere(['helpdesk_id' => $ticketIds])
            ->andWhere(['not', ['created_at' => null]])
            ->orderBy(['created_at' => SORT_ASC])
            ->all(Yii::$app->db);

        // จัดกลุ่มตาม helpdesk_id แล้วเลือกเวลาแรกสุดของแต่ละหมุด
        $result = [];
        foreach ($rows as $row) {
            $hid = (int) $row['helpdesk_id'];
            $milestone = self::classify((string) ($row['status'] ?? ''));
            if ($milestone === null) {
                continue;
            }
            $key = $milestone . '_at';
            // เรียง ASC อยู่แล้ว → เก็บเฉพาะครั้งแรกที่พบหมุดนั้น
            if (!isset($result[$hid][$key])) {
                $result[$hid][$key] = (string) $row['created_at'];
            }
        }

        return $result;
    }

    /**
     * สรุปสาเหตุรากเหง้า (root_cause) จากบันทึกปฏิบัติงานของงานที่ระบุ
     *
     * @param int[] $ticketIds
     * @param int $limit
     * @return array<int,array{cause:string,cnt:int}>
     */
    public static function rootCauseSummary(array $ticketIds, int $limit = 10): array
    {
        $ticketIds = array_values(array_unique(array_filter(array_map('intval', $ticketIds))));
        if (empty($ticketIds)) {
            return [];
        }
        $rows = (new \yii\db\Query())
            ->select([
                'cause' => new \yii\db\Expression("JSON_UNQUOTE(JSON_EXTRACT(data_json,'$.root_cause'))"),
                'cnt' => 'COUNT(*)',
            ])
            ->from('{{%helpdesk_detail}}')
            ->where(['name' => self::DETAIL_NAME])
            ->andWhere(['helpdesk_id' => $ticketIds])
            ->andWhere(new \yii\db\Expression("JSON_EXTRACT(data_json,'$.root_cause') IS NOT NULL"))
            ->groupBy(['cause'])
            ->orderBy(['cnt' => SORT_DESC])
            ->limit($limit)
            ->all(Yii::$app->db);

        $out = [];
        foreach ($rows as $r) {
            $cause = trim((string) $r['cause']);
            if ($cause === '' || strtolower($cause) === 'null') {
                continue;
            }
            $out[] = ['cause' => $cause, 'cnt' => (int) $r['cnt']];
        }
        return $out;
    }

    /**
     * สกัด timeline ของงานเดียว (รวมค่า fallback จากตัว ticket)
     *
     * @return array{
     *   reported_at:?string, acknowledged_at:?string, started_at:?string,
     *   resolved_at:?string, delivered_at:?string
     * }
     */
    public static function forTicket(Helpdesk $ticket): array
    {
        $map = self::forTicketIds([(int) $ticket->id]);
        $timeline = $map[(int) $ticket->id] ?? [];
        return self::withFallback($timeline, $ticket);
    }

    /**
     * เติมค่า fallback จากคอลัมน์หลักของ ticket ให้ครบทุกหมุด
     *
     * @param array<string,?string> $timeline หมุดที่ได้จาก forTicketIds()
     */
    public static function withFallback(array $timeline, Helpdesk $ticket): array
    {
        $reported = $ticket->created_at ?: null;

        $acknowledged = $timeline['acknowledged_at'] ?? null;
        if ($acknowledged === null && !empty($ticket->receive_date)) {
            $acknowledged = (string) $ticket->receive_date;
        }

        $started = $timeline['started_at'] ?? null;

        $resolved = $timeline['resolved_at'] ?? null;
        $delivered = $timeline['delivered_at'] ?? null;

        // ถ้างานปิด (success) แล้วแต่ไม่มีหมุดในบันทึก → ใช้ updated_at เป็นเวลาเสร็จ/ส่งมอบ
        $isClosed = Helpdesk::normalizeRepairStatus($ticket->status) === 'success';
        if ($isClosed) {
            if ($resolved === null) {
                $resolved = $ticket->updated_at ?: $delivered;
            }
            if ($delivered === null) {
                $delivered = $ticket->updated_at ?: $resolved;
            }
        }

        return [
            'reported_at'     => $reported,
            'acknowledged_at' => $acknowledged,
            'started_at'      => $started,
            'resolved_at'     => $resolved,
            'delivered_at'    => $delivered,
        ];
    }

    /**
     * เติมค่า fallback จากข้อมูลดิบ (array) — ใช้ตอนประมวลผลแบบ batch
     * โดยไม่ต้องสร้างโมเดล (เลี่ยง afterFind ที่ lazy-load asset → N+1)
     *
     * @param array<string,?string> $timeline หมุดที่ได้จาก forTicketIds()
     * @param array $t ต้องมีคีย์: created_at, receive_date, status, updated_at
     */
    public static function withFallbackArray(array $timeline, array $t): array
    {
        $reported = $t['created_at'] ?? null ?: null;

        $acknowledged = $timeline['acknowledged_at'] ?? null;
        if ($acknowledged === null && !empty($t['receive_date'])) {
            $acknowledged = (string) $t['receive_date'];
        }

        $started = $timeline['started_at'] ?? null;
        $resolved = $timeline['resolved_at'] ?? null;
        $delivered = $timeline['delivered_at'] ?? null;

        $isClosed = \app\modules\helpdesk2\models\Helpdesk::normalizeRepairStatus($t['status'] ?? null) === 'success';
        if ($isClosed) {
            $updated = $t['updated_at'] ?? null ?: null;
            // เวลาซ่อมเสร็จ: ยึดหมุด 'ปิดงาน' (delivered) ก่อน แล้วจึง updated_at
            // เพราะ updated_at เปลี่ยนได้ทุกครั้งที่แก้ไขเอกสาร ไม่ใช่เวลาปิดงานจริง
            if ($resolved === null) {
                $resolved = $delivered ?: $updated;
            }
            if ($delivered === null) {
                $delivered = $resolved ?: $updated;
            }
        }

        return [
            'reported_at'     => $reported,
            'acknowledged_at' => $acknowledged,
            'started_at'      => $started,
            'resolved_at'     => $resolved,
            'delivered_at'    => $delivered,
        ];
    }

    /**
     * เวลารับเรื่อง (Time To Acknowledge) เป็นวินาที — null ถ้าคำนวณไม่ได้
     */
    public static function secondsToAcknowledge(array $timeline): ?int
    {
        return self::diffSeconds($timeline['reported_at'] ?? null, $timeline['acknowledged_at'] ?? null);
    }

    /**
     * เวลาซ่อมเสร็จ (Time To Resolve) เป็นวินาที — null ถ้าคำนวณไม่ได้
     */
    public static function secondsToResolve(array $timeline): ?int
    {
        return self::diffSeconds($timeline['reported_at'] ?? null, $timeline['resolved_at'] ?? null);
    }

    /**
     * จัดข้อความสถานะเข้าเป็นหมุดเวลา — คืน null ถ้าไม่เข้าเกณฑ์
     */
    private static function classify(string $status): ?string
    {
        $status = trim($status);
        if ($status === '') {
            return null;
        }
        foreach (self::MILESTONE_MAP as $milestone => $labels) {
            foreach ($labels as $label) {
                if ($status === $label || mb_strpos($status, $label) !== false) {
                    return $milestone;
                }
            }
        }
        return null;
    }

    /**
     * ผลต่างเวลาเป็นวินาที (>= 0) — null ถ้าค่าใดว่างหรือ parse ไม่ได้/ติดลบ
     */
    private static function diffSeconds(?string $from, ?string $to): ?int
    {
        if (empty($from) || empty($to)) {
            return null;
        }
        try {
            $a = (new \DateTimeImmutable($from))->getTimestamp();
            $b = (new \DateTimeImmutable($to))->getTimestamp();
        } catch (\Throwable $e) {
            return null;
        }
        $diff = $b - $a;
        return $diff >= 0 ? $diff : null;
    }
}
