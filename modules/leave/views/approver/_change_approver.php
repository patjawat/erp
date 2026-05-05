<?php

use kartik\widgets\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;

/** @var yii\web\View $this */
/** @var app\modules\leave\models\Leave $leave */
/** @var app\modules\approveV2\models\Approve[] $approves */

$searchEmpUrl = Url::to(['/leave/leave/search-employee']);
$saveUrl      = Url::to(['/leave/approver/change-approver', 'id' => $leave->id]);
$csrfParam    = Yii::$app->request->csrfParam;
$csrfToken    = Yii::$app->request->csrfToken;

$statusOptions = [
    'Pending' => 'รอดำเนินการ',
    'Pass'    => 'อนุมัติ',
    'Reject'  => 'ไม่อนุมัติ',
    'None'    => 'None',
];
$statusBadge = [
    'Pending' => 'bg-warning text-dark',
    'Pass'    => 'bg-success text-white',
    'Reject'  => 'bg-danger text-white',
    'None'    => 'bg-secondary text-white',
];
?>

<div class="px-1">
    <!-- ข้อมูลใบลา -->
    <div class="d-flex align-items-center gap-3 p-3 rounded-3 bg-body-secondary bg-opacity-50 mb-4">
        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width:40px;height:40px;">
            <i class="bi bi-calendar-check"></i>
        </div>
        <div>
            <div class="fw-semibold text-body">
                <?= $leave->employee ? Html::encode($leave->employee->fullname ?? '') : '-' ?>
            </div>
            <div class="small text-muted">
                <?= $leave->leaveType ? Html::encode($leave->leaveType->title) : '-' ?>
                &nbsp;·&nbsp;
                <?= $leave->showLeaveDate() ?>
                &nbsp;·&nbsp;
                <span class="fw-medium text-primary"><?= (float) $leave->total_days ?> วัน</span>
            </div>
        </div>
        <div class="ms-auto">
            <?= $leave->viewStatus() ?>
        </div>
    </div>

    <?php if (empty($approves)): ?>
        <div class="text-center text-muted py-4">
            <i class="bi bi-person-x display-6 d-block mb-2 opacity-50"></i>
            <small>ไม่มีรายการผู้อนุมัติ</small>
        </div>
    <?php else: ?>

    <div class="d-flex flex-column gap-3" id="change-approver-list">
        <?php foreach ($approves as $ap): ?>
            <?php
            $currentEmp = $ap->employee ?? null;
            $badgeClass = $statusBadge[$ap->status] ?? 'bg-secondary text-white';
            $inputId    = 'approver-select-' . $ap->id;
            $statusId   = 'status-select-' . $ap->id;
            $initText   = $currentEmp ? Html::encode($currentEmp->fullname ?? '') : '';
            ?>
            <div class="card border rounded-3 shadow-sm" data-approve-id="<?= $ap->id ?>" data-level="<?= (int) $ap->level ?>">
                <div class="card-body p-3">

                    <!-- header: ลำดับ + title -->
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary-subtle fw-semibold px-2 py-1">
                            ลำดับ <?= (int) $ap->level ?>
                        </span>
                        <span class="small fw-semibold text-body"><?= Html::encode($ap->title ?? 'ผู้อนุมัติ') ?></span>
                        <span class="badge <?= $badgeClass ?> small px-2 py-1 rounded-pill ms-auto current-status-badge">
                            <?= Html::encode($statusOptions[$ap->status] ?? $ap->status) ?>
                        </span>
                    </div>

                    <div class="row g-3">
                        <!-- เลือกผู้อนุมัติ -->
                        <div class="col-md-7">
                            <label class="form-label small text-muted mb-1">
                                <i class="bi bi-person me-1"></i> ผู้อนุมัติ
                            </label>
                            <?= Select2::widget([
                                'id'            => $inputId,
                                'name'          => 'approves[' . $ap->id . '][emp_id]',
                                'value'         => $ap->emp_id,
                                'initValueText' => $initText,
                                'options'       => ['placeholder' => 'พิมพ์ชื่อเพื่อค้นหา...', 'class' => 'form-control'],
                                'pluginOptions' => [
                                    'allowClear'         => true,
                                    'minimumInputLength' => 1,
                                    'dropdownParent'     => '#main-modal',
                                    'ajax'               => [
                                        'url'            => $searchEmpUrl,
                                        'dataType'       => 'json',
                                        'delay'          => 250,
                                        'data'           => new JsExpression('function(params){ return {q:params.term}; }'),
                                        'processResults' => new JsExpression('function(data){ return {results: data.results}; }'),
                                        'cache'          => true,
                                    ],
                                    'escapeMarkup'       => new JsExpression('function(m){ return m; }'),
                                    'templateResult'     => new JsExpression('function(r){ return r.text||r.id; }'),
                                    'templateSelection'  => new JsExpression('function(r){ return r.text||r.id; }'),
                                ],
                            ]) ?>
                        </div>

                        <!-- เลือกสถานะ -->
                        <div class="col-md-5">
                            <label class="form-label small text-muted mb-1">
                                <i class="bi bi-flag me-1"></i> สถานะ
                            </label>
                            <select id="<?= $statusId ?>"
                                    name="approves[<?= $ap->id ?>][status]"
                                    class="form-select status-select"
                                    data-approve-id="<?= $ap->id ?>">
                                <?php foreach ($statusOptions as $val => $label): ?>
                                    <option value="<?= $val ?>" <?= $ap->status === $val ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="alert alert-warning border-0 rounded-3 small mt-3 d-flex align-items-start gap-2 py-2 px-3">
        <i class="bi bi-exclamation-triangle-fill text-warning mt-1 flex-shrink-0"></i>
        <span>สถานะของใบลาจะถูกคำนวณใหม่อัตโนมัติตามสถานะผู้อนุมัติ</span>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-3">
        <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">ยกเลิก</button>
        <button type="button" class="btn btn-primary rounded-3 px-4" id="btn-save-approver">
            <i class="bi bi-check2-circle me-1"></i> บันทึก
        </button>
    </div>

    <?php endif; ?>
