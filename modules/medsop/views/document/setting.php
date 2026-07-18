<?php
use app\modules\medsop\assets\MedSopAsset;
use app\widgets\TomSelectWidget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

MedSopAsset::register($this);
$this->title = 'ตั้งค่า MedSOP';
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>กำหนดรหัสเอกสาร อักษรย่อหน่วยงาน และทีมประสาน<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?><?= $this->render('_nav', ['access' => $access, 'active' => 'setting']) ?><?php $this->endBlock(); ?>

<?= Html::beginForm(['setting'], 'post', ['id' => 'medsop-setting-form']) ?>
<?php if (!empty($saveError)): ?>
    <div class="alert alert-danger d-flex align-items-start gap-2" role="alert" tabindex="-1" data-setting-error>
        <i class="bi bi-exclamation-triangle-fill mt-1" aria-hidden="true"></i>
        <div><strong class="d-block">ยังบันทึกการตั้งค่าไม่ได้</strong><span><?= Html::encode($saveError) ?></span></div>
    </div>
<?php endif; ?>
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
        <div class="col-12"><label class="form-label" for="document-types">ประเภทเอกสาร</label><?= Html::textarea('settings[document_types]', implode("\n", array_map(static function ($code, $label) { return $code . '|' . $label; }, array_keys($documentTypes), array_values($documentTypes))), ['id' => 'document-types', 'class' => 'form-control font-monospace', 'rows' => 4, 'required' => true]) ?><div class="form-text">หนึ่งประเภทต่อหนึ่งบรรทัด รูปแบบ <code>รหัส|ชื่อที่แสดง</code> เช่น <code>SOP|SOP</code> เพิ่ม แก้ไข หรือลบรายการที่ยังไม่มีเอกสารใช้งานได้</div></div>
        <div class="col-12 col-lg-6"><label class="form-label" for="document-categories">หมวดหมู่เอกสาร</label><?= Html::textarea('settings[document_categories]', implode("\n", $categories), ['id' => 'document-categories', 'class' => 'form-control', 'rows' => 5, 'required' => true]) ?><div class="form-text">หนึ่งหมวดหมู่ต่อหนึ่งบรรทัด และเรียงตามลำดับที่ต้องการแสดงในฟอร์ม</div></div>
        <div class="col-12 col-lg-6"><label class="form-label" for="announcement-statuses">สถานะประกาศใช้เอกสาร</label><?= Html::textarea('settings[announcement_statuses]', implode("\n", array_values($announcementStatuses)), ['id' => 'announcement-statuses', 'class' => 'form-control', 'rows' => 5, 'required' => true]) ?><div class="form-text">หนึ่งสถานะต่อหนึ่งบรรทัด และเรียงตามลำดับที่ต้องการแสดงในฟอร์ม</div></div>
    </div></div>
