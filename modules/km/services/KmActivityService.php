<?php

namespace app\modules\km\services;

use app\components\AppHelper;
use app\modules\hr\models\Organization;
use app\modules\km\models\KmActivity;
use Yii;
use yii\db\Query;

/**
 * สิทธิ์และขอบเขตหน่วยงานของกิจกรรม KM
 *
 * โมเดลสิทธิ์: กิจกรรมเป็นของ "หน่วยงาน" (owner_unit_id = tree.id) แบบเดียวกับโมดูล task
 * - ทุกคนดูกิจกรรมที่เผยแพร่แล้วได้ (คลังความรู้ร่วม)
 * - ฉบับร่างเห็นเฉพาะคนในสายหน่วยเจ้าของ/ผู้สร้าง/admin
 * - แก้ไข/ลบได้เฉพาะผู้สร้าง หัวหน้าหน่วยเจ้าของ หรือ admin
 *
 * ตรรกะ nested-set / หัวหน้าหน่วย ยึดกติกาเดียวกับ TaskService (คัดมาไว้ในตัวเพื่อไม่ผูกข้ามโมดูล)
 */
class KmActivityService
{
    /**
     * id หน่วยงานในสายของหน่วยที่ระบุ (ตัวเอง + ลูกทุกชั้น) ผ่าน nested set ของ tree
     *
     * @return int[]
     */
    public static function unitScopeIds(?int $unitId): array
    {
        if (!$unitId) {
            return [];
        }

        $node = (new Query())
            ->select(['root', 'lft', 'rgt'])
            ->from(Organization::tableName())
            ->where(['id' => $unitId])
            ->one();

        if (!$node) {
            return [(int) $unitId];
        }

        $ids = (new Query())
            ->select('id')
            ->from(Organization::tableName())
            ->where(['root' => $node['root']])
            ->andWhere(['between', 'lft', (int) $node['lft'], (int) $node['rgt']])
            ->column();

        return array_map('intval', $ids ?: [(int) $unitId]);
    }

    /** เป็นหัวหน้า/รองหัวหน้าของหน่วยนี้ไหม (leader1/leader2 ใน tree.data_json) */
    public static function isUnitLeader(?int $unitId, ?int $empId): bool
    {
        if (!$unitId || !$empId) {
            return false;
        }

        $org = Organization::findOne((int) $unitId);
        if (!$org) {
            return false;
        }

        $dataJson = $org->data_json;
        if (is_string($dataJson)) {
            $decoded = json_decode($dataJson, true);
            $dataJson = is_array($decoded) ? $decoded : [];
        }
        if (!is_array($dataJson)) {
            $dataJson = [];
        }

        $leaders = [];
        foreach (['leader1', 'leader2'] as $key) {
            if (isset($dataJson[$key]) && is_numeric($dataJson[$key])) {
                $leaders[] = (int) $dataJson[$key];
            }
        }

        return in_array((int) $empId, $leaders, true);
    }

    /** แก้ไข/ลบกิจกรรมได้ไหม — ผู้สร้าง / หัวหน้าหน่วยเจ้าของ / admin */
    public static function canManage(KmActivity $activity, ?int $empId, ?int $userId): bool
    {
        if (self::isAdmin()) {
            return true;
        }
        if ($userId !== null && (int) $activity->created_by === (int) $userId) {
            return true;
        }
        return self::isUnitLeader((int) $activity->owner_unit_id, $empId);
    }

    /** ดูกิจกรรมนี้ได้ไหม — เผยแพร่แล้วทุกคนดูได้ ; ฉบับร่างเฉพาะสายหน่วย/ผู้สร้าง/admin */
    public static function canView(KmActivity $activity, ?int $empId, ?int $userId, ?int $empUnitId): bool
    {
        if ($activity->status === KmActivity::STATUS_PUBLISHED) {
            return true;
        }
        if (self::isAdmin()) {
            return true;
        }
        if ($userId !== null && (int) $activity->created_by === (int) $userId) {
            return true;
        }
        return in_array((int) $activity->owner_unit_id, self::unitScopeIds($empUnitId), true);
    }

    /**
     * รายการหน่วยงานทั้งหมด (active) เรียงตามลำดับหน้าตั้งค่าระบบ (ทะเบียน org_unit)
     * คืน [['id' => tree.id, 'name' => ชื่อ], ...] ใช้กับ dropdown ตัวกรอง
     *
     * ลำดับ = source DESC, sort ASC, name ASC (ตรงกับ settings/org-unit)
     * map ผ่าน org_unit.ref_id → tree.id ; ถ้าไม่มีทะเบียน fallback เป็นผัง tree (root, lft)
     */
    public static function orderedUnits(): array
    {
        return self::buildOrderedUnits(null);
    }

    /** หน่วยงานที่ผู้ใช้เลือกเป็นเจ้าภาพได้ (admin = ทุกหน่วย, คนทั่วไป = สายตัวเอง) เรียงตามหน้าตั้งค่า */
    public static function selectableUnits(?int $empUnitId): array
    {
        $scope = self::isAdmin() ? null : (self::unitScopeIds($empUnitId) ?: [-1]);
        return self::buildOrderedUnits($scope);
    }

    /**
     * @param int[]|null $scopeIds จำกัดเฉพาะ tree.id เหล่านี้ (null = ทุกหน่วย)
     * @return array<int, array{id:int,name:string}>
     */
    private static function buildOrderedUnits(?array $scopeIds): array
    {
        // ปีที่มีข้อมูลในทะเบียน: ปีงบปัจจุบัน ไม่มีก็ใช้ปีล่าสุดที่มี
        $year = (int) AppHelper::YearBudget();
        $exists = (new Query())->from('org_unit')->where(['thai_year' => $year])->exists();
        if (!$exists) {
            $year = (int) (new Query())->from('org_unit')->max('thai_year');
        }

        $units = [];
        if ($year) {
            $rows = (new Query())
                ->select(['ref_id', 'name'])
                ->from('org_unit')
                ->where(['thai_year' => $year, 'active' => 1])
                ->andWhere(['not', ['ref_id' => null]])
                ->orderBy(['source' => SORT_DESC, 'sort' => SORT_ASC, 'name' => SORT_ASC])
                ->all();

            $seen = [];
            foreach ($rows as $r) {
                $treeId = (int) $r['ref_id'];
                if ($treeId <= 0 || isset($seen[$treeId])) {
                    continue;
                }
                if ($scopeIds !== null && !in_array($treeId, $scopeIds, true)) {
                    continue;
                }
                $seen[$treeId] = true;
                $units[] = ['id' => $treeId, 'name' => (string) $r['name']];
            }
        }

        if ($units) {
            return $units;
        }

        // fallback: ผังองค์กรจริง (tree) เรียงตาม root, lft
        $q = Organization::find()->where(['active' => 1])->orderBy(['root' => SORT_ASC, 'lft' => SORT_ASC]);
        if ($scopeIds !== null) {
            $q->andWhere(['id' => $scopeIds]);
        }
        return array_map(
            static fn ($o): array => ['id' => (int) $o->id, 'name' => (string) $o->name],
            $q->all()
        );
    }

    public static function isAdmin(): bool
    {
        try {
            return Yii::$app->user->can('admin');
        } catch (\Throwable $e) {
            return false;
        }
    }
}
