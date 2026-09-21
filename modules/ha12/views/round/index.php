<?php

use app\components\widgets\DataSummaryWidget;
use app\modules\ha12\models\Ha12Round;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var Ha12Round[] $rounds */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array<int,array{total:int,published:int}> $progress */
/** @var int $fiscalYear */
/** @var int[] $years */
/** @var bool $isManager */

$this->title = 'HA12-PCT · รอบประเมิน PCT';
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode('HA12-PCT') ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>รอบสรุปและประเมินโดย PCT · ปีงบ <?= $fiscalYear ?><?php $this->endBlock(); ?>

<div class="container-fluid px-0">
    <div class="mb-3"><?= $this->render('@app/modules/ha12/menu', ['active' => 'round']) ?></div>

    <?php foreach (['success' => 'success', 'error' => 'danger'] as $flash => $tone): ?>
        <?php if ($msg = Yii::$app->session->getFlash($flash)): ?>
            <div class="alert alert-<?= $tone ?> alert-dismissible fade show"><?= Html::encode($msg) ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
    <?php endforeach; ?>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <?= Html::beginForm(['index'], 'get', ['class' => 'd-flex align-items-center gap-2']) ?>
            <label class="small text-body-secondary mb-0">ปีงบ</label>
            <?= Html::dropDownList('fy', $fiscalYear, array_combine($years, $years), ['class' => 'form-select form-select-sm', 'style' => 'width:auto', 'onchange' => 'this.form.submit()']) ?>
        <?= Html::endForm() ?>
        <?php if ($isManager): ?>
            <?= Html::a('<i class="bi bi-plus-lg me-1"></i> สร้างรอบประเมิน', ['create', 'fy' => $fiscalYear, 'title' => 'สร้างรอบประเมิน'], ['class' => 'btn btn-success rounded-pill open-modal', 'data' => ['size' => 'modal-lg']]) ?>
        <?php endif; ?>
    </div>

    <div class="card border shadow-sm">
        <div class="card-body p-0">
            <?php if (!$rounds): ?>
                <div class="text-center py-5"><div class="fw-semibold mb-1">ยังไม่มีรอบประเมินในปีงบนี้</div><div class="text-body-secondary small"><?= $isManager ? 'กด “สร้างรอบประเมิน” เพื่อเริ่ม' : 'รอผู้ดูแล/ทีมคุณภาพเปิดรอบ' ?></div></div>
            <?php else: ?>
                <div class="table-responsive d-none d-lg-block">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-body-tertiary"><tr>
                            <th>รอบ</th><th style="width:16rem;">ขอบเขต</th>
                            <th style="width:160px;">ความคืบหน้า</th><th style="width:110px;" class="text-center">สถานะ</th>
                            <th style="width:90px;" class="text-center">เปิด</th>
                        </tr></thead>
                        <tbody>
                        <?php foreach ($rounds as $r): $p = $progress[$r->id]; ?>
                            <tr>
                                <td><?= Html::a(Html::encode($r->displayTitle()), ['view', 'id' => $r->id], ['class' => 'fw-semibold text-decoration-none']) ?></td>
                                <td class="text-body-secondary"><?= Html::encode($r->scopeUnit->name ?? 'ทั้งโรงพยาบาล') ?></td>
                                <td>
                                    <div class="progress" style="height:8px;">
                                        <div class="progress-bar bg-success" style="width: <?= $p['total'] ? round($p['published'] / $p['total'] * 100) : 0 ?>%"></div>
                                    </div>
                                    <span class="small text-body-secondary"><?= $p['published'] ?>/<?= $p['total'] ?> เผยแพร่</span>
                                </td>
                                <td class="text-center">
                                    <?php if ($r->isClosed()): ?><span class="badge bg-secondary-subtle text-secondary-emphasis">ปิดรอบ</span>
                                    <?php else: ?><span class="badge bg-success-subtle text-success-emphasis">เปิดอยู่</span><?php endif; ?>
                                </td>
                                <td class="text-center"><?= Html::a('<i class="bi bi-box-arrow-in-right"></i>', ['view', 'id' => $r->id], ['class' => 'btn btn-sm btn-outline-primary', 'title' => 'เปิดรอบ']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <ul class="list-group list-group-flush d-lg-none">
                    <?php foreach ($rounds as $r): $p = $progress[$r->id]; ?>
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <?= Html::a(Html::encode($r->displayTitle()), ['view', 'id' => $r->id], ['class' => 'fw-semibold text-decoration-none']) ?>
                                <?php if ($r->isClosed()): ?><span class="badge bg-secondary-subtle text-secondary-emphasis">ปิด</span><?php else: ?><span class="badge bg-success-subtle text-success-emphasis">เปิด</span><?php endif; ?>
                            </div>
                            <div class="text-body-secondary small mt-1"><?= Html::encode($r->scopeUnit->name ?? 'ทั้งโรงพยาบาล') ?> · <?= $p['published'] ?>/<?= $p['total'] ?> เผยแพร่</div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        <?php if ($rounds): ?><div class="card-footer bg-body-tertiary"><?= DataSummaryWidget::widget(['dataProvider' => $dataProvider]) ?></div><?php endif; ?>
    </div>
</div>
