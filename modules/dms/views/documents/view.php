<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use app\models\Uploads;
use app\components\UserHelper;
use app\modules\hr\models\Organization;
use app\modules\dms\models\DocumentsDetail;

// ผู้อ่านเอกสาร (สำหรับ summary + offcanvas)
$readers = $model->viewHistory();

// นับไฟล์แนบ (เพื่อโชว์จำนวนใน header)
$attachmentCount = (int) Uploads::find()->where(['ref' => $model->ref, 'name' => 'document_clip'])->count();

/** @var yii\web\View $this */
/** @var app\modules\dms\models\Documents $model */

$this->title = $model->topic;
\yii\web\YiiAsset::register($this);
$this->registerJsFile(Url::to('/libs/pdf/pdf.min.js'), ['position' => View::POS_HEAD]);
$this->registerCss(<<<CSS
.modal-fullscreen .modal-body {
    padding: 0;
    display: flex;
    flex-direction: column;
    min-height: 0;
    overflow: hidden;
}

.modal-fullscreen .modal-body > .container-fluid.document-modal-shell {
    flex: 1 1 auto;
    min-height: 0;
}

.document-modal-shell {
    height: 100%;
    min-height: 0;
    display: flex;
    flex-direction: column;
}

.document-modal-split {
    flex: 1 1 auto;
    min-height: 0;
}

#mobilePdfViewer {
    background: linear-gradient(180deg, #f8fafc 0%, #eef2ff 100%);
    -webkit-overflow-scrolling: touch;
    overscroll-behavior: contain;
}

#mobilePdfViewer .pdf-page {
    display: flex;
    justify-content: center;
    margin-bottom: 1rem;
}

#mobilePdfViewer canvas {
    display: block;
    max-width: 100%;
    height: auto;
    border-radius: 16px;
    background: #fff;
    box-shadow: 0 16px 40px rgba(15, 23, 42, 0.12);
}

#mobilePdfViewer .pdf-page-label {
    font-size: .825rem;
    letter-spacing: .02em;
}

#doc-pdf-pane,
#doc-work-pane {
    min-height: 0;
    overflow: hidden;
}

#doc-work-pane {
    display: flex;
    flex-direction: column;
}

#doc-pdf-pane > #iframeWrapper,
#doc-pdf-pane > #mobilePdfViewer,
#doc-work-pane > .d-flex.flex-column.h-100,
#doc-work-pane > .document-work-shell {
    min-height: 0;
}

