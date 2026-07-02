<?php

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use kartik\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\View;

/** @var yii\web\View $this */
/** @var app\models\Categorise $model */
/** @var array $data */
/** @var app\modules\usermanager\models\User[] $bindings */
/** @var array $notificationTestScenarios */
/** @var int $activeUserCount */
/** @var int $linkedUserCount */
/** @var string $defaultWebhookUrl */

$this->title = 'Telegram Personal Notification';
$this->params['breadcrumbs'][] = $this->title;

$data = is_array($data ?? null) ? $data : [];
$bindings = is_array($bindings ?? null) ? $bindings : [];
$notificationTestScenarios = is_array($notificationTestScenarios ?? null) ? $notificationTestScenarios : [];

$botTokenValue = trim((string) ($data['bot_token'] ?? ''));
$botUsernameValue = ltrim(trim((string) ($data['bot_username'] ?? '')), '@');
$webhookUrlValue = trim((string) ($data['webhook_url'] ?? $defaultWebhookUrl ?? ''));
$miniAppBaseUrlValue = trim((string) ($data['mini_app_base_url'] ?? $data['mini_app'] ?? ''));
$miniAppEnabled = (string) ($data['enable_mini_app'] ?? '0') === '1';
$notificationEnabled = (string) ($data['enable_notification'] ?? '1') === '1';
$linkedPercent = $activeUserCount > 0 ? round(($linkedUserCount / $activeUserCount) * 100) : 0;

$botDeepLink = $botUsernameValue !== '' ? 'https://t.me/' . $botUsernameValue : '';
$botQrDataUri = '';
if ($botDeepLink !== '') {
    $botQrResult = Builder::create()
        ->writer(new PngWriter())
        ->data($botDeepLink)
        ->size(280)
        ->margin(10)
        ->build();
    $botQrDataUri = 'data:image/png;base64,' . base64_encode($botQrResult->getString());
}

$bindingRows = [];
foreach ($bindings as $binding) {
    $employee = $binding->employee ?? null;
    $bindingRows[] = [
        'name' => trim((string) ($binding->fullname ?: ($employee ? ($employee->fullname ?? '') : $binding->username))),
        'username' => (string) ($binding->username ?? ''),
        'department' => $employee ? trim((string) ($employee->departmentName() ?: '-')) : '-',
        'position' => $employee ? trim((string) ($employee->positionName() ?: '-')) : '-',
        'telegramId' => trim((string) ($binding->telegram_id ?? '')),
    ];
}

$testUserOptions = [];
foreach ($bindings as $binding) {
    $employee = $binding->employee ?? null;
    $employeeName = trim((string) ($binding->fullname ?: ($employee ? ($employee->fullname ?? '') : $binding->username)));
    $departmentName = $employee ? trim((string) ($employee->departmentName() ?: '-')) : '-';
    $testUserOptions[(int) $binding->id] = ($employeeName !== '' ? $employeeName : $binding->username) . ' (' . $departmentName . ')';
}

$testScenarioOptions = [];
foreach ($notificationTestScenarios as $scenarioKey => $scenario) {
    $testScenarioOptions[(string) $scenarioKey] = (string) ($scenario['label'] ?? $scenarioKey);
}
$defaultTestScenario = '';
if (!empty($testScenarioOptions)) {
    reset($testScenarioOptions);
    $defaultTestScenario = (string) key($testScenarioOptions);
}
$testScenarioSelectOptions = [
    'id' => 'telegram-test-scenario',
    'class' => 'form-select',
    'disabled' => empty($testScenarioOptions),
];
if (empty($testScenarioOptions)) {
    $testScenarioSelectOptions['prompt'] = 'ยังไม่มี scenario สำหรับทดสอบ';
}

$this->registerCss(<<<CSS
.telegram-personal-shell {
    --ink-1: #1a202c;
    --ink-2: #4a5568;
    --ink-3: #718096;
    --ink-4: #a0aec0;

    --surface: #ffffff;
    --surface-2: #f7f9fc;
    --surface-3: #eef2f7;
    --surface-hover: #f1f5f9;

    --line: rgba(15, 23, 42, 0.08);
    --line-strong: rgba(15, 23, 42, 0.14);

    --primary: #0d6efd;
    --primary-ink: #0a58ca;
    --primary-soft: rgba(13, 110, 253, 0.08);
    --primary-line: rgba(13, 110, 253, 0.22);

    --teal: #0f766e;
    --teal-soft: rgba(15, 118, 110, 0.10);
    --violet: #6d28d9;
    --violet-soft: rgba(109, 40, 217, 0.10);

    --success: #15803d;
    --success-soft: rgba(21, 128, 61, 0.10);
    --warning: #b45309;
    --warning-soft: rgba(180, 83, 9, 0.10);
    --danger: #b91c1c;
    --danger-soft: rgba(185, 28, 28, 0.10);

    --radius: 10px;
    --radius-sm: 8px;
    --radius-xs: 6px;

    --shadow-1: 0 1px 2px rgba(15, 23, 42, 0.04), 0 1px 1px rgba(15, 23, 42, 0.03);

    --ease: cubic-bezier(0.16, 1, 0.3, 1);
    --t-fast: 120ms;
    --t-mid: 180ms;

    color: var(--ink-1);
}

.telegram-personal-shell .setting-panel,
.telegram-personal-shell .summary-tile {
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: var(--radius);
    box-shadow: var(--shadow-1);
}

.telegram-personal-shell .telegram-hero {
    border-radius: var(--radius);
    background: var(--surface-2);
    border: 1px solid var(--line);
    box-shadow: var(--shadow-1);
}

.telegram-personal-shell .bot-mark {
    width: 3.25rem;
    height: 3.25rem;
    border-radius: var(--radius-sm);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: var(--primary);
    background: var(--primary-soft);
    flex: 0 0 auto;
}

.telegram-personal-shell .bot-mark.is-teal { color: var(--teal); background: var(--teal-soft); }
.telegram-personal-shell .bot-mark.is-violet { color: var(--violet); background: var(--violet-soft); }
.telegram-personal-shell .bot-mark.is-success { color: var(--success); background: var(--success-soft); }

.telegram-personal-shell .panel-icon {
    width: 2.1rem;
    height: 2.1rem;
    border-radius: var(--radius-xs);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    font-size: .85rem;
    color: var(--primary);
    background: var(--primary-soft);
}

.telegram-personal-shell .panel-icon.is-teal { color: var(--teal); background: var(--teal-soft); }
.telegram-personal-shell .panel-icon.is-violet { color: var(--violet); background: var(--violet-soft); }
.telegram-personal-shell .panel-icon.is-success { color: var(--success); background: var(--success-soft); }

.telegram-personal-shell .panel-head-text {
    display: flex;
    align-items: flex-start;
    gap: .65rem;
}

.telegram-personal-shell .page-title {
    font-size: 1.45rem;
    line-height: 1.25;
    font-weight: 700;
    text-wrap: balance;
}

.telegram-personal-shell .panel-title {
    font-size: 1.05rem;
    font-weight: 600;
    margin: 0;
}

.telegram-personal-shell .panel-desc,
.telegram-personal-shell .muted-note {
    color: var(--ink-2);
    font-size: .92rem;
    line-height: 1.55;
}

.telegram-personal-shell .status-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: .35rem;
    border-radius: 999px;
    padding: .38rem .72rem;
    font-size: .8rem;
    font-weight: 600;
    white-space: nowrap;
    transition: background-color var(--t-mid) var(--ease), color var(--t-mid) var(--ease);
}

