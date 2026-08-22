<?php

namespace app\modules\am\services;

use Yii;
use app\modules\am\models\Asset;

/**
 * ตรึงเกณฑ์ค่าเสื่อม (snapshot) ลงบนทรัพย์สิน
 *
 * ทำไมต้อง snapshot:
 *   เกณฑ์ที่ผูกไว้ที่ประเภท/หมวด/รายการ อาจถูกแก้ภายหลัง แต่ค่าเสื่อมของทรัพย์สินแต่ละชิ้น
 *   ต้องยึดเกณฑ์ ณ วันขึ้นทะเบียน จึงคัดลอกค่าจากเกณฑ์มาเก็บบนแถว asset ครั้งเดียว
 *
 * ใช้ 2 ทาง:
 *   1. อัตโนมัติตอนบันทึกทรัพย์สิน — Asset::beforeSave() เรียก applyTo()
 *   2. ย้อนหลังกับทะเบียนเดิม — backfill() (มีโหมดทดลอง ไม่บันทึกจริง)
 *
 * หลักการ: ไม่ทับ snapshot เดิม เว้นแต่สั่ง $force (ใช้ตอนผูกเกณฑ์ใหม่แล้วอยากรีเฟรช)
 */
class DepreciationSnapshotService
{
    /** คอลัมน์ snapshot ทั้งหมดบน asset */
    public const SNAPSHOT_ATTRIBUTES = [
        'depreciation_profile_id',
        'depreciation_method',
        'useful_life_months',
        'depreciation_rate',
        'residual_value',
        'depreciation_start_date',
        'depreciation_end_date',
        'depreciation_source_type',
        'depreciation_source_id',
    ];

    /** @var DepreciationProfileResolver */
    private $resolver;

    public function __construct(?DepreciationProfileResolver $resolver = null)
    {
        $this->resolver = $resolver ?: new DepreciationProfileResolver();
    }

    /**
     * ทรัพย์สินชิ้นนี้มี snapshot แล้วหรือยัง
     */
    public static function hasSnapshot(Asset $asset): bool
    {
        return !empty($asset->depreciation_profile_id);
    }

    /**
     * เขียนค่าจากเกณฑ์ที่ resolve ได้ลงบน attribute ของทรัพย์สิน (ยังไม่ save)
     *
     * @param bool $force true = ทับ snapshot เดิม
     * @return array{applied:bool, reason:string, profile_id:?int, source_type:?string, snapshot:array}
     */
    public function applyTo(Asset $asset, bool $force = false): array
    {
        $none = ['applied' => false, 'profile_id' => null, 'source_type' => null, 'snapshot' => []];

        if (!$force && self::hasSnapshot($asset)) {
            return $none + ['reason' => 'already_snapshotted'];
        }

        $resolved = $this->resolver->resolve($asset);
        $profile = $resolved['profile'] ?? null;
        if ($profile === null) {
            // ผูกเกณฑ์ไว้แต่เกณฑ์ถูกปิดใช้งาน จะได้ profile = null ทั้งที่ profile_id มีค่า
            return $none + ['reason' => empty($resolved['profile_id']) ? 'no_binding' : 'profile_inactive'];
        }

        $acquisition = $asset->depreciation_start_date ?: $asset->receive_date;
        if (empty($acquisition)) {
            return $none + ['reason' => 'no_acquisition_date'];
        }

        $snapshot = DepreciationProfileResolver::buildSnapshot(
            $profile,
            (string) $resolved['source_type'],
            $resolved['source_id'] !== null ? (int) $resolved['source_id'] : null,
            $acquisition,
            (float) $asset->price
        );

        foreach ($snapshot as $attr => $value) {
            if (!$asset->hasAttribute($attr)) {
                continue;
            }
            // เกณฑ์แบบคิดตามอายุจะไม่มีอัตราต่อปี — อย่าล้างอัตราเดิมของทรัพย์สินทิ้ง
            // (คอลัมน์นี้ยังถูกใช้โดยรายงาน/หน้าจอชุดเดิม)
            if ($attr === 'depreciation_rate' && $value === null) {
                continue;
            }
            $asset->{$attr} = $value;
        }
        if ($asset->hasAttribute('depreciation_status') && empty($asset->depreciation_status)) {
            $asset->depreciation_status = 'active';
        }
        // เติมอายุเป็นปีให้หน้าจอ/รายงานเดิมที่ยังอ่านคอลัมน์นี้ (ไม่ทับค่าที่กรอกไว้แล้ว)
        if ($asset->hasAttribute('useful_life') && empty($asset->useful_life) && !empty($snapshot['useful_life_months'])) {
            $asset->useful_life = (int) round((int) $snapshot['useful_life_months'] / 12);
        }

        return [
            'applied' => true,
            'reason' => 'ok',
            'profile_id' => (int) $profile->id,
            'source_type' => $resolved['source_type'],
            'snapshot' => $snapshot,
        ];
    }

