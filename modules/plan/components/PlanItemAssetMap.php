<?php

namespace app\modules\plan\components;

use app\models\Categorise;
use yii\helpers\ArrayHelper;

/**
 * จับคู่ "แผนงาน" (categorise plan_item) ↔ "ประเภทพัสดุ" (categorise asset_type) ที่ซื้อได้ภายใต้แผนงานนั้น
 * เก็บใน plan_item.data_json['asset_types'] = ['M1', ...] — ว่าง = ไม่จำกัดประเภท
 * ใช้ในงานจัดซื้อผูกแผน (PurchasePlanControl): กรองหน้าเลือกรายการ + ประเภทไม่ตรง = นอกแผน
 * แก้ไขที่ /plan/plan-item-asset (ผู้มีสิทธิ์ทำแผน)
 */
class PlanItemAssetMap
{
    /**
     * ค่าแนะนำ (เติมเฉพาะแผนงานที่ยังว่าง ผ่านปุ่มในหน้าตั้งค่า) — เฉพาะคู่ที่ชัดเจน
     * วัสดุ = กลับด้านจาก PlanOrder::ASSET_TYPE_TO_VASDU_ITEM ; ครุภัณฑ์ = ชื่อตรงกัน ; จ้างเหมา/ซ่อม = M25
     */
    const SUGGEST = [
        // 2.3 ค่าวัสดุ
        'P85' => ['M1'], 'P86' => ['M2'], 'P82' => ['M3'], 'P80' => ['M4'], 'P84' => ['M5'],
        'P87' => ['M6'], 'P79' => ['M7', 'M20'], 'P88' => ['M9'], 'P89' => ['M10'], 'P81' => ['M12'],
        'P83' => ['M18'], 'P91' => ['M19'], 'P90' => ['M22', 'M26'], 'P92' => ['M23'], 'P93' => ['M24'],
        'P78' => ['M8', 'M11', 'M13', 'M14', 'M15', 'M16', 'M17', 'M21'],
        // 3.1 ค่าครุภัณฑ์
        'P99' => ['OFF'], 'P100' => ['EDU'], 'P101' => ['VEH'], 'P102' => ['AGR'], 'P104' => ['ELE'],
        'P105' => ['ADV'], 'P106' => ['SCI', 'MED'], 'P107' => ['HOM'], 'P108' => ['COM'],
        // 3.2 ค่าที่ดินและสิ่งก่อสร้าง
        'P110' => ['1', '2.1', '2.2', '2.3', 'STR_GRP_CONCRETE', 'STR_GRP_TEMP', 'STR_GRP_WOOD'],
        // 2.5 ค่าใช้สอย — ซ่อมแซม/จ้างเหมา/จ้างตรวจ/ที่ปรึกษา
        'P43' => ['M25'], 'P44' => ['M25'], 'P45' => ['M25'], 'P46' => ['M25'], 'P47' => ['M25'],
        'P48' => ['M25'], 'P49' => ['M25'], 'P50' => ['M25'], 'P51' => ['M25'], 'P52' => ['M25'],
        'P53' => ['M25'], 'P54' => ['M25'], 'P55' => ['M25'], 'P56' => ['M25'], 'P57' => ['M25'],
        'P58' => ['M25'], 'P59' => ['M25'], 'P60' => ['M25'], 'P61' => ['M25'], 'P62' => ['M25'],
        'P63' => ['M25'], 'P64' => ['M25'], 'P65' => ['M25'], 'P68' => ['M25'], 'P124' => ['M25'],
    ];

    /** @var array<string,string[]>|null */
    private static $cache;

    /** ประเภทพัสดุที่แผนงานนี้ซื้อได้ ; [] = ไม่จำกัด */
    public static function typesFor($planItemCode): array
    {
        return self::all()[(string) $planItemCode] ?? [];
    }

    /** @return array<string,string[]> plan_item code => asset_type codes (เฉพาะที่กำหนดไว้) */
    public static function all(): array
    {
        if (self::$cache === null) {
            self::$cache = [];
            foreach (Categorise::find()->where(['name' => 'plan_item'])->all() as $item) {
                $types = self::json($item)['asset_types'] ?? [];
                if (is_array($types) && $types) {
                    self::$cache[(string) $item->code] = array_values(array_map('strval', $types));
                }
            }
        }
        return self::$cache;
    }

    public static function save($planItemCode, array $types): bool
    {
        $item = Categorise::findOne(['name' => 'plan_item', 'code' => (string) $planItemCode]);
        if (!$item) {
            return false;
        }
        $types = array_values(array_unique(array_filter(array_map('strval', $types), 'strlen')));
        $dj = self::json($item);
        if ($types) {
            $dj['asset_types'] = $types;
        } else {
            unset($dj['asset_types']);
        }
        $item->data_json = $dj;
        self::$cache = null;
        return $item->save(false);
    }

    /** เติมค่าแนะนำให้แผนงานที่ยังไม่ได้กำหนด (ไม่ทับค่าที่ตั้งไว้) — คืนจำนวนที่เติม */
    public static function applySuggestions(): int
    {
        $valid = array_flip(array_keys(self::assetTypeOptions()));
        $current = self::all();
        $n = 0;
        foreach (self::SUGGEST as $code => $types) {
            if (!empty($current[$code])) {
                continue;
            }
            $types = array_values(array_filter($types, fn($t) => isset($valid[$t])));
            if ($types && self::save($code, $types)) {
                $n++;
            }
        }
        return $n;
    }

    /** code => title ของประเภทพัสดุทั้งหมด */
    public static function assetTypeOptions(): array
    {
        return ArrayHelper::map(
            Categorise::find()->where(['name' => 'asset_type'])->orderBy(['code' => SORT_ASC])->all(),
            'code',
            fn($m) => $m->title . ' (' . $m->code . ')'
        );
    }

    private static function json($model): array
    {
        $dj = $model->data_json;
        if (!is_array($dj)) {
            $dj = json_decode((string) $dj, true) ?: [];
        }
        return $dj;
    }
}
