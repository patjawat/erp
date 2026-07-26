<?php

declare(strict_types=1);

namespace app\modules\housing\controllers;

use app\modules\filemanager\components\FileManagerHelper;
use app\modules\filemanager\models\Uploads;
use app\modules\housing\models\AssetAssignment;
use app\modules\housing\models\Handover;
use app\modules\housing\models\HousingRequest;
use app\modules\housing\models\Occupancy;
use app\modules\housing\services\HandoverWorkflowService;
use app\modules\hr\models\Employees;
use Yii;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

final class HandoverController extends BaseController
{
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'sign-sender' => ['POST'],
                ],
            ],
        ]);
    }

    public function actionPrepare(int $request_id)
    {
        $request = HousingRequest::find()
            ->with(['occupancy.employee', 'occupancy.unit.building', 'occupancy.room'])
            ->where(['id' => $request_id])
            ->one();
        if (!$request || !$request->occupancy) {
            throw new NotFoundHttpException('ไม่พบรายการจัดสรรที่พัก');
        }
        if (!in_array($request->status, [HousingRequest::STATUS_ALLOCATED, HousingRequest::STATUS_ACTIVE], true)) {
            throw new \DomainException('คำขอยังไม่พร้อมจัดทำเอกสารรับมอบ');
        }

        $occupancy = $request->occupancy;
        $model = Handover::findOne(['occupancy_id' => $occupancy->id]);
        if ($model && $model->status === Handover::STATUS_CONFIRMED) {
            return $this->redirect(['view', 'id' => $model->id]);
        }
        if ($model && $model->handed_over_signed_at !== null) {
            Yii::$app->session->setFlash('warning', 'ผู้ส่งมอบลงนามแล้ว จึงไม่สามารถแก้ไขเอกสารได้');
            return $this->redirect(['view', 'id' => $model->id]);
        }
        if (!$model) {
            $receiver = $occupancy->employee;
            $staff = $this->currentEmployee();
            $model = new Handover([
                'handover_no' => $this->nextNumber(),
                'occupancy_id' => $occupancy->id,
                'handover_date' => date('Y-m-d'),
                'handed_over_by_emp_id' => $staff?->id,
                'handed_over_by_name' => $staff?->fullname() ?: '',
                'received_by_emp_id' => $occupancy->emp_id,
                'received_by_name' => $receiver?->fullname() ?: 'รหัสบุคลากร ' . $occupancy->emp_id,
            ]);
        }

        $assets = $this->locationAssets($occupancy);
        if ($model->load(Yii::$app->request->post())) {
            $model->condition_photos = UploadedFile::getInstances($model, 'condition_photos');
            $model->asset_snapshot = json_encode(
                $this->normalizeAssetSnapshot($assets, (array)Yii::$app->request->post('asset')),
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );
        } elseif ($model->asset_snapshot === null) {
            $model->asset_snapshot = json_encode(
                $this->normalizeAssetSnapshot($assets, []),
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );
        }

        if (Yii::$app->request->isPost && $model->validate() && $model->save(false)) {
            $failed = false;
            foreach ($model->condition_photos as $file) {
                if (FileManagerHelper::saveUploadedFile($file, (string)$model->ref, 'housing_handover_condition', false) === null) {
                    $failed = true;
                }
            }
            Yii::$app->session->setFlash(
                $failed ? 'warning' : 'success',
                $failed ? 'บันทึกเอกสารแล้ว แต่รูปภาพบางไฟล์จัดเก็บไม่สำเร็จ' : 'บันทึกร่างเอกสารรับมอบแล้ว'
            );
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('form', [
            'model' => $model,
            'request' => $request,
            'occupancy' => $occupancy,
            'assetItems' => $model->assetItems(),
        ]);
    }

    public function actionView(int $id)
    {
        $model = $this->findModel($id);
        return $this->render('view', [
            'model' => $model,
            'photos' => Uploads::find()
                ->where(['ref' => $model->ref, 'name' => 'housing_handover_condition'])
                ->orderBy(['id' => SORT_ASC])
                ->all(),
        ]);
    }

    public function actionSignSender(int $id)
    {
        $model = $this->findModel($id);
        if (!Yii::$app->request->post('handed_over_ack')) {
            Yii::$app->session->setFlash('error', 'กรุณายืนยันการลงนามของผู้ส่งมอบ');
            return $this->redirect(['view', 'id' => $id]);
        }
        try {
            (new HandoverWorkflowService())->signSender($model);
            Yii::$app->session->setFlash('success', 'ลงนามส่งมอบแล้ว ระบบแจ้งให้ผู้รับมอบเข้ามาตรวจและลงนามต่อ');
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

    private function findModel(int $id): Handover
    {
        $model = Handover::find()
            ->with(['occupancy.employee', 'occupancy.unit.building', 'occupancy.unit.floor', 'occupancy.room'])
            ->where(['housing_handover.id' => $id])
            ->one();
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบเอกสารรับมอบ');
        }
        return $model;
    }

    private function locationAssets(Occupancy $occupancy): array
    {
        return AssetAssignment::find()
            ->where(['unit_id' => $occupancy->unit_id, 'is_active' => 1])
            ->andWhere($occupancy->room_id ? ['room_id' => $occupancy->room_id] : ['room_id' => null])
            ->orderBy(['item_name' => SORT_ASC])
            ->all();
    }

    private function normalizeAssetSnapshot(array $assets, array $posted): array
    {
        $items = [];
        foreach ($assets as $asset) {
            $input = (array)($posted[(string)$asset->id] ?? []);
            $condition = (string)($input['condition'] ?? $asset->condition_status);
            if (!array_key_exists($condition, AssetAssignment::conditionOptions())) {
                $condition = $asset->condition_status;
            }
            $items[] = [
                'asset_id' => (int)$asset->id,
                'item_name' => $asset->item_name,
                'quantity' => (float)$asset->quantity,
                'unit_name' => $asset->unit_name,
                'condition' => $condition,
                'note' => trim((string)($input['note'] ?? '')),
                'acknowledged' => !empty($input['acknowledged']),
            ];
        }
        return $items;
    }

    private function currentEmployee(): ?Employees
    {
        return Employees::find()->where(['user_id' => Yii::$app->user->id])->one();
    }

    private function nextNumber(): string
    {
        $thaiYear = (int)date('Y') + 543;
        $prefix = 'HDO-' . substr((string)$thaiYear, -2) . '-';
        $last = Handover::find()
            ->where(['like', 'handover_no', $prefix . '%', false])
            ->orderBy(['handover_no' => SORT_DESC])
            ->select('handover_no')
            ->scalar();
        $sequence = $last ? ((int)substr((string)$last, -4) + 1) : 1;
        return $prefix . str_pad((string)$sequence, 4, '0', STR_PAD_LEFT);
    }
}
