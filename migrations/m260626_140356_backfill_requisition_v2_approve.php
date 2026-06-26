<?php

use yii\db\Expression;
use yii\db\Migration;
use yii\db\Query;

class m260626_140356_backfill_requisition_v2_approve extends Migration
{
    private const APPROVE_NAME = 'requisition_v2';

    public function safeUp()
    {
        $this->ensureIndex('idx-approve-name-from_id', '{{%approve}}', ['name', 'from_id']);
        $this->ensureIndex('idx-approve-name-emp-status', '{{%approve}}', ['name', 'emp_id', 'status']);

        $rows = (new Query())
            ->select([
                'id',
                'order_no',
                'order_date',
                'main_warehouse_id',
                'sub_warehouse_id',
                'status',
                'approver_emp_id' => new Expression("CAST(JSON_UNQUOTE(JSON_EXTRACT(data_json, '$.issue_approver.emp_id')) AS UNSIGNED)"),
                'approver_date' => new Expression("JSON_UNQUOTE(JSON_EXTRACT(data_json, '$.issue_approver.date'))"),
            ])
            ->from('{{%stock_order}}')
            ->where([
                'source_type' => 'REQUEST',
                'order_type' => 'OUT',
            ])
            ->andWhere(['<>', 'status', 'DRAFT'])
            ->andWhere(['like', 'data_json', 'issue_approver'])
            ->andWhere("JSON_UNQUOTE(JSON_EXTRACT(data_json, '$.issue_approver.emp_id')) IS NOT NULL")
            ->andWhere("JSON_UNQUOTE(JSON_EXTRACT(data_json, '$.issue_approver.emp_id')) <> ''")
            ->all($this->db);

        foreach ($rows as $row) {
            $empId = (int) ($row['approver_emp_id'] ?? 0);
            if ($empId <= 0) {
                continue;
            }

            $approveStatus = $this->mapStatus($row['status'] ?? null);
            if ($approveStatus === null) {
                continue;
            }

            $fromId = (string) $row['id'];
            $existing = (new Query())
                ->from('{{%approve}}')
                ->where([
                    'name' => self::APPROVE_NAME,
                    'from_id' => $fromId,
                ])
                ->orderBy(['level' => SORT_ASC, 'id' => SORT_ASC])
                ->one($this->db);

            $existingData = $this->normalizeDataJson($existing['data_json'] ?? null);
            $approveDate = $this->resolveApproveDate($approveStatus, $row['approver_date'] ?? null, $existingData);
            $dataJson = [
                'label' => 'อนุมัติเบิกวัสดุ',
                'order_no' => $row['order_no'],
                'main_warehouse_id' => $row['main_warehouse_id'] !== null ? (int) $row['main_warehouse_id'] : null,
                'sub_warehouse_id' => $row['sub_warehouse_id'] !== null ? (int) $row['sub_warehouse_id'] : null,
                'approve_date' => $approveDate,
            ];

            $values = [
                'name' => self::APPROVE_NAME,
                'from_id' => $fromId,
                'emp_id' => $empId,
                'title' => $row['order_no'],
                'level' => 1,
                'status' => $approveStatus,
                'data_json' => json_encode($dataJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            if ($existing) {
                if (empty($existing['created_at'])) {
                    $values['created_at'] = $this->createdAtFromOrderDate($row['order_date'] ?? null);
                }
                $this->update('{{%approve}}', $values, ['id' => $existing['id']]);
            } else {
                $values['created_at'] = $this->createdAtFromOrderDate($row['order_date'] ?? null);
                $this->insert('{{%approve}}', $values);
            }
        }
    }

    public function safeDown()
    {
        $this->delete('{{%approve}}', ['name' => self::APPROVE_NAME]);
        $this->dropIndexIfExists('idx-approve-name-emp-status', '{{%approve}}');
        $this->dropIndexIfExists('idx-approve-name-from_id', '{{%approve}}');
    }

    private function mapStatus($status): ?string
    {
        switch ((string) $status) {
            case 'PENDING':
                return 'Pending';
            case 'APPROVED':
            case 'CONFIRMED':
                return 'Pass';
            case 'CANCELLED':
                return 'Reject';
            default:
                return null;
        }
    }

    private function resolveApproveDate(string $approveStatus, $approverDate, array $existingData): ?string
    {
        if ($approveStatus === 'Pending') {
            return null;
        }

        $approverDate = trim((string) $approverDate);
        if ($approverDate !== '' && strtolower($approverDate) !== 'null') {
            return $approverDate;
        }

        $existingDate = trim((string) ($existingData['approve_date'] ?? ''));
        return $existingDate !== '' ? $existingDate : null;
    }

    private function createdAtFromOrderDate($orderDate): ?string
    {
        $orderDate = trim((string) $orderDate);
        if ($orderDate === '') {
            return null;
        }
        return substr($orderDate, 0, 10) . ' 00:00:00';
    }

    private function normalizeDataJson($value): array
    {
        if (is_array($value)) {
            return $value;
        }
        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }
        return [];
    }

    private function ensureIndex(string $name, string $table, array $columns): void
    {
        try {
            $this->createIndex($name, $table, $columns);
        } catch (\Throwable $e) {
        }
    }

    private function dropIndexIfExists(string $name, string $table): void
    {
        try {
            $this->dropIndex($name, $table);
        } catch (\Throwable $e) {
        }
    }
}
