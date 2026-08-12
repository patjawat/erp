<?php

use app\modules\roster\models\Swap;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\modules\roster\models\Period $period */
/** @var Swap[] $swaps */
/** @var bool $canManage */
/** @var int $pendingCount */

$this->title = 'ใบเปลี่ยนตัวเวร';
$this->params['breadcrumbs'][] = ['label' => 'ตารางเวร', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $period->monthLabel(), 'url' => ['grid', 'id' => $period->id]];
$this->params['breadcrumbs'][] = $this->title;

$waiting = array_filter($swaps, static fn(Swap $s) => $s->status === Swap::STATUS_ACCEPTED);
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-arrow-left-right"></i> <?= Html::encode($this->title) ?>
    </h4>
    <div class="text-body-secondary small">
        <?= Html::encode($period->unitName()) ?> · <?= Html::encode($period->monthLabel()) ?>
        <?php if ($waiting): ?>
            · <span class="text-warning-emphasis fw-semibold">รออนุมัติ <?= count($waiting) ?> ใบ</span>
        <?php endif; ?>
    </div>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/roster/menu', ['active' => 'period', 'pendingCount' => $pendingCount]) ?>
<?php $this->endBlock(); ?>

<div class="card border shadow-sm">
    <div class="card-header bg-body-tertiary d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
        <h6 class="mb-0">
            <i class="bi bi-clock-history"></i> ประวัติการเปลี่ยนตัว
            <span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis"><?= count($swaps) ?></span>
        </h6>
        <?= Html::a('<i class="bi bi-grid-3x3"></i> กลับไปที่ตารางเวร', ['grid', 'id' => $period->id], [
            'class' => 'btn btn-sm btn-outline-secondary',
        ]) ?>
    </div>
    <div class="card-body p-0">
        <?php if (empty($swaps)): ?>
            <div class="text-center py-5">
                <i class="bi bi-arrow-left-right fs-1 text-body-secondary"></i>
                <h6 class="mt-3 mb-1">ยังไม่มีการเปลี่ยนตัวเวร</h6>
                <p class="text-body-secondary small mb-0">
                    เจ้าหน้าที่ยื่นขอแลกเวรได้จากหน้า “เวรของฉัน”
                </p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-body-tertiary">
                        <tr>
                            <th style="width:110px">วันที่เวร</th>
                            <th style="width:120px">ชนิด</th>
                            <th>คนเดิม → คนใหม่</th>
                            <th>เหตุผล</th>
                            <th class="text-center" style="width:150px">สถานะ</th>
                            <th class="text-end" style="width:180px"></th>
                        </tr>
                    </thead>
                    <tbody class="table-group-divider">
                        <?php foreach ($swaps as $swap): ?>
                            <?php
                            $item = $swap->item;
                            $warnings = $swap->warningList();
                            ?>
                            <tr>
                                <td>
                                    <?php if ($item): ?>
                                        <?= Html::encode(date('d/m/', strtotime($item->work_date)) . (date('Y', strtotime($item->work_date)) + 543)) ?>
                                        <span class="badge rounded-pill px-2 <?= $item->shiftCellClass() ?>">
                                            <?= Html::encode($item->shiftShort()) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-body-secondary">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-body-tertiary text-body border">
                                        <?= Html::encode($swap->getTypeLabel()) ?>
                                    </span>
                                </td>
                                <td>
                                    <?= Html::encode($swap->fromEmployee ? $swap->fromEmployee->fullname : '-') ?>
                                    <i class="bi bi-arrow-right text-body-secondary mx-1"></i>
                                    <span class="fw-semibold"><?= Html::encode($swap->toEmployee ? $swap->toEmployee->fullname : '-') ?></span>
                                    <?php if ($warnings): ?>
                                        <div class="small text-danger-emphasis mt-1">
                                            <i class="bi bi-exclamation-triangle"></i> <?= Html::encode(implode(' · ', $warnings)) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="small text-body-secondary"><?= Html::encode((string) $swap->reason) ?></td>
                                <td class="text-center">
                                    <span class="badge bg-<?= $swap->getStatusColor() ?>-subtle text-<?= $swap->getStatusColor() ?>-emphasis">
                                        <?= Html::encode($swap->getStatusLabel()) ?>
                                    </span>
                                    <?php if ($swap->approved_at): ?>
                                        <div class="text-body-secondary small mt-1"><?= Html::encode(substr((string) $swap->approved_at, 0, 16)) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if ($canManage && $swap->status === Swap::STATUS_ACCEPTED): ?>
                                        <button type="button" class="btn btn-sm btn-success swap-decide"
                                                data-id="<?= $swap->id ?>" data-decision="approve">
                                            <i class="bi bi-check-lg"></i> อนุมัติ
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger swap-decide"
                                                data-id="<?= $swap->id ?>" data-decision="reject">
                                            <i class="bi bi-x-lg"></i> ไม่อนุมัติ
                                        </button>
                                    <?php elseif ($swap->status === Swap::STATUS_PENDING): ?>
                                        <span class="text-body-secondary small">รอ<?= Html::encode($swap->toEmployee ? $swap->toEmployee->fname : 'คู่กรณี') ?>ตอบรับ</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$decideUrl = Url::to(['swap-decide']);
$js = <<<JS
jQuery('body').on('click', '.swap-decide', function () {
    var \$btn = jQuery(this);
    var decision = \$btn.data('decision');
    var text = decision === 'approve'
        ? 'อนุมัติการเปลี่ยนตัวนี้? ตารางเวรจะถูกแก้ทันที'
        : 'ไม่อนุมัติใบขอนี้?';
    if (!window.confirm(text)) { return; }
    \$btn.prop('disabled', true);
    jQuery.post('{$decideUrl}', { swap_id: \$btn.data('id'), decision: decision }, function (res) {
        \$btn.prop('disabled', false);
        if (res.status === 'success') {
            if (typeof success === 'function') { success(res.message); }
            window.location.reload();
        } else if (typeof warning === 'function') {
            warning(res.message);
        }
    });
});
JS;
$this->registerJs($js);
?>
