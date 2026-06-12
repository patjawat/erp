<?php

use kartik\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\View;

/** @var yii\web\View $this */
/** @var app\models\Categorise $model */

$this->title = 'Telegram Notification & Bot Management';
$this->params['breadcrumbs'][] = $this->title;

$data = is_array($model->data_json ?? null) ? $model->data_json : [];
$bindings = is_array($bindings ?? null) ? $bindings : [];

$botStatusValue = strtolower(trim((string) ($data['bot_status'] ?? '')));
$groupStatusValue = strtolower(trim((string) ($data['group_status'] ?? '')));
$miniAppEnabledValue = (string) ($data['enable_mini_app'] ?? '0');
$qrcodeUploadIdValue = (int) ($data['group_qrcode_id'] ?? ($qrcodeUploadId ?? 0));
$hasQrcode = !empty($qrcodePreviewUrl);

$botNameValue = trim((string) ($data['bot_name'] ?? ''));
$botUsernameValue = trim((string) ($data['bot_username'] ?? ''));
$botTokenValue = trim((string) ($data['bot_token'] ?? ''));
$groupNameValue = trim((string) ($data['group_name'] ?? ''));
$groupChatIdValue = trim((string) ($data['group_chat_id'] ?? $data['chat_id'] ?? ''));
$groupLinkValue = trim((string) ($data['group_link'] ?? ''));
$groupMemberCountValue = trim((string) ($data['group_member_count'] ?? ''));
$miniAppUrlValue = trim((string) ($data['mini_app'] ?? ''));
$bindingsCount = count($bindings);

$statusCatalog = [
    'bot' => [
        'connected' => ['label' => 'Connected', 'class' => 'is-success'],
        'disconnected' => ['label' => 'Disconnected', 'class' => 'is-warning'],
        'invalid_token' => ['label' => 'Invalid Token', 'class' => 'is-danger'],
        'default' => ['label' => 'Not checked', 'class' => 'is-secondary'],
    ],
    'group' => [
        'connected' => ['label' => 'Connected', 'class' => 'is-success'],
        'disconnected' => ['label' => 'Disconnected', 'class' => 'is-warning'],
        'invalid_token' => ['label' => 'Invalid Token', 'class' => 'is-danger'],
        'default' => ['label' => 'Not checked', 'class' => 'is-secondary'],
    ],
    'qr' => [
        'uploaded' => ['label' => 'Uploaded', 'class' => 'is-success'],
        'empty' => ['label' => 'No QR', 'class' => 'is-secondary'],
    ],
    'miniapp' => [
        'enabled' => ['label' => 'Enabled', 'class' => 'is-success'],
        'disabled' => ['label' => 'Disabled', 'class' => 'is-secondary'],
    ],
];

$resolveStatus = static function (string $kind, string $status) use ($statusCatalog): array {
    $catalog = $statusCatalog[$kind] ?? [];
    $status = trim($status);
    if ($status === '') {
        return $catalog['default'] ?? ['label' => 'Not checked', 'class' => 'is-secondary'];
    }

    if ($kind === 'bot' || $kind === 'group') {
        if (in_array($status, ['connected', 'success', 'ok'], true)) {
            return $catalog['connected'];
        }
        if (in_array($status, ['invalid_token', 'unauthorized', 'invalid', 'token_invalid'], true)) {
            return $catalog['invalid_token'];
        }
        if (in_array($status, ['disconnected', 'error', 'failed'], true)) {
            return $catalog['disconnected'];
        }

        return $catalog['default'];
    }

    if ($kind === 'qr') {
        return !empty($status) && $status !== 'empty' ? $catalog['uploaded'] : $catalog['empty'];
    }

    if ($kind === 'miniapp') {
        return in_array($status, ['1', 'true', 'enabled', 'yes'], true)
            ? $catalog['enabled']
            : $catalog['disabled'];
    }

    return $catalog['default'] ?? ['label' => ucfirst($status), 'class' => 'is-secondary'];
};

$renderStatusPill = static function (string $kind, array $meta) {
    return Html::tag('span', Html::encode($meta['label']), [
        'class' => 'status-pill ' . $meta['class'],
        'data-status-kind' => $kind,
    ]);
};

$botStatusMeta = $resolveStatus('bot', $botStatusValue);
$groupStatusMeta = $resolveStatus('group', $groupStatusValue);
$qrStatusMeta = $resolveStatus('qr', $hasQrcode ? 'uploaded' : 'empty');
$miniAppStatusMeta = $resolveStatus('miniapp', $miniAppEnabledValue);

$this->registerCssFile('https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css', [
    'crossorigin' => 'anonymous',
]);
$this->registerJsFile('https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js', [
    'position' => View::POS_END,
    'crossorigin' => 'anonymous',
]);

