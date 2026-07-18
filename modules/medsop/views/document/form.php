<?php
use app\modules\medsop\assets\MedSopAsset;
use app\modules\medsop\models\Document;
use app\components\AppHelper;
use app\widgets\datepicker\DatepickerThai;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\ActiveForm;

MedSopAsset::register($this);
$this->title = $model->isNewRecord ? 'สร้างเอกสารคุณภาพใหม่' : 'แก้ไขเอกสารคุณภาพ';
$organizationItems = ArrayHelper::map($organizations, 'id', static function ($organization) { return str_repeat('– ', max(0, (int) $organization->lvl - 1)) . $organization->name; });
$relatedDocumentItems = ArrayHelper::map($relatedDocuments, 'id', static function (Document $document) {
    $status = Document::statusOptions()[$document->status] ?? $document->status;
    return $document->document_type . ' · ' . $document->document_no . ' · ' . $document->title . ' · ' . $status;
});
$hasAvailableSop = (bool) array_filter($relatedDocuments, static function (Document $document): bool {
    return $document->document_type === Document::TYPE_SOP;
});
$relatedDocumentId = static function (array $link): int {
    if (!empty($link['document_id'])) return (int) $link['document_id'];
    $query = [];
    parse_str((string) parse_url((string) ($link['url'] ?? ''), PHP_URL_QUERY), $query);
    return (int) ($query['id'] ?? 0);
};
$stepRows = $stepRows ?: [['title' => '', 'description' => '', 'caution' => '', 'related_links' => []]];
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>ระบุข้อมูลหลักและเรียงขั้นตอนตามลำดับการปฏิบัติงาน<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?><div class="d-flex flex-wrap gap-2"><?= $this->render('_nav', ['active' => 'index']) ?></div><?php $this->endBlock(); ?>

