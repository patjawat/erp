<?php

namespace app\modules\ha12\services;

use app\components\AppHelper;
use app\modules\ha12\models\Ha12Review;
use app\modules\ha12\models\Ha12ReviewVersion;
use app\modules\hr\models\Organization;
use Yii;
use yii\db\Query;

/**
 * สิทธิ์ + ตรรกะกลางของการทบทวน HA12
 *
 * โมเดลสิทธิ์ (เจ้าของ = หน่วยงาน):
 * - ผู้ดูแล/ทีมคุณภาพ (admin หรือ role 'ha12') → เห็น/จัดการทุกรายการ
 * - ผู้ใช้ทั่วไป → จัดการเฉพาะรายการของสายหน่วยตนเอง หรือที่ตนสร้าง
 *
 * ตรรกะ nested-set หน่วยงาน + ordered units ยึดกติกาเดียวกับ ComplaintService/KmActivityService
 */
class Ha12ReviewService
{
    /** ผู้ดูแล/ทีมคุณภาพ — เห็น/จัดการทุกรายการ */
    public static function isManager(): bool
    {
        try {
            return Yii::$app->user->can('admin') || Yii::$app->user->can('ha12');
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * id หน่วยงานในสายของหน่วยที่ระบุ (ตัวเอง + ลูกทุกชั้น) ผ่าน nested set ของ tree
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

    /** จัดการรายการนี้ได้ไหม — ผู้ดูแล / ผู้สร้าง / คนในสายหน่วยเจ้าของ */
    public static function canManage(Ha12Review $r, ?int $userId, ?int $empUnitId): bool
    {
        if (self::isManager()) {
            return true;
        }
        if ($userId !== null && (int) $r->created_by === (int) $userId) {
            return true;
        }
        return $r->owner_unit_id
            && in_array((int) $r->owner_unit_id, self::unitScopeIds($empUnitId), true);
    }

    /** ดูรายการนี้ได้ไหม (เฟส 1 = สิทธิ์เดียวกับ manage) */
    public static function canView(Ha12Review $r, ?int $userId, ?int $empUnitId): bool
    {
        return self::canManage($r, $userId, $empUnitId);
    }

    /**
     * บันทึกสแนปช็อตรุ่นปัจจุบันของรายการลงประวัติ (เรียกหลัง $review->save() สำเร็จ)
     * ใช้เลข revision ปัจจุบันของ review
     */
    public static function recordVersion(Ha12Review $review, string $action): void
    {
        $v = new Ha12ReviewVersion([
            'review_id' => (int) $review->id,
            'revision' => (int) $review->revision,
            'action' => $action,
            'review_date' => $review->review_date,
            'title' => $review->title,
            'reviewer_name' => $review->reviewer_name,
            'payload_json' => $review->payload_json,
            'schema_version' => (int) $review->schema_version,
            'deleted' => (int) $review->deleted,
        ]);
        $v->save(false);
    }

    // --- หน่วยงาน (reuse ทะเบียน org_unit เหมือน KM/Complaint) ---------------

    /** @return array<int,array{id:int,name:string}> ทุกหน่วย (ตัวกรอง) */
    public static function orderedUnits(): array
    {
        return self::buildOrderedUnits(null);
    }

    /** @return array<int,array{id:int,name:string}> หน่วยที่ผู้ใช้เลือกได้ (manager=ทุกหน่วย) */
    public static function selectableUnits(?int $empUnitId): array
    {
        $scope = self::isManager() ? null : (self::unitScopeIds($empUnitId) ?: [-1]);
        return self::buildOrderedUnits($scope);
    }

    /**
     * @param int[]|null $scopeIds จำกัดเฉพาะ tree.id เหล่านี้ (null = ทุกหน่วย)
     * @return array<int, array{id:int,name:string}>
     */
    private static function buildOrderedUnits(?array $scopeIds): array
    {
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

        $q = Organization::find()->where(['active' => 1])->orderBy(['root' => SORT_ASC, 'lft' => SORT_ASC]);
        if ($scopeIds !== null) {
            $q->andWhere(['id' => $scopeIds]);
        }
        return array_map(
            static fn ($o): array => ['id' => (int) $o->id, 'name' => (string) $o->name],
            $q->all()
        );
    }
}
