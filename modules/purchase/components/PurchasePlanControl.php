<?php

namespace app\modules\purchase\components;

use Yii;
use app\components\AppHelper;
use app\modules\approve\models\Approve;
use app\modules\plan\components\PlanHelper;
use app\modules\plan\models\PlanOrder;
use app\modules\purchase\models\Order;

/**
 * งานจัดซื้อผูกแผนรายปี (เปิดที่ /plan/plan-period คอลัมน์ "จัดซื้อผูกแผน")
 *
 * ปีที่เปิด: ผู้ขอไม่ต้องเลือกในแผน/นอกแผน — ตอนส่งคำขอประทับ data_json.plan_control=1 และ request_type=NULL (รอตรวจแผน)
 * แล้วพัสดุลงทะเบียนคุม + เลือกแผน → check() ตัดสิน
 *   ผ่าน   = ในแผน: ขั้นอนุมัติผ่านอัตโนมัติ → status 3 (ออกใบสั่งซื้อได้)
 *   ไม่ผ่าน = นอกแผน: หัวหน้า → พัสดุ → ผอ. (ขั้นอนุมัติเดิม) → status 2 → พัสดุยืนยันทะเบียนคุม → 3
 *
 * ปีที่ไม่เปิด / ใบที่ส่งคำขอก่อนเปิด: ทำงานแบบเดิมทุกอย่าง (ไม่แตะข้อมูลเก่า)
 */
class PurchasePlanControl
{
    const OK           = 'ok';
    const NO_PLAN      = 'no_plan';
    const NOT_FOUND    = 'not_found';
    const WRONG_YEAR   = 'wrong_year';
    const NOT_APPROVED = 'not_approved';
    const OVER_BUDGET  = 'over_budget';

    const STATUS_CANCEL = 8;

    /** ยอมให้เกินวงเงินแผนได้ไม่เกินเท่านี้ (บาท) — กันเศษปัด ไม่ถือว่าเกินแผน */
    const BUDGET_TOLERANCE = 1.00;

    /**
     * ใบนี้ใช้วิธีผูกแผนไหม
     * ส่งคำขอแล้ว = ยึดค่าที่ประทับไว้ตอนส่ง (ปิด/เปิดสวิตช์ภายหลังไม่เปลี่ยนเส้นทางใบที่ค้างอยู่)
     * ยังเป็นร่าง = ตามสวิตช์ของปีงบของใบ
     */
    public static function isControlled(Order $order): bool
    {
        if ((string) $order->status !== '') {
            return !empty($order->data_json['plan_control']);
        }
        return PlanHelper::purchaseControl($order->thai_year ?: AppHelper::YearBudget());
    }

    /** ใบผูกแผนที่ยังไม่ได้ตัดสินในแผน/นอกแผน (รอพัสดุลงทะเบียนคุม) */
    public static function isPending(Order $order): bool
    {
        return self::isControlled($order) && (string) $order->status !== '' && $order->request_type === null;
    }

    /** ยอดเงินของใบ (รวม VAT หลังหักส่วนลด) — ใช้เทียบวงเงินแผน */
    public static function orderAmount(Order $order): float
    {
        $v = $order->calculateVAT();
        return (float) ($v['priceAfterVAT'] ?? 0);
    }

    /** ยอดที่ใบอื่น (ไม่รวมใบยกเลิก) ใช้แผนนี้ไปแล้ว */
    public static function usedAmount(int $planOrderId, ?int $excludeOrderId = null): float
    {
        $q = Order::find()
            ->where(['name' => 'order', 'plan_order_id' => $planOrderId])
            ->andWhere(['or', ['status' => null], ['<>', 'status', self::STATUS_CANCEL]]);
        if ($excludeOrderId) {
            $q->andWhere(['<>', 'id', $excludeOrderId]);
        }
        $sum = 0.0;
        foreach ($q->all() as $o) {
            $sum += self::orderAmount($o);
        }
        return $sum;
    }