<?php $form = ActiveForm::begin(['id' => 'medsop-document-form', 'options' => ['data-medsop-form' => true, 'enctype' => 'multipart/form-data']]); ?>
<section class="medsop-form-section mb-4" aria-labelledby="document-master-title">
    <div class="medsop-form-section__head"><h2 id="document-master-title" class="h6 fw-semibold mb-0"><i class="bi bi-grid me-2 text-primary" aria-hidden="true"></i>ข้อมูลทั่วไปและการควบคุมเอกสาร</h2></div>
    <div class="card-body"><div class="medsop-document-order mb-3" role="note" aria-labelledby="document-order-title">
        <div class="medsop-document-order__icon" aria-hidden="true"><i class="bi bi-diagram-3"></i></div>
        <div><strong id="document-order-title" class="d-block mb-1">เริ่มจาก SOP แล้วจึงสร้าง WI</strong><p class="mb-0">SOP กำหนดภาพรวม จุดเริ่มต้น จุดสิ้นสุด ผู้รับผิดชอบ และเหตุผลของกระบวนการ ส่วน WI อธิบายวิธีปฏิบัติแบบทีละขั้นตอน ดังนั้น WI ทุกฉบับต้องอ้างอิง SOP หลักที่สร้างไว้แล้ว</p></div>
    </div><div class="row g-3">
        <div class="col-12 col-md-4"><label class="form-label" for="document-no-preview" data-document-code-label><?= Html::encode(($model->document_type ?: Document::TYPE_SOP) . ' Code') ?></label><?= Html::textInput(null, $model->document_no, ['id' => 'document-no-preview', 'class' => 'form-control medsop-code-input', 'placeholder' => 'ระบบสร้างรหัสให้อัตโนมัติเมื่อบันทึก', 'readonly' => true, 'aria-describedby' => 'document-code-help']) ?><div class="form-text" id="document-code-help">รหัสจะสร้างจากประเภทเอกสาร อักษรย่อหน่วยงาน ปี และเลขลำดับ</div></div>
        <div class="col-12 col-md-4" data-flow-category><?= $form->field($model, 'category')->label('หมวดหมู่ของหน่วยงาน')->dropDownList(array_combine($categories, $categories), ['prompt' => 'เลือกหมวดหมู่ของหน่วยงาน', 'class' => 'form-select', 'required' => true]) ?></div>
        <div class="col-12 col-md-4"><?= $form->field($model, 'announcement_status')->label('สถานะประกาศใช้เอกสาร')->dropDownList($announcementStatuses, ['prompt' => 'เลือกสถานะประกาศใช้', 'class' => 'form-select', 'required' => true]) ?></div>
        <div class="col-12 col-md-8"><?= $form->field($model, 'keywords')->label('คำสำคัญสำหรับการสืบค้น (Keywords)')->textInput(['class' => 'form-control', 'required' => true, 'placeholder' => 'เช่น ความปลอดภัย, ผู้ป่วย, ห้องฉุกเฉิน'])->hint('คั่นคำสำคัญด้วยเครื่องหมายจุลภาค เพื่อช่วยให้ค้นหาเอกสารได้ง่ายขึ้น') ?></div>
        <div class="col-12 col-md-4"><label class="form-label" for="document-review_date">วันที่ต้องทบทวนตรวจสอบ</label><?= DatepickerThai::widget(['name' => 'Document[review_date]', 'value' => $model->review_date ? AppHelper::convertToThai($model->review_date) : '', 'options' => ['id' => 'document-review_date', 'class' => 'form-control', 'autocomplete' => 'off', 'placeholder' => 'วว/ดด/พ.ศ.']]) ?></div>
        <div class="col-12 col-lg-6"><label class="form-label" for="cover-image">รูปปกเอกสาร</label><input id="cover-image" class="form-control" type="file" name="cover_image" accept="image/jpeg,image/png,image/webp"><div class="form-text">JPG, PNG หรือ WebP ขนาดไม่เกิน 10 MB ใช้แสดงในคลังเอกสาร</div><?php if ($model->cover_image): ?><img class="medsop-cover-preview mt-2" src="<?= Html::encode($model->cover_image) ?>" alt="รูปปกปัจจุบัน"><?php endif; ?></div>
        <div class="col-12 col-lg-6"><div class="d-flex justify-content-between align-items-center mb-2"><label class="form-label mb-0" data-related-document-label>SOP หรือ WI ที่เกี่ยวข้อง</label><button type="button" class="btn btn-sm btn-outline-secondary" data-add-related-link><i class="bi bi-plus-lg me-1" aria-hidden="true"></i>เลือกเอกสารเพิ่ม</button></div><div class="vstack gap-2" data-related-links><?php foreach ($relatedLinks as $linkIndex => $link): ?><div class="medsop-related-document" data-related-link><input type="search" class="form-control" data-document-search placeholder="ค้นหาจากรหัสหรือชื่อเอกสาร" aria-label="ค้นหา SOP หรือ WI"><?= Html::dropDownList("related_links[{$linkIndex}][document_id]", $relatedDocumentId($link), $relatedDocumentItems, ['prompt' => 'เลือก SOP หรือ WI ที่สร้างไว้', 'class' => 'form-select', 'data-document-select' => true]) ?><button class="btn btn-outline-danger" type="button" data-remove-related-link aria-label="นำเอกสารที่เกี่ยวข้องออก"><i class="bi bi-trash" aria-hidden="true"></i></button></div><?php endforeach; ?></div><div class="form-text" data-related-document-hint>ค้นหาและเลือกเอกสารที่สร้างไว้ สามารถเพิ่มได้หลายรายการ</div></div>
        <div class="col-12 col-md-8" data-flow-title><?= $form->field($model, 'title')->label('ชื่อเอกสาร ' . ($model->document_type ?: Document::TYPE_SOP), ['data-document-title-label' => true])->textInput(['class' => 'form-control', 'maxlength' => true, 'required' => true]) ?></div>
        <div class="col-12 col-md-4" data-flow-type><?= $form->field($model, 'document_type')->radioList(Document::typeOptions(), [
            'class' => 'btn-group w-100',
            'role' => 'group',
            'unselect' => null,
            'item' => static function ($index, $label, $name, $checked, $value) use ($hasAvailableSop) {
                $id = 'document-document_type-' . $index;
                $disabled = $value === Document::TYPE_WI && !$hasAvailableSop && !$checked;
                return Html::radio($name, $checked, ['id' => $id, 'value' => $value, 'class' => 'btn-check', 'autocomplete' => 'off', 'disabled' => $disabled, 'required' => $index === 0])
                    . Html::label(Html::encode($label), $id, ['class' => 'btn btn-outline-primary flex-fill']);
            },
        ])->hint($hasAvailableSop ? 'เลือก SOP สำหรับภาพรวมกระบวนการ หรือ WI สำหรับวิธีปฏิบัติที่อ้างอิง SOP หลัก' : 'ต้องสร้าง SOP อย่างน้อย 1 ฉบับก่อน จึงจะสามารถเลือกสร้าง WI ได้') ?></div>
        <div class="col-12 col-md-4" data-flow-organization><?= $form->field($model, 'organization_id')->label('หน่วยงานเจ้าของเอกสาร')->dropDownList($organizationItems, ['prompt' => 'เลือกหน่วยงาน', 'class' => 'form-select', 'required' => true]) ?></div>
        <div class="col-12 col-lg-6"><?= $form->field($model, 'objective')->textarea(['class' => 'form-control', 'rows' => 4, 'required' => true]) ?></div>
        <div class="col-12 col-lg-6"><?= $form->field($model, 'scope')->textarea(['class' => 'form-control', 'rows' => 4]) ?></div>
    </div></div>
