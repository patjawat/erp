<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use app\models\Uploads;
use app\components\UserHelper;
use app\modules\hr\models\Organization;
use app\modules\dms\models\DocumentsDetail;

// รายชื่อหน่วยงานที่เอกสารนี้ "ส่งถึง" (รวม forwarding ทั้ง direct + จาก comment) แบบ unique
$forwardedDeptIds = DocumentsDetail::find()
    ->select('to_id')
    ->where(['document_id' => $model->id])
    ->andWhere(['in', 'name', ['department', 'comment_dept']])
    ->andWhere(['not', ['to_id' => null]])
    ->andWhere(['<>', 'to_id', ''])
    ->distinct()
    ->column();
$forwardedDepts = [];
if (!empty($forwardedDeptIds)) {
    $forwardedDepts = Organization::find()
        ->where(['id' => $forwardedDeptIds])
        ->orderBy(['root' => SORT_ASC, 'lft' => SORT_ASC])
        ->all();
}

// ผู้อ่านเอกสาร (สำหรับ summary + offcanvas)
$readers = $model->viewHistory();

// นับไฟล์แนบ (เพื่อโชว์จำนวนใน header)
$attachmentCount = (int) Uploads::find()->where(['ref' => $model->ref, 'name' => 'document_clip'])->count();

/** @var yii\web\View $this */
/** @var app\modules\dms\models\Documents $model */

$this->title = $model->topic;
\yii\web\YiiAsset::register($this);

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
?>

<div class="container-fluid p-0">
    <div class="d-lg-none position-sticky top-0 bg-white border-bottom shadow-sm" style="z-index:1030;">
        <div class="d-flex p-2 gap-2" id="mobile-pane-toggle">
            <button type="button" class="btn btn-primary rounded-pill flex-fill py-2 small fw-semibold" data-target-pane="pdf">
                <i class="fa-regular fa-file-lines me-1"></i> เอกสาร
            </button>
            <button type="button" class="btn btn-light text-secondary rounded-pill flex-fill py-2 small fw-semibold" data-target-pane="work">
                <i class="fa-regular fa-comments me-1"></i> รายละเอียด
                <span class="badge text-bg-light text-muted ms-1" id="mobile-badge-count" style="display:none;">0</span>
            </button>
        </div>
    </div>

    <div class="row g-0" id="doc-split-pane">

        <div class="col-12 col-lg-6 bg-body-secondary" id="doc-pdf-pane" data-pane="pdf">
            <div id="iframeWrapper" class="w-100 h-100">
                <iframe id="myIframe"
                    src="<?= Url::to(['/me/documents/show', 'ref' => $model->ref]) ?>#view=FitH&toolbar=1&navpanes=0"
                    class="w-100 h-100 border-0 d-block bg-white">
                </iframe>
            </div>
        </div>

        <div class="col-12 col-lg-6 bg-body-tertiary" id="doc-work-pane" data-pane="work">
            <div class="d-flex flex-column h-100">

                <div class="bg-white border-bottom border-light-subtle px-4 py-3 flex-shrink-0">
                    <div class="d-flex align-items-start gap-3">
                        <span class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-3 flex-shrink-0" style="width:44px;height:44px;">
                            <i class="fa-regular fa-file-lines fs-5"></i>
                        </span>
                        <div class="flex-grow-1 min-width-0">
                            <div class="text-uppercase small text-primary fw-semibold opacity-75" style="letter-spacing:.05em;">
                                <?= Html::encode($model->documentOrg->title ?? '-') ?>
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
                            </div>
                        </div>
                        <div class="d-flex flex-column gap-1 flex-shrink-0">
                            <?php if ($model->doc_speed === 'ด่วนที่สุด'): ?>
                                <span class="badge text-bg-danger rounded-pill"><i class="fa-solid fa-circle-exclamation me-1"></i>ด่วนที่สุด</span>
                            <?php endif; ?>
                            <?php if ($model->secret === 'ลับที่สุด'): ?>
                                <span class="badge text-bg-dark rounded-pill"><i class="fa-solid fa-lock me-1"></i>ลับที่สุด</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mt-3 pt-3 border-top border-light-subtle d-flex flex-column gap-2">
                        <div id="forwarded-summary-wrapper">
                            <?= $this->render('_forwarded_summary', ['forwardedDepts' => $forwardedDepts]) ?>
                        </div>
                        <div id="read-summary-wrapper">
                            <?= $this->render('_read_summary', ['readers' => $readers]) ?>
                        </div>
                    </div>
                </div>

                <div class="flex-grow-1 overflow-auto p-4">
                    <div class="d-flex flex-column gap-4">

                        <div id="approval-card-wrapper">
                            <?= $this->render('req_approve_tags', ['model' => $model]) ?>
                        </div>

                        <div id="forwarding-card-wrapper">
                            <?= $this->render('_forwarding_card', [
                                'model' => $model,
                                'canManageDepartmentExtra' => $canManageDepartmentExtra,
                                'currentDeptIdsStr' => $currentDeptIdsStr,
                            ]) ?>
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

