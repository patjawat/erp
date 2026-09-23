<?php

use app\components\AppHelper;
use app\components\ThaiDateHelper;
use app\components\widgets\DataSummaryWidget;
use app\widgets\datepicker\DatepickerThai;
use yii\helpers\Html;

/** @var yii\data\ActiveDataProvider $provider */
/** @var string $stage */
/** @var string $from */
/** @var string $to */
/** @var string $q */
/** @var int $assetId */
/** @var array $machineOptions  asset_id => label */
$isWash = $stage === 'WASH';
$this->title = 'ประวัติรอบ' . ($isWash ? 'ซัก' : 'อบ') . 'ผ้า';
$classLabel = static fn($c) => $c === 'INFECTIOUS' ? 'ผ้าติดเชื้อ' : 'ผ้าเปื้อน';
?>
<div class="container-fluid py-3">
    <?= $this->render('../_nav', ['active' => $isWash ? 'wash' : 'dry']) ?>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h4 fw-bold mb-0"><i class="bi bi-clock-history me-2"></i><?= Html::encode($this->title) ?></h1>
        <?= Html::a('<i class="bi bi-arrow-left me-1"></i>กลับหน้าปฏิบัติงาน', ['index', 'stage' => $stage], ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-3"><div class="card-body">
        <?= Html::beginForm(['history'], 'get', ['class' => 'row g-2 align-items-end']) ?>
            <?= Html::hiddenInput('stage', $stage) ?>
            <div class="col-6 col-md-3">
                <label class="form-label small">ตั้งแต่วันที่</label>
                <?= DatepickerThai::widget(['name' => 'from', 'value' => AppHelper::convertToThai($from), 'options' => ['id' => 'h_from', 'class' => 'form-control', 'autocomplete' => 'off', 'placeholder' => 'วว/ดด/พ.ศ.']]) ?>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small">ถึงวันที่</label>
                <?= DatepickerThai::widget(['name' => 'to', 'value' => AppHelper::convertToThai($to), 'options' => ['id' => 'h_to', 'class' => 'form-control', 'autocomplete' => 'off', 'placeholder' => 'วว/ดด/พ.ศ.']]) ?>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label small">เครื่อง</label>
                <?= Html::dropDownList('asset_id', $assetId ?: '', $machineOptions, ['prompt' => 'ทุกเครื่อง', 'class' => 'form-select']) ?>
            </div>
            <div class="col-8 col-md-2">
                <label class="form-label small">ค้นหาเลขรอบ</label>
                <?= Html::textInput('q', $q, ['class' => 'form-control', 'placeholder' => 'LB-...']) ?>
            </div>
            <div class="col-4 col-md-1">
                <?= Html::submitButton('<i class="bi bi-search"></i>', ['class' => 'btn btn-primary w-100']) ?>
            </div>
        <?= Html::endForm() ?>
    </div></div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0"><div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light"><tr>
                    <th class="ps-4">เลขรอบ</th><th>เครื่อง</th><th>กลุ่มผ้า</th>
                    <th>เริ่ม–สิ้นสุด</th><th class="text-end">เข้า (กก.)</th><th class="pe-4">สถานะ</th>
                </tr></thead>
                <tbody>
                <?php foreach ($provider->getModels() as $b): ?>
                    <tr>
                        <td class="ps-4 fw-semibold"><?= Html::encode($b['batch_no']) ?></td>
                        <td class="small"><?= Html::encode($b['asset_code'] ?: '#' . $b['asset_id']) ?></td>
                        <td><?= Html::encode($classLabel($b['linen_class'])) ?></td>
                        <td class="small"><?= Html::encode(ThaiDateHelper::formatThaiDate($b['started_at']) . ' ' . date('H:i', strtotime($b['started_at']))) ?><?= $b['ended_at'] ? '<br>ถึง ' . date('H:i', strtotime($b['ended_at'])) : '' ?></td>
                        <td class="text-end fw-semibold"><?= number_format((float) $b['input_kg'], 1) ?></td>
                        <td class="pe-4">
                            <?php if ($b['status'] === 'COMPLETED'): ?><span class="badge text-bg-success">เสร็จ</span>
                            <?php elseif ($b['status'] === 'ABORTED'): ?><span class="badge text-bg-danger">หยุด</span>
                            <?php elseif ($b['status'] === 'RUNNING'): ?><span class="badge text-bg-warning">กำลังทำงาน</span>
                            <?php else: ?><span class="badge text-bg-secondary"><?= Html::encode($b['status']) ?></span><?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$provider->getModels()): ?>
                    <tr><td colspan="6" class="text-center text-body-secondary py-5"><i class="bi bi-inbox fs-3 d-block mb-2"></i>ไม่พบรอบในช่วงที่เลือก</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div></div>
        <div class="card-footer bg-body border-top py-3 px-4">
            <?= DataSummaryWidget::widget(['dataProvider' => $provider]) ?>
        </div>
    </div>
</div>
