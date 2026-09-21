<?php

namespace app\modules\ha12\services;

use app\modules\ha12\models\Ha12MrecAudit;
use app\modules\ha12\models\Ha12MrecItem;
use app\modules\ha12\models\Ha12MrecTemplate;
use Yii;

/**
 * ตรรกะกลางของการตรวจความสมบูรณ์เวชระเบียน (กิจกรรม 9)
 */
class Ha12MrecService
{
    public static function canManage(Ha12MrecAudit $a, ?int $userId, ?int $empUnitId): bool
    {
        if (Ha12ReviewService::isManager()) {
            return true;
        }
        if ($userId !== null && (int) $a->created_by === (int) $userId) {
            return true;
        }
        return $a->owner_unit_id
            && in_array((int) $a->owner_unit_id, Ha12ReviewService::unitScopeIds($empUnitId), true);
    }

    public static function canView(Ha12MrecAudit $a, ?int $userId, ?int $empUnitId): bool
    {
        return self::canManage($a, $userId, $empUnitId);
    }

    /** สร้างการตรวจพร้อม 12 หัวข้อมาตรฐาน (atomic) */
    public static function createWithScaffold(Ha12MrecAudit $audit): bool
    {
        $tx = Yii::$app->db->beginTransaction();
        try {
            if (!$audit->save()) {
                $tx->rollBack();
                return false;
            }
            $no = 0;
            foreach (Ha12MrecTemplate::items() as $name) {
                $item = new Ha12MrecItem([
                    'audit_id' => (int) $audit->id,
                    'item_no' => ++$no,
                    'item_name' => $name,
                    'sort' => $no,
                ]);
                $item->save(false);
            }
            $tx->commit();
            return true;
        } catch (\Throwable $e) {
            $tx->rollBack();
            Yii::error('Ha12MrecService::createWithScaffold ' . $e->getMessage(), 'ha12');
            return false;
        }
    }
}