.document-work-shell {
    min-height: 0;
    height: 100%;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.document-modal-shell .min-width-0 {
    min-width: 0;
}

.document-work-scroll {
    flex: 1 1 auto;
    min-height: 0;
    overflow-y: auto;
    overflow-x: hidden;
    -webkit-overflow-scrolling: touch;
    overscroll-behavior: contain;
    scroll-behavior: smooth;
    scrollbar-gutter: stable both-edges;
    touch-action: pan-y;
    padding-bottom: calc(1rem + env(safe-area-inset-bottom));
}

.document-work-scroll::-webkit-scrollbar {
    width: 10px;
}

.document-work-scroll::-webkit-scrollbar-track {
    background: transparent;
}

.document-work-scroll::-webkit-scrollbar-thumb {
    background: rgba(148, 163, 184, 0.55);
    border-radius: 999px;
    border: 2px solid transparent;
    background-clip: content-box;
}

.document-work-scroll::-webkit-scrollbar-thumb:hover {
    background: rgba(100, 116, 139, 0.8);
    border: 2px solid transparent;
    background-clip: content-box;
}

.bookmark-toggle {
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    font-weight: 600;
    transition: transform .15s ease, box-shadow .15s ease;
}
.bookmark-toggle:hover { transform: translateY(-1px); }
.bookmark-toggle.is-on {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: #fff !important;
    box-shadow: 0 2px 6px rgba(245, 158, 11, 0.35);
    border: 1px solid transparent;
}
.bookmark-toggle.is-on i { color: #fff; }
.bookmark-toggle.is-off {
    background: #f1f5f9;
    color: #64748b !important;
    border: 1px solid #e2e8f0;
}
.bookmark-toggle.is-off i { color: #94a3b8; }
CSS);

$currentDeptIds = DocumentsDetail::find()
    ->where(['document_id' => $model->id, 'name' => 'department'])
    ->select('to_id')
    ->column();
$currentDeptIdsStr = array_map('strval', $currentDeptIds ?: []);

$emp = UserHelper::GetEmployee();
$isDeptHeadOrDeputy = false;
if ($emp && (int) ($emp->department ?? 0) > 0) {
    $org = Organization::findOne((int) $emp->department);
    if ($org) {
        $orgData = $org->data_json;
        if (is_string($orgData)) {
            $decoded = json_decode($orgData, true);
            $orgData = is_array($decoded) ? $decoded : [];
        }
        if (!is_array($orgData)) {
            $orgData = [];
        }
        $leader1 = isset($orgData['leader1']) && is_numeric($orgData['leader1']) ? (int) $orgData['leader1'] : 0;
        $leader2 = isset($orgData['leader2']) && is_numeric($orgData['leader2']) ? (int) $orgData['leader2'] : 0;
        $isDeptHeadOrDeputy = in_array((int) $emp->id, [$leader1, $leader2], true);
    }
}
$canManageDepartmentExtra = Yii::$app->user->can('document') || $isDeptHeadOrDeputy;
$pdfUrl = Url::to(['/me/documents/show', 'ref' => $model->ref]);
$pdfDownloadUrl = Url::to(['/me/documents/show', 'ref' => $model->ref, 'download' => 1]);
$pdfUrlJs = json_encode($pdfUrl, JSON_UNESCAPED_SLASHES);
$pdfWorkerUrlJs = json_encode(Url::to('/libs/pdf/pdf.worker.js'), JSON_UNESCAPED_SLASHES);

$bookmarkDetailId = isset($detail) && $detail ? (int) $detail->id : 0;
$bookmarkStatusRaw = isset($detail) && $detail ? ($detail->docRead()['status'] ?? 'N') : 'N';
$bookmarkIsOn = $bookmarkStatusRaw === 'Y';
$bookmarkState = [
    'status' => $bookmarkIsOn ? 'Y' : 'N',
    'label' => $bookmarkIsOn ? 'ปักหมุดแล้ว' : 'ปักหมุด',
    'stateClass' => $bookmarkIsOn ? 'is-on' : 'is-off',
];
$bookmarkUrl = $bookmarkDetailId ? Url::to(['/me/documents/bookmark', 'id' => $bookmarkDetailId]) : null;
?>

<div class="container-fluid p-0 document-modal-shell">
    <div class="d-lg-none position-sticky top-0 bg-white border-bottom shadow-sm" style="z-index:1030;">
        <div class="d-flex p-2 gap-2 align-items-center" id="mobile-pane-toggle">
            <button type="button" class="btn btn-primary rounded-pill flex-fill py-2 small fw-semibold" data-target-pane="pdf">
                <i class="fa-regular fa-file-lines me-1"></i> เอกสาร
            </button>
            <button type="button" class="btn btn-light text-secondary rounded-pill flex-fill py-2 small fw-semibold" data-target-pane="work">
                <i class="fa-regular fa-comments me-1"></i> รายละเอียด
                <span class="badge text-bg-light text-muted ms-1" id="mobile-badge-count" style="display:none;">0</span>
            </button>
            <?= Html::a(
                '<i class="fa-solid fa-download"></i>',
                $pdfDownloadUrl,
                [
                    'class' => 'btn btn-outline-primary rounded-pill py-2 px-3 flex-shrink-0',
                    'title' => 'ดาวน์โหลด PDF',
                    'aria-label' => 'ดาวน์โหลด PDF',
                    'data-bs-toggle' => 'tooltip',
                    'data-pjax' => '0',
                    'download' => true,
                ]
            ) ?>
        </div>
    </div>

    <div class="row g-0 document-modal-split" id="doc-split-pane">

        <div class="col-12 col-lg-6 bg-body-secondary d-flex flex-column" id="doc-pdf-pane" data-pane="pdf">
            <div id="mobilePdfViewer" class="d-lg-none w-100 h-100 overflow-auto p-3">
                <div id="mobilePdfStatus" class="d-flex flex-column align-items-center justify-content-center text-center gap-2 h-100 text-muted">
                    <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
                    <div class="small pdf-status-message">กำลังโหลด PDF...</div>
                </div>
            </div>
            <div id="iframeWrapper" class="w-100 h-100 d-none d-lg-block">
                <iframe id="myIframe"
                    src="<?= Html::encode($pdfUrl) ?>#view=FitH&toolbar=1&navpanes=0"
                    class="w-100 h-100 border-0 d-block bg-white">
                </iframe>
            </div>
        </div>

        <div class="col-12 col-lg-6 bg-body-tertiary" id="doc-work-pane" data-pane="work">
            <div class="d-flex flex-column h-100 document-work-shell">

                <div class="bg-white border-bottom border-light-subtle px-4 py-3 flex-shrink-0">
                    <div class="d-flex align-items-start gap-3">
                        <span class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-3 flex-shrink-0" style="width:44px;height:44px;">
                            <i class="fa-regular fa-file-lines fs-5"></i>
                        </span>
                        <div class="flex-grow-1 min-width-0">
                            <div class="text-uppercase small text-primary fw-semibold opacity-75" style="letter-spacing:.05em;">
                                <?= $model->document_type === 'DT2' ? 'หนังสือภายใน' : Html::encode($model->documentOrg->title ?? '-') ?>
                            </div>
                            <div class="fw-bold text-dark text-truncate" title="<?= Html::encode($model->topic) ?>">
                                <?= Html::encode($model->topic) ?>
                            </div>
                            <div class="d-flex flex-wrap gap-3 small text-muted mt-1 align-items-center">
                                <?php if ($model->doc_regis_number): ?>
                                    <span><i class="fa-regular fa-hashtag me-1"></i>เลขที่ <?= Html::encode($model->doc_regis_number) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($model->viewDocDate())): ?>
                                    <span><i class="fa-regular fa-calendar me-1"></i><?= $model->viewDocDate() ?></span>
                                <?php endif; ?>
                                <button type="button" class="btn btn-sm btn-light text-secondary rounded-pill px-2 py-0 small d-inline-flex align-items-center gap-1" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAttachments" aria-controls="offcanvasAttachments">
                                    <i class="fa-solid fa-paperclip"></i>
                                    ไฟล์แนบ
                                    <?php if ($attachmentCount > 0): ?>
                                        <span class="badge text-bg-primary rounded-pill"><?= $attachmentCount ?></span>
                                    <?php endif; ?>
                                </button>
                                <?= Html::a(
                                    '<i class="fa-solid fa-download"></i> ดาวน์โหลด PDF',
                                    $pdfDownloadUrl,
                                    [
                                        'class' => 'btn btn-sm btn-outline-primary rounded-pill px-2 py-0 small d-none d-lg-inline-flex align-items-center gap-1 text-nowrap',
                                        'title' => 'ดาวน์โหลด PDF',
                                        'aria-label' => 'ดาวน์โหลด PDF',
                                        'data-bs-toggle' => 'tooltip',
                                        'data-pjax' => '0',
                                        'download' => true,
                                    ]
                                ) ?>
                            </div>
                        </div>
                        <div class="d-flex flex-column align-items-end gap-1 flex-shrink-0">
                            <?php if ($bookmarkUrl): ?>
                                <?= Html::a(
                                    '<i class="fa-solid fa-thumbtack"></i> <span class="bookmark-label">' . Html::encode($bookmarkState['label']) . '</span>',
                                    $bookmarkUrl,
                                    [
                                        'class' => 'badge rounded-pill px-2 py-1 small bookmark-toggle ' . $bookmarkState['stateClass'],
                                        'id' => 'bookmark-toggle-' . $bookmarkDetailId,
                                        'data-detail-id' => $bookmarkDetailId,
                                        'data-state' => $bookmarkState['status'],
                                        'title' => $bookmarkIsOn ? 'ยกเลิกปักหมุด' : 'ปักหมุดเอกสาร',
                                        'aria-label' => 'ปักหมุดเอกสาร',
                                        'data-bs-toggle' => 'tooltip',
                                        'data-pjax' => '0',
                                    ]
                                ) ?>
                            <?php endif; ?>
                            <?php if ($model->doc_speed === 'ด่วนที่สุด'): ?>
                                <span class="badge text-bg-danger rounded-pill"><i class="fa-solid fa-circle-exclamation me-1"></i>ด่วนที่สุด</span>
                            <?php endif; ?>
                            <?php if ($model->secret === 'ลับที่สุด'): ?>
                                <span class="badge text-bg-dark rounded-pill"><i class="fa-solid fa-lock me-1"></i>ลับที่สุด</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mt-3 pt-3 border-top border-light-subtle d-flex flex-column flex-lg-row align-items-start justify-content-between gap-2 gap-lg-3">
                        <div id="timeline-summary-wrapper" class="flex-grow-1 min-width-0">
                            <?= $this->render('_timeline_summary', [
                                'model' => $model,
                                'canManageDepartmentExtra' => $canManageDepartmentExtra,
                            ]) ?>
                        </div>
                        <div id="read-summary-wrapper" class="flex-shrink-0 ms-lg-auto">
                            <?= $this->render('_read_summary', ['readers' => $readers]) ?>
                        </div>
                    </div>
                </div>

                <div class="document-work-scroll p-4">
                    <div class="d-flex flex-column gap-4">

                        <?php // สร้างงานจากหนังสือฉบับนี้ — ผู้ใช้ตัดสินใจว่าต้องมีคนทำตอนที่กำลังอ่านเนื้อหาอยู่ ?>
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <div class="text-body-secondary small">
                                <i class="bi bi-list-check me-1" aria-hidden="true"></i>
                                เรื่องนี้ต้องมีคนทำต่อหรือไม่
                            </div>
                            <button type="button" class="btn btn-sm btn-primary"
                                    data-bs-toggle="offcanvas" data-bs-target="#offcanvasCreateTask"
                                    aria-controls="offcanvasCreateTask"
                                    data-task-form-url="<?= Url::to(['/task/document/form', 'id' => $model->id]) ?>">
                                <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>สร้างงาน
                            </button>
                        </div>

                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                            <div class="px-4 py-3 border-bottom border-light-subtle">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="d-inline-flex align-items-center justify-content-center bg-info bg-opacity-10 text-info rounded-circle" style="width:32px;height:32px;">
                                        <i class="fa-regular fa-comments"></i>
                                    </span>
                                    <div>
                                        <div class="text-uppercase small text-info fw-semibold opacity-75" style="letter-spacing:.05em;">Comment</div>
                                        <div class="fw-bold text-dark">การลงความเห็น</div>
                                    </div>
                                </div>
                            </div>
                            <div class="listComment">
                                <?= $this->render('_comment_feed', ['model' => $model]) ?>
                            </div>
                            <div class="border-top border-light-subtle bg-white px-4 py-3">
                                <div class="viewFormComment">
                                    <div class="text-center text-muted small py-2">
                                        <div class="spinner-border spinner-border-sm me-2"></div> กำลังโหลดช่องเขียนความเห็น...
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="approval-card-wrapper">
                            <?= $this->render('req_approve_tags', ['model' => $model]) ?>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasReadHistory" aria-labelledby="offcanvasReadHistoryLabel">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold" id="offcanvasReadHistoryLabel">
            <i class="fa-regular fa-eye text-info me-2"></i>ประวัติการอ่าน
            <span class="badge text-bg-light text-muted ms-2 small fw-normal"><?= count($readers) ?> คน</span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0">
        <?= $this->render('_read_list', ['readers' => $readers]) ?>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasTimeline" aria-labelledby="offcanvasTimelineLabel" style="--bs-offcanvas-width: min(100vw, 920px);">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold" id="offcanvasTimelineLabel">
            <i class="fa-regular fa-clock text-primary me-2"></i>ไทม์ไลน์เอกสาร
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0">
        <div id="timeline-detail-wrapper">
            <?= $this->render('_forwarding_card', [
                'model' => $model,
                'canManageDepartmentExtra' => $canManageDepartmentExtra,
                'currentDeptIdsStr' => $currentDeptIdsStr,
            ]) ?>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAttachments" aria-labelledby="offcanvasAttachmentsLabel" style="--bs-offcanvas-width: 480px;">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold" id="offcanvasAttachmentsLabel">
            <i class="fa-solid fa-paperclip text-primary me-2"></i>ไฟล์แนบ
            <?php if ($attachmentCount > 0): ?>
                <span class="badge text-bg-light text-muted ms-2 small fw-normal"><?= $attachmentCount ?> รายการ</span>
            <?php endif; ?>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-3">
        <?= $this->render('_attachment_list', ['model' => $model]) ?>
    </div>
</div>

<?php // สร้างงานจากหนังสือ — เนื้อหาฟอร์มโหลดจากโมดูล task ตอนเปิดครั้งแรก ?>
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasCreateTask" aria-labelledby="offcanvasCreateTaskLabel" style="--bs-offcanvas-width: 480px;">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold" id="offcanvasCreateTaskLabel">
            <i class="bi bi-list-check text-primary me-2"></i>สร้างงานจากหนังสือ
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="ปิด"></button>
    </div>
    <div class="offcanvas-body p-3" id="createTaskBody">
        <div class="d-flex align-items-center gap-2 text-body-secondary">
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            กำลังเตรียมฟอร์ม...
        </div>
    </div>
</div>

<script>
(function () {
    var panel = document.getElementById('offcanvasCreateTask');
    var body = document.getElementById('createTaskBody');
    if (!panel || !body || panel.dataset.bound === '1') { return; }
    panel.dataset.bound = '1';

    var loaded = false;

    // ผูก event หลัง inject HTML เพราะ script ที่มากับ innerHTML จะไม่ทำงานเอง
    function bindForm() {
        var form = document.getElementById('task-from-doc-form');
        if (!form) { return; }

        form.querySelectorAll('.task-quick-date').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var input = document.getElementById('task-due-date');
                if (input) { input.value = btn.dataset.date || ''; }
                form.querySelectorAll('.task-quick-date').forEach(function (b) { b.classList.remove('active'); });
                btn.classList.add('active');
            });
        });

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var msg = document.getElementById('task-form-message');
            var submit = form.querySelector('button[type="submit"]');
            submit.disabled = true;
            msg.className = 'small text-body-secondary';
            msg.textContent = 'กำลังสร้างงาน...';

            fetch(form.action, { method: 'POST', body: new FormData(form), credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.status === 'success') {
                        msg.className = 'small text-success-emphasis';
                        msg.textContent = data.message;
                        submit.classList.remove('btn-primary');
                        submit.classList.add('btn-success');
                        submit.innerHTML = '<i class="bi bi-check-lg me-1"></i>มอบหมายแล้ว';
                    } else {
                        msg.className = 'small text-danger-emphasis';
                        msg.textContent = data.message || 'สร้างงานไม่สำเร็จ';
                        submit.disabled = false;
                    }
                })
                .catch(function () {
                    msg.className = 'small text-danger-emphasis';
                    msg.textContent = 'เชื่อมต่อไม่สำเร็จ กรุณาลองใหม่';
                    submit.disabled = false;
                });
        });
    }

    panel.addEventListener('show.bs.offcanvas', function (event) {
        if (loaded) { return; }
        var trigger = event.relatedTarget;
        var url = trigger ? trigger.getAttribute('data-task-form-url') : null;
        if (!url) { return; }

        fetch(url, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.text(); })
            .then(function (html) {
                body.innerHTML = html;
                loaded = true;
                bindForm();
            })
            .catch(function () {
                body.innerHTML = '<div class="alert alert-danger mb-0">โหลดฟอร์มไม่สำเร็จ กรุณาปิดแล้วลองใหม่</div>';
            });
    });
})();
</script>

