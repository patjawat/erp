<?php

declare(strict_types=1);

namespace app\modules\housing\services;

use app\modules\housing\models\Checkout;
use app\modules\housing\models\Occupancy;
use app\modules\housing\models\Room;
use app\modules\housing\models\Unit;

final class UnitStatusService
{
    public function refresh(int $unitId): void
    {
        $unit = Unit::findOne($unitId);
        if (!$unit || in_array($unit->status, [Unit::STATUS_MAINTENANCE, Unit::STATUS_INACTIVE], true)) {
            return;
        }

        $hasActive = Occupancy::find()->where(['unit_id' => $unitId, 'status' => Occupancy::STATUS_ACTIVE])->exists();
        $hasAllocated = Occupancy::find()->where(['unit_id' => $unitId, 'status' => Occupancy::STATUS_ALLOCATED])->exists();
        $hasMoveOut = Checkout::find()->joinWith('occupancy')->where([
            'housing_occupancy.unit_id' => $unitId,
            'housing_checkout.status' => [Checkout::STATUS_REQUESTED, Checkout::STATUS_INSPECTION, Checkout::STATUS_AWAITING_STAFF],
        ])->exists();
        $unit->updateAttributes([
            'status' => $hasMoveOut ? Unit::STATUS_MOVE_OUT : ($hasActive ? Unit::STATUS_OCCUPIED : ($hasAllocated ? Unit::STATUS_RESERVED : Unit::STATUS_VACANT)),
        ]);

        foreach ($unit->rooms as $room) {
            if (in_array($room->status, [Unit::STATUS_MAINTENANCE, Unit::STATUS_INACTIVE], true)) {
                continue;
            }
            $roomHasActive = Occupancy::find()->where([
                'unit_id' => $unitId,
                'room_id' => $room->id,
                'status' => Occupancy::STATUS_ACTIVE,
            ])->exists();
            $roomHasAllocated = Occupancy::find()->where([
                'unit_id' => $unitId,
                'room_id' => $room->id,
                'status' => Occupancy::STATUS_ALLOCATED,
            ])->exists();
            $roomHasMoveOut = Checkout::find()->joinWith('occupancy')->where([
                'housing_occupancy.unit_id' => $unitId,
                'housing_occupancy.room_id' => $room->id,
                'housing_checkout.status' => [Checkout::STATUS_REQUESTED, Checkout::STATUS_INSPECTION, Checkout::STATUS_AWAITING_STAFF],
            ])->exists();
            $room->updateAttributes([
                'status' => $roomHasMoveOut ? Unit::STATUS_MOVE_OUT : ($roomHasActive ? Unit::STATUS_OCCUPIED : ($roomHasAllocated ? Unit::STATUS_RESERVED : Unit::STATUS_VACANT)),
            ]);
        }
    }
}
