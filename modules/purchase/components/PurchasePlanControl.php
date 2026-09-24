<?php

namespace app\modules\purchase\components;

use Yii;
use app\components\AppHelper;
use app\modules\approve\models\Approve;
use app\modules\plan\components\PlanHelper;
use app\modules\plan\components\PlanItemAssetMap;
use app\modules\plan\models\PlanOrder;
use app\modules\purchase\models\Order;

/**
 * งานจัดซื้อผูกแผนรายปี (เปิดที่ /plan/plan-period คอลัมน์ "จัดซื้อผูกแผน")
 *
 * ปีที่เปิด: ผู้ขอไม่ต้องเลือกในแผน/นอกแผน — เลือก "รายการแผน" ในฟอร์มขอซื้อ (เว้นว่างได้)
 * ตอนกด "ส่งคำขอซื้อ" (มีรายการ/ยอดเงินแล้ว) check() ตัดสิน แล้วประทับ data_json.plan_control=1
 *   ผ่าน   = ในแผน: ขั้นอนุมัติผ่านอัตโนมัติ → status 2 → ลงทะเบียนคุมตามเดิม
 *   ไม่ผ่าน = นอกแผน (เตือนก่อนส่ง): หัวหน้า → พัสดุ → ผอ. (ขั้นอนุมัติเดิม) → status 2
 * ทะเบียนคุมแสดงแผนแบบอ่านอย่างเดียว
 *
 * isPending (request_type NULL) = ใบที่ส่งตามวิธีรุ่นแรก (ตัดสินที่ทะเบียนคุม) — ยังรองรับให้ลงทะเบียนต่อได้
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
    const TYPE_MISMATCH = 'type_mismatch';

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

    /**
     * ประเภทพัสดุที่แผนนี้ซื้อได้ ; [] = ไม่จำกัด
     * = ที่กำหนดไว้ของแผนงาน (/plan/plan-item-asset) + ประเภทวัสดุที่ระบุบนแผนเอง (แผนวัสดุ)
     */
    public static function allowedAssetTypes(PlanOrder $plan): array
    {
        $types = PlanItemAssetMap::typesFor($plan->plan_item_id);
        if (!empty($plan->asset_type_id)) {
            $types[] = (string) $plan->asset_type_id;
        }
        return array_values(array_unique($types));
    }

    /** ประเภทพัสดุของรายการในใบ (asset_item → asset_type) */
    public static function orderAssetTypes(Order $order): array
    {
        return (new \yii\db\Query())
            ->select('a.category_id')->distinct()
            ->from(['i' => 'orders'])
            ->innerJoin(['a' => 'categorise'], "a.name = 'asset_item' AND a.code = i.asset_item")
            ->where(['i.name' => 'order_item', 'i.category_id' => $order->id])
            ->andWhere(['not', ['a.category_id' => null]])
            ->column();
    }

    /** ชื่อประเภทพัสดุ (ไม่พบใช้รหัส) */
    public static function assetTypeTitles(array $codes): array
    {
        $map = \yii\helpers\ArrayHelper::map(
            \app\models\Categorise::find()->where(['name' => 'asset_type', 'code' => array_values($codes)])->all(),
            'code', 'title'
        );
        return array_map(fn($c) => $map[$c] ?? $c, array_values($codes));
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

        // ประเภทพัสดุต้องอยู่ในหมวดของแผน (กันเลือกแผนมั่วเพื่อให้ผ่าน แล้วซื้อของอื่น)
        $allowed = self::allowedAssetTypes($plan);
        if ($allowed && $order->id) {
            $outside = array_diff(self::orderAssetTypes($order), $allowed);
            if ($outside) {
                $r['code'] = self::TYPE_MISMATCH;
                $r['message'] = 'มีรายการประเภท ' . implode(', ', self::assetTypeTitles($outside))
                    . ' ซึ่งไม่อยู่ในแผนงานของแผนที่เลือก (ซื้อได้: ' . implode(', ', self::assetTypeTitles($allowed))
                    . ') — จะปรับเป็น "นอกแผน" และต้องรออนุมัติ';
                return $r;
            }
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

        // ในแผน: เข้าเส้นทางเดิม = ผ่านอนุมัติอัตโนมัติ → status 2 (บอลที่ 2 ลงทะเบียนคุม)
        // นอกแผน: คง status 1 — ขั้นอนุมัติเดิม (หัวหน้า → พัสดุ → ผอ.) พาไป 2 เมื่อ ผอ.อนุมัติ
        $order->status = $check['planned'] ? 2 : 1;

        $exists = Approve::find()->where(['from_id' => $order->id, 'name' => 'purchase'])->exists();
        if (!$exists) {
            $order->createApprove();
        }
    }

    /**
     * ต้นไม้แผนสำหรับเลือกทีละขั้นในฟอร์มขอซื้อ: ประเภท → หมวด → แผนงาน → รายการแผน
     * เฉพาะแผนอนุมัติแล้วของปีนั้น — หมวดที่ไม่มีแผนไม่แสดง
     * สายหมวดยึด plan_item → categorise (plan_type_id/plan_category_id บน plan_order ปนเปื้อน)
     * @return array ['types' => [{code,title,cats:[{code,title,items:[{code,title,plans:[{id,title,unit,budget,used,remaining}]}]}]}]]
     */
    public static function planTree(int $year, ?int $excludeOrderId = null): array
    {
        $rows = (new \yii\db\Query())
            ->select([
                'pid' => 'p.id',
                'icode' => 'i.code', 'ititle' => 'i.title',
                'ccode' => 'c.code', 'ctitle' => 'c.title',
                'tcode' => 't.code', 'ttitle' => 't.title',
            ])
            ->from(['p' => 'plan_order'])
            ->leftJoin(['i' => 'categorise'], "i.name = 'plan_item' AND i.code = p.plan_item_id")
            ->leftJoin(['c' => 'categorise'], "c.name = 'plan_category' AND c.code = i.category_id")
            ->leftJoin(['t' => 'categorise'], "t.name = 'plan_type' AND t.code = c.category_id")
            ->where(['p.thai_year' => $year, 'p.status' => 'approve', 'p.deleted_at' => null])
            ->orderBy(['t.code' => SORT_ASC, 'c.code' => SORT_ASC, 'i.code' => SORT_ASC, 'p.id' => SORT_ASC])
            ->all();

        $plans = PlanOrder::find()->where(['id' => array_column($rows, 'pid')])->indexBy('id')->all();
        $tree = [];
        foreach ($rows as $r) {
            $p = $plans[$r['pid']] ?? null;
            if (!$p) {
                continue;
            }
            $t = $r['tcode'] ?: '_';
            $c = $r['ccode'] ?: '_';
            $i = $r['icode'] ?: '_';
            $tree[$t]['code'] = $t;
            $tree[$t]['title'] = $r['ttitle'] ?: 'ไม่ระบุประเภท';
            $tree[$t]['cats'][$c]['code'] = $c;
            $tree[$t]['cats'][$c]['title'] = $r['ctitle'] ?: 'ไม่ระบุหมวด';
            $tree[$t]['cats'][$c]['items'][$i]['code'] = $i;
            $tree[$t]['cats'][$c]['items'][$i]['title'] = $r['ititle'] ?: 'ไม่ระบุแผนงาน';
            $budget = (float) $p->order_price;
            $used = self::usedAmount((int) $p->id, $excludeOrderId);
            $tree[$t]['cats'][$c]['items'][$i]['plans'][] = [
                'id' => (int) $p->id,
                'title' => trim(strip_tags((string) ($p->description ?: $p->title))),
                'unit' => $p->departmentName(),
                'budget' => $budget,
                'used' => $used,
                'remaining' => $budget - $used,
                'types' => self::assetTypeTitles(self::allowedAssetTypes($p)), // [] = ไม่จำกัดประเภท
            ];
        }

        // เป็น list เรียงลำดับ (JSON object คีย์ตัวเลขจะถูกเรียงใหม่ในเบราว์เซอร์)
        $out = [];
        foreach ($tree as $t) {
            $cats = [];
            foreach ($t['cats'] as $c) {
                $c['items'] = array_values($c['items']);
                $cats[] = $c;
            }
            $t['cats'] = $cats;
            $out[] = $t;
        }
        return ['types' => $out];
    }

    /** ข้อความแสดงแผน: ชื่อ — หน่วยงาน (คงเหลือ) */
    public static function planLabel(PlanOrder $p, ?int $excludeOrderId = null): string
    {
        $remaining = (float) $p->order_price - self::usedAmount((int) $p->id, $excludeOrderId);
        $title = trim(strip_tags((string) ($p->description ?: $p->title)));
        return mb_strimwidth($title, 0, 80, '…') . ' — ' . $p->departmentName()
            . ' (คงเหลือ ' . number_format($remaining, 2) . ')';
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
