<?php

namespace app\modules\am\services;

use Yii;
use app\modules\am\models\Asset;
use app\modules\am\models\AssetDetail;

/**
 * Aggregated data for executive AM dashboard. Optimized for 50k+ assets.
 * Uses single aggregation queries; optional cache.
 */
class DashboardDataService
{
    public const CACHE_KEY = 'am_dashboard_exec_v2';
    public const CACHE_DURATION = 300; // 5 minutes

    /**
     * @return array All dashboard data (kpis, health, replacement, distribution, risk, age, recent)
     */
    public static function getData(bool $useCache = true): array
    {
        if ($useCache && Yii::$app->cache) {
            $data = Yii::$app->cache->get(self::CACHE_KEY);
            if (is_array($data)) {
                return $data;
            }
        }

        $data = [
            'kpis' => self::getKpis(),
            'health' => self::getHealthStatus(),
            'replacementForecast' => self::getReplacementForecast(),
            'categoryDistribution' => self::getCategoryDistribution(),
            'departmentDistribution' => self::getDepartmentDistribution(),
            'groupDistribution' => self::getGroupDistribution(),
            'riskAlerts' => self::getRiskAlerts(),
            'ageAnalysis' => self::getAgeAnalysis(),
            'recentActivities' => self::getRecentActivities(),
        ];

        if (Yii::$app->cache) {
            Yii::$app->cache->set(self::CACHE_KEY, $data, self::CACHE_DURATION);
        }

        return $data;
    }

    public static function invalidateCache(): void
    {
        if (Yii::$app->cache) {
            Yii::$app->cache->delete(self::CACHE_KEY);
        }
    }

    /** Section 1: Executive KPIs */
    protected static function getKpis(): array
    {
        $schema = Yii::$app->db->getSchema()->getTableSchema('{{%asset}}');
        $hasLifecycle = $schema && $schema->getColumn('lifecycle_status') !== null;
        $hasUsefulLife = $schema && $schema->getColumn('useful_life') !== null;

        $total = (int) Yii::$app->db->createCommand(
            'SELECT COUNT(*) FROM {{%asset}} WHERE deleted_at IS NULL'
        )->queryScalar();

        $exceedUsefulLife = 0;
        $underRepair = 0;
        $waitingDisposal = 0;
        if ($hasLifecycle) {
            $underRepair = (int) Yii::$app->db->createCommand(
                'SELECT COUNT(*) FROM {{%asset}} WHERE deleted_at IS NULL AND lifecycle_status = :repair'
            )->bindValue(':repair', Asset::LIFECYCLE_REPAIR)->queryScalar();
            $waitingDisposal = (int) Yii::$app->db->createCommand(
                'SELECT COUNT(*) FROM {{%asset}} WHERE deleted_at IS NULL AND lifecycle_status = :disposed'
            )->bindValue(':disposed', Asset::LIFECYCLE_DISPOSED)->queryScalar();
        }
        if ($hasUsefulLife && $schema->getColumn('receive_date') !== null) {
            $exceedUsefulLife = (int) Yii::$app->db->createCommand(
                'SELECT COUNT(*) FROM {{%asset}} WHERE deleted_at IS NULL AND receive_date IS NOT NULL AND useful_life IS NOT NULL AND useful_life > 0 AND (YEAR(CURDATE()) - YEAR(receive_date)) >= useful_life'
            )->queryScalar();
        }

        $replacementCost = 0;
        if ($hasUsefulLife && $schema->getColumn('receive_date') !== null && $schema->getColumn('price') !== null) {
            $replacementCost = (float) Yii::$app->db->createCommand(
                'SELECT COALESCE(SUM(price), 0) FROM {{%asset}} WHERE deleted_at IS NULL AND receive_date IS NOT NULL AND useful_life IS NOT NULL AND useful_life > 0 AND (YEAR(CURDATE()) - YEAR(receive_date)) >= useful_life'
            )->queryScalar();
        }

        return [
            'total_assets' => $total,
            'exceeding_useful_life' => $exceedUsefulLife,
            'under_repair' => $underRepair,
            'waiting_disposal' => $waitingDisposal,
            'estimated_replacement_cost' => round($replacementCost, 2),
        ];
    }

