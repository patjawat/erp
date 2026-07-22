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

$this->title = 'AI Assistant';
$sendUrl = Url::to(['/ai/chat/send']);
$streamUrl = Url::to(['/ai/chat/stream']);
$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->csrfToken;
?>

<style>
    .ai-shell {
        display: grid;
        grid-template-columns: minmax(220px, 280px) minmax(0, 1fr);
        min-height: calc(100vh - 150px);
        border: 1px solid #d9dee7;
        background: #f7f9fc;
    }

    .ai-sidebar {
        border-right: 1px solid #d9dee7;
        background: #ffffff;
        overflow: hidden;
    }

    .ai-sidebar-header,
    .ai-chat-header {
        min-height: 64px;
        padding: 14px 16px;
        border-bottom: 1px solid #d9dee7;
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
        border-radius: 6px;
        color: #233044;
        text-decoration: none;
        border: 1px solid transparent;
    }

    .ai-thread-link:hover,
    .ai-thread-link.is-active {
        background: #eef4ff;
        border-color: #cbdcf7;
        color: #173d7a;
    }

    .ai-thread-title {
        font-weight: 600;
        font-size: 13px;
        line-height: 1.35;
        overflow-wrap: anywhere;
    }

    .ai-thread-meta {
        margin-top: 3px;
        color: #69778a;
        font-size: 12px;
    }

    .ai-chat {
        display: grid;
        grid-template-rows: auto minmax(0, 1fr) auto;
        min-width: 0;
        background: #fbfcfe;
    }

    .ai-chat-title {
        font-size: 18px;
        font-weight: 700;
        color: #162033;
        margin: 0;
    }

    .ai-provider-select {
        width: 150px;
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
        background: #ffffff;
        color: #172033;
        border: 1px solid #d9dee7;
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
        color: #69778a;
        font-size: 12px;
        font-weight: 600;
    }

    .ai-composer {
        border-top: 1px solid #d9dee7;
        background: #ffffff;
        padding: 12px;
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

    .ai-toolbar {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 8px;
    }

    .ai-error {
        color: #a32020;
        margin-top: 8px;
        font-size: 13px;
    }

    @media (max-width: 860px) {
        .ai-shell {
            grid-template-columns: 1fr;
        }

        .ai-sidebar {
            border-right: 0;
            border-bottom: 1px solid #d9dee7;
        }

        .ai-thread-list {
            max-height: 170px;
        }
    }
</style>

<div class="ai-shell">
    <aside class="ai-sidebar">
        <div class="ai-sidebar-header">
            <strong>Conversations</strong>
            <?= Html::a('New', ['/ai/chat/index'], ['class' => 'btn btn-sm btn-outline-primary']) ?>
        </div>
        <div class="ai-thread-list">
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
            <h1 class="ai-chat-title">AI Assistant</h1>
            <select id="ai-provider" class="form-select form-select-sm ai-provider-select" aria-label="AI provider">
                <?php foreach ($providerCodes as $providerCode): ?>
                    <option value="<?= Html::encode($providerCode) ?>"><?= Html::encode($providerCode) ?></option>
                <?php endforeach; ?>
            </select>
        </header>

        <main id="ai-messages" class="ai-messages">
            <?php foreach ($messages as $message): ?>
                <?php if ($message->role === AiMessage::ROLE_SYSTEM) {
                    continue;
                } ?>
                <article class="ai-message is-<?= Html::encode($message->role) ?>">
                    <div class="ai-message-label"><?= Html::encode($message->role === AiMessage::ROLE_USER ? 'You' : ($message->role === AiMessage::ROLE_TOOL ? 'Tool' : 'Assistant')) ?></div>
                    <div class="ai-message-body"><?= Html::encode((string) $message->content) ?></div>
                </article>
            <?php endforeach; ?>
        </main>

        <form id="ai-composer" class="ai-composer">
            <input type="hidden" name="<?= Html::encode($csrfParam) ?>" value="<?= Html::encode($csrfToken) ?>">
            <input type="hidden" id="ai-conversation-id" value="<?= Html::encode((string) $conversationId) ?>">
            <div class="ai-toolbar">
                <label class="form-check mb-0">
                    <input id="ai-stream" class="form-check-input" type="checkbox" value="1">
                    <span class="form-check-label">Streaming</span>
                </label>
            </div>
            <div class="ai-composer-row">
                <textarea id="ai-message-input" class="form-control" placeholder="ถามข้อมูลในระบบ ERP..." required></textarea>
                <button id="ai-send-button" type="submit" class="btn btn-primary">Send</button>
            </div>
            <div id="ai-error" class="ai-error" hidden></div>
        </form>
    </section>
</div>

<script>
(() => {
    const sendUrl = <?= json_encode($sendUrl, JSON_UNESCAPED_SLASHES) ?>;
    const streamUrl = <?= json_encode($streamUrl, JSON_UNESCAPED_SLASHES) ?>;
    const csrfToken = <?= json_encode($csrfToken) ?>;
    const messages = document.getElementById('ai-messages');
    const form = document.getElementById('ai-composer');
    const input = document.getElementById('ai-message-input');
    const sendButton = document.getElementById('ai-send-button');
    const provider = document.getElementById('ai-provider');
    const conversationInput = document.getElementById('ai-conversation-id');
    const streamToggle = document.getElementById('ai-stream');
    const errorBox = document.getElementById('ai-error');

    const appendMessage = (role, content) => {
        const item = document.createElement('article');
        item.className = `ai-message is-${role}`;

        const label = document.createElement('div');
        label.className = 'ai-message-label';
        label.textContent = role === 'user' ? 'You' : role === 'tool' ? 'Tool' : 'Assistant';

        const body = document.createElement('div');
        body.className = 'ai-message-body';
        body.textContent = content;

        item.append(label, body);
        messages.append(item);
        messages.scrollTop = messages.scrollHeight;
        return body;
    };

    const setBusy = (busy) => {
        sendButton.disabled = busy;
        input.disabled = busy;
        provider.disabled = busy;
    };

    const showError = (message) => {
        errorBox.hidden = !message;
        errorBox.textContent = message || '';
    };

    const payload = () => ({
        message: input.value,
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
                    body: JSON.stringify(payload())
                });

                if (!response.body) {
                    throw new Error('Streaming is not available in this browser.');
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
                    body: JSON.stringify(payload())
                });
                const json = await response.json();

                if (!json.success) {
                    throw new Error(json.error || 'AI request failed.');
                }

                conversationInput.value = json.data.conversation_id;
                appendMessage('assistant', json.data.content || '');
            }
        } catch (error) {
            showError(error.message || String(error));
        } finally {
            setBusy(false);
            input.focus();
        }
    });

    messages.scrollTop = messages.scrollHeight;
})();
</script>
