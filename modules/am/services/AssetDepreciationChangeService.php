<?php

namespace app\modules\am\services;

use Yii;
use app\modules\am\models\Asset;
use app\modules\am\models\DepreciationProfile;
use app\modules\am\models\AssetDepreciationChange;

/**
 * เปลี่ยนเกณฑ์ค่าเสื่อมของทรัพย์สินรายชิ้น + บันทึกประวัติ
 *
 * หลักการ:
 *   - อัปเดต snapshot บนทรัพย์สิน (source_type = asset — override ระดับรายชิ้น)
 *   - งวดที่ปิดแล้ว (posted/locked) คงค่าเดิม ไม่ถูกคำนวณใหม่ (runForPeriod ข้ามงวดปิดอยู่แล้ว)
 *   - งวดที่ยังไม่ปิดจะใช้เกณฑ์ใหม่เมื่อคำนวณครั้งถัดไป
 *   - ไม่คำนวณประวัติใหม่ทั้งหมด — ถ้าต้องปรับย้อนหลังใช้ scope with_adjustment แล้วสร้าง adjustment เอง
 *   - ใช้ transaction
 */
class AssetDepreciationChangeService
{
    /**
     * @param string $scope AssetDepreciationChange::SCOPE_*
     * @return array{success:bool, message:string}
     */
    public function changeProfile(
        Asset $asset,
        DepreciationProfile $newProfile,
        string $effectiveDate,
        string $scope,
        ?string $reason = null,
        ?string $documentReference = null,
        ?int $approvedBy = null,
        ?int $userId = null
    ): array {
        $tx = Yii::$app->db->beginTransaction();
        try {
            // เก็บค่าเดิมก่อนเปลี่ยน
            $old = [
                'profile_id' => $asset->depreciation_profile_id,
                'life_months' => $asset->useful_life_months,
                'rate' => $asset->depreciation_rate,
            ];

            // สร้าง snapshot ใหม่จากเกณฑ์ (source = asset)
            $acq = $asset->depreciation_start_date ?: $asset->receive_date;
            $snap = DepreciationProfileResolver::buildSnapshot(
                $newProfile,
                DepreciationProfileResolver::SOURCE_ASSET,
                (int) $asset->id,
                $acq
            );
            foreach ($snap as $attr => $val) {
                if ($asset->hasAttribute($attr)) {
                    $asset->{$attr} = $val;
                }
            }
            $asset->depreciation_status = 'active';
            if (!$asset->save(false)) {
                throw new \RuntimeException('อัปเดต snapshot ทรัพย์สินไม่สำเร็จ');
            }

            // บันทึกประวัติ
            $change = new AssetDepreciationChange();
            $change->asset_id = $asset->id;
            $change->old_depreciation_profile_id = $old['profile_id'];
            $change->new_depreciation_profile_id = $newProfile->id;
            $change->old_useful_life_months = $old['life_months'];
            $change->new_useful_life_months = $newProfile->useful_life_months;
            $change->old_rate = $old['rate'];
            $change->new_rate = $newProfile->annual_rate;
            $change->effective_date = $effectiveDate;
            $change->change_scope = $scope;
            $change->reason = $reason;
            $change->document_reference = $documentReference;
            $change->approved_by = $approvedBy;
            $change->created_by = $userId;
            if (!$change->save()) {
                throw new \RuntimeException('บันทึกประวัติไม่สำเร็จ: ' . json_encode($change->getErrors(), JSON_UNESCAPED_UNICODE));
            }

            $tx->commit();
            return ['success' => true, 'message' => 'เปลี่ยนเกณฑ์ค่าเสื่อมและบันทึกประวัติเรียบร้อย'];
        } catch (\Throwable $e) {
            $tx->rollBack();
            return ['success' => false, 'message' => 'เปลี่ยนเกณฑ์ไม่สำเร็จ: ' . $e->getMessage()];
        }
    }
}
