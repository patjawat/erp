<?php

use app\modules\iacRisk\models\ServiceProcessVersion;
use yii\helpers\Html;

$this->title = 'กระบวนงาน';
$labels = ServiceProcessVersion::reviewLabels();
$badge = ['pending' => 'warning', 'retained' => 'success', 'modified' => 'info', 'new' => 'primary', 'retired' => 'secondary'];
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>ทะเบียนกระบวนงานจาก Service Profile และผลทบทวนประจำปี<?php $this->endBlock(); ?>

<?= $this->render('_context', ['context' => $context]) ?>
<div class="mb-3"><?= $this->render('@app/modules/iacRisk/menu', ['active' => 'processes', 'context' => $context]) ?></div>

<?php if (!$profiles): ?>
<section class="card bg-body border shadow-sm"><div class="card-body text-center py-5"><h2 class="h5 fw-semibold">ไม่พบ Service Profile ปี <?= (int) $fiscalYear ?></h2><p class="text-body-secondary mb-3">ต้องสร้าง Service Profile ของหน่วยงานก่อนจึงจะจัดทำทะเบียนกระบวนงานได้</p><?= Html::a('ตรวจข้อมูล Service Profile', array_merge(['/iac-risk/default/service-profile'], \app\modules\iacRisk\services\ContextService::query($context)), ['class' => 'btn btn-primary']) ?></div></section>
<?php else: ?>
<section class="card bg-body border shadow-sm">
    <div class="card-header bg-body-tertiary border-bottom d-flex flex-column flex-md-row justify-content-between gap-3 py-3">
        <div><h2 class="h5 fw-semibold mb-1"><?= $profile ? Html::encode($profile->owner_name_snapshot) : 'กระบวนงานทุกหน่วยงาน' ?></h2><p class="small text-body-secondary mb-0">ปีงบประมาณ <?= (int) $fiscalYear ?> · ใช้ Service Profile revision ล่าสุดของแต่ละหน่วยงาน</p></div>
        <?php $processSection = $profile?->getSections()->andWhere(['or', ['section_code' => 'key_processes'], ['block_type' => 'key_process_table']])->one(); ?>
        <?php if ($profile && $canEdit && $processSection): ?><?= Html::a('<i class="bi bi-pencil me-1"></i> แก้ไขชื่อและวัตถุประสงค์', ['/service-profile/default/update-section', 'id' => $processSection->id], ['class' => 'btn btn-outline-primary align-self-start', 'target' => '_blank', 'rel' => 'noopener']) ?><?php endif; ?>
    </div>
    <div class="card-body p-0">
        <div class="d-none d-lg-block"><table class="table align-middle mb-0"><thead><tr><th class="text-center">ลำดับ</th><th>ชื่อกระบวนงาน</th><th>วัตถุประสงค์</th><th>ผลทบทวน</th><th class="text-end">จัดการ</th></tr></thead><tbody>
        <?php foreach ($versions as $version): ?><?php $rowCanEdit = $canEditByProfile[(int) $version->service_profile_id] ?? false; ?><tr><td class="text-center font-monospace"><?= (int) ($version->sequence / 10) ?></td><td><div class="fw-semibold"><?= Html::encode($version->name) ?></div><?php if (!$profile): ?><div class="small text-body-secondary"><?= Html::encode($version->profile?->owner_name_snapshot ?? '-') ?></div><?php endif; ?></td><td><?= nl2br(Html::encode($version->objective ?: '—')) ?></td><td><span class="badge bg-<?= $badge[$version->review_status] ?? 'secondary' ?>-subtle text-<?= $badge[$version->review_status] ?? 'secondary' ?>-emphasis"><?= Html::encode($labels[$version->review_status] ?? $version->review_status) ?></span><?php if ($version->review_note): ?><div class="small text-body-secondary mt-1"><?= Html::encode($version->review_note) ?></div><?php endif; ?></td><td class="text-end"><?php if ($rowCanEdit): ?><button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#review-process-<?= (int) $version->id ?>" aria-expanded="false">ทบทวน</button><?php endif; ?></td></tr>
        <?php if ($rowCanEdit): ?><tr class="collapse" id="review-process-<?= (int) $version->id ?>"><td colspan="5" class="bg-body-tertiary"><form method="post" action="<?= \yii\helpers\Url::to(array_merge(['/iac-risk/default/review-process', 'id' => $version->id], \app\modules\iacRisk\services\ContextService::query($context))) ?>"><input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>"><div class="row g-3 align-items-end"><div class="col-lg-4"><label class="form-label fw-semibold">ผลทบทวน</label><?= Html::dropDownList('review_status', $version->review_status, $labels, ['class' => 'form-select']) ?></div><div class="col-lg-6"><label class="form-label fw-semibold">หมายเหตุ</label><?= Html::textInput('review_note', $version->review_note, ['class' => 'form-control', 'maxlength' => 1000]) ?></div><div class="col-lg-2 d-grid"><?= Html::submitButton('บันทึกผลทบทวน', ['class' => 'btn btn-primary']) ?></div></div></form></td></tr><?php endif; ?>
        <?php endforeach; ?>
        <?php if (!$versions): ?><tr><td colspan="5" class="text-center py-5"><div class="fw-semibold">ยังไม่มีรายการกระบวนงาน</div><div class="small text-body-secondary mt-1">เพิ่มชื่อและวัตถุประสงค์ในหัวข้อ “กระบวนการหลักของหน่วยงาน” ของ Service Profile</div></td></tr><?php endif; ?>
        </tbody></table></div>
        <div class="d-lg-none list-group list-group-flush"><?php foreach ($versions as $version): ?><div class="list-group-item bg-body p-3"><div class="d-flex justify-content-between gap-2"><span class="fw-semibold"><?= Html::encode($version->name) ?></span><span class="badge bg-<?= $badge[$version->review_status] ?? 'secondary' ?>-subtle text-<?= $badge[$version->review_status] ?? 'secondary' ?>-emphasis align-self-start"><?= Html::encode($labels[$version->review_status] ?? $version->review_status) ?></span></div><div class="small text-body-secondary mt-2"><?= Html::encode($version->objective ?: 'ยังไม่ระบุวัตถุประสงค์') ?></div></div><?php endforeach; ?><?php if (!$versions): ?><div class="list-group-item text-center py-5 text-body-secondary">ยังไม่มีรายการกระบวนงาน</div><?php endif; ?></div>
    </div>
</section>
<?php endif; ?>
