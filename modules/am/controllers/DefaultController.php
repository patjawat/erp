<?php

namespace app\modules\am\controllers;

use Yii;
use yii\web\Controller;
use app\modules\am\models\Asset;
use app\modules\am\models\AssetStatus;
use app\modules\am\models\AssetCondition;
use app\modules\am\models\AssetDetail;
use app\modules\am\services\DashboardDataService;

/**
 * Default controller for the `am` module
 */
class DefaultController extends Controller
{
    /**
     * Renders the executive dashboard (index)
     * @return string
     */
    public function actionIndex()
    {
        $dashboard = DashboardDataService::getData(true);

        $lifecycleStats = $this->getLifecycleStats();
        $assetStatusStats = $this->getAssetStatusStats();
        $assetConditionStats = $this->getAssetConditionStats();
        $recentTransfers = $this->getRecentTransfers();

        return $this->render('index', [
            'dashboard' => $dashboard,
            // 'lifecycleStats' => $lifecycleStats,
            'lifecycleStats' => null,
            'assetStatusStats' => $assetStatusStats,
            'assetConditionStats' => $assetConditionStats,
            'recentTransfers' => $recentTransfers,
        ]);
    }

    protected function getAssetStatusStats(): array
    {
        try {
            $schema = Yii::$app->db->getSchema()->getTableSchema('{{%asset}}');
            if ($schema === null || $schema->getColumn('asset_status') === null) {
                return [];
            }

            $counts = Asset::find()
                ->alias('a')
                ->select(['asset_status', 'COUNT(*) AS total'])
                ->where(['a.deleted_at' => null])
                ->groupBy(['asset_status'])
                ->indexBy('asset_status')
                ->asArray()
                ->all();

            $statusRows = AssetStatus::find()
                ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])
                ->all();

            $stats = [];
            foreach ($statusRows as $status) {
                $stats[] = [
                    'id' => (string) $status->id,
                    'name' => $status->name,
                    'total' => (int) ($counts[$status->id]['total'] ?? 0),
                ];
            }

            return $stats;
        } catch (\Throwable $e) {
            return [];
        }
    }

    protected function getAssetConditionStats(): array
    {
        try {
            $schema = Yii::$app->db->getSchema()->getTableSchema('{{%asset}}');
            if ($schema === null || $schema->getColumn('asset_condition') === null) {
                return [];
            }

            $counts = Asset::find()
                ->alias('a')
                ->select(['asset_condition', 'COUNT(*) AS total'])
                ->where(['a.deleted_at' => null])
                ->groupBy(['asset_condition'])
                ->indexBy('asset_condition')
                ->asArray()
                ->all();

            $conditionRows = AssetCondition::find()
                ->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC])
                ->all();

            $stats = [];
            foreach ($conditionRows as $condition) {
                $stats[] = [
                    'id' => (string) $condition->id,
                    'name' => $condition->name,
                    'total' => (int) ($counts[$condition->id]['total'] ?? 0),
                ];
            }

            $unknownTotal = 0;
            foreach (['', null] as $key) {
                if (array_key_exists($key, $counts)) {
                    $unknownTotal += (int) $counts[$key]['total'];
                }
            }
            if ($unknownTotal > 0) {
                $stats[] = [
                    'id' => 'unknown',
                    'name' => 'ไม่ระบุสภาพ',
                    'total' => $unknownTotal,
                ];
            }

            return $stats;
        } catch (\Throwable $e) {
            return [];
        }
    }

    protected function getLifecycleStats()
    {
        try {
            $schema = Yii::$app->db->getSchema()->getTableSchema('{{%asset}}');
            if ($schema && $schema->getColumn('lifecycle_status') === null) {
                return null;
            }
            $total = (int) Asset::find()->andWhere('deleted_at IS NULL')->count();
            $active = (int) Asset::find()->andWhere(['lifecycle_status' => Asset::LIFECYCLE_ACTIVE])->andWhere('deleted_at IS NULL')->count();
            $repair = (int) Asset::find()->andWhere(['lifecycle_status' => Asset::LIFECYCLE_REPAIR])->andWhere('deleted_at IS NULL')->count();
            $disposed = (int) Asset::find()->andWhere(['lifecycle_status' => Asset::LIFECYCLE_DISPOSED])->count();
            return ['total' => $total, 'active' => $active, 'repair' => $repair, 'disposed' => $disposed];
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function getRecentTransfers()
    {
        try {
            $schema = Yii::$app->db->getSchema()->getTableSchema('{{%asset_detail}}');
            if ($schema === null || $schema->getColumn('asset_id') === null) {
                return [];
            }
            return AssetDetail::find()
                ->where(['name' => AssetDetail::NAME_LIFECYCLE])
                ->andWhere("JSON_UNQUOTE(JSON_EXTRACT(data_json, '$.transaction_type')) = 'TRANSFER'")
                ->orderBy(['created_at' => SORT_DESC])
                ->limit(10)
                ->with('assetById')
                ->all();
        } catch (\Throwable $e) {
            return [];
        }
    }
}
