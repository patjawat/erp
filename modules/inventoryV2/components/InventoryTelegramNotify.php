<?php

namespace app\modules\inventoryV2\components;

use app\modules\hr\models\Employees;
use app\modules\inventoryV2\models\StockDetail;
use app\modules\inventoryV2\models\StockOrder;
use app\modules\inventoryV2\models\Warehouse;
use app\modules\usermanager\models\User;
use Yii;

class InventoryTelegramNotify
{
    private const OPT = [
        'parse_mode' => 'HTML',
        'disable_web_page_preview' => true,
    ];

    public static function notifyRequisitionCreated(StockOrder $model): void
    {
        if (!self::isRequisition($model)) {
            return;
        }

        $approver = self::employeeBySignature($model, 'approver');
        if (!$approver) {
            return;
        }

        self::sendToEmployee($approver, self::buildPendingApprovalMessage($model));
    }

    public static function notifyRequisitionApproved(StockOrder $model): void
    {
        if (!self::isRequisition($model)) {
            return;
        }

        self::sendToRequester($model, self::buildRequesterApprovedMessage($model));
        self::sendToMainWarehouseOfficers($model, self::buildWarehouseApprovedMessage($model));
    }

    public static function notifyRequisitionDisbursed(StockOrder $model): void
    {
        if (!self::isRequisition($model)) {
            return;
        }

        self::sendToRequester($model, self::buildDisbursedMessage($model));
    }

    private static function isRequisition(StockOrder $model): bool
    {
        return $model->order_type === StockOrder::ORDER_TYPE_OUT
            && $model->source_type === 'REQUEST';
    }

    private static function sendToRequester(StockOrder $model, string $messageHtml): bool
    {
        $requester = self::employeeBySignature($model, 'requester');
        if (!$requester) {
            $requester = $model->getRequesterEmployee();
        }

        return $requester ? self::sendToEmployee($requester, $messageHtml) : false;
    }

    private static function sendToMainWarehouseOfficers(StockOrder $model, string $messageHtml): void
    {
        try {
            $warehouse = $model->mainWarehouse ?: Warehouse::findOne($model->main_warehouse_id);
            if (!$warehouse) {
                return;
            }

            $data = self::normalizeDataJson($warehouse->data_json ?? null);
            $userIds = self::normalizeIdList($data['officer'] ?? []);
            if ($userIds === []) {
                return;
            }

            $users = User::find()
                ->where(['id' => $userIds, 'status' => User::STATUS_ACTIVE])
                ->andWhere(['IS NOT', 'telegram_id', null])
                ->andWhere(['<>', 'telegram_id', ''])
                ->all();

            foreach ($users as $user) {
                self::sendToChatId($user->telegram_id ?? '', $messageHtml);
            }
        } catch (\Throwable $e) {
            Yii::error('InventoryTelegramNotify sendToMainWarehouseOfficers failed: ' . $e->getMessage(), __METHOD__);
        }
    }

    private static function sendToEmployee(Employees $employee, string $messageHtml): bool
    {
        try {
            $user = $employee->user;
            if (!$user) {
                return false;
            }

            return self::sendToChatId($user->telegram_id ?? '', $messageHtml);
        } catch (\Throwable $e) {
            Yii::error('InventoryTelegramNotify sendToEmployee failed: ' . $e->getMessage(), __METHOD__);
            return false;
        }
    }

    private static function sendToChatId($chatId, string $messageHtml): bool
    {
        $chatId = trim((string) $chatId);
        if ($chatId === '') {
            return false;
        }

        try {
            return (bool) Yii::$app->telegram->sendDirectMessage($chatId, $messageHtml, self::OPT);
        } catch (\Throwable $e) {
            Yii::error('InventoryTelegramNotify sendToChatId failed: ' . $e->getMessage(), __METHOD__);
            return false;
        }
    }