.telegram-personal-shell .is-success { background: var(--success-soft); color: var(--success); }
.telegram-personal-shell .is-warning { background: var(--warning-soft); color: var(--warning); }
.telegram-personal-shell .is-danger { background: var(--danger-soft); color: var(--danger); }
.telegram-personal-shell .is-neutral { background: var(--surface-3); color: var(--ink-2); }

.telegram-personal-shell .summary-value {
    font-size: 1.32rem;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
}

.telegram-personal-shell .flow-list {
    position: relative;
}

.telegram-personal-shell .flow-list::before {
    content: '';
    position: absolute;
    left: 1rem;
    top: 1rem;
    bottom: 1rem;
    width: 2px;
    background: var(--line);
}

.telegram-personal-shell .flow-step {
    position: relative;
    display: flex;
    align-items: flex-start;
    gap: .75rem;
}

.telegram-personal-shell .step-dot {
    position: relative;
    z-index: 1;
    width: 2rem;
    height: 2rem;
    border-radius: var(--radius-xs);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--primary-soft);
    color: var(--primary);
    flex: 0 0 auto;
}

.telegram-personal-shell .step-dot.is-teal { color: var(--teal); background: var(--teal-soft); }
.telegram-personal-shell .step-dot.is-violet { color: var(--violet); background: var(--violet-soft); }
.telegram-personal-shell .step-dot.is-success { color: var(--success); background: var(--success-soft); }

.telegram-personal-shell .form-control,
.telegram-personal-shell .form-select,
.telegram-personal-shell .btn {
    min-height: 44px;
    transition: border-color var(--t-mid) var(--ease), box-shadow var(--t-mid) var(--ease), background-color var(--t-mid) var(--ease), transform var(--t-fast) var(--ease);
}

.telegram-personal-shell .btn:active {
    transform: translateY(1px);
}

.telegram-personal-shell .binding-table-wrap {
    border: 1px solid var(--line);
    border-radius: var(--radius-sm);
    overflow: hidden;
}

.telegram-personal-shell .binding-table thead th {
    background: var(--surface-2);
    color: var(--ink-2);
    font-size: .82rem;
    font-weight: 600;
    white-space: nowrap;
}

.telegram-personal-shell .binding-table tbody td {
    vertical-align: middle;
}

.telegram-personal-shell .binding-cards {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: .6rem;
}

.telegram-personal-shell .binding-card {
    border: 1px solid var(--line);
    border-radius: var(--radius-sm);
    padding: .75rem;
    background: var(--surface);
}

.telegram-personal-shell .binding-card__head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: .5rem;
    margin-bottom: .45rem;
}

.telegram-personal-shell .binding-card__name {
    font-weight: 600;
    color: var(--ink-1);
    font-size: .92rem;
}

.telegram-personal-shell .binding-card__username {
    color: var(--ink-3);
    font-size: .78rem;
}

.telegram-personal-shell .binding-card__meta {
    color: var(--ink-2);
    font-size: .84rem;
    margin-bottom: .5rem;
}

.telegram-personal-shell .binding-card__chat {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-top: 1px dashed var(--line);
    padding-top: .5rem;
}

.telegram-personal-shell .binding-card__chat code {
    font-size: .8rem;
    color: var(--ink-2);
}

.telegram-personal-shell .empty-state {
    border: 1px dashed var(--line-strong);
    border-radius: var(--radius);
    background: var(--surface-2);
}

.telegram-personal-shell .bot-qr-card {
    display: flex;
    align-items: center;
    gap: .75rem;
    border: 1px solid var(--line);
    border-radius: var(--radius-sm);
    background: var(--surface);
    padding: .65rem;
}

.telegram-personal-shell .bot-qr-card__image {
    border: 1px solid var(--line);
    border-radius: var(--radius-xs);
    flex: 0 0 auto;
    display: block;
}

.telegram-personal-shell .bot-qr-card__trigger {
    display: block;
    padding: 0;
    border: 0;
    background: none;
    line-height: 0;
    flex: 0 0 auto;
    cursor: zoom-in;
    border-radius: var(--radius-xs);
    transition: opacity var(--t-fast) var(--ease);
}

.telegram-personal-shell .bot-qr-card__trigger:hover {
    opacity: .85;
}

.telegram-personal-shell .bot-qr-card__trigger:focus-visible {
    outline: 2px solid var(--primary);
    outline-offset: 2px;
}

.telegram-personal-shell .bot-qr-card__body {
    min-width: 0;
}

.telegram-personal-shell .bot-qr-card__label {
    font-size: .8rem;
    font-weight: 600;
    color: var(--ink-1);
}

.telegram-personal-shell .bot-qr-card__link {
    font-size: .74rem;
    color: var(--ink-2);
    line-height: 1.4;
    overflow-wrap: anywhere;
}

.telegram-personal-shell .bot-qr-card__empty {
    display: flex;
    align-items: center;
    gap: .6rem;
    color: var(--ink-2);
    font-size: .82rem;
    padding: .3rem;
}

.telegram-personal-shell .bot-qr-card__empty i {
    font-size: 1.4rem;
    color: var(--ink-3);
    flex: 0 0 auto;
}

.telegram-personal-shell .test-suite {
    border-top: 1px solid var(--line);
    margin-top: 1.25rem;
    padding-top: 1.25rem;
}

.telegram-personal-shell .test-actions {
    display: flex;
    flex-wrap: wrap;
    gap: .5rem;
}

.telegram-personal-shell .scenario-preview {
    min-height: 100%;
    border: 1px solid var(--line-strong);
    border-radius: var(--radius);
    background: var(--surface-2);
    padding: 1rem;
    transition: border-color var(--t-mid) var(--ease), background-color var(--t-mid) var(--ease), box-shadow var(--t-mid) var(--ease);
}

