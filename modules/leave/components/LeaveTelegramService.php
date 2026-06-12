<?php

namespace app\modules\leave\components;

use app\models\Categorise;
use Yii;
use yii\helpers\Url;
use app\modules\leave\models\Leave;
use app\modules\approveV2\models\Approve;

class LeaveTelegramService
{
    public function notifyPendingApprove(Approve $approve): bool
    {
        if ($approve->name !== 'leave' || $approve->status !== 'Pending') {
            return false;
        }

        $employee = $approve->employee;
        $user = $employee ? $employee->user : null;
        $chatId = $user ? $user->telegram_id : null;
        $leave  = $approve->leave;
        if (empty($chatId) || !$leave) {
            return false;
        }

        $leaveEmployee = $leave->employee;
        $leaveTypeModel = $leave->leaveType;
        $requesterName = $leaveEmployee ? ($leaveEmployee->fullname ?? '-') : '-';
        $leaveType     = $leaveTypeModel ? ($leaveTypeModel->title ?? 'ใบลา') : 'ใบลา';
        $totalDays     = (float) $leave->total_days;
        $dateRange     = trim(strip_tags((string) $leave->showLeaveDate()));
        $approveData   = $this->normalizeDataJson($approve->data_json ?? null);
        $leaveData     = $this->normalizeDataJson($leave->data_json ?? null);
        $levelLabel    = $approveData['label'] ?? $approve->title ?? 'ผู้อนุมัติ';
        $reason        = $leaveData['reason'] ?? '';

        // ระดับสุดท้าย ใช้คำว่า อนุมัติ/ไม่อนุมัติ, ระดับกลาง ใช้คำว่า เห็นชอบ/ไม่เห็นชอบ
        $isFinal     = (bool) $approve->maxLevel();
        $passLabel   = $isFinal ? '✅ อนุมัติ'    : '✅ เห็นชอบ';
        $rejectLabel = $isFinal ? '❌ ไม่อนุมัติ' : '❌ ไม่เห็นชอบ';

        $lines = [
            '📋 <b>แจ้งเตือนการอนุมัติใบลา</b>',
            '',
            '👤 ผู้ขอ: '    . htmlspecialchars($requesterName, ENT_QUOTES),
            '📌 ประเภท: '   . htmlspecialchars($leaveType, ENT_QUOTES),
            '📅 ช่วงเวลา: ' . htmlspecialchars($dateRange, ENT_QUOTES),
            '🗓 จำนวน: '    . $totalDays . ' วัน',
        ];
        if ($reason !== '') {
            $lines[] = '📝 เหตุผล: ' . htmlspecialchars(mb_substr($reason, 0, 80), ENT_QUOTES);
        }
        $lines[] = '';
        $lines[] = '🔖 ขั้นตอน: ' . htmlspecialchars($levelLabel, ENT_QUOTES);

        // inline keyboard: ปุ่มเห็นชอบ/ไม่เห็นชอบ
        $keyboard = [
            [
                ['text' => $passLabel,   'callback_data' => 'leave_approve:' . $approve->id . ':Pass'],
                ['text' => $rejectLabel, 'callback_data' => 'leave_approve:' . $approve->id . ':Reject'],
            ],
        ];

        return (bool) Yii::$app->telegram->sendDirectMessage(
            $chatId,
            implode("\n", $lines),
            ['reply_markup' => ['inline_keyboard' => $keyboard]]
        );
    }

