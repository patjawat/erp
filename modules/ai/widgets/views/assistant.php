<?php

use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\View;

/** @var View $this */
/** @var bool $connected */
/** @var string $selectedModel */
/** @var bool $enabled */
/** @var int $userId */

$streamUrl = Url::to(['/ai/chat/stream']);
$historyUrl = Url::to(['/ai/chat/history']);
$connectionUrl = Url::to(['/ai/chat/openrouter-connection']);
$settingsUrl = Url::to(['/ai/chat/index', '#' => 'openrouter-connection']);
$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->csrfToken;
$connectionLabel = $connected
    ? ($selectedModel !== '' ? $selectedModel : 'OpenRouter พร้อมใช้งาน')
    : 'ยังไม่เชื่อมต่อ OpenRouter';

$this->registerCss(<<<'CSS'
.erp-ai-assistant {
    --erp-ai-ink-1: #1a202c;
    --erp-ai-ink-2: #4a5568;
    --erp-ai-ink-3: #718096;
    --erp-ai-ink-4: #a0aec0;
    --erp-ai-surface: #ffffff;
    --erp-ai-surface-2: #f7f9fc;
    --erp-ai-surface-3: #eef2f7;
    --erp-ai-line: rgba(15, 23, 42, 0.08);
    --erp-ai-line-strong: rgba(15, 23, 42, 0.14);
    --erp-ai-primary: #0d6efd;
    --erp-ai-primary-hover: #0a58ca;
    --erp-ai-primary-soft: rgba(13, 110, 253, 0.08);
    --erp-ai-info-soft: rgba(13, 110, 253, 0.08);
    --erp-ai-success: #15803d;
    --erp-ai-success-soft: rgba(21, 128, 61, 0.10);
    --erp-ai-warning: #b45309;
    --erp-ai-warning-soft: rgba(180, 83, 9, 0.10);
    --erp-ai-danger: #b91c1c;
    --erp-ai-danger-soft: rgba(185, 28, 28, 0.10);
    --erp-ai-ease: cubic-bezier(0.16, 1, 0.3, 1);
    --erp-ai-t-fast: 120ms;
    --erp-ai-t-mid: 180ms;
    --erp-ai-t-slow: 240ms;
    position: fixed;
    right: 20px;
    bottom: calc(20px + env(safe-area-inset-bottom));
    z-index: 1038;
    width: 0;
    height: 0;
    color: var(--erp-ai-ink-1);
    font-family: inherit;
}

.erp-ai-assistant[hidden] {
    display: none !important;
}

.erp-ai-assistant__panel {
    position: absolute;
    right: 0;
    bottom: 66px;
    width: min(420px, calc(100vw - 32px));
    height: min(660px, calc(100dvh - 112px));
    display: flex;
    flex-direction: column;
    overflow: hidden;
    border: 1px solid var(--erp-ai-line-strong);
    border-radius: 10px;
    background: var(--erp-ai-surface);
    box-shadow: 0 20px 52px rgba(15, 23, 42, 0.18), 0 5px 14px rgba(15, 23, 42, 0.10);
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    transform: translateY(12px) scale(0.98);
    transform-origin: bottom right;
    transition: opacity var(--erp-ai-t-mid) var(--erp-ai-ease), transform var(--erp-ai-t-mid) var(--erp-ai-ease), visibility var(--erp-ai-t-mid);
}

.erp-ai-assistant.is-open .erp-ai-assistant__panel {
    opacity: 1;
    visibility: visible;
    pointer-events: auto;
    transform: translateY(0) scale(1);
}

.erp-ai-assistant__header {
    min-height: 72px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 13px 16px;
    color: #ffffff;
    background: var(--erp-ai-primary);
}

.erp-ai-assistant__identity {
    min-width: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.erp-ai-assistant__identity > div {
    min-width: 0;
}

.erp-ai-assistant__brand-icon {
    width: 40px;
    height: 40px;
    flex: 0 0 40px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.16);
    font-size: 1.25rem;
}

.erp-ai-assistant__title {
    margin: 0;
    color: #ffffff;
    font-size: 1.0625rem;
    font-weight: 700;
    line-height: 1.25;
}

.erp-ai-assistant__subtitle {
    max-width: 230px;
    margin-top: 2px;
    overflow: hidden;
    color: #ffffff;
    font-size: 0.75rem;
    line-height: 1.3;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.erp-ai-assistant__header-actions {
    display: flex;
    align-items: center;
    gap: 2px;
}

.erp-ai-assistant__icon-button {
    width: 36px;
    height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    border: 0;
    border-radius: 6px;
    color: #ffffff;
    background: transparent;
    transition: background-color var(--erp-ai-t-fast) var(--erp-ai-ease), transform var(--erp-ai-t-fast) var(--erp-ai-ease), opacity var(--erp-ai-t-fast) var(--erp-ai-ease);
}

.erp-ai-assistant__icon-button:hover {
    color: #ffffff;
    background: rgba(255, 255, 255, 0.14);
}

.erp-ai-assistant__icon-button:focus-visible {
    outline: 3px solid rgba(255, 255, 255, 0.38);
    outline-offset: 1px;
}

.erp-ai-assistant__icon-button:active:not(:disabled) {
    transform: translateY(1px);
}

.erp-ai-assistant__icon-button:disabled {
    cursor: not-allowed;
    opacity: 0.45;
}

.erp-ai-assistant__messages {
    flex: 1 1 auto;
    min-height: 0;
    display: flex;
    flex-direction: column;
    gap: 14px;
    overflow-y: auto;
    padding: 18px 16px;
    background: var(--erp-ai-surface);
    overscroll-behavior: contain;
}

.erp-ai-assistant__message {
    width: fit-content;
    max-width: 92%;
    padding: 11px 13px;
    border: 1px solid var(--erp-ai-line);
    border-radius: 8px;
    color: var(--erp-ai-ink-1);
    background: var(--erp-ai-surface-2);
    font-size: 1rem;
    line-height: 1.55;
    overflow-wrap: anywhere;
    white-space: pre-wrap;
    animation: erp-ai-message-enter var(--erp-ai-t-slow) var(--erp-ai-ease) both;
}

.erp-ai-assistant__message.is-static {
    animation: none;
}

.erp-ai-assistant__message.is-user {
    align-self: flex-end;
    max-width: 86%;
    color: #ffffff;
    border-color: var(--erp-ai-primary);
    background: var(--erp-ai-primary);
}

.erp-ai-assistant__message.is-error {
    color: var(--erp-ai-danger);
    border-color: rgba(185, 28, 28, 0.22);
    background: var(--erp-ai-danger-soft);
}

.erp-ai-assistant__message.is-awaiting {
    min-width: 68px;
    color: var(--erp-ai-primary);
}

.erp-ai-assistant__message.is-awaiting .erp-ai-assistant__message-content::before {
    content: '';
    display: inline-block;
    width: 6px;
    height: 6px;
    margin-right: 22px;
    border-radius: 50%;
    background: currentColor;
    box-shadow: 11px 0 currentColor, 22px 0 currentColor;
    animation: erp-ai-waiting-dots 900ms ease-in-out infinite;
}

.erp-ai-assistant__message.is-streaming .erp-ai-assistant__message-content::after {
    content: '';
    display: inline-block;
    width: 2px;
    height: 1em;
    margin-left: 3px;
    vertical-align: -0.12em;
    border-radius: 1px;
    background: var(--erp-ai-primary);
    animation: erp-ai-stream-caret 850ms step-end infinite;
}

@keyframes erp-ai-message-enter {
    from {
        opacity: 0;
        transform: translateY(7px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes erp-ai-waiting-dots {
    0%, 100% { opacity: 0.38; transform: translateY(0); }
    50% { opacity: 1; transform: translateY(-1px); }
}

@keyframes erp-ai-stream-caret {
    0%, 46% { opacity: 1; }
    47%, 100% { opacity: 0; }
}

.erp-ai-assistant__activity {
    --erp-ai-activity-color: var(--erp-ai-success);
    --erp-ai-activity-surface: var(--erp-ai-success-soft);
    min-height: 52px;
    display: grid;
    grid-template-columns: 28px minmax(0, 1fr) auto;
    align-items: center;
    gap: 9px;
    padding: 8px 16px;
    color: var(--erp-ai-activity-color);
    border-top: 1px solid var(--erp-ai-line);
    background: var(--erp-ai-activity-surface);
    transition: color var(--erp-ai-t-mid) var(--erp-ai-ease), background-color var(--erp-ai-t-mid) var(--erp-ai-ease);
}

.erp-ai-assistant__activity[data-state="connecting"],
.erp-ai-assistant__activity[data-state="thinking"],
.erp-ai-assistant__activity[data-state="composing"] {
    --erp-ai-activity-color: var(--erp-ai-primary-hover);
    --erp-ai-activity-surface: var(--erp-ai-info-soft);
}

.erp-ai-assistant__activity[data-state="searching"],
.erp-ai-assistant__activity[data-state="disconnected"] {
    --erp-ai-activity-color: var(--erp-ai-warning);
    --erp-ai-activity-surface: var(--erp-ai-warning-soft);
}

.erp-ai-assistant__activity[data-state="error"] {
    --erp-ai-activity-color: var(--erp-ai-danger);
    --erp-ai-activity-surface: var(--erp-ai-danger-soft);
}

.erp-ai-assistant.is-disconnected .erp-ai-assistant__activity {
    display: none;
}

.erp-ai-assistant__activity-icon {
    width: 28px;
    height: 28px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid currentColor;
    border-radius: 50%;
    font-size: 0.875rem;
}

.erp-ai-assistant__activity-copy {
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 1px;
}

.erp-ai-assistant__activity-title {
    color: currentColor;
    font-size: 0.875rem;
    font-weight: 700;
    line-height: 1.25;
}

.erp-ai-assistant__activity-detail {
    overflow: hidden;
    color: currentColor;
    font-size: 0.75rem;
    line-height: 1.3;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.erp-ai-assistant__activity-dots {
    display: none;
    align-items: center;
    gap: 3px;
    padding-right: 2px;
}

.erp-ai-assistant__activity-dots > span {
    width: 4px;
    height: 4px;
    border-radius: 50%;
    background: currentColor;
    animation: erp-ai-activity-dot 900ms ease-in-out infinite;
}

.erp-ai-assistant__activity-dots > span:nth-child(2) { animation-delay: 120ms; }
.erp-ai-assistant__activity-dots > span:nth-child(3) { animation-delay: 240ms; }

.erp-ai-assistant__activity[data-state="connecting"] .erp-ai-assistant__activity-dots,
.erp-ai-assistant__activity[data-state="thinking"] .erp-ai-assistant__activity-dots,
.erp-ai-assistant__activity[data-state="searching"] .erp-ai-assistant__activity-dots,
.erp-ai-assistant__activity[data-state="composing"] .erp-ai-assistant__activity-dots {
    display: flex;
}

@keyframes erp-ai-activity-dot {
    0%, 60%, 100% { opacity: 0.35; transform: translateY(0); }
    30% { opacity: 1; transform: translateY(-2px); }
}

.erp-ai-assistant__connection-notice {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 9px 14px;
    color: var(--erp-ai-warning);
    border-top: 1px solid var(--erp-ai-line);
    background: var(--erp-ai-warning-soft);
    font-size: 0.8125rem;
}

.erp-ai-assistant__connection-notice[hidden] {
    display: none;
}

.erp-ai-assistant__connection-notice a {
    margin-left: auto;
    color: var(--erp-ai-primary);
    font-weight: 600;
    text-decoration: none;
}

.erp-ai-assistant__connection-notice a:hover {
    text-decoration: underline;
}

.erp-ai-assistant__form {
    padding: 12px 16px 15px;
    border-top: 1px solid var(--erp-ai-line);
    background: var(--erp-ai-surface-2);
}

.erp-ai-assistant__form-error {
    margin-bottom: 7px;
    color: var(--erp-ai-danger);
    font-size: 0.8125rem;
    line-height: 1.35;
}

.erp-ai-assistant__form-error[hidden] {
    display: none;
}

.erp-ai-assistant__composer {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 44px;
    align-items: end;
    gap: 8px;
    padding: 5px;
    border: 1px solid var(--erp-ai-line-strong);
    border-radius: 8px;
    background: var(--erp-ai-surface);
}

.erp-ai-assistant__composer:focus-within {
    border-color: var(--erp-ai-primary);
    box-shadow: 0 0 0 3px var(--erp-ai-primary-soft);
}

.erp-ai-assistant__input {
    width: 100%;
    min-height: 34px;
    max-height: 96px;
    padding: 7px 8px;
    resize: none;
    overflow-y: auto;
    border: 0;
    border-radius: 6px;
    color: var(--erp-ai-ink-1);
    background: transparent;
    font: inherit;
    font-size: 1rem;
    line-height: 1.5;
}

.erp-ai-assistant__input::placeholder {
    color: var(--erp-ai-ink-2);
    opacity: 1;
}

.erp-ai-assistant__input:focus {
    outline: 0;
}

.erp-ai-assistant__input:disabled {
    cursor: not-allowed;
    opacity: 0.62;
}

.erp-ai-assistant__send {
    width: 44px;
    height: 44px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    border: 0;
    border-radius: 6px;
    color: #ffffff;
    background: var(--erp-ai-primary);
    transition: background-color var(--erp-ai-t-fast) var(--erp-ai-ease), transform var(--erp-ai-t-fast) var(--erp-ai-ease), opacity var(--erp-ai-t-fast) var(--erp-ai-ease);
}

.erp-ai-assistant__send:hover:not(:disabled) {
    background: var(--erp-ai-primary-hover);
}

.erp-ai-assistant__send:active:not(:disabled) {
    transform: translateY(1px);
}

.erp-ai-assistant__send:focus-visible {
    outline: 3px solid var(--erp-ai-primary-soft);
    outline-offset: 2px;
}

.erp-ai-assistant__send:disabled {
    cursor: not-allowed;
    opacity: 0.5;
}

.erp-ai-assistant__send-loader {
    display: none;
    width: 17px;
    height: 17px;
    border: 2px solid rgba(255, 255, 255, 0.42);
    border-top-color: #ffffff;
    border-radius: 50%;
    animation: erp-ai-spin 700ms linear infinite;
}

.erp-ai-assistant__send.is-loading .bi-send,
.erp-ai-assistant__send.is-loading .bi-send-fill {
    display: none;
}

.erp-ai-assistant__send.is-loading .erp-ai-assistant__send-loader {
    display: block;
}

@keyframes erp-ai-spin {
    to { transform: rotate(360deg); }
}

.erp-ai-assistant__toggle {
    position: absolute;
    right: 0;
    bottom: 0;
    width: 58px;
    height: 58px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    border: 0;
    border-radius: 50%;
    color: #ffffff;
    background: var(--erp-ai-primary);
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.22);
    font-size: 1.4375rem;
    transition: background-color var(--erp-ai-t-fast) var(--erp-ai-ease), transform var(--erp-ai-t-fast) var(--erp-ai-ease), box-shadow var(--erp-ai-t-fast) var(--erp-ai-ease);
}

.erp-ai-assistant__toggle:hover {
    color: #ffffff;
    background: var(--erp-ai-primary-hover);
    box-shadow: 0 10px 28px rgba(15, 23, 42, 0.26);
}

.erp-ai-assistant__toggle:active {
    transform: translateY(1px);
}

.erp-ai-assistant__toggle:focus-visible {
    outline: 3px solid var(--erp-ai-primary-soft);
    outline-offset: 3px;
}

.erp-ai-assistant.is-open .erp-ai-assistant__toggle {
    background: var(--erp-ai-danger);
}

.erp-ai-assistant__toggle .bi-x-lg,
.erp-ai-assistant.is-open .erp-ai-assistant__toggle .bi-robot {
    display: none;
}

.erp-ai-assistant.is-open .erp-ai-assistant__toggle .bi-x-lg {
    display: inline-block;
}

.erp-ai-assistant__status-dot {
    position: absolute;
    top: 1px;
    right: 1px;
    width: 12px;
    height: 12px;
    border: 2px solid #ffffff;
    border-radius: 50%;
    background: var(--erp-ai-warning);
}

.erp-ai-assistant.is-busy .erp-ai-assistant__status-dot {
    background: var(--erp-ai-primary);
    animation: erp-ai-status-pulse 1.4s ease-in-out infinite;
}

@keyframes erp-ai-status-pulse {
    0%, 100% { box-shadow: 0 0 0 0 rgba(13, 110, 253, 0); }
    45% { box-shadow: 0 0 0 5px rgba(13, 110, 253, 0.18); }
}

.erp-ai-assistant.is-connected .erp-ai-assistant__status-dot {
    background: var(--erp-ai-success);
}

.erp-ai-assistant.is-connected.is-busy .erp-ai-assistant__status-dot {
    background: var(--erp-ai-primary);
}

.erp-ai-assistant.is-open .erp-ai-assistant__status-dot {
    display: none;
}

@media (max-width: 575.98px) {
    .erp-ai-assistant {
        right: 12px;
        bottom: calc(12px + env(safe-area-inset-bottom));
    }

    .erp-ai-assistant__panel {
        width: calc(100vw - 24px);
        height: min(660px, calc(100dvh - 90px));
    }

    .erp-ai-assistant__messages {
        padding: 16px 12px;
    }

    .erp-ai-assistant__form {
        padding: 10px 12px 12px;
    }

    .erp-ai-assistant__activity {
        padding-right: 12px;
        padding-left: 12px;
    }
}

@media (prefers-reduced-motion: reduce) {
    .erp-ai-assistant__panel,
    .erp-ai-assistant__toggle,
    .erp-ai-assistant__send,
    .erp-ai-assistant__icon-button {
        transition-duration: 80ms;
        transition-property: opacity, background-color, color;
    }

    .erp-ai-assistant__message,
    .erp-ai-assistant__message.is-awaiting .erp-ai-assistant__message-content::before,
    .erp-ai-assistant__message.is-streaming .erp-ai-assistant__message-content::after,
    .erp-ai-assistant__activity-dots > span,
    .erp-ai-assistant__send-loader,
    .erp-ai-assistant.is-busy .erp-ai-assistant__status-dot {
        animation: none;
    }

    .erp-ai-assistant__panel {
        transform: none;
    }
}
CSS);

$streamUrlJson = Json::htmlEncode($streamUrl);
$historyUrlJson = Json::htmlEncode($historyUrl);
$connectionUrlJson = Json::htmlEncode($connectionUrl);
$csrfParamJson = Json::htmlEncode($csrfParam);
$csrfTokenJson = Json::htmlEncode($csrfToken);
$openStorageKeyJson = Json::htmlEncode('erp.ai.assistant.open.' . $userId);
$conversationStorageKeyJson = Json::htmlEncode('erp.ai.assistant.conversation.' . $userId);

$js = <<<'JS'
(() => {
    if (window.erpAiAssistantInitialized) {
        return;
    }
    window.erpAiAssistantInitialized = true;

    const root = document.getElementById('erp-ai-assistant');
    if (!root) {
        return;
    }

    const streamUrl = __STREAM_URL__;
    const historyUrl = __HISTORY_URL__;
    const connectionUrl = __CONNECTION_URL__;
    const csrfParam = __CSRF_PARAM__;
    const csrfToken = __CSRF_TOKEN__;
    const openStorageKey = __OPEN_STORAGE_KEY__;
    const conversationStorageKey = __CONVERSATION_STORAGE_KEY__;
    const panel = document.getElementById('erp-ai-assistant-panel');
    const toggle = document.getElementById('erp-ai-assistant-toggle');
    const closeButton = document.getElementById('erp-ai-assistant-close');
    const newButton = document.getElementById('erp-ai-assistant-new');
    const messages = document.getElementById('erp-ai-assistant-messages');
    const form = document.getElementById('erp-ai-assistant-form');
    const input = document.getElementById('erp-ai-assistant-input');
    const sendButton = document.getElementById('erp-ai-assistant-send');
    const formError = document.getElementById('erp-ai-assistant-error');
    const connectionNotice = document.getElementById('erp-ai-assistant-connection-notice');
    const subtitle = document.getElementById('erp-ai-assistant-subtitle');
    const activity = document.getElementById('erp-ai-assistant-activity');
    const activityIcon = document.getElementById('erp-ai-assistant-activity-icon');
    const activityTitle = document.getElementById('erp-ai-assistant-activity-title');
    const activityDetail = document.getElementById('erp-ai-assistant-activity-detail');
    let connected = root.dataset.connected === '1';
    let busy = false;
    let historyLoaded = false;
    let scrollFrame = 0;

    const activityStates = {
        ready: {
            icon: 'bi-check2-circle',
            title: 'พร้อมรับคำถาม',
            detail: 'OpenRouter เชื่อมต่อแล้ว'
        },
        disconnected: {
            icon: 'bi-plug',
            title: 'รอการเชื่อมต่อ',
            detail: 'ตั้งค่า OpenRouter เพื่อเริ่มใช้งาน'
        },
        connecting: {
            icon: 'bi-link-45deg',
            title: 'กำลังเชื่อมต่อ OpenRouter',
            detail: 'ตรวจสอบ API key และโมเดลที่เลือก'
        },
        thinking: {
            icon: 'bi-lightbulb',
            title: 'กำลังวิเคราะห์คำถาม',
            detail: 'เลือกวิธีตอบที่ตรงกับคำถาม'
        },
        searching: {
            icon: 'bi-search',
            title: 'กำลังค้นข้อมูลที่ได้รับอนุญาต',
            detail: 'เรียกดูเฉพาะข้อมูลที่คุณมีสิทธิ์'
        },
        composing: {
            icon: 'bi-pencil-square',
            title: 'กำลังเรียบเรียงคำตอบ',
            detail: 'คำตอบกำลังแสดงอย่างต่อเนื่อง'
        },
        error: {
            icon: 'bi-exclamation-circle',
            title: 'การตอบกลับหยุดลง',
            detail: 'ตรวจสอบข้อความด้านบนแล้วลองอีกครั้ง'
        }
    };

    const readStorage = (key) => {
        try {
            return window.localStorage.getItem(key);
        } catch (error) {
            return null;
        }
    };

    const writeStorage = (key, value) => {
        try {
            window.localStorage.setItem(key, value);
        } catch (error) {
            // The assistant still works when browser storage is unavailable.
        }
    };

    const removeStorage = (key) => {
        try {
            window.localStorage.removeItem(key);
        } catch (error) {
            // The assistant still works when browser storage is unavailable.
        }
    };

    const setActivity = (state, detail = '') => {
        const knownState = Object.prototype.hasOwnProperty.call(activityStates, state) ? state : 'ready';
        const nextState = activityStates[knownState];
        activity.dataset.state = knownState;
        activityIcon.className = `bi ${nextState.icon}`;
        activityTitle.textContent = nextState.title;
        activityDetail.textContent = detail || nextState.detail;
    };

    const scrollMessages = () => {
        if (scrollFrame) {
            return;
        }
        scrollFrame = window.requestAnimationFrame(() => {
            messages.scrollTop = messages.scrollHeight;
            scrollFrame = 0;
        });
    };

    const appendMessage = (role, content, state = '', animate = true) => {
        const item = document.createElement('article');
        item.className = `erp-ai-assistant__message is-${role}${state ? ` ${state}` : ''}`;
        item.classList.toggle('is-static', !animate);

        const label = document.createElement('span');
        label.className = 'visually-hidden';
        label.textContent = role === 'user' ? 'คุณ: ' : 'ผู้ช่วย AI: ';

        const body = document.createElement('span');
        body.className = 'erp-ai-assistant__message-content';
        body.textContent = content;

        item.append(label, body);
        messages.append(item);
        scrollMessages();
        return item;
    };

    const renderGreeting = () => {
        messages.replaceChildren();
        appendMessage('assistant', 'สวัสดีครับ ผมคือผู้ช่วย AI สำหรับระบบ ERP คุณสามารถสอบถามข้อมูลที่ได้รับอนุญาตได้ที่นี่');
    };

    const showFormError = (message) => {
        formError.hidden = !message;
        formError.textContent = message || '';
    };

    const setBusy = (nextBusy) => {
        busy = nextBusy;
        root.classList.toggle('is-busy', busy);
        input.disabled = busy || !connected;
        sendButton.disabled = busy || !connected;
        sendButton.classList.toggle('is-loading', busy);
        sendButton.setAttribute('aria-label', busy ? 'กำลังส่งข้อความ' : 'ส่งข้อความ');
        sendButton.title = busy ? 'กำลังส่งข้อความ' : 'ส่งข้อความ';
        newButton.disabled = busy;
        form.setAttribute('aria-busy', busy ? 'true' : 'false');
    };

    const applyConnection = (status) => {
        connected = Boolean(status.connected);
        root.dataset.connected = connected ? '1' : '0';
        root.classList.toggle('is-connected', connected);
        root.classList.toggle('is-disconnected', !connected);
        connectionNotice.hidden = connected;
        subtitle.textContent = connected
            ? (status.selected_model || 'OpenRouter พร้อมใช้งาน')
            : 'ยังไม่เชื่อมต่อ OpenRouter';
        subtitle.title = subtitle.textContent;
        setBusy(busy);
        if (!busy) {
            setActivity(connected ? 'ready' : 'disconnected');
        }
    };

    const refreshConnection = async () => {
        try {
            const response = await fetch(connectionUrl, {
                method: 'GET',
                headers: { 'Accept': 'application/json' }
            });
            const json = await response.json();
            if (response.ok && json.success) {
                applyConnection(json.data || {});
            }
        } catch (error) {
            if (!busy && !connected) {
                setActivity('disconnected');
            }
        }
    };

    const loadHistory = async () => {
        if (historyLoaded) {
            return;
        }
        historyLoaded = true;

        const conversationId = readStorage(conversationStorageKey);
        if (!conversationId) {
            return;
        }

        messages.setAttribute('aria-busy', 'true');
        try {
            const response = await fetch(`${historyUrl}?id=${encodeURIComponent(conversationId)}`, {
                method: 'GET',
                headers: { 'Accept': 'application/json' }
            });
            const json = await response.json();
            if (!response.ok || !json.success || !Array.isArray(json.data)) {
                throw new Error('ไม่สามารถโหลดบทสนทนาเดิมได้');
            }

            const history = json.data
                .filter((message) => message.role === 'user' || message.role === 'assistant')
                .slice(-30);
            if (history.length === 0) {
                return;
            }

            messages.replaceChildren();
            history.forEach((message) => appendMessage(message.role, message.content || '', '', false));
        } catch (error) {
            removeStorage(conversationStorageKey);
            renderGreeting();
        } finally {
            messages.setAttribute('aria-busy', 'false');
            scrollMessages();
        }
    };

    const setOpen = (open, persist = true) => {
        root.classList.toggle('is-open', open);
        panel.setAttribute('aria-hidden', open ? 'false' : 'true');
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        toggle.setAttribute('aria-label', open ? 'ปิดผู้ช่วย AI' : 'เปิดผู้ช่วย AI');
        toggle.title = open ? 'ปิดผู้ช่วย AI' : 'เปิดผู้ช่วย AI';

        if (persist) {
            writeStorage(openStorageKey, open ? '1' : '0');
        }

        if (open) {
            void refreshConnection();
            void loadHistory();
            if (persist) {
                window.setTimeout(() => {
                    if (!input.disabled) {
                        input.focus();
                    }
                }, 180);
            }
        } else if (persist) {
            toggle.focus();
        }
    };

    const setEnabled = (nextEnabled) => {
        const enabled = Boolean(nextEnabled);
        root.dataset.enabled = enabled ? '1' : '0';
        if (!enabled) {
            writeStorage(openStorageKey, '0');
            setOpen(false, false);
            root.hidden = true;
            return;
        }

        root.hidden = false;
        setOpen(false, false);
        void refreshConnection();
    };

    window.erpAiAssistant = { setEnabled };

    toggle.addEventListener('click', () => {
        setOpen(!root.classList.contains('is-open'));
    });

    closeButton.addEventListener('click', () => {
        setOpen(false);
    });

    newButton.addEventListener('click', () => {
        removeStorage(conversationStorageKey);
        historyLoaded = true;
        showFormError('');
        renderGreeting();
        setActivity(connected ? 'ready' : 'disconnected');
        if (!input.disabled) {
            input.focus();
        }
    });

    input.addEventListener('input', () => {
        showFormError('');
        input.style.height = 'auto';
        input.style.height = `${Math.min(input.scrollHeight, 96)}px`;
    });

    input.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && !event.shiftKey && !event.isComposing) {
            event.preventDefault();
            form.requestSubmit();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && root.classList.contains('is-open')) {
            setOpen(false);
        }
    });

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
        if (/HTTP status 5\d\d|provider stream failed/i.test(message)) {
            return 'OpenRouter ไม่สามารถตอบกลับได้ในขณะนี้ กรุณาลองอีกครั้ง';
        }
        // ปล่อยผ่านเฉพาะข้อความไทยสั้น ๆ ที่ตั้งใจสื่อสารกับผู้ใช้ กัน error ภายใน (SQL/exception/path) หลุด
        const isSafe = message
            && message.length <= 300
            && !/[\r\n]/.test(message)
            && /[฀-๿]/.test(message)
            && !/SQLSTATE|SQL being executed|INSERT INTO|SELECT |UPDATE |DELETE FROM|Exception|Stack trace|\/app\/|::/i.test(message);
        return isSafe ? message : 'ส่งคำถามไปยังผู้ช่วย AI ไม่สำเร็จ กรุณาลองอีกครั้ง';
    };

    const consumeEventStream = async (response, handlers) => {
        if (!response.ok) {
            const errorPayload = await response.json().catch(() => null);
            throw new Error(errorPayload?.error || `HTTP ${response.status}`);
        }
        if (!response.body) {
            throw new Error('เบราว์เซอร์ไม่รองรับการรับคำตอบแบบต่อเนื่อง');
        }

        const reader = response.body.getReader();
        const decoder = new TextDecoder('utf-8');
        let buffer = '';
        let finalPayload = null;

        const dispatchBlock = (block) => {
            let eventName = 'message';
            const dataLines = [];
            block.split(/\r?\n/).forEach((line) => {
                if (line.startsWith('event:')) {
                    eventName = line.slice(6).trim();
                } else if (line.startsWith('data:')) {
                    dataLines.push(line.slice(5).trimStart());
                }
            });

            if (dataLines.length === 0) {
                return;
            }

            let payload;
            try {
                payload = JSON.parse(dataLines.join('\n'));
            } catch (error) {
                throw new Error('ได้รับข้อมูลตอบกลับจากผู้ช่วย AI ไม่ครบถ้วน');
            }

            if (eventName === 'status') {
                handlers.onStatus(payload.status || 'thinking');
            } else if (eventName === 'delta') {
                handlers.onDelta(String(payload.delta || ''));
            } else if (eventName === 'done') {
                finalPayload = payload;
                handlers.onDone(payload);
            } else if (eventName === 'error') {
                throw new Error(payload.error || 'ผู้ช่วย AI หยุดตอบโดยไม่ทราบสาเหตุ');
            }
        };

        while (true) {
            const result = await reader.read();
            buffer += decoder.decode(result.value || new Uint8Array(), { stream: !result.done });

            let boundary = buffer.match(/\r?\n\r?\n/);
            while (boundary && typeof boundary.index === 'number') {
                const block = buffer.slice(0, boundary.index);
                buffer = buffer.slice(boundary.index + boundary[0].length);
                dispatchBlock(block);
                boundary = buffer.match(/\r?\n\r?\n/);
            }

            if (result.done) {
                break;
            }
        }

        if (buffer.trim() !== '') {
            dispatchBlock(buffer);
        }
        if (!finalPayload) {
            throw new Error('การเชื่อมต่อสิ้นสุดก่อนผู้ช่วย AI ตอบเสร็จ');
        }

        return finalPayload;
    };

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const message = input.value.trim();
        if (!message || busy) {
            return;
        }
        if (!connected) {
            showFormError('กรุณาเชื่อมต่อ OpenRouter ก่อนส่งคำถาม');
            return;
        }

        showFormError('');
        appendMessage('user', message);
        input.value = '';
        input.style.height = 'auto';
        const pending = appendMessage('assistant', '', 'is-awaiting');
        const pendingBody = pending.querySelector('.erp-ai-assistant__message-content');
        let receivedContent = false;
        let deltaBuffer = '';
        let deltaTimer = 0;

        const flushDeltas = () => {
            if (!deltaBuffer) {
                deltaTimer = 0;
                return;
            }
            if (!receivedContent) {
                pendingBody.textContent = '';
                pending.classList.remove('is-awaiting');
                pending.classList.add('is-streaming');
                receivedContent = true;
            }
            pendingBody.append(document.createTextNode(deltaBuffer));
            deltaBuffer = '';
            deltaTimer = 0;
            scrollMessages();
        };

        const queueDelta = (delta) => {
            if (!delta) {
                return;
            }
            deltaBuffer += delta;
            if (!deltaTimer) {
                deltaTimer = window.setTimeout(flushDeltas, 36);
            }
        };

        setBusy(true);
        setActivity('connecting');

        try {
            const requestBody = {
                message,
                conversation_id: readStorage(conversationStorageKey) || null,
                provider: 'openrouter'
            };
            requestBody[csrfParam] = csrfToken;

            const response = await fetch(streamUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'text/event-stream',
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken
                },
                body: JSON.stringify(requestBody)
            });
            const result = await consumeEventStream(response, {
                onStatus: (status) => setActivity(status),
                onDelta: queueDelta,
                onDone: () => {
                    window.clearTimeout(deltaTimer);
                    flushDeltas();
                    pending.classList.remove('is-awaiting', 'is-streaming');
                }
            });

            if (result.conversation_id) {
                writeStorage(conversationStorageKey, result.conversation_id);
            }
            if (!receivedContent) {
                pendingBody.textContent = result.content || 'ผู้ช่วย AI ไม่ได้ส่งข้อความตอบกลับ';
            }
            pending.classList.remove('is-awaiting', 'is-streaming');
            setActivity('ready');
        } catch (error) {
            window.clearTimeout(deltaTimer);
            deltaBuffer = '';
            pending.classList.remove('is-awaiting', 'is-streaming');
            pending.classList.add('is-error');
            pendingBody.textContent = friendlyError(error);
            setActivity('error');
        } finally {
            setBusy(false);
            scrollMessages();
            if (!input.disabled) {
                input.focus();
            }
        }
    });

    renderGreeting();
    applyConnection({
        connected,
        selected_model: subtitle.textContent
    });
    const initiallyEnabled = root.dataset.enabled === '1';
    root.hidden = !initiallyEnabled;
    setOpen(initiallyEnabled && readStorage(openStorageKey) === '1', false);
})();
JS;
$js = strtr($js, [
    '__STREAM_URL__' => $streamUrlJson,
    '__HISTORY_URL__' => $historyUrlJson,
    '__CONNECTION_URL__' => $connectionUrlJson,
    '__CSRF_PARAM__' => $csrfParamJson,
    '__CSRF_TOKEN__' => $csrfTokenJson,
    '__OPEN_STORAGE_KEY__' => $openStorageKeyJson,
    '__CONVERSATION_STORAGE_KEY__' => $conversationStorageKeyJson,
]);
$this->registerJs($js, View::POS_END);
?>

