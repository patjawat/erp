<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\ApproveHelper;
use app\modules\leave\models\Leave;

/** @var yii\web\View $this */
/** @var app\modules\leave\models\LeaveSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$models        = $dataProvider->getModels();
$offset        = (int) ($dataProvider->pagination->offset ?? 0);

$bulkActionUrl = Url::to(['/leave/approver/bulk-action']);
$bulkChangeApproverUrl = Url::to(['/leave/approver/bulk-change-approver', 'title' => '<i class="bi bi-person-gear me-1"></i> เปลี่ยนผู้อนุมัติ (หลายรายการ)']);
$csrfParam     = Yii::$app->request->csrfParam;
$csrfToken     = Yii::$app->request->csrfToken;
?>

<div class="row">
<div class="col-4">

<!-- ── Bulk action bar ─────────────────────────────────────────── -->
<div id="bulk-action-bar" class="d-none align-items-center gap-3 px-3 py-2 mb-3 ms-3 mt-3 ms-3 rounded-3 border bg-primary bg-opacity-10 border-primary-subtle">
    <i class="bi bi-check2-square text-primary fs-5"></i>
    <span class="fw-semibold text-primary small" id="bulk-count-label">เลือก 0 รายการ</span>

    <div class="ms-auto d-flex gap-2 align-items-center">
        <?= Html::a(
                        '<i class="bi bi-person-gear me-2"></i> เปลี่ยนผู้อนุมัติ(ผู้ปฏิบัติหน้าที่แทน ผอ.)',
                        $bulkChangeApproverUrl,
                        [
                            'id' => 'btn-bulk-change-approver',
                            'class' => 'btn btn-warning btn-sm rounded-3 open-modal',
                            'data' => [
                                'size' => 'modal-lg',
                                'base-url' => $bulkChangeApproverUrl,
                            ],
                        ]
                    ) ?>
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-3" id="btn-bulk-clear">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
</div>
    
</div>
</div>
<!-- ── Table ──────────────────────────────────────────────────── -->
<div class="table-responsive" style="min-height: 500px;">
    <table class="table table-hover align-middle mb-0" id="leave-approver-table">
        <thead class="table-light">
            <tr>
                <th class="text-center py-3 px-3" style="width:42px;">
                    <input type="checkbox" class="form-check-input" id="chk-select-all" title="เลือกทั้งหมด">
                </th>
                <th class="text-center py-3 px-3 small">ลำดับ</th>
                <th class="text-center py-3 px-3 small">ประเภทการลา</th>
                <th class="text-center">จำนวนวันลา</th>
                <th class="py-3 px-3 small">ผู้ขออนุมัติการลา</th>
                <th class="py-3 px-3 small">ประเภทบุคลากร</th>
                <th class="py-3 px-3 small">ประเภทเวร</th>
                <th class="py-3 px-3 small">เหตุผล</th>
                <th class="py-3 px-3 small">ระหว่างวันที่</th>
                <th class="py-3 px-3 small">หน่วยงาน</th>
                <th class="text-center py-3 px-3 small">เอกสารแนบ</th>
                <th class="py-3 px-3 small" style="min-width: 150px;">ผู้อนุมัติ</th>
                <th class="py-3 px-3 small">สถานะ/ความคืบหน้า</th>
                <th class="text-end py-3 px-3 small">ดำเนินการ</th>
            </tr>
        </thead>
        <tbody class="align-middle table-group-divider">
            <?php foreach ($models as $key => $item): ?>
                <?php
                $no = $offset + $key + 1;
                $attachments = $item->getAttachmentList();
                $stackApproves = $item->approves ? array_filter($item->approves, function ($a) {
                    return !in_array($a->status, ['None', 'Pending'], true);
                }) : [];
                $isLockedStatus = !in_array($item->status, ['Checkup_pass', 'Checking2_pass'], true);
                usort($stackApproves, function ($x, $y) {
                    return (int) $y->level - (int) $x->level;
                });
                ?>
                <tr
                    data-leave-id="<?= $item->id ?>"
                    data-employee-name="<?= Html::encode($item->employee ? ($item->employee->fullname ?? '') : '') ?>"
                    data-leave-type="<?= Html::encode($item->leaveType ? $item->leaveType->title : '') ?>"
                    data-leave-range="<?= Html::encode(trim(strip_tags((string) $item->showLeaveDate()))) ?>"
                >
                    <td class="text-center py-3 px-3">
                        <input
                            type="checkbox"
                            class="form-check-input chk-leave-row<?= $isLockedStatus ? ' opacity-50' : '' ?>"
                            value="<?= $item->id ?>"
                            <?= $isLockedStatus ? 'disabled aria-disabled="true" title="สถานะนี้ไม่สามารถเลือกได้"' : '' ?>
                        >
                    </td>
                    <td class="text-center py-3 px-3 text-muted small"><?= $no ?></td>
                    <td class="text-center py-3 px-3 small">
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill fw-medium px-2 py-1">
                            <?= $item->leaveType ? Html::encode($item->leaveType->title) : '-' ?>
                        </span>
                    </td>
                    <td class="text-center fw-bold">
                        <?= (float) $item->total_days ?>
                    </td>
                    <td class="py-3 px-3">
                        <a href="<?= Url::to(['/leave/leave/view', 'id' => $item->id, 'title' => '<i class="fa-solid fa-calendar-plus"></i> แก้ไขวันลา']) ?>"
                           class="open-modal text-decoration-none d-inline-flex align-items-center gap-2" data-size="modal-xl">
                            <?= $item->employee ? $item->employee->getAvatar(false) : '-' ?>
                        </a>
                    </td>
                    <td class="py-3 px-3 small"><?= $item->employee && $item->employee->employeeType ? Html::encode($item->employee->employeeType->title) : '-' ?></td>
                    <td class="py-3 px-3 small"><?= Html::encode($item->work_shift_name ?? '-') ?></td>
                    <td class="py-3 px-3">
                        <div class="small"><?= Html::encode($item->data_json['reason'] ?? '') ?></div>
                    </td>
                    <td class="py-3 px-3 small"><?= $item->showLeaveDate() ?></td>
                    <td class="py-3 px-3 small text-muted text-truncate" style="max-width: 150px;"><?= $item->employee ? Html::encode($item->employee->departmentName()) : '-' ?></td>
                    <td>
                        <?php if (!empty($attachments)): ?>
                            <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                                <?php foreach ($attachments as $att): ?>
                                <li>
                                    <a href="<?= Html::encode(Url::to(['/leave/leave/show-file', 'id' => $att->id])) ?>"
                                       class="leave-attachment-link d-inline-flex align-items-center gap-2 text-decoration-none text-body border rounded-3 px-3 py-2 bg-body-secondary bg-opacity-50"
                                       data-url="<?= Html::encode(Url::to(['/leave/leave/show-file', 'id' => $att->id])) ?>"
                                       data-open="new-tab" target="_blank" rel="noopener noreferrer">
                                        <i class="fa-solid fa-paperclip text-primary"></i>
                                    </a>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </td>
                    <td class="py-3 px-3"><?= Leave::renderStackChecker($stackApproves) ?></td>
                    <td class="py-3 px-3 small">
                        <?= $item->viewStatus() ?>
                        <?= ApproveHelper::viewStepFromSteps($item->approves ?? []) ?>
                    </td>
                    <td class="text-end py-3 px-3">
                        <div class="dropdown">
                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots-vertical"></i> ดำเนินการ
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <?= Html::a(
                                        '<i class="bi bi-eye me-2"></i> แสดง',
                                        ['/leave/leave/view', 'id' => $item->id, 'title' => '<i class="fa-solid fa-calendar-plus"></i> แก้ไขวันลา'],
                                        ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-xl']]
                                    ) ?>
                                </li>
                                <li>
                                    <?= Html::a(
                                        '<i class="bi bi-pencil me-2"></i> แก้ไข',
                                        ['/leave/leave/update', 'id' => $item->id],
                                        ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-xl']]
                                    ) ?>
                                </li>
                                <li>
                                    <?= Html::a(
                                        '<i class="bi bi-printer me-2"></i> พิมพ์ใบลา (PDF)',
                                        ['/leave/leave/pdf', 'id' => $item->id],
                                        [
                                            'class'      => 'dropdown-item',
                                            'target'     => '_blank',
                                            'rel'        => 'noopener noreferrer',
                                            'data-pjax'  => '0',
                                        ]
                                    ) ?>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (count($models) === 0): ?>
                <tr>
                    <td colspan="14" class="text-center text-muted py-5 px-3">
                        <i class="bi bi-inbox display-5 d-block mb-2 opacity-50"></i>
                        <span class="small">ไม่มีรายการวันลา</span>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
$js = <<<JS
(function () {
    var bulkActionUrl = "{$bulkActionUrl}";
    var bulkChangeApproverUrl = "{$bulkChangeApproverUrl}";
    var csrfParam     = "{$csrfParam}";
    var csrfToken     = "{$csrfToken}";

    // ── Checkbox logic ────────────────────────────────────────────
    var bar   = document.getElementById('bulk-action-bar');
    var label = document.getElementById('bulk-count-label');

    function getSelectedIds() {
        return Array.from(document.querySelectorAll('.chk-leave-row:not(:disabled):checked')).map(function (c) { return c.value; });
    }

    function getSelectableCheckboxes() {
        return Array.from(document.querySelectorAll('.chk-leave-row:not(:disabled)'));
    }

    function updateBar() {
        var ids = getSelectedIds();
        var n   = ids.length;
        if (n > 0) {
            label.textContent = 'เลือก ' + n + ' รายการ';
            bar.classList.remove('d-none');
            bar.classList.add('d-flex');
        } else {
            bar.classList.add('d-none');
            bar.classList.remove('d-flex');
        }
    }

    document.getElementById('chk-select-all')?.addEventListener('change', function () {
        getSelectableCheckboxes().forEach(function (c) { c.checked = this.checked; }.bind(this));
        updateBar();
    });

    document.querySelectorAll('.chk-leave-row').forEach(function (c) {
        c.addEventListener('change', function () {
            var all  = getSelectableCheckboxes();
            var chkd = document.querySelectorAll('.chk-leave-row:not(:disabled):checked');
            document.getElementById('chk-select-all').indeterminate = chkd.length > 0 && chkd.length < all.length;
            document.getElementById('chk-select-all').checked = chkd.length === all.length && all.length > 0;
            updateBar();
        });
    });

    document.getElementById('btn-bulk-clear')?.addEventListener('click', function () {
        getSelectableCheckboxes().forEach(function (c) { c.checked = false; });
        document.getElementById('chk-select-all').checked       = false;
        document.getElementById('chk-select-all').indeterminate = false;
        updateBar();
    });

    document.getElementById('btn-bulk-change-approver')?.addEventListener('click', function (e) {
        e.preventDefault();

        var ids = getSelectedIds();
        if (!ids.length) {
            e.stopPropagation();
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'warning', text: 'กรุณาเลือกรายการอย่างน้อย 1 รายการ' });
            }
            return;
        }

        var baseUrl = this.dataset.baseUrl || bulkChangeApproverUrl || this.getAttribute('href') || '';
        var query = ids.map(function (id) {
            return 'leave_ids[]=' + encodeURIComponent(id);
        }).join('&');
        var href = baseUrl + (baseUrl.indexOf('?') === -1 ? '?' : '&') + query;
        this.setAttribute('href', href);
    });

    // ── Helper: POST to bulk action ───────────────────────────────
    function doBulkPost(payload, successMsg) {
        var fd = new FormData();
        fd.append(csrfParam, csrfToken);
        Object.keys(payload).forEach(function (k) {
            var v = payload[k];
            if (Array.isArray(v)) {
                v.forEach(function (item) { fd.append(k + '[]', item); });
            } else {
                fd.append(k, v);
            }
        });

        if (typeof Swal !== 'undefined') {
            Swal.fire({ title: 'กำลังดำเนินการ...', allowOutsideClick: false, didOpen: function () { Swal.showLoading(); } });
        }

        fetch(bulkActionUrl, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.status === 'success') {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'success', title: 'สำเร็จ', text: res.message || successMsg, showConfirmButton: false, timer: 1600 })
                            .then(function () { location.reload(); });
                    } else {
                        location.reload();
                    }
                } else if (res.status === 'partial') {
                    // ดำเนินการได้บางส่วน — บางรายการยังไม่ถูกเห็นชอบ
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'warning', title: 'ดำเนินการบางส่วน', html: res.message, confirmButtonText: 'ตกลง' })
                            .then(function () { location.reload(); });
                    } else {
                        location.reload();
                    }
                } else if (res.status === 'warning') {
                    // ไม่ผ่านเงื่อนไขทั้งหมด — ไม่มีการเปลี่ยนแปลง
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'warning', title: 'ข้อมูลยังไม่ถูกเห็นชอบ', html: res.message, confirmButtonText: 'ตกลง' });
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', html: res.message || 'ดำเนินการไม่สำเร็จ' });
                    }
                }
            })
            .catch(function () {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', text: 'เกิดข้อผิดพลาดในการเชื่อมต่อ' });
                }
            });
    }

    // ── Bulk approve ──────────────────────────────────────────────
    document.getElementById('btn-bulk-approve')?.addEventListener('click', function () {
        var ids = getSelectedIds();
        if (!ids.length) return;
        if (typeof Swal !== 'undefined') {
            Swal.fire({ title: 'อนุมัติ ' + ids.length + ' รายการ?', icon: 'question', showCancelButton: true, confirmButtonText: 'อนุมัติ', cancelButtonText: 'ยกเลิก' })
                .then(function (r) { if (r.isConfirmed) doBulkPost({ action: 'approve', leave_ids: ids }, 'อนุมัติสำเร็จ'); });
        } else {
            doBulkPost({ action: 'approve', leave_ids: ids }, 'อนุมัติสำเร็จ');
        }
    });

    // ── Bulk reject ───────────────────────────────────────────────
    document.getElementById('btn-bulk-reject')?.addEventListener('click', function () {
        var ids = getSelectedIds();
        if (!ids.length) return;
        if (typeof Swal !== 'undefined') {
            Swal.fire({ title: 'ไม่อนุมัติ ' + ids.length + ' รายการ?', icon: 'warning', showCancelButton: true, confirmButtonText: 'ยืนยัน', cancelButtonText: 'ยกเลิก', confirmButtonColor: '#dc3545' })
                .then(function (r) { if (r.isConfirmed) doBulkPost({ action: 'reject', leave_ids: ids }, 'ไม่อนุมัติสำเร็จ'); });
        } else {
            doBulkPost({ action: 'reject', leave_ids: ids }, 'ไม่อนุมัติสำเร็จ');
        }
    });

    updateBar();

})();
JS;
$this->registerJs($js, \yii\web\View::POS_END);
?>
