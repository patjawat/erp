<?php

namespace app\modules\am\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use app\components\AppHelper;
use app\modules\am\models\Asset;
use app\modules\am\models\AssetDetail;
use app\modules\am\services\AssetTransactionLogService;
use app\modules\hr\models\Organization;
use app\models\Categorise;

/**
 * Asset lifecycle: transfer, repair, dispose, print QR.
 * Routes: /am/asset-lifecycle/transfer, repair, dispose, print-qr
 */
class AssetLifecycleController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [['allow' => true, 'roles' => ['@']]],
            ],
        ];
    }

    public function actionTransfer()
    {
        $code = trim((string) Yii::$app->request->get('code', ''));
        if ($code !== '') {
            $a = Asset::findOne(['code' => $code]);
            if ($a) {
                return $this->redirect(['transfer', 'asset_id' => $a->id]);
            }
            Yii::$app->session->setFlash('error', 'ไม่พบครุภัณฑ์หมายเลข ' . Html::encode($code));
        }
        $assetId = Yii::$app->request->get('asset_id') ?: Yii::$app->request->post('asset_id');
        $asset = $assetId ? $this->findAsset($assetId) : null;

        if (Yii::$app->request->isPost && $asset) {
            $toLocation = trim((string) Yii::$app->request->post('to_location', ''));
            $toDepartment = Yii::$app->request->post('to_department') !== '' ? (int) Yii::$app->request->post('to_department') : null;
            $remark = trim((string) Yii::$app->request->post('remark', ''));

            $fromLocation = is_array($asset->data_json) && isset($asset->data_json['location']) ? $asset->data_json['location'] : '';
            $fromDept = $asset->department;

            $d = new AssetDetail();
            $d->name = AssetDetail::NAME_LIFECYCLE;
            $d->asset_id = $asset->id;
            $d->code = $asset->code;
            $transferredBy = Yii::$app->user->isGuest ? null : [
                'user_id' => Yii::$app->user->id,
                'username' => Yii::$app->user->identity->username ?? null,
            ];
            $d->data_json = [
                'transaction_type' => AssetDetail::TYPE_TRANSFER,
                'from_location' => $fromLocation,
                'to_location' => $toLocation,
                'from_department' => $fromDept,
                'to_department' => $toDepartment,
                'remark' => $remark,
                'transferred_by' => $transferredBy,
            ];
            if ($d->save(false)) {
                $asset->department = $toDepartment;
                $data = is_array($asset->data_json) ? $asset->data_json : [];
                $data['location'] = $toLocation;
                $asset->data_json = $data;
                $asset->save(false);
                AssetTransactionLogService::log($asset, 'TRANSFER', [
                    'from_location' => $fromLocation,
                    'to_location' => $toLocation,
                    'from_department' => $fromDept,
                    'to_department' => $toDepartment,
                    'remark' => $remark,
                ]);
                Yii::$app->session->setFlash('success', 'บันทึกการโอนย้ายเรียบร้อย');
                return $this->redirect(['/am/equip/view-asset', 'id' => $asset->id]);
            }
        }

        $departments = $this->getDepartmentList();
        return $this->render('transfer', [
            'asset' => $asset,
            'departments' => $departments,
        ]);
    }

    public function actionRepair()
    {
        $code = trim((string) Yii::$app->request->get('code', ''));
        if ($code !== '') {
            $a = Asset::findOne(['code' => $code]);
            if ($a) {
                return $this->redirect(['repair', 'asset_id' => $a->id]);
            }
            Yii::$app->session->setFlash('error', 'ไม่พบครุภัณฑ์หมายเลข ' . Html::encode($code));
        }
        $assetId = Yii::$app->request->get('asset_id') ?: Yii::$app->request->post('asset_id');
        $asset = $assetId ? $this->findAsset($assetId) : null;

        if (Yii::$app->request->isPost && $asset) {
            $repairDate = AppHelper::DateToDb(Yii::$app->request->post('repair_date')) ?: date('Y-m-d');
            $problem = trim((string) Yii::$app->request->post('problem_description', ''));
            $cost = Yii::$app->request->post('repair_cost') !== '' ? (float) Yii::$app->request->post('repair_cost') : null;
            $vendor = trim((string) Yii::$app->request->post('vendor', ''));

            $d = new AssetDetail();
            $d->name = AssetDetail::NAME_LIFECYCLE;
            $d->asset_id = $asset->id;
            $d->code = $asset->code;
            $d->data_json = [
                'transaction_type' => AssetDetail::TYPE_REPAIR,
                'remark' => $problem,
                'repair_date' => $repairDate,
                'repair_cost' => $cost,
                'vendor' => $vendor,
            ];
            if ($d->save(false)) {
                $asset->lifecycle_status = Asset::LIFECYCLE_REPAIR;
                $asset->save(false);
                AssetTransactionLogService::log($asset, 'REPAIR', [
                    'remark' => $problem,
                    'data_json' => ['repair_date' => $repairDate, 'repair_cost' => $cost, 'vendor' => $vendor],
                ]);
                Yii::$app->session->setFlash('success', 'บันทึกส่งซ่อมเรียบร้อย');
                return $this->redirect(['/am/equip/view-asset', 'id' => $asset->id]);
            }
        }

        $vendors = ArrayHelper::map(Categorise::find()->where(['name' => 'vendor'])->orderBy('title')->all(), 'code', 'title');
        return $this->render('repair', [
            'asset' => $asset,
            'vendors' => $vendors,
        ]);
    }

    public function actionReturnRepair($id)
    {
        $asset = $this->findAsset($id);
        $d = new AssetDetail();
        $d->name = AssetDetail::NAME_LIFECYCLE;
        $d->asset_id = $asset->id;
        $d->code = $asset->code;
        $d->data_json = [
            'transaction_type' => AssetDetail::TYPE_RETURN,
            'remark' => 'รับคืนจากซ่อม',
        ];
        $d->save(false);
        $asset->lifecycle_status = Asset::LIFECYCLE_ACTIVE;
        $asset->save(false);
        AssetTransactionLogService::log($asset, 'RETURN', ['remark' => 'รับคืนจากซ่อม']);
        Yii::$app->session->setFlash('success', 'อัปเดตสถานะเป็นใช้งานได้แล้ว');
        return $this->redirect(['/am/equip/view-asset', 'id' => $asset->id]);
    }

    public function actionDispose()
    {
        $code = trim((string) Yii::$app->request->get('code', ''));
        if ($code !== '') {
            $a = Asset::findOne(['code' => $code]);
            if ($a) {
                return $this->redirect(['dispose', 'asset_id' => $a->id]);
            }
            Yii::$app->session->setFlash('error', 'ไม่พบครุภัณฑ์หมายเลข ' . Html::encode($code));
        }
        $assetId = Yii::$app->request->get('asset_id') ?: Yii::$app->request->post('asset_id');
        $asset = $assetId ? $this->findAsset($assetId) : null;

        if (Yii::$app->request->isPost && $asset) {
            $method = trim((string) Yii::$app->request->post('disposal_method', ''));
            $disposeDate = AppHelper::DateToDb(Yii::$app->request->post('disposal_date')) ?: date('Y-m-d');
            $remark = trim((string) Yii::$app->request->post('remark', ''));

            $d = new AssetDetail();
            $d->name = AssetDetail::NAME_LIFECYCLE;
            $d->asset_id = $asset->id;
            $d->code = $asset->code;
            $d->data_json = [
                'transaction_type' => AssetDetail::TYPE_DISPOSE,
                'remark' => $remark,
                'disposal_method' => $method,
                'disposal_date' => $disposeDate,
            ];
            if ($d->save(false)) {
                $asset->lifecycle_status = Asset::LIFECYCLE_DISPOSED;
                $asset->save(false);
                AssetTransactionLogService::log($asset, 'DISPOSE', [
                    'remark' => $remark,
                    'data_json' => ['disposal_method' => $method, 'disposal_date' => $disposeDate],
                ]);
                Yii::$app->session->setFlash('success', 'บันทึกจำหน่ายเรียบร้อย');
                return $this->redirect(['/am/equip/view-asset', 'id' => $asset->id]);
            }
        }

        return $this->render('dispose', ['asset' => $asset]);
    }

    public function actionPrintQr()
    {
        $ids = Yii::$app->request->get('ids') ?: Yii::$app->request->post('ids');
        if (is_string($ids)) {
            $ids = array_filter(array_map('intval', explode(',', $ids)));
        }
        if (!is_array($ids) || empty($ids)) {
            $assets = [];
        } else {
            $assets = Asset::find()
                ->where(['id' => $ids])
                ->andWhere(['not', ['code' => null]])
                ->andWhere(['!=', 'code', ''])
                ->limit(100)
                ->all();
        }
        return $this->render('print-qr', ['assets' => $assets]);
    }

    protected function findAsset($id)
    {
        $model = Asset::findOne(['id' => $id]);
        if ($model !== null) {
            return $model;
        }
        throw new NotFoundHttpException('ไม่พบครุภัณฑ์');
    }

    protected function getDepartmentList()
    {
        $list = Organization::find()->orderBy('lft')->all();
        $out = [];
        foreach ($list as $d) {
            $out[$d->id] = str_repeat('— ', (int) $d->lvl) . $d->name;
        }
        return $out;
    }
}
