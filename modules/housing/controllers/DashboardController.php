<?php

declare(strict_types=1);

namespace app\modules\housing\controllers;

use app\modules\housing\models\Building;
use app\modules\housing\models\Occupancy;
use app\modules\housing\services\HousingAccessService;
use app\modules\housing\models\Unit;
use yii\db\Expression;

final class DashboardController extends BaseController
{
    public function actionIndex(?int $building_id = null, ?string $status = null, ?string $q = null)
    {
        $query = Building::find()
            ->with(['floors.units.rooms', 'units.rooms'])
            ->where(['or',
                ['housing_building.status' => Building::STATUS_ACTIVE],
                ['housing_building.building_type' => Building::TYPE_HOUSE],
            ])
            ->orderBy(new Expression("CASE WHEN housing_building.building_type = 'house' THEN 0 ELSE 1 END"))
            ->addOrderBy([
                'housing_building.sort_order' => SORT_ASC,
                'housing_building.name' => SORT_ASC,
            ]);

        if ($building_id) {
            $query->andWhere(['housing_building.id' => $building_id]);
        }

        $buildings = $query->all();
        $unitQuery = Unit::find();
        if ($building_id) {
            $unitQuery->andWhere(['building_id' => $building_id]);
        }
        if ($status) {
            $unitQuery->andWhere(['status' => $status]);
        }
        if ($q) {
            $unitQuery->andWhere(['or',
                ['like', 'code', $q],
                ['like', 'name', $q],
            ]);
        }

        $visibleUnitIds = array_map('intval', $unitQuery->select('id')->column());
        $occupants = [];
        if ($visibleUnitIds !== []) {
            $occupancies = Occupancy::find()
                ->with(['employee', 'room'])
                ->where([
                    'unit_id' => $visibleUnitIds,
                    'status' => [Occupancy::STATUS_ALLOCATED, Occupancy::STATUS_ACTIVE],
                ])
                ->orderBy(['start_date' => SORT_ASC, 'id' => SORT_ASC])
                ->all();
            foreach ($occupancies as $occupancy) {
                $occupants[(int) $occupancy->unit_id][(int) ($occupancy->room_id ?? 0)][] = $occupancy;
            }
        }
        $countQuery = Unit::find()
            ->select(['status', 'total' => new Expression('COUNT(*)')])
            ->groupBy('status')
            ->indexBy('status')
            ->asArray();
        if ($building_id) {
            $countQuery->andWhere(['building_id' => $building_id]);
        }
        $counts = $countQuery->all();
        $eligibleEmployeeIds = HousingAccessService::eligibleEmployeeIds();
        $responsibleAttentionCount = (int) Building::find()
            ->where($eligibleEmployeeIds === []
                ? []
                : ['or',
                    ['responsible_employee_id' => null],
                    ['not in', 'responsible_employee_id', $eligibleEmployeeIds],
                ])
            ->count();

        return $this->render('index', [
            'buildings' => $buildings,
            'buildingOptions' => Building::find()
                ->select('name')
                ->orderBy(new Expression("CASE WHEN building_type = 'house' THEN 0 ELSE 1 END"))
                ->addOrderBy([
                    'sort_order' => SORT_ASC,
                    'name' => SORT_ASC,
                ])
                ->indexBy('id')
                ->column(),
            'visibleUnitIds' => $visibleUnitIds,
            'occupants' => $occupants,
            'counts' => $counts,
            'filters' => compact('building_id', 'status', 'q'),
            'responsibleAttentionCount' => $responsibleAttentionCount,
        ]);
    }
}
