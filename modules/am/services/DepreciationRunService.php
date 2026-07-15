<?php

namespace app\modules\am\services;

use Yii;
use app\modules\am\models\Asset;
use app\modules\am\models\AccountingPeriod;
use app\modules\am\models\AssetDepreciation;
use app\modules\am\models\DepreciationProfile;

/**
 * ประมวลผลค่าเสื่อมของงวด (รายเดือน) — สร้าง/อัปเดตรายการ asset_depreciations
 *
 * หลักการ:
 *   - คำนวณระดับรายเดือนเท่านั้น (ไตรมาส/ปีให้รายงานรวมจากรายเดือน)
 *   - ห้ามคำนวณทับงวดที่ posted/locked
 *   - รายการที่ posted/locked แล้วจะไม่ถูกแตะ (ต้องใช้ adjustment/reversal)
 *   - ใช้ snapshot บนทรัพย์สินเป็นเกณฑ์ (แก้ profile ภายหลังไม่กระทบย้อนหลัง)
 *   - เก็บ snapshot เกณฑ์ลงรายการทุกครั้งเพื่อ audit
 */
class DepreciationRunService
{
    /** มูลค่าซากมาตรฐานราชการเมื่อไม่ได้ระบุ (บาท) */
    public const DEFAULT_SALVAGE_BAHT = 1.0;

    /**
     * สร้างพารามิเตอร์คำนวณจาก snapshot ของทรัพย์สิน (+ เกณฑ์ที่ผูก ถ้ามี)
     */
    public static function paramsForAsset(Asset $asset, ?DepreciationProfile $profile = null): array
    {
        $cost = (float) $asset->price;

        // อายุเป็นเดือน: ใช้ snapshot months ก่อน, ไม่มีก็แปลงจากปี
        $lifeMonths = $asset->useful_life_months
            ? (int) $asset->useful_life_months
            : ((int) $asset->useful_life * 12);

        // มูลค่าซาก: residual_value คือ snapshot ซาก (ไม่มี = 1 บาทตามมาตรฐาน)
        $salvage = ($asset->residual_value !== null && $asset->residual_value !== '')
            ? (float) $asset->residual_value
            : self::DEFAULT_SALVAGE_BAHT;

        // วันเริ่มคิด: ใช้ snapshot ที่ resolve แล้ว ไม่มีก็ใช้วันรับเข้า
        $startDate = $asset->depreciation_start_date ?: $asset->receive_date;

        $basis = $profile && $profile->calculation_basis
            ? $profile->calculation_basis
            : DepreciationProfile::BASIS_MONTHLY;

        // อัตราหลายช่วงจากเกณฑ์ (ถ้ามี)
        $tiers = [];
        if ($profile) {
            foreach ($profile->rates as $r) {
                $tiers[] = [
                    'start_month' => (int) $r->start_month,
                    'end_month' => $r->end_month !== null ? (int) $r->end_month : null,
                    'rate_percent' => (float) $r->rate_percent,
                ];
            }
        }

        // อัตราเดียว (annual_rate) เมื่อไม่มีช่วงและไม่มีอายุ
        $annualRate = null;
        if (empty($tiers) && $lifeMonths <= 0 && $asset->depreciation_rate) {
            $annualRate = (float) $asset->depreciation_rate;
        }

        return DepreciationCalculator::params([
            'cost' => $cost,
            'salvage_value' => $salvage,
            'salvage_value_type' => DepreciationProfile::SALVAGE_AMOUNT,
            'method' => $asset->depreciation_method ?: DepreciationProfile::METHOD_STRAIGHT_LINE,
            'useful_life_months' => $lifeMonths,
            'annual_rate' => $annualRate,
            'calculation_basis' => $basis,
            'start_rule' => DepreciationProfile::START_READY_DATE, // start_date ถูก resolve แล้ว
            'rounding_scale' => $profile ? (int) $profile->rounding_scale : 2,
            'acquisition_date' => $startDate,
            'rate_tiers' => $tiers,
        ]);
    }

    /**
     * schedule รายเดือนของทรัพย์สิน 1 ชิ้น (สำหรับหน้า "ทดลองคำนวณ")
     */
    public function previewForAsset(Asset $asset): array
    {
        $resolver = new DepreciationProfileResolver();
        $resolved = $resolver->resolve($asset);
        $params = self::paramsForAsset($asset, $resolved['profile'] ?? null);
        $schedule = DepreciationCalculator::buildMonthlySchedule($params);
        $schedule['resolved'] = $resolved;
        $schedule['params'] = $params;
        return $schedule;
    }

    /**
     * ทรัพย์สินที่มีสิทธิ์คิดค่าเสื่อม
     *
     * @return Asset[]
     */
    public function eligibleAssets(): array
    {
        return Asset::find()
            ->andWhere(['deleted_at' => null])
            ->andWhere(['>', 'price', 0])
            ->andWhere(['or',
                ['and', ['not', ['useful_life_months' => null]], ['>', 'useful_life_months', 0]],
                ['and', ['not', ['useful_life' => null]], ['>', 'useful_life', 0]],
            ])
            ->andWhere(['not', ['receive_date' => null]])
            ->all();
    }

