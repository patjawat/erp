<?php

namespace app\modules\roster\helpers;

use app\components\UserHelper;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;
use Yii;

/**
 * ใครทำอะไรกับตารางเวรได้บ้าง — สิทธิ์เกือบทั้งหมดมาจากผังองค์กร ไม่ใช่ RBAC
 *
 * หัวหน้าหอผู้ป่วยเป็น role `user` ธรรมดา ไม่มีสิทธิ์ hr/leave จึงต้องอ่านจาก
 * tree.data_json.leader1 (ไม่มีขีดล่าง — ใช้ Employees::ledOrganizations() ที่อ่านคีย์นี้ถูก)
 *
 * แยก 4 สิทธิ์ให้ชัด เพราะ 3 ชั้นมีขอบเขตต่างกัน:
 *
 *   จัดเวร (manage)   หัวหน้า "หน่วยนั้นเอง" เท่านั้น — ไม่รวมหน่วยลูก
 *                     หัวหน้ากลุ่มงานจึงแก้ตารางของหอลูกไม่ได้ ป้องกันการแก้งานลูกน้องโดยไม่ตั้งใจ
 *   ดู/ตรวจ (view)    ทั้งกิ่งใต้หน่วยที่ตัวเองเป็นหัวหน้า — หัวหน้ากลุ่มเห็นทุกหอในกลุ่ม
 *   ตรวจสอบ (review)  ต้องเป็นหัวหน้าของหน่วย "แม่" ของ unit นั้น (ไม่ใช่ตัวมันเอง)
 *                     กันไม่ให้หัวหน้าหน่วยตรวจงานตัวเอง
 *   อนุมัติ (approve) ผอ. เท่านั้น (categorise.site.director_name)
 */
class RosterAccess
{
    /** ดูได้ทุกหน่วยแบบอ่านอย่างเดียว (role กลาง) — ไม่ได้แปลว่าแก้หรืออนุมัติได้ */
    public static function isGlobalViewer(): bool
    {
        if (!Yii::$app->has('user')) {
            return false; // รันจาก console
        }
        $user = Yii::$app->user;
        return $user->can('roster') || $user->can('hr') || $user->can('admin');
    }

    /**
     * cache ต่อ request — เมนูบนแถบหลักถูกเรนเดอร์ทุกหน้า และกริดเรียกซ้ำหลายสิบครั้ง
     * ถ้าไม่ cache จะยิง query ซ้ำโดยไม่จำเป็น
     * @var array<string, mixed>
     */
    private static array $cache = [];

    /** ล้าง cache — ใช้เวลาสลับผู้ใช้ในสคริปต์/เทสต์ */
    public static function resetCache(): void
    {
        static::$cache = [];
    }

    private static function cacheKey(string $name): string
    {
        $uid = Yii::$app->has('user') && !Yii::$app->user->isGuest ? (string) Yii::$app->user->id : 'guest';
        return $name . ':' . $uid;
    }

    public static function currentEmployee(): ?Employees
    {
        if (!Yii::$app->has('user')) {
            return null; // รันจาก console
        }
        $key = static::cacheKey('emp');
        if (array_key_exists($key, static::$cache)) {
            return static::$cache[$key];
        }
        try {
            $emp = UserHelper::GetEmployee();
            $emp = $emp instanceof Employees ? $emp : null;
        } catch (\Throwable $e) {
            $emp = null;
        }
        return static::$cache[$key] = $emp;
    }

    /** @return Organization[] หน่วยงานที่ผู้ใช้ปัจจุบันเป็นหัวหน้า */
    private static function ledOrganizations(): array
    {
        $key = static::cacheKey('led');
        if (array_key_exists($key, static::$cache)) {
            return static::$cache[$key];
        }
        $emp = static::currentEmployee();
        try {
            $led = $emp ? $emp->ledOrganizations() : [];
        } catch (\Throwable $e) {
            $led = [];
        }
        return static::$cache[$key] = $led;
    }

    /**
     * รหัสพนักงานของผู้อำนวยการ — อ่านจาก categorise.site.director_name โดยตรง
     *
     * ตั้งใจไม่เรียก SiteHelper::viewDirector() แม้จะเป็นแหล่งข้อมูลเดียวกัน เพราะตัวนั้น
     * โหลดโลโก้/ลายเซ็น/ที่อยู่มาด้วย แล้ว catch ทุก exception เป็นค่าว่าง
     * ถ้าไฟล์โลโก้หายหรือ alias ไม่พร้อม การ resolve จะเงียบๆ กลายเป็น null
     * แปลว่า "ไม่มีใครอนุมัติตารางเวรได้" ซึ่งอันตรายเกินไปสำหรับด่านสิทธิ์
     */
    /**
     * ค่าตั้งค่าเว็บจาก categorise.site — อ่านตรงจาก data_json
     * ไม่ผ่าน SiteHelper เพราะตัวนั้นพังถ้าโลโก้/ชื่อย่อยังไม่ได้ตั้ง
     */
    public static function siteSetting(string $key): ?string
    {
        $cacheKey = 'site:' . $key;
        if (array_key_exists($cacheKey, static::$cache)) {
            return static::$cache[$cacheKey];
        }
        try {
            $value = (new \yii\db\Query())
                ->select(new \yii\db\Expression('data_json->>' . Yii::$app->db->quoteValue('$.' . $key)))
                ->from('categorise')
                ->where(['name' => 'site'])
                ->scalar();
            $value = ($value === false || $value === null || $value === '') ? null : (string) $value;
        } catch (\Throwable $e) {
            $value = null;
        }
        return static::$cache[$cacheKey] = $value;
    }

