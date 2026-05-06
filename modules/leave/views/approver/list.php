<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use kartik\widgets\Select2;
use app\components\ApproveHelper;
use app\modules\leave\models\Leave;

/** @var yii\web\View $this */
/** @var app\modules\leave\models\LeaveSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$offset        = (int) ($dataProvider->pagination->offset ?? 0);
$models        = $dataProvider->getModels();
$levelSettings = \app\modules\approveV2\models\ApproveLevelSetting::getLevelsBySystem('leave');

$searchEmpUrl  = Url::to(['/leave/leave/search-employee']);
$bulkActionUrl = Url::to(['/leave/approver/bulk-action']);
$csrfParam     = Yii::$app->request->csrfParam;
$csrfToken     = Yii::$app->request->csrfToken;
?>


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
                <th class="py-3 px-3 small">ประเภทเวร</th>
                <th class="py-3 px-3 small">เหตุผล</th>
                <th class="py-3 px-3 small">ระหว่างวันที่</th>
                <th class="py-3 px-3 small">หน่วยงาน</th>
                <th class="text-center py-3 px-3 small">เอกสารแนบ</th>
                <th class="py-3 px-3 small">ผู้อนุมัติ</th>
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
                usort($stackApproves, function ($x, $y) {
                    return (int) $y->level - (int) $x->level;
                });
                ?>
                <tr data-leave-id="<?= $item->id ?>">
                    <td class="text-center py-3 px-3">
                        <input type="checkbox" class="form-check-input chk-leave-row" value="<?= $item->id ?>">
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
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <?= Html::a(
                                        '<i class="bi bi-person-gear me-2 text-warning"></i> เปลี่ยนผู้อนุมัติ',
                                        ['/leave/approver/change-approver', 'id' => $item->id,
                                            'title' => '<i class="bi bi-person-gear me-1"></i> เปลี่ยนผู้อนุมัติ'],
                                        ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']]
                                    ) ?>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (count($models) === 0): ?>
                <tr>
                    <td colspan="13" class="text-center text-muted py-5 px-3">
                        <i class="bi bi-inbox display-5 d-block mb-2 opacity-50"></i>
                        <span class="small">ไม่มีรายการวันลา</span>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- ── Bulk change-approver modal ─────────────────────────────── -->
<div class="modal fade" id="bulk-approver-modal" tabindex="-1" aria-labelledby="bulk-approver-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-semibold" id="bulk-approver-modal-label">
                    <i class="bi bi-person-gear me-1 text-warning"></i> เปลี่ยนผู้อนุมัติ (หลายรายการ)
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-3">

                <div class="alert alert-info border-0 rounded-3 small d-flex align-items-start gap-2 py-2 px-3 mb-3">
                    <i class="bi bi-info-circle-fill flex-shrink-0 mt-1"></i>
                    <span>การเปลี่ยนแปลงจะมีผลกับใบลา <strong id="bulk-selected-count">0</strong> รายการที่เลือก</span>
                </div>

                <!-- Level -->
                <div class="mb-3">
                    <label class="form-label small fw-semibold">
                        <i class="bi bi-list-ol me-1"></i> ลำดับผู้อนุมัติ (Level)
                    </label>
                    <select id="bulk-level" class="form-select rounded-3">
                        <option value="">-- เลือกลำดับ --</option>
                        <?php foreach ($levelSettings as $ls): ?>
                            <option value="<?= (int) $ls->level ?>">
                                ลำดับ <?= (int) $ls->level ?> — <?= Html::encode($ls->title) ?>
                            </option>
                        <?php endforeach; ?>
                        <?php if (empty($levelSettings)): ?>
                            <option value="" disabled>ไม่พบการตั้งค่าลำดับ</option>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Approver Select2 -->
                <div class="mb-3">
                    <label class="form-label small fw-semibold">
                        <i class="bi bi-person me-1"></i> ผู้อนุมัติใหม่ (เว้นว่างถ้าไม่เปลี่ยน)
                    </label>
                    <select id="bulk-emp-select" class="form-control" style="width:100%;" placeholder="พิมพ์ชื่อเพื่อค้นหา...">
                        <option value=""></option>
                    </select>
                </div>

                <!-- Status -->
                <div class="mb-1">
                    <label class="form-label small fw-semibold">
                        <i class="bi bi-flag me-1"></i> สถานะ (เว้นว่างถ้าไม่เปลี่ยน)
                    </label>
                    <select id="bulk-status-select" class="form-select rounded-3">
                        <option value="">-- ไม่เปลี่ยนสถานะ --</option>
                        <option value="Pending">รอดำเนินการ</option>
                        <option value="Pass">อนุมัติ</option>
                        <option value="Reject">ไม่อนุมัติ</option>
                        <option value="None">ไม่ใช้งาน</option>
                    </select>
                </div>

            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-primary rounded-3 px-4" id="btn-bulk-approver-save">
                    <i class="bi bi-check2-circle me-1"></i> บันทึก
                </button>
                <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">ยกเลิก</button>
            </div>
        </div>
    </div>
</div>

<?php
$js = <<<JS
(function () {
    var bulkActionUrl = "{$bulkActionUrl}";
    var searchEmpUrl  = "{$searchEmpUrl}";
    var csrfParam     = "{$csrfParam}";
    var csrfToken     = "{$csrfToken}";

    // ── Init Select2 for bulk approver ───────────────────────────
    if (typeof jQuery !== 'undefined' && jQuery('#bulk-emp-select').length) {
        jQuery('#bulk-emp-select').select2({
            dropdownParent: jQuery('#bulk-approver-modal'),
            placeholder: 'พิมพ์ชื่อเพื่อค้นหา...',
            allowClear: true,
            minimumInputLength: 1,
            ajax: {
                url: searchEmpUrl,
                dataType: 'json',
                delay: 250,
                data: function (params) { return { q: params.term }; },
                processResults: function (data) { return { results: data.results }; },
                cache: true,
            },
            escapeMarkup: function (m) { return m; },
            templateResult: function (r) { return r.text || r.id; },
            templateSelection: function (r) { return r.text || r.id; },
        });
    }

    // ── Checkbox logic ────────────────────────────────────────────
    var bar   = document.getElementById('bulk-action-bar');
    var label = document.getElementById('bulk-count-label');

    function getSelectedIds() {
        return Array.from(document.querySelectorAll('.chk-leave-row:checked')).map(function (c) { return c.value; });
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
        document.querySelectorAll('.chk-leave-row').forEach(function (c) { c.checked = this.checked; }.bind(this));
        updateBar();
    });

    document.querySelectorAll('.chk-leave-row').forEach(function (c) {
        c.addEventListener('change', function () {
            var all  = document.querySelectorAll('.chk-leave-row');
            var chkd = document.querySelectorAll('.chk-leave-row:checked');
            document.getElementById('chk-select-all').indeterminate = chkd.length > 0 && chkd.length < all.length;
            document.getElementById('chk-select-all').checked = chkd.length === all.length && all.length > 0;
            updateBar();
        });
    });

    document.getElementById('btn-bulk-clear')?.addEventListener('click', function () {
        document.querySelectorAll('.chk-leave-row').forEach(function (c) { c.checked = false; });
        document.getElementById('chk-select-all').checked       = false;
        document.getElementById('chk-select-all').indeterminate = false;
        updateBar();
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

    // ── Open bulk-change-approver modal ───────────────────────────
    document.getElementById('btn-bulk-change-approver')?.addEventListener('click', function () {
        var ids = getSelectedIds();
        if (!ids.length) return;
        document.getElementById('bulk-selected-count').textContent = ids.length;
        document.getElementById('bulk-level').value = '';
        if (typeof jQuery !== 'undefined') {
            jQuery('#bulk-emp-select').val(null).trigger('change');
        }
        document.getElementById('bulk-status-select').value = '';
        var modal = new bootstrap.Modal(document.getElementById('bulk-approver-modal'));
        modal.show();
    });

    // ── Save bulk change-approver ─────────────────────────────────
    document.getElementById('btn-bulk-approver-save')?.addEventListener('click', function () {
        var ids    = getSelectedIds();
        var level  = document.getElementById('bulk-level').value.trim();
        var empId  = typeof jQuery !== 'undefined' ? (jQuery('#bulk-emp-select').val() || '') : '';
        var status = document.getElementById('bulk-status-select').value;

        if (!level || parseInt(level) < 1) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'warning', text: 'กรุณาระบุลำดับผู้อนุมัติ (Level)' });
            }
            return;
        }

        if (!empId && !status) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'warning', text: 'กรุณาระบุผู้อนุมัติใหม่หรือสถานะอย่างใดอย่างหนึ่ง' });
            }
            return;
        }

        var payload = { action: 'change-approver', leave_ids: ids, level: level };
        if (empId)  payload.emp_id  = empId;
        if (status) payload.status  = status;

        if (typeof Swal !== 'undefined') {
            Swal.fire({ title: 'ยืนยันการบันทึก?', icon: 'question', showCancelButton: true, confirmButtonText: 'บันทึก', cancelButtonText: 'ยกเลิก' })
                .then(function (r) {
                    if (r.isConfirmed) {
                        bootstrap.Modal.getInstance(document.getElementById('bulk-approver-modal'))?.hide();
                        doBulkPost(payload, 'เปลี่ยนผู้อนุมัติสำเร็จ');
                    }
                });
        } else {
            bootstrap.Modal.getInstance(document.getElementById('bulk-approver-modal'))?.hide();
            doBulkPost(payload, 'เปลี่ยนผู้อนุมัติสำเร็จ');
        }
    });

})();
JS;
$this->registerJs($js, \yii\web\View::POS_END);
?>
