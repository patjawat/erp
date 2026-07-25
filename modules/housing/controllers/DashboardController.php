<?php

declare(strict_types=1);

namespace app\modules\housing\controllers;

use app\modules\housing\models\Building;
use app\modules\housing\models\Unit;
use yii\db\Expression;

final class DashboardController extends BaseController
{
    public function actionIndex(?int $building_id = null, ?string $status = null, ?string $q = null)
    {
        $query = Building::find()
            ->with(['floors.units.rooms', 'units.rooms'])
            ->where(['housing_building.status' => Building::STATUS_ACTIVE])
            ->orderBy(['housing_building.sort_order' => SORT_ASC, 'housing_building.name' => SORT_ASC]);

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
        $countQuery = Unit::find()
            ->select(['status', 'total' => new Expression('COUNT(*)')])
            ->groupBy('status')
            ->indexBy('status')
            ->asArray();
        if ($building_id) {
            $countQuery->andWhere(['building_id' => $building_id]);
        }
        $counts = $countQuery->all();

        return $this->render('index', [
            'buildings' => $buildings,
            'buildingOptions' => Building::find()->select('name')->indexBy('id')->column(),
            'visibleUnitIds' => $visibleUnitIds,
            'counts' => $counts,
            'filters' => compact('building_id', 'status', 'q'),
        ]);
    }
}