$this->registerCss(<<<CSS
.telegram-admin-shell {
    --tg-primary: #2155d6;
    --tg-surface: #ffffff;
    --tg-border: #dde5ef;
    --tg-muted: #64748b;
    --tg-soft: #f5f8fc;
    color: #0f172a;
}

.telegram-admin-shell .hero-panel,
.telegram-admin-shell .section-card,
.telegram-admin-shell .side-card {
    background: var(--tg-surface);
    border: 1px solid var(--tg-border);
    box-shadow: 0 14px 40px rgba(15, 23, 42, 0.05);
}

.telegram-admin-shell .hero-panel {
    border-radius: 1.5rem;
    background: linear-gradient(180deg, #ffffff 0%, #f9fbff 100%);
}

.telegram-admin-shell .section-card,
.telegram-admin-shell .side-card {
    border-radius: 1.25rem;
}

.telegram-admin-shell .section-card + .section-card {
    margin-top: 1.25rem;
}

.telegram-admin-shell .section-kicker {
    font-size: .72rem;
    letter-spacing: .18em;
    text-transform: uppercase;
    font-weight: 800;
    color: var(--tg-muted);
}

.telegram-admin-shell .section-title {
    font-size: 1.05rem;
    font-weight: 800;
    color: #0f172a;
}

.telegram-admin-shell .section-desc {
    color: var(--tg-muted);
    font-size: .92rem;
}

.telegram-admin-shell .section-topline {
    width: 72px;
    height: 4px;
    border-radius: 999px;
    background: linear-gradient(90deg, var(--tg-primary), #73a3ff);
}

.telegram-admin-shell .hero-icon {
    width: 52px;
    height: 52px;
    border-radius: 1rem;
    background: #eaf1ff;
    color: var(--tg-primary);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.telegram-admin-shell .quick-stat {
    border: 1px solid var(--tg-border);
    border-radius: 1rem;
    background: #fff;
    padding: 1rem;
    height: 100%;
}

.telegram-admin-shell .quick-stat .label {
    font-size: .72rem;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: var(--tg-muted);
    font-weight: 800;
}

.telegram-admin-shell .quick-stat .value {
    font-size: 1.05rem;
    font-weight: 800;
    color: #0f172a;
}

.telegram-admin-shell .quick-stat .meta {
    color: var(--tg-muted);
    font-size: .9rem;
}

.telegram-admin-shell .stat-icon {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: .9rem;
    background: #eff4ff;
    color: var(--tg-primary);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.telegram-admin-shell .status-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: .35rem;
    border-radius: 999px;
    padding: .4rem .8rem;
    font-size: .78rem;
    font-weight: 800;
    border: 1px solid transparent;
    white-space: nowrap;
}

.telegram-admin-shell .status-pill.is-success {
    background: #e7f8ef;
    color: #0f7a42;
    border-color: #bbeacb;
}

.telegram-admin-shell .status-pill.is-warning {
    background: #fff7df;
    color: #9a6700;
    border-color: #f1d79e;
}

.telegram-admin-shell .status-pill.is-danger {
    background: #feecee;
    color: #b42318;
    border-color: #f7c4c9;
}

.telegram-admin-shell .status-pill.is-secondary {
    background: #eef3f8;
    color: #64748b;
    border-color: #dae2ea;
}

.telegram-admin-shell .help-card {
    background: var(--tg-soft);
    border: 1px solid #d9e5f0;
    border-radius: 1rem;
}

.telegram-admin-shell .mini-note {
    color: var(--tg-muted);
    font-size: .9rem;
}

.telegram-admin-shell .qr-dropzone {
    min-height: 290px;
    border: 1.5px dashed #c7d3e2;
    border-radius: 1.25rem;
    background: #f8fbff;
    cursor: pointer;
    transition: .2s ease;
}

.telegram-admin-shell .qr-dropzone.is-dragover {
    border-color: var(--tg-primary);
    background: #edf4ff;
}

.telegram-admin-shell .qr-preview-wrap {
    border: 1px solid #e2e8f0;
    border-radius: 1rem;
    overflow: hidden;
    aspect-ratio: 1 / 1;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
}

.telegram-admin-shell .qr-preview-wrap img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.telegram-admin-shell .qr-empty {
    text-align: center;
    color: var(--tg-muted);
    padding: 1rem;
}

.telegram-admin-shell .binding-table thead th {
    background: #f8fafc;
    color: #475569;
    font-size: .78rem;
    letter-spacing: .08em;
    text-transform: uppercase;
    white-space: nowrap;
}

.telegram-admin-shell .binding-table tbody td {
    vertical-align: middle;
}

.telegram-admin-shell .sticky-xl-top {
    position: sticky;
    top: 1.25rem;
}

@media (max-width: 1199.98px) {
    .telegram-admin-shell .sticky-xl-top {
        position: static;
    }
}
CSS);

$config = Json::encode([
    'csrfToken' => Yii::$app->request->csrfToken,
    'testBotUrl' => Url::to(['/telegrambot/default/test-bot']),
    'testGroupUrl' => Url::to(['/telegrambot/default/test-group']),
    'testUserUrl' => Url::to(['/telegrambot/default/test-user']),
    'setMenuUrl' => Url::to(['/telegrambot/home/set-menu']),
    'uploadUrl' => Url::to(['/filemanager/uploads/single']),
    'deleteUrl' => Url::to(['/filemanager/uploads/deletefile-ajax']),
    'qrcodeRef' => $qrcodeUploadRef ?? 'telegrambot_group_qrcode',
    'qrcodeName' => $qrcodeUploadName ?? 'group_qrcode',
    'qrcodeId' => $qrcodeUploadIdValue,
    'previewUrl' => $qrcodePreviewUrl ?: '',
]);

$this->registerJs(<<<JS
window.telegrambotConfig = {$config};
(function () {
    const config = window.telegrambotConfig || {};
    const form = document.getElementById('telegram-settings-form');
    const botNameField = document.getElementById('telegram-bot-name');
    const botUsernameField = document.getElementById('telegram-bot-username');
    const botTokenField = document.getElementById('telegram-bot-token');
    const botStatusInput = document.getElementById('telegram-bot-status-input');
    const groupNameField = document.getElementById('telegram-group-name');
    const groupChatIdField = document.getElementById('telegram-group-chat-id');
    const groupLinkField = document.getElementById('telegram-group-link');
    const groupMemberCountField = document.getElementById('telegram-group-member-count');
    const groupStatusInput = document.getElementById('telegram-group-status-input');
    const qrcodeIdField = document.getElementById('telegram-group-qrcode-id-input');
    const miniAppField = document.getElementById('telegram-mini-app-url');
    const miniAppCheckbox = document.getElementById('telegram-enable-mini-app');
    const bindingSearch = document.getElementById('telegram-binding-search');
    const qrcodeDropzone = document.getElementById('telegram-qr-dropzone');
    const qrcodeFileInput = document.getElementById('telegram-qr-file');
    const qrcodePreviewWrap = document.getElementById('telegram-qr-preview-wrap');
    const qrcodePreviewImage = document.getElementById('telegram-qr-preview-image');
    const qrcodeEmpty = document.getElementById('telegram-qr-empty');
    const qrcodeDeleteButton = document.getElementById('btn-delete-qrcode');
    const qrcodePreviewButton = document.getElementById('btn-preview-qrcode');
    const qrcodeSelectButton = document.getElementById('btn-select-qrcode');
    const botToggleButton = document.getElementById('btn-toggle-bot-token');
    const testBotButton = document.getElementById('btn-test-bot');
    const testGroupCheckButton = document.getElementById('btn-test-group-check');
    const testGroupSendButton = document.getElementById('btn-test-group-send');
    const setMenuButton = document.getElementById('btn-set-menu');
    const bindingRows = Array.from(document.querySelectorAll('[data-binding-row]'));
    const testUserButtons = Array.from(document.querySelectorAll('[data-test-user-id]'));
    const cropModalEl = document.getElementById('telegram-qr-crop-modal');
    const cropImageEl = document.getElementById('telegram-qr-crop-image');
    const cropConfirmButton = document.getElementById('btn-confirm-qrcode-crop');
    const cropCancelButton = document.getElementById('btn-cancel-qrcode-crop');
    const previewModalEl = document.getElementById('telegram-qr-preview-modal');
    const previewModalImage = document.getElementById('telegram-qr-preview-full');
    const statusMap = {
        bot: {
            connected: { label: 'Connected', className: 'is-success' },
            disconnected: { label: 'Disconnected', className: 'is-warning' },
            invalid_token: { label: 'Invalid Token', className: 'is-danger' },
            default: { label: 'Not checked', className: 'is-secondary' },
        },
        group: {
            connected: { label: 'Connected', className: 'is-success' },
            disconnected: { label: 'Disconnected', className: 'is-warning' },
            invalid_token: { label: 'Invalid Token', className: 'is-danger' },
            default: { label: 'Not checked', className: 'is-secondary' },
        },
        qr: {
            uploaded: { label: 'Uploaded', className: 'is-success' },
            empty: { label: 'No QR', className: 'is-secondary' },
        },
        miniapp: {
            enabled: { label: 'Enabled', className: 'is-success' },
            disabled: { label: 'Disabled', className: 'is-secondary' },
        },
    };

    let cropper = null;
    let cropObjectUrl = null;
    let cropFile = null;

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

    function updateStatus(kind, key) {
        const meta = (statusMap[kind] && statusMap[kind][key]) || statusMap[kind].default;
        document.querySelectorAll('[data-status-kind="' + kind + '"]').forEach(function (node) {
            node.className = 'status-pill ' + meta.className;
            node.textContent = meta.label;
        });
        return meta;
    }

    function updateMiniAppStatus() {
        updateStatus('miniapp', miniAppCheckbox && miniAppCheckbox.checked ? 'enabled' : 'disabled');
    }

    function updateQrcodeStatus(hasImage) {
        updateStatus('qr', hasImage ? 'uploaded' : 'empty');
        if (qrcodeDeleteButton) {
            qrcodeDeleteButton.disabled = !hasImage;
        }
        if (qrcodePreviewButton) {
            qrcodePreviewButton.disabled = !hasImage;
        }
    }

    function updateQrcodePreview(src, id) {
        if (qrcodePreviewImage) {
            qrcodePreviewImage.src = src || '';
        }
        if (qrcodePreviewWrap) {
            qrcodePreviewWrap.classList.toggle('d-none', !src);
        }
        if (qrcodeEmpty) {
            qrcodeEmpty.classList.toggle('d-none', !!src);
        }
        if (qrcodePreviewImage) {
            qrcodePreviewImage.classList.toggle('d-none', !src);
        }
        if (qrcodeIdField) {
            qrcodeIdField.value = id ? String(id) : '';
        }
        updateQrcodeStatus(!!src);
    }

    function normalizeErrorType(message, fallback) {
        const text = safeText(message).toLowerCase();
        if (text.includes('unauthorized') || text.includes('401') || text.includes('token')) {
            return 'invalid_token';
        }
        return fallback || 'disconnected';
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

    async function copyText(text) {
        const value = safeText(text);
        if (!value) {
            flash('warning', 'ไม่มีข้อมูลให้คัดลอก', 'กรุณากรอกข้อมูลก่อน');
            return;
        }

        if (navigator.clipboard && navigator.clipboard.writeText) {
            await navigator.clipboard.writeText(value);
            flash('success', 'คัดลอกแล้ว', value);
            return;
        }

        const temp = document.createElement('textarea');
        temp.value = value;
        temp.setAttribute('readonly', 'readonly');
        temp.style.position = 'absolute';
        temp.style.left = '-9999px';
        document.body.appendChild(temp);
        temp.select();
        document.execCommand('copy');
        document.body.removeChild(temp);
        flash('success', 'คัดลอกแล้ว', value);
    }

    function resetCropState() {
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
        if (cropObjectUrl) {
            URL.revokeObjectURL(cropObjectUrl);
            cropObjectUrl = null;
        }
        cropFile = null;
        if (cropImageEl) {
            cropImageEl.src = '';
        }
    }

    function showCropModal(file) {
        if (!cropModalEl || !cropImageEl) {
            uploadImage(file);
            return;
        }

        cropFile = file;
        resetCropState();
        cropObjectUrl = URL.createObjectURL(file);
        cropImageEl.onload = function () {
            if (!window.Cropper) {
                modal.hide();
                uploadImage(file);
                return;
            }

            cropper = new Cropper(cropImageEl, {
                aspectRatio: 1,
                viewMode: 2,
                autoCropArea: 1,
                background: false,
                responsive: true,
                movable: true,
                zoomable: true,
                rotatable: false,
                scalable: false,
            });
        };

        const modal = bootstrap.Modal.getOrCreateInstance(cropModalEl);
        cropImageEl.src = cropObjectUrl;
        modal.show();
    }

    async function uploadImage(blob, originalFile) {
        const uploadButton = qrcodeSelectButton;
        const formData = new FormData();
        const extension = ((blob.type || (originalFile && originalFile.type) || 'image/png').split('/')[1] || 'png').replace('jpeg', 'jpg');
        const fileName = 'telegram-qrcode.' + extension;
        const file = blob instanceof File ? blob : new File([blob], fileName, { type: blob.type || (originalFile && originalFile.type) || 'image/png' });

        formData.append('_csrf', config.csrfToken || '');
        formData.append('ref', config.qrcodeRef || 'telegrambot_group_qrcode');
        formData.append('name', config.qrcodeName || 'group_qrcode');
        formData.append(config.qrcodeName || 'group_qrcode', file, file.name || fileName);

        setBusy(uploadButton, true, 'กำลังอัปโหลด...');
        try {
            const response = await fetch(config.uploadUrl, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': config.csrfToken || '',
                },
                body: formData,
            });
            const result = await response.json();
            if (!(result.success === true || result.success === 'true')) {
                throw new Error(result.message || result.error || 'อัปโหลดรูปไม่สำเร็จ');
            }

            const imageUrl = result.img || '';
            if (qrcodePreviewImage && imageUrl) {
                updateQrcodePreview(imageUrl, result.data && result.data.id ? result.data.id : '');
                flash('success', 'อัปโหลด QR Code สำเร็จ', 'รูป QR จะถูกใช้กับกลุ่ม Telegram ทันที');
            } else {
                throw new Error('ระบบไม่สามารถอ่าน URL รูปที่อัปโหลดได้');
            }
        } catch (error) {
            flash('error', 'อัปโหลดไม่สำเร็จ', error.message || 'กรุณาลองใหม่อีกครั้ง');
        } finally {
            setBusy(uploadButton, false);
            resetCropState();
            const cropModalInstance = cropModalEl ? bootstrap.Modal.getInstance(cropModalEl) : null;
            if (cropModalInstance) {
                cropModalInstance.hide();
            }
            if (qrcodeFileInput) {
                qrcodeFileInput.value = '';
            }
        }
    }

    function uploadFromFile(file) {
        if (!file) {
            return;
        }

        if (!file.type || !file.type.startsWith('image/')) {
            flash('warning', 'รองรับเฉพาะไฟล์ภาพ', 'กรุณาเลือก JPG, PNG หรือ WEBP');
            return;
        }

        if (window.Cropper) {
            showCropModal(file);
            return;
        }

        uploadImage(file, file);
    }

    function bindUploadZone() {
        if (!qrcodeDropzone || !qrcodeFileInput) {
            return;
        }

        qrcodeDropzone.addEventListener('click', function () {
            qrcodeFileInput.click();
        });

        qrcodeFileInput.addEventListener('change', function () {
            if (qrcodeFileInput.files && qrcodeFileInput.files[0]) {
                uploadFromFile(qrcodeFileInput.files[0]);
            }
        });

        ['dragenter', 'dragover'].forEach(function (eventName) {
            qrcodeDropzone.addEventListener(eventName, function (event) {
                event.preventDefault();
                event.stopPropagation();
                qrcodeDropzone.classList.add('is-dragover');
            });
        });

        ['dragleave', 'drop'].forEach(function (eventName) {
            qrcodeDropzone.addEventListener(eventName, function (event) {
                event.preventDefault();
                event.stopPropagation();
                qrcodeDropzone.classList.remove('is-dragover');
            });
        });

        qrcodeDropzone.addEventListener('drop', function (event) {
            const files = event.dataTransfer && event.dataTransfer.files ? event.dataTransfer.files : [];
            if (files.length) {
                uploadFromFile(files[0]);
            }
        });
    }

    function filterBindings() {
        const query = safeText(bindingSearch ? bindingSearch.value : '').trim().toLowerCase();
        bindingRows.forEach(function (row) {
            const text = (row.textContent || '').toLowerCase();
            row.classList.toggle('d-none', query !== '' && text.indexOf(query) === -1);
        });
    }

    if (bindingSearch) {
        bindingSearch.addEventListener('input', filterBindings);
    }

    if (botToggleButton && botTokenField) {
        botToggleButton.addEventListener('click', function () {
            const nextType = botTokenField.type === 'password' ? 'text' : 'password';
            botTokenField.type = nextType;
            botToggleButton.innerHTML = nextType === 'password'
                ? '<i class="fa-regular fa-eye me-1"></i> แสดง Token'
                : '<i class="fa-regular fa-eye-slash me-1"></i> ซ่อน Token';
        });
    }

    if (miniAppCheckbox) {
        miniAppCheckbox.addEventListener('change', updateMiniAppStatus);
    }

    if (qrcodeSelectButton) {
        qrcodeSelectButton.addEventListener('click', function () {
            if (qrcodeFileInput) {
                qrcodeFileInput.click();
            }
        });
    }

    if (qrcodePreviewButton && qrcodePreviewImage && previewModalEl && previewModalImage) {
        qrcodePreviewButton.addEventListener('click', function () {
            if (!qrcodePreviewImage.src) {
                return;
            }
            previewModalImage.src = qrcodePreviewImage.src;
            bootstrap.Modal.getOrCreateInstance(previewModalEl).show();
        });
    }

    if (qrcodeDeleteButton) {
        qrcodeDeleteButton.addEventListener('click', async function () {
            const uploadId = qrcodeIdField ? safeText(qrcodeIdField.value).trim() : '';
            if (!uploadId) {
                updateQrcodePreview('', '');
                return;
            }

            const confirmed = window.Swal
                ? await Swal.fire({
                    icon: 'warning',
                    title: 'ลบ QR Code?',
                    text: 'ไฟล์ QR ปัจจุบันจะถูกลบออกจากระบบ',
                    showCancelButton: true,
                    confirmButtonText: 'ลบ',
                    cancelButtonText: 'ยกเลิก',
                }).then(function (res) {
                    return !!res.isConfirmed;
                })
                : window.confirm('ลบ QR Code ปัจจุบัน?');

            if (!confirmed) {
                return;
            }

            setBusy(qrcodeDeleteButton, true, 'กำลังลบ...');
            try {
                const params = new URLSearchParams();
                params.set('_csrf', config.csrfToken || '');
                params.set('key', uploadId);
                const response = await fetch(config.deleteUrl, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-Token': config.csrfToken || '',
                    },
                    body: params,
                });
                const result = await response.json();
                if (!result.success) {
                    throw new Error(result.message || 'ลบรูปไม่สำเร็จ');
                }
                updateQrcodePreview('', '');
                flash('success', 'ลบ QR Code แล้ว', 'ไฟล์ถูกลบจากระบบเรียบร้อย');
            } catch (error) {
                flash('error', 'ลบไม่สำเร็จ', error.message || 'กรุณาลองใหม่อีกครั้ง');
            } finally {
                setBusy(qrcodeDeleteButton, false);
            }
        });
    }

    if (cropCancelButton) {
        cropCancelButton.addEventListener('click', function () {
            resetCropState();
        });
    }

    if (cropConfirmButton) {
        cropConfirmButton.addEventListener('click', function () {
            if (!cropper) {
                if (cropFile) {
                    uploadImage(cropFile, cropFile);
                }
                return;
            }

            const canvas = cropper.getCroppedCanvas({
                width: 1200,
                height: 1200,
                imageSmoothingQuality: 'high',
            });

            canvas.toBlob(function (blob) {
                if (!blob) {
                    flash('error', 'ตัดภาพไม่สำเร็จ', 'กรุณาลองใหม่อีกครั้ง');
                    return;
                }
                uploadImage(blob, cropFile);
            }, (cropFile && cropFile.type) || 'image/png', 0.95);
        });
    }

    if (testBotButton && botTokenField) {
        testBotButton.addEventListener('click', async function () {
            const botToken = safeText(botTokenField.value).trim();
            if (!botToken) {
                flash('warning', 'กรุณาระบุ Bot Token', 'ก่อนทดสอบ Bot');
                return;
            }

            try {
                const result = await postJson(config.testBotUrl, { bot_token: botToken }, testBotButton, 'กำลังทดสอบ...');
                if (!(result.status === 'success')) {
                    const statusKey = result.error_type || normalizeErrorType(result.message, 'disconnected');
                    botStatusInput.value = statusKey;
                    updateStatus('bot', statusKey);
                    throw new Error(result.message || 'ทดสอบ Bot ไม่สำเร็จ');
                }

                botStatusInput.value = 'connected';
                updateStatus('bot', 'connected');
                if (botNameField && result.bot_name) {
                    botNameField.value = result.bot_name;
                }
                if (botUsernameField && result.bot_username) {
                    botUsernameField.value = result.bot_username.replace(/^@/, '');
                }
                flash('success', 'Bot เชื่อมต่อได้แล้ว', result.message || 'ทดสอบสำเร็จ');
            } catch (error) {
                flash('error', 'ทดสอบ Bot ไม่สำเร็จ', error.message || 'กรุณาตรวจสอบ token');
            }
        });
    }

    if (testGroupCheckButton && groupChatIdField && botTokenField) {
        testGroupCheckButton.addEventListener('click', async function () {
            const botToken = safeText(botTokenField.value).trim();
            const groupChatId = safeText(groupChatIdField.value).trim();
            if (!botToken) {
                flash('warning', 'กรุณาระบุ Bot Token', 'ก่อนตรวจสอบกลุ่ม');
                return;
            }
            if (!groupChatId) {
                flash('warning', 'กรุณาระบุ Chat ID', 'ก่อนตรวจสอบกลุ่ม');
                return;
            }

            try {
                const result = await postJson(config.testGroupUrl, {
                    mode: 'check',
                    bot_token: botToken,
                    group_chat_id: groupChatId,
                }, testGroupCheckButton, 'กำลังตรวจสอบ...');

                if (!(result.status === 'success')) {
                    const statusKey = result.error_type || normalizeErrorType(result.message, 'disconnected');
                    groupStatusInput.value = statusKey;
                    updateStatus('group', statusKey);
                    throw new Error(result.message || 'ตรวจสอบกลุ่มไม่สำเร็จ');
                }

                groupStatusInput.value = 'connected';
                updateStatus('group', 'connected');
                if (groupNameField && result.group_title) {
                    groupNameField.value = result.group_title;
                }
                if (groupMemberCountField && result.member_count !== undefined && result.member_count !== null) {
                    groupMemberCountField.value = result.member_count;
                }
                if (groupLinkField && result.group_link) {
                    groupLinkField.value = result.group_link;
                }
                if (groupChatIdField && result.chat_id) {
                    groupChatIdField.value = result.chat_id;
                }
                flash('success', 'เชื่อมต่อกลุ่มได้แล้ว', result.message || 'ตรวจสอบสำเร็จ');
            } catch (error) {
                flash('error', 'ตรวจสอบกลุ่มไม่สำเร็จ', error.message || 'กรุณาตรวจสอบ Chat ID');
            }
        });
    }

    if (testGroupSendButton && groupChatIdField && botTokenField) {
        testGroupSendButton.addEventListener('click', async function () {
            const botToken = safeText(botTokenField.value).trim();
            const groupChatId = safeText(groupChatIdField.value).trim();
            if (!botToken) {
                flash('warning', 'กรุณาระบุ Bot Token', 'ก่อนส่งข้อความทดสอบ');
                return;
            }
            if (!groupChatId) {
                flash('warning', 'กรุณาระบุ Chat ID', 'ก่อนส่งข้อความทดสอบ');
                return;
            }

            try {
                const result = await postJson(config.testGroupUrl, {
                    mode: 'send',
                    bot_token: botToken,
                    group_chat_id: groupChatId,
                }, testGroupSendButton, 'กำลังส่งข้อความ...');

                if (!(result.status === 'success')) {
                    const statusKey = result.error_type || normalizeErrorType(result.message, 'disconnected');
                    groupStatusInput.value = statusKey;
                    updateStatus('group', statusKey);
                    throw new Error(result.message || 'ส่งข้อความทดสอบไม่สำเร็จ');
                }

                groupStatusInput.value = 'connected';
                updateStatus('group', 'connected');
                flash('success', 'ส่งข้อความทดสอบแล้ว', result.message || 'ข้อความถูกส่งไปยังกลุ่มเรียบร้อย');
            } catch (error) {
                flash('error', 'ส่งข้อความไม่สำเร็จ', error.message || 'กรุณาลองใหม่อีกครั้ง');
            }
        });
    }

    if (setMenuButton && miniAppField && botTokenField) {
        setMenuButton.addEventListener('click', async function () {
            const botToken = safeText(botTokenField.value).trim();
            const miniAppUrl = safeText(miniAppField.value).trim();
            if (!botToken) {
                flash('warning', 'กรุณาระบุ Bot Token', 'ก่อนตั้งค่าเมนู Bot');
                return;
            }
            if (!miniAppUrl) {
                flash('warning', 'กรุณาระบุ Mini App URL', 'ก่อนตั้งค่าเมนู Bot');
                return;
            }

            try {
                const result = await postJson(config.setMenuUrl, {
                    bot_token: botToken,
                    mini_app_url: miniAppUrl,
                }, setMenuButton, 'กำลังตั้งค่า...');

                if (!(result.status === 'success')) {
                    throw new Error(result.message || 'ตั้งค่าเมนู Bot ไม่สำเร็จ');
                }

                flash('success', 'ตั้งค่าเมนู Bot แล้ว', result.message || 'ตั้งค่าเมนูสำเร็จ');
            } catch (error) {
                flash('error', 'ตั้งค่าเมนูไม่สำเร็จ', error.message || 'กรุณาตรวจสอบ URL');
            }
        });
    }

    testUserButtons.forEach(function (button) {
        button.addEventListener('click', async function () {
            const userId = button.getAttribute('data-test-user-id');
            if (!userId) {
                return;
            }

            try {
                setBusy(button, true, 'กำลังส่ง...');
                const response = await fetch(config.testUserUrl + '?id=' + encodeURIComponent(userId), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const result = await response.json();
                if (!(result.status === 'success')) {
                    throw new Error(result.message || 'ส่งข้อความทดสอบไม่สำเร็จ');
                }
                flash('success', 'ส่งข้อความให้ผู้ใช้แล้ว', result.message || 'ส่งสำเร็จ');
            } catch (error) {
                flash('error', 'ส่งข้อความไม่สำเร็จ', error.message || 'กรุณาลองใหม่อีกครั้ง');
            } finally {
                setBusy(button, false);
            }
        });
    });

    document.querySelectorAll('[data-copy-target]').forEach(function (button) {
        button.addEventListener('click', function () {
            const targetId = button.getAttribute('data-copy-target');
            const target = targetId ? document.getElementById(targetId) : null;
            copyText(target ? target.value : '');
        });
    });

    bindUploadZone();
    updateMiniAppStatus();
    updateQrcodePreview(config.previewUrl || '', qrcodeIdField ? safeText(qrcodeIdField.value || config.qrcodeId || '') : '');
    updateStatus('bot', botStatusValue || 'default');
    updateStatus('group', groupStatusValue || 'default');

    if (botToggleButton && botTokenField) {
        botToggleButton.innerHTML = '<i class="fa-regular fa-eye me-1"></i> แสดง Token';
    }
})();
JS, View::POS_END);
?>

<div class="telegram-admin-shell container-fluid py-4">
    <div class="hero-panel p-4 p-xl-5 mb-4">
        <div class="d-flex flex-column flex-xl-row justify-content-between gap-4">
            <div class="flex-grow-1">
                <div class="d-inline-flex align-items-center gap-2 rounded-pill px-3 py-2 mb-3" style="background:#eaf1ff;color:#2155d6;font-weight:800;">
                    <i class="fa-brands fa-telegram"></i>
                    Telegram Admin
                </div>
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="hero-icon">
                        <i class="fa-solid fa-gear fs-5"></i>
                    </div>
                    <div>
                        <h1 class="h3 fw-bold mb-1">ตั้งค่า Telegram Notification และ Bot Management</h1>
                        <p class="section-desc mb-0">
                            โครงสร้างหน้าแบบ clean enterprise admin สำหรับจัดการ bot, group, QR Code และ mini app จากจุดเดียว
                        </p>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 mb-4">
                    <a href="#section-general" class="btn btn-light border rounded-3 px-3">General</a>
                    <a href="#section-group" class="btn btn-light border rounded-3 px-3">Telegram Group</a>
                    <a href="#section-qrcode" class="btn btn-light border rounded-3 px-3">QR Code</a>
                    <a href="#section-integration" class="btn btn-light border rounded-3 px-3">Integration</a>
                    <a href="#section-bindings" class="btn btn-light border rounded-3 px-3">User Bindings</a>
                </div>

                <div class="row row-cols-1 row-cols-md-2 row-cols-xxl-4 g-3">
                    <div class="col">
                        <div class="quick-stat">
                            <div class="d-flex align-items-start justify-content-between gap-3 mb-2">
                                <div class="stat-icon">
                                    <i class="fa-brands fa-telegram"></i>
                                </div>
                                <?= $renderStatusPill('bot', $botStatusMeta) ?>
                            </div>
                            <div class="label mb-1">Bot</div>
                            <div class="value"><?= Html::encode($botNameValue !== '' ? $botNameValue : 'Bot Name not set') ?></div>
                            <div class="meta mt-1">Username: <?= Html::encode($botUsernameValue !== '' ? '@' . ltrim($botUsernameValue, '@') : '-') ?></div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="quick-stat">
                            <div class="d-flex align-items-start justify-content-between gap-3 mb-2">
                                <div class="stat-icon">
                                    <i class="fa-solid fa-users"></i>
                                </div>
                                <?= $renderStatusPill('group', $groupStatusMeta) ?>
                            </div>
                            <div class="label mb-1">Telegram Group</div>
                            <div class="value"><?= Html::encode($groupNameValue !== '' ? $groupNameValue : 'Group not set') ?></div>
                            <div class="meta mt-1">Chat ID: <?= Html::encode($groupChatIdValue !== '' ? $groupChatIdValue : '-') ?></div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="quick-stat">
                            <div class="d-flex align-items-start justify-content-between gap-3 mb-2">
                                <div class="stat-icon">
                                    <i class="fa-solid fa-qrcode"></i>
                                </div>
                                <?= $renderStatusPill('qr', $qrStatusMeta) ?>
                            </div>
                            <div class="label mb-1">QR Code</div>
                            <div class="value"><?= $hasQrcode ? 'Ready to use' : 'No QR uploaded' ?></div>
                            <div class="meta mt-1">File ID: <?= $qrcodeUploadIdValue > 0 ? '#' . (int) $qrcodeUploadIdValue : '-' ?></div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="quick-stat">
                            <div class="d-flex align-items-start justify-content-between gap-3 mb-2">
                                <div class="stat-icon">
                                    <i class="fa-solid fa-user-check"></i>
                                </div>
                                <span class="status-pill is-secondary"><?= (int) $bindingsCount ?> users</span>
                            </div>
                            <div class="label mb-1">User Bindings</div>
                            <div class="value"><?= number_format($bindingsCount) ?> users</div>
                            <div class="meta mt-1">Telegram ID ผูกแล้วในระบบ</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-column gap-3" style="width:min(100%, 360px);">
                <div class="side-card p-4">
                    <div class="section-kicker mb-2">Quick Actions</div>
                    <div class="section-title mb-2">ดำเนินการทันที</div>
                    <div class="mini-note mb-3">บันทึกการตั้งค่า หรือกระโดดไปยัง section ที่ต้องการได้จากกล่องนี้</div>
                    <div class="d-grid gap-2">
                        <button type="submit" form="telegram-settings-form" class="btn btn-primary rounded-3 shadow-sm">
                            <i class="fa-solid fa-floppy-disk me-1"></i> บันทึกการตั้งค่า
                        </button>
                        <a href="#section-general" class="btn btn-outline-secondary rounded-3 text-start">
                            <i class="fa-solid fa-gear me-1"></i> ไปข้อมูล Bot
                        </a>
                        <a href="#section-group" class="btn btn-outline-secondary rounded-3 text-start">
                            <i class="fa-solid fa-users me-1"></i> ไปข้อมูล Group
                        </a>
                        <a href="#section-qrcode" class="btn btn-outline-secondary rounded-3 text-start">
                            <i class="fa-solid fa-qrcode me-1"></i> ไป QR Code
                        </a>
                        <a href="#section-integration" class="btn btn-outline-secondary rounded-3 text-start">
                            <i class="fa-solid fa-mobile-screen-button me-1"></i> ไป Mini App
                        </a>
                    </div>
                </div>

                <div class="side-card p-4">
                    <div class="section-kicker mb-2">Work Notes</div>
                    <div class="section-title mb-2">แนวทางใช้งาน</div>
                    <ul class="mini-note mb-0 ps-3">
                        <li class="mb-2">กด <b>ทดสอบ Bot</b> เพื่อดึงชื่อ bot และยืนยัน token</li>
                        <li class="mb-2">กด <b>ตรวจสอบกลุ่ม</b> หลังใส่ Chat ID เพื่อยืนยันว่า bot เข้าได้จริง</li>
                        <li class="mb-2">อัปโหลด QR Code แบบสี่เหลี่ยมจัตุรัส และครอปก่อนส่ง</li>
                        <li>Mini App ต้องเป็น URL แบบ <code>https</code> และเข้าถึงได้จากภายนอก</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 align-items-start">
        <div class="col-12 col-xl-8">
            <?php $form = ActiveForm::begin([
                'id' => 'telegram-settings-form',
                'options' => ['autocomplete' => 'off'],
            ]); ?>

            <div class="d-none">
                <?= $form->field($model, 'name')->hiddenInput()->label(false) ?>
                <?= $form->field($model, 'code')->hiddenInput()->label(false) ?>
                <?= $form->field($model, 'title')->hiddenInput()->label(false) ?>
                <?= $form->field($model, 'data_json[bot_status]')->hiddenInput(['id' => 'telegram-bot-status-input'])->label(false) ?>
                <?= $form->field($model, 'data_json[group_status]')->hiddenInput(['id' => 'telegram-group-status-input'])->label(false) ?>
                <?= $form->field($model, 'data_json[group_qrcode_id]')->hiddenInput(['id' => 'telegram-group-qrcode-id-input'])->label(false) ?>
            </div>

            <div class="section-card nav-anchor" id="section-general">
                <div class="card-body p-4 p-xl-5">
                    <div class="section-topline mb-3"></div>
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                        <div>
                            <div class="section-kicker">General Settings</div>
                            <div class="section-title">ข้อมูลบอท Telegram</div>
                            <div class="section-desc">กรอกข้อมูล bot ให้ครบ แล้วใช้ปุ่มทดสอบเพื่อยืนยันสถานะก่อนบันทึก</div>
                        </div>
                        <?= $renderStatusPill('bot', $botStatusMeta) ?>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <?= $form->field($model, 'data_json[bot_name]')->label('ชื่อบอท')->textInput([
                                'id' => 'telegram-bot-name',
                                'class' => 'form-control',
                                'maxlength' => true,
                                'placeholder' => 'เช่น ERP Notification Bot',
                            ]) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'data_json[bot_username]')->label('Username ของบอท')->textInput([
                                'id' => 'telegram-bot-username',
                                'class' => 'form-control',
                                'maxlength' => true,
                                'placeholder' => 'เช่น erp_notification_bot',
                            ]) ?>
                        </div>
                        <div class="col-12">
                            <?= $form->field($model, 'data_json[bot_token]')->label('Bot Token')->passwordInput([
                                'id' => 'telegram-bot-token',
                                'class' => 'form-control',
                                'maxlength' => true,
                                'autocomplete' => 'off',
                                'placeholder' => 'วาง Bot Token จาก BotFather',
                            ]) ?>
                            <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm rounded-3" id="btn-toggle-bot-token">
                                    <i class="fa-regular fa-eye me-1"></i> แสดง Token
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm rounded-3" id="btn-test-bot">
                                    <i class="fa-solid fa-plug-circle-check me-1"></i> ทดสอบ Bot
                                </button>
                                <span class="mini-note">ระบบนี้ใช้ token เดียวสำหรับการทดสอบ bot และการแจ้งเตือนโดยตรง</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-card nav-anchor" id="section-group">
                <div class="card-body p-4 p-xl-5">
                    <div class="section-topline mb-3"></div>
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                        <div>
                            <div class="section-kicker">Telegram Group</div>
                            <div class="section-title">จัดการข้อมูลกลุ่มสำหรับแจ้งเตือน</div>
                            <div class="section-desc">ข้อมูลกลุ่มนี้จะใช้เป็นเป้าหมายของข้อความแจ้งเตือนและการทดสอบส่งข้อความ</div>
                        </div>
                        <?= $renderStatusPill('group', $groupStatusMeta) ?>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <?= $form->field($model, 'data_json[group_name]')->label('ชื่อกลุ่ม')->textInput([
                                'id' => 'telegram-group-name',
                                'class' => 'form-control',
                                'maxlength' => true,
                                'placeholder' => 'เช่น กลุ่มแจ้งเตือน ERP',
                            ]) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'data_json[group_chat_id]')->label('Chat ID')->textInput([
                                'id' => 'telegram-group-chat-id',
                                'class' => 'form-control',
                                'maxlength' => true,
                                'placeholder' => 'เช่น -1001234567890',
                            ]) ?>
                        </div>
                        <div class="col-md-8">
                            <?= $form->field($model, 'data_json[group_link]')->label('ลิงก์กลุ่ม')->textInput([
                                'id' => 'telegram-group-link',
                                'class' => 'form-control',
                                'type' => 'url',
                                'placeholder' => 'https://t.me/...',
                            ]) ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($model, 'data_json[group_member_count]')->label('จำนวนสมาชิก')->textInput([
                                'id' => 'telegram-group-member-count',
                                'class' => 'form-control',
                                'readonly' => true,
                                'placeholder' => '0',
                            ]) ?>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <button type="button" class="btn btn-outline-primary rounded-3" id="btn-test-group-check">
                            <i class="fa-solid fa-link me-1"></i> ตรวจสอบการเชื่อมต่อ
                        </button>
                        <button type="button" class="btn btn-outline-primary rounded-3" id="btn-test-group-send">
                            <i class="fa-regular fa-paper-plane me-1"></i> ส่งข้อความทดสอบ
                        </button>
                        <button type="button" class="btn btn-outline-secondary rounded-3" data-copy-target="telegram-group-chat-id">
                            <i class="fa-regular fa-copy me-1"></i> คัดลอก Chat ID
                        </button>
                        <button type="button" class="btn btn-outline-secondary rounded-3" data-copy-target="telegram-group-link">
                            <i class="fa-regular fa-copy me-1"></i> คัดลอกลิงก์กลุ่ม
                        </button>
                    </div>

                    <div class="help-card p-3 mt-3">
                        <div class="d-flex align-items-start gap-2">
                            <i class="fa-solid fa-circle-info text-primary mt-1"></i>
                            <div class="mini-note">
                                ใช้ Chat ID ของกลุ่มหรือ channel ที่ bot เข้าถึงได้จริง จากนั้นกดตรวจสอบเพื่อดึงชื่อกลุ่ม, ประเภท และจำนวนสมาชิกอัตโนมัติ
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-card nav-anchor" id="section-qrcode">
                <div class="card-body p-4 p-xl-5">
                    <div class="section-topline mb-3"></div>
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                        <div>
                            <div class="section-kicker">QR Code Upload</div>
                            <div class="section-title">อัปโหลด QR Code สำหรับกลุ่ม Telegram</div>
                            <div class="section-desc">รองรับ JPG, PNG และ WEBP พร้อม crop ก่อนอัปโหลดเพื่อให้ภาพดูคมและเท่ากันทุกอุปกรณ์</div>
                        </div>
                        <?= $renderStatusPill('qr', $qrStatusMeta) ?>
                    </div>

                    <div class="row g-4 align-items-stretch">
                        <div class="col-lg-7">
                            <div class="qr-dropzone p-4 d-flex flex-column justify-content-center" id="telegram-qr-dropzone">
                                <input type="file" id="telegram-qr-file" class="d-none" accept="image/jpeg,image/png,image/webp">

                                <div class="text-center<?= $hasQrcode ? ' d-none' : '' ?>" id="telegram-qr-empty">
                                    <div class="hero-icon mx-auto mb-3" style="width:72px;height:72px;border-radius:1.25rem;">
                                        <i class="fa-solid fa-cloud-arrow-up fs-3"></i>
                                    </div>
                                    <div class="fw-bold mb-1">ลากไฟล์ QR Code มาวางที่นี่</div>
                                    <div class="mini-note mb-3">หรือคลิกเพื่อเลือกไฟล์จากเครื่อง รองรับภาพสี่เหลี่ยมจัตุรัสสำหรับ Telegram Group</div>
                                    <button type="button" class="btn btn-primary rounded-3">
                                        <i class="fa-solid fa-upload me-1"></i> เลือกไฟล์ / แทนที่รูป
                                    </button>
                                </div>

                                <div class="qr-preview-wrap<?= $hasQrcode ? '' : ' d-none' ?>" id="telegram-qr-preview-wrap">
                                    <img src="<?= Html::encode($qrcodePreviewUrl) ?>" alt="QR Code" id="telegram-qr-preview-image"<?= $hasQrcode ? '' : ' class="d-none"' ?>>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap gap-2 mt-3">
                                <button type="button" class="btn btn-outline-primary rounded-3" id="btn-select-qrcode">
                                    <i class="fa-solid fa-arrow-up-from-bracket me-1"></i> อัปโหลด / เปลี่ยนรูป
                                </button>
                                <button type="button" class="btn btn-outline-secondary rounded-3" id="btn-preview-qrcode"<?= $hasQrcode ? '' : ' disabled' ?>>
                                    <i class="fa-regular fa-image me-1"></i> ดูเต็มจอ
                                </button>
                                <button type="button" class="btn btn-outline-danger rounded-3" id="btn-delete-qrcode"<?= $hasQrcode ? '' : ' disabled' ?>>
                                    <i class="fa-regular fa-trash-can me-1"></i> ลบรูป
                                </button>
                            </div>
                        </div>

                        <div class="col-lg-5">
                            <div class="help-card p-4 h-100">
                                <div class="section-kicker mb-2">Preview Notes</div>
                                <div class="section-title mb-2">การแสดงผล QR</div>
                                <div class="mini-note mb-3">
                                    ภาพ QR จะถูกแทนที่อัตโนมัติเมื่ออัปโหลดภาพใหม่ และใช้ภาพล่าสุดที่บันทึกไว้ในระบบเสมอ
                                </div>
                                <ul class="mini-note mb-0 ps-3">
                                    <li class="mb-2">ครอปให้เป็นสี่เหลี่ยมจัตุรัสก่อนอัปโหลด</li>
                                    <li class="mb-2">หลีกเลี่ยงข้อความหรือขอบภาพเกินเข้ามาในเฟรม</li>
                                    <li class="mb-2">ใช้ไฟล์ที่คมชัดเพื่อให้สแกนบนมือถือได้ง่าย</li>
                                    <li>หลังอัปโหลด ระบบจะจำหมายเลขไฟล์ล่าสุดให้อัตโนมัติ</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-card nav-anchor" id="section-integration">
                <div class="card-body p-4 p-xl-5">
                    <div class="section-topline mb-3"></div>
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                        <div>
                            <div class="section-kicker">Integration</div>
                            <div class="section-title">Mini App และเมนู Telegram</div>
                            <div class="section-desc">กำหนด URL สำหรับเปิด ERP Mobile หรือ Web App ผ่านปุ่มเมนูของ Telegram bot</div>
                        </div>
                        <?= $renderStatusPill('miniapp', $miniAppStatusMeta) ?>
                    </div>

                    <div class="row g-3 align-items-end">
                        <div class="col-lg-9">
                            <?= $form->field($model, 'data_json[mini_app]')->label('Mini App URL')->textInput([
                                'id' => 'telegram-mini-app-url',
                                'class' => 'form-control',
                                'type' => 'url',
                                'maxlength' => true,
                                'placeholder' => 'https://example.com/mobile',
                            ]) ?>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-check form-switch mb-1">
                                <?= Html::activeCheckbox($model, 'data_json[enable_mini_app]', [
                                    'id' => 'telegram-enable-mini-app',
                                    'class' => 'form-check-input',
                                    'uncheck' => '0',
                                    'value' => '1',
                                ]) ?>
                                <?= Html::label('เปิดใช้งาน Mini App', 'telegram-enable-mini-app', ['class' => 'form-check-label ms-2']) ?>
                            </div>
                            <div class="mini-note">ต้องเป็น URL แบบ HTTPS และเปิดจากภายนอกได้</div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap align-items-center gap-2 mt-3">
                        <button type="button" class="btn btn-outline-primary rounded-3" id="btn-set-menu">
                            <i class="fa-solid fa-bars-progress me-1"></i> ตั้งค่าเมนู Bot
                        </button>
                        <a href="#section-general" class="btn btn-outline-secondary rounded-3">
                            <i class="fa-solid fa-angles-up me-1"></i> กลับไปข้อมูลหลัก
                        </a>
                    </div>

                    <div class="help-card p-3 mt-3">
                        <div class="d-flex align-items-start gap-2">
                            <i class="fa-solid fa-shield-halved text-primary mt-1"></i>
                            <div class="mini-note">
                                เมื่อกดตั้งค่าเมนู Bot ระบบจะใช้ bot token ปัจจุบันและ Mini App URL ที่กรอกไว้ เพื่อกำหนดปุ่มเมนูใน Telegram โดยตรง
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-card nav-anchor" id="section-bindings">
                <div class="card-body p-4 p-xl-5">
                    <div class="section-topline mb-3"></div>
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3 mb-4">
                        <div>
                            <div class="section-kicker">User Bindings</div>
                            <div class="section-title">ผู้ใช้งานที่ผูก Telegram แล้ว</div>
                            <div class="section-desc">ค้นหาและทดสอบส่งข้อความรายคนจากหน้าเดียว เพื่อช่วยตรวจสอบการแจ้งเตือนแบบ end-to-end</div>
                        </div>
                        <div class="text-lg-end">
                            <div class="status-pill is-secondary mb-2"><?= number_format($bindingsCount) ?> users</div>
                            <div class="mini-note">ผูก Telegram ID แล้ว</div>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-12 col-lg-6">
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass"></i></span>
                                <input type="search" class="form-control" id="telegram-binding-search" placeholder="ค้นหาชื่อ, Telegram ID, แผนก หรือ ตำแหน่ง">
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 binding-table">
                            <thead>
                                <tr>
                                    <th>ผู้ใช้งาน</th>
                                    <th>แผนก / ตำแหน่ง</th>
                                    <th>Telegram ID</th>
                                    <th class="text-center">สถานะ</th>
                                    <th class="text-end">ดำเนินการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($bindings)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            ยังไม่มีผู้ใช้ที่ผูก Telegram ID
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
                                        <tr data-binding-row>
                                            <td>
                                                <div class="fw-semibold"><?= Html::encode($employeeName !== '' ? $employeeName : '-') ?></div>
                                                <div class="mini-note"><?= Html::encode($binding->username ?? '') ?></div>
                                            </td>
                                            <td>
                                                <div><?= Html::encode($departmentName !== '' ? $departmentName : '-') ?></div>
                                                <div class="mini-note"><?= Html::encode($positionName !== '' ? $positionName : '-') ?></div>
                                            </td>
                                            <td>
                                                <code><?= Html::encode($telegramId !== '' ? $telegramId : '-') ?></code>
                                            </td>
                                            <td class="text-center">
                                                <span class="status-pill is-success">Linked</span>
                                            </td>
                                            <td class="text-end">
                                                <button type="button" class="btn btn-sm btn-outline-primary rounded-3" data-test-user-id="<?= (int) $binding->id ?>">
                                                    <i class="fa-regular fa-paper-plane me-1"></i> ทดสอบ
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-4">
                <div class="mini-note">
                    ข้อมูลทั้งหมดจะถูกเก็บใน setting เดียวของ <code>telegram_setting</code> เพื่อให้ bot, group และ mini app ใช้งานร่วมกันได้ง่าย
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="<?= Url::to(['/settings/telegram/index']) ?>" class="btn btn-outline-secondary rounded-3">
                        <i class="fa-solid fa-layer-group me-1"></i> จัดการกลุ่ม Telegram
                    </a>
                    <button type="submit" form="telegram-settings-form" class="btn btn-primary rounded-3 shadow-sm">
                        <i class="fa-solid fa-floppy-disk me-1"></i> บันทึกการตั้งค่า
                    </button>
                </div>
            </div>

            <?php ActiveForm::end(); ?>
        </div>

        <div class="col-12 col-xl-4">
            <div class="sticky-xl-top">
                <div class="side-card p-4 mb-3">
                    <div class="section-kicker mb-2">Status Overview</div>
                    <div class="section-title mb-3">ภาพรวมการตั้งค่า</div>
                    <div class="d-grid gap-3">
                        <div class="d-flex align-items-center justify-content-between gap-3">
                            <div>
                                <div class="fw-semibold">Bot</div>
                                <div class="mini-note">ตรวจสอบ token และชื่อ bot</div>
                            </div>
                            <?= $renderStatusPill('bot', $botStatusMeta) ?>
                        </div>
                        <div class="d-flex align-items-center justify-content-between gap-3">
                            <div>
                                <div class="fw-semibold">Group</div>
                                <div class="mini-note">Chat ID และสถานะการเชื่อมต่อ</div>
                            </div>
                            <?= $renderStatusPill('group', $groupStatusMeta) ?>
                        </div>
                        <div class="d-flex align-items-center justify-content-between gap-3">
                            <div>
                                <div class="fw-semibold">QR Code</div>
                                <div class="mini-note">ภาพที่ใช้กับ group</div>
                            </div>
                            <?= $renderStatusPill('qr', $qrStatusMeta) ?>
                        </div>
                        <div class="d-flex align-items-center justify-content-between gap-3">
                            <div>
                                <div class="fw-semibold">Mini App</div>
                                <div class="mini-note">สถานะการเปิดใช้งาน</div>
                            </div>
                            <?= $renderStatusPill('miniapp', $miniAppStatusMeta) ?>
                        </div>
                    </div>
                </div>

                <div class="side-card p-4 mb-3">
                    <div class="section-kicker mb-2">Shortcuts</div>
                    <div class="section-title mb-3">ลิงก์ด่วน</div>
                    <div class="d-grid gap-2">
                        <a href="<?= Url::to(['/settings/telegram/index']) ?>" class="btn btn-outline-primary rounded-3 text-start">
                            <i class="fa-solid fa-layer-group me-1"></i> จัดการกลุ่ม Telegram
                        </a>
                        <a href="#section-qrcode" class="btn btn-outline-secondary rounded-3 text-start">
                            <i class="fa-solid fa-qrcode me-1"></i> ไปส่วน QR Code
                        </a>
                        <a href="#section-bindings" class="btn btn-outline-secondary rounded-3 text-start">
                            <i class="fa-solid fa-user-check me-1"></i> ไปส่วนผู้ใช้งานที่ผูก Telegram
                        </a>
                        <a href="#section-integration" class="btn btn-outline-secondary rounded-3 text-start">
                            <i class="fa-solid fa-mobile-screen-button me-1"></i> ไปส่วน Mini App
                        </a>
                    </div>
                </div>

                <div class="side-card p-4">
                    <div class="section-kicker mb-2">Guidance</div>
                    <div class="section-title mb-3">ข้อแนะนำ</div>
                    <ul class="mini-note mb-0 ps-3">
                        <li class="mb-2">หาก bot ไม่ตอบกลับ ให้ตรวจสอบว่า token ถูกต้องและ bot ยังไม่ถูก revoke</li>
                        <li class="mb-2">กรณี group เป็น private ให้ใส่ chat id ที่ถูกต้องจาก Telegram</li>
                        <li class="mb-2">Upload QR ใหม่จะถูกเก็บเป็นไฟล์ล่าสุดและแสดงผลทันที</li>
                        <li>ถ้าต้องการใช้งาน multi-group สามารถจัดการได้จากหน้า Telegram groups</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="telegram-qr-crop-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0">
                <div>
                    <h5 class="modal-title fw-bold mb-0">Crop QR Code</h5>
                    <div class="mini-note">ปรับสัดส่วนภาพก่อนอัปโหลด</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-0">
                <div class="ratio ratio-1x1 bg-light rounded-4 overflow-hidden">
                    <img id="telegram-qr-crop-image" alt="Crop QR Code" style="max-width:100%;display:block;">
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal" id="btn-cancel-qrcode-crop-2">ยกเลิก</button>
                <button type="button" class="btn btn-primary rounded-3" id="btn-confirm-qrcode-crop">
                    <i class="fa-solid fa-scissors me-1"></i> ครอปและอัปโหลด
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="telegram-qr-preview-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0">
                <div>
                    <h5 class="modal-title fw-bold mb-0">QR Code Preview</h5>
                    <div class="mini-note">ดูภาพเต็มจอก่อนใช้งาน</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-0">
                <div class="qr-preview-wrap" style="max-height:70vh;">
                    <img src="" alt="QR Preview" id="telegram-qr-preview-full">
                </div>
            </div>
        </div>
    </div>
</div>
