<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Categorise;
use kartik\editors\Summernote;
use app\modules\purchase\models\Tor;

/** @var yii\web\View $this */
/** @var app\modules\purchase\models\Tor $model */
/** @var app\modules\purchase\models\TorPrice[] $prices */

/**
 * Toolbar ของตัวแก้ไขเนื้อความ — จงใจจำกัดไว้เท่าที่ PhpWord แปลงลงไฟล์ Word ได้จริง
 * ถ้าเปิดครบทุกปุ่ม (ฟอนต์/สี/รูป) ผู้ใช้จะจัดรูปแบบสวยบนเว็บแล้วพิมพ์ออกมาไม่เหมือน
 * รายการที่อนุญาตต้องสอดคล้องกับ Tor::ALLOWED_HTML ที่ใช้กรองตอนบันทึก
 */
$toolbar = [
    ['style', ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
    ['para', ['ul', 'ol', 'paragraph']],
    ['insert', ['table']],
    ['misc', ['undo', 'redo', 'codeview']],
];
$editorOptions = function (int $height) use ($toolbar) {
    return [
        'useKrajeePresets' => false,
        'pluginOptions' => [
            'height' => $height,
            'minHeight' => $height,
            'maxHeight' => 600,
            'toolbar' => $toolbar,
            'disableDragAndDrop' => true,
        ],
    ];
};

// ทะเบียนผู้แทนจำหน่าย: ใช้ datalist ชุดเดียวร่วมกันทุกแถว (ผู้ขายเกือบพันราย
// ถ้าทำเป็น dropdown ต่อแถวจะหนักมาก) — พิมพ์ชื่อแหล่งอ้างอิงที่ไม่อยู่ในทะเบียนได้ด้วย
$vendorNames = Categorise::find()
    ->select('title')
    ->where(['name' => 'vendor'])
    ->andWhere(['!=', 'code', '-'])
    ->orderBy(['title' => SORT_ASC])
    ->column();

$rows = $prices ?: [];
$form = ActiveForm::begin([
    'id' => 'tor-form',
    'options' => ['autocomplete' => 'off'],
]);
?>

<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-info" type="button">ข้อมูลทั่วไป</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-spec" type="button">คุณลักษณะ</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-cond" type="button">เงื่อนไข</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-price" type="button">ราคากลาง</button></li>
</ul>

<?= $form->errorSummary($model, ['class' => 'alert alert-danger']) ?>

<div class="tab-content">

    <!-- ─── ข้อมูลทั่วไป ─────────────────────────────────────────────── -->
    <div class="tab-pane fade show active" id="tab-info">
        <div class="row g-3">
            <div class="col-12">
                <?= $form->field($model, 'title')->textInput([
                    'maxlength' => true,
                    'placeholder' => 'เช่น จัดซื้อเครื่องคอมพิวเตอร์สำหรับงานสำนักงาน ปีงบประมาณ 2569',
                ]) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'asset_type_id')->dropDownList(Tor::listAssetType(), ['prompt' => '— เลือกประเภทพัสดุ —']) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'purchase_method')->dropDownList(Tor::listPurchaseMethod(), ['prompt' => '— เลือกวิธีจัดซื้อจัดจ้าง —']) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'status')->dropDownList(Tor::statusList()) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'budget')->input('number', ['step' => '0.01', 'min' => 0]) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'thai_year')->input('number', ['min' => 2500, 'max' => 2700]) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'tor_date')->input('date') ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'egp_no')->textInput([
                    'maxlength' => true,
                    'placeholder' => 'ระบุหลังลงทะเบียนใน e-GP',
                ]) ?>
            </div>
            <div class="col-12">
                <?= $form->field($model, 'purpose')->widget(Summernote::class, $editorOptions(140)) ?>
            </div>
        </div>
    </div>

    <!-- ─── คุณลักษณะ ────────────────────────────────────────────────── -->
    <div class="tab-pane fade" id="tab-spec">
        <div class="alert alert-warning d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <i class="bi bi-exclamation-triangle me-1"></i>
                <span class="fw-medium">ห้ามระบุยี่ห้อ รุ่น ประเทศ หรือแหล่งกำเนิดสินค้า</span>
                (พ.ร.บ. จัดซื้อจัดจ้างฯ 2560 มาตรา 7) เมื่อจำเป็นต้องอ้างอิงสเปกเฉพาะ ให้ระบุ “หรือเทียบเท่า” กำกับเสมอ
            </div>
            <?= Html::a('<i class="bi bi-collection me-1"></i>เลือกจากคลังแม่แบบ', ['template-picker', 'title' => 'คลังแม่แบบคุณลักษณะ'], [
                'class' => 'btn btn-sm btn-primary rounded-pill px-3 open-modal',
                'data' => ['size' => 'modal-xl'],
            ]) ?>
        </div>

        <div class="row g-3">
            <div class="col-md-3">
                <?= $form->field($model, 'qty')->input('number', ['step' => '0.01', 'min' => 0, 'placeholder' => 'เช่น 20']) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'unit_name')->dropDownList(Tor::listUnit(), ['prompt' => '— เลือกหน่วยนับ —']) ?>
            </div>
            <div class="col-md-6 d-flex align-items-end">
                <div class="mb-3 small text-muted" id="tor-ref-price-hint"></div>
            </div>
            <div class="col-12">
                <?= $form->field($model, 'spec')->widget(Summernote::class, $editorOptions(320)) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'standard')->widget(Summernote::class, $editorOptions(120)) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'warranty')->widget(Summernote::class, $editorOptions(120)) ?>
            </div>
        </div>
    </div>

    <!-- ─── เงื่อนไข ─────────────────────────────────────────────────── -->
    <div class="tab-pane fade" id="tab-cond">
        <div class="row g-3">
            <div class="col-md-3">
                <?= $form->field($model, 'delivery_days')->input('number', ['min' => 0, 'placeholder' => '30']) ?>
            </div>
            <div class="col-md-9">
                <?= $form->field($model, 'delivery_place')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-12">
                <?= $form->field($model, 'delivery_term')->widget(Summernote::class, $editorOptions(140)) ?>
            </div>
            <div class="col-12">
                <?= $form->field($model, 'payment_term')->widget(Summernote::class, $editorOptions(140)) ?>
            </div>
            <div class="col-12">
                <?= $form->field($model, 'vendor_qualification')->widget(Summernote::class, $editorOptions(180)) ?>
            </div>
        </div>
    </div>

    <!-- ─── ราคากลาง ─────────────────────────────────────────────────── -->
    <div class="tab-pane fade" id="tab-price">
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-1"></i>
            ต้องสืบราคาไม่น้อยกว่า 3 แหล่งก่อนกำหนดราคากลาง —
            <span class="fw-medium">ทุกแถวต้องเป็นราคาที่สืบได้จริง</span>
            ระบบไม่เติมราคาให้อัตโนมัติ
        </div>

        <div class="table-responsive">
            <table class="table table-bordered align-middle" id="tor-price-table">
                <thead class="table-light">
                    <tr>
                        <th style="width:52px" class="text-center">ที่</th>
                        <th style="min-width:220px">ชื่อผู้เสนอราคา/แหล่งอ้างอิง</th>
                        <th style="min-width:220px">รายละเอียดที่เสนอ</th>
                        <th style="width:170px" class="text-end">ราคา (บาท)</th>
                        <th style="width:56px"></th>
                    </tr>
                </thead>
                <tbody id="tor-price-body">
                    <?php
                    // เริ่มต้นให้ครบ 3 แถวตามเกณฑ์ขั้นต่ำ แต่เพิ่ม/ลบได้อิสระ
                    $render = $rows;
                    for ($i = count($render); $i < 3; $i++) {
                        $render[] = null;
                    }
                    foreach ($render as $i => $row):
                    ?>
                        <tr>
                            <td class="text-center tor-row-no"><?= $i + 1 ?></td>
                            <td>
                                <?= Html::textInput("prices[$i][vendor_name]", $row->vendor_name ?? '', [
                                    'class' => 'form-control',
                                    'list' => 'tor-vendor-list',
                                    'placeholder' => 'ชื่อร้าน/บริษัท หรือแหล่งอ้างอิง',
                                ]) ?>
                                <?= Html::hiddenInput("prices[$i][vendor_id]", $row->vendor_id ?? '') ?>
                            </td>
                            <td>
                                <?= Html::textInput("prices[$i][detail]", $row->detail ?? '', [
                                    'class' => 'form-control',
                                    'placeholder' => 'รายละเอียดที่เสนอ',
                                ]) ?>
                            </td>
                            <td>
                                <?= Html::input('number', "prices[$i][price]", $row->price ?? '', [
                                    'class' => 'form-control text-end tor-price',
                                    'step' => '0.01',
                                    'min' => 0,
                                    'placeholder' => '0.00',
                                ]) ?>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-danger tor-row-remove" title="ลบแถว">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" id="tor-row-add">
            <i class="bi bi-plus-lg me-1"></i>เพิ่มแหล่งสืบราคา
        </button>

        <div class="row g-3 mt-2">
            <div class="col-md-4">
                <?= $form->field($model, 'mid_method')->dropDownList(Tor::listMidMethod()) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'mid_price')->input('number', [
                    'step' => '0.01',
                    'min' => 0,
                    'class' => 'form-control fw-semibold text-end',
                ])->hint('คำนวณให้อัตโนมัติจากราคาที่กรอก แก้ทับได้') ?>
            </div>
            <div class="col-md-5">
                <?= $form->field($model, 'mid_note')->textarea(['rows' => 3]) ?>
            </div>
        </div>
    </div>
