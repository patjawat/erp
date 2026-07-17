<?php
use app\modules\medsop\assets\MedSopAsset;
use app\modules\medsop\models\Document;
use yii\helpers\Html;
MedSopAsset::register($this);
$this->title = $model->title;
$badge = Document::getStatusBadgeConfigFor($model->status);
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?><?= Html::encode($model->document_no) ?> · ฉบับที่ <?= number_format($model->current_revision) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?><div class="d-flex flex-wrap gap-2"><?= Html::a('กลับคลังเอกสาร', ['index'], ['class' => 'btn btn-sm btn-outline-secondary']) ?><?php if ($access->canUpdate($model)): ?><?= Html::a('แก้ไขเอกสาร', ['update', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-primary']) ?><?php endif; ?></div><?php $this->endBlock(); ?>

<div class="medsop-detail-meta">
    <span class="medsop-type-badge"><?= Html::encode($model->document_type) ?></span>
    <span class="<?= Html::encode($badge['class']) ?>"><?= Html::encode($badge['label']) ?></span>
    <span><?= Html::encode($model->organization ? $model->organization->name : 'ไม่ระบุแผนก') ?></span>
    <span>ปรับปรุง <?= Yii::$app->formatter->asDate($model->updated_at, 'medium') ?></span>
</div>
<section class="surface-card mb-3"><div class="surface-card__body"><div class="medsop-overview"><div><h2>วัตถุประสงค์</h2><p><?= nl2br(Html::encode($model->objective)) ?></p></div><div><h2>ขอบเขตการใช้งาน</h2><p><?= $model->scope ? nl2br(Html::encode($model->scope)) : '<span class="text-muted">ไม่ระบุ</span>' ?></p></div></div></div></section>
<section class="surface-card" aria-labelledby="timeline-title"><div class="surface-card__head"><h2 id="timeline-title" class="surface-card__title">ขั้นตอนปฏิบัติงาน</h2></div><div class="surface-card__body"><ol class="medsop-timeline">
    <?php foreach ($model->steps as $step): ?><li><span class="medsop-timeline__number"><?= number_format($step->step_order) ?></span><div><h3><?= Html::encode($step->title) ?></h3><?php if ($step->description): ?><p><?= nl2br(Html::encode($step->description)) ?></p><?php endif; ?><?php if ($step->caution): ?><div class="medsop-caution"><strong>ข้อควรระวัง</strong><p><?= nl2br(Html::encode($step->caution)) ?></p></div><?php endif; ?></div></li><?php endforeach; ?>
</ol></div></section>
