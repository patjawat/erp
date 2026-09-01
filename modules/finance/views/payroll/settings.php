<?php

use yii\helpers\Html;

$this->title = 'ตั้งค่ารายการเงินเดือน';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'เงินเดือน', 'url' => ['/finance/payroll']];
$this->params['breadcrumbs'][] = $this->title;
$directionLabels = ['earning' => 'รายการรับ', 'deduction' => 'รายการหัก', 'employer_contribution' => 'เงินสมทบนายจ้าง'];
$groupLabels = ['monthly_pay' => 'เงินรายเดือน', 'compensation' => 'ค่าตอบแทน', 'deduction' => 'รายการหัก', 'employer_contribution' => 'เงินสมทบนายจ้าง'];

$this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2"><i class="bi bi-sliders fs-4" aria-hidden="true"></i><h4 class="mb-0"><?= Html::encode($this->title) ?></h4></div>
<?php $this->endBlock();
$this->beginBlock('sub-title'); ?>กำหนดรายการที่ใช้ซ้ำและกฎตามช่วงวันที่ก่อนนำไปคำนวณในแต่ละรอบ<?php $this->endBlock();
$this->beginBlock('page-action'); echo $this->render('@app/modules/finance/menu', ['active' => 'payroll']); $this->endBlock();
?>

<?= $this->render('_menu', ['active' => 'items']) ?>

<div aria-live="polite" aria-atomic="true">
    <?php foreach (['success' => 'success', 'error' => 'danger'] as $flash => $class): if (!Yii::$app->session->hasFlash($flash)) continue; ?>
        <div class="alert alert-<?= $class ?> alert-dismissible fade show d-flex gap-2" role="<?= $flash === 'error' ? 'alert' : 'status' ?>">
            <i class="bi <?= $flash === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' ?> flex-shrink-0" aria-hidden="true"></i>
            <div class="flex-grow-1"><?= Html::encode(Yii::$app->session->getFlash($flash)) ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="ปิดข้อความ"></button>
        </div>
    <?php endforeach; ?>
</div>

