<?php

use app\components\AppHelper;
use app\components\widgets\DataSummaryWidget;
use app\modules\ha12\models\Ha12Audit;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var Ha12Audit[] $logs */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'HA12-PCT · ร่องรอยการกำกับดูแล';
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode('HA12-PCT') ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>ร่องรอยการกำกับดูแล (audit trail)<?php $this->endBlock(); ?>

<div class="container-fluid px-0">
    <div class="mb-3"><?= $this->render('@app/modules/ha12/menu', ['active' => 'report']) ?></div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h5 fw-semibold mb-0"><i class="bi bi-shield-lock me-1"></i> ร่องรอยการกำกับดูแล</h1>
        <?= Html::a('<i class="bi bi-arrow-left"></i> กลับรายงาน', ['index'], ['class' => 'btn btn-outline-secondary rounded-pill btn-sm']) ?>
    </div>

    <div class="card border shadow-sm">
        <div class="card-body p-0">
            <?php if (!$logs): ?>
                <div class="text-center py-5 text-body-secondary">ยังไม่มีบันทึกเหตุการณ์</div>
            <?php else: ?>
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-body-tertiary"><tr>
                        <th style="width:160px;">เวลา</th><th style="width:130px;">ประเภท</th>
                        <th style="width:130px;">การกระทำ</th><th>รายละเอียด</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td class="small text-body-secondary" style="font-variant-numeric:tabular-nums;"><?= $log->created_at ? AppHelper::convertToThai(substr((string) $log->created_at, 0, 10)) . ' ' . substr((string) $log->created_at, 11, 5) : '' ?></td>
                            <td><span class="badge bg-body-secondary text-body-emphasis"><?= Html::encode($log->entityLabel()) ?></span></td>
                            <td><?= Html::encode($log->actionLabel()) ?></td>
                            <td class="small"><?= Html::encode((string) $log->detail) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php if ($logs): ?><div class="card-footer bg-body-tertiary"><?= DataSummaryWidget::widget(['dataProvider' => $dataProvider]) ?></div><?php endif; ?>
    </div>
</div>
