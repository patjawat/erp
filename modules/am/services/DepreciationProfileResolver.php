<?php

namespace app\modules\am\services;

use app\models\Categorise;
use app\modules\am\models\Asset;
use app\modules\am\models\DepreciationProfile;

/**
 * หาเกณฑ์ค่าเสื่อมจากระดับที่เฉพาะเจาะจงที่สุด แล้วสร้าง snapshot ตอนขึ้นทะเบียน
 *
 * ลำดับความเฉพาะเจาะจง (มาก → น้อย):
 *   1. ทรัพย์สินรายชิ้น (asset)
 *   2. รายการ/ชนิดทรัพย์สิน (asset_item)
 *   3. หมวด (asset_category)
 *   4. ประเภทหลัก (asset_type)
 *
 * การผูกเกณฑ์กับแต่ละระดับ:
 *   - asset.depreciation_profile_id (คอลัมน์จริง)
 *   - categorise (item/category/type): เก็บ depreciation_profile_id ใน data_json (เพิ่มผ่านหน้าจอเฟส 2)
 *
 * ส่วน logic การเลือกระดับ (pickMostSpecific) แยกเป็น pure method เพื่อ unit test
 */
class DepreciationProfileResolver
{
    public const SOURCE_ASSET = 'asset';
    public const SOURCE_ITEM = 'asset_item';
    public const SOURCE_CATEGORY = 'asset_category';
    public const SOURCE_TYPE = 'asset_type';

    /** ลำดับความเฉพาะเจาะจงจากมากไปน้อย */
    public const PRECEDENCE = [
        self::SOURCE_ASSET,
        self::SOURCE_ITEM,
        self::SOURCE_CATEGORY,
        self::SOURCE_TYPE,
    ];

    /**
     * กลุ่มประเภทหลัก (categorise.group_id ของ asset_type) ที่คิดค่าเสื่อม
     * อาคาร (BLDG) + ครุภัณฑ์ (EQUIP) + สิ่งก่อสร้าง (STRUCT)
     * — ตัดวัสดุ (MATER) / สินทรัพย์ไม่มีตัวตน (INTAN) / ที่ดิน ออก
     */
    public const DEPRECIABLE_TYPE_GROUPS = ['BLDG', 'EQUIP', 'STRUCT'];

    /**
     * code ของ asset_type ทุกแถวที่อยู่ในกลุ่มที่คิดค่าเสื่อม (distinct — ข้อมูลจริงมีรหัสซ้ำหลายแถว)
     *
     * @return string[]
     */
    public static function depreciableTypeCodes(): array
    {
        return array_values(array_unique(array_filter(
            Categorise::find()
                ->select('code')->distinct()
                ->where(['name' => self::SOURCE_TYPE, 'group_id' => self::DEPRECIABLE_TYPE_GROUPS])
                ->column(),
            static fn($c) => $c !== null && $c !== ''
        )));
    }

    /**
     * เลือกระดับที่เฉพาะเจาะจงที่สุดที่มี profile_id
     *
     * @param array<string,int|null> $candidates map source_type => profile_id|null
     * @return array{source_type:string, profile_id:int}|null
     */
    public static function pickMostSpecific(array $candidates): ?array
    {
        foreach (self::PRECEDENCE as $sourceType) {
            if (!array_key_exists($sourceType, $candidates)) {
                continue;
            }
            $profileId = $candidates[$sourceType];
            if ($profileId !== null && (int) $profileId > 0) {
                return ['source_type' => $sourceType, 'profile_id' => (int) $profileId];
            }
        }
        return null;
    }

    /**
     * หาเกณฑ์ของทรัพย์สินจาก DB ตามลำดับความเฉพาะเจาะจง
     *
     * @return array{
     *   profile: DepreciationProfile|null,
     *   source_type: string|null,
     *   source_id: int|null,
     *   profile_id: int|null
     * }
     */
    public function resolve(Asset $asset): array
    {
        // asset อ้างอิงลำดับชั้นด้วย categorise.code + name (ตาม Asset::getAssetType/getAssetCategory)
        $itemRow = self::categoriseRow($asset->asset_item_id, self::CATEGORISE_NAME_ITEM);
        $catRow = self::categoriseRow($asset->asset_category_id, self::SOURCE_CATEGORY);
        $typeRow = self::categoriseRow($asset->asset_type_id, self::SOURCE_TYPE);

        $candidates = [
            self::SOURCE_ASSET => $asset->depreciation_profile_id ? (int) $asset->depreciation_profile_id : null,
            self::SOURCE_ITEM => self::profileIdFromRow($itemRow),
            self::SOURCE_CATEGORY => self::profileIdFromRow($catRow),
            self::SOURCE_TYPE => self::profileIdFromRow($typeRow),
        ];

        $sourceIds = [
            self::SOURCE_ASSET => (int) $asset->id,
            self::SOURCE_ITEM => $itemRow['id'] ?? null,
            self::SOURCE_CATEGORY => $catRow['id'] ?? null,
            self::SOURCE_TYPE => $typeRow['id'] ?? null,
        ];

        $picked = self::pickMostSpecific($candidates);
        if ($picked === null) {
            return ['profile' => null, 'source_type' => null, 'source_id' => null, 'profile_id' => null];
        }

        $profile = DepreciationProfile::findOne([
            'id' => $picked['profile_id'],
            'status' => DepreciationProfile::STATUS_ACTIVE,
        ]);

        return [
            'profile' => $profile,
            'source_type' => $picked['source_type'],
            'source_id' => $sourceIds[$picked['source_type']] ?? null,
            'profile_id' => $picked['profile_id'],
        ];
    }

