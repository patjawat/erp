<?php

namespace app\modules\ha12\services;

use app\modules\ha12\models\Ha12MedCount;
use app\modules\ha12\models\Ha12MedGroup;
use app\modules\ha12\models\Ha12MedReport;
use app\modules\ha12\models\Ha12MedTemplate;
use Yii;

/**
 * ตรรกะกลางของรายงานความคลาดเคลื่อนทางยา (กิจกรรม 7)
 *
 * สิทธิ์/ขอบเขตหน่วยงาน ใช้ร่วมกับ Ha12ReviewService (isManager/unitScopeIds/units)
 */
class Ha12MedService
{
    /** จัดการรายงานนี้ได้ไหม — ผู้ดูแล / ผู้สร้าง / คนในสายหน่วยเจ้าของ */
    public static function canManage(Ha12MedReport $r, ?int $userId, ?int $empUnitId): bool
    {
        if (Ha12ReviewService::isManager()) {
            return true;
        }
        if ($userId !== null && (int) $r->created_by === (int) $userId) {
            return true;
        }
        return $r->owner_unit_id
            && in_array((int) $r->owner_unit_id, Ha12ReviewService::unitScopeIds($empUnitId), true);
    }

    public static function canView(Ha12MedReport $r, ?int $userId, ?int $empUnitId): bool
    {
        return self::canManage($r, $userId, $empUnitId);
    }

    /**
     * สร้าง 5 หัวข้อหลัก + ความเสี่ยงย่อยมาตรฐานให้รายงานที่เพิ่งสร้าง (ครั้งเดียว)
     * เรียกใน transaction หลัง report->save()
     */
    public static function scaffoldGroups(Ha12MedReport $report): void
    {
        foreach (Ha12MedTemplate::groups() as $tpl) {
            $group = new Ha12MedGroup([
                'report_id' => (int) $report->id,
                'group_no' => (int) $tpl['no'],
                'divisor_unit' => $tpl['default_unit'],
            ]);
            $group->save(false);

            $sort = 0;
            foreach ($tpl['subrisks'] as $name) {
                $c = new Ha12MedCount([
                    'group_id' => (int) $group->id,
                    'risk_name' => $name,
                    'is_other' => 0,
                    'sort' => ++$sort,
                ]);
                $c->save(false);
            }
        }
    }

    /** สร้างรายงานพร้อมโครงหัวข้อ/ความเสี่ยงมาตรฐาน (atomic) */
    public static function createWithScaffold(Ha12MedReport $report): bool
    {
        $tx = Yii::$app->db->beginTransaction();
        try {
            if (!$report->save()) {
                $tx->rollBack();
                return false;
            }
            self::scaffoldGroups($report);
            $tx->commit();
            return true;
        } catch (\Throwable $e) {
            $tx->rollBack();
            Yii::error('Ha12MedService::createWithScaffold ' . $e->getMessage(), 'ha12');
            return false;
        }
    }
}
