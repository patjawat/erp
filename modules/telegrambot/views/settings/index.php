<?php

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
$botQrImagePath = trim((string) ($data['bot_qr_image'] ?? ''));
$botQrImageName = trim((string) ($data['bot_qr_image_name'] ?? ''));
$botQrImageUploadedAt = trim((string) ($data['bot_qr_image_uploaded_at'] ?? ''));
$botQrImageVersion = $botQrImageUploadedAt !== '' ? (string) strtotime($botQrImageUploadedAt) : substr(md5($botQrImagePath), 0, 8);
$botQrImageUrl = $botQrImagePath !== ''
    ? Url::to(['/telegrambot/settings/bot-qr', 'v' => $botQrImageVersion])
    : '';
$botQrDownloadUrl = $botQrImagePath !== ''
    ? Url::to(['/telegrambot/settings/bot-qr', 'download' => 1])
    : '';

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
    --tg-primary: #2155d6;
    --tg-primary-soft: #edf4ff;
    --tg-ink: #0f172a;
    --tg-muted: #64748b;
    --tg-border: #dbe5f1;
    --tg-surface: #ffffff;
    --tg-page: #f6f8fc;
    color: var(--tg-ink);
}

.telegram-personal-shell .setting-panel,
.telegram-personal-shell .summary-tile {
    background: var(--tg-surface);
    border: 1px solid var(--tg-border);
    box-shadow: 0 16px 40px rgba(15, 23, 42, .05);
}

.telegram-personal-shell .telegram-hero {
    border-radius: 1.4rem;
    background: linear-gradient(180deg, #f9fbff 0%, #f6f8fc 100%);
}

.telegram-personal-shell .setting-panel,
.telegram-personal-shell .summary-tile {
    border-radius: 1.1rem;
}

.telegram-personal-shell .bot-mark {
    width: 3.25rem;
    height: 3.25rem;
    border-radius: 1rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: var(--tg-primary);
    background: var(--tg-primary-soft);
    flex: 0 0 auto;
}

.telegram-personal-shell .page-title {
    font-size: 1.45rem;
    line-height: 1.25;
    font-weight: 800;
    text-wrap: balance;
}

.telegram-personal-shell .panel-title {
    font-size: 1.05rem;
    font-weight: 800;
    margin: 0;
}

.telegram-personal-shell .panel-desc,
.telegram-personal-shell .muted-note {
    color: var(--tg-muted);
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
    font-weight: 800;
    border: 1px solid transparent;
    white-space: nowrap;
    transition: background-color 180ms cubic-bezier(0.25, 1, 0.5, 1), border-color 180ms cubic-bezier(0.25, 1, 0.5, 1), color 180ms cubic-bezier(0.25, 1, 0.5, 1);
}

.telegram-personal-shell .is-success {
    background: #e7f8ef;
    color: #0f7a42;
    border-color: #bbeacb;
}

.telegram-personal-shell .is-warning {
    background: #fff7df;
    color: #9a6700;
    border-color: #f1d79e;
}

.telegram-personal-shell .is-danger {
    background: #feecee;
    color: #b42318;
    border-color: #f7c4c9;
}

.telegram-personal-shell .is-neutral {
    background: #eef3f8;
    color: #526177;
    border-color: #dae2ea;
}

.telegram-personal-shell .summary-value {
    font-size: 1.32rem;
    font-weight: 850;
    font-variant-numeric: tabular-nums;
}

.telegram-personal-shell .flow-step {
    display: flex;
    align-items: flex-start;
    gap: .75rem;
}

.telegram-personal-shell .step-dot {
    width: 2rem;
    height: 2rem;
    border-radius: .75rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--tg-primary-soft);
    color: var(--tg-primary);
    flex: 0 0 auto;
}

.telegram-personal-shell .form-control,
.telegram-personal-shell .form-select,
.telegram-personal-shell .btn {
    min-height: 44px;
    transition: border-color 180ms cubic-bezier(0.25, 1, 0.5, 1), box-shadow 180ms cubic-bezier(0.25, 1, 0.5, 1), background-color 180ms cubic-bezier(0.25, 1, 0.5, 1), transform 120ms cubic-bezier(0.25, 1, 0.5, 1);
}