    public function notifyLeaveResult(Leave $leave, string $status): bool
    {
        $employee = $leave->employee;
        $user = $employee ? $employee->user : null;
        $chatId = $user ? $user->telegram_id : null;
        if (empty($chatId)) {
            return false;
        }

        $leaveTypeModel = $leave->leaveType;
        $statusText = $status === 'Approve' ? 'ได้รับการอนุมัติแล้ว' : 'ไม่ได้รับการอนุมัติ';
        $url = $this->buildMobileUrl(['/mobile/default/leave-request-view', 'id' => $leave->id]);
        $messageLines = [
            'สถานะใบลาของคุณอัปเดตแล้ว',
            'ประเภท: ' . ($leaveTypeModel ? ($leaveTypeModel->title ?? 'ใบลา') : 'ใบลา'),
            'ผลการพิจารณา: ' . $statusText,
            'ช่วงเวลา: ' . trim(strip_tags((string) $leave->showLeaveDate())),
        ];
        if (!$url) {
            $messageLines[] = '';
            $messageLines[] = 'กรุณาเปิดระบบ ERP Mobile เพื่อตรวจสอบรายละเอียด';
        }

        return (bool) Yii::$app->telegram->sendDirectMessage(
            $chatId,
            implode("\n", $messageLines),
            $this->buildWebAppOptions($url, 'ดูรายละเอียดใบลา')
        );
    }

    protected function buildWebAppOptions(?string $url, string $buttonText): array
    {
        if (!$url) {
            return [];
        }

        return [
            'reply_markup' => [
                'inline_keyboard' => [
                    [
                        [
                            'text' => $buttonText,
                            'web_app' => ['url' => $url],
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function buildMobileUrl(array $route): ?string
    {
        $configuredBaseUrl = $this->resolveMiniAppBaseUrl();
        if ($configuredBaseUrl) {
            $relativeUrl = Url::to($route);
            $candidateUrl = $this->joinBaseUrl($configuredBaseUrl, $relativeUrl);
            if ($this->isValidTelegramWebAppUrl($candidateUrl)) {
                return $candidateUrl;
            }
        }

        $absoluteUrl = Url::to($route, true);
        if ($this->isValidTelegramWebAppUrl($absoluteUrl)) {
            return $absoluteUrl;
        }

        Yii::warning('Telegram Mini App URL is invalid for leave notification: ' . $absoluteUrl, __METHOD__);
        return null;
    }

    protected function resolveMiniAppBaseUrl(): ?string
    {
        $setting = Categorise::findOne(['name' => 'telegram_setting']);
        $data = $setting ? $this->normalizeDataJson($setting->data_json) : [];
        $miniAppUrl = trim((string) ($data['mini_app'] ?? ''));
        $enabled = (string) ($data['enable_mini_app'] ?? '0');

        if ($enabled !== '1' || $miniAppUrl === '') {
            return null;
        }

        return rtrim($miniAppUrl, '/');
    }

    protected function joinBaseUrl(string $baseUrl, string $relativeUrl): string
    {
        $baseParts = parse_url($baseUrl);
        $relativeParts = parse_url($relativeUrl);
        if (!$baseParts || empty($baseParts['scheme']) || empty($baseParts['host'])) {
            return $baseUrl;
        }

        $basePath = isset($baseParts['path']) ? rtrim((string) $baseParts['path'], '/') : '';
        $routePath = (string) ($relativeParts['path'] ?? '');
        if ($basePath !== '' && strpos($routePath, $basePath . '/') === 0) {
            $routePath = substr($routePath, strlen($basePath));
        }

        $combinedPath = trim($basePath . '/' . ltrim($routePath, '/'), '/');
        $url = $baseParts['scheme'] . '://' . $baseParts['host'];
        if (!empty($baseParts['port'])) {
            $url .= ':' . $baseParts['port'];
        }
        if ($combinedPath !== '') {
            $url .= '/' . $combinedPath;
        }
        if (!empty($relativeParts['query'])) {
            $url .= '?' . $relativeParts['query'];
        }

        return $url;
    }

    protected function isValidTelegramWebAppUrl(?string $url): bool
    {
        if (!$url) {
            return false;
        }

        $parts = parse_url($url);
        if (!$parts || ($parts['scheme'] ?? '') !== 'https') {
            return false;
        }

        $host = strtolower((string) ($parts['host'] ?? ''));
        if ($host === '' || $host === 'localhost') {
            return false;
        }

        if (filter_var($host, FILTER_VALIDATE_IP)
            && !filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return false;
        }

        return true;
    }

    protected function normalizeDataJson($data): array
    {
        if (is_array($data)) {
            return $data;
        }

        if (is_string($data) && $data !== '') {
            return json_decode($data, true) ?: [];
        }

        return [];
    }
}