<section class="card shadow-sm mb-4" aria-labelledby="item-type-heading">
    <div class="card-header bg-body px-3 px-md-4 py-3">
        <h5 id="item-type-heading" class="mb-1">ประเภทรายการรับและรายการหัก</h5>
        <p class="small text-body-secondary mb-0">เป็นชื่อรายการกลาง ระบบจะนำไปผูกกับบุคลากรและบันทึกยอดจริงแยกในแต่ละรอบ</p>
    </div>
    <div class="card-body px-3 px-md-4 border-bottom">
        <?= Html::beginForm(['create-item-type'], 'post', ['class' => 'row g-3 align-items-end', 'aria-label' => 'เพิ่มประเภทรายการ']) ?>
        <div class="col-md-2"><label class="form-label" for="item-code">รหัสรายการ</label><input class="form-control" id="item-code" name="code" maxlength="50" pattern="[A-Za-z0-9_]+" required placeholder="เช่น coop_debt"><div class="form-text">พิมพ์เล็กหรือใหญ่ได้ ระบบจะบันทึกเป็นตัวพิมพ์ใหญ่</div></div>
        <div class="col-md-3"><label class="form-label" for="item-name">ชื่อรายการ</label><input class="form-control" id="item-name" name="name" maxlength="255" required placeholder="เช่น หนี้สหกรณ์"></div>
        <div class="col-md-2"><label class="form-label" for="item-direction">ประเภท</label><select class="form-select" id="item-direction" name="direction" required><option value="earning">รายการรับ</option><option value="deduction">รายการหัก</option><option value="employer_contribution">เงินสมทบนายจ้าง</option></select></div>
        <div class="col-md-2"><label class="form-label" for="item-group">กลุ่มงาน</label><select class="form-select" id="item-group" name="item_group" required><option value="monthly_pay">เงินรายเดือน</option><option value="compensation">ค่าตอบแทน</option><option value="deduction">รายการหัก</option></select></div>
        <div class="col-md-2">
            <div class="form-check mb-2"><input class="form-check-input" type="checkbox" value="1" name="is_recurring" id="item-recurring" checked><label class="form-check-label" for="item-recurring">ใช้เป็นรายการประจำ</label></div>
            <div class="form-check"><input class="form-check-input" type="checkbox" value="1" name="is_sso_wage" id="item-sso-wage"><label class="form-check-label" for="item-sso-wage">รวมเป็นฐานประกันสังคม</label></div>
        </div>
        <div class="col-md-2"><button class="btn btn-primary w-100" type="submit"><i class="bi bi-plus-lg me-1" aria-hidden="true"></i>เพิ่มรายการ</button></div>
        <?= Html::endForm() ?>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>รหัส</th><th>ชื่อรายการ</th><th>กลุ่มงาน</th><th>ประเภท</th><th class="text-center">รายการประจำ</th><th class="text-center">ฐานประกันสังคม</th><th>สถานะ</th><th class="text-end">จัดการ</th></tr></thead>
            <tbody>
            <?php foreach ($itemTypes as $item): ?>
                <tr><td class="text-nowrap"><code><?= Html::encode($item['code']) ?></code><?php if (!preg_match('/[A-Z0-9]/', $item['code'])): ?><div><span class="badge bg-warning-subtle text-warning-emphasis mt-1">กรุณาแก้รหัส</span></div><?php endif; ?></td><td><strong><?= Html::encode($item['name']) ?></strong></td><td><?= Html::encode($groupLabels[$item['item_group']] ?? $item['item_group']) ?></td><td><?= Html::encode($directionLabels[$item['direction']] ?? $item['direction']) ?></td>
                    <td class="text-center"><?= $item['is_recurring'] ? '<i class="bi bi-check-circle-fill text-success" aria-label="ใช่"></i>' : '<span class="text-body-secondary">—</span>' ?></td>
                    <td class="text-center"><?= $item['is_sso_wage'] ? '<span class="badge bg-info-subtle text-info-emphasis">รวมฐาน</span>' : '<span class="text-body-secondary">ไม่รวม</span>' ?></td>
                    <td><span class="badge bg-success-subtle text-success-emphasis">ใช้งาน</span></td>
                    <td class="text-end"><button type="button" class="btn btn-sm btn-outline-primary payroll-item-edit" data-bs-toggle="modal" data-bs-target="#payroll-item-edit-modal"
                        data-id="<?= (int) $item['id'] ?>" data-code="<?= Html::encode($item['code']) ?>" data-name="<?= Html::encode($item['name']) ?>"
                        data-direction="<?= Html::encode($item['direction']) ?>" data-group="<?= Html::encode($item['item_group']) ?>" data-recurring="<?= (int) $item['is_recurring'] ?>" data-sso-wage="<?= (int) $item['is_sso_wage'] ?>"
                        aria-label="แก้ไข <?= Html::encode($item['name']) ?>"><i class="bi bi-pencil me-1" aria-hidden="true"></i>แก้ไข</button></td></tr>
            <?php endforeach; ?>
            <?php if (!$itemTypes): ?><tr><td colspan="8" class="text-center text-body-secondary py-4">ยังไม่มีประเภทรายการ</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="card shadow-sm" aria-labelledby="sso-rule-heading">
    <div class="card-header bg-body px-3 px-md-4 py-3">
        <h5 id="sso-rule-heading" class="mb-1">กฎเงินสมทบประกันสังคม มาตรา 33</h5>
        <p class="small text-body-secondary mb-0">อัตราลูกจ้างเป็นรายการหัก ส่วนอัตรานายจ้างเป็นค่าใช้จ่ายหน่วยงานและไม่หักจากเงินสุทธิ</p>
    </div>
    <div class="card-body px-3 px-md-4 border-bottom">
        <?= Html::beginForm(['create-contribution-rule'], 'post', ['class' => 'row g-3', 'aria-label' => 'เพิ่มกฎประกันสังคม']) ?>
        <div class="col-md-4"><label class="form-label" for="rule-name">ชื่อกฎ</label><input class="form-control" id="rule-name" name="name" maxlength="255" required placeholder="เช่น ประกันสังคม ม.33 ปี 2572–2574"></div>
        <div class="col-md-2"><label class="form-label" for="rule-from">มีผลตั้งแต่</label><input class="form-control" type="date" id="rule-from" name="effective_from" required></div>
        <div class="col-md-2"><label class="form-label" for="rule-to">สิ้นสุด</label><input class="form-control" type="date" id="rule-to" name="effective_to"><div class="form-text">เว้นว่างหากไม่มีกำหนด</div></div>
        <div class="col-md-2"><label class="form-label" for="rule-min">ฐานขั้นต่ำ</label><input class="form-control" type="number" id="rule-min" name="minimum_wage_base" min="0" step="0.01" required></div>
        <div class="col-md-2"><label class="form-label" for="rule-max">ฐานสูงสุด</label><input class="form-control" type="number" id="rule-max" name="maximum_wage_base" min="0" step="0.01" required></div>
        <div class="col-md-2"><label class="form-label" for="employee-rate">ลูกจ้าง (%)</label><input class="form-control" type="number" id="employee-rate" name="employee_rate" min="0" max="100" step="0.0001" required></div>
        <div class="col-md-2"><label class="form-label" for="employer-rate">นายจ้าง (%)</label><input class="form-control" type="number" id="employer-rate" name="employer_rate" min="0" max="100" step="0.0001" required></div>
        <div class="col-md-6"><label class="form-label" for="legal-reference">กฎหมาย/ประกาศอ้างอิง</label><input class="form-control" id="legal-reference" name="legal_reference" maxlength="500" required placeholder="ชื่อประกาศหรือเลขราชกิจจานุเบกษา"></div>
        <div class="col-md-2 d-flex align-items-end"><button class="btn btn-outline-primary w-100" type="submit"><i class="bi bi-calendar-plus me-1" aria-hidden="true"></i>เพิ่มกฎ</button></div>
        <?= Html::endForm() ?>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>ช่วงวันที่มีผล</th><th>ชื่อกฎ</th><th class="text-end">ฐานค่าจ้าง</th><th class="text-end">ลูกจ้าง</th><th class="text-end">นายจ้าง</th><th>อ้างอิง</th></tr></thead>
            <tbody>
            <?php foreach ($contributionRules as $rule): ?>
                <tr><td class="text-nowrap"><?= Yii::$app->formatter->asDate($rule['effective_from'], 'php:d/m/Y') ?> – <?= $rule['effective_to'] ? Yii::$app->formatter->asDate($rule['effective_to'], 'php:d/m/Y') : 'ไม่มีกำหนด' ?></td>
                    <td><strong><?= Html::encode($rule['name']) ?></strong><div class="small"><span class="badge bg-success-subtle text-success-emphasis">ใช้งาน</span></div></td>
                    <td class="text-end text-nowrap"><?= number_format($rule['minimum_wage_base'], 2) ?> – <?= number_format($rule['maximum_wage_base'], 2) ?></td>
                    <td class="text-end"><?= number_format($rule['employee_rate'] * 100, 2) ?>%</td><td class="text-end"><?= number_format($rule['employer_rate'] * 100, 2) ?>%</td><td class="small text-body-secondary"><?= Html::encode($rule['legal_reference'] ?: 'ไม่ระบุ') ?></td></tr>
            <?php endforeach; ?>
            <?php if (!$contributionRules): ?><tr><td colspan="6" class="text-center text-body-secondary py-4">ยังไม่มีกฎประกันสังคม</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<div class="alert alert-info d-flex gap-2 mt-3 mb-0" role="note"><i class="bi bi-info-circle flex-shrink-0" aria-hidden="true"></i><div>หน้านี้เป็นการตั้งค่ากฎเท่านั้น ยังไม่สร้างรายการหักหรือเปลี่ยนยอดสุทธิในรอบเงินเดือน จนกว่าจะผ่านขั้นตอนคำนวณและตรวจสอบ</div></div>

