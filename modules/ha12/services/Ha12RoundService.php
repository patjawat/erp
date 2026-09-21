<?php

namespace app\modules\ha12\services;

use app\components\AppHelper;
use app\modules\ha12\models\Ha12Activity;
use app\modules\ha12\models\Ha12Assessment;
use app\modules\ha12\models\Ha12Indicator;
use app\modules\ha12\models\Ha12MedReport;
use app\modules\ha12\models\Ha12MrecAudit;
use app\modules\ha12\models\Ha12Review;
use app\modules\ha12\models\Ha12Round;
use app\modules\ha12\models\Ha12SummarySource;
use app\modules\hr\models\Organization;

/**
 * ตรรกะกลางของรอบสรุป/ประเมิน PCT (เฟส 3)
 *
 * สิทธิ์: สร้าง/ประเมิน/ปิดรอบ = ผู้ดูแล/ทีมคุณภาพ (admin หรือ role ha12)
 *         ผู้ใช้ทั่วไป = อ่านผลที่เผยแพร่แล้ว
 */
class Ha12RoundService
{
    public static function isManager(): bool
    {
        return Ha12ReviewService::isManager();
    }

    /** หา assessment ของกิจกรรมในรอบ (สร้างใหม่ถ้ายังไม่มี) */
    public static function getOrCreateAssessment(Ha12Round $round, int $activityId): Ha12Assessment
    {
        $a = Ha12Assessment::find()->where(['round_id' => $round->id, 'activity_id' => $activityId])->one();
        if (!$a) {
            $a = new Ha12Assessment(['round_id' => (int) $round->id, 'activity_id' => $activityId, 'status' => Ha12Assessment::STATUS_DRAFT]);
            $a->save();
        }
        return $a;
    }

    /** จำนวนกิจกรรมทั้งหมด (12) และที่เผยแพร่แล้วในรอบ */
    public static function progress(Ha12Round $round): array
    {
        $total = (int) Ha12Activity::find()->where(['is_active' => 1])->count();
        $published = (int) Ha12Assessment::find()
            ->where(['round_id' => $round->id, 'status' => Ha12Assessment::STATUS_PUBLISHED])->count();
        return ['total' => $total, 'published' => $published];
    }

    /** ปิดรอบได้เมื่อทุกกิจกรรมเผยแพร่แล้ว */
    public static function canClose(Ha12Round $round): bool
    {
        $p = self::progress($round);
        return $p['total'] > 0 && $p['published'] >= $p['total'];
    }

    /** tree.id ในขอบเขตของรอบ (null = ทุกหน่วย) */
    private static function scopeIds(Ha12Round $round): ?array
    {
        if (!$round->scope_unit_id) {
            return null;
        }
        return Ha12ReviewService::unitScopeIds((int) $round->scope_unit_id) ?: [-1];
    }