    /** Section 2: Asset health (Healthy, Near EOL, Expired, Under Repair, Pending Disposal) */
    protected static function getHealthStatus(): array
    {
        $schema = Yii::$app->db->getSchema()->getTableSchema('{{%asset}}');
        $hasLifecycle = $schema && $schema->getColumn('lifecycle_status') !== null;
        $hasUsefulLife = $schema && $schema->getColumn('useful_life') !== null;
        $hasReceiveDate = $schema && $schema->getColumn('receive_date') !== null;

        $healthy = 0;
        $nearEol = 0;
        $expired = 0;
        $underRepair = 0;
        $pendingDisposal = 0;

        if (!$hasLifecycle) {
            return [
                'healthy' => 0,
                'near_eol' => 0,
                'expired' => 0,
                'under_repair' => 0,
                'pending_disposal' => 0,
            ];
        }

        $underRepair = (int) Yii::$app->db->createCommand(
            'SELECT COUNT(*) FROM {{%asset}} WHERE deleted_at IS NULL AND lifecycle_status = :repair'
        )->bindValue(':repair', Asset::LIFECYCLE_REPAIR)->queryScalar();

        $pendingDisposal = (int) Yii::$app->db->createCommand(
            'SELECT COUNT(*) FROM {{%asset}} WHERE deleted_at IS NULL AND lifecycle_status = :disposed'
        )->bindValue(':disposed', Asset::LIFECYCLE_DISPOSED)->queryScalar();

        if ($hasUsefulLife && $hasReceiveDate) {
            $expired = (int) Yii::$app->db->createCommand(
                'SELECT COUNT(*) FROM {{%asset}} WHERE deleted_at IS NULL AND lifecycle_status = :active AND receive_date IS NOT NULL AND useful_life IS NOT NULL AND useful_life > 0 AND (YEAR(CURDATE()) - YEAR(receive_date)) >= useful_life'
            )->bindValue(':active', Asset::LIFECYCLE_ACTIVE)->queryScalar();

            $nearEol = (int) Yii::$app->db->createCommand(
                'SELECT COUNT(*) FROM {{%asset}} WHERE deleted_at IS NULL AND lifecycle_status = :active AND receive_date IS NOT NULL AND useful_life IS NOT NULL AND useful_life > 0 AND (YEAR(CURDATE()) - YEAR(receive_date)) < useful_life AND (useful_life - (YEAR(CURDATE()) - YEAR(receive_date))) <= 3 AND (useful_life - (YEAR(CURDATE()) - YEAR(receive_date))) > 0'
            )->bindValue(':active', Asset::LIFECYCLE_ACTIVE)->queryScalar();

            $healthy = (int) Yii::$app->db->createCommand(
                'SELECT COUNT(*) FROM {{%asset}} WHERE deleted_at IS NULL AND lifecycle_status = :active AND (receive_date IS NULL OR useful_life IS NULL OR useful_life <= 0 OR (useful_life - (YEAR(CURDATE()) - YEAR(receive_date))) > 3)'
            )->bindValue(':active', Asset::LIFECYCLE_ACTIVE)->queryScalar();
        } else {
            $healthy = (int) Yii::$app->db->createCommand(
                'SELECT COUNT(*) FROM {{%asset}} WHERE deleted_at IS NULL AND lifecycle_status = :active'
            )->bindValue(':active', Asset::LIFECYCLE_ACTIVE)->queryScalar();
        }

        return [
            'healthy' => $healthy,
            'near_eol' => $nearEol,
            'expired' => $expired,
            'under_repair' => $underRepair,
            'pending_disposal' => $pendingDisposal,
        ];
    }