</div>

<datalist id="tor-vendor-list">
    <?php foreach ($vendorNames as $name): ?>
        <option value="<?= Html::encode($name) ?>"></option>
    <?php endforeach; ?>
</datalist>

<div class="d-grid d-sm-flex justify-content-sm-end gap-2 mt-4">
    <?= Html::submitButton('<i class="bi bi-save me-1"></i>บันทึก TOR', ['class' => 'btn btn-primary']) ?>
    <?= Html::a('ยกเลิก', $model->isNewRecord ? ['index'] : ['view', 'id' => $model->id], ['class' => 'btn btn-outline-secondary']) ?>
</div>

<?php ActiveForm::end(); ?>

<?php
$templateDataUrl = Url::to(['template-data']);
$specInputId = Html::getInputId($model, 'spec');
$standardInputId = Html::getInputId($model, 'standard');
$warrantyInputId = Html::getInputId($model, 'warranty');
$titleInputId = Html::getInputId($model, 'title');
$unitInputId = Html::getInputId($model, 'unit_name');
$daysInputId = Html::getInputId($model, 'delivery_days');
$midInputId = Html::getInputId($model, 'mid_price');
$midMethodId = Html::getInputId($model, 'mid_method');

$js = <<<JS
(function () {
    // ── ใบสืบราคา: เพิ่ม/ลบแถว + คำนวณราคากลาง ────────────────────────────
    function renumber() {
        \$('#tor-price-body tr').each(function (i) {
            \$(this).find('.tor-row-no').text(i + 1);
            \$(this).find('input').each(function () {
                var name = \$(this).attr('name');
                if (name) \$(this).attr('name', name.replace(/prices\\[\\d+\\]/, 'prices[' + i + ']'));
            });
        });
    }

    function recalc() {
        var values = [];
        \$('#tor-price-body .tor-price').each(function () {
            var v = parseFloat(\$(this).val());
            if (!isNaN(v) && v > 0) values.push(v);
        });
        if (!values.length) return;

        var method = \$('#{$midMethodId}').val() || '';
        var result = method.indexOf('ต่ำสุด') >= 0
            ? Math.min.apply(null, values)
            : values.reduce(function (s, v) { return s + v; }, 0) / values.length;

        \$('#{$midInputId}').val(result.toFixed(2));
    }

    \$('#tor-row-add').on('click', function () {
        var \$body = \$('#tor-price-body');
        var \$row = \$body.find('tr:first').clone();
        \$row.find('input').val('');
        \$body.append(\$row);
        renumber();
    });

    \$('#tor-price-body').on('click', '.tor-row-remove', function () {
        if (\$('#tor-price-body tr').length <= 1) {
            \$(this).closest('tr').find('input').val('');
        } else {
            \$(this).closest('tr').remove();
        }
        renumber();
        recalc();
    });

    // พิมพ์ชื่อผู้เสนอราคาเอง = ไม่ได้เลือกจากทะเบียน ล้าง vendor_id ทิ้ง
    // ฝั่งเซิร์ฟเวอร์จะจับคู่กลับให้เองถ้าชื่อตรงกับทะเบียนพอดี
    \$('#tor-price-body').on('input', 'input[list="tor-vendor-list"]', function () {
        \$(this).siblings('input[type="hidden"]').val('');
    });

    \$('#tor-price-body').on('input', '.tor-price', recalc);
    \$('#{$midMethodId}').on('change', recalc);

    // ── คลังแม่แบบ: เติมค่าลงฟอร์ม ────────────────────────────────────────
    // ref_price ใช้แสดงเป็นข้อมูลประกอบเท่านั้น ห้ามเติมลงใบสืบราคาเด็ดขาด
    window.torApplyTemplate = function (id) {
        \$.getJSON('{$templateDataUrl}', { id: id }, function (r) {
            if (!r || r.status !== 'success') {
                if (typeof toastr !== 'undefined') toastr.error((r && r.message) || 'โหลดแม่แบบไม่สำเร็จ');
                return;
            }
            var d = r.data;

            if (!\$('#{$titleInputId}').val()) \$('#{$titleInputId}').val('จัดซื้อ' + d.title);
            if (d.unit_name) \$('#{$unitInputId}').val(d.unit_name).trigger('change');
            if (d.delivery_days) \$('#{$daysInputId}').val(d.delivery_days);

            if (d.spec) \$('#{$specInputId}').summernote('code', d.spec);
            if (d.standard) \$('#{$standardInputId}').summernote('code', d.standard);
            if (d.warranty) \$('#{$warrantyInputId}').summernote('code', d.warranty);

            \$('#tor-ref-price-hint').html(
                d.ref_price_text
                    ? '<i class="bi bi-info-circle me-1"></i>ราคาอ้างอิงตลาดของแม่แบบนี้ ' + d.ref_price_text +
                      ' บาท/หน่วย <span class="fst-italic">ใช้ดูประกอบเท่านั้น ต้องสืบราคาจริงเอง</span>'
                    : ''
            );

            if (typeof erpHideModal === 'function') erpHideModal('#main-modal');
            else \$('#main-modal').modal('hide');

            if (typeof toastr !== 'undefined') {
                toastr.success('โหลดแม่แบบ "' + d.title + '" แล้ว — ตรวจสอบและปรับแก้ให้ตรงกับความต้องการก่อนบันทึก');
            }
        });
    };
})();
JS;
$this->registerJs($js, View::POS_READY);
?>
