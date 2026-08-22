<?php

namespace app\modules\am\services;

use Yii;
use app\modules\am\models\AccountingPeriod;
use app\modules\am\models\AssetDepreciation;

/**
 * บันทึกบัญชี/ปิดงวดค่าเสื่อม (post / lock / reversal / adjustment)
 *
 * ข้อกำหนด:
 *   - post ได้เฉพาะงวดที่คำนวณแล้ว (calculated) — งวดที่ posted/locked ห้ามทำซ้ำ
 *   - รายการที่ posted/locked ห้ามแก้ไขโดยตรง → ใช้ reversal/adjustment
 *   - ใช้ database transaction ทุกการเปลี่ยนสถานะ
 *   - post งวดไตรมาส/ปี = post งวดเดือนที่อยู่ในช่วงวันที่เดียวกัน (ยอดรวมจากรายเดือน)
 */
class DepreciationPostingService
{
    /**
     * บันทึกบัญชีงวด (เปลี่ยนรายการ + งวด เป็น posted)
     *
     * @return array{success:bool, message:string, posted:int}
     */
    public function postPeriod(AccountingPeriod $period, ?int $userId = null): array
    {
        if (in_array($period->status, [AccountingPeriod::STATUS_POSTED, AccountingPeriod::STATUS_LOCKED], true)) {
            return ['success' => false, 'message' => 'งวดนี้ถูกบันทึกบัญชี/ล็อกแล้ว', 'posted' => 0];
        }
        $blocker = $this->unpostedEarlierPeriod($period);
        if ($blocker !== null) {
            return [
                'success' => false,
                'message' => "ต้องบันทึกบัญชีงวดก่อนหน้าให้เสร็จก่อน: {$blocker->name}",
                'posted' => 0,
            ];
        }

        $tx = Yii::$app->db->beginTransaction();
        try {
            $posted = $this->postPeriodInternal($period, $userId);
            $tx->commit();
            return ['success' => true, 'message' => "บันทึกบัญชีงวด {$period->name} เรียบร้อย ({$posted} รายการ)", 'posted' => $posted];
        } catch (\Throwable $e) {
            $tx->rollBack();
            return ['success' => false, 'message' => 'บันทึกบัญชีไม่สำเร็จ: ' . $e->getMessage(), 'posted' => 0];
        }
    }

    /**
     * งวดเดือนก่อนหน้าในปีงบเดียวกันที่ยังไม่ได้บันทึกบัญชี — บัญชีต้องปิดตามลำดับ
     * ไม่งั้นยอดค่าเสื่อมสะสมในสมุดบัญชีจะข้ามเดือน
     */
    public function unpostedEarlierPeriod(AccountingPeriod $period): ?AccountingPeriod
    {
        return AccountingPeriod::find()
            ->where([
                'period_type' => AccountingPeriod::TYPE_MONTH,
                'fiscal_year' => $period->fiscal_year,
            ])
            ->andWhere(['not in', 'status', [AccountingPeriod::STATUS_POSTED, AccountingPeriod::STATUS_LOCKED]])
            ->andWhere(['<', 'start_date', $period->start_date])
            ->orderBy(['start_date' => SORT_ASC])
            ->one();
    }

    private function postPeriodInternal(AccountingPeriod $period, ?int $userId): int
    {
        $now = date('Y-m-d H:i:s');
        $posted = 0;

        if ($period->period_type === AccountingPeriod::TYPE_MONTH) {
            $rows = AssetDepreciation::find()
                ->where(['accounting_period_id' => $period->id])
                ->andWhere(['not in', 'status', [AssetDepreciation::STATUS_POSTED, AssetDepreciation::STATUS_LOCKED, AssetDepreciation::STATUS_REVERSED]])
                ->all();
            foreach ($rows as $r) {
                $r->status = AssetDepreciation::STATUS_POSTED;
                $r->posted_at = $now;
                $r->posted_by = $userId;
                $r->save(false);
                $posted++;
            }
        } else {
            // ไตรมาส/ปี: post งวดเดือนที่อยู่ในช่วงวันที่
            $months = AccountingPeriod::find()
                ->where(['period_type' => AccountingPeriod::TYPE_MONTH])
                ->andWhere(['>=', 'start_date', $period->start_date])
                ->andWhere(['<=', 'end_date', $period->end_date])
                ->all();
            foreach ($months as $mp) {
                if (in_array($mp->status, [AccountingPeriod::STATUS_POSTED, AccountingPeriod::STATUS_LOCKED], true)) {
                    continue;
                }
                $posted += $this->postPeriodInternal($mp, $userId);
                $mp->status = AccountingPeriod::STATUS_POSTED;
                $mp->closed_at = $now;
                $mp->closed_by = $userId;
                $mp->save(false);
            }
        }

        $period->status = AccountingPeriod::STATUS_POSTED;
        $period->closed_at = $now;
        $period->closed_by = $userId;
        $period->save(false);

        return $posted;
    }

