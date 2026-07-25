<?php

declare(strict_types=1);

namespace app\modules\housing\services;

use app\modules\housing\models\HousingRequest;
use app\modules\housing\models\Occupancy;
use app\modules\hr\models\Employees;

final class HousingContextService
{
    public function forUser(int $userId): array
    {
        $employee = Employees::findOne(['user_id' => $userId]);
        if (!$employee) {
            return ['mode' => 'unavailable', 'employee' => null, 'occupancy' => null, 'request' => null];
        }
        $occupancy = Occupancy::find()
            ->where(['emp_id' => $employee->id, 'status' => [Occupancy::STATUS_ALLOCATED, Occupancy::STATUS_ACTIVE]])
            ->orderBy(['id' => SORT_DESC])
            ->one();
        if ($occupancy) {
            return [
                'mode' => $occupancy->status === Occupancy::STATUS_ACTIVE ? 'resident' : 'allocated',
                'employee' => $employee,
                'occupancy' => $occupancy,
                'request' => null,
            ];
        }
        $request = HousingRequest::find()
            ->where(['emp_id' => $employee->id])
            ->andWhere(['not in', 'status', [
                HousingRequest::STATUS_REJECTED,
                HousingRequest::STATUS_COMPLETED,
                HousingRequest::STATUS_CANCELLED,
            ]])
            ->orderBy(['id' => SORT_DESC])
            ->one();
        return [
            'mode' => $request ? 'request' : 'applicant',
            'employee' => $employee,
            'occupancy' => null,
            'request' => $request,
        ];
    }
}