    public static function directorEmpId(): ?int
    {
        try {
            $row = (new \yii\db\Query())
                ->select(new \yii\db\Expression("data_json->>'$.director_name'"))
                ->from('categorise')
                ->where(['name' => 'site'])
                ->scalar();
            $id = (int) $row;
            return $id > 0 ? $id : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    // ── จัดเวร: เฉพาะหน่วยที่ตัวเองเป็นหัวหน้าโดยตรง ────────────────────────

    /**
     * หน่วยที่ "จัดเวรได้" — เฉพาะหน่วยที่ตัวเองเป็น leader1 โดยตรง ไม่ขยายลงหน่วยลูก
     * @return int[]|null null = ได้ทุกหน่วย (admin เท่านั้น)
     */
    public static function manageableUnitIds(): ?array
    {
        if (Yii::$app->has('user') && Yii::$app->user->can('admin')) {
            return null;
        }
        $emp = static::currentEmployee();
        if (!$emp) {
            return [];
        }
        $ids = [];
        foreach (static::ledOrganizations() as $node) {
            $ids[] = (int) $node->id;
        }
        return array_values(array_unique($ids));
    }

    public static function canManageUnit(int $unitId): bool
    {
        $allowed = static::manageableUnitIds();
        return $allowed === null || in_array($unitId, $allowed, true);
    }

    // ── ดู/ตรวจ: ทั้งกิ่งใต้หน่วยที่ตัวเองเป็นหัวหน้า ────────────────────────

    /**
     * หน่วยที่ "ดูได้" — ทั้งกิ่งใต้ทุกหน่วยที่ตัวเองเป็นหัวหน้า
     * หัวหน้ากลุ่มงานการพยาบาลจึงเห็นทุกหอในกลุ่ม · หัวหน้า root เห็นทั้งโรงพยาบาล
     * @return int[]|null null = เห็นทุกหน่วย
     */
    public static function viewableUnitIds(): ?array
    {
        if (static::isGlobalViewer()) {
            return null;
        }
        $emp = static::currentEmployee();
        if (!$emp) {
            return [];
        }
        $ids = [];
        foreach (static::ledOrganizations() as $node) {
            $ids[] = (int) $node->id;
            foreach (static::descendantIds($node) as $childId) {
                $ids[] = $childId;
            }
        }
        return array_values(array_unique($ids));
    }

    public static function canViewUnit(int $unitId): bool
    {
        $allowed = static::viewableUnitIds();
        return $allowed === null || in_array($unitId, $allowed, true);
    }

    /** @return int[] รหัสหน่วยทั้งกิ่งใต้ node (รวมตัวมันเอง) */
    public static function descendantIds(Organization $node): array
    {
        try {
            return array_map('intval', Organization::find()
                ->select(['id'])
                ->where(['root' => $node->root])
                ->andWhere(['>=', 'lft', $node->lft])
                ->andWhere(['<=', 'rgt', $node->rgt])
                ->column());
        } catch (\Throwable $e) {
            return [(int) $node->id]; // nested set ผิดปกติ — ใช้เฉพาะตัวเอง
        }
    }

    // ── ตรวจสอบ: ต้องเป็นหัวหน้าของหน่วยแม่ ─────────────────────────────────

    /**
     * ตรวจสอบตารางเวรของหน่วยนี้ได้ไหม
     * เงื่อนไข: เป็นหัวหน้าของหน่วย "เหนือขึ้นไป" — ไม่นับหน่วยนั้นเอง (กันตรวจงานตัวเอง)
     * ผอ. ตรวจได้ทุกหน่วยอยู่แล้วเพราะเป็นหัวหน้า root
     */
    public static function canReviewUnit(int $unitId): bool
    {
        $emp = static::currentEmployee();
        if (!$emp) {
            return false;
        }
        $unit = Organization::findOne($unitId);
        if (!$unit) {
            return false;
        }
        foreach (static::ledOrganizations() as $node) {
            if ((int) $node->id === $unitId) {
                continue; // หัวหน้าหน่วยนั้นเอง — ไม่ใช่ผู้ตรวจ
            }
            if ((int) $node->root === (int) $unit->root
                && (int) $unit->lft > (int) $node->lft
                && (int) $unit->rgt < (int) $node->rgt) {
                return true;
            }
        }
        return false;
    }

    // ── อนุมัติ: ผอ. เท่านั้น ────────────────────────────────────────────────

    public static function canApprove(): bool
    {
        $emp = static::currentEmployee();
        $directorId = static::directorEmpId();
        if ($emp && $directorId && (int) $emp->id === $directorId) {
            return true;
        }
        return Yii::$app->has('user') && Yii::$app->user->can('admin');
    }

    /**
     * ผู้ตรวจกับผู้อนุมัติเป็นคนเดียวกันไหมสำหรับหน่วยนี้
     * เกิดกับหน่วยที่แขวนอยู่ใต้ root โดยตรง (ผอ. เป็นทั้งหัวหน้าแม่และผู้อนุมัติ)
     * ใช้ยุบปุ่ม "ตรวจแล้ว" กับ "อนุมัติ" เป็นปุ่มเดียว จะได้ไม่ต้องกดซ้ำสองรอบ
     */
    public static function reviewerIsApprover(int $unitId): bool
    {
        return static::canApprove() && static::canReviewUnit($unitId);
    }

    // ── ทั่วไป ───────────────────────────────────────────────────────────────

    /** เข้าโมดูลได้ไหม */
    public static function canEnter(): bool
    {
        if (static::isGlobalViewer() || static::canApprove()) {
            return true;
        }
        $units = static::viewableUnitIds();
        return $units === null || !empty($units);
    }

    /** เห็นหน้าภาพรวมผู้ตรวจสอบไหม — ต้องคุมมากกว่า 1 หน่วย หรือเป็นผู้ดู/ผอ. ระดับบน */
    public static function canSeeOverview(): bool
    {
        if (static::isGlobalViewer() || static::canApprove()) {
            return true;
        }
        $viewable = static::viewableUnitIds();
        $manageable = static::manageableUnitIds();
        return $viewable === null || count($viewable) > count($manageable ?? []);
    }

    /** หน่วยงานที่ "จัดเวรได้" (ตั้งค่าเวร/กฎ) — [id => name] */
    public static function unitOptions(): array
    {
        return static::optionsFor(static::manageableUnitIds());
    }

    /**
     * หน่วยงานที่ "สร้างรอบเวรให้ได้" — ทุกหน่วยในกิ่งที่ตัวเองดูแล
     * หัวหน้ากลุ่มงานจึงเปิดรอบแทนหอลูกได้เวลาหัวหน้าหอไม่อยู่ (แต่ยังแก้กริดเองไม่ได้)
     * คืนแบบจัดกลุ่มตามผังโครงสร้าง เหมือนที่ระบบบุคลากรใช้
     *
     * @return array [ชื่อราก => [id => "กลุ่มงาน › งาน"]]
     */
    public static function creatableUnitGroups(): array
    {
        $allowed = static::viewableUnitIds();
        $grouped = Organization::groupedByRoot();
        if ($allowed === null) {
            return $grouped;
        }
        $filtered = [];
        foreach ($grouped as $rootName => $items) {
            foreach ($items as $id => $label) {
                if (in_array((int) $id, $allowed, true)) {
                    $filtered[$rootName][$id] = $label;
                }
            }
        }
        return $filtered;
    }

    /** แบนราบเป็น [id => label] ไว้ตรวจสิทธิ์/นับจำนวน */
    public static function creatableUnitOptions(): array
    {
        $flat = [];
        foreach (static::creatableUnitGroups() as $items) {
            foreach ($items as $id => $label) {
                $flat[(int) $id] = $label;
            }
        }
        return $flat;
    }

    /** สร้างรอบเวรให้หน่วยนี้ได้ไหม */
    public static function canCreateForUnit(int $unitId): bool
    {
        return static::canViewUnit($unitId);
    }

    /** หน่วยงานที่ "ดูได้" สำหรับตัวกรองในหน้าภาพรวม — [id => name] */
    public static function viewableUnitOptions(): array
    {
        return static::optionsFor(static::viewableUnitIds());
    }

    /** @param int[]|null $allowed */
    private static function optionsFor(?array $allowed): array
    {
        $query = Organization::find()->where(['active' => 1])->orderBy(['lvl' => SORT_ASC, 'name' => SORT_ASC]);
        if ($allowed !== null) {
            if (empty($allowed)) {
                return [];
            }
            $query->andWhere(['id' => $allowed]);
        }
        $options = [];
        foreach ($query->all() as $node) {
            $options[(int) $node->id] = $node->name;
        }
        return $options;
    }
}
