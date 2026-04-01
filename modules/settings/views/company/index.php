<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use kartik\form\ActiveForm;
use kartik\widgets\Select2;
use yii\widgets\MaskedInput;
use app\components\AppHelper;
use kartik\widgets\Typeahead;
use app\components\SiteHelper;
use app\widgets\TomSelectWidget;
use kartik\editors\Summernote;
use app\modules\hr\models\Employees;
use iamsaint\datetimepicker\Datetimepicker;
use app\modules\filemanager\components\FileManagerHelper;

$this->title = 'ตั้งค่าองค์กร';

$formatJs = <<< 'JS'
    var formatRepo = function (repo) {
        if (repo.loading) {
            return repo.avatar;
        }
        // console.log(repo);
        var markup =
    '<div class="row">' +
        '<div class="col-12">' +
            '<span>' + repo.avatar + '</span>' +
        '</div>' +
    '</div>';
        if (repo.description) {
          markup += '<p>' + repo.avatar + '</p>';
        }
        return '<div style="overflow:hidden;">' + markup + '</div>';
    };
    var formatRepoSelection = function (repo) {
        return repo.avatar || repo.avatar;
    }
    JS;

// Register the formatting script
$this->registerJs($formatJs, View::POS_HEAD);

// script to parse the results into the format expected by Select2
$resultsJs = <<< JS
    function (data, params) {
        params.page = params.page || 1;
        return {
            results: data.results,
            pagination: {
                more: (params.page * 30) < data.total_count
            }
        };
    }
    JS;

$headerLucideTomRender = <<<'JS'
{
    option: function(item, escape) {
        var v = String(item.value || "");
        if (!v) return "<div>" + escape(item.text) + "</div>";
        return '<div class="d-flex align-items-center gap-2 py-1 erp-lucide-ts-option">' +
            '<span class="erp-lucide-ts-ic flex-shrink-0" aria-hidden="true"><i data-lucide="' + escape(v) + '"></i></span>' +
            '<span class="flex-grow-1">' + escape(item.text) + '</span>' +
            '<span class="text-muted small text-nowrap">' + escape(v) + '</span></div>';
    },
    item: function(item, escape) {
        var v = String(item.value || "");
        if (!v) return "<div>" + escape(item.text) + "</div>";
        return '<div class="d-flex align-items-center gap-2 erp-lucide-ts-item">' +
            '<span class="erp-lucide-ts-ic flex-shrink-0" aria-hidden="true"><i data-lucide="' + escape(v) + '"></i></span>' +
            '<span class="text-truncate">' + escape(item.text) + '</span></div>';
    }
}
JS;

?>
<style>

    .field-director_type{
        margin-bottom: 3px !important;
    }

    .erp-lucide-ts-option .erp-lucide-ts-ic svg,
    .erp-lucide-ts-item .erp-lucide-ts-ic svg {
        width: 1.35rem;
        height: 1.35rem;
        stroke-width: 2;
        vertical-align: middle;
    }

    .ts-wrapper.single .ts-control .erp-lucide-ts-item .erp-lucide-ts-ic svg {
        width: 1.125rem;
        height: 1.125rem;
    }

    .erp-lucide-ts-option {
        min-height: 2rem;
    }
</style>

<?php $this->beginBlock('page-title'); ?>
<?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?php echo $this->render('@app/modules/settings/views/menu.php',['active' => 'company']) ?>
<?php $this->endBlock(); ?>


<!-- <h1 class="text-center"><i class="bi bi-building-fill-check fs-1"></i> ข้อมูลองค์กร</h1> -->