    /** Section 3: Replacement forecast — stacked bar (within 1yr, 2–3yr, 4–5yr) */
    protected static function getReplacementForecast(): array
    {
        $schema = Yii::$app->db->getSchema()->getTableSchema('{{%asset}}');
        if (!$schema || $schema->getColumn('useful_life') === null || $schema->getColumn('receive_date') === null) {
            return ['labels' => [], 'counts' => [], 'prices' => []];
        }

        $sql = <<<SQL
SELECT
  CASE
    WHEN (a.useful_life - (YEAR(CURDATE()) - YEAR(a.receive_date))) <= 1 AND (a.useful_life - (YEAR(CURDATE()) - YEAR(a.receive_date))) > 0 THEN 'within_1'
    WHEN (a.useful_life - (YEAR(CURDATE()) - YEAR(a.receive_date))) <= 3 AND (a.useful_life - (YEAR(CURDATE()) - YEAR(a.receive_date))) > 1 THEN 'within_3'
    WHEN (a.useful_life - (YEAR(CURDATE()) - YEAR(a.receive_date))) <= 5 AND (a.useful_life - (YEAR(CURDATE()) - YEAR(a.receive_date))) > 3 THEN 'within_5'
    ELSE 'other'
  END AS bucket,
  COALESCE(SUM(a.price), 0) AS total_price,
  COUNT(*) AS cnt
FROM {{%asset}} a
WHERE a.deleted_at IS NULL
  AND a.receive_date IS NOT NULL
  AND a.useful_life IS NOT NULL
  AND a.useful_life > 0
  AND (YEAR(CURDATE()) - YEAR(a.receive_date)) < a.useful_life
  AND (a.useful_life - (YEAR(CURDATE()) - YEAR(a.receive_date))) <= 5
GROUP BY bucket
SQL;
        $rows = Yii::$app->db->createCommand($sql)->queryAll();
        $byBucket = [];
        foreach ($rows as $r) {
            $byBucket[$r['bucket']] = ['count' => (int) $r['cnt'], 'total_price' => (float) $r['total_price']];
        }
        return [
            'labels' => ['ภายใน 1 ปี', 'ภายใน 2–3 ปี', 'ภายใน 4–5 ปี'],
            'counts' => [
                $byBucket['within_1']['count'] ?? 0,
                $byBucket['within_3']['count'] ?? 0,
                $byBucket['within_5']['count'] ?? 0,
            ],
            'prices' => [
                $byBucket['within_1']['total_price'] ?? 0,
                $byBucket['within_3']['total_price'] ?? 0,
                $byBucket['within_5']['total_price'] ?? 0,
            ],
        ];
    }

    /** Section 4a: Category distribution (asset_type_id) */
    protected static function getCategoryDistribution(): array
    {
        $schema = Yii::$app->db->getSchema()->getTableSchema('{{%asset}}');
        if (!$schema || $schema->getColumn('asset_type_id') === null) {
            return [];
        }
        return Yii::$app->db->createCommand(
            'SELECT asset_type_id AS label, COUNT(*) AS value FROM {{%asset}} WHERE deleted_at IS NULL AND asset_type_id IS NOT NULL AND asset_type_id != "" GROUP BY asset_type_id ORDER BY value DESC LIMIT 10'
        )->queryAll();
    }

    /** Section 4b: Department distribution */
    protected static function getDepartmentDistribution(): array
    {
        $schema = Yii::$app->db->getSchema()->getTableSchema('{{%asset}}');
        if (!$schema || $schema->getColumn('department') === null) {
            return [];
        }
        return Yii::$app->db->createCommand(
            'SELECT COALESCE(department, 0) AS dept_id, COUNT(*) AS value FROM {{%asset}} WHERE deleted_at IS NULL GROUP BY department ORDER BY value DESC LIMIT 15'
        )->queryAll();
    }

    /** Section 4c: Asset Group distribution */
    protected static function getGroupDistribution(): array
    {
        $schema = Yii::$app->db->getSchema()->getTableSchema('{{%asset}}');
        if (!$schema || $schema->getColumn('asset_group_id') === null) {
            return [];
        }
        return Yii::$app->db->createCommand(
            "SELECT c.title AS label, a.asset_group_id, COUNT(a.asset_group_id) AS value 
             FROM {{%asset}} a 
             LEFT JOIN {{%categorise}} c ON c.code = a.asset_group_id AND c.name = 'asset_group'
             WHERE a.deleted_at IS NULL AND c.code IN(1,2,3,4,5,6)
             GROUP BY a.asset_group_id
             ORDER BY a.asset_group_id DESC"
        )->queryAll();
    }

