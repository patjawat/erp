<?php

namespace app\modules\development\components;

use Yii;
use app\models\Categorise;
use yii\helpers\Html;
use yii\helpers\Url;
use app\components\ThaiDateHelper;
use app\modules\hr\models\Development;
use app\modules\approveV2\models\Approve;

class DevelopmentTelegramService
{
    public function notifyPendingApprove(Development $development, Approve $approve): bool
    {
        if ($approve->name !== 'development' || (int) $approve->level !== 1 || $approve->status !== 'Pending') {
            return false;
        }

        $employee = $approve->employee;
        $user = $employee ? $employee->user : null;
        $chatId = $user ? trim((string) $user->telegram_id) : '';
        if ($chatId === '') {
            return false;
        }

        $options = [
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
        ];

        $mobileUrl = $this->buildMobileUrl(['/development/default/mine']);
        if ($this->isValidTelegramWebAppUrl($mobileUrl)) {
            $options['reply_markup'] = [
                'inline_keyboard' => [
                    [
                        [
                            'text' => 'เปิดรายการขอไปราชการ',
                            'web_app' => [
                                'url' => $mobileUrl,
                            ],
                        ],
                    ],
                ],
            ];
        }

        return (bool) Yii::$app->telegram->sendDirectMessage(
            $chatId,
            $this->buildPendingApproveMessage($development),
            $options
        );
    }

    protected function buildPendingApproveMessage(Development $development): string
    {
        $requester = $development->createdByEmp;
        $data = $this->normalizeDataJson($development->data_json);
        $location = $this->resolveLocation($data);

        $lines = [
            '🧾 <b>คำขออนุญาตไปราชการรอพิจารณา</b>',
            'เรื่อง: ' . Html::encode((string) ($development->topic ?: '-')),
            'วันที่เดินทาง: ' . Html::encode($this->formatTravelDate($development)),
            'สถานที่: ' . Html::encode($location),
            'ผู้ขอ: ' . Html::encode((string) ($requester?->fullname ?? '-')),
            'สถานะ: รอผู้มีอำนาจพิจารณา',
        ];

        return implode("\n", $lines);
    }

    protected function formatTravelDate(Development $development): string
    {
        $dateStart = (string) ($development->date_start ?? '');
        $dateEnd = (string) ($development->date_end ?? '');

        if ($dateStart === '' && $dateEnd === '') {
            return '-';
        }

        if ($dateStart === '') {
            return ThaiDateHelper::formatThaiDate($dateEnd, 'long', 'short');
        }

        if ($dateEnd === '' || $dateStart === $dateEnd) {
            return ThaiDateHelper::formatThaiDate($dateStart, 'long', 'short');
        }

        return ThaiDateHelper::formatThaiDate($dateStart, 'long', 'short') . ' ถึง ' . ThaiDateHelper::formatThaiDate($dateEnd, 'long', 'short');
    }

    protected function resolveLocation(array $data): string
    {
        $parts = array_filter([
            trim((string) ($data['location_org'] ?? '')),
            trim((string) ($data['location'] ?? '')),
            trim((string) ($data['province_name'] ?? '')),
        ]);

        return $parts ? implode(' ', array_unique($parts)) : '-';
    }

    protected function buildMobileUrl(array $route): ?string
    {
        $configuredBaseUrl = $this->resolveMiniAppBaseUrl();
        if (!$configuredBaseUrl) {
            return null;
        }

        return $this->joinBaseUrl($configuredBaseUrl, Url::to($route));
    }

    protected function resolveMiniAppBaseUrl(): ?string
    {
        $setting = Categorise::findOne(['name' => 'telegram_setting']);
        $data = $setting ? $this->normalizeDataJson($setting->data_json) : [];
        $miniAppUrl = trim((string) ($data['mini_app_base_url'] ?? $data['mini_app'] ?? ''));
        $enabled = (string) ($data['enable_mini_app'] ?? '0');

        if ($enabled !== '1' || !$this->isValidTelegramWebAppUrl($miniAppUrl)) {
            return null;
        }

        return $miniAppUrl;
    }

    protected function joinBaseUrl(string $baseUrl, string $relativeUrl): string
    {
        $baseParts = parse_url($baseUrl);
        $relativeParts = parse_url($relativeUrl);
        if (!$baseParts || empty($baseParts['scheme']) || empty($baseParts['host'])) {
            return $relativeUrl;
        }

        $basePath = rtrim((string) ($baseParts['path'] ?? ''), '/');
        $relativePath = '/' . ltrim((string) ($relativeParts['path'] ?? $relativeUrl), '/');
        $combinedPath = preg_replace('#/+#', '/', $basePath . $relativePath);

        $url = $baseParts['scheme'] . '://' . $baseParts['host'];
        if (!empty($baseParts['port'])) {
            $url .= ':' . $baseParts['port'];
        }
        $url .= $combinedPath ?: '/';

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
        if (!$parts || ($parts['scheme'] ?? '') !== 'https' || empty($parts['host'])) {
            return false;
        }

        return filter_var($parts['host'], FILTER_VALIDATE_IP) === false;
    }

    protected function normalizeDataJson($data): array
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
}
