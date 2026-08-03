<?php
use app\modules\medsop\assets\MedSopAsset;
use app\widgets\TomSelectWidget;
use yii\helpers\Html;
use yii\helpers\Url;

MedSopAsset::register($this);
$this->title = 'ตั้งค่า MedSOP';
$personLabel = static function ($employeeId) use ($responsibleEmployees) {
    $employee = $responsibleEmployees[(int) $employeeId] ?? null;
    if (!$employee) return '<span class="text-body-secondary">ยังไม่ได้กำหนด</span>';
    $position = method_exists($employee, 'positionName') ? $employee->positionName() : null;
    return '<strong class="d-block">' . Html::encode($employee->fullname()) . '</strong>'
        . ($position ? '<span class="small text-body-secondary">' . Html::encode($position) . '</span>' : '');
};
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>กำหนดรหัสเอกสาร ประเภท หมวดหมู่ และสถานะประกาศใช้<?php $this->endBlock(); ?>
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
<?php // section "หน่วยงาน" และ "ทีมประสาน" ถูกซ่อน — ย้ายการกำหนดอักษรย่อ/เปิด-ปิดใช้ ไปทะเบียนหน่วยงานกลาง (/settings/org-unit) เพื่อรวมเป็นจุดเดียว ?>
<section class="card shadow-sm">
    <div class="card-body">
        <div class="alert alert-info d-flex align-items-start gap-2 mb-0" role="note">
            <i class="bi bi-info-circle-fill mt-1" aria-hidden="true"></i>
            <div>
                <strong class="d-block">หน่วยงานและทีมประสานย้ายไปทะเบียนหน่วยงานกลาง</strong>
                <span class="small">การกำหนดอักษรย่อและเปิด-ปิดใช้หน่วยงาน/ทีมประสาน ย้ายไปที่ <strong>ทะเบียนหน่วยงานกลาง</strong> (ตั้งค่าระบบ) เพื่อรวมเป็นจุดเดียว ไม่ให้ซ้ำซ้อน</span>
            </div>
        </div>
    </div>
    <div class="card-footer bg-body d-flex justify-content-end py-3"><?= Html::submitButton('<i class="bi bi-check-lg me-1"></i>บันทึกการตั้งค่า', ['class' => 'btn btn-primary']) ?></div>
</section>
<?= Html::endForm() ?>

<?php $this->registerJs(<<<'JS'
const settingError = document.querySelector('[data-setting-error]');
if (settingError) settingError.focus();
document.querySelectorAll('[data-setting-search]').forEach(function (search) {
  search.addEventListener('input', function () {
    const query = this.value.trim().toLocaleLowerCase('th');
    const type = this.dataset.settingSearch;
    document.querySelectorAll('[data-setting-row="' + type + '"]').forEach(function (row) {
      row.hidden = query !== '' && !row.dataset.search.includes(query);
    });
  });
});
JS); ?>