</section>

<section class="medsop-form-section medsop-form-section--steps" aria-labelledby="document-steps-title">
    <div class="medsop-form-section__head d-flex flex-wrap justify-content-between align-items-center gap-2"><h2 id="document-steps-title" class="h6 fw-semibold mb-0"><i class="bi bi-stars me-2 text-primary" aria-hidden="true"></i>รายละเอียดขั้นตอนปฏิบัติการ</h2><button type="button" class="btn btn-outline-primary" data-add-step><i class="bi bi-plus-circle me-2" aria-hidden="true"></i>เพิ่มขั้นตอนงาน</button></div>
    <div class="card-body"><div class="vstack gap-4" data-step-list>
        <?php foreach ($stepRows as $index => $step): ?>
            <article class="medsop-manual-editor" data-step>
                <input type="hidden" name="steps[<?= $index ?>][id]" value="<?= (int) ($step['id'] ?? 0) ?>" data-step-id>
                <div class="medsop-manual-editor__head"><span class="medsop-step-number" data-step-number><?= $index + 1 ?></span><strong>ขั้นตอนที่ <span data-step-label><?= $index + 1 ?></span></strong><span class="text-body-secondary small">เพิ่มภาพหรือคลิปเพื่อให้ทำตามได้ง่าย</span><button type="button" class="btn btn-sm btn-outline-danger ms-auto" data-remove-step aria-label="ลบขั้นตอนที่ <?= $index + 1 ?>"><i class="bi bi-trash" aria-hidden="true"></i><span class="visually-hidden">ลบขั้นตอน</span></button></div>
                <div class="row g-3">
                    <div class="col-12"><label class="form-label" for="step-<?= $index ?>-title">ชื่อขั้นตอน</label><input id="step-<?= $index ?>-title" class="form-control" name="steps[<?= $index ?>][title]" value="<?= Html::encode($step['title'] ?? '') ?>" required><div class="invalid-feedback">กรุณาระบุชื่อขั้นตอน</div></div>
                    <div class="col-12 col-lg-6"><label class="form-label" for="step-<?= $index ?>-description">รายละเอียด</label><textarea id="step-<?= $index ?>-description" class="form-control" rows="3" name="steps[<?= $index ?>][description]"><?= Html::encode($step['description'] ?? '') ?></textarea></div>
                    <div class="col-12 col-lg-6"><label class="form-label" for="step-<?= $index ?>-caution">ข้อควรระวัง</label><textarea id="step-<?= $index ?>-caution" class="form-control" rows="3" name="steps[<?= $index ?>][caution]"><?= Html::encode($step['caution'] ?? '') ?></textarea></div>
                    <div class="col-12">
                        <div class="medsop-media-picker" data-media-picker>
                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                                <div><label class="form-label mb-1" for="step-<?= $index ?>-media">ภาพและวิดีโอประกอบ</label><p class="form-text mt-0 mb-0">เพิ่มภาพได้ไม่เกิน 5 ภาพต่อขั้นตอน (JPG, PNG, WebP ไม่เกิน 10 MB ต่อภาพ) หรือวิดีโอ MP4, WebM ไม่เกิน 100 MB</p></div>
                                <button class="btn btn-outline-secondary" type="button" data-trigger-media><i class="bi bi-paperclip me-2" aria-hidden="true"></i>เพิ่มสื่อ</button>
                            </div>
                            <input id="step-<?= $index ?>-media" class="visually-hidden" type="file" name="steps[<?= $index ?>][media][]" accept="image/jpeg,image/png,image/webp,video/mp4,video/webm" multiple data-media-input>
                            <div class="medsop-media-grid mt-3<?= empty($step['media']) ? ' d-none' : '' ?>" data-media-preview aria-live="polite">
                                <?php foreach (($step['media'] ?? []) as $media): ?>
                                    <article class="medsop-media-item" data-existing-media data-media-kind="<?= $media->media_type === 'image' ? 'image' : 'video' ?>">
                                        <input type="hidden" name="steps[<?= $index ?>][keep_media][]" value="<?= (int) $media->id ?>" data-keep-media>
                                        <?php if ($media->media_type === 'image'): ?>
                                            <img src="<?= Html::encode($media->file_path) ?>" alt="<?= Html::encode($media->file_name) ?>" loading="lazy">
                                        <?php else: ?>
                                            <div class="medsop-media-video"><i class="bi bi-play-btn" aria-hidden="true"></i></div>
                                        <?php endif; ?>
                                        <div class="medsop-media-item__meta"><span title="<?= Html::encode($media->file_name) ?>"><?= Html::encode($media->file_name) ?></span><button type="button" class="btn btn-sm btn-outline-danger" data-remove-media aria-label="นำ <?= Html::encode($media->file_name) ?> ออก"><i class="bi bi-x-lg" aria-hidden="true"></i></button></div>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                            <p class="form-text mt-2 mb-0" data-image-count aria-live="polite"></p>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                            <div><span class="form-label d-block mb-1">SOP หรือ WI ที่เกี่ยวข้องกับขั้นตอนนี้</span><p class="form-text mt-0 mb-0">เลือกจากเอกสารที่สร้างไว้ในระบบ เพื่อให้ผู้ปฏิบัติงานกดเปิดอ่านได้ทันที</p></div>
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-add-step-link><i class="bi bi-link-45deg me-1" aria-hidden="true"></i>เลือกเอกสารเพิ่ม</button>
                        </div>
                        <div class="vstack gap-2" data-step-related-links>
                            <?php foreach (($step['related_links'] ?? []) as $linkIndex => $link): ?>
                                <div class="medsop-related-document" data-step-related-link><input type="search" class="form-control" data-document-search placeholder="ค้นหาจากรหัสหรือชื่อเอกสาร" aria-label="ค้นหา SOP หรือ WI"><?= Html::dropDownList("steps[{$index}][related_links][{$linkIndex}][document_id]", $relatedDocumentId($link), $relatedDocumentItems, ['prompt' => 'เลือก SOP หรือ WI ที่สร้างไว้', 'class' => 'form-select', 'data-document-select' => true]) ?><button class="btn btn-outline-danger" type="button" data-remove-step-link aria-label="นำเอกสารที่เกี่ยวข้องออก"><i class="bi bi-trash" aria-hidden="true"></i></button></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div><p class="form-text mt-3 mb-0" data-step-hint aria-live="polite">ต้องมีอย่างน้อย 1 ขั้นตอน</p></div>
