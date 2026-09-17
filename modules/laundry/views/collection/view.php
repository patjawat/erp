<?php

use yii\helpers\Html;

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
    <div class="d-flex flex-column flex-sm-row justify-content-between gap-2 mb-3">
        <div>
            <h4 class="fw-bold mb-1"><?= Html::encode($this->title) ?></h4>
            <div class="text-muted">วันที่เก็บ <?= Html::encode($round['collection_date']) ?> · <?= $round['status'] === 'CONFIRMED' ? 'ยืนยันแล้ว' : 'กำลังบันทึก' ?></div>
        </div>
        <?= Html::a('กลับรายการรอบ', ['index'], ['class' => 'btn btn-outline-secondary rounded-3 align-self-start']) ?>
    </div>
    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger"><?= Html::encode(Yii::$app->session->getFlash('error')) ?></div>
    <?php endif; ?>
    <div class="row g-3 mb-3">
        <?php foreach (['ผ้าเปื้อน' => $totalSoiled, 'ผ้าติดเชื้อ' => $totalInfectious, 'รวม' => $totalSoiled + $totalInfectious] as $label => $kg): ?>
            <div class="col-12 col-sm-4"><div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body py-3">
                <div class="fw-bold fs-3"><?= number_format($kg, 3) ?> <span class="fs-6">กก.</span></div>
                <div class="text-primary small fw-semibold mt-2"><?= Html::encode($label) ?></div>
            </div></div></div>
        <?php endforeach; ?>
    </div>

    <?php if ($canEdit): ?>
        <div class="card border-0 shadow-sm rounded-4 mb-3"><div class="card-body">
            <h5 class="fw-semibold">เพิ่มหน่วยงานที่ไปรับผ้า</h5>
            <?= Html::beginForm(['add-stop', 'id' => $round['id']], 'post', ['class' => 'row g-3 align-items-end']) ?>
                <div class="col-12 col-lg-4"><label class="form-label">หน่วยงาน</label>
                    <?= Html::dropDownList('department_id', null, $departments, ['prompt' => 'เลือกหน่วยงาน', 'class' => 'form-select', 'required' => true]) ?>
                </div>
                <div class="col-12 col-lg-3"><label class="form-label">วันที่และเวลาที่เก็บ</label>
                    <?= Html::input('datetime-local', 'collected_at', $round['collection_date'] . 'T' . date('H:i'), ['class' => 'form-control', 'required' => true]) ?>
                </div>
                <div class="col-6 col-lg-2"><label class="form-label">ถุงผ้าเปื้อน</label>
                    <?= Html::input('number', 'soiled_bag_count', 0, ['class' => 'form-control', 'min' => 0, 'required' => true]) ?>
                </div>
                <div class="col-6 col-lg-2"><label class="form-label">ถุงผ้าติดเชื้อ</label>
                    <?= Html::input('number', 'infectious_bag_count', 0, ['class' => 'form-control', 'min' => 0, 'required' => true]) ?>
                </div>
                <div class="col-12 col-lg-1"><?= Html::submitButton('เพิ่ม', ['class' => 'btn btn-primary rounded-3 w-100']) ?></div>
            <?= Html::endForm() ?>
        </div></div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-4 mb-3"><div class="card-header bg-body px-4 py-3"><h5 class="fw-semibold mb-0">หน่วยงานที่เก็บและผลชั่ง ณ โรงซัก</h5></div>
        <div class="card-body p-0"><div class="table-responsive"><table class="table align-middle mb-0">
            <thead><tr><th class="ps-4">หน่วยงาน</th><th>ผ้าเปื้อน</th><th>ผ้าติดเชื้อ</th><th>รวม</th><th class="pe-4">บันทึกน้ำหนัก</th></tr></thead>
            <tbody>
            <?php foreach ($stops as $stop): ?>
                <?php $w = $byStop[$stop['id']] ?? []; $soiled = $w['SOILED']['net_kg'] ?? null; $infectious = $w['INFECTIOUS']['net_kg'] ?? null; ?>
                <tr>
                    <td class="ps-4 fw-semibold"><?= Html::encode($stop['department_name'] ?: '#' . $stop['department_id']) ?><div class="small text-muted"><?= Html::encode($stop['collected_at']) ?></div></td>
                    <td><?= (int) $stop['soiled_bag_count'] ?> ถุง · <?= $soiled === null ? 'รอชั่ง' : number_format((float) $soiled, 3) . ' กก.' ?></td>
                    <td><?= (int) $stop['infectious_bag_count'] ?> ถุง · <?= $infectious === null ? 'รอชั่ง' : number_format((float) $infectious, 3) . ' กก.' ?></td>
                    <td class="fw-semibold"><?= number_format((float) $soiled + (float) $infectious, 3) ?> กก.</td>
                    <td class="pe-4">
                        <?php if ($canEdit): foreach (['SOILED' => 'ผ้าเปื้อน', 'INFECTIOUS' => 'ผ้าติดเชื้อ'] as $class => $label): ?>
                                <?= Html::beginForm(['weigh', 'id' => $round['id'], 'stopId' => $stop['id']], 'post', ['class' => 'd-flex flex-wrap gap-1 align-items-center mb-2']) ?>
                                    <?= Html::hiddenInput('linen_class', $class) ?>
                                    <span class="small text-muted"><?= Html::encode($label) ?></span>
                                    <?= Html::input('number', 'gross_kg', $w[$class]['gross_kg'] ?? null, ['class' => 'form-control form-control-sm', 'style' => 'width:92px', 'min' => '0.001', 'step' => '0.001', 'placeholder' => 'รวม กก.', 'required' => true]) ?>
                                    <?= Html::input('number', 'tare_kg', $w[$class]['tare_kg'] ?? '0', ['class' => 'form-control form-control-sm', 'style' => 'width:92px', 'min' => 0, 'step' => '0.001', 'placeholder' => 'ภาชนะ', 'required' => true]) ?>
                                    <?= Html::textInput('scale_ref', $w[$class]['scale_ref'] ?? '', ['class' => 'form-control form-control-sm', 'style' => 'width:110px', 'placeholder' => 'เครื่องชั่ง']) ?>
                                    <?= Html::submitButton('บันทึก', ['class' => 'btn btn-sm btn-outline-primary']) ?>
                                <?= Html::endForm() ?>
                        <?php endforeach; endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$stops): ?><tr><td colspan="5" class="text-center text-muted py-4">ยังไม่มีหน่วยงานในรอบนี้</td></tr><?php endif; ?>
            </tbody>
        </table></div></div>
    </div>
    <?php if ($canEdit): ?>
        <?= Html::beginForm(['confirm', 'id' => $round['id']], 'post', ['class' => 'text-end']) ?>
            <?= Html::submitButton('ยืนยันรอบเก็บและน้ำหนัก', ['class' => 'btn btn-success rounded-3', 'data-confirm' => 'ตรวจสอบน้ำหนักครบทุกหน่วยงานแล้วใช่หรือไม่? หลังยืนยันจะแก้ไขตรงไม่ได้']) ?>
        <?= Html::endForm() ?>
    <?php endif; ?>
</div>
