<?php

namespace app\modules\plan\components;

use Yii;
use app\models\Categorise;
use app\components\AppHelper;

/**
 * PlanHelper — จัดการ "รอบการทำแผน" (planning period) ต่อปีงบประมาณ
 * เก็บใน categorise name='plan_period' (code=ปีงบ, data_json={phase, current})
 *
 * phase:
 *   open   = เปิดรับแผน (สร้าง/แก้ไข/ส่งได้)
 *   lock   = ปิดแก้ไข (เพิ่มใหม่ได้ แต่แก้ของเดิมไม่ได้ ยกเว้นผู้ดูแลแผน/admin)
 *   adjust = เปิดปรับแผน (ปรับแผนที่อนุมัติได้)
 *   closed = ปิดรอบ (ดูอย่างเดียว)
 */
class PlanHelper
{
    const PHASE_OPEN   = 'open';
    const PHASE_LOCK   = 'lock';
    const PHASE_ADJUST = 'adjust';
    const PHASE_CLOSED = 'closed';

    /** รอบทั้งหมด (ทุกปี) เรียงปีใหม่ก่อน */
    public static function periods()
    {
        return Categorise::find()
            ->where(['name' => 'plan_period'])
            ->orderBy(['code' => SORT_DESC])
            ->all();
    }

    /** รอบของปีที่ระบุ */
    public static function period($year)
    {
        return Categorise::findOne(['name' => 'plan_period', 'code' => (string) $year]);
    }

    /** ปีที่เปิดให้ทำแผนปัจจุบัน (data_json.current=1) — ไม่พบ = YearBudget()+1 */
    public static function currentPlanYear()
    {
        foreach (self::periods() as $p) {
            if (!empty(self::json($p)['current'])) {
                return (int) $p->code;
            }
        }
        return (int) AppHelper::YearBudget() + 1;
    }

    /** phase ของปี (ไม่ระบุ = ปีปัจจุบัน) ; ไม่มีรอบ = closed */
    public static function phase($year = null)
    {
        $year = $year ?: self::currentPlanYear();
        $p = self::period($year);
        return $p ? (self::json($p)['phase'] ?? self::PHASE_CLOSED) : self::PHASE_CLOSED;
    }

    /** สร้าง/เพิ่มแผนใหม่ได้ไหม (เปิดรับ หรือ ปิดแก้ไข-เพิ่มได้) ; ผู้ดูแลแผนทำได้เสมอ */
    public static function canAdd($year = null)
    {
        return in_array(self::phase($year), [self::PHASE_OPEN, self::PHASE_LOCK], true)
            || self::isPlanAdmin();
    }

    /** แก้ไขแผนเดิมได้ไหม (เฉพาะเปิดรับ) ; ผู้ดูแลแผน/admin แก้ได้เสมอ */
    public static function canEdit($year = null)
    {
        return self::phase($year) === self::PHASE_OPEN || self::isPlanAdmin();
    }

    /** ปรับแผน (renew) ได้ไหม (เฉพาะเปิดปรับแผน) ; ผู้ดูแลแผนทำได้เสมอ */
    public static function canAdjust($year = null)
    {
        return self::phase($year) === self::PHASE_ADJUST || self::isPlanAdmin();
    }

    /** ผู้ดูแลแผน (role plan) หรือ admin */
    public static function isPlanAdmin()
    {
        if (!Yii::$app->has('user') || Yii::$app->user->isGuest) {
            return false;
        }
        return Yii::$app->user->can('plan') || Yii::$app->user->can('admin');
    }

    /** ป้ายชื่อ phase */
    public static function phaseLabel($phase)
    {
        $map = [
            self::PHASE_OPEN   => 'เปิดรับแผน',
            self::PHASE_LOCK   => 'ปิดแก้ไข (เพิ่มได้)',
            self::PHASE_ADJUST => 'เปิดปรับแผน',
            self::PHASE_CLOSED => 'ปิดรอบ',
        ];
        return $map[$phase] ?? $phase;
    }

    /** สี badge ของ phase */
    public static function phaseClass($phase)
    {
        $map = [
            self::PHASE_OPEN   => 'bg-success',
            self::PHASE_LOCK   => 'bg-warning text-dark',
            self::PHASE_ADJUST => 'bg-info text-dark',
            self::PHASE_CLOSED => 'bg-secondary',
        ];
        return $map[$phase] ?? 'bg-secondary';
    }

    private static function json($model)
    {
        $dj = $model->data_json;
        if (!is_array($dj)) {
            $dj = json_decode((string) $dj, true) ?: [];
        }
        return $dj;
    }
}