    /**
     * ประมวลผลค่าเสื่อมของงวด (เฉพาะงวดชนิด month)
     *
     * @param bool $save true=บันทึกลง DB, false=preview เท่านั้น
     * @return array{success:bool, message:string, period:string, created:int, updated:int, skipped:int, rows:array}
     */
    public function runForPeriod(AccountingPeriod $period, bool $save = true, ?int $userId = null): array
    {
        $base = ['success' => false, 'message' => '', 'period' => $period->name, 'created' => 0, 'updated' => 0, 'skipped' => 0, 'rows' => []];

        if ($period->period_type !== AccountingPeriod::TYPE_MONTH) {
            $base['message'] = 'คำนวณได้เฉพาะงวดรายเดือน (ไตรมาส/ปีให้รวมจากรายเดือน)';
            return $base;
        }
        if ($period->isClosed()) {
            $base['message'] = 'งวดนี้ปิดแล้ว (posted/locked) ห้ามคำนวณทับ';
            return $base;
        }

        $periodYear = (int) date('Y', strtotime($period->start_date));
        $periodMonth = (int) date('n', strtotime($period->start_date));
        $resolver = new DepreciationProfileResolver();

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $rows = [];

        $tx = $save ? Yii::$app->db->beginTransaction() : null;
        try {
            foreach ($this->eligibleAssets() as $asset) {
                $resolved = $resolver->resolve($asset);
                $profile = $resolved['profile'] ?? null;
                $params = self::paramsForAsset($asset, $profile);
                $sch = DepreciationCalculator::buildMonthlySchedule($params);
                if (!$sch['can_calculate']) {
                    continue;
                }

                // หาแถว schedule ของเดือนงวดนี้
                $row = null;
                foreach ($sch['schedule'] as $r) {
                    if ($r['calendar_year'] === $periodYear && $r['calendar_month'] === $periodMonth) {
                        $row = $r;
                        break;
                    }
                }
                if ($row === null) {
                    continue; // ทรัพย์สินยังไม่เริ่ม หรือหมดอายุค่าเสื่อมในงวดนี้
                }

                $existing = AssetDepreciation::findOne([
                    'asset_id' => $asset->id,
                    'accounting_period_id' => $period->id,
                    'transaction_type' => AssetDepreciation::TX_NORMAL,
                ]);

                // รายการที่ปิดแล้วห้ามแตะ
                if ($existing && $existing->isLocked()) {
                    $skipped++;
                    continue;
                }

                $model = $existing ?: new AssetDepreciation();
                $isNew = $model->isNewRecord;
                $model->asset_id = $asset->id;
                $model->accounting_period_id = $period->id;
                $model->transaction_type = AssetDepreciation::TX_NORMAL;
                $model->opening_cost = $sch['cost'];
                $model->depreciable_base = $sch['depreciable_base'];
                $model->depreciation_amount = $row['depreciation'];
                $model->adjustment_amount = 0;
                $model->accumulated_depreciation = $row['accumulated_depreciation'];
                $model->closing_net_book_value = $row['remaining_value'];
                $model->calculation_days = $row['days_used'];
                $model->calculation_months = 1;
                $model->depreciation_profile_id = $resolved['profile_id'] ?? $asset->depreciation_profile_id;
                $model->method_snapshot = $params['method'];
                $model->useful_life_months_snapshot = $params['useful_life_months'] ?: null;
                $model->rate_snapshot = $row['rate_percent'];
                $model->salvage_value_snapshot = $sch['salvage'];
                $model->status = AssetDepreciation::STATUS_CALCULATED;
                $model->calculated_at = date('Y-m-d H:i:s');
                $model->calculated_by = $userId;

                if ($save) {
                    if (!$model->save()) {
                        throw new \RuntimeException('บันทึกค่าเสื่อมไม่สำเร็จ (asset ' . $asset->id . '): ' . json_encode($model->getErrors(), JSON_UNESCAPED_UNICODE));
                    }
                }
                $isNew ? $created++ : $updated++;

                $rows[] = [
                    'asset_id' => $asset->id,
                    'code' => $asset->code,
                    'name' => $asset->asset_name,
                    'depreciation' => $row['depreciation'],
                    'accumulated' => $row['accumulated_depreciation'],
                    'nbv' => $row['remaining_value'],
                    'days' => $row['days_used'],
                ];
            }

            if ($save) {
                // อัปเดตสถานะงวดเป็น "คำนวณแล้ว"
                if ($period->status === AccountingPeriod::STATUS_OPEN) {
                    $period->status = AccountingPeriod::STATUS_CALCULATED;
                    $period->save(false);
                }
                $tx->commit();
            }
        } catch (\Throwable $e) {
            if ($tx) {
                $tx->rollBack();
            }
            $base['message'] = 'ผิดพลาด: ' . $e->getMessage();
            return $base;
        }

        $base['success'] = true;
        $base['created'] = $created;
        $base['updated'] = $updated;
        $base['skipped'] = $skipped;
        $base['rows'] = $rows;
        $base['message'] = ($save ? 'ประมวลผล' : 'ทดลองคำนวณ') . "งวด {$period->name}: สร้าง {$created} ปรับ {$updated} ข้าม {$skipped} รายการ";
        return $base;
    }
}