<?php
$getCommentUrl = Url::to(['/me/documents/comment', 'id' => $model->id]);
$saveCommentTemplate = Url::to(['/me/documents/save-comment-template']);
$forwardingCardUrl = Url::to(['/dms/documents/forwarding-card', 'id' => $model->id]);
$js = <<<JS
(function () {
    $('[data-bs-toggle="popover"]').popover();
    $('[data-bs-toggle="tooltip"]').tooltip();
    updateCharCount();

    function updateIframeHeight() {
        var iframe = document.getElementById('myIframe');
        var splitPane = document.getElementById('doc-split-pane');
        var workPane = document.getElementById('doc-work-pane');
        var pdfPane = document.getElementById('doc-pdf-pane');
        var wrapper = document.getElementById('iframeWrapper');
        if (!iframe || !splitPane) return;
        var winHeight = window.innerHeight;
        var winWidth = window.innerWidth;
        if (winWidth < 992) {
            // mobile/tablet: pane เดียวต่อหน้าจอ — เติมเต็ม viewport (หัก toggle bar)
            var toggleBar = document.querySelector('.d-lg-none.position-sticky');
            var toggleH = toggleBar ? toggleBar.offsetHeight : 0;
            var available = Math.max(400, winHeight - toggleH);
            splitPane.style.height = '';
            if (pdfPane) { pdfPane.style.height = available + 'px'; }
            if (workPane) { workPane.style.height = available + 'px'; }
            if (wrapper) { wrapper.style.height = available + 'px'; }
            iframe.style.height = available + 'px';
        } else {
            // desktop: split-view เต็ม viewport
            var offsetTop = splitPane.getBoundingClientRect().top + window.scrollY;
            var available = Math.max(500, winHeight - offsetTop);
            splitPane.style.height = available + 'px';
            if (pdfPane) { pdfPane.style.height = ''; }
            if (workPane) { workPane.style.height = available + 'px'; }
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

async function getComment() {
    await \$.ajax({
        type: 'get',
        url: '$getCommentUrl',
        dataType: 'json',
        success: function (res) {
            \$('.viewFormComment').html(res.content);
            // composer มี #viewlistCommenttemplate อยู่ภายใน → load รายการ template หลัง composer มา
            if (typeof listCommentTemplate === 'function') { listCommentTemplate(); }
        }
    });
}

function reloadTimeline() {
    var \$cardWrap = \$('#forwarding-card-wrapper');
    var \$summaryWrap = \$('#forwarded-summary-wrapper');
    if (!\$cardWrap.length) { return \$.Deferred().resolve().promise(); }
    return \$.ajax({
        url: '$forwardingCardUrl',
        type: 'get',
        cache: false,
        dataType: 'json'
    }).then(function (res) {
        if (res && res.card) { \$cardWrap.html(res.card); }
        if (res && res.summary && \$summaryWrap.length) { \$summaryWrap.html(res.summary); }
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
        success: function (res) {
            \$('.viewFormComment').html(res.content);
            // scroll work pane down to composer
            var workPane = document.getElementById('doc-work-pane');
            var composerEl = document.querySelector('.viewFormComment');
            if (workPane && composerEl) {
                var scroller = workPane.querySelector('.overflow-auto') || workPane;
                var rect = composerEl.getBoundingClientRect();
                var scrollerRect = scroller.getBoundingClientRect();
                scroller.scrollTo({
                    top: scroller.scrollTop + (rect.top - scrollerRect.top) - 24,
                    behavior: 'smooth'
                });
            }
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
                success: function (res) { if (res.status === 'success') { reloadTimeline(); } }
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
