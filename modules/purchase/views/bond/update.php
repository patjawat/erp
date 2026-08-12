<?php

use yii\helpers\Html;
use app\modules\purchase\models\Bond;

/** @var yii\web\View $this */
/** @var app\modules\purchase\models\Bond $model */

$this->title = 'แก้ไขหลักประกัน';
$this->params['breadcrumbs'][] = ['label' => 'ทะเบียนหลักประกัน', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$badge = Bond::statusBadge($model->status);
?>

<?php $this->beginBlock('page-title'); ?>
<h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
    <i class="bi bi-shield-check"></i> <?= Html::encode($this->title) ?>
    <span class="badge text-bg-<?= $badge['color'] ?> align-middle"><?= $badge['label'] ?></span>
</h4>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
<?= Html::encode($model->doc_no ?: '—') ?> · <?= Html::encode($model->title) ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<div class="d-flex flex-wrap gap-2">
    <?php if (!in_array($model->status, [Bond::STATUS_RETURNED, Bond::STATUS_SEIZED, Bond::STATUS_EXEMPT], true)): ?>
        <?= Html::a('<i class="bi bi-box-arrow-up me-1"></i>บันทึกการคืน/การยึด', ['return', 'id' => $model->id], [
            'class' => 'btn btn-sm btn-success rounded-pill px-3',
        ]) ?>
    <?php endif; ?>
    <?= Html::a('<i class="bi bi-arrow-left me-1"></i>กลับทะเบียนหลักประกัน', ['index'], [
        'class' => 'btn btn-sm btn-outline-secondary rounded-pill px-3',
    ]) ?>
</div>
<?php $this->endBlock(); ?>

<?php if ($model->isExpired()): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-octagon me-1"></i>
        หลักประกันใบนี้สิ้นอายุแล้ว <?= abs((int) $model->daysToExpiry()) ?> วัน แต่ยังไม่ได้ปิดเรื่อง
        — ต้องขอให้คู่สัญญาต่ออายุ หรือบันทึกการคืน/การยึดให้ตรงกับที่เกิดขึ้นจริง
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <?= $this->render('_form', ['model' => $model]) ?>
    </div>
</div>
