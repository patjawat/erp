<?php

namespace app\modules\am\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use app\modules\am\models\Asset;

/**
 * API for asset scan (e.g. mobile verification).
 * URL: /api/asset/scan?asset_number=XXX
 */
class AssetApiController extends Controller
{
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        Yii::$app->response->format = Response::FORMAT_JSON;
        return true;
    }

    /**
     * GET or POST asset_number → returns asset details JSON.
     */
    public function actionScan()
    {
        $assetNumber = Yii::$app->request->get('asset_number') ?: Yii::$app->request->post('asset_number');
        if (empty($assetNumber)) {
            return [
                'success' => false,
                'message' => 'Missing asset_number',
                'data' => null,
            ];
        }
        $asset = Asset::find()
            ->andWhere(['code' => trim($assetNumber)])
            ->one();
        if (!$asset) {
            return [
                'success' => false,
                'message' => 'Asset not found',
                'data' => null,
            ];
        }
        $data = [
            'id' => (int) $asset->id,
            'code' => $asset->code,
            'asset_name' => $asset->AssetitemName() ?? $asset->name ?? $asset->code,
            'department' => $asset->departmentName(),
            'location' => $asset->data_json['location'] ?? null,
            'lifecycle_status' => $asset->lifecycle_status ?? Asset::LIFECYCLE_ACTIVE,
            'receive_date' => $asset->receive_date ? Yii::$app->formatter->asDate($asset->receive_date) : null,
        ];
        if (!empty($asset->qr_code_path)) {
            $data['qr_code_url'] = $asset->qr_code_path;
        }
        return [
            'success' => true,
            'message' => 'OK',
            'data' => $data,
        ];
    }
}
