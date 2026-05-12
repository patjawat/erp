<?php

use kartik\widgets\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/** @var yii\web\View $this */
/** @var app\modules\leave\models\Leave[] $leaves */

$searchEmpUrl = Url::to(['/leave/leave/search-employee']);
$saveUrl      = Url::to(['/leave/approver/change-approver-on-change']);
$csrfParam    = Yii::$app->request->csrfParam;
$csrfToken    = Yii::$app->request->csrfToken;
$leaveIds     = array_map(static function ($leave) {
    return (int) $leave->id;
}, $leaves);
$leaveIdsJson = json_encode($leaveIds);
?>

<div class="px-1">
    <?php if (!empty($leaves)): ?>
        <div class="alert alert-info border-0 rounded-3 small d-flex align-items-start gap-2 py-2 px-3 mb-3">
            <i class="bi bi-info-circle-fill flex-shrink-0 mt-1"></i>
            <span>คุณเลือกใบลา <strong><?= count($leaves) ?></strong> รายการ</span>
        </div>

        <div class="mb-3">
            <label class="form-label small fw-semibold">
                <i class="bi bi-list-check me-1"></i> รายชื่อที่เลือก
            </label>
            <div class="d-grid gap-2" style="max-height: 280px; overflow:auto;">
                <?php foreach ($leaves as $index => $leave): ?>
                    <div class="border rounded-3 p-2 bg-body-secondary bg-opacity-50">
                        <div class="d-flex align-items-start gap-2">
                            <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary-subtle flex-shrink-0 mt-1">
                                <?= $index + 1 ?>
                            </span>
                            <div class="flex-grow-1">
                                <div class="fw-semibold text-body">
                                    <?= $leave->employee ? Html::encode($leave->employee->fullname ?? '') : '-' ?>
                                </div>
                                <div class="small text-muted">
                                    <?= $leave->leaveType ? Html::encode($leave->leaveType->title) : '-' ?>
                                    &nbsp;·&nbsp;
                                    <?= Html::encode(trim(strip_tags((string) $leave->showLeaveDate()))) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label small fw-semibold">
                <i class="bi bi-person me-1"></i> ผู้อนุมัติใหม่ (เว้นว่างถ้าไม่เปลี่ยน)
            </label>
            <select id="bulk-emp-select" class="form-control" style="width:100%;" placeholder="พิมพ์ชื่อเพื่อค้นหา...">
                <option value=""></option>
            </select>
        </div>

        <div class="alert alert-warning border-0 rounded-3 small d-flex align-items-start gap-2 py-2 px-3 mb-0">
            <i class="bi bi-shield-check flex-shrink-0 mt-1"></i>
            <span>ระบบจะอัปเดตผู้อนุมัติชั้นที่ 4 ของรายการที่เลือก และผ่านชั้นที่ 3 ตาม flow ปกติ</span>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-3">
            <button type="button" class="btn btn-primary rounded-3 px-4" id="btn-bulk-approver-save">
                <i class="bi bi-check2-circle me-1"></i> บันทึกการเปลี่ยนแปลง
            </button>
            <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">ยกเลิก</button>
        </div>
    <?php else: ?>
        <div class="alert alert-warning border-0 rounded-3 mb-0">กรุณาเลือกรายการอย่างน้อย 1 รายการ</div>
    <?php endif; ?>
</div>

<?php
$js = <<<JS
(function () {
    var saveUrl   = "{$saveUrl}";
    var csrfParam = "{$csrfParam}";
    var csrfToken = "{$csrfToken}";
    var leaveIds   = {$leaveIdsJson};

    if (!Array.isArray(leaveIds) || leaveIds.length === 0) {
        return;
    }

    if (typeof jQuery !== 'undefined' && jQuery('#bulk-emp-select').length) {
        jQuery('#bulk-emp-select').select2({
            dropdownParent: jQuery('#main-modal'),
            placeholder: 'พิมพ์ชื่อเพื่อค้นหา...',
            allowClear: true,
            minimumInputLength: 1,
            ajax: {
                url: "{$searchEmpUrl}",
                dataType: 'json',
                delay: 250,
                data: function (params) { return { q: params.term }; },
                processResults: function (data) { return { results: data.results }; },
                cache: true
            },
            escapeMarkup: function (m) { return m; },
            templateResult: function (r) { return r.text || r.id; },
            templateSelection: function (r) { return r.text || r.id; }
        });
    }

    document.getElementById('btn-bulk-approver-save')?.addEventListener('click', function () {
        var empId = typeof jQuery !== 'undefined' ? (jQuery('#bulk-emp-select').val() || '') : '';

        if (!empId) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'warning', text: 'กรุณาเลือกผู้อนุมัติใหม่' });
            }
            return;
        }

        var fd = new FormData();
        fd.append(csrfParam, csrfToken);
        leaveIds.forEach(function (id) {
            fd.append('leave_ids[]', id);
        });
        fd.append('emp_id', empId);

        function doBulkChangeApprover() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ title: 'กำลังบันทึก...', allowOutsideClick: false, didOpen: function () { Swal.showLoading(); } });
            }

            fetch(saveUrl, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    if (res.status === 'success') {
                        var modalEl = document.getElementById('main-modal');
                        if (modalEl) {
                            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                                bootstrap.Modal.getInstance(modalEl)?.hide();
                            } else if (typeof jQuery !== 'undefined') {
                                jQuery('#main-modal').modal('hide');
                            }
                        }

                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'สำเร็จ',
                                text: res.message || 'เปลี่ยนผู้อนุมัติสำเร็จ',
                                showConfirmButton: false,
                                timer: 1600
                            }).then(function () { location.reload(); });
                        } else {
                            location.reload();
                        }
                    } else {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', html: res.message || 'เปลี่ยนผู้อนุมัติไม่สำเร็จ' });
                        }
                    }
                })
                .catch(function () {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', text: 'เกิดข้อผิดพลาดในการเชื่อมต่อ' });
                    }
                });
        }

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'ยืนยันการเปลี่ยนผู้อนุมัติ?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'บันทึก',
                cancelButtonText: 'ยกเลิก'
            }).then(function (r) {
                if (r.isConfirmed) {
                    doBulkChangeApprover();
                }
            });
        } else {
            doBulkChangeApprover();
        }
    });
})();
JS;
$this->registerJs($js, View::POS_END);
?>