.telegram-personal-shell .btn:active {
    transform: translateY(1px);
}

.telegram-personal-shell .bot-share-card {
    background: #ffffff;
    border: 1px solid var(--tg-border);
    border-radius: 1rem;
    padding: .9rem;
}

.telegram-personal-shell .bot-qr-frame {
    width: 8.5rem;
    aspect-ratio: 1;
    border: 1px solid #d9e4f2;
    border-radius: .85rem;
    background: #ffffff;
    display: grid;
    place-items: center;
    overflow: hidden;
    flex: 0 0 auto;
}

.telegram-personal-shell .bot-qr-frame img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.telegram-personal-shell .bot-qr-frame.is-empty {
    background: #f8fbff;
    color: #8090a6;
}

.telegram-personal-shell .bot-qr-placeholder {
    display: grid;
    place-items: center;
    gap: .35rem;
    text-align: center;
    font-size: .8rem;
    color: var(--tg-muted);
}

.telegram-personal-shell .bot-qr-actions .btn {
    min-height: 38px;
}

.telegram-personal-shell .bot-upload-control {
    border: 1px dashed #b9c9df;
    border-radius: .85rem;
    background: #f8fbff;
    padding: .65rem;
}

.telegram-personal-shell .bot-upload-control .form-control {
    min-height: 38px;
    font-size: .86rem;
}

.telegram-personal-shell .bot-qr-meta {
    color: #526177;
    font-size: .82rem;
    line-height: 1.45;
    overflow-wrap: anywhere;
}

.telegram-personal-shell .bot-qr-save-hint {
    color: #9a6700;
    font-size: .82rem;
    line-height: 1.45;
}

.telegram-personal-shell .binding-table thead th {
    background: #f8fafc;
    color: #475569;
    font-size: .82rem;
    font-weight: 800;
    white-space: nowrap;
}

.telegram-personal-shell .binding-table tbody td {
    vertical-align: middle;
}

.telegram-personal-shell .empty-state {
    border: 1px dashed #c9d6e6;
    border-radius: 1rem;
    background: #f8fbff;
}

.telegram-personal-shell .test-suite {
    border-top: 1px solid var(--tg-border);
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
    border: 1px solid #cdd9ea;
    border-radius: 1rem;
    background: #f8fbff;
    padding: 1rem;
    transition: border-color 180ms cubic-bezier(0.25, 1, 0.5, 1), background-color 180ms cubic-bezier(0.25, 1, 0.5, 1), box-shadow 180ms cubic-bezier(0.25, 1, 0.5, 1);
}

.telegram-personal-shell .scenario-preview.is-updated {
    animation: scenario-preview-pulse 320ms cubic-bezier(0.22, 1, 0.36, 1);
}

.telegram-personal-shell .scenario-chip {
    display: inline-flex;
    align-items: center;
    min-height: 1.75rem;
    border-radius: 999px;
    background: var(--tg-primary-soft);
    color: var(--tg-primary);
    padding: .25rem .6rem;
    font-size: .78rem;
    font-weight: 800;
}

.telegram-personal-shell .scenario-message {
    color: var(--tg-ink);
    font-size: .9rem;
    line-height: 1.55;
    white-space: pre-line;
}

.telegram-personal-shell .scenario-route {
    color: #526177;
    font-size: .82rem;
    overflow-wrap: anywhere;
}

@keyframes scenario-preview-pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(33, 85, 214, .18);
        border-color: #9bb7ea;
    }
    100% {
        box-shadow: 0 0 0 .65rem rgba(33, 85, 214, 0);
        border-color: #cdd9ea;
    }
}

