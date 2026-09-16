<?php

use app\modules\complaint\models\Complaint;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var int $fiscalYear */
/** @var int[] $years */
/** @var array<string,int> $counts */
/** @var int $total */
/** @var int $open */
/** @var int $overdue */
/** @var Complaint[] $recent */

$this->title = 'รับเรื่องร้องเรียน';

$cards = [
    ['label' => 'ทั้งหมดปีนี้', 'value' => $total, 'icon' => 'bi-megaphone', 'tone' => 'primary'],
    ['label' => 'กำลังดำเนินการ', 'value' => $open, 'icon' => 'bi-hourglass-split', 'tone' => 'warning'],
    ['label' => 'ปิดเคสแล้ว', 'value' => $counts[Complaint::STATUS_CLOSED] ?? 0, 'icon' => 'bi-check2-circle', 'tone' => 'success'],
    ['label' => 'เกินกำหนดปิด', 'value' => $overdue, 'icon' => 'bi-exclamation-triangle', 'tone' => 'danger'],
];
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>ภาพรวมการจัดการเรื่องร้องเรียน ปีงบ <?= $fiscalYear ?><?php $this->endBlock(); ?>

<div class="container-fluid px-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h4 fw-semibold mb-0"><i class="bi bi-megaphone me-1"></i> รับเรื่องร้องเรียน</h1>
            <div class="text-body-secondary small">แจ้งเรื่อง → รับเรื่อง → ประเมิน → ดำเนินงาน → ปิดเคส</div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <?= Html::beginForm(['index'], 'get', ['class' => 'd-flex align-items-center gap-2 mb-0']) ?>
                <label class="small text-body-secondary mb-0">ปีงบ</label>
                <?= Html::dropDownList('fy', $fiscalYear, array_combine($years, $years), ['class' => 'form-select form-select-sm', 'style' => 'width:auto', 'onchange' => 'this.form.submit()']) ?>
            <?= Html::endForm() ?>
            <?= Html::a('<i class="bi bi-plus-lg me-1"></i> รับเรื่องใหม่', ['/complaint/complaint/create'], ['class' => 'btn btn-primary btn-sm']) ?>
        </div>
    </div>

    <div class="mb-3"><?= $this->render('@app/modules/complaint/menu', ['active' => 'overview']) ?></div>

    <div class="row g-3 mb-3">
        <?php foreach ($cards as $c): ?>
            <div class="col-6 col-xl-3">
                <div class="card border shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-<?= $c['tone'] ?>-subtle text-<?= $c['tone'] ?>-emphasis" style="width:48px;height:48px;">
                            <i class="bi <?= $c['icon'] ?> fs-4"></i>
                        </span>
                        <div>
                            <div class="text-body-secondary small"><?= $c['label'] ?></div>
                            <div class="h4 fw-bold mb-0"><?= number_format($c['value']) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="row g-3">
        <div class="col-lg-5">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-transparent fw-semibold"><i class="bi bi-bar-chart-steps me-1"></i> สรุปตามสถานะ</div>
                <ul class="list-group list-group-flush">
                    <?php foreach (Complaint::statusLabels() as $st => $label): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>
                                <span class="badge rounded-pill text-bg-<?= Complaint::statusColor($st) ?> me-2">&nbsp;</span>
                                <?= Html::encode($label) ?>
                            </span>
                            <a href="<?= Url::to(['/complaint/complaint/index', 'fy' => $fiscalYear, 'status' => $st]) ?>" class="badge text-bg-light text-decoration-none">
                                <?= number_format($counts[$st] ?? 0) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-transparent fw-semibold d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-clock-history me-1"></i> เรื่องล่าสุด</span>
                    <?= Html::a('ดูทั้งหมด', ['/complaint/complaint/index', 'fy' => $fiscalYear], ['class' => 'small text-decoration-none']) ?>
                </div>
                <div class="list-group list-group-flush">
                    <?php if (!$recent): ?>
                        <div class="list-group-item text-body-secondary small">— ยังไม่มีเรื่องร้องเรียนในปีงบนี้ —</div>
                    <?php endif; ?>
                    <?php foreach ($recent as $c): ?>
                        <a href="<?= Url::to(['/complaint/complaint/view', 'id' => $c->id]) ?>" class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <div class="text-truncate">
                                    <span class="fw-semibold"><?= Html::encode($c->title) ?></span>
                                    <div class="small text-body-secondary">
                                        <?= Html::encode($c->complaint_no) ?>
                                        <?php if ($c->type): ?> · <?= Html::encode($c->type->name) ?><?php endif; ?>
                                        <?php if ($c->assignedUnit): ?> · <?= Html::encode($c->assignedUnit->name) ?><?php endif; ?>
                                    </div>
                                </div>
                                <span class="badge text-bg-<?= Complaint::statusColor($c->status) ?> flex-shrink-0"><?= Html::encode($c->statusLabel()) ?></span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
