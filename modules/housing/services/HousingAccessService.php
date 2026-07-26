<?php

declare(strict_types=1);

namespace app\modules\housing\services;

use app\modules\hr\models\Employees;
use yii\db\Query;

final class HousingAccessService
{
    private const RESPONSIBLE_PERMISSIONS = [
        'housing.staff',
        'housing.admin',
    ];

    private static ?array $eligibleUserIds = null;
    private static ?array $eligibleEmployeeIds = null;

    public static function eligibleUserIds(): array
    {
        if (self::$eligibleUserIds !== null) {
            return self::$eligibleUserIds;
        }

        $accessItems = array_fill_keys(self::RESPONSIBLE_PERMISSIONS, true);
        $relations = (new Query())
            ->select(['parent', 'child'])
            ->from('{{%auth_item_child}}')
            ->all();

        do {
            $changed = false;
            foreach ($relations as $relation) {
                if (isset($accessItems[$relation['child']]) && !isset($accessItems[$relation['parent']])) {
                    $accessItems[$relation['parent']] = true;
                    $changed = true;
                }
            }
        } while ($changed);

        self::$eligibleUserIds = array_map(
            'intval',
            (new Query())
                ->select('user_id')
                ->distinct()
                ->from('{{%auth_assignment}}')
                ->where(['item_name' => array_keys($accessItems)])
                ->column()
        );

        return self::$eligibleUserIds;
    }

    public static function eligibleEmployeeIds(): array
    {
        if (self::$eligibleEmployeeIds !== null) {
            return self::$eligibleEmployeeIds;
        }

        $userIds = self::eligibleUserIds();
        if ($userIds === []) {
            return self::$eligibleEmployeeIds = [];
        }

        self::$eligibleEmployeeIds = array_map(
            'intval',
            Employees::find()
                ->select('id')
                ->where(['status' => '1', 'user_id' => $userIds])
                ->column()
        );

        return self::$eligibleEmployeeIds;
    }

    public static function canBeResponsible(?Employees $employee): bool
    {
        return $employee !== null
            && (string) $employee->status === '1'
            && in_array((int) $employee->id, self::eligibleEmployeeIds(), true);
    }
}