<?php
$getCommentUrl = Url::to(['/me/documents/comment', 'id' => $model->id]);
$listCommentUrl = Url::to(['/me/documents/list-comment', 'id' => $model->id]);
$saveCommentTemplate = Url::to(['/me/documents/save-comment-template']);
$forwardingCardUrl = Url::to(['/dms/documents/forwarding-card', 'id' => $model->id]);
$js = <<<JS
(function () {
    function initBootstrapWidgets(root) {
        var scope = root || document;
        var tooltipSelector = '[data-bs-toggle="tooltip"]';
        var popoverSelector = '[data-bs-toggle="popover"]';

        if (window.bootstrap && bootstrap.Tooltip && scope.querySelectorAll) {
            scope.querySelectorAll(tooltipSelector).forEach(function (el) {
                bootstrap.Tooltip.getOrCreateInstance(el);
            });
        } else if (typeof $.fn.tooltip === 'function') {
            $(scope).find(tooltipSelector).tooltip();
        }

        if (window.bootstrap && bootstrap.Popover && scope.querySelectorAll) {
            scope.querySelectorAll(popoverSelector).forEach(function (el) {
                bootstrap.Popover.getOrCreateInstance(el);
            });
        } else if (typeof $.fn.popover === 'function') {
            $(scope).find(popoverSelector).popover();
        }
    }

    if (window.pdfjsLib) {
        pdfjsLib.GlobalWorkerOptions.workerSrc = $pdfWorkerUrlJs;
    }

    var pdfUrl = $pdfUrlJs;
    var mobilePdfViewer = document.getElementById('mobilePdfViewer');
    var mobilePdfStatus = document.getElementById('mobilePdfStatus');
    var mobilePdfRenderSeq = 0;
    var mobilePdfLoadTask = null;

    initBootstrapWidgets(document);
    updateCharCount();

    function isMobilePdfMode() {
        return window.innerWidth < 992;
    }

    function showPdfLoading(message) {
        if (!mobilePdfViewer) return;
        mobilePdfViewer.innerHTML = '' +
            '<div id="mobilePdfStatus" class="d-flex flex-column align-items-center justify-content-center text-center gap-2 h-100 text-muted">' +
                '<div class="spinner-border text-primary" role="status" aria-hidden="true"></div>' +
                '<div class="small pdf-status-message">' + message + '</div>' +
            '</div>';
        mobilePdfStatus = document.getElementById('mobilePdfStatus');
    }

    function showPdfError(message) {
        if (!mobilePdfViewer) return;
        mobilePdfViewer.innerHTML = '' +
            '<div id="mobilePdfStatus" class="d-flex flex-column align-items-center justify-content-center text-center gap-2 h-100 text-danger">' +
                '<i class="fa-solid fa-triangle-exclamation fs-3"></i>' +
                '<div class="small pdf-status-message">' + message + '</div>' +
                '<a href="' + pdfUrl + '" target="_blank" rel="noopener" class="btn btn-sm btn-primary rounded-pill">เปิด PDF ในแท็บใหม่</a>' +
            '</div>';
        mobilePdfStatus = document.getElementById('mobilePdfStatus');
    }

    async function renderMobilePdf() {
        if (!mobilePdfViewer || !window.pdfjsLib || !isMobilePdfMode()) return;
        var viewerWidth = Math.floor(mobilePdfViewer.clientWidth || 0);
        if (!viewerWidth) return;

        if (mobilePdfViewer.dataset.rendered === '1' && mobilePdfViewer.dataset.renderWidth === String(viewerWidth)) {
            return;
        }

        mobilePdfRenderSeq += 1;
        var renderSeq = mobilePdfRenderSeq;
        showPdfLoading('กำลังโหลด PDF...');

        if (mobilePdfLoadTask && typeof mobilePdfLoadTask.destroy === 'function') {
            try { mobilePdfLoadTask.destroy(); } catch (err) {}
        }

        try {
            mobilePdfLoadTask = pdfjsLib.getDocument({ url: pdfUrl });
            var pdfDoc = await mobilePdfLoadTask.promise;
            if (renderSeq !== mobilePdfRenderSeq) return;

            if (mobilePdfStatus) {
                var statusMessage = mobilePdfStatus.querySelector('.pdf-status-message');
                if (statusMessage) {
                    statusMessage.textContent = 'กำลังเตรียมเอกสาร...';
                }
            }

            var pageCount = pdfDoc.numPages;
            for (var pageNum = 1; pageNum <= pageCount; pageNum++) {
                if (renderSeq !== mobilePdfRenderSeq) return;
                var page = await pdfDoc.getPage(pageNum);
                if (renderSeq !== mobilePdfRenderSeq) return;

                var baseViewport = page.getViewport({ scale: 1 });
                var availableWidth = Math.max(280, viewerWidth - 48);
                var scale = availableWidth / baseViewport.width;
                var viewport = page.getViewport({ scale: scale });

                var pageWrap = document.createElement('div');
                pageWrap.className = 'pdf-page flex-column';

                var pageLabel = document.createElement('div');
                pageLabel.className = 'pdf-page-label text-muted text-center mb-2 fw-semibold';
                pageLabel.textContent = 'หน้า ' + pageNum + ' / ' + pageCount;
                pageWrap.appendChild(pageLabel);

                var canvas = document.createElement('canvas');
                canvas.width = Math.floor(viewport.width);
                canvas.height = Math.floor(viewport.height);
                canvas.style.width = Math.floor(viewport.width) + 'px';
                canvas.style.height = Math.floor(viewport.height) + 'px';
                pageWrap.appendChild(canvas);
                mobilePdfViewer.appendChild(pageWrap);

                await page.render({
                    canvasContext: canvas.getContext('2d', { alpha: false }),
                    viewport: viewport
                }).promise;

                if (mobilePdfStatus) {
                    var statusMessageAfterPage = mobilePdfStatus.querySelector('.pdf-status-message');
                    if (statusMessageAfterPage) {
                        statusMessageAfterPage.textContent = 'กำลังเรนเดอร์หน้า ' + pageNum + ' / ' + pageCount + '...';
                    }
                }
            }

            if (renderSeq !== mobilePdfRenderSeq) return;
            if (mobilePdfStatus && mobilePdfStatus.parentNode === mobilePdfViewer) {
                mobilePdfStatus.remove();
            }
            mobilePdfViewer.dataset.rendered = '1';
            mobilePdfViewer.dataset.renderWidth = String(viewerWidth);
            mobilePdfLoadTask = null;
        } catch (error) {
            if (renderSeq !== mobilePdfRenderSeq) return;
            console.error(error);
            mobilePdfLoadTask = null;
            showPdfError('ไม่สามารถแสดง PDF ได้บนอุปกรณ์นี้');
        }
    }

    function updateIframeHeight() {
        var iframe = document.getElementById('myIframe');
        var splitPane = document.getElementById('doc-split-pane');
        var workPane = document.getElementById('doc-work-pane');
        var pdfPane = document.getElementById('doc-pdf-pane');
        var wrapper = document.getElementById('iframeWrapper');
        var mobileViewer = document.getElementById('mobilePdfViewer');
        var workScroll = workPane ? workPane.querySelector('.document-work-scroll') : null;
        if (!iframe || !splitPane) return;
        var modalBody = splitPane.closest('.modal-body');
        var modalBodyRect = modalBody ? modalBody.getBoundingClientRect() : null;
        var splitRect = splitPane.getBoundingClientRect();
        var workScrollRect = workScroll ? workScroll.getBoundingClientRect() : null;
        var pdfTarget = isMobilePdfMode() ? mobileViewer : wrapper;
        var pdfTargetRect = pdfTarget ? pdfTarget.getBoundingClientRect() : null;
        var availableBase = modalBodyRect ? Math.max(0, Math.floor(modalBodyRect.bottom - splitRect.top)) : Math.max(0, Math.floor(window.innerHeight - splitRect.top));
        var workAvailable = modalBodyRect && workScrollRect ? Math.max(0, Math.floor(modalBodyRect.bottom - workScrollRect.top)) : availableBase;
        var pdfAvailable = modalBodyRect && pdfTargetRect ? Math.max(0, Math.floor(modalBodyRect.bottom - pdfTargetRect.top)) : availableBase;

        splitPane.style.height = '';
        if (pdfPane) { pdfPane.style.height = ''; }
        if (workPane) { workPane.style.height = ''; }
        if (wrapper) { wrapper.style.height = ''; }
        if (mobileViewer) { mobileViewer.style.height = ''; }
        if (workScroll) { workScroll.style.height = ''; }
        iframe.style.height = '';
        if (isMobilePdfMode()) {
            var available = Math.max(420, pdfAvailable);
            if (pdfPane) { pdfPane.style.height = available + 'px'; }
            if (workPane) { workPane.style.height = available + 'px'; }
            if (workScroll) { workScroll.style.height = Math.max(320, workAvailable) + 'px'; }
            if (wrapper) { wrapper.style.height = available + 'px'; }
            if (mobileViewer) { mobileViewer.style.height = available + 'px'; }
            iframe.style.height = available + 'px';
            renderMobilePdf();
        }
        else {
            var available = Math.max(500, pdfAvailable);
            if (workPane) { workPane.style.height = available + 'px'; }
            if (workScroll) { workScroll.style.height = Math.max(320, workAvailable) + 'px'; }
            if (wrapper) { wrapper.style.height = available + 'px'; }
            iframe.style.height = available + 'px';
        }
    }

    function applyMobilePaneVisibility(target) {
        var winWidth = window.innerWidth;
        var pdfPane = document.getElementById('doc-pdf-pane');
        var workPane = document.getElementById('doc-work-pane');
        if (!pdfPane || !workPane) return;
        if (winWidth >= 992) {
            // desktop: แสดงทั้งคู่
            pdfPane.classList.remove('d-none');
            workPane.classList.remove('d-none');
            return;
        }
        if (target === 'work') {
            pdfPane.classList.add('d-none');
            workPane.classList.remove('d-none');
        } else {
            pdfPane.classList.remove('d-none');
            workPane.classList.add('d-none');
        }
    }

    \$(document).off('click.mobilePane').on('click.mobilePane', '#mobile-pane-toggle [data-target-pane]', function () {
        var target = \$(this).data('target-pane');
        \$('#mobile-pane-toggle button').removeClass('btn-primary').addClass('btn-light text-secondary');
        \$(this).removeClass('btn-light text-secondary').addClass('btn-primary');
        applyMobilePaneVisibility(target);
        updateIframeHeight();
    });

    // initial state mobile = แสดง PDF ก่อน
    applyMobilePaneVisibility('pdf');
    updateIframeHeight();
    \$(window).on('resize', function () {
        var active = \$('#mobile-pane-toggle .btn-primary').data('target-pane') || 'pdf';
        applyMobilePaneVisibility(active);
        updateIframeHeight();
    });

    // textarea auto-grow + ความสูงสบายตามอนเปิด
    \$(document).off('input.taGrow focus.taGrow').on('input.taGrow focus.taGrow', '#documentsdetail-data_json-comment', function () {
        var ta = this;
        ta.style.height = 'auto';
        var minH = (window.innerWidth < 992) ? 96 : 56;
        ta.style.height = Math.max(minH, ta.scrollHeight) + 'px';
    });

    getComment();
})();