    private static function buildPendingApprovalMessage(StockOrder $model): string
    {
        $lines = [
            '📦 <b>ใบขอเบิกวัสดุรออนุมัติ</b>',
            'เลขที่: ' . self::esc($model->order_no),
            'ผู้ขอ: ' . self::esc(self::requesterName($model)),
            'คลังที่จ่าย: ' . self::esc(self::warehouseName($model->mainWarehouse, $model->main_warehouse_id)),
            'คลังที่รับ: ' . self::esc(self::warehouseName($model->subWarehouse, $model->sub_warehouse_id)),
            'วันที่ขอ: ' . self::esc(self::formatDate($model->order_date)),
            'จำนวนรายการ: ' . self::detailCount($model) . ' รายการ',
        ];

        self::appendReason($lines, $model);
        self::appendDetailSummary($lines, $model);

        return implode("\n", $lines);
    }

    private static function buildRequesterApprovedMessage(StockOrder $model): string
    {
        $lines = [
            '✅ <b>ผลการอนุมัติใบขอเบิกวัสดุ</b>',
            'เลขที่: ' . self::esc($model->order_no),
            'ผลการพิจารณา: อนุมัติแล้ว',
            'ผู้อนุมัติ: ' . self::esc(self::signatureName($model, 'approver')),
            'สถานะต่อไป: รอคลังหลักดำเนินการจ่าย',
        ];

        self::appendDetailSummary($lines, $model);

        return implode("\n", $lines);
    }

    private static function buildWarehouseApprovedMessage(StockOrder $model): string
    {
        $lines = [
            '📦 <b>ใบขอเบิกวัสดุรอจ่ายจากคลังหลัก</b>',
            'เลขที่: ' . self::esc($model->order_no),
            'ผู้ขอ: ' . self::esc(self::requesterName($model)),
            'คลังที่จ่าย: ' . self::esc(self::warehouseName($model->mainWarehouse, $model->main_warehouse_id)),
            'คลังที่รับ: ' . self::esc(self::warehouseName($model->subWarehouse, $model->sub_warehouse_id)),
            'วันที่อนุมัติ: ' . self::esc(self::approvalDate($model)),
            'จำนวนรายการ: ' . self::detailCount($model) . ' รายการ',
        ];

        self::appendReason($lines, $model);
        self::appendDetailSummary($lines, $model);

        return implode("\n", $lines);
    }

    private static function buildDisbursedMessage(StockOrder $model): string
    {
        $lines = [
            '✅ <b>คลังหลักจ่ายพัสดุแล้ว</b>',
            'เลขที่: ' . self::esc($model->order_no),
            'คลังที่จ่าย: ' . self::esc(self::warehouseName($model->mainWarehouse, $model->main_warehouse_id)),
            'คลังที่รับ: ' . self::esc(self::warehouseName($model->subWarehouse, $model->sub_warehouse_id)),
            'วันที่จ่าย: ' . self::esc(self::formatTimestamp($model->getDisbursementDate())),
            'ผู้จ่าย: ' . self::esc(self::signatureName($model, 'disbursing')),
            'จำนวนรายการ: ' . self::detailCount($model) . ' รายการ',
        ];

        self::appendDetailSummary($lines, $model);

        return implode("\n", $lines);
    }

    private static function appendReason(array &$lines, StockOrder $model): void
    {
        $reason = self::shorten($model->getIssueReason(), 120);
        if ($reason !== '') {
            $lines[] = 'วัตถุประสงค์: ' . self::esc($reason);
        }
    }

    private static function appendDetailSummary(array &$lines, StockOrder $model): void
    {
        $summary = self::detailSummary($model);
        if ($summary !== '') {
            $lines[] = 'รายการหลัก: ' . self::esc($summary);
        }
    }

    private static function employeeBySignature(StockOrder $model, string $role): ?Employees
    {
        $empId = $model->getIssueSignatureEmpId($role);
        if (!$empId) {
            return null;
        }

        return Employees::findOne($empId);
    }

