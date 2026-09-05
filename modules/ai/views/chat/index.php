<?php

use app\modules\ai\models\AiConversation;
use app\modules\ai\models\AiMessage;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string|null $conversationId */
/** @var array<int, string> $providerCodes */
/** @var array<int, AiConversation> $conversations */
/** @var array<int, AiMessage> $messages */
/** @var array{connected: bool, source: string|null, masked: string|null, selected_model: string|null, assistant_widget_enabled: bool} $openRouterConnection */

$this->title = 'ผู้ช่วย AI';
$sendUrl = Url::to(['/ai/chat/send']);
$streamUrl = Url::to(['/ai/chat/stream']);
$connectionUrl = Url::to(['/ai/chat/openrouter-connection']);
$modelsUrl = Url::to(['/ai/chat/openrouter-models']);
$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->csrfToken;
$openRouterConnected = (bool) ($openRouterConnection['connected'] ?? false);
$openRouterSource = (string) ($openRouterConnection['source'] ?? '');
$openRouterMasked = (string) ($openRouterConnection['masked'] ?? '');
$selectedModel = (string) ($openRouterConnection['selected_model'] ?? '');
$assistantWidgetEnabled = (bool) ($openRouterConnection['assistant_widget_enabled'] ?? true);
$providerCode = $providerCodes[0] ?? 'openrouter';
$currentConversationTitle = 'บทสนทนาใหม่';
foreach ($conversations as $conversation) {
    if ($conversation->id === $conversationId) {
        $currentConversationTitle = $conversation->title;
        break;
    }
}

$this->params['breadcrumbs'][] = ['label' => $this->title, 'url' => ['/ai/chat/index']];
$this->params['breadcrumbs'][] = $currentConversationTitle;
?>

<?php $this->beginBlock('page-title'); ?>
<h4 class="fw-semibold mb-0"><?= Html::encode($this->title) ?></h4>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
สนทนากับข้อมูล ERP ที่ได้รับอนุญาตผ่าน OpenRouter
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?= $this->render('_menu', ['active' => 'chat']) ?>
<?php $this->endBlock(); ?>

