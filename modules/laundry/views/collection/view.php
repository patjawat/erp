<?php

use app\components\AppHelper;
use app\components\ThaiDateHelper;
use app\widgets\datepicker\DatepickerThai;
use yii\helpers\Html;

/** @var array $round */
/** @var array $stops */
/** @var array $byStop */
/** @var array $departments */
$this->title = 'รอบเก็บ ' . $round['round_no'];
$canEdit = $round['status'] === 'OPEN' && Yii::$app->user->can('laundry.manage');
$totalSoiled = 0;
$totalInfectious = 0;
foreach ($byStop as $values) {
    $totalSoiled += (float) ($values['SOILED']['net_kg'] ?? 0);
    $totalInfectious += (float) ($values['INFECTIOUS']['net_kg'] ?? 0);
}
?>
<div class="container-fluid py-3">
    <?= $this->render('../_nav', ['active' => 'collection']) ?>
    <div class="d-flex flex-column flex-sm-row justify-content-between gap-2 mb-3">
        <div>
            <h1 class="h4 fw-bold mb-1"><i class="bi bi-basket3 me-2"></i><?= Html::encode($this->title) ?></h1>
            <div class="text-body-secondary">วันที่เก็บ <?= Html::encode(ThaiDateHelper::formatThaiDate($round['collection_date'])) ?> ·
                <?php if ($round['status'] === 'CONFIRMED'): ?>
                    <span class="text-success-emphasis"><i class="bi bi-check-circle me-1"></i>ตรวจรับแล้ว</span>
                <?php else: ?>
                    <span class="text-warning-emphasis"><i class="bi bi-pencil me-1"></i>กำลังบันทึก</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2 align-self-start">
            <?php if ($canEdit): ?>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEntryModal">
                    <i class="bi bi-plus-lg me-1"></i>เพิ่มข้อมูลรับผ้า
                </button>
            <?php endif; ?>
            <?= Html::a('<i class="bi bi-arrow-left me-1"></i>กลับรายการรอบ', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
    </div>
    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger d-flex align-items-center"><i class="bi bi-exclamation-triangle me-2"></i><?= Html::encode(Yii::$app->session->getFlash('error')) ?></div>
    <?php endif; ?>

    <div class="row g-3 mb-3">
        <?php foreach (['ผ้าเปื้อน' => $totalSoiled, 'ผ้าติดเชื้อ' => $totalInfectious, 'รวม' => $totalSoiled + $totalInfectious] as $label => $kg): ?>
            <div class="col-12 col-sm-4"><div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body py-3">
                <div class="fw-bold fs-3"><?= number_format($kg, 3) ?> <span class="fs-6 text-body-secondary">กก.</span></div>
                <div class="text-primary small fw-semibold mt-2"><?= Html::encode($label) ?></div>
            </div></div></div>
        <?php endforeach; ?>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-3">
        <div class="card-header bg-body px-4 py-3"><h2 class="h6 fw-semibold mb-0"><i class="bi bi-list-ul me-2"></i>หน่วยงานที่เก็บและน้ำหนัก</h2></div>
        <div class="card-body p-0"><div class="table-responsive"><table class="table align-middle mb-0">
            <thead class="table-light"><tr>
                <th class="ps-4">หน่วยงาน</th>
                <th class="text-end">ผ้าเปื้อน (กก.)</th><th class="text-end">ผ้าติดเชื้อ (กก.)</th><th class="text-end pe-4">รวม (กก.)</th>
            </tr></thead>
            <tbody>
            <?php foreach ($stops as $stop): ?>
                <?php $w = $byStop[$stop['id']] ?? []; $soiled = (float) ($w['SOILED']['net_kg'] ?? 0); $infectious = (float) ($w['INFECTIOUS']['net_kg'] ?? 0); ?>
                <tr>
                    <td class="ps-4 fw-semibold"><?= Html::encode($stop['department_name'] ?: '#' . $stop['department_id']) ?>
                        <div class="small text-body-secondary"><?= $stop['collected_at'] ? Html::encode(ThaiDateHelper::formatThaiDate($stop['collected_at']) . ' ' . date('H:i', strtotime($stop['collected_at']))) : '' ?></div>
                    </td>
                    <td class="text-end"><?= number_format($soiled, 3) ?></td>
                    <td class="text-end"><?= number_format($infectious, 3) ?></td>
                    <td class="text-end pe-4 fw-semibold"><?= number_format($soiled + $infectious, 3) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$stops): ?><tr><td colspan="4" class="text-center text-body-secondary py-4"><i class="bi bi-inbox me-1"></i>ยังไม่มีหน่วยงานในรอบนี้ — กด "เพิ่มข้อมูลรับผ้า"</td></tr><?php endif; ?>
            </tbody>
        </table></div></div>
    </div>

    <?php if ($canEdit && $stops): ?>
        <?= Html::beginForm(['confirm', 'id' => $round['id']], 'post', ['class' => 'text-end']) ?>
            <?= Html::submitButton('<i class="bi bi-check-circle me-1"></i>ยืนยันรอบเก็บและน้ำหนัก', ['class' => 'btn btn-success', 'data-confirm' => 'ตรวจสอบน้ำหนักครบทุกหน่วยงานแล้วใช่หรือไม่? หลังยืนยันจะแก้ไขตรงไม่ได้']) ?>
        <?= Html::endForm() ?>
    <?php endif; ?>