<div class="card">
    <div class="card-body">
        <h4 class="card-title"><i class="bi bi-building-fill-check fs-1"></i> ข้อมูลองค์กร</h4>

        <?php $form = ActiveForm::begin(['id' => 'form-company']); ?>
        <?= $form->field($model, 'data_json[leader_fullname]')->hiddenInput()->label(false) ?>
        <?= $form->field($model, 'data_json[leader_position]')->hiddenInput()->label(false) ?>
        <?= $form->field($model, 'data_json[director_position]')->hiddenInput()->label(false) ?>

        <div class="container-fluid px-0">
            <div class="row g-3 justify-content-center mb-2">
                <div class="col-12 text-center">
                    <input type="file" id="my_file" class="d-none" />
                    <a href="#" class="select-photo d-inline-block text-decoration-none">
                        <?php if ($model->isNewRecord): ?>
                            <?= Html::img('@web/img/placeholder-img.jpg', ['class' => 'object-fit-cover rounded shadow', 'style' => 'margin-top: 25px;max-width: 135px;max-height: 135px;    width: 100%;height: 100%;']) ?>
                        <?php else: ?>
                            <?php echo Html::img($model->logo(),['class' => 'object-fit-cover rounded','style' =>'margin-top: 25px;max-width: 135px;max-height: 135px;    width: 100%;height: 100%;']) ?>
                        <?php endif ?>
                    </a>
                </div>
            </div>

            <div class="row g-3 mt-2">
                <div class="col-12 col-lg-6">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <?= $form->field($model, 'data_json[company_name]')->textInput()->label('ชื่อหน่วยงาน') ?>
                        </div>
                        <div class="col-12 col-md-6">
                            <?= $form->field($model, 'data_json[doc_number]')->textInput()->label('เลขที่หนังสือ') ?>
                        </div>
                    </div>
                    <?= $form->field($model, 'data_json[phone]')->textInput()->label('โทรศัพท์') ?>
                    <?= $form->field($model, 'data_json[website]')->textInput()->label('เว็บไซต์') ?>
                    <?php //  $form->field($model, 'data_json[director_name]')->textInput()->label('ผู้อำนวยการ')
                    ?>
                    <?php //  $form->field($model, 'data_json[director_position]')->textInput()->label('ตำแหน่ง')
                    ?>
                    <?php
                    echo $form->field($model, 'data_json[director_type]')->radioList(
                        ['ผู้อำนวยการ' => 'ผู้อำนวยการ', 'รักษาการแทน' => 'รักษาการแทน'],
                        ['custom' => true, 'inline' => true, 'id' => 'director_type']
                    )->label(false);
                    ?>
                    <?php
                try {
                    //code...
                    $initEmployee = isset($model->data_json['director_name']) ? Employees::find()->where(['id' => $model->data_json['director_name']])->one()->getAvatar(false) : null;
                } catch (\Throwable $th) {
                    //throw $th;
                    $initEmployee = '';
                }
                // echo $initEmployee->getAvatar(false);
                echo $form->field($model, 'data_json[director_name]')->widget(Select2::classname(), [
                    'initValueText' => $initEmployee,
                    'id' => 'boardId',
                    'options' => ['placeholder' => 'เลือก ...'],
                    'size' => Select2::LARGE,
                    'pluginEvents' => [
                        'select2:unselect' => 'function() {
                            $("#categorise-data_json-director_position").val("")

                            }',
                            'select2:select' => 'function() {
                                var position_name = $(this).select2("data")[0].position_name_text;
                                $("#categorise-data_json-director_position").val(position_name)

                                }',
                            ],
                            'pluginOptions' => [
                                // 'dropdownParent' => '#main-modal',
                                'allowClear' => true,
                                'minimumInputLength' => 1,
                                'ajax' => [
                                    'url' => Url::to(['/depdrop/employee-by-id']),
                                    'dataType' => 'json',
                                    'delay' => 250,
                                    'data' => new JsExpression('function(params) { return {q:params.term, page: params.page}; }'),
                                    'processResults' => new JsExpression($resultsJs),
                                    'cache' => true,
                                ],
                                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                                'templateSelection' => new JsExpression('function (item) { return item.text; }'),
                                'templateResult' => new JsExpression('formatRepo'),
                            ],
                            ])->label(false)
                            ?>
                </div>

                <div class="col-12 col-lg-6">
                    <?= $form->field($model, 'data_json[province]')->textInput(['placeholder' => 'ระบุ เช่น จังหวัดขอนแก่น'])->label('จังหวัด') ?>
                    <?= $form->field($model, 'data_json[hoscode]')->textInput()->label('รหัสโรงพยาบาล') ?>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <?= $form->field($model, 'data_json[email]')->textInput()->label('อีเมล') ?>
                        </div>
                        <div class="col-12 col-md-6">
                            <?= $form->field($model, 'data_json[fax]')->textInput()->label('แฟกซ์') ?>
                        </div>
                    </div>

                    <?php
                try {
                    //code...
                    $initEmployee = isset($model->data_json['leader']) ? Employees::find()->where(['id' => $model->data_json['leader']])->one()->getAvatar(false) : null;
                } catch (\Throwable $th) {
                    //throw $th;
                    $initEmployee = '';
                }
                // echo $initEmployee->getAvatar(false);
                echo $form->field($model, 'data_json[leader]')->widget(Select2::classname(), [
                    'initValueText' => $initEmployee,
                    'id' => 'boardId',
                    'options' => ['placeholder' => 'เลือก ...'],
                    'size' => Select2::LARGE,
                    'pluginEvents' => [
                        'select2:unselect' => 'function() {
                            $("#categorise-data_json-leader_fullname").val("")
                            $("#categorise-data_json-leader_position").val("")

         }',
                        'select2:select' => 'function() {
                        console.log($(this).select2("data")[0])
                            var fullname = $(this).select2("data")[0].fullname;
                            var position_name = $(this).select2("data")[0].position_name_text;
                            $("#categorise-data_json-leader_fullname").val(fullname)
                            $("#categorise-data_json-leader_position").val(position_name)

         }',
                    ],
                    'pluginOptions' => [
                        // 'dropdownParent' => '#main-modal',
                        'allowClear' => true,
                        'minimumInputLength' => 1,
                        'ajax' => [
                            'url' => Url::to(['/depdrop/employee-by-id']),
                            'dataType' => 'json',
                            'delay' => 250,
                            'data' => new JsExpression('function(params) { return {q:params.term, page: params.page}; }'),
                            'processResults' => new JsExpression($resultsJs),
                            'cache' => true,
                        ],
                        'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                        'templateSelection' => new JsExpression('function (item) { return item.text; }'),
                        'templateResult' => new JsExpression('formatRepo'),
                    ],
                ])->label('หัวหน้าเจ้าหน้าที่')
                ?>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12">
                    <?= $form->field($model, 'data_json[address]')->textArea(['style' => 'height:100px'])->label('ที่อยู่') ?>
                    <?= $form->field($model, 'data_json[pdpa_url]')->textInput()->label('เงื่อนไขการให้บริการ share google drive เช่น https://drive.google.com/file/d/123456/preview') ?>
                    <?= $form->field($model, 'data_json[active_pdpa]')->checkbox([
                        'custom' => true,
                        'switch' => true,
                        'checked' => (isset($model->data_json['active_pdpa']) && $model->data_json['active_pdpa'] == "1" ? true : false)
                        ])->label('ต้องยินยอมเงื่อนไข PDPA');?>

                    <?= $form->field($model, 'data_json[manual]')->textInput()->label('คู่มือการใช้งาน') ?>
                </div>
            </div>

            <div class="row g-3 mt-3 pt-3 border-top">
                <div class="col-12">
                    <h5 class="border-bottom pb-2"><i class="bi bi-type me-1"></i> ข้อความบนแถบเมนู (Header)</h5>
                    <p class="text-muted small mb-0">
                        กำหนดข้อความแทน &quot;HOSPITAL&quot; / &quot;ERP SYSTEM&quot; ได้ ปรับขนาด สี ความหนา และฟอนต์จาก
                        <a href="https://fonts.google.com" target="_blank" rel="noopener noreferrer">Google Fonts</a>
                        (พิมพ์ชื่อฟอนต์ตรงกับที่แสดงบนเว็บ เช่น Sarabun, Kanit)
                        — เว้นบรรทัดที่ 1 ว่างจะแสดง HOSPITAL;
                        เว้นบรรทัดที่ 2 ว่างจะแสดง ERP SYSTEM
                    </p>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <?= $form->field($model, 'data_json[header_brand_lucide_icon]')->widget(TomSelectWidget::class, [
                        'items' => SiteHelper::headerLucideIconChoices(),
                        'options' => ['class' => 'form-select'],
                        'clientOptions' => [
                            'placeholder' => 'ค้นหาชื่อไอคอน...',
                            'maxOptions' => 500,
                            'create' => false,
                            'searchField' => ['text', 'value'],
                            'render' => new JsExpression($headerLucideTomRender),
                            'onInitialize' => new JsExpression('function () { if (typeof lucide !== "undefined" && lucide.createIcons) { setTimeout(function () { lucide.createIcons(); }, 0); } }'),
                            'onDropdownOpen' => new JsExpression('function () { if (typeof lucide !== "undefined" && lucide.createIcons) { requestAnimationFrame(function () { lucide.createIcons(); }); } }'),
                            'onChange' => new JsExpression('function () { if (typeof lucide !== "undefined" && lucide.createIcons) { requestAnimationFrame(function () { lucide.createIcons(); }); } }'),
                        ],
                    ])->label('ไอคอนข้างข้อความ (Lucide)')
                        ->hint(Html::a(
                            'lucide.dev/icons',
                            'https://lucide.dev/icons/',
                            ['target' => '_blank', 'rel' => 'noopener noreferrer']
                        ) . ' — พิมพ์ค้นหาได้ แสดงไอคอนและรายการทั้งหมดในเมนู') ?>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <?= $form->field($model, 'data_json[header_brand_line1]')->textInput(['placeholder' => 'เว้นว่าง = HOSPITAL'])->label('ข้อความบรรทัดที่ 1 (Header)') ?>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <?= $form->field($model, 'data_json[header_brand_line2]')->textInput(['placeholder' => 'เว้นว่าง = ERP SYSTEM'])->label('ข้อความบรรทัดที่ 2 (Header)') ?>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <?= $form->field($model, 'data_json[header_brand_google_font]')->textInput(['placeholder' => 'เช่น Sarabun, Kanit'])->label('Google Font (ชื่อฟอนต์)') ?>
                </div>
                <div class="col-12 col-md-4">
                    <?= $form->field($model, 'data_json[header_brand_line1_size]')->textInput(['placeholder' => '1.35rem'])->label('บรรทัด 1 — ขนาด') ?>
                </div>
                <div class="col-12 col-md-4">
                    <?= $form->field($model, 'data_json[header_brand_line1_color]')->textInput(['placeholder' => '#ffffff'])->label('บรรทัด 1 — สี (hex หรือ rgba)') ?>
                </div>
                <div class="col-12 col-md-4">
                    <?= $form->field($model, 'data_json[header_brand_line1_weight]')->dropDownList([
                        '' => 'ค่าเริ่มต้น (700)',
                        '300' => '300',
                        '400' => '400',
                        '500' => '500',
                        '600' => '600',
                        '700' => '700',
                        '800' => '800',
                        'normal' => 'normal',
                        'bold' => 'bold',
                    ])->label('บรรทัด 1 — ความหนา') ?>
                </div>
                <div class="col-12 col-md-4">
                    <?= $form->field($model, 'data_json[header_brand_line2_size]')->textInput(['placeholder' => '11px'])->label('บรรทัด 2 — ขนาด') ?>
                </div>
                <div class="col-12 col-md-4">
                    <?= $form->field($model, 'data_json[header_brand_line2_color]')->textInput(['placeholder' => 'rgba(255,255,255,0.5)'])->label('บรรทัด 2 — สี') ?>
                </div>
                <div class="col-12 col-md-4">
                    <?= $form->field($model, 'data_json[header_brand_line2_weight]')->dropDownList([
                        '' => 'ค่าเริ่มต้น (500)',
                        '300' => '300',
                        '400' => '400',
                        '500' => '500',
                        '600' => '600',
                        '700' => '700',
                        'normal' => 'normal',
                        'bold' => 'bold',
                    ])->label('บรรทัด 2 — ความหนา') ?>
                </div>
            </div>

            <div class="row g-3 mt-2">
                <div class="col-12 text-center">
                    <?= AppHelper::BtnSave() ?>
                </div>
            </div>
        </div>

        <?php ActiveForm::end(); ?>

    </div>
</div>


<?php
$ref = $model->ref;
$urlUpload = Url::to('/filemanager/uploads/single');
$js = <<< JS

                \$(".select-photo").click(function() {
                    \$("input[id='my_file']").click();
                });


                \$('#my_file').change(function (e) {
                    e.preventDefault();
                    formdata = new FormData();
                    if(\$(this).prop('files').length > 0)
                    {
                        file =\$(this).prop('files')[0];
                        formdata.append("logo", file);
                        formdata.append("id", 1);
                        formdata.append("ref", '$ref');
                        formdata.append("name",'logo');

                        console.log(file);
                        \$.ajax({
                            url: '$urlUpload',
                            type: "POST",
                            data: formdata,
                            processData: false,
                            contentType: false,
                            success: function (res) {
                                console.log(res);
                                \$('.avatar-profile').attr('src', res.img)
                                window.location.reload(true);
                                // success('แก้ไขภาพ')
                            }
                        });
                    }
                });

    JS;
$this->registerJS($js)
?>
