<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap5\ActiveForm;

/** @var array $draft */

$this->title = 'บันทึกข้อความขอไปราชการ';
$this->params['breadcrumbs'] = [];
$rows = $draft['class_change_rows'] ?? [];
if (empty($rows)) {
    $rows = [['day_time' => '', 'period' => '', 'subject_class' => '']];
}
?>
<div class="travel-request-wizard">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="<?= Url::to(['/development/travel-request/index']) ?>" class="btn btn-link text-decoration-none text-body p-0">
            <i class="bi bi-arrow-left me-1"></i><?= Html::encode($this->title) ?>
        </a>
        <span class="text-muted small">Step 2 of 4</span>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4">
            <h5 class="fw-bold text-body mb-4">2. รายละเอียดเพิ่มเติม</h5>

            <?php $form = ActiveForm::begin([
                'id' => 'travel-step2-form',
                'action' => ['step2'],
                'method' => 'post',
            ]); ?>

            <div class="row">
                <div class="col-lg-6 border-end">
                    <h6 class="fw-semibold text-body mb-3">สิ่งที่ส่งมาด้วย</h6>
                    <div class="form-check mb-2">
                        <?= Html::checkbox('attach_invitation', !empty($draft['attach_invitation']), ['class' => 'form-check-input', 'id' => 'attach_invitation']) ?>
                        <label class="form-check-label" for="attach_invitation">หนังสือราชการ / บันทึกข้อความเชิญ</label>
                    </div>
                    <div class="form-check mb-2">
                        <?= Html::checkbox('attach_class_change', !empty($draft['attach_class_change']), ['class' => 'form-check-input', 'id' => 'attach_class_change']) ?>
                        <label class="form-check-label" for="attach_class_change">แบบบันทึกการขอเปลี่ยนคาบสอน/สอนแทน</label>
                    </div>
                    <div id="class-change-table" class="table-responsive mb-3 ms-3">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>วัน/เวลา</th>
                                    <th>คาบ</th>
                                    <th>วิชา/ชั้น</th>
                                </tr>
                            </thead>
                            <tbody id="class-change-tbody">
                                <?php foreach ($rows as $i => $r): ?>
                                <tr>
                                    <td><input type="text" name="class_change_rows[<?= $i ?>][day_time]" class="form-control form-control-sm" value="<?= Html::encode($r['day_time'] ?? '') ?>" placeholder="วัน/เวลา"></td>
                                    <td><input type="text" name="class_change_rows[<?= $i ?>][period]" class="form-control form-control-sm" value="<?= Html::encode($r['period'] ?? '') ?>" placeholder="คาบ"></td>
                                    <td><input type="text" name="class_change_rows[<?= $i ?>][subject_class]" class="form-control form-control-sm" value="<?= Html::encode($r['subject_class'] ?? '') ?>" placeholder="วิชา/ชั้น"></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="add-class-row">+ เพิ่มแถว</button>
                    </div>
                    <div class="form-check mb-2">
                        <?= Html::checkbox('attach_vehicle', !empty($draft['attach_vehicle']), ['class' => 'form-check-input', 'id' => 'attach_vehicle']) ?>
                        <label class="form-check-label" for="attach_vehicle">ขออนุญาตใช้รถยนต์โรงเรียน</label>
                    </div>
                    <div class="form-check mb-2">
                        <?= Html::checkbox('attach_budget', !empty($draft['attach_budget']), ['class' => 'form-check-input', 'id' => 'attach_budget']) ?>
                        <label class="form-check-label" for="attach_budget">ขอใช้งบประมาณ</label>
                    </div>
                    <div class="form-check mb-2 d-flex align-items-center gap-2">
                        <?= Html::checkbox('attach_other', !empty($draft['attach_other_text']), ['class' => 'form-check-input', 'id' => 'attach_other']) ?>
                        <label class="form-check-label" for="attach_other">อื่นๆ:</label>
                        <?= Html::textInput('attach_other_text', $draft['attach_other_text'] ?? '', ['class' => 'form-control form-control-sm flex-grow-1', 'placeholder' => 'ระบุ...', 'style' => 'max-width: 200px;']) ?>
                    </div>
                </div>
                <div class="col-lg-6 ps-lg-4">
                    <h6 class="fw-semibold text-body mb-3">เรื่องที่ขออนุมัติ</h6>
                    <div class="form-check mb-2">
                        <?= Html::checkbox('claim_travel', !empty($draft['claim_travel']), ['class' => 'form-check-input', 'id' => 'claim_travel']) ?>
                        <label class="form-check-label" for="claim_travel">ให้ข้าพเจ้า และคณะเดินทางไปราชการ ณ <?= Html::encode($draft['location'] ?? '') ?> <?= Html::encode($draft['province_name'] ?? '') ?></label>
                    </div>
                    <div class="form-check mb-2">
                        <?= Html::checkbox('claim_per_diem', !empty($draft['claim_per_diem']), ['class' => 'form-check-input', 'id' => 'claim_per_diem']) ?>
                        <label class="form-check-label" for="claim_per_diem">เบิกค่าเบี้ยเลี้ยง</label>
                    </div>
                    <div class="form-check mb-2">
                        <?= Html::checkbox('claim_transport', !empty($draft['claim_transport']), ['class' => 'form-check-input', 'id' => 'claim_transport']) ?>
                        <label class="form-check-label" for="claim_transport">เบิกค่าพาหนะ</label>
                    </div>
                    <div class="form-check mb-2">
                        <?= Html::checkbox('claim_accommodation', !empty($draft['claim_accommodation']), ['class' => 'form-check-input', 'id' => 'claim_accommodation']) ?>
                        <label class="form-check-label" for="claim_accommodation">เบิกค่าที่พัก</label>
                    </div>
                    <div class="form-check mb-2">
                        <?= Html::checkbox('claim_registration', !empty($draft['claim_registration']), ['class' => 'form-check-input', 'id' => 'claim_registration']) ?>
                        <label class="form-check-label" for="claim_registration">เบิกค่าลงทะเบียน</label>
                    </div>
                    <div class="mb-3 ms-4">
                        <?= Html::textInput('registration_amount', $draft['registration_amount'] ?? '', ['class' => 'form-control', 'id' => 'registration_amount', 'placeholder' => 'จำนวนเงิน']) ?>
                        <p id="registration-baht-text" class="small text-muted mb-0 mt-1"></p>
                    </div>
                    <div class="form-check mb-2">
                        <?= Html::checkbox('no_claim_org', !empty($draft['no_claim_org']), ['class' => 'form-check-input', 'id' => 'no_claim_org']) ?>
                        <label class="form-check-label" for="no_claim_org">ไม่เบิกต้นสังกัด</label>
                    </div>
                    <div class="form-check mb-2">
                        <?= Html::checkbox('use_official_vehicle', !empty($draft['use_official_vehicle']), ['class' => 'form-check-input', 'id' => 'use_official_vehicle']) ?>
                        <label class="form-check-label" for="use_official_vehicle">ขอใช้รถราชการ</label>
                    </div>
                    <div class="row g-2 ms-4 mb-3">
                        <div class="col-md-6">
                            <?= Html::textInput('vehicle_plate', $draft['vehicle_plate'] ?? '', ['class' => 'form-control', 'placeholder' => 'ทะเบียนรถ...']) ?>
                        </div>
                        <div class="col-md-6">
                            <?= Html::textInput('driver_name', $draft['driver_name'] ?? '', ['class' => 'form-control', 'placeholder' => 'พนักงานขับ']) ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between pt-4 mt-3 border-top">
                <?= Html::a('ย้อนกลับ', ['index'], ['class' => 'btn btn-outline-secondary rounded-3']) ?>
                <?= Html::submitButton('ถัดไป <i class="bi bi-arrow-right ms-1"></i>', ['class' => 'btn btn-primary rounded-3 px-4']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<?php
$this->registerJs(<<<'JS'
(function() {
    var rowIndex = document.querySelectorAll('#class-change-tbody tr').length;
    document.getElementById('add-class-row').onclick = function() {
        var tbody = document.getElementById('class-change-tbody');
        var tr = document.createElement('tr');
        tr.innerHTML = '<td><input type="text" name="class_change_rows[' + rowIndex + '][day_time]" class="form-control form-control-sm" placeholder="วัน/เวลา"></td>' +
            '<td><input type="text" name="class_change_rows[' + rowIndex + '][period]" class="form-control form-control-sm" placeholder="คาบ"></td>' +
            '<td><input type="text" name="class_change_rows[' + rowIndex + '][subject_class]" class="form-control form-control-sm" placeholder="วิชา/ชั้น"></td>';
        tbody.appendChild(tr);
        rowIndex++;
    };
    document.getElementById('registration_amount').oninput = function() {
        var n = parseFloat(this.value) || 0;
        document.getElementById('registration-baht-text').textContent = n > 0 ? '(' + n + ' บาท)' : '';
    };
})();
JS
);
?>
