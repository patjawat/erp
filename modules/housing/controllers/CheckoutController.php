<?php

declare(strict_types=1);

namespace app\modules\housing\controllers;

use app\modules\filemanager\components\FileManagerHelper;
use app\modules\filemanager\models\Uploads;
use app\modules\housing\models\AssetAssignment;
use app\modules\housing\models\Checkout;
use app\modules\housing\models\Occupancy;
use app\modules\housing\models\MonthlyAccount;
use app\modules\housing\services\CheckoutWorkflowService;
use app\modules\housing\services\UnitStatusService;
use app\modules\hr\models\Employees;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

final class CheckoutController extends BaseController
{
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'verbs' => ['class' => VerbFilter::class, 'actions' => ['sign-inspector' => ['POST']]],
        ]);
    }

    public function actionIndex()
    {
        return $this->render('index', ['dataProvider' => new ActiveDataProvider([
            'query' => Checkout::find()->with(['occupancy.employee', 'occupancy.unit.building', 'occupancy.room'])
                ->orderBy(['id' => SORT_DESC]),
            'pagination' => ['pageSize' => 20],
        ])]);
    }

    public function actionPrepare(int $id)
    {
        $model = $this->findModel($id);
        if ($model->status !== Checkout::STATUS_REQUESTED) {
            return $this->redirect(['view', 'id' => $model->id]);
        }
        $occupancy = $model->occupancy;
        $assets = $this->locationAssets($occupancy);
        $staff = Employees::findOne(['user_id' => Yii::$app->user->id]);
        $model->checkout_date = $model->checkout_date ?: date('Y-m-d');
        $model->outstanding_amount = (float)MonthlyAccount::find()->where(['occupancy_id' => $occupancy->id])->sum('balance_amount');
        $model->inspected_by_emp_id = $staff?->id;
        $model->inspected_by_name = $staff?->fullname() ?: (string)Yii::$app->user->identity?->username;

        if ($model->load(Yii::$app->request->post())) {
            $model->condition_photos = UploadedFile::getInstances($model, 'condition_photos');
            $model->asset_snapshot = json_encode($this->normalizeAssets($assets, (array)Yii::$app->request->post('asset')), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $model->inspected_by_emp_id = $staff?->id;
            $model->inspected_by_name = $staff?->fullname() ?: (string)Yii::$app->user->identity?->username;
            $model->outstanding_amount = (float)MonthlyAccount::find()->where(['occupancy_id' => $occupancy->id])->sum('balance_amount');
        } elseif ($model->asset_snapshot === null) {
            $model->asset_snapshot = json_encode($this->normalizeAssets($assets, []), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        if (Yii::$app->request->isPost && $model->validate() && $model->save(false)) {
            $failed = false;
            foreach ($model->condition_photos as $file) {
                if (FileManagerHelper::saveUploadedFile($file, (string)$model->ref, 'housing_checkout_condition', false) === null) {
                    $failed = true;
                }
            }
            try {
                (new CheckoutWorkflowService())->submitInspection($model);
                Yii::$app->session->setFlash($failed ? 'warning' : 'success', $failed ? 'บันทึกผลตรวจแล้ว แต่รูปภาพบางไฟล์จัดเก็บไม่สำเร็จ' : 'บันทึกผลตรวจแล้ว ระบบรอผู้พักลงนามส่งคืน');
            } catch (\Throwable $e) {
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
            return $this->redirect(['view', 'id' => $model->id]);
        }
        return $this->render('form', ['model' => $model, 'occupancy' => $occupancy, 'assetItems' => $model->assetItems()]);
    }

    public function actionView(int $id)
    {
        $model = $this->findModel($id);
        return $this->render('view', [
            'model' => $model,
            'photos' => Uploads::find()->where(['ref' => $model->ref, 'name' => 'housing_checkout_condition'])->orderBy('id')->all(),
        ]);
    }

    public function actionSignInspector(int $id)
    {
        $model = $this->findModel($id);
        if (!Yii::$app->request->post('inspector_ack')) {
            Yii::$app->session->setFlash('error', 'กรุณายืนยันการตรวจรับคืน');
            return $this->redirect(['view', 'id' => $id]);
        }
        try {
            (new CheckoutWorkflowService())->signInspector($model);
            Yii::$app->session->setFlash('success', 'รับคืนบ้านพักและปิดสถานะผู้พักเรียบร้อยแล้ว');
        } catch (\Throwable $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }
        return $this->redirect(['view', 'id' => $id]);
    }

    public function actionPrint(int $id)
    {
        $this->layout = false;
        return $this->render('print', ['model' => $this->findModel($id)]);
    }

    private function findModel(int $id): Checkout
    {
        $model = Checkout::find()->with(['occupancy.employee', 'occupancy.unit.building', 'occupancy.unit.floor', 'occupancy.room'])->where(['housing_checkout.id' => $id])->one();
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบเอกสารส่งคืนบ้านพัก');
        }
        return $model;
    }

    private function locationAssets(Occupancy $occupancy): array
    {
        return AssetAssignment::find()->where(['unit_id' => $occupancy->unit_id, 'is_active' => 1])
            ->andWhere($occupancy->room_id ? ['room_id' => $occupancy->room_id] : ['room_id' => null])
            ->orderBy('item_name')->all();
    }

    private function normalizeAssets(array $assets, array $posted): array
    {
        $items = [];
        foreach ($assets as $asset) {
            $input = (array)($posted[(string)$asset->id] ?? []);
            $condition = (string)($input['condition'] ?? $asset->condition_status);
            $items[] = [
                'asset_id' => (int)$asset->id, 'item_name' => $asset->item_name,
                'quantity' => (float)$asset->quantity, 'unit_name' => $asset->unit_name,
                'condition' => array_key_exists($condition, AssetAssignment::conditionOptions()) ? $condition : $asset->condition_status,
                'note' => trim((string)($input['note'] ?? '')), 'acknowledged' => !empty($input['acknowledged']),
            ];
        }
        return $items;
    }
}