    private static function requesterName(StockOrder $model): string
    {
        $requester = $model->getIssueSignature('requester');
        if (!empty($requester['name'])) {
            return $requester['name'];
        }

        $employee = $model->getRequesterEmployee();
        return $employee ? (string) ($employee->fullname ?? '-') : '-';
    }

    private static function signatureName(StockOrder $model, string $role): string
    {
        $signature = $model->getIssueSignature($role);
        return trim((string) ($signature['name'] ?? '')) ?: '-';
    }

    private static function approvalDate(StockOrder $model): string
    {
        $signature = $model->getIssueSignature('approver');
        return self::formatDateTime($signature['date'] ?? '');
    }

    private static function warehouseName($warehouse, $warehouseId): string
    {
        if ($warehouse) {
            return (string) ($warehouse->warehouse_name ?? '-');
        }

        if ($warehouseId) {
            $model = Warehouse::findOne($warehouseId);
            return $model ? (string) $model->warehouse_name : '-';
        }

        return '-';
    }

    private static function detailCount(StockOrder $model): int
    {
        return (int) StockDetail::find()
            ->where(['stock_order_id' => $model->id])
            ->count();
    }

    private static function detailSummary(StockOrder $model): string
    {
        $rows = StockDetail::find()
            ->with(['item'])
            ->where(['stock_order_id' => $model->id])
            ->limit(4)
            ->all();

        if ($rows === []) {
            return '';
        }

        $parts = [];
        foreach ($rows as $detail) {
            $itemName = $detail->item ? (string) ($detail->item->item_name ?? $detail->item->title ?? '') : '';
            $label = trim($itemName) !== '' ? $itemName : (string) $detail->item_code;
            $unit = $detail->item && method_exists($detail->item, 'getUnitName') ? (string) ($detail->item->getUnitName() ?: '') : '';
            $qty = rtrim(rtrim(number_format((float) $detail->qty, 2, '.', ''), '0'), '.');
            $parts[] = trim($label . ' ' . $qty . ($unit !== '' ? ' ' . $unit : ''));
        }

        $count = self::detailCount($model);
        if ($count > count($rows)) {
            $parts[] = 'และอีก ' . ($count - count($rows)) . ' รายการ';
        }

        return implode(', ', $parts);
    }

    private static function formatDate($value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        $timestamp = is_numeric($value) ? (int) $value : strtotime((string) $value);
        return $timestamp ? date('d/m/Y', $timestamp) : (string) $value;
    }

    private static function formatDateTime($value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        $timestamp = is_numeric($value) ? (int) $value : strtotime((string) $value);
        return $timestamp ? date('d/m/Y H:i', $timestamp) : (string) $value;
    }

    private static function formatTimestamp($timestamp): string
    {
        $timestamp = (int) $timestamp;
        return $timestamp > 0 ? date('d/m/Y H:i', $timestamp) : '-';
    }

    private static function normalizeDataJson($data): array
    {
        if (is_array($data)) {
            return $data;
        }

        if (is_string($data) && $data !== '') {
            $decoded = json_decode($data, true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    private static function normalizeIdList($value): array
    {
        if (is_string($value)) {
            $value = preg_split('/\s*,\s*/', $value, -1, PREG_SPLIT_NO_EMPTY);
        }

        if (!is_array($value)) {
            return [];
        }

        $ids = [];
        foreach ($value as $item) {
            if (is_numeric($item) && (int) $item > 0) {
                $ids[] = (int) $item;
            }
        }

        return array_values(array_unique($ids));
    }

    private static function shorten(?string $text, int $limit): string
    {
        $text = trim((string) $text);
        if ($text === '') {
            return '';
        }

        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            return mb_strlen($text, 'UTF-8') > $limit
                ? mb_substr($text, 0, $limit, 'UTF-8') . '...'
                : $text;
        }

        return strlen($text) > $limit ? substr($text, 0, $limit) . '...' : $text;
    }

    private static function esc(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