function injectAjaxContent(\$target, html) {
    if (typeof erpInjectModalContent === 'function') {
        return erpInjectModalContent(\$target, html);
    }

    \$target.html(html);
    return Promise.resolve();
}

async function getComment() {
    await \$.ajax({
        type: 'get',
        url: '$getCommentUrl',
        dataType: 'json',
        success: async function (res) {
            await injectAjaxContent(\$('.viewFormComment'), res.content);
            // composer มี #viewlistCommenttemplate อยู่ภายใน → load รายการ template หลัง composer มา
            if (typeof listCommentTemplate === 'function') { listCommentTemplate(); }
        }
    });
}

async function listComment() {
    await \$.ajax({
        type: 'get',
        url: '$listCommentUrl',
        dataType: 'json',
        success: function (res) {
            \$('.listComment').html(res.content);
        }
    });
}

function reloadTimeline() {
    var \$detailWrap = \$('#timeline-detail-wrapper');
    var \$summaryWrap = \$('#timeline-summary-wrapper');
    if (!\$detailWrap.length && !\$summaryWrap.length) { return \$.Deferred().resolve().promise(); }
    return \$.ajax({
        url: '$forwardingCardUrl',
        type: 'get',
        cache: false,
        dataType: 'json'
    }).then(function (res) {
        if (res && res.card && \$detailWrap.length) { \$detailWrap.html(res.card); }
        if (res && res.summary && \$summaryWrap.length) { \$summaryWrap.html(res.summary); }
        initBootstrapWidgets(\$detailWrap[0]);
        initBootstrapWidgets(\$summaryWrap[0]);
    }, function () {
        window.location.reload();
    });
}