</section>
<section class="card shadow-sm" aria-labelledby="org-setting-title">
    <div class="card-header bg-body-tertiary py-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2"><h2 id="org-setting-title" class="h6 fw-semibold mb-0">อักษรย่อ หมวดหมู่ และทีมประสานของหน่วยงาน</h2><div class="search-input-wrap" style="min-width:min(100%, 22rem)"><i class="bi bi-search search-input__icon" aria-hidden="true"></i><input type="search" class="form-control form-control-input" placeholder="ค้นหาชื่อหน่วยงาน" aria-label="ค้นหาชื่อหน่วยงาน" data-organization-search></div></div>
    <div class="list-group list-group-flush">
        <?php foreach ($organizations as $organization): $setting = $organizationSettings[$organization->id] ?? null; $fieldPrefix = 'organization-' . (int) $organization->id; ?>
            <div class="list-group-item py-3" data-organization-row data-search="<?= Html::encode(mb_strtolower($organization->name)) ?>">
                <div class="row g-3 align-items-end">
                    <div class="col-12"><strong class="text-break" style="padding-inline-start:<?= max(0, (int) $organization->lvl - 1) * 1.25 ?>rem"><?= Html::encode($organization->name) ?></strong></div>
                    <div class="col-12 col-md-3"><label class="form-label" for="<?= $fieldPrefix ?>-code">อักษรย่อ</label><?= Html::textInput("organizations[{$organization->id}][code]", $setting->code ?? '', ['id' => $fieldPrefix . '-code', 'class' => 'form-control text-uppercase', 'maxlength' => 20, 'placeholder' => 'เช่น OPD']) ?></div>
                    <div class="col-12 col-md-9"><label class="form-label" for="<?= $fieldPrefix ?>-categories">หมวดหมู่เอกสารของหน่วยงาน</label><?= Html::textarea("organizations[{$organization->id}][document_categories]", implode("\n", json_decode((string) ($setting->document_categories ?? ''), true) ?: []), ['id' => $fieldPrefix . '-categories', 'class' => 'form-control', 'rows' => 2, 'placeholder' => 'เว้นว่างเพื่อใช้หมวดหมู่กลาง หรือระบุหนึ่งหมวดหมู่ต่อหนึ่งบรรทัด']) ?></div>
                    <div class="col-12 col-lg-4"><label class="form-label" for="<?= $fieldPrefix ?>-team-group">ทีมประสานจากงานบุคลากร</label><?= TomSelectWidget::widget(['name' => "organizations[{$organization->id}][coordinator_team_group_id]", 'value' => $setting->coordinator_team_group_id ?? '', 'id' => $fieldPrefix . '-team-group', 'items' => ['' => ''] + ArrayHelper::map($teamGroups, 'id', 'title'), 'options' => ['class' => 'form-select'], 'clientOptions' => ['placeholder' => 'ค้นหาและเลือกทีมประสาน', 'allowEmptyOption' => true]]) ?></div>
                    <div class="col-12 col-lg-4"><label class="form-label" for="<?= $fieldPrefix ?>-employee">ผู้ประสานงานจากงานบุคลากร</label><?= TomSelectWidget::widget(['name' => "organizations[{$organization->id}][coordinator_employee_id]", 'value' => $setting->coordinator_employee_id ?? '', 'id' => $fieldPrefix . '-employee', 'items' => !empty($setting->coordinator_employee_id) && isset($coordinatorEmployees[$setting->coordinator_employee_id]) ? ['' => '', $setting->coordinator_employee_id => $coordinatorEmployees[$setting->coordinator_employee_id]->fullname()] : ['' => ''], 'options' => ['class' => 'form-select'], 'loadUrl' => Url::to(['coordinator-employees']), 'clientOptions' => ['valueField' => 'id', 'labelField' => 'fullname', 'searchField' => ['fullname', 'position_name'], 'placeholder' => 'พิมพ์ชื่อหรือนามสกุล', 'allowEmptyOption' => true]]) ?></div>
                    <div class="col-12 col-lg-3"><label class="form-label" for="<?= $fieldPrefix ?>-team">ชื่อเรียกเพิ่มเติม</label><?= Html::textInput("organizations[{$organization->id}][coordinator_team]", $setting->coordinator_team ?? '', ['id' => $fieldPrefix . '-team', 'class' => 'form-control', 'maxlength' => 255, 'placeholder' => 'เช่น PCT']) ?></div>
                    <div class="col-12 col-lg-1"><?= Html::hiddenInput("organizations[{$organization->id}][active]", 0) ?><div class="form-check form-switch mb-2"><?= Html::checkbox("organizations[{$organization->id}][active]", $setting ? (bool) $setting->active : true, ['id' => $fieldPrefix . '-active', 'value' => 1, 'class' => 'form-check-input']) ?><label class="form-check-label" for="<?= $fieldPrefix ?>-active">ใช้</label></div></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="card-footer bg-body d-flex justify-content-end py-3"><?= Html::submitButton('<i class="bi bi-check-lg me-1"></i>บันทึกการตั้งค่า', ['class' => 'btn btn-primary']) ?></div>
</section>
<?= Html::endForm() ?>

<?php $this->registerJs(<<<'JS'
const settingError = document.querySelector('[data-setting-error]');
if (settingError) settingError.focus();
const organizationSearch = document.querySelector('[data-organization-search]');
if (organizationSearch) {
  organizationSearch.addEventListener('input', function () {
    const query = this.value.trim().toLocaleLowerCase('th');
    document.querySelectorAll('[data-organization-row]').forEach(function (row) {
      row.hidden = query !== '' && !row.dataset.search.includes(query);
    });
  });
}
JS); ?>