    /**
     * ล็อกงวด (จาก posted → locked) แก้ไขไม่ได้อีก
     */
    public function lockPeriod(AccountingPeriod $period, ?int $userId = null): array
    {
        if ($period->status !== AccountingPeriod::STATUS_POSTED) {
            return ['success' => false, 'message' => 'ต้องบันทึกบัญชี (posted) ก่อนจึงจะล็อกได้', 'locked' => 0];
        }
        $tx = Yii::$app->db->beginTransaction();
        try {
            $locked = 0;
            $rows = AssetDepreciation::find()
                ->where(['accounting_period_id' => $period->id, 'status' => AssetDepreciation::STATUS_POSTED])
                ->all();
            foreach ($rows as $r) {
                $r->status = AssetDepreciation::STATUS_LOCKED;
                $r->save(false);
                $locked++;
            }
            $period->status = AccountingPeriod::STATUS_LOCKED;
            $period->save(false);
            $tx->commit();
            return ['success' => true, 'message' => "ล็อกงวด {$period->name} เรียบร้อย", 'locked' => $locked];
        } catch (\Throwable $e) {
            $tx->rollBack();
            return ['success' => false, 'message' => 'ล็อกไม่สำเร็จ: ' . $e->getMessage(), 'locked' => 0];
        }
    }

    /**
     * กลับรายการค่าเสื่อมที่ post แล้ว (สร้างรายการ reversal ยอดติดลบ)
     */
    public function reverse(AssetDepreciation $normal, ?int $userId = null, ?string $note = null): array
    {
        if ($normal->transaction_type !== AssetDepreciation::TX_NORMAL) {
            return ['success' => false, 'message' => 'กลับรายการได้เฉพาะรายการปกติ'];
        }
        if ($normal->status !== AssetDepreciation::STATUS_POSTED) {
            return ['success' => false, 'message' => 'กลับรายการได้เฉพาะรายการที่บันทึกบัญชีแล้ว'];
        }
        $exists = AssetDepreciation::findOne([
            'asset_id' => $normal->asset_id,
            'accounting_period_id' => $normal->accounting_period_id,
            'transaction_type' => AssetDepreciation::TX_REVERSAL,
        ]);
        if ($exists) {
            return ['success' => false, 'message' => 'มีรายการกลับรายการของงวดนี้แล้ว'];
        }

        $tx = Yii::$app->db->beginTransaction();
        try {
            $now = date('Y-m-d H:i:s');
            $rev = new AssetDepreciation();
            $rev->asset_id = $normal->asset_id;
            $rev->accounting_period_id = $normal->accounting_period_id;
            $rev->transaction_type = AssetDepreciation::TX_REVERSAL;
            $rev->opening_cost = $normal->opening_cost;
            $rev->depreciable_base = $normal->depreciable_base;
            $rev->depreciation_amount = -1 * (float) $normal->depreciation_amount;
            $rev->adjustment_amount = 0;
            $rev->accumulated_depreciation = $normal->accumulated_depreciation;
            $rev->closing_net_book_value = $normal->closing_net_book_value;
            $rev->depreciation_profile_id = $normal->depreciation_profile_id;
            $rev->method_snapshot = $normal->method_snapshot;
            $rev->useful_life_months_snapshot = $normal->useful_life_months_snapshot;
            $rev->rate_snapshot = $normal->rate_snapshot;
            $rev->salvage_value_snapshot = $normal->salvage_value_snapshot;
            $rev->status = AssetDepreciation::STATUS_POSTED;
            $rev->posted_at = $now;
            $rev->posted_by = $userId;
            $rev->note = $note ?: 'กลับรายการ';
            if (!$rev->save()) {
                throw new \RuntimeException(json_encode($rev->getErrors(), JSON_UNESCAPED_UNICODE));
            }
            // รายการเดิม → reversed
            $normal->status = AssetDepreciation::STATUS_REVERSED;
            $normal->save(false);
            $tx->commit();
            return ['success' => true, 'message' => 'กลับรายการเรียบร้อย'];
        } catch (\Throwable $e) {
            $tx->rollBack();
            return ['success' => false, 'message' => 'กลับรายการไม่สำเร็จ: ' . $e->getMessage()];
        }
    }

    /**
     * สร้างรายการปรับปรุง (adjustment) สำหรับงวดที่ปิดแล้ว — ไม่แก้รายการเดิม
     */
    public function createAdjustment(int $assetId, int $periodId, float $amount, ?string $note = null, ?int $userId = null): array
    {
        $exists = AssetDepreciation::findOne([
            'asset_id' => $assetId,
            'accounting_period_id' => $periodId,
            'transaction_type' => AssetDepreciation::TX_ADJUSTMENT,
        ]);
        if ($exists) {
            return ['success' => false, 'message' => 'มีรายการปรับปรุงของงวดนี้แล้ว'];
        }

        $tx = Yii::$app->db->beginTransaction();
        try {
            $now = date('Y-m-d H:i:s');
            $adj = new AssetDepreciation();
            $adj->asset_id = $assetId;
            $adj->accounting_period_id = $periodId;
            $adj->transaction_type = AssetDepreciation::TX_ADJUSTMENT;
            $adj->opening_cost = 0;
            $adj->depreciable_base = 0;
            $adj->depreciation_amount = 0;
            $adj->adjustment_amount = $amount;
            $adj->accumulated_depreciation = 0;
            $adj->closing_net_book_value = 0;
            $adj->status = AssetDepreciation::STATUS_POSTED;
            $adj->posted_at = $now;
            $adj->posted_by = $userId;
            $adj->note = $note ?: 'รายการปรับปรุง';
            if (!$adj->save()) {
                throw new \RuntimeException(json_encode($adj->getErrors(), JSON_UNESCAPED_UNICODE));
            }
            $tx->commit();
            return ['success' => true, 'message' => 'สร้างรายการปรับปรุงเรียบร้อย'];
        } catch (\Throwable $e) {
            $tx->rollBack();
            return ['success' => false, 'message' => 'สร้างรายการปรับปรุงไม่สำเร็จ: ' . $e->getMessage()];
        }
    }
}