\$('body').on('click', '.update-comment', function (e) {
    e.preventDefault();
    \$.ajax({
        type: 'get',
        url: \$(this).attr('href'),
        dataType: 'json',
        success: async function (res) {
            await injectAjaxContent(\$('.viewFormComment'), res.content);
            if (typeof listCommentTemplate === 'function') { listCommentTemplate(); }
            // scroll work pane down to composer
            setTimeout(function () {
                if (typeof updateIframeHeight === 'function') {
                    updateIframeHeight();
                }
                var target = document.getElementById('documentsdetail-data_json-comment') || document.querySelector('.viewFormComment');
                var scroller = target ? (target.closest('.document-work-scroll, .offcanvas-body, .modal-body') || target.parentElement) : null;
                if (scroller && target) {
                    var targetRect = target.getBoundingClientRect();
                    var scrollerRect = scroller.getBoundingClientRect();
                    scroller.scrollTo({
                        top: scroller.scrollTop + (targetRect.top - scrollerRect.top) - 24,
                        behavior: 'smooth'
                    });
                }
            }, 0);
            // focus textarea
            setTimeout(function () {
                var ta = document.getElementById('documentsdetail-data_json-comment');
                if (ta) { ta.focus(); }
            }, 200);
        }
    });
});

\$('body').on('click', '.delete-comment', function (e) {
    e.preventDefault();
    var url = \$(this).attr('href');
    Swal.fire({
        title: 'ยืนยัน',
        text: 'ต้องการลบหรือไม่',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'ใช่, ยืนยัน!',
        cancelButtonText: 'ยกเลิก',
    }).then(function (result) {
        if (result.isConfirmed) {
            \$.ajax({
                type: 'post',
                url: url,
                dataType: 'json',
                success: function (res) {
                    if (res.status === 'success') {
                        if (typeof listComment === 'function') { listComment(); }
                        if (typeof getComment === 'function') { getComment(); }
                    }
                }
            });
        }
    });
});

