<?php

namespace app\modules\km\services;

use app\modules\iacRisk\models\RiskRegister;
use app\modules\kpi\models\KpiItem;
use app\modules\km\models\KmActivityLink;
use app\modules\task\models\Task;

/**
 * ค้นหา + resolve ป้ายชื่อ ของรายการปลายทางที่กิจกรรมจะผูกเป็นหลักฐาน
 *
 * เฟส 3 เปิด 3 ประเภท: task / kpi / risk (ของทีมเราเอง)
 * ประเภท dms_doc / medsop สงวนไว้ ต้องประสานทีม Codex ก่อนจึงเปิด
 *
 * map ประเภท → โมเดล/คอลัมน์ป้ายชื่อ รวมไว้ที่เดียว เพื่อไม่ให้ controller รู้จักโมเดลปลายทางตรงๆ
 */
class KmLinkService
{
    /** ประเภทที่เปิดให้ผูกได้ตอนนี้ (ตามลำดับที่จะแสดงใน dropdown) */
    public static function enabledTypes(): array
    {
        return [
            KmActivityLink::TYPE_TASK,
            KmActivityLink::TYPE_KPI,
            KmActivityLink::TYPE_RISK,
        ];
    }

    public static function isEnabled(string $type): bool
    {
        return in_array($type, self::enabledTypes(), true);
    }

    /**
     * ค้นหารายการปลายทางตามคำค้น
     * @return array<int, array{id:string,label:string}>
     */
    public static function search(string $type, string $q, int $limit = 40): array
    {
        $q = trim($q);
        switch ($type) {
            case KmActivityLink::TYPE_TASK:
                $rows = Task::find()->select(['id', 'label' => 'title']);
                if ($q !== '') {
                    $rows->where(['like', 'title', $q]);
                }
                $rows = $rows->orderBy(['id' => SORT_DESC])->limit($limit)->asArray()->all();
                break;

            case KmActivityLink::TYPE_KPI:
                $rows = KpiItem::find()->select(['id', 'label' => 'indicator']);
                if ($q !== '') {
                    $rows->where(['like', 'indicator', $q]);
                }
                $rows = $rows->orderBy(['id' => SORT_DESC])->limit($limit)->asArray()->all();
                break;

            case KmActivityLink::TYPE_RISK:
                $rows = RiskRegister::find()->select(['id', 'label' => 'risk_name']);
                if ($q !== '') {
                    $rows->where(['like', 'risk_name', $q]);
                }
                $rows = $rows->orderBy(['id' => SORT_DESC])->limit($limit)->asArray()->all();
                break;

            default:
                return [];
        }

        return array_map(
            static fn ($r): array => ['id' => (string) $r['id'], 'label' => (string) ($r['label'] ?? ('#' . $r['id']))],
            $rows
        );
    }

    /** ป้ายชื่อของรายการปลายทาง (null = ไม่พบ) — เรียกฝั่ง server เสมอ ไม่เชื่อค่าจาก client */
    public static function resolveLabel(string $type, string $refId): ?string
    {
        $id = (int) $refId;
        if ($id <= 0) {
            return null;
        }
        switch ($type) {
            case KmActivityLink::TYPE_TASK:
                $m = Task::find()->select('title')->where(['id' => $id])->scalar();
                return $m === false ? null : (string) $m;
            case KmActivityLink::TYPE_KPI:
                $m = KpiItem::find()->select('indicator')->where(['id' => $id])->scalar();
                return $m === false ? null : (string) $m;
            case KmActivityLink::TYPE_RISK:
                $m = RiskRegister::find()->select('risk_name')->where(['id' => $id])->scalar();
                return $m === false ? null : (string) $m;
            default:
                return null;
        }
    }

    /** route ไปดูรายการต้นทาง (null = ไม่มีหน้า view ที่ลิงก์ได้) */
    public static function outUrl(string $type, string $refId): ?array
    {
        $id = (int) $refId;
        if ($id <= 0) {
            return null;
        }
        return match ($type) {
            KmActivityLink::TYPE_TASK => ['/task/default/view', 'id' => $id],
            default => null, // kpi/risk ยังไม่มีหน้า view เดี่ยวที่ลิงก์ตรงได้
        };
    }

    /** ไอคอนประจำประเภท (Bootstrap Icons) */
    public static function icon(string $type): string
    {
        return match ($type) {
            KmActivityLink::TYPE_TASK => 'bi-check2-square',
            KmActivityLink::TYPE_KPI => 'bi-graph-up',
            KmActivityLink::TYPE_RISK => 'bi-exclamation-triangle',
            KmActivityLink::TYPE_DMS => 'bi-file-earmark-text',
            KmActivityLink::TYPE_MEDSOP => 'bi-journal-text',
            default => 'bi-link-45deg',
        };
    }
}