    /** Section 5: Risk alerts (no department, many transfers) */
    protected static function getRiskAlerts(): array
    {
        $schema = Yii::$app->db->getSchema()->getTableSchema('{{%asset}}');
        $noDept = 0;
        if ($schema && $schema->getColumn('department') !== null) {
            $noDept = (int) Yii::$app->db->createCommand(
                'SELECT COUNT(*) FROM {{%asset}} WHERE deleted_at IS NULL AND (department IS NULL OR department = 0)'
            )->queryScalar();
        }

        $manyTransfers = [];
        try {
            $detailSchema = Yii::$app->db->getSchema()->getTableSchema('{{%asset_detail}}');
            if ($detailSchema) {
                $manyTransfers = Yii::$app->db->createCommand(
                    'SELECT asset_id, COUNT(*) AS transfer_count FROM {{%asset_detail}} WHERE name = :name AND JSON_UNQUOTE(JSON_EXTRACT(data_json, \'$.transaction_type\')) = \'TRANSFER\' GROUP BY asset_id HAVING transfer_count >= 3 ORDER BY transfer_count DESC LIMIT 10'
                )->bindValue(':name', AssetDetail::NAME_LIFECYCLE)->queryAll();
            }
        } catch (\Throwable $e) {
            // ignore
        }

        return [
            'no_department_count' => $noDept,
            'many_transfers' => $manyTransfers,
        ];
    }

    /** Section 6: Age analysis (0–3, 4–6, 7–10, 10+ years) */
    protected static function getAgeAnalysis(): array
    {
        $schema = Yii::$app->db->getSchema()->getTableSchema('{{%asset}}');
        if (!$schema || $schema->getColumn('receive_date') === null) {
            return [];
        }
        $rows = Yii::$app->db->createCommand(
            'SELECT
  CASE
    WHEN TIMESTAMPDIFF(YEAR, receive_date, CURDATE()) <= 3 THEN \'0-3\'
    WHEN TIMESTAMPDIFF(YEAR, receive_date, CURDATE()) <= 6 THEN \'4-6\'
    WHEN TIMESTAMPDIFF(YEAR, receive_date, CURDATE()) <= 10 THEN \'7-10\'
    ELSE \'10+\'
  END AS age_bucket,
  COUNT(*) AS value
FROM {{%asset}}
WHERE deleted_at IS NULL AND receive_date IS NOT NULL
GROUP BY age_bucket'
        )->queryAll();
        $order = ['0-3' => 0, '4-6' => 1, '7-10' => 2, '10+' => 3];
        usort($rows, function ($a, $b) use ($order) {
            return ($order[$a['age_bucket']] ?? 99) <=> ($order[$b['age_bucket']] ?? 99);
        });
        return $rows;
    }

    /** Section 7: Recent activities */
    protected static function getRecentActivities(): array
    {
        $transfers = [];
        $repairs = [];
        $disposals = [];
        try {
            $detailSchema = Yii::$app->db->getSchema()->getTableSchema('{{%asset_detail}}');
            if (!$detailSchema) {
                return ['transfers' => [], 'repairs' => [], 'disposals' => []];
            }
            $transfers = Yii::$app->db->createCommand(
                'SELECT ad.id, ad.asset_id, ad.created_at, ad.data_json FROM {{%asset_detail}} ad WHERE ad.name = :name AND JSON_UNQUOTE(JSON_EXTRACT(ad.data_json, \'$.transaction_type\')) = \'TRANSFER\' ORDER BY ad.created_at DESC LIMIT 10'
            )->bindValue(':name', AssetDetail::NAME_LIFECYCLE)->queryAll();
            $repairs = Yii::$app->db->createCommand(
                'SELECT ad.id, ad.asset_id, ad.created_at, ad.data_json FROM {{%asset_detail}} ad WHERE ad.name = :name AND JSON_UNQUOTE(JSON_EXTRACT(ad.data_json, \'$.transaction_type\')) IN (\'REPAIR\', \'RETURN\') ORDER BY ad.created_at DESC LIMIT 10'
            )->bindValue(':name', AssetDetail::NAME_LIFECYCLE)->queryAll();
            $disposals = Yii::$app->db->createCommand(
                'SELECT ad.id, ad.asset_id, ad.created_at, ad.data_json FROM {{%asset_detail}} ad WHERE ad.name = :name AND JSON_UNQUOTE(JSON_EXTRACT(ad.data_json, \'$.transaction_type\')) = \'DISPOSE\' ORDER BY ad.created_at DESC LIMIT 10'
            )->bindValue(':name', AssetDetail::NAME_LIFECYCLE)->queryAll();
        } catch (\Throwable $e) {
            // ignore
        }
        return ['transfers' => $transfers, 'repairs' => $repairs, 'disposals' => $disposals];
    }
}
