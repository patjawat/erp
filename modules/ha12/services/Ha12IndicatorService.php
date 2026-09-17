<?php

namespace app\modules\ha12\services;

use app\modules\ha12\models\Ha12Indicator;

/**
 * ตรรกะกลางของตัวชี้วัด (กิจกรรม 12)
 */
class Ha12IndicatorService
{
    public static function canManage(Ha12Indicator $i, ?int $userId, ?int $empUnitId): bool
    {
        if (Ha12ReviewService::isManager()) {
            return true;
        }
        if ($userId !== null && (int) $i->created_by === (int) $userId) {
            return true;
        }
        return $i->owner_unit_id
            && in_array((int) $i->owner_unit_id, Ha12ReviewService::unitScopeIds($empUnitId), true);
    }

    public static function canView(Ha12Indicator $i, ?int $userId, ?int $empUnitId): bool
    {
        return self::canManage($i, $userId, $empUnitId);
    }
}
