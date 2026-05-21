<?php

namespace app\modules\purchaseV2\services;

use Yii;
use yii\db\ActiveQuery;
use app\components\AppHelper;
use app\models\Categorise;
use app\modules\hr\models\Employees;
use app\modules\purchase\models\Order;
use app\modules\purchaseV2\models\PurchaseRequest;
use app\modules\purchaseV2\models\PurchaseRequestItem;
use app\modules\purchaseV2\models\PurchaseRequestApproval;
use app\modules\purchaseV2\models\PurchaseRequestLog;

class PurchaseMigrationService
{
    public function previewLegacyOrders(int $limit = 20, array $filters = []): array
    {
        $query = $this->buildLegacyOrderQuery($filters, false);
        $total = (clone $query)->count();
        $migratedCount = $this->hasTable(PurchaseRequest::tableName())
            ? (int) PurchaseRequest::find()
                ->where(['in', 'legacy_order_id', (clone $query)->select('id')])
                ->count()
            : 0;
        $models = $query->orderBy(['id' => SORT_DESC])->limit($limit)->all();
        $rows = [];
        foreach ($models as $model) {
            $rows[] = $this->mapLegacyOrder($model);
        }

        return [
            'total' => $total,
            'migrated_count' => $migratedCount,
            'rows' => $rows,
        ];
    }

    public function mapLegacyOrder(Order $legacyOrder): array
    {
        $request = $this->hasTable(PurchaseRequest::tableName())
            ? PurchaseRequest::find()->where(['legacy_order_id' => $legacyOrder->id])->one()
            : null;
        $requester = $legacyOrder->getEmployee();
        $items = $legacyOrder->ListOrderItems();
        $approvals = \app\modules\approve\models\Approve::find()->where(['from_id' => $legacyOrder->id, 'name' => 'purchase'])->count();

        return [
            'legacy_id' => $legacyOrder->id,
            'legacy_ref' => $legacyOrder->ref,
            'request_no' => $legacyOrder->pr_number ?: ($legacyOrder->pq_number ?: ($legacyOrder->po_number ?: ('PRV2-L-' . $legacyOrder->id))),
            'request_title' => $legacyOrder->data_json['order_type_name'] ?? ($legacyOrder->name ?? 'คำขอจัดซื้อ'),
            'requester_name' => $requester?->fullname ?? '-',
            'department_name' => $requester?->departmentName() ?? '-',
            'status' => (int) ($legacyOrder->status ?? 0),
            'status_label' => $legacyOrder->viewStatus()['status_name'] ?? '-',
            'grand_total' => (float) $legacyOrder->calculateVAT()['priceAfterVAT'],
            'item_count' => count($items),
            'approval_count' => (int) $approvals,
            'migrated' => (bool) $request,
            'migrated_request_id' => $request?->id,
            'migrated_request_no' => $request?->request_no,
            'migrated_request_ref' => $request?->ref,
            'legacy_order' => $legacyOrder,
        ];
    }

    public function buildLegacyOrderQuery(array $filters = [], bool $excludeMigrated = false): ActiveQuery
    {
        $query = Order::find()->where(['name' => 'order']);

        if (!empty($filters['id'])) {
            $query->andWhere(['id' => (int) $filters['id']]);
        } else {
            if (!empty($filters['fromId'])) {
                $query->andWhere(['>=', 'id', (int) $filters['fromId']]);
            }

            if (!empty($filters['toId'])) {
                $query->andWhere(['<=', 'id', (int) $filters['toId']]);
            }
        }

        if (!empty($filters['q'])) {
            $query->andWhere([
                'or',
                ['like', 'pr_number', $filters['q']],
                ['like', 'pq_number', $filters['q']],
                ['like', 'po_number', $filters['q']],
                ['like', 'gr_number', $filters['q']],
                ['like', 'ref', $filters['q']],
                ['like', 'name', $filters['q']],
                ['like', 'data_json', $filters['q']],
            ]);
        }

        if ($excludeMigrated) {
            $query->andWhere([
                'not in',
                'id',
                PurchaseRequest::find()
                    ->select('legacy_order_id')
                    ->where(['is not', 'legacy_order_id', null]),
            ]);
        }

        return $query;
    }

