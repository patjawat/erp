<?php

use yii\helpers\Html;

$this->title = 'รอบซัก–อบ';
$canManage = Yii::$app->user->can('laundry.manage');
$canApprove = Yii::$app->user->can('laundry.approve');
?>
<div class="container-fluid py-3">
    <?= $this->render('../_nav', ['active' => 'processing']) ?>
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
        <div><h1 class="h4 fw-bold mb-1"><i class="bi bi-moisture me-2"></i><?= Html::encode($this->title) ?></h1><div class="text-body-secondary">บันทึกเครื่องครุภัณฑ์ เวลา และน้ำหนักต่อรอบ (กิโลกรัม)</div></div>
        <?php if ($canManage): ?>
            <div class="d-flex flex-wrap gap-2">
                <?= Html::a('<i class="bi bi-droplet me-1"></i>เริ่มรอบซัก', ['new', 'stage' => 'WASH'], ['class' => 'btn btn-primary']) ?>
                <?= Html::a('<i class="bi bi-wind me-1"></i>เริ่มรอบอบ', ['new', 'stage' => 'DRY'], ['class' => 'btn btn-outline-primary']) ?>
            </div>
        <?php endif; ?>
    </div>
    <?php foreach (['error' => 'danger', 'success' => 'success'] as $key => $class): ?>
        <?php if (Yii::$app->session->hasFlash($key)): ?><div class="alert alert-<?= $class ?> d-flex align-items-center"><i class="bi bi-<?= $class === 'danger' ? 'exclamation-triangle' : 'check-circle' ?> me-2"></i><?= Html::encode(Yii::$app->session->getFlash($key)) ?></div><?php endif; ?>
    <?php endforeach; ?>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-body px-4 py-3"><h5 class="fw-semibold mb-0">รอบเครื่องล่าสุด</h5></div>
        <div class="card-body p-0"><div class="table-responsive"><table class="table align-middle mb-0">
            <thead><tr><th class="ps-4">เลขรอบ / เครื่อง</th><th>ขั้นตอน / ประเภทผ้า</th><th>เริ่ม–สิ้นสุด</th><th class="text-end">เข้า (กก.)</th><th class="text-end">ออก (กก.)</th><th>สถานะ / จัดการ</th></tr></thead>
            <tbody>
            <?php foreach ($batches as $batch): ?>
                <tr>
                    <td class="ps-4 fw-semibold"><?= Html::encode($batch['batch_no']) ?><div class="small text-muted"><?= Html::encode($batch['asset_code'] ?: '#' . $batch['asset_id']) ?></div></td>
                    <td><?= $batch['stage'] === 'WASH' ? 'ซัก' : 'อบ' ?> · <?= $batch['linen_class'] === 'INFECTIOUS' ? 'ผ้าติดเชื้อ' : 'ผ้าเปื้อน' ?></td>
                    <td class="small"><?= Html::encode($batch['started_at']) ?><br><?= Html::encode($batch['ended_at'] ?: 'กำลังทำงาน') ?></td>
                    <td class="text-end"><?= number_format((float) $batch['input_kg'], 3) ?></td>
                    <td class="text-end"><?= $batch['output_kg'] === null ? '—' : number_format((float) $batch['output_kg'], 3) ?></td>
                    <td>
                        <?php if ($batch['status'] === 'RUNNING' && $canManage): ?>
                            <?= Html::beginForm(['finish', 'id' => $batch['id']], 'post', ['class' => 'd-flex flex-wrap gap-1 mb-2']) ?>
                                <?= Html::input('number', 'output_kg', '', ['class' => 'form-control form-control-sm w-auto', 'step' => '0.001', 'min' => '0.001', 'placeholder' => 'ผลผลิต กก.', 'required' => true]) ?>
                                <?= Html::submitButton('ปิดรอบ', ['class' => 'btn btn-sm btn-success']) ?>
                            <?= Html::endForm() ?>
                            <?= Html::beginForm(['abort', 'id' => $batch['id']], 'post', ['class' => 'd-flex flex-wrap gap-1']) ?>
                                <?= Html::textInput('reason', '', ['class' => 'form-control form-control-sm w-auto', 'placeholder' => 'เหตุหยุดรอบ', 'required' => true, 'minlength' => 5]) ?>
                                <?= Html::submitButton('หยุดรอบ', ['class' => 'btn btn-sm btn-outline-danger', 'data-confirm' => 'ยืนยันหยุดรอบ? น้ำหนักต้นทางจะยังถูกกันไว้']) ?>
                            <?= Html::endForm() ?>
                        <?php else: ?>
                            <span class="badge <?= $batch['status'] === 'COMPLETED' ? 'bg-success' : ($batch['status'] === 'ABORTED' ? 'bg-danger' : 'bg-warning text-dark') ?>"><?= Html::encode($batch['status']) ?></span>
                            <?php if ($batch['note']): ?><div class="small text-muted"><?= Html::encode($batch['note']) ?></div><?php endif; ?>
                            <?php if ($batch['status'] === 'ABORTED' && $canManage && !isset($recoveryByBatch[$batch['id']])): ?>
                                <?= Html::beginForm(['request-recovery', 'id' => $batch['id']], 'post', ['class' => 'd-flex flex-wrap gap-1 mt-2']) ?>
                                    <?= Html::dropDownList('outcome', 'REPROCESS', ['REPROCESS' => 'นำกลับเข้ารอบ', 'DISCARD' => 'ใช้ต่อไม่ได้'], ['class' => 'form-select form-select-sm w-auto']) ?>
                                    <?= Html::input('number', 'measured_kg', '', ['class' => 'form-control form-control-sm w-auto', 'step' => '0.001', 'min' => 0, 'placeholder' => 'ชั่งใหม่ กก.']) ?>
                                    <?= Html::textInput('evidence', '', ['class' => 'form-control form-control-sm w-auto', 'placeholder' => 'หลักฐาน/สภาพผ้า', 'minlength' => 5, 'required' => true]) ?>
                                    <?= Html::submitButton('ส่งอนุมัติ', ['class' => 'btn btn-sm btn-outline-primary']) ?>
                                <?= Html::endForm() ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$batches): ?><tr><td colspan="6" class="text-center text-muted py-4">ยังไม่มีรอบเครื่อง</td></tr><?php endif; ?>
            </tbody>
        </table></div></div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mt-3">
        <div class="card-header bg-body px-4 py-3"><h5 class="fw-semibold mb-0">ผลตรวจผ้าจากรอบเครื่องที่หยุด</h5></div>
        <div class="px-4 pt-3 small text-muted">การระบุว่าใช้ต่อไม่ได้เป็นผลด้านน้ำหนักรอบเครื่องเท่านั้น ไม่ตัดยอดผ้าเป็นชิ้น หากต้องตัดผ้าให้ทำรายการสูญเสียและอนุมัติในบัญชีผ้าแยกต่างหาก</div>
        <div class="card-body p-0"><div class="table-responsive"><table class="table align-middle mb-0">
            <thead><tr><th class="ps-4">รอบเดิม</th><th>ผลตรวจ</th><th class="text-end">น้ำหนักชั่งใหม่</th><th>หลักฐาน</th><th>สถานะ / ดำเนินการ</th></tr></thead>
            <tbody>
            <?php foreach ($recoveries as $recovery): ?>
                <tr>
                    <td class="ps-4 fw-semibold"><?= Html::encode($recovery['batch_no']) ?> <span class="small text-muted">(<?= number_format((float) $recovery['input_kg'], 3) ?> กก. เดิม)</span></td>
                    <td><?= $recovery['outcome'] === 'REPROCESS' ? 'นำกลับเข้ารอบ' : 'ใช้ต่อไม่ได้' ?></td>
                    <td class="text-end"><?= number_format((float) $recovery['measured_kg'], 3) ?> กก.</td>
                    <td><?= Html::encode($recovery['evidence']) ?></td>
                    <td>
                        <?php if ($recovery['status'] === 'PENDING' && $canApprove && (int) $recovery['created_by'] !== (int) Yii::$app->user->id): ?>
                            <?= Html::beginForm(['approve-recovery', 'id' => $recovery['id']], 'post') ?>
                                <?= Html::submitButton('อนุมัติผลตรวจ', ['class' => 'btn btn-sm btn-outline-success', 'data-confirm' => 'ยืนยันผลตรวจและน้ำหนักชั่งใหม่?']) ?>
                            <?= Html::endForm() ?>
                        <?php else: ?>
                            <span class="badge <?= $recovery['status'] === 'APPROVED' ? 'bg-success' : 'bg-warning text-dark' ?>"><?= Html::encode($recovery['status']) ?></span>
                            <?php if ($recovery['status'] === 'APPROVED' && $recovery['outcome'] === 'REPROCESS' && $canManage): ?>
                                <?= Html::a('เริ่มรอบใหม่', ['new', 'stage' => $recovery['stage'], 'linenClass' => $recovery['linen_class'], 'mode' => 'RECOVERY'], ['class' => 'btn btn-sm btn-outline-primary ms-1']) ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$recoveries): ?><tr><td colspan="5" class="text-center text-muted py-4">ยังไม่มีรอบที่ต้องตรวจผ้ากู้คืน</td></tr><?php endif; ?>
            </tbody>
        </table></div></div>
    </div>
</div>
