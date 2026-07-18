<?php
use app\modules\medsop\assets\MedSopAsset;
use yii\helpers\Html;

MedSopAsset::register($this);
$this->title = 'ตั้งค่า MedSOP';
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>กำหนดรหัสเอกสาร อักษรย่อหน่วยงาน และทีมประสาน<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?><?= $this->render('_nav', ['access' => $access, 'active' => 'setting']) ?><?php $this->endBlock(); ?>

<?= Html::beginForm(['setting'], 'post', ['id' => 'medsop-setting-form']) ?>
<section class="card shadow-sm mb-3" aria-labelledby="code-setting-title">
    <div class="card-header bg-body-tertiary py-3"><h2 id="code-setting-title" class="h6 fw-semibold mb-0">รูปแบบรหัสเอกสาร</h2></div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12 col-md-3"><label class="form-label" for="sop-prefix">อักษรย่อ SOP</label><?= Html::textInput('settings[sop_prefix]', $sopPrefix, ['id' => 'sop-prefix', 'class' => 'form-control text-uppercase', 'maxlength' => 10, 'required' => true, 'placeholder' => 'SP']) ?></div>
            <div class="col-12 col-md-3"><label class="form-label" for="wi-prefix">อักษรย่อ WI</label><?= Html::textInput('settings[wi_prefix]', $wiPrefix, ['id' => 'wi-prefix', 'class' => 'form-control text-uppercase', 'maxlength' => 10, 'required' => true, 'placeholder' => 'WI']) ?></div>
            <div class="col-12 col-md-6"><label class="form-label" for="code-pattern">รูปแบบรหัส</label><?= Html::textInput('settings[code_pattern]', $codePattern, ['id' => 'code-pattern', 'class' => 'form-control', 'required' => true]) ?><div class="form-text">ตัวแปรที่ใช้ได้: {type} {org} {year} {sequence}</div></div>
        </div>
        <div class="alert alert-secondary d-flex flex-wrap justify-content-between gap-2 mt-3 mb-0" role="status"><span>ตัวอย่างรหัส</span><strong data-code-preview>SP-OPD-2569-0001</strong></div>
    </div>
</section>
<section class="card shadow-sm mb-3" aria-labelledby="catalog-setting-title">
    <div class="card-header bg-body-tertiary py-3"><h2 id="catalog-setting-title" class="h6 fw-semibold mb-0">หมวดหมู่และสถานะประกาศใช้</h2></div>
    <div class="card-body"><div class="row g-3">
        <div class="col-12 col-lg-6"><label class="form-label" for="document-categories">หมวดหมู่เอกสาร</label><?= Html::textarea('settings[document_categories]', implode("\n", $categories), ['id' => 'document-categories', 'class' => 'form-control', 'rows' => 5, 'required' => true]) ?><div class="form-text">หนึ่งหมวดหมู่ต่อหนึ่งบรรทัด และเรียงตามลำดับที่ต้องการแสดงในฟอร์ม</div></div>
        <div class="col-12 col-lg-6"><label class="form-label" for="announcement-statuses">สถานะประกาศใช้เอกสาร</label><?= Html::textarea('settings[announcement_statuses]', implode("\n", array_values($announcementStatuses)), ['id' => 'announcement-statuses', 'class' => 'form-control', 'rows' => 5, 'required' => true]) ?><div class="form-text">หนึ่งสถานะต่อหนึ่งบรรทัด และเรียงตามลำดับที่ต้องการแสดงในฟอร์ม</div></div>
    </div></div>
</section>
<section class="card shadow-sm" aria-labelledby="org-setting-title">
    <div class="card-header bg-body-tertiary py-3"><h2 id="org-setting-title" class="h6 fw-semibold mb-0">อักษรย่อหน่วยงานและทีมประสาน</h2></div>
    <div class="list-group list-group-flush">
        <?php foreach ($organizations as $organization): $setting = $organizationSettings[$organization->id] ?? null; $fieldPrefix = 'organization-' . (int) $organization->id; ?>
            <div class="list-group-item py-3">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-lg-3"><strong class="text-break"><?= Html::encode($organization->name) ?></strong></div>
                    <div class="col-12 col-sm-5 col-lg-2"><label class="form-label" for="<?= $fieldPrefix ?>-code">อักษรย่อ</label><?= Html::textInput("organizations[{$organization->id}][code]", $setting->code ?? '', ['id' => $fieldPrefix . '-code', 'class' => 'form-control text-uppercase', 'maxlength' => 20, 'placeholder' => 'เช่น OPD']) ?></div>
                    <div class="col-12 col-sm-7 col-lg-5"><label class="form-label" for="<?= $fieldPrefix ?>-team">ทีมประสาน</label><?= Html::textInput("organizations[{$organization->id}][coordinator_team]", $setting->coordinator_team ?? '', ['id' => $fieldPrefix . '-team', 'class' => 'form-control', 'maxlength' => 255, 'placeholder' => 'ชื่อทีม/ผู้ประสานงาน เช่น PCT']) ?></div>
                    <div class="col-12 col-lg-2"><?= Html::hiddenInput("organizations[{$organization->id}][active]", 0) ?><div class="form-check form-switch mb-2"><?= Html::checkbox("organizations[{$organization->id}][active]", $setting ? (bool) $setting->active : true, ['id' => $fieldPrefix . '-active', 'value' => 1, 'class' => 'form-check-input']) ?><label class="form-check-label" for="<?= $fieldPrefix ?>-active">เปิดใช้งาน</label></div></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="card-footer bg-body d-flex justify-content-end py-3"><?= Html::submitButton('<i class="bi bi-check-lg me-1"></i>บันทึกการตั้งค่า', ['class' => 'btn btn-primary']) ?></div>
</section>
<?= Html::endForm() ?>