    public function migrateLegacyOrder(int $legacyOrderId, ?Employees $actor = null): PurchaseRequest
    {
        $this->assertTargetTablesExist();

        $legacyOrder = Order::find()->where(['id' => $legacyOrderId, 'name' => 'order'])->one();
        if (!$legacyOrder) {
            throw new \RuntimeException('ไม่พบข้อมูลเดิมที่ต้องการย้าย');
        }

        $existing = PurchaseRequest::find()->where(['legacy_order_id' => $legacyOrder->id])->one();
        if ($existing) {
            return $existing;
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $requester = $legacyOrder->getEmployee();
            if (!$requester && !empty($legacyOrder->created_by)) {
                $requester = Employees::find()->where(['user_id' => $legacyOrder->created_by])->one();
            }
            if (!$requester && $actor) {
                $requester = $actor;
            }
            if (!$requester) {
                $requester = Employees::find()->orderBy(['id' => SORT_ASC])->one();
            }

            $request = new PurchaseRequest();
            $request->legacy_order_id = $legacyOrder->id;
            $request->legacy_ref = $legacyOrder->ref;
            $request->legacy_status = $legacyOrder->status;
            $request->migrated_from = 'purchase';
            $request->migrated_at = date('Y-m-d H:i:s');
            $request->migrated_by = $actor?->id;
            $request->ref = PurchaseRequest::generateRef();
            $request->request_no = $this->resolveLegacyRequestNo($legacyOrder);
            $request->request_date = $this->resolveLegacyRequestDate($legacyOrder);
            $request->request_type = $legacyOrder->request_type ?: PurchaseRequest::TYPE_PLANNED;
            $request->request_title = $this->resolveLegacyTitle($legacyOrder);
            $request->summary = $legacyOrder->data_json['comment'] ?? ($legacyOrder->data_json['vendor_name'] ?? null);
            $request->requester_emp_id = $requester?->id;
            $request->department_id = $requester?->department;
            $request->budget_year = $legacyOrder->thai_year ?: AppHelper::YearBudget();
            $request->budget_type_code = $legacyOrder->data_json['pq_budget_type'] ?? null;
            $request->budget_amount = (float) $legacyOrder->calculateVAT()['priceAfterVAT'];
            $request->subtotal_amount = (float) $legacyOrder->SumPo();
            $request->discount_amount = (float) ($legacyOrder->discount_price ?? 0);
            $request->vat_type = $legacyOrder->vatType ?: PurchaseRequest::VAT_NONE;
            $request->vat_amount = (float) ($legacyOrder->calculateVAT()['vatAmount'] ?? 0);
            $request->grand_total = (float) $legacyOrder->calculateVAT()['priceAfterVAT'];
            $request->vendor_id = $legacyOrder->vendor_id;
            $request->vendor_name = $this->resolveVendorTitle($legacyOrder);
            $request->status = $this->mapLegacyStatus((int) ($legacyOrder->status ?? 0));
            $request->current_approval_level = $this->resolveCurrentApprovalLevel($legacyOrder);
            $request->pr_number = $legacyOrder->pr_number;
            $request->pq_number = $legacyOrder->pq_number;
            $request->po_number = $legacyOrder->po_number;
            $request->gr_number = $legacyOrder->gr_number;
            $request->submitted_at = $legacyOrder->created_at;
            $request->approved_at = ((int) ($legacyOrder->status ?? 0) >= PurchaseRequest::STATUS_APPROVED) ? $legacyOrder->updated_at : null;
            $request->ordered_at = $legacyOrder->data_json['po_date'] ?? null;
            $request->received_at = $legacyOrder->data_json['gr_date'] ?? null;
            $request->data_json = [
                'legacy' => [
                    'group_id' => $legacyOrder->group_id,
                    'category_id' => $legacyOrder->category_id,
                    'asset_type' => $legacyOrder->asset_type,
                    'asset_item' => $legacyOrder->asset_item,
                    'code' => $legacyOrder->code,
                    'approve' => $legacyOrder->approve,
                ],
                'vendor_address' => $legacyOrder->data_json['vendor_address'] ?? null,
                'vendor_phone' => $legacyOrder->data_json['vendor_phone'] ?? null,
                'vendor_tax' => $legacyOrder->data_json['vendor_tax'] ?? null,
                'account_name' => $legacyOrder->data_json['account_name'] ?? null,
                'account_number' => $legacyOrder->data_json['account_number'] ?? null,
                'request_type_name' => $legacyOrder->data_json['order_type_name'] ?? null,
            ];

            if (!$request->save(false)) {
                throw new \RuntimeException('ไม่สามารถบันทึกหัวคำขอใหม่ได้');
            }

            $this->migrateItems($request, $legacyOrder);
            PurchaseWorkflowService::importLegacyWorkflow($request, $legacyOrder, $actor);

            PurchaseRequestLog::deleteAll(['request_id' => $request->id, 'action' => 'migrated']);
            $log = new PurchaseRequestLog();
            $log->request_id = $request->id;
            $log->action = 'migrated';
            $log->message = 'ย้ายข้อมูลจากระบบเดิม purchase';
            $log->from_status = $legacyOrder->status;
            $log->to_status = $request->status;
            $log->actor_emp_id = $actor?->id;
            $log->actor_user_id = $actor?->user_id;
            $log->data_json = [
                'legacy_order_id' => $legacyOrder->id,
                'legacy_ref' => $legacyOrder->ref,
            ];
            $log->save(false);

            $transaction->commit();

            return $request;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    public function migrateAll(array $filters = [], int $limit = 0): array
    {
        $this->assertTargetTablesExist();

        $query = $this->buildLegacyOrderQuery($filters, true)->orderBy(['id' => SORT_ASC]);

        if ($limit > 0) {
            $query->limit($limit);
        }

        $results = [];
        foreach ($query->each(100) as $legacyOrder) {
            $results[] = $this->migrateLegacyOrder((int) $legacyOrder->id);
        }

        return $results;
    }

    protected function hasTable(string $tableName): bool
    {
        return Yii::$app->db->schema->getTableSchema($tableName, true) !== null;
    }

    protected function assertTargetTablesExist(): void
    {
        $tables = [
            PurchaseRequest::tableName(),
            PurchaseRequestItem::tableName(),
            PurchaseRequestApproval::tableName(),
            PurchaseRequestLog::tableName(),
        ];
        $missing = [];
        foreach ($tables as $table) {
            if (!$this->hasTable($table)) {
                $missing[] = $table;
            }
        }

        if (!empty($missing)) {
            throw new \RuntimeException(
                'ยังไม่ได้สร้างตาราง purchaseV2: ' . implode(', ', $missing)
                . '. กรุณารัน `php yii migrate --migrationPath=@app/modules/purchaseV2/migrations` ก่อน'
            );
        }
    }

    protected function migrateItems(PurchaseRequest $request, Order $legacyOrder): void
    {
        PurchaseRequestItem::deleteAll(['request_id' => $request->id]);

        $line = 1;
        foreach ($legacyOrder->ListOrderItems() as $legacyItem) {
            $item = new PurchaseRequestItem();
            $item->request_id = $request->id;
            $item->line_no = $line++;
            $item->item_type = $this->resolveItemType($legacyItem);
            $item->item_code = (string) ($legacyItem->asset_item ?? '');
            $item->item_name = $this->resolveItemName($legacyItem);
            $item->detail = $legacyItem->data_json['detail'] ?? $legacyItem->data_json['comment'] ?? null;
            $item->unit_name = $legacyItem->data_json['unit_name'] ?? 'ชิ้น';
            $item->qty = (float) ($legacyItem->qty ?? 0);
            $item->unit_price = (float) ($legacyItem->price ?? 0);
            $item->amount = $item->qty * $item->unit_price;
            $item->budget_type_code = $request->budget_type_code;
            $item->legacy_order_item_id = $legacyItem->id;
            $item->legacy_ref = $legacyItem->ref;
            $item->data_json = $legacyItem->data_json;
            $item->save(false);
        }
    }

    protected function resolveLegacyRequestNo(Order $legacyOrder): string
    {
        $base = trim((string) ($legacyOrder->pr_number ?: $legacyOrder->pq_number ?: $legacyOrder->po_number ?: ''));
        if ($base === '') {
            $base = 'PRV2-L-' . $legacyOrder->id;
        }

        $candidate = $base;
        $counter = 1;
        while (PurchaseRequest::find()->where(['request_no' => $candidate])->exists()) {
            $candidate = $base . '-' . $counter;
            $counter++;
        }

        return $candidate;
    }

    protected function resolveLegacyRequestDate(Order $legacyOrder): ?string
    {
        if (!empty($legacyOrder->data_json['pr_create_date'])) {
            $converted = AppHelper::convertToGregorian($legacyOrder->data_json['pr_create_date']);
            if ($converted) {
                return $converted;
            }
        }

        if (!empty($legacyOrder->created_at)) {
            return substr((string) $legacyOrder->created_at, 0, 10);
        }

        return date('Y-m-d');
    }

    protected function resolveLegacyTitle(Order $legacyOrder): string
    {
        if (!empty($legacyOrder->data_json['order_type_name'])) {
            return (string) $legacyOrder->data_json['order_type_name'];
        }

        if (!empty($legacyOrder->assetType?->title)) {
            return (string) $legacyOrder->assetType->title;
        }

        return 'คำขอจัดซื้อ';
    }

    protected function resolveVendorTitle(Order $legacyOrder): ?string
    {
        $vendorCode = trim((string) ($legacyOrder->vendor_id ?? ''));
        if ($vendorCode !== '') {
            $vendor = Categorise::find()
                ->where(['name' => 'vendor', 'code' => $vendorCode])
                ->one();

            $vendorTitle = trim((string) ($vendor?->title ?? ''));
            if ($vendorTitle !== '') {
                return $vendorTitle;
            }
        }

        $legacyVendorName = trim((string) ($legacyOrder->vendor_name ?? ''));
        if ($legacyVendorName !== '' && $legacyVendorName !== '-') {
            return $legacyVendorName;
        }

        return null;
    }

    protected function mapLegacyStatus(int $status): int
    {
        return match ($status) {
            1 => PurchaseRequest::STATUS_PENDING_APPROVAL,
            2 => PurchaseRequest::STATUS_APPROVED,
            3 => PurchaseRequest::STATUS_ORDERED,
            4 => PurchaseRequest::STATUS_RECEIVED,
            5 => PurchaseRequest::STATUS_STOCKED,
            6 => PurchaseRequest::STATUS_COMPLETED,
            7 => PurchaseRequest::STATUS_CANCELLED,
            default => PurchaseRequest::STATUS_DRAFT,
        };
    }

    protected function resolveCurrentApprovalLevel(Order $legacyOrder): int
    {
        $current = \app\modules\approve\models\Approve::find()
            ->where(['from_id' => $legacyOrder->id, 'name' => 'purchase', 'status' => 'Pending'])
            ->orderBy(['level' => SORT_ASC])
            ->one();

        return $current ? (int) $current->level : (int) ($legacyOrder->status ?: 0);
    }

    protected function resolveItemType($legacyItem): string
    {
        try {
            if (!empty($legacyItem->assetType?->category_id) && $legacyItem->assetType->category_id === 'M25') {
                return 'service';
            }
            if (!empty($legacyItem->group_id) && (int) $legacyItem->group_id === 3) {
                return 'asset';
            }
        } catch (\Throwable $e) {
        }

        return 'consumable';
    }

    protected function resolveItemName($legacyItem): string
    {
        if (!empty($legacyItem->product?->title)) {
            return (string) $legacyItem->product->title;
        }

        if (!empty($legacyItem->assetType?->title)) {
            return (string) $legacyItem->assetType->title;
        }

        return (string) ($legacyItem->data_json['asset_item_name'] ?? $legacyItem->asset_item ?? 'รายการ');
    }
}