    /**
     * ตรวจแผนที่เลือกกับใบขอซื้อ
     * @return array{code:string, planned:bool, message:string, plan_order_id:?int, plan_title:?string,
     *               budget:float, used:float, amount:float, remaining:float}
     */
    public static function check(Order $order, $planOrderId): array
    {
        $amount = self::orderAmount($order);
        $r = [
            'code' => self::NO_PLAN, 'planned' => false, 'message' => '',
            'plan_order_id' => $planOrderId ? (int) $planOrderId : null, 'plan_title' => null,
            'budget' => 0.0, 'used' => 0.0, 'amount' => $amount, 'remaining' => 0.0,
        ];

        if (empty($planOrderId)) {
            $r['message'] = 'ไม่ได้เลือกรายการแผน — จะเป็น "นอกแผน" และต้องรออนุมัติ';
            return $r;
        }

        $plan = PlanOrder::findOne((int) $planOrderId);
        if (!$plan || $plan->deleted_at !== null) {
            $r['code'] = self::NOT_FOUND;
            $r['message'] = 'ไม่พบรายการแผนที่เลือก (อาจถูกลบ) — จะเป็น "นอกแผน" และต้องรออนุมัติ';
            return $r;
        }

        $r['plan_title'] = trim(strip_tags((string) ($plan->description ?: $plan->title)));
        $r['budget'] = (float) $plan->order_price;

        if ((int) $plan->thai_year !== (int) $order->thai_year) {
            $r['code'] = self::WRONG_YEAR;
            $r['message'] = 'แผนที่เลือกเป็นของปี ' . $plan->thai_year . ' แต่ใบขอซื้อเป็นปี ' . $order->thai_year . ' — จะเป็น "นอกแผน" และต้องรออนุมัติ';
            return $r;
        }

        if ($plan->status !== 'approve') {
            $r['code'] = self::NOT_APPROVED;
            $r['message'] = 'แผนที่เลือกยังไม่ได้รับอนุมัติ — จะเป็น "นอกแผน" และต้องรออนุมัติ';
            return $r;
        }

        $r['used'] = self::usedAmount((int) $plan->id, $order->id ? (int) $order->id : null);
        $r['remaining'] = $r['budget'] - $r['used'];

        // เผื่อเศษปัดจาก VAT/ราคาต่อหน่วยในแผน (เจอจริง: แผน 699,999.96 กับใบ 700,000.00)
        if ($amount - $r['remaining'] > self::BUDGET_TOLERANCE) {
            $r['code'] = self::OVER_BUDGET;
            $r['message'] = 'ยอดเกินวงเงินแผน: วงเงิน ' . number_format($r['budget'], 2)
                . ' ใช้ไปแล้ว ' . number_format($r['used'], 2)
                . ' คงเหลือ ' . number_format($r['remaining'], 2)
                . ' แต่ใบนี้ ' . number_format($amount, 2)
                . ' บาท — จะปรับเป็น "นอกแผน" และต้องรออนุมัติ';
            return $r;
        }

        $r['code'] = self::OK;
        $r['planned'] = true;
        $r['message'] = 'อยู่ในแผน — คงเหลือหลังใบนี้ ' . number_format($r['remaining'] - $amount, 2) . ' บาท';
        return $r;
    }

    /**
     * ตัดสินในแผน/นอกแผน + สร้างขั้นอนุมัติ (เรียกตอนพัสดุลงทะเบียนคุมใบที่รอตรวจแผน)
     * ไม่ save — ผู้เรียก save เอง
     */
    public static function decide(Order $order, array $check): void
    {
        $plan = $check['plan_order_id'] ? PlanOrder::findOne($check['plan_order_id']) : null;
        if ($plan) {
            // ยึด plan_order เป็นหลัก แล้วเติมสายหมวดตามแผน (ค่าที่ส่งมาจาก dropdown อาจไม่ตรงกัน)
            $order->plan_type_id = $plan->plan_type_id;
            $order->plan_category_id = $plan->plan_category_id;
            $order->plan_item_id = $plan->plan_item_id;
        }

        $order->request_type = $check['planned'] ? 'planned' : 'unplanned';
        $me = \app\components\UserHelper::GetEmployee();
        $dj = $order->data_json ?: [];
        $dj['plan_check'] = [
            'code' => $check['code'],
            'message' => $check['message'],
            'plan_order_id' => $check['plan_order_id'],
            'budget' => $check['budget'],
            'used' => $check['used'],
            'amount' => $check['amount'],
            'checked_at' => date('Y-m-d H:i:s'),
            'checked_by' => $me ? $me->fullname : null,
        ];
        $order->data_json = $dj;

        if ($check['planned']) {
            // ในแผน: ข้ามรอ ผอ. ไปขั้นออกใบสั่งซื้อ (ทะเบียนคุมเพิ่งกรอกเสร็จ)
            $order->status = 3;
        }
        // นอกแผน: คง status 1 — ขั้นอนุมัติเดิมพาไป 2 เมื่อ ผอ.อนุมัติ

        $exists = Approve::find()->where(['from_id' => $order->id, 'name' => 'purchase'])->exists();
        if (!$exists) {
            $order->createApprove();
        }
    }

    /**
     * รายการแผนสำหรับ dropdown ทะเบียนคุม (ใบผูกแผน): เฉพาะปีเดียวกับใบ + อนุมัติแล้ว + ไม่ถูกลบ
     * ไม่จำกัดหน่วยงาน (ใช้ได้ทั้งแผนหน่วยงานผู้ขอและแผนพัสดุ) — แสดงชื่อหน่วยงาน/คงเหลือช่วยเลือก
     */
    public static function planOptions(Order $order, $planItemId): array
    {
        $plans = PlanOrder::find()
            ->where([
                'plan_item_id' => $planItemId,
                'thai_year' => $order->thai_year,
                'status' => 'approve',
                'deleted_at' => null,
            ])
            ->orderBy(['id' => SORT_ASC])
            ->all();

        $out = [];
        foreach ($plans as $p) {
            $remaining = (float) $p->order_price - self::usedAmount((int) $p->id, $order->id ? (int) $order->id : null);
            $title = trim(strip_tags((string) ($p->description ?: $p->title)));
            $out[] = [
                'id' => $p->id,
                'name' => mb_strimwidth($title, 0, 80, '…') . ' — ' . $p->departmentName()
                    . ' (คงเหลือ ' . number_format($remaining, 2) . ')',
            ];
        }
        return $out;
    }
}