@media (max-width: 767.98px) {
    .telegram-personal-shell {
        padding-inline: .25rem;
    }

    .telegram-personal-shell .page-title {
        font-size: 1.2rem;
    }

    .telegram-personal-shell .bot-share-card .d-flex {
        align-items: center;
    }

    .telegram-personal-shell .bot-qr-frame {
        width: 7.75rem;
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
    'botQrImageUrl' => $botQrImageUrl,
    'botQrDownloadUrl' => $botQrDownloadUrl,
    'botQrImageName' => $botQrImageName,
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
    const scenarioPreview = document.getElementById('telegram-scenario-preview');
    const scenarioPreviewLabel = document.getElementById('telegram-scenario-preview-label');
    const scenarioPreviewTitle = document.getElementById('telegram-scenario-preview-title');
    const scenarioPreviewDescription = document.getElementById('telegram-scenario-preview-description');
    const scenarioPreviewMessage = document.getElementById('telegram-scenario-preview-message');
    const scenarioPreviewRoute = document.getElementById('telegram-scenario-preview-route');
    const botQrFrame = document.getElementById('telegram-bot-qr-frame');
    const botQrImage = document.getElementById('telegram-bot-qr-image');
    const botQrPlaceholder = document.getElementById('telegram-bot-qr-placeholder');
    const openBotQrLink = document.getElementById('btn-open-bot-qr');
    const downloadBotQrLink = document.getElementById('btn-download-bot-qr');
    const shareBotQrButton = document.getElementById('btn-share-bot-qr');
    const botQrUploadField = document.getElementById('telegram-bot-qr-upload');
    const removeBotQrInput = document.getElementById('telegram-remove-bot-qr-image');
    const removeBotQrButton = document.getElementById('btn-remove-bot-qr');
    const botQrMetaNode = document.getElementById('telegram-bot-qr-meta');
    const botQrSaveHint = document.getElementById('telegram-bot-qr-save-hint');
    let selectedQrFile = null;
    let selectedQrPreviewUrl = '';
    let savedQrImageUrl = safeText(config.botQrImageUrl);
    let savedQrDownloadUrl = safeText(config.botQrDownloadUrl);
    let savedQrImageName = safeText(config.botQrImageName);
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
            scenarioPreviewRoute.textContent = scenario.button_text
                ? 'ปุ่ม Mini App: ' + scenario.button_text + ' | Route: ' + routeToText(scenario.route || '')
                : 'Route: ' + routeToText(scenario.route || '');
        }
        if (animate && scenarioPreview) {
            scenarioPreview.classList.remove('is-updated');
            void scenarioPreview.offsetWidth;
            scenarioPreview.classList.add('is-updated');
        }
    }

    function setQrLinkState(element, href, disabled) {
        if (!element) {
            return;
        }
        element.href = disabled ? '#' : href;
        element.classList.toggle('disabled', disabled);
        element.setAttribute('aria-disabled', disabled ? 'true' : 'false');
        if (disabled) {
            element.setAttribute('tabindex', '-1');
        } else {
            element.removeAttribute('tabindex');
        }
    }

    function setQrUi(imageUrl, downloadUrl, fileName, isPendingSave) {
        const hasImage = !!imageUrl;
        if (botQrFrame) {
            botQrFrame.classList.toggle('is-empty', !hasImage);
        }
        if (botQrImage) {
            if (hasImage) {
                botQrImage.src = imageUrl;
                botQrImage.alt = fileName ? 'QR Code: ' + fileName : 'QR Code ของ Telegram Bot';
                botQrImage.classList.remove('d-none');
            } else {
                botQrImage.removeAttribute('src');
                botQrImage.alt = '';
                botQrImage.classList.add('d-none');
            }
        }
        if (botQrPlaceholder) {
            botQrPlaceholder.classList.toggle('d-none', hasImage);
        }

        setQrLinkState(openBotQrLink, imageUrl, !hasImage);
        setQrLinkState(downloadBotQrLink, downloadUrl || imageUrl, !hasImage);
        if (downloadBotQrLink) {
            if (fileName) {
                downloadBotQrLink.setAttribute('download', fileName);
            } else {
                downloadBotQrLink.removeAttribute('download');
            }
        }
        if (shareBotQrButton) {
            shareBotQrButton.disabled = !hasImage;
        }
        if (removeBotQrButton) {
            removeBotQrButton.disabled = !hasImage;
        }
        if (botQrMetaNode) {
            botQrMetaNode.textContent = hasImage
                ? (fileName || 'รูป QR Code พร้อมใช้งาน')
                : 'ยังไม่มีรูป QR Code ที่อัปโหลด';
        }
        if (botQrSaveHint) {
            botQrSaveHint.classList.toggle('d-none', !isPendingSave);
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
            if (!miniAppBaseUrl) {
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
                flash('success', 'ส่งการแจ้งเตือนทดสอบแล้ว', result.message || 'ผู้ใช้จะได้รับข้อความพร้อมปุ่ม Mini App');
            } catch (error) {
                flash('error', 'ส่งการแจ้งเตือนไม่สำเร็จ', error.message || 'กรุณาตรวจสอบ URL, token หรือผู้รับ');
            }
        });
    }

    if (botQrUploadField) {
        botQrUploadField.addEventListener('change', function () {
            if (selectedQrPreviewUrl) {
                URL.revokeObjectURL(selectedQrPreviewUrl);
                selectedQrPreviewUrl = '';
            }
            selectedQrFile = botQrUploadField.files && botQrUploadField.files[0] ? botQrUploadField.files[0] : null;
            if (!selectedQrFile) {
                setQrUi(savedQrImageUrl, savedQrDownloadUrl, savedQrImageName, false);
                return;
            }
            selectedQrPreviewUrl = URL.createObjectURL(selectedQrFile);
            if (removeBotQrInput) {
                removeBotQrInput.value = '0';
            }
            setQrUi(selectedQrPreviewUrl, selectedQrPreviewUrl, selectedQrFile.name, true);
        });
    }

    if (removeBotQrButton) {
        removeBotQrButton.addEventListener('click', function () {
            if (selectedQrPreviewUrl) {
                URL.revokeObjectURL(selectedQrPreviewUrl);
                selectedQrPreviewUrl = '';
            }
            selectedQrFile = null;
            if (botQrUploadField) {
                botQrUploadField.value = '';
            }
            if (removeBotQrInput) {
                removeBotQrInput.value = '1';
            }
            savedQrImageUrl = '';
            savedQrDownloadUrl = '';
            savedQrImageName = '';
            setQrUi('', '', '', true);
        });
    }

    if (shareBotQrButton) {
        shareBotQrButton.addEventListener('click', async function () {
            const username = getBotUsername();
            const qrImageUrl = selectedQrPreviewUrl || savedQrImageUrl;
            const qrFileName = selectedQrFile ? selectedQrFile.name : (savedQrImageName || 'telegram_bot_qr.png');
            if (!qrImageUrl) {
                flash('warning', 'ยังไม่มี QR Code', 'กรุณาอัปโหลดรูป QR Code ก่อน');
                return;
            }

            try {
                if (navigator.share) {
                    if (navigator.canShare && window.File && window.File.prototype) {
                        let file = selectedQrFile;
                        if (!file) {
                            const response = await fetch(qrImageUrl, {
                                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                            });
                            const blob = await response.blob();
                            file = new File([blob], qrFileName, { type: blob.type || 'image/png' });
                        }
                        if (navigator.canShare({ files: [file] })) {
                            await navigator.share({
                                files: [file],
                                title: 'Telegram Bot QR Code',
                                text: username
                                    ? 'สแกน QR เพื่อเพิ่ม @' + username
                                    : 'สแกน QR เพื่อเพิ่ม Telegram Bot',
                            });
                            return;
                        }
                    }

                    await navigator.share({
                        title: 'Telegram Bot',
                        text: username ? 'เพิ่ม @' + username + ' เพื่อรับแจ้งเตือน ERP' : 'เพิ่ม Telegram Bot เพื่อรับแจ้งเตือน ERP',
                        url: qrImageUrl,
                    });
                    return;
                }

                if (navigator.clipboard && navigator.clipboard.writeText) {
                    await navigator.clipboard.writeText(qrImageUrl);
                    flash('success', 'คัดลอกลิงก์รูป QR แล้ว', qrImageUrl);
                    return;
                }

                window.open(qrImageUrl, '_blank', 'noopener');
            } catch (error) {
                if (error && error.name === 'AbortError') {
                    return;
                }
                flash('error', 'แชร์ QR Code ไม่สำเร็จ', error.message || 'เปิดรูป QR แล้วส่งต่อด้วยตนเองได้');
            }
        });
    }

    if (botUsernameField) {
        botUsernameField.addEventListener('input', normalizeBotUsernameInput);
        normalizeBotUsernameInput();
    }
    setQrUi(savedQrImageUrl, savedQrDownloadUrl, savedQrImageName, false);
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
                    <div class="d-flex flex-wrap gap-2">
                        <a href="#settings-general" class="btn btn-light border rounded-3">ตั้งค่า Bot</a>
                        <a href="#settings-mini-app" class="btn btn-light border rounded-3">Mini App</a>
                        <a href="#settings-testing" class="btn btn-light border rounded-3">ทดสอบ</a>
                        <a href="#settings-bindings" class="btn btn-light border rounded-3">ผู้ใช้ที่ผูกแล้ว</a>
                    </div>
                </div>
            </div>
            <div class="d-grid gap-2" style="min-width:min(100%, 280px);">
                <button type="submit" form="telegram-settings-form" class="btn btn-primary rounded-3 shadow-sm">
                    <i class="fa-solid fa-floppy-disk me-1"></i> บันทึกการตั้งค่า
                </button>
                <div class="bot-share-card">
                    <div class="d-flex flex-column flex-sm-row gap-3">
                        <div class="bot-qr-frame<?= $botQrImageUrl === '' ? ' is-empty' : '' ?>" id="telegram-bot-qr-frame">
                            <img
                                id="telegram-bot-qr-image"
                                <?= $botQrImageUrl !== '' ? 'src="' . Html::encode($botQrImageUrl) . '"' : '' ?>
                                alt="<?= Html::encode($botQrImageName !== '' ? 'QR Code: ' . $botQrImageName : 'QR Code ของ Telegram Bot') ?>"
                                class="<?= $botQrImageUrl === '' ? 'd-none' : '' ?>"
                            >
                            <div class="bot-qr-placeholder<?= $botQrImageUrl === '' ? '' : ' d-none' ?>" id="telegram-bot-qr-placeholder">
                                <i class="fa-solid fa-qrcode fs-2"></i>
                                <span>อัปโหลด QR Code</span>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold mb-1">QR Code ที่อัปโหลด</div>
                            <div class="muted-note">ใช้รูป QR Code ที่ผู้ดูแลเตรียมไว้เอง สำหรับเปิดรูป ดาวน์โหลด หรือแชร์ให้ผู้ใช้เพิ่ม bot</div>
                            <div class="bot-upload-control mt-3">
                                <?= Html::hiddenInput('remove_bot_qr_image', '0', [
                                    'id' => 'telegram-remove-bot-qr-image',
                                    'form' => 'telegram-settings-form',
                                ]) ?>
                                <?= Html::fileInput('bot_qr_image', null, [
                                    'id' => 'telegram-bot-qr-upload',
                                    'class' => 'form-control',
                                    'accept' => 'image/png,image/jpeg,image/webp,image/gif',
                                    'form' => 'telegram-settings-form',
                                ]) ?>
                                <div class="bot-qr-meta mt-2" id="telegram-bot-qr-meta">
                                    <?= Html::encode($botQrImageName !== '' ? $botQrImageName : 'ยังไม่มีรูป QR Code ที่อัปโหลด') ?>
                                </div>
                                <div class="bot-qr-save-hint mt-2 d-none" id="telegram-bot-qr-save-hint">
                                    กดบันทึกการตั้งค่าเพื่อใช้รูป QR Code นี้
                                </div>
                            </div>
                            <div class="bot-qr-actions d-flex flex-wrap gap-2 mt-3">
                                <a
                                    href="<?= Html::encode($botQrImageUrl !== '' ? $botQrImageUrl : '#') ?>"
                                    target="_blank"
                                    rel="noopener"
                                    class="btn btn-sm btn-outline-primary rounded-3<?= $botQrImageUrl === '' ? ' disabled' : '' ?>"
                                    id="btn-open-bot-qr"
                                    aria-disabled="<?= $botQrImageUrl === '' ? 'true' : 'false' ?>"
                                    <?= $botQrImageUrl === '' ? 'tabindex="-1"' : '' ?>
                                >
                                    <i class="fa-regular fa-image me-1"></i> เปิดรูป QR
                                </a>
                                <a
                                    href="<?= Html::encode($botQrDownloadUrl !== '' ? $botQrDownloadUrl : '#') ?>"
                                    class="btn btn-sm btn-outline-secondary rounded-3<?= $botQrDownloadUrl === '' ? ' disabled' : '' ?>"
                                    id="btn-download-bot-qr"
                                    aria-disabled="<?= $botQrDownloadUrl === '' ? 'true' : 'false' ?>"
                                    <?= $botQrDownloadUrl === '' ? 'tabindex="-1"' : '' ?>
                                >
                                    <i class="fa-solid fa-download me-1"></i> ดาวน์โหลด QR
                                </a>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-primary rounded-3"
                                    id="btn-share-bot-qr"
                                    <?= $botQrImageUrl === '' ? 'disabled' : '' ?>
                                >
                                    <i class="fa-solid fa-share-nodes me-1"></i> แชร์ QR
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-danger rounded-3"
                                    id="btn-remove-bot-qr"
                                    <?= $botQrImageUrl === '' ? 'disabled' : '' ?>
                                >
                                    <i class="fa-regular fa-trash-can me-1"></i> ลบรูป QR
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row row-cols-1 row-cols-md-2 row-cols-xxl-4 g-3 mt-4">
            <div class="col">
                <div class="summary-tile p-3 h-100">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                        <div class="bot-mark" style="width:2.5rem;height:2.5rem;border-radius:.85rem;">
                            <i class="fa-solid fa-plug-circle-check"></i>
                        </div>
                        <span class="status-pill <?= $botTokenValue !== '' ? 'is-neutral' : 'is-warning' ?>" data-status-kind="bot">
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
                        <div class="bot-mark" style="width:2.5rem;height:2.5rem;border-radius:.85rem;">
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
                        <div class="bot-mark" style="width:2.5rem;height:2.5rem;border-radius:.85rem;">
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
                        <div class="bot-mark" style="width:2.5rem;height:2.5rem;border-radius:.85rem;">
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
            'enctype' => 'multipart/form-data',
        ],
    ]); ?>

    <div class="d-none">
        <?= $form->field($model, 'name')->hiddenInput()->label(false) ?>
        <?= $form->field($model, 'code')->hiddenInput()->label(false) ?>
        <?= $form->field($model, 'title')->hiddenInput()->label(false) ?>
    </div>

    <div class="row g-4 align-items-start">
        <div class="col-12 col-xl-8">
            <div class="setting-panel p-4 p-xl-5 mb-4" id="settings-general">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                    <div>
                        <h2 class="panel-title">General</h2>
                        <p class="panel-desc mb-0">ตั้งค่า token, username และ webhook ที่รับ event จาก Telegram Bot</p>
                    </div>
                    <span class="status-pill <?= $botTokenValue !== '' ? 'is-neutral' : 'is-warning' ?>" data-status-kind="bot">
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

            <div class="setting-panel p-4 p-xl-5 mb-4" id="settings-mini-app">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                    <div>
                        <h2 class="panel-title">Telegram Mini App</h2>
                        <p class="panel-desc mb-0">ทุกปุ่มเปิด ERP Mobile จะอ่าน URL จาก Mini App Base URL นี้ เปลี่ยน domain ได้โดยไม่แก้โค้ด</p>
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
                        <span class="step-dot"><i class="fa-solid fa-route"></i></span>
                        <div class="muted-note">
                            Flow ใหม่: ระบบเชื่อม Telegram กับผู้ใช้รายบุคคล แล้วเปิด ERP Mobile ผ่าน Mini App โดยใช้ URL ที่ตั้งค่าไว้
                        </div>
                    </div>
                </div>
            </div>

            <div class="setting-panel p-4 p-xl-5 mb-4" id="settings-testing">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                    <div>
                        <h2 class="panel-title">ทดสอบการแจ้งเตือน</h2>
                        <p class="panel-desc mb-0">ส่งข้อความ ปุ่ม Mini App และตัวอย่าง notification ตามระบบงานให้ผู้ใช้ที่ผูก Telegram แล้ว</p>
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

            <div class="setting-panel p-4 p-xl-5" id="settings-bindings">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                    <div>
                        <h2 class="panel-title">บัญชีที่ผูก Telegram แล้ว</h2>
                        <p class="panel-desc mb-0">รายชื่อผู้ใช้ที่ระบบสามารถส่งแจ้งเตือนรายบุคคลได้ทันที</p>
                    </div>
                    <span class="status-pill is-neutral"><?= number_format($linkedUserCount) ?> users</span>
                </div>

                <div class="table-responsive">
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
                            <?php if (empty($bindings)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-5">
                                        <div class="empty-state p-4">
                                            <div class="fw-bold mb-1">ยังไม่มีผู้ใช้ที่ผูก Telegram</div>
                                            <div class="muted-note">ให้ผู้ใช้เชื่อมต่อ Telegram กับบัญชี ERP ตาม flow ที่ระบบกำหนดไว้</div>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($bindings as $binding): ?>
                                    <?php
                                    $employee = $binding->employee ?? null;
                                    $employeeName = trim((string) ($binding->fullname ?: ($employee ? ($employee->fullname ?? '') : $binding->username)));
                                    $departmentName = $employee ? trim((string) ($employee->departmentName() ?: '-')) : '-';
                                    $positionName = $employee ? trim((string) ($employee->positionName() ?: '-')) : '-';
                                    $telegramId = trim((string) ($binding->telegram_id ?? ''));
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold"><?= Html::encode($employeeName !== '' ? $employeeName : '-') ?></div>
                                            <div class="muted-note"><?= Html::encode($binding->username ?? '') ?></div>
                                        </td>
                                        <td>
                                            <div><?= Html::encode($departmentName !== '' ? $departmentName : '-') ?></div>
                                            <div class="muted-note"><?= Html::encode($positionName !== '' ? $positionName : '-') ?></div>
                                        </td>
                                        <td><code><?= Html::encode($telegramId) ?></code></td>
                                        <td class="text-center"><span class="status-pill is-success">พร้อมรับแจ้งเตือน</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="setting-panel p-4 mb-4">
                <h2 class="panel-title mb-3">แนวคิดใหม่</h2>
                <div class="d-grid gap-3">
                    <div class="flow-step">
                        <span class="step-dot"><i class="fa-solid fa-user-plus"></i></span>
                        <div>
                            <div class="fw-semibold">เชื่อมผู้ใช้ Telegram</div>
                            <div class="muted-note">ระบบบันทึก Telegram Chat ID กับบัญชีผู้ใช้รายบุคคล</div>
                        </div>
                    </div>
                    <div class="flow-step">
                        <span class="step-dot"><i class="fa-solid fa-right-to-bracket"></i></span>
                        <div>
                            <div class="fw-semibold">เปิด ERP Mobile</div>
                            <div class="muted-note">Mini App ส่ง Telegram user id ไปที่หน้า login</div>
                        </div>
                    </div>
                    <div class="flow-step">
                        <span class="step-dot"><i class="fa-solid fa-link"></i></span>
                        <div>
                            <div class="fw-semibold">ผูกบัญชี</div>
                            <div class="muted-note">เมื่อ login สำเร็จ ระบบบันทึก Telegram Chat ID ให้ผู้ใช้คนนั้น</div>
                        </div>
                    </div>
                    <div class="flow-step">
                        <span class="step-dot"><i class="fa-regular fa-bell"></i></span>
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
</div>