    /**
     * สร้าง snapshot ของเกณฑ์เพื่อบันทึกลงทรัพย์สิน ณ วันขึ้นทะเบียน
     * (การแก้ profile ภายหลังจะไม่กระทบ snapshot นี้)
     *
     * @param float|null $cost ราคาทุน — ใช้แปลงมูลค่าซากชนิด "ร้อยละ" ให้เป็นจำนวนเงิน
     *                         (คอลัมน์ residual_value เก็บเป็นจำนวนเงินเสมอ)
     * @return array<string,mixed> คีย์ตรงกับคอลัมน์ snapshot ใน asset
     */
    public static function buildSnapshot(
        DepreciationProfile $profile,
        string $sourceType,
        ?int $sourceId,
        ?string $acquisitionDate,
        ?float $cost = null
    ): array {
        $startDate = DepreciationCalculator::resolveStartDate($acquisitionDate, $profile->start_rule);
        $endDate = null;
        if ($startDate && $profile->useful_life_months) {
            $ts = strtotime($startDate);
            // สิ้นสุด = เริ่ม + อายุ (เดือน) - 1 วัน (โดยประมาณ ปรับจริงตอนคำนวณงวด)
            $endDate = date('Y-m-d', strtotime('+' . (int) $profile->useful_life_months . ' month -1 day', $ts));
        }

        // มูลค่าซาก: แปลงตามชนิดของเกณฑ์ (จำนวนเงิน / ร้อยละของทุน / 1 บาท) ให้เป็นจำนวนเงินเสมอ
        $salvage = DepreciationCalculator::resolveSalvage(
            (float) ($cost ?? 0),
            (string) $profile->salvage_value_type,
            (float) $profile->salvage_value
        );

        return [
            'depreciation_profile_id' => $profile->id,
            'depreciation_method' => $profile->method,
            'useful_life_months' => $profile->useful_life_months,
            'depreciation_rate' => $profile->annual_rate,
            'residual_value' => round($salvage, 2), // salvage snapshot เป็นจำนวนเงิน (reuse คอลัมน์เดิม)
            'depreciation_start_date' => $startDate,
            'depreciation_end_date' => $endDate,
            'depreciation_calculation_basis' => $profile->calculation_basis,
            'depreciation_start_rule' => $profile->start_rule,
            'depreciation_source_type' => $sourceType,
            'depreciation_source_id' => $sourceId,
        ];
    }

    /** ชื่อ name ของ categorise สำหรับระดับรายการ (ข้อมูลจริงใช้ 'asset_item') */
    public const CATEGORISE_NAME_ITEM = 'asset_item';

    /**
     * หาแถว categorise จาก code + name (ตามลิงก์จริงของระบบ: asset.*_id = categorise.code)
     *
     * @param int|string|null $code ค่าที่เก็บใน asset (เป็น code)
     * @return array{id:int,data_json:mixed}|null
     */
    /** @var array<string,array|null> cache ภายใน request (runForPeriod เรียกซ้ำต่อ asset) */
    private static $rowCache = [];

    private static function categoriseRow($code, string $name): ?array
    {
        if ($code === null || $code === '') {
            return null;
        }
        $key = $name . '::' . $code;
        if (!array_key_exists($key, self::$rowCache)) {
            // ข้อมูลจริงเคยมีรหัสซ้ำหลายแถว (import ซ้ำ) — เรียงตาม id ให้ผลคงที่
            // และเลือกแถวที่ "ผูกเกณฑ์ไว้" ก่อน กันกรณีผูกไปคนละแถวกับที่หยิบมา
            $rows = Categorise::find()
                ->select(['id', 'data_json'])
                ->where(['code' => (string) $code, 'name' => $name])
                ->orderBy(['id' => SORT_ASC])
                ->asArray()
                ->all();

            $picked = null;
            foreach ($rows as $row) {
                if ($picked === null) {
                    $picked = $row;
                }
                if (self::profileIdFromRow($row) !== null) {
                    $picked = $row;
                    break;
                }
            }
            self::$rowCache[$key] = $picked;
        }
        return self::$rowCache[$key];
    }

    /**
     * decode data_json ให้เป็น array แบบทนทาน — รองรับกรณี double-encoded
     * (บางแถว categorise เก็บ JSON เป็นข้อความซ้อน JSON_TYPE=STRING)
     *
     * @param mixed $value
     */
    public static function decodeDataJson($value): array
    {
        $d = $value;
        for ($i = 0; $i < 3 && is_string($d); $i++) {
            $decoded = json_decode($d, true);
            if ($decoded === null) {
                return [];
            }
            $d = $decoded;
        }
        return is_array($d) ? $d : [];
    }

    /**
     * อ่าน depreciation_profile_id ที่ผูกไว้ใน data_json ของแถว categorise
     */
    private static function profileIdFromRow(?array $row): ?int
    {
        if (!$row) {
            return null;
        }
        $dj = self::decodeDataJson($row['data_json']);
        if (!empty($dj['depreciation_profile_id']) && (int) $dj['depreciation_profile_id'] > 0) {
            return (int) $dj['depreciation_profile_id'];
        }
        return null;
    }
}