<div
    id="erp-ai-assistant"
    class="erp-ai-assistant <?= $connected ? 'is-connected' : 'is-disconnected' ?>"
    data-connected="<?= $connected ? '1' : '0' ?>"
    data-enabled="<?= $enabled ? '1' : '0' ?>"
    <?= $enabled ? '' : 'hidden' ?>
>
    <section
        id="erp-ai-assistant-panel"
        class="erp-ai-assistant__panel"
        role="dialog"
        aria-modal="false"
        aria-hidden="true"
        aria-labelledby="erp-ai-assistant-title"
    >
        <header class="erp-ai-assistant__header">
            <div class="erp-ai-assistant__identity">
                <span class="erp-ai-assistant__brand-icon" aria-hidden="true"><i class="bi bi-stars"></i></span>
                <div class="min-w-0">
                    <h2 id="erp-ai-assistant-title" class="erp-ai-assistant__title">ผู้ช่วย AI</h2>
                    <div id="erp-ai-assistant-subtitle" class="erp-ai-assistant__subtitle" title="<?= Html::encode($connectionLabel) ?>">
                        <?= Html::encode($connectionLabel) ?>
                    </div>
                </div>
            </div>
            <div class="erp-ai-assistant__header-actions">
                <button id="erp-ai-assistant-new" type="button" class="erp-ai-assistant__icon-button" title="เริ่มบทสนทนาใหม่" aria-label="เริ่มบทสนทนาใหม่">
                    <i class="bi bi-plus-lg" aria-hidden="true"></i>
                </button>
                <button id="erp-ai-assistant-close" type="button" class="erp-ai-assistant__icon-button" title="ปิดผู้ช่วย AI" aria-label="ปิดผู้ช่วย AI">
                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                </button>
            </div>
        </header>

        <div id="erp-ai-assistant-messages" class="erp-ai-assistant__messages" role="log" aria-live="polite" aria-relevant="additions"></div>

        <div
            id="erp-ai-assistant-activity"
            class="erp-ai-assistant__activity"
            data-state="<?= $connected ? 'ready' : 'disconnected' ?>"
            role="status"
            aria-live="polite"
            aria-atomic="true"
        >
            <span class="erp-ai-assistant__activity-icon" aria-hidden="true">
                <i id="erp-ai-assistant-activity-icon" class="bi <?= $connected ? 'bi-check2-circle' : 'bi-plug' ?>"></i>
            </span>
            <span class="erp-ai-assistant__activity-copy">
                <strong id="erp-ai-assistant-activity-title" class="erp-ai-assistant__activity-title">
                    <?= $connected ? 'พร้อมรับคำถาม' : 'รอการเชื่อมต่อ' ?>
                </strong>
                <span id="erp-ai-assistant-activity-detail" class="erp-ai-assistant__activity-detail">
                    <?= $connected ? 'OpenRouter เชื่อมต่อแล้ว' : 'ตั้งค่า OpenRouter เพื่อเริ่มใช้งาน' ?>
                </span>
            </span>
            <span class="erp-ai-assistant__activity-dots" aria-hidden="true">
                <span></span><span></span><span></span>
            </span>
        </div>

        <div id="erp-ai-assistant-connection-notice" class="erp-ai-assistant__connection-notice" <?= $connected ? 'hidden' : '' ?>>
            <i class="bi bi-exclamation-circle" aria-hidden="true"></i>
            <span>ยังไม่ได้เชื่อมต่อ OpenRouter</span>
            <a href="<?= Html::encode($settingsUrl) ?>">ตั้งค่า</a>
        </div>

        <form id="erp-ai-assistant-form" class="erp-ai-assistant__form" aria-busy="false">
            <div id="erp-ai-assistant-error" class="erp-ai-assistant__form-error" role="alert" aria-live="polite" hidden></div>
            <div class="erp-ai-assistant__composer">
                <textarea
                    id="erp-ai-assistant-input"
                    class="erp-ai-assistant__input"
                    rows="1"
                    placeholder="สอบถามข้อมูลในระบบ..."
                    aria-label="ข้อความถึงผู้ช่วย AI"
                    <?= $connected ? '' : 'disabled' ?>
                ></textarea>
                <button
                    id="erp-ai-assistant-send"
                    class="erp-ai-assistant__send"
                    type="submit"
                    title="ส่งข้อความ"
                    aria-label="ส่งข้อความ"
                    <?= $connected ? '' : 'disabled' ?>
                >
                    <i class="bi bi-send" aria-hidden="true"></i>
                    <span class="erp-ai-assistant__send-loader" aria-hidden="true"></span>
                </button>
            </div>
        </form>
    </section>

    <button
        id="erp-ai-assistant-toggle"
        class="erp-ai-assistant__toggle"
        type="button"
        aria-label="เปิดผู้ช่วย AI"
        aria-controls="erp-ai-assistant-panel"
        aria-expanded="false"
        title="เปิดผู้ช่วย AI"
    >
        <i class="bi bi-robot" aria-hidden="true"></i>
        <i class="bi bi-x-lg" aria-hidden="true"></i>
        <span class="erp-ai-assistant__status-dot" aria-hidden="true"></span>
    </button>
</div>
