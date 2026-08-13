<?php

use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;
use app\modules\purchase\models\DocTemplate;

/** @var yii\web\View $this */
/** @var app\modules\purchase\models\DocTemplate $model */
/** @var array $catalog */

/**
 * ฟอร์มแม่แบบเอกสาร
 *
 * เนื้อเอกสารแก้เป็น HTML ในกล่องข้อความ ไม่ใช่แก้แบบ WYSIWYG เหมือนหน้าแก้เอกสาร
 * เพราะแม่แบบมีโครงสร้างที่ตัวแก้แบบเห็นภาพทำให้พังได้ง่าย โดยเฉพาะบล็อกวนซ้ำ
 * {{#items}}...{{/items}} ที่ต้องครอบ <tr> ให้พอดีทั้งแถว ถ้าผู้ใช้ลากเมาส์คลุมผิด
 * ไปครึ่งแถว ตารางรายการพัสดุจะเพี้ยนทั้งตารางโดยไม่มีอะไรบอก
 *
 * สิ่งที่ชดเชยให้คือรายการฟิลด์ด้านขวาที่คลิกแล้วแทรกแท็กให้ตรงตำแหน่งเคอร์เซอร์
 * ผู้ใช้จึงไม่ต้องจำชื่อแท็กเอง — รายการนี้มาจาก DocMergeEngine::fieldCatalog()
 * ซึ่งเป็นที่เดียวกับที่ตัวแทนค่าอ่าน จึงไม่มีทางที่รายการกับของจริงจะไม่ตรงกัน
 */

$form = ActiveForm::begin([
    'id' => 'doc-template-form',
    'options' => ['autocomplete' => 'off'],
]);
?>

<div class="row g-3">
    <div class="col-lg-5">
        <?= $form->field($model, 'name')->textInput([
            'maxlength' => true,
            'placeholder' => 'เช่น บันทึกข้อความขอจัดซื้อ (ว.804)',
        ]) ?>
    </div>
    <div class="col-lg-3">
        <?= $form->field($model, 'code')->textInput([
            'maxlength' => true,
            'placeholder' => 'w804_memo_buy',
        ])->hint('ใช้อ้างในโค้ดและ seed — เปลี่ยนภายหลังได้ แต่ห้ามซ้ำกับแม่แบบอื่น') ?>
    </div>
    <div class="col-lg-2 col-6">
        <?= $form->field($model, 'category')->dropDownList(DocTemplate::categoryList()) ?>
    </div>
    <div class="col-lg-2 col-6">
        <?= $form->field($model, 'sort_order')->textInput(['type' => 'number']) ?>
    </div>

    <div class="col-lg-4">
        <?= $form->field($model, 'ref_type')->dropDownList(DocTemplate::refTypeList())
            ->hint('กำหนดว่าแท็ก {{ref.xxx}} จะดึงค่าจากตารางไหน') ?>
    </div>
    <div class="col-lg-3 col-6">
        <?= $form->field($model, 'emblem')->dropDownList(DocTemplate::emblemList()) ?>
    </div>
    <div class="col-lg-2 col-6">
        <?= $form->field($model, 'orientation')->dropDownList([
            'portrait' => 'ตั้ง (Portrait)',
            'landscape' => 'นอน (Landscape)',
        ]) ?>
    </div>
    <div class="col-lg-2 col-6">
        <?= $form->field($model, 'font_size')->textInput(['type' => 'number', 'min' => 10, 'max' => 26]) ?>
    </div>
    <div class="col-lg-1 col-6">
        <?= $form->field($model, 'active')->checkbox() ?>
    </div>

    <div class="col-lg-6">
        <?= $form->field($model, 'law_ref')->textInput([
            'maxlength' => true,
            'placeholder' => 'เช่น หนังสือกระทรวงการคลัง ที่ กค (กวจ) 0405.2/ว 804 ลว. 12 พ.ย. 2568',
        ]) ?>
    </div>
    <div class="col-lg-6">
        <?= $form->field($model, 'note')->textarea(['rows' => 2])
            ->hint('ข้อความนี้จะขึ้นเตือนผู้ใช้ตอนเลือกแม่แบบในหน้าสร้างเอกสาร') ?>
    </div>