    /**
     * ตรึงเกณฑ์ย้อนหลังให้ทะเบียนเดิมทั้งหมด
     *
     * @param bool $save false = ทดลอง (dry-run) ไม่เขียน DB
     * @param bool $force true = ทับ snapshot เดิมด้วย
     * @return array{
     *   total:int, applied:int,
     *   reasons:array<string,int>,
     *   by_profile:array<string,int>,
     *   samples:array<int,array>,
     *   saved:bool, error:?string
     * }
     */
    public function backfill(bool $save = false, bool $force = false, int $sampleLimit = 20): array
    {
        $run = new DepreciationRunService();
        $result = [
            'total' => 0,
            'applied' => 0,
            'reasons' => [],
            'by_profile' => [],
            'samples' => [],
            'saved' => $save,
            'error' => null,
        ];

        $tx = $save ? Yii::$app->db->beginTransaction() : null;
        try {
            foreach ($run->eligibleQuery()->each(200) as $asset) {
                /** @var Asset $asset */
                $result['total']++;
                $res = $this->applyTo($asset, $force);
                $reason = $res['reason'];
                $result['reasons'][$reason] = ($result['reasons'][$reason] ?? 0) + 1;

                if (!$res['applied']) {
                    continue;
                }
                $result['applied']++;

                $key = (string) $res['profile_id'];
                $result['by_profile'][$key] = ($result['by_profile'][$key] ?? 0) + 1;

                if (count($result['samples']) < $sampleLimit) {
                    $result['samples'][] = [
                        'id' => $asset->id,
                        'code' => $asset->code,
                        'name' => $asset->asset_name,
                        'price' => (float) $asset->price,
                        'profile_id' => $res['profile_id'],
                        'source_type' => $res['source_type'],
                        'useful_life_months' => $res['snapshot']['useful_life_months'] ?? null,
                        'residual_value' => $res['snapshot']['residual_value'] ?? null,
                        'start_date' => $res['snapshot']['depreciation_start_date'] ?? null,
                    ];
                }

                if ($save) {
                    // updateAttributes: เขียนเฉพาะคอลัมน์ snapshot ไม่ปลุก beforeSave ของ Asset
                    // (กันกฎ legacy เช่นการ generate หมายเลขครุภัณฑ์ทำงานซ้ำกับทะเบียนเดิม)
                    $attrs = array_merge(self::SNAPSHOT_ATTRIBUTES, ['depreciation_status', 'useful_life']);
                    $asset->updateAttributes(array_values(array_filter(
                        $attrs,
                        static fn($a) => $asset->hasAttribute($a)
                    )));
                }
            }

            if ($tx) {
                $tx->commit();
            }
        } catch (\Throwable $e) {
            if ($tx) {
                $tx->rollBack();
            }
            $result['error'] = $e->getMessage();
            $result['saved'] = false;
        }

        return $result;
    }

    /** คำอธิบายภาษาไทยของเหตุผลที่ไม่ได้ตรึงเกณฑ์ */
    public static function reasonLabel(string $reason): string
    {
        return [
            'ok' => 'ตรึงเกณฑ์สำเร็จ',
            'already_snapshotted' => 'มีเกณฑ์อยู่แล้ว (ไม่ทับ)',
            'no_binding' => 'ยังไม่ได้ผูกเกณฑ์ให้ประเภท/หมวด/รายการ',
            'profile_inactive' => 'เกณฑ์ที่ผูกไว้ถูกปิดใช้งาน',
            'no_acquisition_date' => 'ไม่มีวันรับเข้า',
        ][$reason] ?? $reason;
    }
}