<div class="modal fade" id="payroll-item-edit-modal" tabindex="-1" aria-labelledby="payroll-item-edit-title" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content">
        <?= Html::beginForm(['update-item-type'], 'post') ?>
        <div class="modal-header"><h5 class="modal-title" id="payroll-item-edit-title">แก้ไขประเภทรายการ</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button></div>
        <div class="modal-body">
            <?= Html::hiddenInput('id', '', ['id' => 'edit-item-id']) ?>
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label" for="edit-item-code">รหัสรายการ</label><input class="form-control" id="edit-item-code" name="code" maxlength="50" pattern="[A-Za-z0-9_]+" required><div class="form-text">พิมพ์เล็กหรือใหญ่ได้ ระบบจะบันทึกเป็นตัวพิมพ์ใหญ่</div></div>
                <div class="col-md-8"><label class="form-label" for="edit-item-name">ชื่อรายการ</label><input class="form-control" id="edit-item-name" name="name" maxlength="255" required></div>
                <div class="col-md-4"><label class="form-label" for="edit-item-direction">ประเภท</label><select class="form-select" id="edit-item-direction" name="direction"><option value="earning">รายการรับ</option><option value="deduction">รายการหัก</option><option value="employer_contribution">เงินสมทบนายจ้าง</option></select></div>
                <div class="col-md-4"><label class="form-label" for="edit-item-group">กลุ่มงาน</label><select class="form-select" id="edit-item-group" name="item_group"><option value="monthly_pay">เงินรายเดือน</option><option value="compensation">ค่าตอบแทน</option><option value="deduction">รายการหัก</option></select></div>
                <div class="col-md-4 d-flex flex-column justify-content-end gap-2">
                    <div class="form-check"><input class="form-check-input" type="checkbox" value="1" name="is_recurring" id="edit-item-recurring"><label class="form-check-label" for="edit-item-recurring">ใช้เป็นรายการประจำ</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" value="1" name="is_sso_wage" id="edit-item-sso-wage"><label class="form-check-label" for="edit-item-sso-wage">รวมเป็นฐานประกันสังคม</label></div>
                </div>
            </div>
            <div class="alert alert-warning d-flex gap-2 mt-3 mb-0" role="note"><i class="bi bi-exclamation-triangle flex-shrink-0" aria-hidden="true"></i><div>การแก้รหัสหรือประเภทจะมีผลกับการตั้งค่าที่นำไปใช้ในรอบถัดไป และระบบจะเก็บประวัติการเปลี่ยนแปลง</div></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ยกเลิก</button><button type="submit" class="btn btn-primary">บันทึกการแก้ไข</button></div>
        <?= Html::endForm() ?>
    </div></div>
</div>

<?php
$this->registerJs(<<<'JS'
document.querySelectorAll('.payroll-item-edit').forEach(function (button) {
    button.addEventListener('click', function () {
        document.getElementById('edit-item-id').value = button.dataset.id;
        document.getElementById('edit-item-code').value = button.dataset.code;
        document.getElementById('edit-item-name').value = button.dataset.name;
        document.getElementById('edit-item-direction').value = button.dataset.direction;
        document.getElementById('edit-item-group').value = button.dataset.group;
        document.getElementById('edit-item-recurring').checked = button.dataset.recurring === '1';
        document.getElementById('edit-item-sso-wage').checked = button.dataset.ssoWage === '1';
    });
});
JS);
?>