</div>

<hr>

<div class="row g-3">
    <div class="col-lg-8">
        <?= $form->field($model, 'body_html')->textarea([
            'rows' => 24,
            'id' => 'doc-body-html',
            'class' => 'form-control font-monospace',
            'style' => 'font-size:.85rem',
            'spellcheck' => 'false',
        ])->hint(
            'เนื้อเอกสารเป็น HTML — ใช้ได้เฉพาะ p, span, strong, u, table, tr, td, ol, ul, li'
            . ' และคุมรูปแบบด้วย class ที่ระบบเตรียมไว้ (d-title, d-body, d-items ...)'
            . ' แท็กหรือ style อื่นจะถูกตัดออกตอนบันทึก'
        ) ?>
    </div>

    <div class="col-lg-4">
        <div class="card border">
            <div class="card-header bg-body-tertiary py-2">
                <h6 class="mb-0 small">
                    <i class="bi bi-braces me-1"></i>ฟิลด์ที่ใช้ได้ — คลิกเพื่อแทรก
                </h6>
            </div>
            <div class="card-body p-2" style="max-height:520px;overflow:auto">
                <?php foreach ($catalog as $group => $fields): ?>
                    <div class="fw-semibold small text-muted mt-2 mb-1"><?= Html::encode($group) ?></div>
                    <div class="d-flex flex-wrap gap-1">
                        <?php foreach ($fields as $tag => $label): ?>
                            <button type="button"
                                class="btn btn-sm btn-outline-secondary js-insert-tag text-start"
                                data-tag="<?= Html::encode('{{' . $tag . '}}') ?>"
                                title="<?= Html::encode($label) ?>"
                                style="font-size:.75rem">
                                <?= Html::encode($tag) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php if ($used = $model->docCount()): ?>
    <div class="alert alert-warning small mt-3">
        <i class="bi bi-info-circle me-1"></i>
        แม่แบบนี้ถูกใช้ออกเอกสารไปแล้ว <?= $used ?> ฉบับ
        การแก้ข้อความที่นี่มีผลกับเอกสารที่สร้าง<strong>ใหม่</strong>เท่านั้น
        เอกสารที่ออกไปแล้วยังใช้ข้อความเดิม เพราะต้องพิมพ์ซ้ำให้เหมือนฉบับที่ลงนามไปแล้วได้
    </div>
<?php endif; ?>

<div class="d-flex gap-2 mt-3">
    <?= Html::submitButton('<i class="bi bi-check2-circle me-1"></i>บันทึกแม่แบบ', [
        'class' => 'btn btn-primary',
    ]) ?>
    <?= Html::a('ยกเลิก', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
</div>

<?php ActiveForm::end(); ?>

<?php
$js = <<<JS
// แทรกแท็กที่ตำแหน่งเคอร์เซอร์ ไม่ต่อท้ายทั้งก้อน — ผู้ใช้เลือกจุดวางเองได้
jQuery(document).on('click', '.js-insert-tag', function (e) {
    e.preventDefault();
    var tag = jQuery(this).data('tag');
    var area = document.getElementById('doc-body-html');
    if (!area) { return; }

    var start = area.selectionStart || 0;
    var end = area.selectionEnd || 0;
    area.value = area.value.substring(0, start) + tag + area.value.substring(end);

    // คืนโฟกัสและวางเคอร์เซอร์ต่อท้ายแท็กที่เพิ่งแทรก เพื่อพิมพ์ต่อได้ทันที
    area.focus();
    area.selectionStart = area.selectionEnd = start + tag.length;
});
JS;

$this->registerJs($js, View::POS_READY);
?>