.telegram-personal-shell .scenario-preview.is-updated {
    animation: scenario-preview-pulse 320ms var(--ease);
}

.telegram-personal-shell .scenario-chip {
    display: inline-flex;
    align-items: center;
    min-height: 1.75rem;
    border-radius: 999px;
    background: var(--primary-soft);
    color: var(--primary-ink);
    padding: .25rem .6rem;
    font-size: .78rem;
    font-weight: 600;
}

.telegram-personal-shell .scenario-message {
    color: var(--ink-1);
    font-size: .9rem;
    line-height: 1.55;
    white-space: pre-line;
}

.telegram-personal-shell .scenario-route {
    color: var(--ink-2);
    font-size: .82rem;
    overflow-wrap: anywhere;
}

@keyframes scenario-preview-pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(13, 110, 253, .18);
        border-color: var(--primary-line);
    }
    100% {
        box-shadow: 0 0 0 .65rem rgba(13, 110, 253, 0);
        border-color: var(--line-strong);
    }
}

@media (max-width: 767.98px) {
    .telegram-personal-shell {
        padding-inline: .25rem;
    }

    .telegram-personal-shell .page-title {
        font-size: 1.2rem;
    }
}

@media (prefers-reduced-motion: reduce) {
    .telegram-personal-shell .status-pill,
    .telegram-personal-shell .form-control,
    .telegram-personal-shell .form-select,
    .telegram-personal-shell .btn,
    .telegram-personal-shell .scenario-preview {
        transition-duration: .01ms;
    }

    .telegram-personal-shell .scenario-preview.is-updated {
        animation: none;
    }

    .telegram-personal-shell .flow-list::before {
        display: none;
    }
}
CSS);

$config = Json::encode([
    'csrfToken' => Yii::$app->request->csrfToken,
    'testBotUrl' => Url::to(['/telegrambot/settings/test-bot']),
    'setWebhookUrl' => Url::to(['/telegrambot/settings/set-webhook']),
    'testMessageUrl' => Url::to(['/telegrambot/settings/test-message']),
    'testMiniAppUrl' => Url::to(['/telegrambot/settings/test-mini-app']),
    'testNotificationScenarioUrl' => Url::to(['/telegrambot/settings/test-notification-scenario']),
    'notificationScenarios' => $notificationTestScenarios,
    'botDeepLink' => $botDeepLink,
]);

