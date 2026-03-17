<?php

namespace app\modules\am\services;

use Yii;
use app\modules\am\models\Asset;

/**
 * Writes lifecycle events to am_asset_transactions for reporting and audit.
 * Does not replace AssetDetail (existing workflow unchanged).
 */
class AssetTransactionLogService
{
    /**
     * @param Asset $asset
     * @param string $transactionType RECEIVE|TRANSFER|REPAIR|RETURN|DISPOSE
     * @param array $options from_location, to_location, from_department, to_department, remark, data_json
     */
    public static function log(Asset $asset, string $transactionType, array $options = []): bool
    {
        $schema = Yii::$app->db->getSchema()->getTableSchema('{{%am_asset_transactions}}');
        if ($schema === null) {
            return false;
        }

        try {
            Yii::$app->db->createCommand()->insert('{{%am_asset_transactions}}', [
                'asset_id' => $asset->id,
                'transaction_type' => $transactionType,
                'from_location' => $options['from_location'] ?? null,
                'to_location' => $options['to_location'] ?? null,
                'from_department' => $options['from_department'] ?? null,
                'to_department' => $options['to_department'] ?? null,
                'remark' => $options['remark'] ?? null,
                'data_json' => isset($options['data_json']) ? json_encode($options['data_json']) : null,
                'created_by' => Yii::$app->user->isGuest ? null : Yii::$app->user->id,
                'created_at' => date('Y-m-d H:i:s'),
            ])->execute();
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