\$('body').off('click', '.text-template').on('click', '.text-template', function (e) {
    e.preventDefault();
    var textDist = \$('#documentsdetail-data_json-comment');
    var text = \$(this).text().trim();
    textDist.val(function (i, oldVal) { return oldVal + ' ' + text; });
    textDist.focus();
    if (typeof updateCharCount === 'function') updateCharCount();
});

// ใช้ closure เก็บ selectedText ให้ทั้ง mouseup + click handler เข้าถึงได้
// (delegation จำเป็นเพราะปุ่ม #btn-save-temp-now อยู่ใน composer ที่โหลดผ่าน AJAX)
var __selectedTextForTemplate = '';
\$(document).off('mouseup.tplSel keyup.tplSel').on('mouseup.tplSel keyup.tplSel', '#documentsdetail-data_json-comment', function () {
    var el = \$(this);
    var start = el.prop('selectionStart');
    var end = el.prop('selectionEnd');
    __selectedTextForTemplate = (el.val() || '').substring(start, end).trim();
    var \$btn = \$('#btn-save-temp-now');
    if (__selectedTextForTemplate.length > 0) { \$btn.show(); } else { \$btn.hide(); }
});

\$(document).off('click.tplSave').on('click.tplSave', '#btn-save-temp-now', function (e) {
    e.preventDefault();
    if (!__selectedTextForTemplate) { return; }
    var \$btn = \$(this);
    \$btn.prop('disabled', true);
    \$.ajax({
        url: '$saveCommentTemplate',
        type: 'POST',
        data: { text: __selectedTextForTemplate },
        success: function () {
            if (typeof listCommentTemplate === 'function') { listCommentTemplate(); }
            \$btn.hide();
            __selectedTextForTemplate = '';
            if (typeof success === 'function') { success('บันทึกแม่แบบเรียบร้อย'); }
        },
        error: function () {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'ไม่สำเร็จ', text: 'ไม่สามารถบันทึกแม่แบบได้' });
            }
        },
        complete: function () { \$btn.prop('disabled', false); }
    });
});