</div>

<?php if ($canEdit): ?>
<!-- Modal: เพิ่มข้อมูลรับผ้า -->
<div class="modal fade" id="addEntryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4">
            <?= Html::beginForm(['add-stop', 'id' => $round['id']], 'post') ?>
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-lg me-2"></i>เพิ่มข้อมูลรับผ้า</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">หน่วยงาน</label>
                        <?= Html::dropDownList('department_id', null, $departments, ['prompt' => 'เลือกหน่วยงาน', 'class' => 'form-select', 'required' => true]) ?>
                    </div>
                    <div class="col-7 col-sm-4">
                        <label class="form-label">วันที่เก็บ</label>
                        <?= DatepickerThai::widget([
                            'name' => 'collected_date',
                            'value' => AppHelper::convertToThai($round['collection_date']),
                            'options' => ['id' => 'entry_collected_date', 'class' => 'form-control', 'autocomplete' => 'off', 'placeholder' => 'วว/ดด/พ.ศ.', 'required' => true],
                        ]) ?>
                    </div>
                    <div class="col-5 col-sm-3">
                        <label class="form-label">เวลา</label>
                        <?= Html::input('time', 'collected_time', date('H:i'), ['class' => 'form-control', 'required' => true]) ?>
                    </div>
                    <div class="col-6 col-sm">
                        <label class="form-label">ผ้าเปื้อน (กก.)</label>
                        <?= Html::input('number', 'soiled_kg', '', ['class' => 'form-control', 'min' => '0', 'step' => '0.001', 'placeholder' => '0.000']) ?>
                    </div>
                    <div class="col-6 col-sm">
                        <label class="form-label">ผ้าติดเชื้อ (กก.)</label>
                        <?= Html::input('number', 'infectious_kg', '', ['class' => 'form-control', 'min' => '0', 'step' => '0.001', 'placeholder' => '0.000']) ?>
                    </div>
                </div>
                <div class="form-text mt-2"><i class="bi bi-info-circle me-1"></i>กรอกน้ำหนักอย่างน้อยหนึ่งประเภท (กิโลกรัม)</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><i class="bi bi-x-lg me-1"></i>ยกเลิก</button>
                <?= Html::submitButton('<i class="bi bi-save me-1"></i>บันทึกข้อมูล', ['class' => 'btn btn-primary']) ?>
            </div>
            <?= Html::endForm() ?>
        </div>
    </div>
</div>
<?php endif; ?>