$this->registerJs(<<<JS
window.telegramPersonalConfig = {$config};
(function () {
    const config = window.telegramPersonalConfig || {};
    const botTokenField = document.getElementById('telegram-bot-token');
    const botUsernameField = document.getElementById('telegram-bot-username');
    const webhookUrlField = document.getElementById('telegram-webhook-url');
    const miniAppBaseUrlField = document.getElementById('telegram-mini-app-base-url');
    const testUserField = document.getElementById('telegram-test-user-id');
    const botStatusNodes = Array.from(document.querySelectorAll('[data-status-kind="bot"]'));
    const tokenToggleButton = document.getElementById('btn-toggle-bot-token');
    const testBotButton = document.getElementById('btn-test-bot');
    const setWebhookButton = document.getElementById('btn-set-webhook');
    const testMessageButton = document.getElementById('btn-test-message');
    const testMiniAppButton = document.getElementById('btn-test-mini-app');
    const testScenarioField = document.getElementById('telegram-test-scenario');
    const testScenarioButton = document.getElementById('btn-test-notification-scenario');
    const copyBotLinkButton = document.getElementById('btn-copy-bot-link');
    const scenarioPreview = document.getElementById('telegram-scenario-preview');
    const scenarioPreviewLabel = document.getElementById('telegram-scenario-preview-label');
    const scenarioPreviewTitle = document.getElementById('telegram-scenario-preview-title');
    const scenarioPreviewDescription = document.getElementById('telegram-scenario-preview-description');
    const scenarioPreviewMessage = document.getElementById('telegram-scenario-preview-message');
    const scenarioPreviewRoute = document.getElementById('telegram-scenario-preview-route');
    const notificationScenarios = config.notificationScenarios || {};

    function safeText(value) {
        return value === null || value === undefined ? '' : String(value);
    }

    function setBusy(button, busy, label) {
        if (!button) {
            return;
        }
        if (busy) {
            if (!button.dataset.originalHtml) {
                button.dataset.originalHtml = button.innerHTML;
            }
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>' + label;
            return;
        }

        button.disabled = false;
        if (button.dataset.originalHtml) {
            button.innerHTML = button.dataset.originalHtml;
        }
    }

    function flash(icon, title, text) {
        if (window.Swal) {
            return Swal.fire({
                icon: icon,
                title: title,
                text: text || '',
                confirmButtonText: 'ปิด',
            });
        }
        window.alert([title, text].filter(Boolean).join('\\n'));
    }

    function updateStatus(nodes, label, className) {
        nodes.forEach(function (node) {
            node.className = 'status-pill ' + className;
            node.textContent = label;
        });
    }

    function getBotUsername() {
        return safeText(botUsernameField ? botUsernameField.value : '').trim().replace(/^@/, '').replace(/[^A-Za-z0-9_]/g, '');
    }

    function stripTelegramHtml(value) {
        return safeText(value).replace(/<[^>]+>/g, '');
    }

    function routeToText(route) {
        if (Array.isArray(route)) {
            return route.filter(function (item) {
                return item !== null && item !== undefined && item !== '';
            }).map(function (item) {
                return safeText(item);
            }).join(' ');
        }
        if (route && typeof route === 'object') {
            return Object.keys(route).map(function (key) {
                return key + '=' + route[key];
            }).join(', ');
        }
        return safeText(route);
    }

    function getSelectedScenario() {
        const scenarioKey = safeText(testScenarioField ? testScenarioField.value : '').trim();
        return notificationScenarios[scenarioKey] || null;
    }

    function renderScenarioPreview(animate) {
        const scenario = getSelectedScenario();
        if (!scenario) {
            if (scenarioPreviewTitle) {
                scenarioPreviewTitle.textContent = 'เลือกรูปแบบการแจ้งเตือน';
            }
            if (scenarioPreviewDescription) {
                scenarioPreviewDescription.textContent = 'เลือก scenario เพื่อดูตัวอย่างข้อความและปุ่ม Mini App';
            }
            if (scenarioPreviewMessage) {
                scenarioPreviewMessage.textContent = '';
            }
            if (scenarioPreviewRoute) {
                scenarioPreviewRoute.textContent = '';
            }
            return;
        }

        if (scenarioPreviewLabel) {
            scenarioPreviewLabel.textContent = safeText(scenario.short_label || scenario.label || 'ทดสอบ');
        }
        if (scenarioPreviewTitle) {
            scenarioPreviewTitle.textContent = safeText(scenario.label || 'ทดสอบการแจ้งเตือน');
        }
        if (scenarioPreviewDescription) {
            scenarioPreviewDescription.textContent = safeText(scenario.description || '');
        }
        if (scenarioPreviewMessage) {
            const lines = Array.isArray(scenario.lines) ? scenario.lines : [];
            scenarioPreviewMessage.textContent = lines.map(stripTelegramHtml).join('\\n');
        }
        if (scenarioPreviewRoute) {
            if (scenario.attach_web_app === false) {
                scenarioPreviewRoute.textContent = 'ข้อความล้วน: ไม่มีปุ่ม Mini App';
            } else {
                scenarioPreviewRoute.textContent = scenario.button_text
                    ? 'ปุ่ม Mini App: ' + scenario.button_text + ' | Route: ' + routeToText(scenario.route || '')
                    : 'Route: ' + routeToText(scenario.route || '');
            }
        }
        if (animate && scenarioPreview) {
            scenarioPreview.classList.remove('is-updated');
            void scenarioPreview.offsetWidth;
            scenarioPreview.classList.add('is-updated');
        }
    }

    function normalizeBotUsernameInput() {
        const username = safeText(botUsernameField ? botUsernameField.value : '').trim().replace(/^@/, '');
        const normalizedUsername = getBotUsername();

        if (username !== normalizedUsername && botUsernameField) {
            botUsernameField.value = normalizedUsername;
        }
    }

    async function postJson(url, payload, button, busyLabel) {
        setBusy(button, true, busyLabel || 'กำลังดำเนินการ...');
        try {
            const params = new URLSearchParams();
            params.set('_csrf', config.csrfToken || '');
            Object.keys(payload || {}).forEach(function (key) {
                params.set(key, payload[key] === undefined || payload[key] === null ? '' : String(payload[key]));
            });

            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': config.csrfToken || '',
                },
                body: params,
            });
            return await response.json();
        } finally {
            setBusy(button, false);
        }
    }

    function requireToken() {
        const token = safeText(botTokenField ? botTokenField.value : '').trim();
        if (!token) {
            flash('warning', 'กรุณาระบุ Bot Token', 'ต้องมี token ก่อนทดสอบ Telegram');
            return '';
        }
        return token;
    }

    if (tokenToggleButton && botTokenField) {
        tokenToggleButton.addEventListener('click', function () {
            const nextType = botTokenField.type === 'password' ? 'text' : 'password';
            botTokenField.type = nextType;
            tokenToggleButton.setAttribute('aria-pressed', nextType === 'text' ? 'true' : 'false');
            tokenToggleButton.innerHTML = nextType === 'password'
                ? '<i class="fa-regular fa-eye me-1"></i> แสดง Token'
                : '<i class="fa-regular fa-eye-slash me-1"></i> ซ่อน Token';
        });
    }

    if (testBotButton) {
        testBotButton.addEventListener('click', async function () {
            const token = requireToken();
            if (!token) {
                return;
            }
            try {
                const result = await postJson(config.testBotUrl, { bot_token: token }, testBotButton, 'กำลังทดสอบ...');
                if (result.status !== 'success') {
                    updateStatus(botStatusNodes, 'Token ไม่ถูกต้อง', 'is-danger');
                    throw new Error(result.message || 'ทดสอบ Bot ไม่สำเร็จ');
                }
                if (botUsernameField && result.bot_username) {
                    botUsernameField.value = result.bot_username.replace(/^@/, '');
                    normalizeBotUsernameInput();
                }
                updateStatus(botStatusNodes, 'เชื่อมต่อแล้ว', 'is-success');
                flash('success', 'เชื่อมต่อ Bot สำเร็จ', result.message || 'พร้อมใช้งาน');
            } catch (error) {
                flash('error', 'ทดสอบ Bot ไม่สำเร็จ', error.message || 'กรุณาตรวจสอบ token');
            }
        });
    }

    if (setWebhookButton) {
        setWebhookButton.addEventListener('click', async function () {
            const token = requireToken();
            const webhookUrl = safeText(webhookUrlField ? webhookUrlField.value : '').trim();
            if (!token) {
                return;
            }
            if (!webhookUrl) {
                flash('warning', 'กรุณาระบุ Webhook URL', 'ต้องเป็น HTTPS ที่ Telegram เข้าถึงได้');
                return;
            }

            try {
                const result = await postJson(config.setWebhookUrl, {
                    bot_token: token,
                    webhook_url: webhookUrl,
                }, setWebhookButton, 'กำลังตั้งค่า...');
                if (result.status !== 'success') {
                    throw new Error(result.message || 'ตั้งค่า Webhook ไม่สำเร็จ');
                }
                flash('success', 'ตั้งค่า Webhook แล้ว', result.message || webhookUrl);
            } catch (error) {
                flash('error', 'ตั้งค่า Webhook ไม่สำเร็จ', error.message || 'กรุณาตรวจสอบ URL');
            }
        });
    }

    if (testMessageButton) {
        testMessageButton.addEventListener('click', async function () {
            const token = requireToken();
            const userId = safeText(testUserField ? testUserField.value : '').trim();
            if (!token) {
                return;
            }
            if (!userId) {
                flash('warning', 'เลือกผู้รับก่อน', 'ต้องมีผู้ใช้ที่ผูก Telegram แล้ว');
                return;
            }

            try {
                const result = await postJson(config.testMessageUrl, {
                    bot_token: token,
                    user_id: userId,
                }, testMessageButton, 'กำลังส่ง...');
                if (result.status !== 'success') {
                    throw new Error(result.message || 'ส่งข้อความทดสอบไม่สำเร็จ');
                }
                flash('success', 'ส่งข้อความแล้ว', result.message || 'ผู้ใช้จะได้รับข้อความใน Telegram');
            } catch (error) {
                flash('error', 'ส่งข้อความไม่สำเร็จ', error.message || 'ผู้ใช้อาจยังไม่ได้เชื่อมต่อ Telegram หรือบล็อก bot');
            }
        });
    }

    if (testMiniAppButton) {
        testMiniAppButton.addEventListener('click', async function () {
            const token = requireToken();
            const userId = safeText(testUserField ? testUserField.value : '').trim();
            const miniAppBaseUrl = safeText(miniAppBaseUrlField ? miniAppBaseUrlField.value : '').trim();
            if (!token) {
                return;
            }
            if (!userId) {
                flash('warning', 'เลือกผู้รับก่อน', 'ต้องมีผู้ใช้ที่ผูก Telegram แล้ว');
                return;
            }
            if (!miniAppBaseUrl) {
                flash('warning', 'กรุณาระบุ Mini App Base URL', 'ต้องเป็น HTTPS ที่เปิดจาก Telegram ได้');
                return;
            }

            try {
                const result = await postJson(config.testMiniAppUrl, {
                    bot_token: token,
                    user_id: userId,
                    mini_app_base_url: miniAppBaseUrl,
                }, testMiniAppButton, 'กำลังส่ง...');
                if (result.status !== 'success') {
                    throw new Error(result.message || 'ส่งปุ่ม Mini App ไม่สำเร็จ');
                }
                flash('success', 'ส่งปุ่ม Mini App แล้ว', result.message || 'ผู้ใช้จะเห็นปุ่มเปิด ERP Mobile');
            } catch (error) {
                flash('error', 'ส่งปุ่ม Mini App ไม่สำเร็จ', error.message || 'กรุณาตรวจสอบ URL หรือผู้รับ');
            }
        });
    }

    if (testScenarioField) {
        testScenarioField.addEventListener('change', function () {
            renderScenarioPreview(true);
        });
        renderScenarioPreview(false);
    }

    if (testScenarioButton) {
        testScenarioButton.addEventListener('click', async function () {
            const token = requireToken();
            const userId = safeText(testUserField ? testUserField.value : '').trim();
            const miniAppBaseUrl = safeText(miniAppBaseUrlField ? miniAppBaseUrlField.value : '').trim();
            const scenarioKey = safeText(testScenarioField ? testScenarioField.value : '').trim();
            if (!token) {
                return;
            }
            if (!userId) {
                flash('warning', 'เลือกผู้รับก่อน', 'ต้องมีผู้ใช้ที่ผูก Telegram แล้ว');
                return;
            }
            if (!scenarioKey) {
                flash('warning', 'เลือกระบบที่จะทดสอบ', 'เลือก scenario เช่น ใบลา จองห้อง จองรถ หรือแจ้งซ่อม');
                return;
            }
            const scenario = getSelectedScenario();
            const requiresMiniApp = !(scenario && scenario.attach_web_app === false);
            if (requiresMiniApp && !miniAppBaseUrl) {
                flash('warning', 'กรุณาระบุ Mini App Base URL', 'ต้องเป็น HTTPS ที่เปิดจาก Telegram ได้');
                return;
            }

            try {
                const result = await postJson(config.testNotificationScenarioUrl, {
                    bot_token: token,
                    user_id: userId,
                    mini_app_base_url: miniAppBaseUrl,
                    scenario: scenarioKey,
                }, testScenarioButton, 'กำลังส่ง...');
                if (result.status !== 'success') {
                    throw new Error(result.message || 'ส่งการแจ้งเตือนทดสอบไม่สำเร็จ');
                }
                flash('success', 'ส่งการแจ้งเตือนทดสอบแล้ว', result.message || (requiresMiniApp ? 'ผู้ใช้จะได้รับข้อความพร้อมปุ่ม Mini App' : 'ผู้ใช้จะได้รับข้อความล้วนใน Telegram'));
            } catch (error) {
                flash('error', 'ส่งการแจ้งเตือนไม่สำเร็จ', error.message || 'กรุณาตรวจสอบ URL, token หรือผู้รับ');
            }
        });
    }

    if (copyBotLinkButton) {
        copyBotLinkButton.addEventListener('click', async function () {
            const link = safeText(config.botDeepLink);
            if (!link) {
                return;
            }
            try {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    await navigator.clipboard.writeText(link);
                } else {
                    const helper = document.createElement('textarea');
                    helper.value = link;
                    helper.style.position = 'fixed';
                    helper.style.opacity = '0';
                    document.body.appendChild(helper);
                    helper.select();
                    document.execCommand('copy');
                    document.body.removeChild(helper);
                }
                flash('success', 'คัดลอกลิงก์แล้ว', link);
            } catch (error) {
                flash('error', 'คัดลอกลิงก์ไม่สำเร็จ', link);
            }
        });
    }

    if (botUsernameField) {
        botUsernameField.addEventListener('input', normalizeBotUsernameInput);
        normalizeBotUsernameInput();
    }
})();
JS, View::POS_END);
?>