</div>

<?php
$statusBadgeJs = json_encode([
    'Pending' => ['label' => 'รอดำเนินการ', 'class' => 'bg-warning text-dark'],
    'Pass'    => ['label' => 'อนุมัติ',      'class' => 'bg-success text-white'],
    'Reject'  => ['label' => 'ไม่อนุมัติ',   'class' => 'bg-danger text-white'],
    'None'    => ['label' => 'ไม่ใช้งาน',    'class' => 'bg-secondary text-white'],
]);
$js = <<<JS
(function(){
    var saveUrl   = "{$saveUrl}";
    var csrfParam = "{$csrfParam}";
    var csrfToken = "{$csrfToken}";
    var statusMap = {$statusBadgeJs};

    function updateBadge(card, statusVal) {
        var badge = card ? card.querySelector('.current-status-badge') : null;
        var info  = statusMap[statusVal];
        if (badge && info) {
            badge.className = 'badge small px-2 py-1 rounded-pill ms-auto current-status-badge ' + info.class;
            badge.textContent = info.label;
        }
    }

    document.querySelectorAll('.status-select').forEach(function(sel){
        sel.addEventListener('change', function(){
            updateBadge(this.closest('[data-approve-id]'), this.value);
        });
    });

    document.getElementById('btn-save-approver')?.addEventListener('click', function(){
        var cards = document.querySelectorAll('#change-approver-list [data-approve-id]');
        var fd = new FormData();
        fd.append(csrfParam, csrfToken);

        cards.forEach(function(card){
            var id      = card.dataset.approveId;
            var empSel  = card.querySelector('select[name="approves[' + id + '][emp_id]"]');
            var statSel = card.querySelector('select[name="approves[' + id + '][status]"]');
            if (empSel  && empSel.value)  fd.append('approves[' + id + '][emp_id]',  empSel.value);
            if (statSel && statSel.value) fd.append('approves[' + id + '][status]', statSel.value);
        });

        if (typeof Swal !== 'undefined') {
            Swal.fire({ title: 'ยืนยันการบันทึก?', icon: 'question', showCancelButton: true, confirmButtonText: 'บันทึก', cancelButtonText: 'ยกเลิก' })
            .then(function(r){ if (r.isConfirmed) doSave(fd); });
        } else {
            doSave(fd);
        }
    });

    function doSave(fd) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({ title: 'กำลังบันทึก...', allowOutsideClick: false, didOpen: function(){ Swal.showLoading(); } });
        }
        fetch(saveUrl, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r){ return r.json(); })
            .then(function(res){
                if (res.status === 'success') {
                    if (document.getElementById('main-modal')) jQuery('#main-modal').modal('hide');
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'success', title: 'บันทึกสำเร็จ', text: res.leave_status_label || '', showConfirmButton: false, timer: 1800 })
                            .then(function(){ location.reload(); });
                    } else {
                        location.reload();
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', html: res.message || 'บันทึกไม่สำเร็จ' });
                    }
                }
            })
            .catch(function(){
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', text: 'เกิดข้อผิดพลาดในการเชื่อมต่อ' });
                }
            });
    }
})();
JS;
$this->registerJs($js, \yii\web\View::POS_END);
?>