    /**
     * รายการหลักฐานที่เลือกได้สำหรับกิจกรรมในรอบ (ตามชนิดฟอร์ม + ช่วงเวลา + ขอบเขต)
     * @return array<int, array{type:string,id:int,rev:?int,label:string}>
     */
    public static function availableSources(Ha12Round $round, Ha12Activity $activity): array
    {
        $scope = self::scopeIds($round);
        $out = [];

        if ($activity->form_type === Ha12Activity::FORM_MED) {
            $q = Ha12MedReport::find()->with('ownerUnit')->where(['deleted' => 0])
                ->andWhere(['<=', 'period_start', $round->period_end])
                ->andWhere(['>=', 'period_end', $round->period_start]);
            if ($scope !== null) {
                $q->andWhere(['owner_unit_id' => $scope]);
            }
            foreach ($q->all() as $r) {
                $out[] = ['type' => 'med', 'id' => (int) $r->id, 'rev' => null,
                    'label' => ($r->ownerUnit->name ?? '—') . ' · ' . AppHelper::convertToThai($r->period_start) . '–' . AppHelper::convertToThai($r->period_end) . ' · รวม ' . number_format($r->grandTotal())];
            }
        } elseif ($activity->form_type === Ha12Activity::FORM_MREC) {
            $q = Ha12MrecAudit::find()->with('ownerUnit')->where(['deleted' => 0, 'fiscal_year' => $round->fiscal_year]);
            if ($scope !== null) {
                $q->andWhere(['owner_unit_id' => $scope]);
            }
            foreach ($q->all() as $r) {
                $pct = $r->overallPercent();
                $out[] = ['type' => 'mrec', 'id' => (int) $r->id, 'rev' => null,
                    'label' => ($r->ownerUnit->name ?? '—') . ' · ตรวจ ' . ($r->total_charts ?? '—') . ' · ' . ($pct !== null ? number_format($pct, 1) . '%' : '—')];
            }
        } elseif ($activity->form_type === Ha12Activity::FORM_KPI) {
            $q = Ha12Indicator::find()->with('ownerUnit')->where(['deleted' => 0, 'fiscal_year' => $round->fiscal_year]);
            if ($scope !== null) {
                $q->andWhere(['owner_unit_id' => $scope]);
            }
            foreach ($q->all() as $r) {
                $out[] = ['type' => 'indicator', 'id' => (int) $r->id, 'rev' => null,
                    'label' => ($r->ownerUnit->name ?? '—') . ' · ' . $r->name];
            }
        } else { // general → review
            $q = Ha12Review::find()->with('ownerUnit')->where(['activity_id' => $activity->id, 'deleted' => 0])
                ->andWhere(['between', 'review_date', $round->period_start, $round->period_end]);
            if ($scope !== null) {
                $q->andWhere(['owner_unit_id' => $scope]);
            }
            foreach ($q->all() as $r) {
                $out[] = ['type' => 'review', 'id' => (int) $r->id, 'rev' => (int) $r->revision,
                    'label' => ($r->ownerUnit->name ?? '—') . ' · ' . ($r->title ?: '(ไม่มีหัวข้อ)') . ' · ' . ($r->review_date ? AppHelper::convertToThai($r->review_date) : '')];
            }
        }
        return $out;
    }

    /**
     * สร้าง snapshot ของหลักฐาน ณ ตอนเลือก (คืน [label, rev, snapshot array] หรือ null ถ้าไม่พบ/ไม่เข้าเกณฑ์)
     * @return array{label:string,rev:?int,snapshot:array}|null
     */
    public static function buildSnapshot(string $type, int $id): ?array
    {
        switch ($type) {
            case 'review':
                $r = Ha12Review::find()->with('ownerUnit')->where(['id' => $id, 'deleted' => 0])->one();
                if (!$r) {
                    return null;
                }
                return ['label' => ($r->ownerUnit->name ?? '—') . ' · ' . ($r->title ?: '(ไม่มีหัวข้อ)'),
                    'rev' => (int) $r->revision,
                    'snapshot' => ['unit' => $r->ownerUnit->name ?? null, 'title' => $r->title, 'review_date' => $r->review_date, 'fields' => $r->fields, 'reviewer' => $r->reviewer_name]];
            case 'med':
                $r = Ha12MedReport::find()->with('ownerUnit')->where(['id' => $id, 'deleted' => 0])->one();
                if (!$r) {
                    return null;
                }
                return ['label' => ($r->ownerUnit->name ?? '—') . ' · รายงานยา', 'rev' => null,
                    'snapshot' => ['unit' => $r->ownerUnit->name ?? null, 'period' => [$r->period_start, $r->period_end], 'grand_total' => $r->grandTotal()]];
            case 'mrec':
                $r = Ha12MrecAudit::find()->with('ownerUnit')->where(['id' => $id, 'deleted' => 0])->one();
                if (!$r) {
                    return null;
                }
                return ['label' => ($r->ownerUnit->name ?? '—') . ' · เวชระเบียน', 'rev' => null,
                    'snapshot' => ['unit' => $r->ownerUnit->name ?? null, 'total_charts' => $r->total_charts, 'overall_percent' => $r->overallPercent()]];
            case 'indicator':
                $r = Ha12Indicator::find()->with('ownerUnit')->where(['id' => $id, 'deleted' => 0])->one();
                if (!$r) {
                    return null;
                }
                return ['label' => ($r->ownerUnit->name ?? '—') . ' · ' . $r->name, 'rev' => null,
                    'snapshot' => ['unit' => $r->ownerUnit->name ?? null, 'name' => $r->name, 'target' => $r->target, 'values' => $r->valueMap()]];
            default:
                return null;
        }
    }

    /** หน่วยงานที่เลือกเป็นขอบเขตรอบได้ (ผู้ดูแลเห็นทุกหน่วย) */
    public static function scopeUnitOptions(): array
    {
        return Ha12ReviewService::orderedUnits();
    }
}