<div class="telegram-personal-shell container-fluid py-4">
    <div class="telegram-hero p-4 p-xl-5 mb-4">
        <div class="d-flex flex-column flex-xl-row justify-content-between gap-4">
            <div class="d-flex align-items-start gap-3">
                <div class="bot-mark">
                    <i class="fa-brands fa-telegram fs-4"></i>
                </div>
                <div>
                    <h1 class="page-title mb-2">Telegram Personal Notification</h1>
                    <p class="panel-desc mb-3">
                        ใช้ Telegram Bot เป็นช่องทางแจ้งเตือนรายบุคคล และเป็นประตูเข้า ERP Mobile ผ่าน Telegram Mini App โดยไม่ต้องสร้างกลุ่ม Telegram
                    </p>
                    <nav class="d-flex flex-wrap gap-2" aria-label="ลัดไปยังหัวข้อการตั้งค่า">
                        <a href="#settings-general" class="btn btn-light border rounded-3">ตั้งค่า Bot</a>
                        <a href="#settings-mini-app" class="btn btn-light border rounded-3">Mini App</a>
                        <a href="#settings-testing" class="btn btn-light border rounded-3">ทดสอบ</a>
                        <a href="#settings-bindings" class="btn btn-light border rounded-3">ผู้ใช้ที่ผูกแล้ว</a>
                    </nav>
                </div>
            </div>
            <div class="d-grid gap-2" style="min-width:min(100%, 280px);">
                <button type="submit" form="telegram-settings-form" class="btn btn-primary rounded-3 shadow-sm">
                    <i class="fa-solid fa-floppy-disk me-1"></i> บันทึกการตั้งค่า
                </button>
                <div class="bot-qr-card">
                    <?php if ($botQrDataUri !== ''): ?>
                        <button
                            type="button"
                            class="bot-qr-card__trigger"
                            data-bs-toggle="modal"
                            data-bs-target="#botQrModal"
                            aria-label="ขยาย QR Code สำหรับเปิดแชทกับ @<?= Html::encode($botUsernameValue) ?> ใน Telegram"
                        >
                            <img
                                src="<?= $botQrDataUri ?>"
                                width="72"
                                height="72"
                                class="bot-qr-card__image"
                                alt=""
                            >
                        </button>
                        <div class="bot-qr-card__body">
                            <div class="bot-qr-card__label">สแกนเพื่อเปิดแชทกับ Bot</div>
                            <div class="bot-qr-card__link"><?= Html::encode($botDeepLink) ?></div>
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-3 mt-2" id="btn-copy-bot-link">
                                <i class="fa-regular fa-copy me-1"></i> คัดลอกลิงก์
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="bot-qr-card__empty">
                            <i class="fa-solid fa-qrcode" aria-hidden="true"></i>
                            <span>ตั้งค่า Bot Username แล้วบันทึก เพื่อสร้าง QR Code เปิดแชทกับ Bot</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="row row-cols-1 row-cols-md-2 row-cols-xxl-4 g-3 mt-4">
            <div class="col">
                <div class="summary-tile p-3 h-100">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                        <div class="bot-mark" style="width:2.5rem;height:2.5rem;border-radius:var(--radius-xs);">
                            <i class="fa-solid fa-plug-circle-check"></i>
                        </div>
                        <span class="status-pill <?= $botTokenValue !== '' ? 'is-neutral' : 'is-warning' ?>" data-status-kind="bot" aria-live="polite">
                            <?= $botTokenValue !== '' ? 'รอทดสอบ' : 'ยังไม่มี Token' ?>
                        </span>
                    </div>
                    <div class="summary-value"><?= Html::encode($botUsernameValue !== '' ? '@' . $botUsernameValue : '-') ?></div>
                    <div class="muted-note">Bot username</div>
                </div>
            </div>
            <div class="col">
                <div class="summary-tile p-3 h-100">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                        <div class="bot-mark is-teal" style="width:2.5rem;height:2.5rem;border-radius:var(--radius-xs);">
                            <i class="fa-solid fa-mobile-screen-button"></i>
                        </div>
                        <span class="status-pill <?= $miniAppEnabled ? 'is-success' : 'is-neutral' ?>">
                            <?= $miniAppEnabled ? 'เปิดใช้งาน' : 'ปิดอยู่' ?>
                        </span>
                    </div>
                    <div class="summary-value"><?= Html::encode($miniAppBaseUrlValue !== '' ? parse_url($miniAppBaseUrlValue, PHP_URL_HOST) : '-') ?></div>
                    <div class="muted-note">Mini App Base URL</div>
                </div>
            </div>
            <div class="col">
                <div class="summary-tile p-3 h-100">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                        <div class="bot-mark is-success" style="width:2.5rem;height:2.5rem;border-radius:var(--radius-xs);">
                            <i class="fa-regular fa-bell"></i>
                        </div>
                        <span class="status-pill <?= $notificationEnabled ? 'is-success' : 'is-warning' ?>">
                            <?= $notificationEnabled ? 'ส่งแจ้งเตือน' : 'หยุดส่ง' ?>
                        </span>
                    </div>
                    <div class="summary-value"><?= number_format($linkedUserCount) ?></div>
                    <div class="muted-note">ผู้ใช้พร้อมรับข้อความ</div>
                </div>
            </div>
            <div class="col">
                <div class="summary-tile p-3 h-100">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                        <div class="bot-mark is-violet" style="width:2.5rem;height:2.5rem;border-radius:var(--radius-xs);">
                            <i class="fa-solid fa-user-check"></i>
                        </div>
                        <span class="status-pill is-neutral"><?= (int) $linkedPercent ?>%</span>
                    </div>
                    <div class="summary-value"><?= number_format($linkedUserCount) ?> / <?= number_format($activeUserCount) ?></div>
                    <div class="muted-note">บัญชีที่ผูก Telegram แล้ว</div>
                </div>
            </div>
        </div>
    </div>

    <?php $form = ActiveForm::begin([
        'id' => 'telegram-settings-form',
        'options' => [
            'autocomplete' => 'off',
        ],
    ]); ?>

    <div class="d-none">
        <?= $form->field($model, 'name')->hiddenInput()->label(false) ?>
        <?= $form->field($model, 'code')->hiddenInput()->label(false) ?>
        <?= $form->field($model, 'title')->hiddenInput()->label(false) ?>
    </div>

    <div class="row g-4 align-items-start">
        <div class="col-12 col-xl-8">
            <div class="setting-panel p-4 p-xl-5 mb-4" id="settings-general" role="region" aria-labelledby="settings-general-title">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                    <div class="panel-head-text">
                        <span class="panel-icon" aria-hidden="true"><i class="fa-solid fa-plug-circle-check"></i></span>
                        <div>
                            <h2 class="panel-title" id="settings-general-title">General</h2>
                            <p class="panel-desc mb-0">ตั้งค่า token, username และ webhook ที่รับ event จาก Telegram Bot</p>
                        </div>
                    </div>
                    <span class="status-pill <?= $botTokenValue !== '' ? 'is-neutral' : 'is-warning' ?>" data-status-kind="bot" aria-live="polite">
                        <?= $botTokenValue !== '' ? 'รอทดสอบ' : 'ยังไม่มี Token' ?>
                    </span>
                </div>

                <div class="row g-3">
                    <div class="col-12">
                        <?= $form->field($model, 'data_json[bot_token]')->label('Bot Token')->passwordInput([
                            'id' => 'telegram-bot-token',
                            'class' => 'form-control',
                            'value' => $botTokenValue,
                            'autocomplete' => 'off',
                            'placeholder' => 'วาง Bot Token จาก BotFather',
                        ]) ?>
                        <div class="d-flex flex-wrap gap-2 mt-2">
                            <button type="button" class="btn btn-outline-secondary rounded-3" id="btn-toggle-bot-token">
                                <i class="fa-regular fa-eye me-1"></i> แสดง Token
                            </button>
                            <button type="button" class="btn btn-outline-primary rounded-3" id="btn-test-bot">
                                <i class="fa-solid fa-plug-circle-check me-1"></i> Test Bot Connection
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <?= $form->field($model, 'data_json[bot_username]')->label('Bot Username')->textInput([
                            'id' => 'telegram-bot-username',
                            'class' => 'form-control',
                            'value' => $botUsernameValue,
                            'placeholder' => 'เช่น erp_notification_bot',
                        ]) ?>
                    </div>
                    <div class="col-md-6">
                        <?= $form->field($model, 'data_json[webhook_url]')->label('Webhook URL')->textInput([
                            'id' => 'telegram-webhook-url',
                            'class' => 'form-control',
                            'value' => $webhookUrlValue,
                            'placeholder' => $defaultWebhookUrl,
                        ]) ?>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 mt-3">
                    <button type="button" class="btn btn-outline-primary rounded-3" id="btn-set-webhook">
                        <i class="fa-solid fa-link me-1"></i> ตั้งค่า Webhook
                    </button>
                    <button type="submit" form="telegram-settings-form" class="btn btn-primary rounded-3">
                        <i class="fa-solid fa-floppy-disk me-1"></i> บันทึก
                    </button>
                </div>
            </div>

            <div class="setting-panel p-4 p-xl-5 mb-4" id="settings-mini-app" role="region" aria-labelledby="settings-mini-app-title">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                    <div class="panel-head-text">
                        <span class="panel-icon is-teal" aria-hidden="true"><i class="fa-solid fa-mobile-screen-button"></i></span>
                        <div>
                            <h2 class="panel-title" id="settings-mini-app-title">Telegram Mini App</h2>
                            <p class="panel-desc mb-0">ทุกปุ่มเปิด ERP Mobile จะอ่าน URL จาก Mini App Base URL นี้ เปลี่ยน domain ได้โดยไม่แก้โค้ด</p>
                        </div>
                    </div>
                    <span class="status-pill <?= $miniAppEnabled ? 'is-success' : 'is-neutral' ?>">
                        <?= $miniAppEnabled ? 'เปิดใช้งาน' : 'ปิดอยู่' ?>
                    </span>
                </div>

                <div class="row g-3 align-items-end">
                    <div class="col-lg-8">
                        <?= $form->field($model, 'data_json[mini_app_base_url]')->label('Mini App Base URL')->textInput([
                            'id' => 'telegram-mini-app-base-url',
                            'class' => 'form-control',
                            'type' => 'url',
                            'value' => $miniAppBaseUrlValue,
                            'placeholder' => 'https://erp.example.com/mobile',
                        ]) ?>
                    </div>
                    <div class="col-lg-4">
                        <div class="d-grid gap-2">
                            <div class="form-check form-switch">
                                <?= Html::activeCheckbox($model, 'data_json[enable_mini_app]', [
                                    'id' => 'telegram-enable-mini-app',
                                    'class' => 'form-check-input',
                                    'uncheck' => '0',
                                    'value' => '1',
                                    'checked' => $miniAppEnabled,
                                ]) ?>
                                <?= Html::label('Enable Mini App', 'telegram-enable-mini-app', ['class' => 'form-check-label ms-2']) ?>
                            </div>
                            <div class="form-check form-switch">
                                <?= Html::activeCheckbox($model, 'data_json[enable_notification]', [
                                    'id' => 'telegram-enable-notification',
                                    'class' => 'form-check-input',
                                    'uncheck' => '0',
                                    'value' => '1',
                                    'checked' => $notificationEnabled,
                                ]) ?>
                                <?= Html::label('Enable Notification', 'telegram-enable-notification', ['class' => 'form-check-label ms-2']) ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="empty-state p-3 mt-3">
                    <div class="flow-step">
                        <span class="step-dot is-teal"><i class="fa-solid fa-route"></i></span>
                        <div class="muted-note">
                            Flow ใหม่: ระบบเชื่อม Telegram กับผู้ใช้รายบุคคล แล้วเปิด ERP Mobile ผ่าน Mini App โดยใช้ URL ที่ตั้งค่าไว้
                        </div>
                    </div>
                </div>
            </div>

            <div class="setting-panel p-4 p-xl-5 mb-4" id="settings-testing" role="region" aria-labelledby="settings-testing-title">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                    <div class="panel-head-text">
                        <span class="panel-icon is-violet" aria-hidden="true"><i class="fa-solid fa-vial"></i></span>
                        <div>
                            <h2 class="panel-title" id="settings-testing-title">ทดสอบการแจ้งเตือน</h2>
                            <p class="panel-desc mb-0">ส่งข้อความ ปุ่ม Mini App และตัวอย่าง notification ตามระบบงานให้ผู้ใช้ที่ผูก Telegram แล้ว</p>
                        </div>
                    </div>
                    <span class="status-pill <?= $linkedUserCount > 0 ? 'is-success' : 'is-warning' ?>">
                        <?= $linkedUserCount > 0 ? number_format($linkedUserCount) . ' users' : 'ยังไม่มีผู้รับ' ?>
                    </span>
                </div>

                <div class="row g-3 align-items-end mb-2">
                    <div class="col-lg-6">
                        <label for="telegram-test-user-id" class="form-label">ผู้รับสำหรับทดสอบ</label>
                        <?= Html::dropDownList('telegram_test_user_id', '', $testUserOptions, [
                            'id' => 'telegram-test-user-id',
                            'class' => 'form-select',
                            'prompt' => $testUserOptions ? 'เลือกผู้ใช้ที่ผูก Telegram แล้ว' : 'ยังไม่มีผู้ใช้ที่ผูก Telegram',
                            'disabled' => empty($testUserOptions),
                        ]) ?>
                    </div>
                    <div class="col-lg-6">
                        <div class="test-actions">
                            <button type="button" class="btn btn-outline-primary rounded-3" id="btn-test-message"<?= empty($testUserOptions) ? ' disabled' : '' ?>>
                                <i class="fa-regular fa-paper-plane me-1"></i> ทดสอบส่งข้อความ
                            </button>
                            <button type="button" class="btn btn-primary rounded-3" id="btn-test-mini-app"<?= empty($testUserOptions) ? ' disabled' : '' ?>>
                                <i class="fa-solid fa-mobile-screen-button me-1"></i> ทดสอบปุ่ม Mini App
                            </button>
                        </div>
                    </div>
                </div>

                <div class="test-suite">
                    <div class="row g-3 align-items-stretch">
                        <div class="col-lg-5">
                            <label for="telegram-test-scenario" class="form-label">ระบบแจ้งเตือนที่ต้องการทดสอบ</label>
                            <?= Html::dropDownList('telegram_test_scenario', $defaultTestScenario, $testScenarioOptions, $testScenarioSelectOptions) ?>
                            <div class="muted-note mt-2">
                                ข้อความเป็นข้อมูลจำลองเพื่อทดสอบการส่งจริง ไม่สร้างใบลา การจอง หรือใบแจ้งซ่อมในระบบ
                            </div>
                            <button
                                type="button"
                                class="btn btn-success rounded-3 mt-3"
                                id="btn-test-notification-scenario"
                                <?= empty($testUserOptions) || empty($testScenarioOptions) ? 'disabled' : '' ?>
                            >
                                <i class="fa-regular fa-bell me-1"></i> ส่งทดสอบตามระบบ
                            </button>
                        </div>
                        <div class="col-lg-7">
                            <div class="scenario-preview" id="telegram-scenario-preview" aria-live="polite">
                                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-2">
                                    <div>
                                        <span class="scenario-chip mb-2" id="telegram-scenario-preview-label">ทดสอบ</span>
                                        <div class="fw-bold" id="telegram-scenario-preview-title">เลือกรูปแบบการแจ้งเตือน</div>
                                    </div>
                                    <i class="fa-brands fa-telegram text-primary fs-4" aria-hidden="true"></i>
                                </div>
                                <div class="muted-note mb-3" id="telegram-scenario-preview-description">
                                    เลือก scenario เพื่อดูตัวอย่างข้อความและปุ่ม Mini App
                                </div>
                                <div class="scenario-message mb-3" id="telegram-scenario-preview-message"></div>
                                <div class="scenario-route" id="telegram-scenario-preview-route"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="setting-panel p-4 p-xl-5" id="settings-bindings" role="region" aria-labelledby="settings-bindings-title">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                    <div class="panel-head-text">
                        <span class="panel-icon is-success" aria-hidden="true"><i class="fa-solid fa-users"></i></span>
                        <div>
                            <h2 class="panel-title" id="settings-bindings-title">บัญชีที่ผูก Telegram แล้ว</h2>
                            <p class="panel-desc mb-0">รายชื่อผู้ใช้ที่ระบบสามารถส่งแจ้งเตือนรายบุคคลได้ทันที</p>
                        </div>
                    </div>
                    <span class="status-pill is-neutral"><?= number_format($linkedUserCount) ?> users</span>
                </div>

                <?php if (empty($bindingRows)): ?>
                    <div class="empty-state p-4 text-center">
                        <div class="fw-bold mb-1">ยังไม่มีผู้ใช้ที่ผูก Telegram</div>
                        <div class="muted-note">ให้ผู้ใช้เชื่อมต่อ Telegram กับบัญชี ERP ตาม flow ที่ระบบกำหนดไว้</div>
                    </div>
                <?php else: ?>
                    <div class="binding-table-wrap d-none d-lg-block">
                        <table class="table table-hover align-middle mb-0 binding-table">
                            <thead>
                                <tr>
                                    <th>ผู้ใช้งาน</th>
                                    <th>แผนก / ตำแหน่ง</th>
                                    <th>Telegram Chat ID</th>
                                    <th class="text-center">สถานะ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bindingRows as $row): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold"><?= Html::encode($row['name'] !== '' ? $row['name'] : '-') ?></div>
                                            <div class="muted-note"><?= Html::encode($row['username']) ?></div>
                                        </td>
                                        <td>
                                            <div><?= Html::encode($row['department']) ?></div>
                                            <div class="muted-note"><?= Html::encode($row['position']) ?></div>
                                        </td>
                                        <td><code><?= Html::encode($row['telegramId']) ?></code></td>
                                        <td class="text-center"><span class="status-pill is-success">พร้อมรับแจ้งเตือน</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <ul class="binding-cards d-lg-none" role="list">
                        <?php foreach ($bindingRows as $row): ?>
                            <li class="binding-card">
                                <div class="binding-card__head">
                                    <div>
                                        <div class="binding-card__name"><?= Html::encode($row['name'] !== '' ? $row['name'] : '-') ?></div>
                                        <div class="binding-card__username"><?= Html::encode($row['username']) ?></div>
                                    </div>
                                    <span class="status-pill is-success">พร้อมรับแจ้งเตือน</span>
                                </div>
                                <div class="binding-card__meta"><?= Html::encode($row['department']) ?> · <?= Html::encode($row['position']) ?></div>
                                <div class="binding-card__chat">
                                    <span class="muted-note">Chat ID</span>
                                    <code><?= Html::encode($row['telegramId']) ?></code>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="setting-panel p-4 mb-4">
                <h2 class="panel-title mb-3">แนวคิดใหม่</h2>
                <div class="d-grid gap-3 flow-list">
                    <div class="flow-step">
                        <span class="step-dot"><i class="fa-solid fa-user-plus"></i></span>
                        <div>
                            <div class="fw-semibold">เชื่อมผู้ใช้ Telegram</div>
                            <div class="muted-note">ระบบบันทึก Telegram Chat ID กับบัญชีผู้ใช้รายบุคคล</div>
                        </div>
                    </div>
                    <div class="flow-step">
                        <span class="step-dot is-teal"><i class="fa-solid fa-right-to-bracket"></i></span>
                        <div>
                            <div class="fw-semibold">เปิด ERP Mobile</div>
                            <div class="muted-note">Mini App ส่ง Telegram user id ไปที่หน้า login</div>
                        </div>
                    </div>
                    <div class="flow-step">
                        <span class="step-dot is-violet"><i class="fa-solid fa-link"></i></span>
                        <div>
                            <div class="fw-semibold">ผูกบัญชี</div>
                            <div class="muted-note">เมื่อ login สำเร็จ ระบบบันทึก Telegram Chat ID ให้ผู้ใช้คนนั้น</div>
                        </div>
                    </div>
                    <div class="flow-step">
                        <span class="step-dot is-success"><i class="fa-regular fa-bell"></i></span>
                        <div>
                            <div class="fw-semibold">แจ้งเตือนรายบุคคล</div>
                            <div class="muted-note">ERP ส่งข้อความตรงถึงผู้รับงานหรือผู้อนุมัติ ไม่ต้องใช้กลุ่ม</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="setting-panel p-4">
                <h2 class="panel-title mb-3">ข้อผิดพลาดที่รองรับ</h2>
                <ul class="muted-note mb-0 ps-3">
                    <li class="mb-2">Token ไม่ถูกต้องหรือถูก revoke</li>
                    <li class="mb-2">Mini App Base URL ไม่ใช่ HTTPS</li>
                    <li class="mb-2">ผู้ใช้ยังไม่ได้เชื่อมต่อ Telegram กับบัญชี ERP</li>
                    <li class="mb-2">ผู้ใช้ block bot หรือปิดรับข้อความ</li>
                    <li>Telegram API rate limit หรือ network timeout</li>
                </ul>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

    <?php if ($botQrDataUri !== ''): ?>
        <div class="modal fade" id="botQrModal" tabindex="-1" aria-labelledby="botQrModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content rounded-3">
                    <div class="modal-header border-0 pb-0">
                        <h2 class="modal-title fs-6 fw-semibold" id="botQrModalLabel">QR Code สำหรับเปิดแชทกับ Bot</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
                    </div>
                    <div class="modal-body text-center pt-2">
                        <img
                            src="<?= $botQrDataUri ?>"
                            width="260"
                            height="260"
                            class="img-fluid rounded-3 border"
                            alt="QR Code สำหรับเปิดแชทกับ @<?= Html::encode($botUsernameValue) ?> ใน Telegram"
                        >
                        <div class="muted-note mt-3" style="overflow-wrap:anywhere;"><?= Html::encode($botDeepLink) ?></div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
