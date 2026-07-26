<?php

declare(strict_types=1);

namespace app\modules\housing\services;

use app\modules\filemanager\components\FileManagerHelper;
use app\modules\filemanager\models\Uploads;
use Yii;

final class HousingUploadService
{
    public const SLOT_BUILDING_IMAGE = 'building_image';
    public const SLOT_LOCATION_PHOTO = 'housing_location_photo';
    public const SLOT_ASSET_PHOTO = 'housing_asset_photo';
    public const SLOT_REPAIR_BEFORE = 'housing_repair_before';
    public const SLOT_REPAIR_AFTER = 'housing_repair_after';

    public const MAX_REPAIR_PHOTOS_PER_SLOT = 10;

    public static function protectedSlots(): array
    {
        return [
            self::SLOT_BUILDING_IMAGE,
            self::SLOT_LOCATION_PHOTO,
            self::SLOT_ASSET_PHOTO,
            self::SLOT_REPAIR_BEFORE,
            self::SLOT_REPAIR_AFTER,
        ];
    }

    public static function isProtectedSlot(?string $slot): bool
    {
        return in_array($slot, self::protectedSlots(), true);
    }

    public static function exceedsLimit(int $existingCount, int $incomingCount, int $limit): bool
    {
        return $existingCount + $incomingCount > $limit;
    }

    public function countByRefAndSlot(string $ref, string $slot): int
    {
        return (int) Uploads::find()
            ->where(['ref' => $ref, 'name' => $slot])
            ->count();
    }

    public function findIdsByRefsAndSlots(array $refs, array $slots): array
    {
        $refs = array_values(array_unique(array_filter(array_map('strval', $refs))));
        $slots = array_values(array_unique(array_filter(array_map('strval', $slots))));
        if ($refs === [] || $slots === []) {
            return [];
        }

        return array_map(
            'intval',
            Uploads::find()
                ->select('id')
                ->where(['ref' => $refs, 'name' => $slots])
                ->column()
        );
    }

    /**
     * @return int[] IDs that could not be removed
     */
    public function deleteUploads(array $ids): array
    {
        $failedIds = [];
        foreach (array_values(array_unique(array_map('intval', $ids))) as $id) {
            if ($id <= 0 || Uploads::findOne($id) === null) {
                continue;
            }
            try {
                $deleted = FileManagerHelper::Deletefile($id);
            } catch (\Throwable $exception) {
                Yii::error($exception, __METHOD__);
                $deleted = false;
            }
            if (!$deleted && Uploads::findOne($id) !== null) {
                $failedIds[] = $id;
            }
        }

        return $failedIds;
    }
}