<style>
    .page-content-wrapper > .page-title-box {
        width: 100% !important;
        margin-inline: 0 !important;
    }

    .ai-shell,
    .ai-connection-offcanvas {
        --ai-ink-1: #1a202c;
        --ai-ink-2: #4a5568;
        --ai-ink-3: #718096;
        --ai-surface: #ffffff;
        --ai-surface-2: #f7f9fc;
        --ai-surface-3: #eef2f7;
        --ai-surface-hover: #f1f5f9;
        --ai-line: rgba(15, 23, 42, 0.08);
        --ai-line-strong: rgba(15, 23, 42, 0.14);
        --ai-primary: #0d6efd;
        --ai-primary-soft: rgba(13, 110, 253, 0.08);
        --ai-success: #15803d;
        --ai-success-soft: rgba(21, 128, 61, 0.10);
        --ai-warning: #b45309;
        --ai-warning-soft: rgba(180, 83, 9, 0.10);
        --ai-radius: 10px;
        --ai-radius-sm: 8px;
        --ai-radius-xs: 6px;
    }

    .ai-shell {
        display: grid;
        grid-template-columns: minmax(220px, 280px) minmax(0, 1fr);
        min-height: calc(100vh - 150px);
        border: 1px solid var(--ai-line);
        border-radius: var(--ai-radius);
        background: var(--ai-surface-2);
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04), 0 1px 1px rgba(15, 23, 42, 0.03);
        overflow: hidden;
    }

    .ai-sidebar {
        border-right: 1px solid var(--ai-line);
        background: var(--ai-surface);
        overflow: hidden;
        scroll-margin-top: 120px;
    }

    .ai-sidebar-header,
    .ai-chat-header {
        min-height: 64px;
        padding: 14px 16px;
        border-bottom: 1px solid var(--ai-line);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .ai-thread-list {
        max-height: calc(100vh - 230px);
        overflow: auto;
        padding: 8px;
    }

    .ai-thread-link {
        display: block;
        padding: 10px 12px;
        border-radius: var(--ai-radius-xs);
        color: var(--ai-ink-1);
        text-decoration: none;
        border: 1px solid transparent;
        transition: color 120ms ease, background-color 120ms ease, border-color 120ms ease;
    }

    .ai-thread-link:hover,
    .ai-thread-link.is-active {
        background: var(--ai-surface-hover, #f1f5f9);
        border-color: var(--ai-line-strong);
        color: #0a58ca;
    }

    .ai-thread-link:focus-visible {
        outline: 3px solid var(--ai-primary-soft);
        border-color: var(--ai-primary);
    }

    .ai-thread-title {
        font-weight: 600;
        font-size: 13px;
        line-height: 1.35;
        overflow-wrap: anywhere;
    }

    .ai-thread-meta {
        margin-top: 3px;
        color: var(--ai-ink-3);
        font-size: 12px;
    }

    .ai-chat {
        display: grid;
        grid-template-rows: auto minmax(0, 1fr) auto;
        min-width: 0;
        background: var(--ai-surface);
    }

    .ai-chat-title {
        font-size: 18px;
        font-weight: 700;
        color: var(--ai-ink-1);
        margin: 0;
    }

    .ai-provider-badge {
        padding: 5px 9px;
        border: 0;
        border-radius: 999px;
        color: var(--ai-warning);
        background: var(--ai-warning-soft);
        font-size: 13px;
        font-weight: 600;
        white-space: nowrap;
        cursor: pointer;
    }

    .ai-provider-badge.is-connected {
        color: var(--ai-success);
        background: var(--ai-success-soft);
    }

    .ai-provider-badge:focus-visible {
        outline: 3px solid var(--ai-primary-soft);
        box-shadow: 0 0 0 1px var(--ai-primary);
    }

    .ai-messages {
        padding: 18px;
        overflow: auto;
    }

    .ai-message {
        display: grid;
        gap: 6px;
        margin-bottom: 14px;
        max-width: 880px;
    }

    .ai-message.is-user {
        margin-left: auto;
        justify-items: end;
    }

    .ai-message-body {
        padding: 10px 12px;
        border-radius: 8px;
        line-height: 1.55;
        white-space: pre-wrap;
        overflow-wrap: anywhere;
        background: var(--ai-surface);
        color: var(--ai-ink-1);
        border: 1px solid var(--ai-line);
    }

    .ai-message.is-user .ai-message-body {
        background: #1d4f91;
        border-color: #1d4f91;
        color: #ffffff;
    }

    .ai-message.is-tool .ai-message-body {
        background: #f0f6f2;
        border-color: #c9dfd0;
        color: #1d3f2a;
        font-size: 12px;
    }

    .ai-message-label {
        color: var(--ai-ink-3);
        font-size: 12px;
        font-weight: 600;
    }

    .ai-composer {
        border-top: 1px solid var(--ai-line);
        background: var(--ai-surface);
        padding: 12px;
    }

    .ai-connection-offcanvas {
        --bs-offcanvas-width: 420px;
        z-index: 1060;
        color: var(--ai-ink-1);
        background: var(--ai-surface);
    }

    .offcanvas-backdrop.show {
        z-index: 1055;
    }

    .ai-connection-offcanvas .offcanvas-header {
        min-height: 64px;
        padding: 14px 18px;
        border-bottom: 1px solid var(--ai-line);
    }

    .ai-connection-offcanvas .offcanvas-title {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 16px;
        font-weight: 700;
    }

    .ai-connection-offcanvas .offcanvas-body {
        padding: 18px;
    }

    .ai-openrouter-panel {
        display: grid;
        gap: 20px;
    }

    .ai-openrouter-status {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px;
        border: 1px solid var(--ai-line-strong);
        border-radius: var(--ai-radius-xs);
        background: var(--ai-surface-2);
    }

    .ai-openrouter-dot {
        width: 10px;
        height: 10px;
        flex: 0 0 10px;
        border-radius: 999px;
        background: var(--ai-warning);
    }

    .ai-connection-offcanvas.is-connected .ai-openrouter-dot {
        background: var(--ai-success);
    }

    .ai-openrouter-copy {
        margin-top: 2px;
        color: var(--ai-ink-3);
        font-size: 12px;
        line-height: 1.35;
    }

    .ai-openrouter-form {
        display: grid;
        gap: 12px;
    }

    .ai-connection-field {
        display: grid;
        gap: 6px;
    }

    .ai-connection-field label {
        color: var(--ai-ink-2);
        font-size: 13px;
        font-weight: 600;
    }

    .ai-connection-actions {
        display: flex;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 8px;
    }

    .ai-settings-section {
        display: grid;
        gap: 14px;
        padding-top: 18px;
        border-top: 1px solid var(--ai-line);
    }

    .ai-widget-setting {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        align-items: center;
        gap: 12px;
        padding-bottom: 14px;
        border-bottom: 1px solid var(--ai-line);
    }

    .ai-widget-setting__label {
        display: block;
        margin: 0;
        color: var(--ai-ink-1);
        font-size: 0.875rem;
        font-weight: 700;
        line-height: 1.3;
        cursor: pointer;
    }

    .ai-widget-setting__status {
        margin-top: 3px;
        color: var(--ai-ink-3);
        font-size: 0.75rem;
        line-height: 1.3;
    }

    .ai-widget-setting .form-check-input {
        margin: 0;
        cursor: pointer;
    }

    .ai-composer-row {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 10px;
        align-items: end;
    }

    .ai-composer textarea {
        min-height: 56px;
        max-height: 160px;
        resize: vertical;
    }

    .ai-model-field {
        display: grid;
        gap: 8px;
        min-width: 0;
    }

    .ai-model-label {
        color: var(--ai-ink-2);
        font-size: 13px;
        font-weight: 600;
    }

    .ai-model-filters {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px 10px;
    }

    .ai-model-search {
        flex: 1 1 220px;
        min-width: 0;
    }

    .ai-model-search .input-group-text {
        color: var(--ai-ink-2);
        background: var(--ai-surface-2);
        border-color: var(--ai-line-strong);
    }

    .ai-model-search .form-control {
        min-width: 0;
        border-color: var(--ai-line-strong);
    }

    .ai-model-search .form-control::placeholder {
        color: var(--ai-ink-2);
        opacity: 1;
    }

    .ai-free-filter {
        display: inline-flex;
        align-items: center;
        min-height: 31px;
        margin: 0;
        white-space: nowrap;
    }

    .ai-free-filter .form-check-input,
    .ai-free-filter .form-check-label {
        cursor: pointer;
    }

    .ai-model-row {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 42px;
        gap: 6px;
    }

    .ai-model-select {
        min-height: 42px;
        min-width: 0;
        border-color: var(--ai-line-strong);
        border-radius: var(--ai-radius-sm);
    }

    .ai-model-select:focus {
        border-color: var(--ai-primary);
        box-shadow: 0 0 0 3px var(--ai-primary-soft);
    }

    .ai-model-refresh {
        width: 42px;
        height: 42px;
        padding: 0;
        border-radius: var(--ai-radius-sm);
    }

    .ai-model-status {
        min-height: 16px;
        color: var(--ai-ink-3);
        font-size: 12px;
        line-height: 1.3;
        overflow-wrap: anywhere;
    }

    .ai-model-status.is-error {
        color: #b91c1c;
    }

    .ai-stream-control {
        min-height: 42px;
        display: inline-flex;
        align-items: center;
        padding-top: 14px;
        border-top: 1px solid var(--ai-line);
    }

    .ai-error {
        color: #b91c1c;
        margin-top: 8px;
        font-size: 13px;
    }

    @media (max-width: 860px) {
        .ai-shell {
            grid-template-columns: 1fr;
        }

        .ai-sidebar {
            border-right: 0;
            border-bottom: 1px solid var(--ai-line);
        }

        .ai-thread-list {
            max-height: 170px;
        }

    }

    @media (max-width: 640px) {
        .ai-connection-offcanvas {
            --bs-offcanvas-width: 100vw;
        }

        .ai-model-filters {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
        }

        .ai-model-search {
            width: 100%;
        }

        .ai-connection-actions .btn {
            flex: 1 1 auto;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .ai-thread-link {
            transition: none;
        }
    }
</style>

<div class="ai-shell">
    <aside id="ai-conversations" class="ai-sidebar" aria-label="ประวัติการสนทนา">
        <div class="ai-sidebar-header">
            <strong>ประวัติการสนทนา</strong>
        </div>
        <div class="ai-thread-list">
            <?php if ($conversations === []): ?>
                <div class="px-2 py-3 text-muted small">ยังไม่มีประวัติ เริ่มส่งข้อความเพื่อสร้างบทสนทนาแรก</div>
            <?php endif; ?>
            <?php foreach ($conversations as $conversation): ?>
                <?php $active = $conversationId === $conversation->id; ?>
                <?= Html::a(
                    '<div class="ai-thread-title">' . Html::encode($conversation->title) . '</div>'
                    . '<div class="ai-thread-meta">' . Html::encode($conversation->provider) . ' · ' . Html::encode($conversation->updated_at) . '</div>',
                    ['/ai/chat/index', 'id' => $conversation->id],
                    ['class' => 'ai-thread-link' . ($active ? ' is-active' : '')]
                ) ?>
            <?php endforeach; ?>
        </div>
    </aside>

    <section class="ai-chat">
        <header class="ai-chat-header">
            <h2 class="ai-chat-title"><?= Html::encode($currentConversationTitle) ?></h2>
            <button id="ai-provider-status" type="button" class="ai-provider-badge <?= $openRouterConnected ? 'is-connected' : '' ?>" aria-live="polite" data-bs-toggle="offcanvas" data-bs-target="#openrouter-connection" aria-controls="openrouter-connection">
                <?= $openRouterConnected ? 'OpenRouter พร้อมใช้งาน' : 'OpenRouter ยังไม่เชื่อมต่อ' ?>
            </button>
        </header>

        <main id="ai-messages" class="ai-messages">
            <?php foreach ($messages as $message): ?>
                <?php if ($message->role === AiMessage::ROLE_SYSTEM) {
                    continue;
                } ?>
                <article class="ai-message is-<?= Html::encode($message->role) ?>">
                    <div class="ai-message-label"><?= Html::encode($message->role === AiMessage::ROLE_USER ? 'คุณ' : ($message->role === AiMessage::ROLE_TOOL ? 'เครื่องมือ' : 'ผู้ช่วย AI')) ?></div>
                    <div class="ai-message-body"><?= Html::encode((string) $message->content) ?></div>
                </article>
            <?php endforeach; ?>
        </main>

        <form id="ai-composer" class="ai-composer">
            <input type="hidden" name="<?= Html::encode($csrfParam) ?>" value="<?= Html::encode($csrfToken) ?>">
            <input type="hidden" id="ai-conversation-id" value="<?= Html::encode((string) $conversationId) ?>">
            <input type="hidden" id="ai-provider" value="<?= Html::encode($providerCode) ?>">
            <div class="ai-composer-row">
                <textarea id="ai-message-input" class="form-control" placeholder="ถามข้อมูลในระบบ ERP..." required></textarea>
                <button id="ai-send-button" type="submit" class="btn btn-primary"><i class="bi bi-send me-1" aria-hidden="true"></i>ส่งข้อความ</button>
            </div>
            <div id="ai-error" class="ai-error" role="alert" aria-live="polite" hidden></div>
        </form>
    </section>
</div>

<aside id="openrouter-connection" class="offcanvas offcanvas-end ai-connection-offcanvas <?= $openRouterConnected ? 'is-connected' : 'is-missing' ?>" tabindex="-1" aria-labelledby="openrouter-connection-title">
    <div class="offcanvas-header">
        <h5 id="openrouter-connection-title" class="offcanvas-title mb-0">
            <i class="bi bi-plug" aria-hidden="true"></i>
            การเชื่อมต่อ OpenRouter
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="ปิด"></button>
    </div>
    <div class="offcanvas-body">
        <section class="ai-openrouter-panel" aria-label="ตั้งค่าการเชื่อมต่อ OpenRouter">
            <div class="ai-openrouter-status">
                <span class="ai-openrouter-dot" aria-hidden="true"></span>
                <div>
                    <strong id="ai-openrouter-state-title"><?= $openRouterConnected ? 'OpenRouter พร้อมใช้งาน' : 'ยังไม่เชื่อมต่อ' ?></strong>
                    <div id="ai-openrouter-status" class="ai-openrouter-copy" aria-live="polite">
                        <?= Html::encode($openRouterConnected ? ('เชื่อมต่อแล้ว' . ($openRouterMasked !== '' ? ' · ' . $openRouterMasked : '') . ($openRouterSource === 'environment' ? ' · คีย์ของระบบ' : '')) : 'ต้องเชื่อมต่อ API key ก่อนใช้งาน') ?>
                    </div>
                </div>
            </div>
            <form id="ai-openrouter-form" class="ai-openrouter-form">
                <div class="ai-connection-field">
                    <label for="ai-openrouter-key">OpenRouter API key</label>
                    <input id="ai-openrouter-key" class="form-control" type="password" placeholder="sk-or-v1-..." autocomplete="off">
                </div>
                <div class="ai-connection-actions">
                    <button id="ai-openrouter-save" type="submit" class="btn btn-primary">เชื่อมต่อ</button>
                    <button id="ai-openrouter-clear" type="button" class="btn btn-outline-secondary" <?= $openRouterSource === 'session' ? '' : 'hidden' ?>>ล้างคีย์</button>
                </div>
            </form>
            <div id="ai-connection-error" class="ai-error" role="alert" aria-live="polite" hidden></div>
            <section class="ai-settings-section" aria-label="การตั้งค่า OpenRouter และผู้ช่วย AI">
                <div class="ai-widget-setting">
                    <div>
                        <label class="ai-widget-setting__label" for="ai-assistant-widget-enabled">แสดงผู้ช่วย AI ทุกหน้า</label>
                        <div id="ai-assistant-widget-status" class="ai-widget-setting__status" aria-live="polite">
                            <?= $assistantWidgetEnabled ? 'กำลังแสดงอยู่ทุกหน้า' : 'ซ่อนอยู่ทุกหน้า' ?>
                        </div>
                    </div>
                    <div class="form-check form-switch m-0">
                        <input
                            id="ai-assistant-widget-enabled"
                            class="form-check-input"
                            type="checkbox"
                            role="switch"
                            aria-describedby="ai-assistant-widget-status"
                            <?= $assistantWidgetEnabled ? 'checked' : '' ?>
                        >
                    </div>
                </div>
                <div class="ai-model-field">
                    <label class="ai-model-label" for="ai-model">โมเดล AI</label>
                    <div class="ai-model-filters">
                        <div class="input-group input-group-sm ai-model-search">
                            <span class="input-group-text" aria-hidden="true"><i class="bi bi-search"></i></span>
                            <input id="ai-model-search" class="form-control" type="search" placeholder="ค้นหาชื่อหรือรหัสโมเดล" aria-label="ค้นหาโมเดล AI" autocomplete="off" disabled>
                        </div>
                        <div class="form-check form-switch ai-free-filter">
                            <input id="ai-free-only" class="form-check-input" type="checkbox" role="switch" disabled>
                            <label class="form-check-label" for="ai-free-only">เฉพาะโมเดลฟรี</label>
                        </div>
                    </div>
                    <div class="ai-model-row">
                        <select id="ai-model" class="form-select ai-model-select" disabled>
                            <option value=""><?= $openRouterConnected ? 'กำลังโหลดรายการโมเดล...' : 'เชื่อมต่อ OpenRouter ก่อน' ?></option>
                        </select>
                        <button id="ai-model-refresh" type="button" class="btn btn-light ai-model-refresh" title="โหลดรายการโมเดลใหม่" aria-label="โหลดรายการโมเดลใหม่" <?= $openRouterConnected ? '' : 'disabled' ?>>
                            <i class="bi bi-arrow-clockwise" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div id="ai-model-status" class="ai-model-status" aria-live="polite">
                        <?= Html::encode($openRouterConnected ? ($selectedModel !== '' ? 'โมเดลปัจจุบัน: ' . $selectedModel : 'กำลังโหลดรายการโมเดล...') : 'เชื่อมต่อ API key เพื่อดูโมเดลที่ใช้งานได้') ?>
                    </div>
                </div>
                <label class="form-check form-switch mb-0 ai-stream-control">
                    <input id="ai-stream" class="form-check-input" type="checkbox" role="switch" value="1">
                    <span class="form-check-label">แสดงคำตอบทันที</span>
                </label>
            </section>
        </section>
    </div>
</aside>

<script>
(() => {
    const sendUrl = <?= json_encode($sendUrl, JSON_UNESCAPED_SLASHES) ?>;
    const streamUrl = <?= json_encode($streamUrl, JSON_UNESCAPED_SLASHES) ?>;
    const connectionUrl = <?= json_encode($connectionUrl, JSON_UNESCAPED_SLASHES) ?>;
    const modelsUrl = <?= json_encode($modelsUrl, JSON_UNESCAPED_SLASHES) ?>;
    const csrfToken = <?= json_encode($csrfToken) ?>;
    let openRouterConnected = <?= json_encode($openRouterConnected) ?>;
    let selectedModel = <?= json_encode($selectedModel) ?>;
    let assistantWidgetEnabled = <?= json_encode($assistantWidgetEnabled) ?>;
    let modelCatalog = [];
    let modelCatalogReady = false;
    let modelCatalogLoading = false;
    const messages = document.getElementById('ai-messages');
    const form = document.getElementById('ai-composer');
    const input = document.getElementById('ai-message-input');
    const sendButton = document.getElementById('ai-send-button');
    const provider = document.getElementById('ai-provider');
    const conversationInput = document.getElementById('ai-conversation-id');
    const streamToggle = document.getElementById('ai-stream');
    const errorBox = document.getElementById('ai-error');
    const connectionPanel = document.getElementById('openrouter-connection');
    const providerStatus = document.getElementById('ai-provider-status');
    const connectionForm = document.getElementById('ai-openrouter-form');
    const connectionKey = document.getElementById('ai-openrouter-key');
    const connectionStateTitle = document.getElementById('ai-openrouter-state-title');
    const connectionStatus = document.getElementById('ai-openrouter-status');
    const connectionSave = document.getElementById('ai-openrouter-save');
    const connectionClear = document.getElementById('ai-openrouter-clear');
    const connectionError = document.getElementById('ai-connection-error');
    const assistantWidgetToggle = document.getElementById('ai-assistant-widget-enabled');
    const assistantWidgetStatus = document.getElementById('ai-assistant-widget-status');
    const modelSelect = document.getElementById('ai-model');
    const modelSearch = document.getElementById('ai-model-search');
    const freeOnly = document.getElementById('ai-free-only');
    const modelRefresh = document.getElementById('ai-model-refresh');
    const modelStatus = document.getElementById('ai-model-status');

    const appendMessage = (role, content) => {
        const item = document.createElement('article');
        item.className = `ai-message is-${role}`;

        const label = document.createElement('div');
        label.className = 'ai-message-label';
        label.textContent = role === 'user' ? 'คุณ' : role === 'tool' ? 'เครื่องมือ' : 'ผู้ช่วย AI';

        const body = document.createElement('div');
        body.className = 'ai-message-body';
        body.textContent = content;

        item.append(label, body);
        messages.append(item);
        messages.scrollTop = messages.scrollHeight;
        return body;
    };

    const setBusy = (busy) => {
        const blocked = busy || !openRouterConnected;
        sendButton.disabled = blocked;
        input.disabled = blocked;
        provider.disabled = busy;
        streamToggle.disabled = blocked;
        connectionKey.disabled = busy;
        connectionSave.disabled = busy;
        connectionClear.disabled = busy || !openRouterConnected;
        assistantWidgetToggle.disabled = busy;
        modelSelect.disabled = busy || !openRouterConnected || !modelCatalogReady || modelCatalogLoading;
        modelSearch.disabled = busy || !openRouterConnected || modelCatalogLoading || modelCatalog.length === 0;
        freeOnly.disabled = busy || !openRouterConnected || modelCatalogLoading || modelCatalog.length === 0;
        modelRefresh.disabled = busy || !openRouterConnected || modelCatalogLoading;
    };

    const showError = (message) => {
        errorBox.hidden = !message;
        errorBox.textContent = message || '';
    };

    const showConnectionError = (message) => {
        connectionError.hidden = !message;
        connectionError.textContent = message || '';
    };

    const friendlyError = (error) => {
        const message = error instanceof Error ? error.message : String(error || '');
        if (/402|more credits|fewer max_tokens/i.test(message)) {
            return 'เครดิต OpenRouter ไม่เพียงพอสำหรับโมเดลนี้ กรุณาเลือกโมเดลฟรีหรือเติมเครดิตแล้วลองอีกครั้ง';
        }
        if (/429|rate.?limit|too many requests/i.test(message)) {
            return 'OpenRouter กำลังมีคำขอหนาแน่น กรุณารอสักครู่แล้วลองใหม่ หรือเลือกโมเดลอื่น';
        }
        if (/401|403|api key|not configured/i.test(message)) {
            return 'เชื่อมต่อ OpenRouter ไม่สำเร็จ กรุณาตรวจสอบ API key และโมเดลที่เลือก';
        }
        if (/HTTP status 5\d\d|provider (request|stream) failed/i.test(message)) {
            return 'OpenRouter ไม่สามารถตอบกลับได้ในขณะนี้ กรุณาลองอีกครั้ง';
        }
        // ปล่อยผ่านเฉพาะข้อความไทยสั้น ๆ ที่ตั้งใจสื่อสารกับผู้ใช้ กัน error ภายใน (SQL/exception/path) หลุด
        const isSafe = message
            && message.length <= 300
            && !/[\r\n]/.test(message)
            && /[฀-๿]/.test(message)
            && !/SQLSTATE|SQL being executed|INSERT INTO|SELECT |UPDATE |DELETE FROM|Exception|Stack trace|\/app\/|::/i.test(message);
        if (isSafe) {
            return message;
        }

        return 'ไม่สามารถติดต่อ OpenRouter ได้ กรุณาลองอีกครั้ง';
    };

    const setModelStatus = (message, isError = false) => {
        modelStatus.textContent = message || '';
        modelStatus.classList.toggle('is-error', isError);
    };

    const resetModelSelector = (message = 'เชื่อมต่อ API key เพื่อดูโมเดลที่ใช้งานได้') => {
        modelCatalogReady = false;
        modelCatalogLoading = false;
        modelCatalog = [];
        modelSearch.value = '';
        freeOnly.checked = false;
        modelSelect.replaceChildren(new Option('เชื่อมต่อ OpenRouter ก่อน', ''));
        setModelStatus(message);
        setBusy(false);
    };

    const formatContextLength = (value) => {
        const contextLength = Number(value || 0);
        return contextLength > 0 ? `${contextLength.toLocaleString('th-TH')} tokens` : '';
    };

    const renderModelOptions = () => {
        const query = modelSearch.value.trim().toLocaleLowerCase('th-TH');
        const models = modelCatalog.filter((model) => {
            const matchesSearch = query === ''
                || String(model.name || '').toLocaleLowerCase('th-TH').includes(query)
                || String(model.id || '').toLocaleLowerCase('th-TH').includes(query);
            return matchesSearch && (!freeOnly.checked || model.is_free === true);
        });

        if (models.length === 0) {
            modelCatalogReady = false;
            modelSelect.replaceChildren(new Option('ไม่พบโมเดลที่ตรงกับตัวกรอง', ''));
            setModelStatus('ไม่พบโมเดล ลองเปลี่ยนคำค้นหาหรือปิดตัวกรองเฉพาะฟรี');
            setBusy(false);
            return;
        }

        const selectedModelVisible = models.some((model) => model.id === selectedModel);
        const options = models.map((model) => {
            const name = model.name && model.name !== model.id
                ? `${model.name} (${model.id})`
                : model.id;
            const details = [];
            if (model.is_free === true) {
                details.push('ฟรี');
            }
            const context = formatContextLength(model.context_length);
            if (context) {
                details.push(context);
            }
            return new Option(details.length > 0 ? `${name} · ${details.join(' · ')}` : name, model.id);
        });

        if (!selectedModelVisible) {
            options.unshift(new Option('เลือกโมเดลจากผลการค้นหา', ''));
        }

        modelSelect.replaceChildren(...options);
        modelSelect.value = selectedModelVisible ? selectedModel : '';
        modelCatalogReady = true;
        const filterLabel = freeOnly.checked ? 'โมเดลฟรี' : 'โมเดล';
        const selectionLabel = selectedModelVisible
            ? ` · ใช้งาน ${selectedModel}`
            : ' · โมเดลปัจจุบันถูกซ่อนโดยตัวกรอง';
        setModelStatus(`แสดง ${models.length.toLocaleString('th-TH')} จาก ${modelCatalog.length.toLocaleString('th-TH')} ${filterLabel}${selectionLabel}`);
        setBusy(false);
    };

    const loadModels = async () => {
        if (!openRouterConnected) {
            resetModelSelector();
            return;
        }

        modelCatalogReady = false;
        modelCatalogLoading = true;
        modelSelect.replaceChildren(new Option('กำลังโหลดรายการโมเดล...', ''));
        setModelStatus('กำลังโหลดรายการโมเดลจาก OpenRouter...');
        setBusy(false);

        try {
            const response = await fetch(modelsUrl, {
                method: 'GET',
                headers: { 'Accept': 'application/json' }
            });
            const json = await response.json();
            if (!json.success) {
                throw new Error(json.error || 'โหลดรายการโมเดลไม่สำเร็จ');
            }

            modelCatalog = Array.isArray(json.data.models) ? json.data.models : [];
            if (modelCatalog.length === 0) {
                throw new Error('ไม่พบโมเดลที่ใช้งานได้สำหรับ API key นี้');
            }

            selectedModel = json.data.selected_model || modelCatalog[0].id;
            renderModelOptions();
        } catch (error) {
            modelCatalog = [];
            modelSelect.replaceChildren(new Option('โหลดรายการโมเดลไม่สำเร็จ', ''));
            setModelStatus(friendlyError(error), true);
        } finally {
            modelCatalogLoading = false;
            setBusy(false);
        }
    };

    const applyAssistantWidgetStatus = (enabled) => {
        assistantWidgetEnabled = Boolean(enabled);
        assistantWidgetToggle.checked = assistantWidgetEnabled;
        assistantWidgetStatus.textContent = assistantWidgetEnabled
            ? 'กำลังแสดงอยู่ทุกหน้า'
            : 'ซ่อนอยู่ทุกหน้า';

        const widgetRoot = document.getElementById('erp-ai-assistant');
        const currentEnabled = widgetRoot?.dataset.enabled === '1';
        if (widgetRoot && currentEnabled !== assistantWidgetEnabled) {
            if (window.erpAiAssistant?.setEnabled) {
                window.erpAiAssistant.setEnabled(assistantWidgetEnabled);
            } else {
                widgetRoot.dataset.enabled = assistantWidgetEnabled ? '1' : '0';
                widgetRoot.hidden = !assistantWidgetEnabled;
            }
        }
    };

    const applyConnectionStatus = (status) => {
        openRouterConnected = Boolean(status.connected);
        connectionPanel.classList.toggle('is-connected', openRouterConnected);
        connectionPanel.classList.toggle('is-missing', !openRouterConnected);
        providerStatus.classList.toggle('is-connected', openRouterConnected);
        providerStatus.textContent = openRouterConnected
            ? 'OpenRouter พร้อมใช้งาน'
            : 'OpenRouter ยังไม่เชื่อมต่อ';
        connectionStateTitle.textContent = openRouterConnected
            ? 'OpenRouter พร้อมใช้งาน'
            : 'ยังไม่เชื่อมต่อ';
        connectionStatus.textContent = openRouterConnected
            ? `เชื่อมต่อแล้ว${status.masked ? ` · ${status.masked}` : ''}${status.source === 'environment' ? ' · คีย์ของระบบ' : ''}`
            : (status.message || 'ต้องเชื่อมต่อ API key ก่อนใช้งาน');
        connectionClear.hidden = status.source !== 'session';
        connectionKey.value = '';
        showConnectionError('');
        selectedModel = status.selected_model || selectedModel;
        if (typeof status.assistant_widget_enabled === 'boolean') {
            applyAssistantWidgetStatus(status.assistant_widget_enabled);
        }
        setBusy(false);

        if (openRouterConnected) {
            void loadModels();
        } else {
            resetModelSelector();
        }
    };

    const payload = (message) => ({
        message,
        conversation_id: conversationInput.value || null,
        provider: provider.value,
        _csrf: csrfToken
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const text = input.value.trim();
        if (!text) {
            return;
        }

        if (!openRouterConnected) {
            showError('กรุณาเชื่อมต่อ OpenRouter API key ก่อนส่งข้อความ');
            connectionKey.focus();
            return;
        }

        showError('');
        appendMessage('user', text);
        input.value = '';
        setBusy(true);

        try {
            if (streamToggle.checked) {
                const assistantBody = appendMessage('assistant', '');
                const response = await fetch(streamUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': csrfToken
                    },
                    body: JSON.stringify(payload(text))
                });

                if (!response.body) {
                    throw new Error('เบราว์เซอร์นี้ไม่รองรับการแสดงคำตอบแบบทันที');
                }

                const reader = response.body.getReader();
                const decoder = new TextDecoder();
                let buffer = '';
                while (true) {
                    const { value, done } = await reader.read();
                    if (done) {
                        break;
                    }

                    buffer += decoder.decode(value, { stream: true });
                    const events = buffer.split('\n\n');
                    buffer = events.pop() || '';

                    for (const eventText of events) {
                        const line = eventText.split('\n').find((row) => row.startsWith('data: '));
                        if (!line) {
                            continue;
                        }
                        const data = JSON.parse(line.slice(6));
                        if (data.delta) {
                            assistantBody.textContent += data.delta;
                            messages.scrollTop = messages.scrollHeight;
                        }
                        if (data.conversation_id) {
                            conversationInput.value = data.conversation_id;
                        }
                        if (data.fallback_from && data.model) {
                            setModelStatus(`โมเดล ${data.fallback_from} ติด rate limit · ใช้ ${data.model} ชั่วคราว`);
                        }
                        if (data.error) {
                            showError(data.error);
                        }
                    }
                }
            } else {
                const response = await fetch(sendUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': csrfToken
                    },
                    body: JSON.stringify(payload(text))
                });
                const json = await response.json();

                if (!json.success) {
                    throw new Error(json.error || 'ส่งคำถามไปยัง AI ไม่สำเร็จ');
                }

                conversationInput.value = json.data.conversation_id;
                appendMessage('assistant', json.data.content || '');
                if (json.data.fallback_from && json.data.model) {
                    setModelStatus(`โมเดล ${json.data.fallback_from} ติด rate limit · ใช้ ${json.data.model} ชั่วคราว`);
                }
            }
        } catch (error) {
            showError(friendlyError(error));
        } finally {
            setBusy(false);
            input.focus();
        }
    });

    connectionForm.addEventListener('submit', async (event) => {
        event.preventDefault();

        const apiKey = connectionKey.value.trim();
        if (!apiKey) {
            connectionKey.focus();
            return;
        }

        showConnectionError('');
        setBusy(true);

        try {
            const response = await fetch(connectionUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken
                },
                body: JSON.stringify({ api_key: apiKey, _csrf: csrfToken })
            });
            const json = await response.json();
            if (!json.success) {
                throw new Error(json.error || 'เชื่อมต่อ OpenRouter ไม่สำเร็จ');
            }

            applyConnectionStatus(json.data);
        } catch (error) {
            showConnectionError(friendlyError(error));
            setBusy(false);
        }
    });

    connectionClear.addEventListener('click', async () => {
        showConnectionError('');
        setBusy(true);

        try {
            const response = await fetch(connectionUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken
                },
                body: JSON.stringify({ clear: true, _csrf: csrfToken })
            });
            const json = await response.json();
            if (!json.success) {
                throw new Error(json.error || 'ยกเลิกการเชื่อมต่อ OpenRouter ไม่สำเร็จ');
            }

            applyConnectionStatus(json.data);
        } catch (error) {
            showConnectionError(friendlyError(error));
            setBusy(false);
        }
    });

    assistantWidgetToggle.addEventListener('change', async () => {
        const nextEnabled = assistantWidgetToggle.checked;
        const previousEnabled = assistantWidgetEnabled;
        showConnectionError('');
        assistantWidgetStatus.textContent = 'กำลังบันทึกการตั้งค่า...';
        setBusy(true);

        try {
            const response = await fetch(connectionUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken
                },
                body: JSON.stringify({
                    assistant_widget_enabled: nextEnabled,
                    _csrf: csrfToken
                })
            });
            const json = await response.json();
            if (!json.success) {
                throw new Error(json.error || 'บันทึกการแสดงผู้ช่วย AI ไม่สำเร็จ');
            }

            applyAssistantWidgetStatus(Boolean(json.data.assistant_widget_enabled));
        } catch (error) {
            applyAssistantWidgetStatus(previousEnabled);
            showConnectionError(friendlyError(error));
        } finally {
            setBusy(false);
        }
    });

    modelRefresh.addEventListener('click', () => {
        void loadModels();
    });

    modelSearch.addEventListener('input', renderModelOptions);
    freeOnly.addEventListener('change', renderModelOptions);

    modelSelect.addEventListener('change', async () => {
        const nextModel = modelSelect.value;
        if (!nextModel || nextModel === selectedModel) {
            return;
        }

        const previousModel = selectedModel;
        modelCatalogLoading = true;
        setModelStatus('กำลังเปลี่ยนโมเดล...');
        setBusy(true);

        try {
            const response = await fetch(modelsUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken
                },
                body: JSON.stringify({ model: nextModel, _csrf: csrfToken })
            });
            const json = await response.json();
            if (!json.success) {
                throw new Error(json.error || 'เปลี่ยนโมเดลไม่สำเร็จ');
            }

            selectedModel = json.data.selected_model;
            setModelStatus(`ใช้งานโมเดล ${selectedModel} แล้ว`);
        } catch (error) {
            modelSelect.value = previousModel;
            setModelStatus(friendlyError(error), true);
        } finally {
            modelCatalogLoading = false;
            setBusy(false);
        }
    });

    messages.scrollTop = messages.scrollHeight;
    setBusy(false);
    if (openRouterConnected) {
        void loadModels();
    } else {
        resetModelSelector();
    }

    const showConnectionFromHash = () => {
        if (window.location.hash !== '#openrouter-connection' || !window.bootstrap?.Offcanvas) {
            return;
        }

        window.bootstrap.Offcanvas.getOrCreateInstance(connectionPanel).show();
    };

    if (document.readyState === 'complete') {
        showConnectionFromHash();
    } else {
        window.addEventListener('load', showConnectionFromHash, { once: true });
    }
    window.addEventListener('hashchange', showConnectionFromHash);
})();
</script>