</section>
<template data-related-document-row><div class="medsop-related-document" data-related-link><input type="search" class="form-control" data-document-search placeholder="ค้นหาจากรหัสหรือชื่อเอกสาร" aria-label="ค้นหา SOP หรือ WI"><?= Html::dropDownList('', null, $relatedDocumentItems, ['prompt' => 'เลือก SOP หรือ WI ที่สร้างไว้', 'class' => 'form-select', 'data-document-select' => true]) ?><button class="btn btn-outline-danger" type="button" data-remove-related-link aria-label="นำเอกสารที่เกี่ยวข้องออก"><i class="bi bi-trash" aria-hidden="true"></i></button></div></template>
<template data-step-related-document-row><div class="medsop-related-document" data-step-related-link><input type="search" class="form-control" data-document-search placeholder="ค้นหาจากรหัสหรือชื่อเอกสาร" aria-label="ค้นหา SOP หรือ WI"><?= Html::dropDownList('', null, $relatedDocumentItems, ['prompt' => 'เลือก SOP หรือ WI ที่สร้างไว้', 'class' => 'form-select', 'data-document-select' => true]) ?><button class="btn btn-outline-danger" type="button" data-remove-step-link aria-label="นำเอกสารที่เกี่ยวข้องออก"><i class="bi bi-trash" aria-hidden="true"></i></button></div></template>
<div class="card shadow-sm position-sticky bottom-0 mt-3"><div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3"><span class="text-body-secondary small">ระบบจะบันทึกเอกสารและทุกขั้นตอนพร้อมกัน</span><div class="d-flex gap-2"><?= Html::a('ยกเลิก', ['index'], ['class' => 'btn btn-outline-secondary flex-fill']) ?><?= Html::submitButton('<span class="spinner-border spinner-border-sm me-2 d-none" data-save-spinner aria-hidden="true"></span><span data-save-label>บันทึกเอกสาร</span>', ['class' => 'btn btn-primary flex-fill', 'data-save-document' => true]) ?></div></div></div>
<?php ActiveForm::end(); ?>
<?php
$categoryMapJson = Json::htmlEncode($organizationCategoryMap);
$defaultCategoriesJson = Json::htmlEncode(array_values($defaultCategories));
$this->registerJs(<<<JS
(function () {
  const organization = document.getElementById('document-organization_id');
  const category = document.getElementById('document-category');
  if (!organization || !category) return;
  const categoryMap = {$categoryMapJson};
  const defaultCategories = {$defaultCategoriesJson};
  const flowType = document.querySelector('[data-flow-type]');
  const flowOrganization = document.querySelector('[data-flow-organization]');
  const flowCategory = document.querySelector('[data-flow-category]');
  const flowTitle = document.querySelector('[data-flow-title]');
  const row = flowType && flowType.parentElement;
  if (row && flowOrganization && flowCategory && flowTitle) {
    row.insertBefore(flowType, row.firstElementChild);
    flowType.after(flowOrganization);
    flowOrganization.after(flowCategory);
    flowCategory.after(flowTitle);
  }
  function refreshCategories(preserveCurrent) {
    const current = category.value;
    const organizationId = organization.value;
    const values = categoryMap[organizationId] || defaultCategories;
    category.disabled = organizationId === '';
    category.replaceChildren(new Option(organizationId === '' ? 'เลือกหน่วยงานก่อน' : 'เลือกหมวดหมู่ของหน่วยงาน', ''));
    values.forEach(function (value) { category.add(new Option(value, value, false, value === current)); });
    if (!preserveCurrent || !values.includes(current)) category.value = '';
  }
  organization.addEventListener('change', function () { refreshCategories(false); });
  refreshCategories(true);
})();
JS);
?>
