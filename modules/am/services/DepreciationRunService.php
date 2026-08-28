<?php

namespace app\modules\am\services;

use Yii;
use app\modules\am\models\Asset;
use app\modules\am\models\AssetDisposal;
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
    public static function paramsForAsset(Asset $asset, ?DepreciationProfile $profile = null, ?string $disposalDate = null): array
    {
        $profileValues = null;
        if ($profile) {
            $tiers = [];
            foreach ($profile->rates as $r) {
                $tiers[] = [
                    'start_month' => (int) $r->start_month,
                    'end_month' => $r->end_month !== null ? (int) $r->end_month : null,
                    'rate_percent' => (float) $r->rate_percent,
                ];
            }
            $profileValues = [
                'useful_life_months' => $profile->useful_life_months,
                'annual_rate' => $profile->annual_rate,
                'salvage_value_type' => $profile->salvage_value_type,
                'salvage_value' => $profile->salvage_value,
                'calculation_basis' => $profile->calculation_basis,
                'start_rule' => $profile->start_rule,
                'rounding_scale' => $profile->rounding_scale,
                'method' => $profile->method,
                'rate_tiers' => $tiers,
            ];
        }

        return self::mergeParams([
            'price' => $asset->price,
            'useful_life_months' => $asset->useful_life_months,
            'useful_life' => $asset->useful_life,
            'residual_value' => $asset->residual_value,
            'depreciation_rate' => $asset->depreciation_rate,
            'depreciation_method' => $asset->depreciation_method,
            'depreciation_start_date' => $asset->depreciation_start_date,
            'depreciation_calculation_basis' => $asset->hasAttribute('depreciation_calculation_basis')
                ? $asset->depreciation_calculation_basis : null,
            'depreciation_start_rule' => $asset->hasAttribute('depreciation_start_rule')
                ? $asset->depreciation_start_rule : null,
            'receive_date' => $asset->receive_date,
            'disposal_date' => $disposalDate,
        ], $profileValues);
    }

    /**
     * รวมค่าจาก "snapshot บนทรัพย์สิน" กับ "เกณฑ์ที่ผูกไว้" เป็นพารามิเตอร์คำนวณ (pure — unit test ได้)
     *
     * ลำดับความสำคัญของแต่ละค่า:
     *   อายุ (เดือน)   : snapshot useful_life_months → เกณฑ์ → useful_life (ปี) × 12
     *                    (snapshot มาก่อนเสมอ = "ตรึงเกณฑ์ ณ วันขึ้นทะเบียน";
     *                     เกณฑ์มาก่อนคอลัมน์ปีเดิม เพราะคอลัมน์ปีเป็นข้อมูลเก่าที่ไม่รู้ที่มา)
     *   มูลค่าซาก      : snapshot residual_value → เกณฑ์ (แปลงตามชนิด) → 1 บาทตามมาตรฐานราชการ
     *   วันเริ่มคิด    : snapshot depreciation_start_date → วันรับเข้า ปรับด้วย start_rule ของเกณฑ์
     *   อัตรา/ฐาน/ปัดเศษ: เกณฑ์เป็นหลัก
     *
     * @param array $asset ค่าจากคอลัมน์ของ asset
     * @param array|null $profile ค่าจากเกณฑ์ที่ resolve ได้ (null = ไม่ได้ผูกเกณฑ์)
     */
    public static function mergeParams(array $asset, ?array $profile = null): array
    {
        $cost = (float) ($asset['price'] ?? 0);
        $p = $profile ?? [];

        // ---- อายุการใช้งาน (เดือน) ----
        $lifeMonths = 0;
        foreach ([
            $asset['useful_life_months'] ?? null,
            $p['useful_life_months'] ?? null,
            !empty($asset['useful_life']) ? ((int) $asset['useful_life'] * 12) : null,
        ] as $candidate) {
            if ($candidate !== null && $candidate !== '' && (int) $candidate > 0) {
                $lifeMonths = (int) $candidate;
                break;
            }
        }

        // ---- มูลค่าซาก (เป็นจำนวนเงินเสมอ) ----
        if (($asset['residual_value'] ?? null) !== null && $asset['residual_value'] !== '') {
            $salvage = (float) $asset['residual_value'];
        } elseif ($profile !== null) {
            $salvage = DepreciationCalculator::resolveSalvage(
                $cost,
                (string) ($p['salvage_value_type'] ?? DepreciationProfile::SALVAGE_AMOUNT),
                (float) ($p['salvage_value'] ?? 0)
            );
        } else {
            $salvage = self::DEFAULT_SALVAGE_BAHT;
        }

        // ---- วันเริ่มคิดค่าเสื่อม ----
        // มี snapshot แล้ว = resolve ตาม start_rule ไปแล้ว ห้ามปรับซ้ำ
        $snapStart = $asset['depreciation_start_date'] ?? null;
        if ($snapStart !== null && $snapStart !== '') {
            $startDate = $snapStart;
            $startRule = DepreciationProfile::START_READY_DATE;
        } else {
            $startDate = $asset['receive_date'] ?? null;
            $startRule = (string) (($asset['depreciation_start_rule'] ?? null)
                ?: ($p['start_rule'] ?? DepreciationProfile::START_READY_DATE));
        }

        $tiers = is_array($p['rate_tiers'] ?? null) ? $p['rate_tiers'] : [];

        // ---- อัตราต่อปี: ใช้เมื่อไม่มีช่วงอัตราและไม่มีอายุ ----
        $annualRate = null;
        if (empty($tiers) && $lifeMonths <= 0) {
            foreach ([$asset['depreciation_rate'] ?? null, $p['annual_rate'] ?? null] as $candidate) {
                if ($candidate !== null && $candidate !== '' && (float) $candidate > 0) {
                    $annualRate = (float) $candidate;
                    break;
                }
            }
        }

        return DepreciationCalculator::params([
            'cost' => $cost,
            'disposal_date' => $asset['disposal_date'] ?? null,
            'salvage_value' => $salvage,
            'salvage_value_type' => DepreciationProfile::SALVAGE_AMOUNT, // แปลงเป็นจำนวนเงินแล้ว
            'method' => ($asset['depreciation_method'] ?? null)
                ?: ($p['method'] ?? DepreciationProfile::METHOD_STRAIGHT_LINE),
            'useful_life_months' => $lifeMonths,
            'annual_rate' => $annualRate,
            'calculation_basis' => ($asset['depreciation_calculation_basis'] ?? null)
                ?: ($p['calculation_basis'] ?? DepreciationProfile::BASIS_MONTHLY),
            'start_rule' => $startRule,
            'rounding_scale' => isset($p['rounding_scale']) ? (int) $p['rounding_scale'] : 2,
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
        $disposalDate = $this->disposalDates()[(int) $asset->id] ?? null;
        $params = self::paramsForAsset($asset, $resolved['profile'] ?? null, $disposalDate);
        $schedule = DepreciationCalculator::buildMonthlySchedule($params);
        $schedule['resolved'] = $resolved;
        $schedule['params'] = $params;
        return $schedule;
    }

    /**
     * query ทรัพย์สินที่มีสิทธิ์คิดค่าเสื่อม
     *
     * เกณฑ์:
     *   - ยังไม่ถูกลบ · มีราคาทุน > 0 · มีวันรับเข้า
     *   - ไม่ใช่ทรัพย์สินที่จำหน่ายแล้ว (lifecycle_status = disposed)
     *   - อยู่ในกลุ่มประเภทที่คิดค่าเสื่อม (อาคาร/ครุภัณฑ์/สิ่งก่อสร้าง) — ตัดวัสดุและสินทรัพย์ไม่มีตัวตน
     *     หรือถูกกำหนดเกณฑ์รายชิ้นไว้แล้ว (override ระดับ asset)
     *
     * หมายเหตุ: ไม่บังคับว่าต้องมีอายุการใช้งานบนตัวทรัพย์สิน — อายุมาจากเกณฑ์ที่ผูกไว้ได้
     * (ชิ้นที่หาเกณฑ์ไม่เจอจริง ๆ จะถูกนับเป็น no_profile ตอนรัน)
     */
    public function eligibleQuery(): \yii\db\ActiveQuery
    {
        $typeCodes = DepreciationProfileResolver::depreciableTypeCodes();

        return Asset::find()
            ->andWhere(['deleted_at' => null])
            ->andWhere(['>', 'price', 0])
            ->andWhere(['not', ['receive_date' => null]])
            ->andWhere(['or',
                ['asset_type_id' => $typeCodes],
                ['and', ['not', ['depreciation_profile_id' => null]], ['>', 'depreciation_profile_id', 0]],
            ])
            ->orderBy(['id' => SORT_ASC]);
    }

    /**
     * วันจำหน่ายของทรัพย์สิน (จากใบจำหน่ายที่อนุมัติ/ดำเนินการแล้ว)
     *
     * ใช้หยุดคิดค่าเสื่อมหลังวันจำหน่าย — ทรัพย์สินที่จำหน่ายแล้วยังต้องคิดค่าเสื่อม
     * ของเดือนสุดท้ายจนถึงวันจำหน่าย จึงตัดออกจาก eligibleQuery ไม่ได้
     *
     * @return array<int,string> asset_id => Y-m-d
     */
    public function disposalDates(): array
    {
        try {
            if (Yii::$app->db->getTableSchema('{{%asset_disposal_items}}') === null) {
                return [];
            }
            $rows = (new \yii\db\Query())
                ->select(['i.asset_id', 'disposal_date' => 'MIN(d.disposal_date)'])
                ->from(['i' => '{{%asset_disposal_items}}'])
                ->innerJoin(['d' => '{{%asset_disposals}}'], 'd.id = i.disposal_id')
                ->where(['d.status' => [AssetDisposal::STATUS_APPROVED, AssetDisposal::STATUS_DONE]])
                ->andWhere(['not', ['i.asset_id' => null]])
                ->andWhere(['not', ['d.disposal_date' => null]])
                ->groupBy('i.asset_id')
                ->all();
        } catch (\Throwable $e) {
            return [];
        }

        $out = [];
        foreach ($rows as $r) {
            $out[(int) $r['asset_id']] = (string) $r['disposal_date'];
        }

        return $out;
    }

    /**
     * งวดเดือนก่อนหน้าในปีงบเดียวกันที่ยังไม่ได้คำนวณ — ต้องเดินงวดตามลำดับ
     * ไม่งั้นยอดสะสมในบัญชีจะข้ามเดือนโดยไม่มีใครสังเกต
     *
     * @return AccountingPeriod[]
     */
    public function pendingEarlierPeriods(AccountingPeriod $period): array
    {
        return AccountingPeriod::find()
            ->where([
                'period_type' => AccountingPeriod::TYPE_MONTH,
                'fiscal_year' => $period->fiscal_year,
                'status' => AccountingPeriod::STATUS_OPEN,
            ])
            ->andWhere(['<', 'start_date', $period->start_date])
            ->orderBy(['start_date' => SORT_ASC])
            ->all();
    }

    /**
     * ทรัพย์สินที่มีสิทธิ์คิดค่าเสื่อม — อ่านเป็นชุด (batch) กันหน่วยความจำบานเมื่อทะเบียนใหญ่
     *
     * @return iterable<Asset>
     */
    public function eligibleAssets(int $batchSize = 200): iterable
    {
        foreach ($this->eligibleQuery()->each($batchSize) as $asset) {
            yield $asset;
        }
    }

    /**
     * ประมวลผลค่าเสื่อมของงวด (เฉพาะงวดชนิด month)
     *
     * @param bool $save true=บันทึกลง DB, false=preview เท่านั้น
     * @return array{success:bool, message:string, period:string, created:int, updated:int, skipped:int, rows:array, excluded:array}
     */
    public function runForPeriod(AccountingPeriod $period, bool $save = true, ?int $userId = null): array
    {
        $base = [
            'success' => false, 'message' => '', 'period' => $period->name,
            'created' => 0, 'updated' => 0, 'skipped' => 0, 'rows' => [],
            // เหตุผลที่ทรัพย์สินไม่เข้างวดนี้ — ให้หน้าตรวจผลอธิบายได้ว่าทำไมยอดน้อยกว่าที่คาด
            'excluded' => ['no_profile' => 0, 'cannot_calculate' => 0, 'out_of_period' => 0, 'disposed_no_date' => 0],
        ];

        if ($period->period_type !== AccountingPeriod::TYPE_MONTH) {
            $base['message'] = 'คำนวณได้เฉพาะงวดรายเดือน (ไตรมาส/ปีให้รวมจากรายเดือน)';
            return $base;
        }
        if ($period->isClosed()) {
            $base['message'] = 'งวดนี้ปิดแล้ว (posted/locked) ห้ามคำนวณทับ';
            return $base;
        }

        // บันทึกจริงต้องเดินงวดตามลำดับ — กันคำนวณเดือน มี.ค. ทั้งที่ ม.ค.-ก.พ. ยังไม่ได้ทำ
        if ($save) {
            $pending = $this->pendingEarlierPeriods($period);
            if ($pending) {
                $names = implode(', ', array_map(static fn($p) => $p->name, array_slice($pending, 0, 3)));
                $more = count($pending) > 3 ? ' และอีก ' . (count($pending) - 3) . ' งวด' : '';
                $base['message'] = "ต้องคำนวณงวดก่อนหน้าให้เสร็จก่อน: {$names}{$more}";
                return $base;
            }
        }

        $periodYear = (int) date('Y', strtotime($period->start_date));
        $periodMonth = (int) date('n', strtotime($period->start_date));
        $resolver = new DepreciationProfileResolver();
        $disposals = $this->disposalDates();

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $rows = [];
        $excluded = ['no_profile' => 0, 'cannot_calculate' => 0, 'out_of_period' => 0, 'disposed_no_date' => 0];

        $tx = $save ? Yii::$app->db->beginTransaction() : null;
        try {
            foreach ($this->eligibleAssets() as $asset) {
                $disposalDate = $disposals[(int) $asset->id] ?? null;
                // ทำเครื่องหมายว่าจำหน่ายแล้วแต่ไม่มีใบจำหน่ายที่ระบุวันที่ — ไม่เดาวันให้
                // (นับแยกไว้ให้เห็น ดีกว่าคิดค่าเสื่อมต่อไปเงียบ ๆ)
                if ($disposalDate === null && $asset->lifecycle_status === Asset::LIFECYCLE_DISPOSED) {
                    $excluded['disposed_no_date']++;
                    continue;
                }

                $resolved = $resolver->resolve($asset);
                $profile = $resolved['profile'] ?? null;
                if ($profile === null && empty($asset->useful_life_months) && empty($asset->useful_life)) {
                    $excluded['no_profile']++;
                    continue;
                }
                $params = self::paramsForAsset($asset, $profile, $disposalDate);
                $sch = DepreciationCalculator::buildMonthlySchedule($params);
                if (!$sch['can_calculate']) {
                    $excluded['cannot_calculate']++;
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
                    $excluded['out_of_period']++;
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
        $base['excluded'] = $excluded;
        $base['message'] = ($save ? 'ประมวลผล' : 'ทดลองคำนวณ') . "งวด {$period->name}: สร้าง {$created} ปรับ {$updated} ข้าม {$skipped} รายการ";
        if ($excluded['no_profile'] > 0) {
            $base['message'] .= " · ยังไม่ได้ผูกเกณฑ์ {$excluded['no_profile']} รายการ";
        }
        if ($excluded['disposed_no_date'] > 0) {
            $base['message'] .= " · จำหน่ายแล้วแต่ไม่มีวันจำหน่าย {$excluded['disposed_no_date']} รายการ";
        }
        return $base;
    }
}