function listCommentTemplate() {
    \$.ajax({
        type: 'get',
        url: '/me/documents/list-comment-template',
        dataType: 'json',
        success: function (res) {
            if (res.totalCount !== undefined) { \$('#counttemplate').html(res.totalCount); }
            \$('#viewlistCommenttemplate').html(res.content);
        }
    });
}

function updateCharCount() {
    var textArea = \$('#documentsdetail-data_json-comment');
    if (textArea.length > 0) {
        var content = textArea.val();
        var len = content ? content.length : 0;
        \$('#char-count').html(len + ' ตัวอักษร');
        if (len > 0) {
            \$('#char-count').removeClass('opacity-50').addClass('opacity-100');
        } else {
            \$('#char-count').removeClass('opacity-100').addClass('opacity-50');
        }
    }
}

\$(document).on('input keyup', '#documentsdetail-data_json-comment', updateCharCount);

\$(document).off('click.bookmarkToggle').on('click.bookmarkToggle', '.bookmark-toggle', function (e) {
    e.preventDefault();
    var \$btn = \$(this);
    if (\$btn.prop('disabled')) { return; }
    \$btn.prop('disabled', true);
    \$.ajax({
        type: 'get',
        url: \$btn.attr('href'),
        dataType: 'json',
        success: function (res) {
            if (res && res.status === 'success' && res.data) {
                var newState = res.data.bookmark === 'Y' ? 'Y' : 'N';
                \$btn.data('state', newState).attr('data-state', newState);
                if (newState === 'Y') {
                    \$btn.removeClass('is-off').addClass('is-on');
                    \$btn.find('.bookmark-label').text('ปักหมุดแล้ว');
                    \$btn.attr('title', 'ยกเลิกปักหมุด').attr('data-bs-original-title', 'ยกเลิกปักหมุด');
                    if (typeof success === 'function') { success('ปักหมุดเอกสารแล้ว'); }
                } else {
                    \$btn.removeClass('is-on').addClass('is-off');
                    \$btn.find('.bookmark-label').text('ปักหมุด');
                    \$btn.attr('title', 'ปักหมุดเอกสาร').attr('data-bs-original-title', 'ปักหมุดเอกสาร');
                    if (typeof success === 'function') { success('ยกเลิกปักหมุดแล้ว'); }
                }
                if (window.bootstrap && bootstrap.Tooltip) {
                    var tip = bootstrap.Tooltip.getInstance(\$btn[0]);
                    if (tip) { tip.dispose(); }
                    bootstrap.Tooltip.getOrCreateInstance(\$btn[0]);
                }
            }
        },
        error: function () {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'ไม่สำเร็จ', text: 'ไม่สามารถปักหมุดได้' });
            }
        },
        complete: function () { \$btn.prop('disabled', false); }
    });
});

\$(document).on('click', '.btn-delete-action', function (e) {
    e.preventDefault();
    var btnDelete = \$(this);
    var templateId = btnDelete.data('id');
    Swal.fire({
        title: 'ยืนยันการลบ?',
        text: 'คุณต้องการลบแม่แบบนี้ใช่หรือไม่?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'ใช่, ลบเลย!',
        cancelButtonText: 'ยกเลิก',
    }).then(function (result) {
        if (result.isConfirmed) {
            \$.ajax({
                url: '/me/documents/delete-comment-template',
                type: 'POST',
                data: { id: templateId },
                success: function (res) {
                    if (res.status === 'success') {
                        listCommentTemplate();
                        btnDelete.closest('.template-item, li, .template-wrapper').fadeOut(300, function () { \$(this).remove(); });
                        Swal.fire({ title: 'ลบสำเร็จ!', icon: 'success', timer: 1500, showConfirmButton: false });
                    }
                },
                error: function () { Swal.fire('เกิดข้อผิดพลาด!', 'ไม่สามารถลบข้อมูลได้', 'error'); }
            });
        }
    });
});
JS;
$this->registerJS($js, View::POS_END);
?>
