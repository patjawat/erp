<?php
use app\modules\medsop\assets\MedSopAsset;
use app\modules\medsop\models\Document;
use app\modules\medsop\models\MedSopSetting;
use Da\QrCode\QrCode;
use Da\QrCode\Writer\SvgWriter;
use yii\helpers\Html;
MedSopAsset::register($this);
$this->title = $model->title;
$badge = Document::getStatusBadgeConfigFor($model->status);
$qrCode = new QrCode(Yii::$app->request->absoluteUrl, null, new SvgWriter());
$qrCode->setSize(156)->setMargin(8);
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?><?= Html::encode($model->document_no) ?> · ฉบับที่ <?= number_format($model->current_revision) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?><div class="d-flex flex-wrap align-items-center gap-2"><?= $this->render('_nav', ['access' => $access, 'active' => 'index']) ?><?php if ($access->canUpdate($model)): ?><?= Html::a('<i class="bi bi-people me-1" aria-hidden="true"></i>กำหนดผู้รับเอกสาร', ['audience', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-primary']) ?><?= Html::a('<i class="bi bi-pencil me-1" aria-hidden="true"></i>แก้ไขเอกสาร', ['update', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-primary']) ?><?= Html::beginForm(['publish', 'id' => $model->id], 'post', ['class' => 'd-inline']) ?><?= Html::submitButton('<i class="bi bi-send-check me-1" aria-hidden="true"></i>เผยแพร่เอกสาร', ['class' => 'btn btn-sm btn-primary rounded-pill px-3', 'data-medsop-confirm' => true, 'data-confirm-title' => 'ยืนยันการเผยแพร่', 'data-confirm-text' => 'ระบบจะส่งเอกสารฉบับนี้ให้ผู้รับที่กำหนดไว้', 'data-confirm-label' => 'เผยแพร่เอกสาร']) ?><?= Html::endForm() ?><?php endif; ?></div><?php $this->endBlock(); ?>

