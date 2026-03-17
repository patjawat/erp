<?php

namespace app\modules\am\controllers;

use Yii;
use yii\web\Controller;
use app\modules\am\models\Asset;
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
        $recentTransfers = $this->getRecentTransfers();

        return $this->render('index', [
            'dashboard' => $dashboard,
            'lifecycleStats' => $lifecycleStats,
            'recentTransfers' => $recentTransfers,
        ]);
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
