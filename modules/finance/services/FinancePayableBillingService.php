<?php

namespace app\modules\finance\services;

use Yii;
use app\modules\finance\models\FinancePayable;
use app\modules\finance\models\FinancePayableBilling;

/**
 * บันทึกรับวางบิล: บริษัทนำบิล (ที่การเงินรับเอกสารแล้ว) มาวาง → ผูกบิลกับใบรับวางบิล
 * และคำนวณวันครบกำหนดใหม่จากวันวางบิล + เครดิตของบิล
 */
class FinancePayableBillingService
{
    /**
     * @param array $data billing_date(Y-m-d), vendor_name, vendor_ref, deliverer_name, receiver_name, note
     * @param int[] $payableIds
     * @throws \DomainException
     */
    public function create(array $data, array $payableIds): FinancePayableBilling
    {
        $billingDate = (string) ($data['billing_date'] ?? '');
        $vendorName = trim((string) ($data['vendor_name'] ?? ''));
        if ($billingDate === '') {
            throw new \DomainException('กรุณาระบุวันที่รับวางบิล');
        }
        if ($vendorName === '') {
            throw new \DomainException('กรุณาเลือกบริษัท');
        }
        $payableIds = array_values(array_filter(array_map('intval', $payableIds)));
        if (!$payableIds) {
            throw new \DomainException('ยังไม่ได้เลือกบิลที่บริษัทนำมาวาง');
        }
        /** @var FinancePayable[] $bills */
        $bills = FinancePayable::find()->where([
            'id' => $payableIds,
            'status' => FinancePayable::STATUS_APPROVED,
            'billing_id' => null,
            'vendor_name_snapshot' => $vendorName,
        ])->all();
        if (count($bills) !== count($payableIds)) {
            throw new \DomainException('มีบิลที่เลือกถูกวางไปแล้วหรือไม่ใช่ของบริษัทนี้ กรุณาโหลดหน้าใหม่');
        }

        $billing = new FinancePayableBilling([
            'billing_no' => FinancePayableBilling::nextNo($billingDate),
            'billing_date' => $billingDate,
            'vendor_id' => (int) $bills[0]->vendor_id ?: null,
            'vendor_name' => $vendorName,
            'vendor_ref' => trim((string) ($data['vendor_ref'] ?? '')) ?: null,
            'deliverer_name' => trim((string) ($data['deliverer_name'] ?? '')) ?: null,
            'receiver_id' => Yii::$app->has('user') && !Yii::$app->user->isGuest ? Yii::$app->user->id : null,
            'receiver_name' => trim((string) ($data['receiver_name'] ?? '')) ?: null,
            'note' => trim((string) ($data['note'] ?? '')) ?: null,
            'bill_count' => count($bills),
            'total_amount' => round(array_sum(array_map(static fn(FinancePayable $p) => (float) $p->net_amount, $bills)), 2),
        ]);
        if (!$billing->save()) {
            throw new \DomainException(implode(' ', $billing->getFirstErrors()));
        }
        foreach ($bills as $p) {
            $p->billing_id = $billing->id;
            $p->billing_date = $billingDate;
            $p->due_date = FinancePayableDraftService::calculateDueDate($billingDate, (int) $p->credit_days);
            $p->save(false, ['billing_id', 'billing_date', 'due_date', 'updated_at', 'updated_by']);
        }
        return $billing;
    }

    /** ยกเลิกใบรับวางบิล — บิลกลับเป็น "รอวางบิล" (เลขที่ใบคงไว้ในทะเบียน) */
    public function cancel(FinancePayableBilling $billing, string $reason): void
    {
        $reason = trim($reason);
        if ($reason === '') {
            throw new \DomainException('กรุณาระบุเหตุผลที่ยกเลิก');
        }
        if ($billing->isCancelled()) {
            throw new \DomainException('ใบรับวางบิลนี้ยกเลิกไปแล้ว');
        }
        FinancePayable::updateAll(['billing_id' => null], ['billing_id' => $billing->id]);
        $billing->cancelled_at = date('Y-m-d H:i:s');
        $billing->cancelled_by = Yii::$app->has('user') && !Yii::$app->user->isGuest ? Yii::$app->user->id : null;
        $billing->cancel_reason = mb_substr($reason, 0, 255);
        $billing->save(false, ['cancelled_at', 'cancelled_by', 'cancel_reason']);
    }

    /** บริษัทที่มีบิลรับเอกสารแล้วแต่ยังไม่วางบิล: [ชื่อบริษัท => จำนวนบิล] */
    public static function vendorsWithOpenBills(): array
    {
        $rows = FinancePayable::find()->select(['vendor_name_snapshot', 'n' => 'COUNT(*)'])
            ->where(['status' => FinancePayable::STATUS_APPROVED, 'billing_id' => null])
            ->groupBy('vendor_name_snapshot')->orderBy(['vendor_name_snapshot' => SORT_ASC])->asArray()->all();
        $out = [];
        foreach ($rows as $r) {
            $out[$r['vendor_name_snapshot']] = (int) $r['n'];
        }
        return $out;
    }

    /** @return FinancePayable[] บิลรอวางบิลของบริษัท */
    public static function openBills(string $vendorName): array
    {
        return FinancePayable::find()
            ->where(['status' => FinancePayable::STATUS_APPROVED, 'billing_id' => null, 'vendor_name_snapshot' => $vendorName])
            ->orderBy(['invoice_date' => SORT_ASC, 'id' => SORT_ASC])->all();
    }
}