<div class="medsop-document-tools mb-3" aria-label="รูปแบบการอ่านเอกสาร">
    <?= Html::a('<i class="bi bi-easel2 me-2" aria-hidden="true"></i>ดูแบบสไลด์', ['slides', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
    <?= Html::a('<i class="bi bi-file-earmark-pdf me-2" aria-hidden="true"></i>ส่งออกคู่มือ PDF', ['manual-pdf', 'id' => $model->id], ['class' => 'btn btn-outline-danger', 'target' => '_blank', 'rel' => 'noopener']) ?>
</div>

<section class="medsop-document-hero mb-4" aria-label="ข้อมูลเอกสาร">
    <div class="medsop-document-hero__main">
        <div class="d-flex flex-wrap align-items-center gap-2 mb-3"><span class="medsop-code"><?= Html::encode($model->document_no) ?></span><span class="badge text-bg-dark"><?= Html::encode($model->document_type) ?></span><span class="<?= Html::encode($badge['class']) ?>"><?= Html::encode($badge['label']) ?></span></div>
        <h1 class="medsop-document-title"><?= Html::encode($model->title) ?></h1>
        <div class="medsop-document-meta mb-3"><span><i class="bi bi-folder2" aria-hidden="true"></i><?= Html::encode($model->category) ?></span><span><i class="bi bi-calendar-check" aria-hidden="true"></i>ทบทวน <?= $model->review_date ? Yii::$app->formatter->asDate($model->review_date, 'long') : 'ยังไม่กำหนด' ?></span><span><i class="bi bi-megaphone" aria-hidden="true"></i><?= Html::encode(MedSopSetting::listValue(MedSopSetting::ANNOUNCEMENT_STATUSES)[$model->announcement_status] ?? $model->announcement_status) ?></span></div>
        <p class="small text-body-secondary mb-2"><strong>คำสำคัญ:</strong> <?= Html::encode($model->keywords) ?></p>
        <?php if ($model->getRelatedLinkItems()): ?><div class="small"><strong>เอกสารที่เกี่ยวข้อง:</strong> <?php foreach ($model->getRelatedLinkItems() as $linkIndex => $link): ?><?= $linkIndex ? ' · ' : '' ?><?= Html::a(Html::encode($link['label'] ?? $link['url']), $link['url'], ['target' => '_blank', 'rel' => 'noopener noreferrer']) ?><?php endforeach; ?></div><?php endif; ?>
        <?php if ($linkedWorkInstructions): ?>
            <nav class="medsop-linked-wi mt-3" aria-label="WI ที่อ้างอิง SOP ฉบับนี้">
                <strong class="d-block mb-2">WI ที่เชื่อมโยงจาก SOP ฉบับนี้</strong>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($linkedWorkInstructions as $workInstruction): ?>
                        <?= Html::a(
                            '<i class="bi bi-link-45deg me-1" aria-hidden="true"></i>'
                                . Html::encode($workInstruction->document_no . ' · ' . $workInstruction->title),
                            ['view', 'id' => $workInstruction->id],
                            ['class' => 'btn btn-sm btn-outline-primary']
                        ) ?>
                    <?php endforeach; ?>
                </div>
            </nav>
        <?php endif; ?>
        <div class="medsop-document-meta"><span><i class="bi bi-diagram-3" aria-hidden="true"></i><?= Html::encode($model->organization ? $model->organization->name : 'ไม่ระบุแผนก') ?></span><span><i class="bi bi-clock" aria-hidden="true"></i>ปรับปรุง <?= Yii::$app->formatter->asDate($model->updated_at, 'medium') ?></span><span>ฉบับที่ <?= number_format($model->current_revision) ?></span></div>
    </div>
    <div class="medsop-document-hero__context"><div class="medsop-qr"><img src="<?= Html::encode($qrCode->writeDataUri()) ?>" width="156" height="156" alt="คิวอาร์โค้ดสำหรับเปิดเอกสารนี้"><span>สแกนเพื่อเปิดอ่านบนมือถือ</span></div><div class="medsop-document-purpose"><h2>วัตถุประสงค์</h2><p><?= nl2br(Html::encode($model->objective)) ?></p><?php if ($model->scope): ?><h2>ขอบเขตการใช้งาน</h2><p><?= nl2br(Html::encode($model->scope)) ?></p><?php endif; ?></div></div>
</section>
<section class="medsop-steps-section" aria-labelledby="timeline-title"><div class="medsop-section-head"><div><h2 id="timeline-title" class="h5 fw-semibold mb-1"><i class="bi bi-layers me-2 text-primary" aria-hidden="true"></i>ขั้นตอนปฏิบัติงานมาตรฐาน</h2><p class="small text-body-secondary mb-0">ปฏิบัติตามลำดับทั้งหมด <?= count($model->steps) ?> ขั้นตอน</p></div></div><ol class="medsop-manual-steps medsop-step-line">
    <?php foreach ($model->steps as $stepIndex => $step): ?><?php $displayOrder = $stepIndex + 1; ?><li class="medsop-manual-step"><div class="medsop-manual-step__head"><span class="medsop-step-number" aria-label="ขั้นตอนที่ <?= $displayOrder ?>"><span class="medsop-step-number__label">ขั้นตอน</span><strong><?= $displayOrder ?></strong></span><div><h3 class="h5 fw-semibold text-break mb-0"><?= Html::encode($step->title) ?></h3></div></div><div class="medsop-manual-step__layout"><div class="medsop-manual-step__content"><?php if ($step->description): ?><p class="text-break mb-3"><?= nl2br(Html::encode($step->description)) ?></p><?php endif; ?><?php if ($step->caution): ?><div class="alert alert-warning mb-3" role="note"><strong class="d-block mb-1"><i class="bi bi-exclamation-triangle me-1" aria-hidden="true"></i>ข้อควรระวัง</strong><p class="text-break mb-0"><?= nl2br(Html::encode($step->caution)) ?></p></div><?php endif; ?><?php if ($step->getRelatedLinkItems()): ?><nav class="medsop-step-links" aria-label="เอกสารที่เกี่ยวข้องกับขั้นตอนที่ <?= $displayOrder ?>"><strong class="d-block mb-2">เอกสารที่เกี่ยวข้อง</strong><div class="d-flex flex-wrap gap-2"><?php foreach ($step->getRelatedLinkItems() as $link): ?><?= Html::a('<i class="bi bi-box-arrow-up-right me-1" aria-hidden="true"></i>' . Html::encode($link['label'] ?? $link['url']), $link['url'], ['class' => 'btn btn-sm btn-outline-primary', 'target' => '_blank', 'rel' => 'noopener noreferrer']) ?><?php endforeach; ?></div></nav><?php endif; ?></div><?php if ($step->media): ?><div class="medsop-step-media"><?php foreach ($step->media as $media): ?><figure><?php if ($media->media_type === 'image'): ?><img src="<?= Html::encode($media->file_path) ?>" alt="ภาพประกอบ <?= Html::encode($step->title) ?>" loading="lazy"><?php else: ?><video controls preload="metadata"><source src="<?= Html::encode($media->file_path) ?>" type="<?= Html::encode($media->mime_type) ?>">เบราว์เซอร์นี้ไม่รองรับวิดีโอ</video><?php endif; ?><figcaption><?= Html::encode($media->file_name) ?></figcaption></figure><?php endforeach; ?></div><?php endif; ?></div></li><?php endforeach; ?>
</ol></section>
<?php if ($assignment && $assignmentEmployee): ?>
    <?= $this->render('_acknowledgement', compact('model', 'assignment', 'assignmentEmployee')) ?>
<?php endif; ?>
