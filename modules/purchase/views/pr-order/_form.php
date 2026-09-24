<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use app\modules\hr\models\Employees;
use app\widgets\datepicker\DatepickerThai;
/** @var yii\web\View $this */
/** @var app\modules\sm\models\Inventory $model */
$this->title = 'ราการขอซื้อ';
$this->params['breadcrumbs'][] = ['label' => 'Inventories', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

$employee = Employees::find()->where(['user_id' => Yii::$app->user->id])->one();

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

?>
<style>
.col-form-label {
    text-align: end;
}

</style>

<?php $form = ActiveForm::begin([
    'id' => 'form-order',
     'enableAjaxValidation' => true, //เปิดการใช้งาน AjaxValidation
    'validationUrl' => ['/purchase/pr-order/createvalidator'],
]); ?>

<?php // popup modal-xl: 2 คอลัมน์ (5:7) ซ้าย = ข้อมูลใบ / ขวา = กรรมการ+แผน — ลดการเลื่อน scroll ?>
<div class="row">
    <div class="col-lg-5">
<div class="row">
    <div class="col-6">

    <?=$form->field($model, 'data_json[pr_create_date]')->textInput(['placeholder' => 'เลือกวันที่ขอซื้อ'])->label('วันที่ขอซื้อ');
                ?>

</div>
        <div class="col-6">
        <?=$form->field($model, 'data_json[due_date]')->textInput(['placeholder' => 'เลือกวันที่ต้องการ'])->label('วันที่ต้องการ');?>


    </div>
</div>



<?php
        echo $form->field($model, 'vendor_id')->widget(Select2::classname(), [
            'data' => $model->ListVendor(),
            'options' => ['placeholder' => 'เลือกบริษัทแนะนำ)'],
            'pluginOptions' => [
                'allowClear' => true,
                'dropdownParent' => '#main-modal',
            ],
            'pluginEvents' => [
                "select2:unselecting" => "function() { 
                    $('#order-data_json-vendor_address').val('')
                    $('#order-data_json-vendor_phone').val('')
                    $('#order-data_json-vendor_tax').val('')
                    $('#order-data_json-account_name').val('')
                    $('#order-data_json-account_number').val('')
                    $('#order-data_json-contact_name').val('')
                    $('#order-data_json-contact_position').val('')
                }",
                'select2:select' => "function(result) { 
                                    var data =  $(this).select2('data')[0].text;
                                      $('#order-data_json-vendor_name').val(data)
                                    $.ajax({
                                        type: 'get',
                                        url: '/depdrop/get-vendor',
                                        data:{id:$(this).val()},
                                        dataType:'json',
                                        success: function (res) {
                                            $('#order-data_json-vendor_address').val(res.data_json.address)
                                            $('#order-data_json-vendor_phone').val(res.data_json.phone)
                                            $('#order-data_json-vendor_tax').val(res.code)
                                            $('#order-data_json-account_name').val(res.data_json.account_name)
                                            $('#order-data_json-account_number').val(res.data_json.account_number)
                                            $('#order-data_json-contact_name').val(res.data_json.contact_name)
                                            $('#order-data_json-contact_position').val(res.data_json.contact_position)
                                        }
                                    });

                                }",
            ]
        ])->label('บริษัทแนะนำ');
    ?>

<?php
try {
    //code...
    $initEmployee =  Employees::find()->where(['id' => $model->data_json['leader1']])->one()->getAvatar(false);
} catch (\Throwable $th) {
    $initEmployee = '';
}
        echo $form->field($model, 'data_json[leader1]')->widget(Select2::classname(), [
            'initValueText' => $initEmployee,
            'options' => ['placeholder' => 'เลือก ...'],
            'size' => Select2::LARGE,
            'pluginEvents' => [
                'select2:unselect' => 'function() {
                $("#order-data_json-board_fullname").val("")

         }',
                'select2:select' => 'function() {
                var fullname = $(this).select2("data")[0].fullname;
                var position_name = $(this).select2("data")[0].position_name;
                $("#order-data_json-board_fullname").val(fullname)
                $("#order-data_json-position_name").val(position_name)
               
         }',
            ],
            'pluginOptions' => [
                'dropdownParent' => '#main-modal',
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
        ])->label('ผู้เห็นชอบ')
    ?>

<?= $form->field($model, 'data_json[comment]')->textArea(['rows' => 4])->label('รายละเอียดและความจำเป็น') ?>
    </div><!-- /คอลัมน์ซ้าย -->
    <div class="col-lg-7">
<?php
// ปีที่เปิด "จัดซื้อผูกแผน": ไม่ต้องเลือกในแผน/นอกแผน — เลือกกรรมการ + รายการแผน แล้วระบบตัดสินตอนส่งคำขอ
if (\app\modules\purchase\components\PurchasePlanControl::isControlled($model)):
?>
<?php
// กรรมการตรวจรับ: ผู้ขอระบุเองตั้งแต่ขอซื้อ (บันทึกเป็นแถว name=committee ผ่าน Order::syncCommittee)
$committeeData = [];
$committeeIds = [];
if (!$model->isNewRecord) {
    foreach ($model->ListCommittee() as $c) {
        $eid = (string) ($c->data_json['employee_id'] ?? '');
        if ($eid !== '') {
            $committeeIds[] = $eid;
            $committeeData[$eid] = $c->data_json['emp_fullname'] ?? $eid;
        }
    }
}
echo $form->field($model, 'data_json[committee_ids]')->widget(Select2::classname(), [
    'data' => $committeeData,
    'options' => ['placeholder' => 'พิมพ์ชื่อเพื่อค้นหา ...', 'multiple' => true, 'value' => $committeeIds],
    'pluginOptions' => [
        'dropdownParent' => '#main-modal',
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
        'templateSelection' => new JsExpression('function (item) { return item.fullname || item.text; }'),
        'templateResult' => new JsExpression('formatRepo'),
    ],
])->label('กรรมการตรวจรับ')->hint('1 คน = ผู้ตรวจรับพัสดุ ; หลายคน = คนแรกเป็นประธานกรรมการ (เรียงตามลำดับที่เลือก)');
?>
<?php
// รายการแผน: ระบบตัดสินในแผน/นอกแผนตอนกด "ส่งคำขอซื้อ" (เทียบยอดกับวงเงินคงเหลือ) — ส่งแล้วเปลี่ยนแผนไม่ได้
$planLocked = (string) $model->status !== '';
$planYear = (int) ($model->thai_year ?: \app\components\AppHelper::YearBudget());
$planTree = \app\modules\purchase\components\PurchasePlanControl::planTree($planYear, $model->id ? (int) $model->id : null);
$dis = $planLocked ? 'disabled' : '';
?>
<fieldset class="border rounded p-2 mb-3" id="plan-picker">
    <legend class="float-none w-auto px-2 fs-6 mb-0">รายการแผน <small class="text-muted">(ปีงบ <?= $planYear ?>)</small></legend>
    <div class="row g-2">
        <div class="col-12">
            <label class="form-label small mb-1">1. ประเภทแผน</label>
            <select class="form-select form-select-sm" id="pp-type" <?= $dis ?>></select>
        </div>
        <div class="col-12">
            <label class="form-label small mb-1">2. หมวด</label>
            <select class="form-select form-select-sm" id="pp-cat" <?= $dis ?>></select>
        </div>
        <div class="col-12">
            <label class="form-label small mb-1">3. แผนงาน</label>
            <select class="form-select form-select-sm" id="pp-item" <?= $dis ?>></select>
        </div>
        <div class="col-12">
            <label class="form-label small mb-1">4. รายการแผน</label>
            <?= Html::activeDropDownList($model, 'plan_order_id', [], ['class' => 'form-select form-select-sm', 'id' => 'pp-plan', 'disabled' => $planLocked]) ?>
        </div>
        <div class="col-12"><div id="pp-info" class="small"></div></div>
    </div>
    <div class="form-text">
        <?= $planLocked
            ? 'ส่งคำขอแล้ว — ' . Html::encode($model->requestType()['label']) . ' (เปลี่ยนแผนไม่ได้)'
            : 'แสดงเฉพาะแผนที่อนุมัติแล้ว ทุกหน่วยงาน — ยอดไม่เกินวงเงินคงเหลือ = <b>ในแผน</b> ผ่านอนุมัติอัตโนมัติ ; ไม่เลือก/เกินวงเงิน = <b>นอกแผน</b> ต้องรออนุมัติ (ตรวจตอนกดส่งคำขอซื้อ)' ?>
    </div>
</fieldset>
<?php
$planTreeJson = json_encode($planTree, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
$planCurrent = (int) $model->plan_order_id;
$this->registerJs(<<<JS
(function () {
    var tree = {$planTreeJson}.types, current = {$planCurrent};
    var \$t = $('#pp-type'), \$c = $('#pp-cat'), \$i = $('#pp-item'), \$p = $('#pp-plan'), \$info = $('#pp-info');
    var fmt = function (n) { return Number(n).toLocaleString('th-TH', {minimumFractionDigits: 2, maximumFractionDigits: 2}); };
    var esc = function (s) { return $('<span>').text(s == null ? '' : s).html(); };
    function fill(\$sel, list, ph, label) {
        \$sel.empty().append($('<option>').val('').text(ph));
        (list || []).forEach(function (x, idx) { \$sel.append($('<option>').val(x.code !== undefined ? idx : x.id).text(label(x))); });
    }
    function cats() { var t = tree[\$t.val()]; return t ? t.cats : []; }
    function items() { var c = cats()[\$c.val()]; return c ? c.items : []; }
    function plans() { var i = items()[\$i.val()]; return i ? i.plans : []; }
    function showInfo() {
        var id = parseInt(\$p.val(), 10), pl = plans().filter(function (x) { return x.id === id; })[0];
        if (!pl) {
            \$info.html(tree.length ? '<div class="alert alert-light border py-1 px-2 mb-0">ไม่เลือกรายการแผน = <b>นอกแผน</b> ต้องรออนุมัติ</div>'
                : '<div class="alert alert-warning py-1 px-2 mb-0">ปีงบนี้ยังไม่มีแผนที่อนุมัติแล้ว — ใบนี้จะเป็น <b>นอกแผน</b></div>');
            return;
        }
        \$info.html('<div class="alert alert-info py-1 px-2 mb-0">' + esc(pl.title) + '<br><span class="text-muted">' + esc(pl.unit)
            + '</span> — วงเงิน ' + fmt(pl.budget) + ' ใช้ไปแล้ว ' + fmt(pl.used) + ' <b>คงเหลือ ' + fmt(pl.remaining) + '</b> บาท'
            + '<br>ซื้อได้เฉพาะประเภท: ' + (pl.types && pl.types.length ? '<b>' + esc(pl.types.join(', ')) + '</b>'
                : '<span class="text-warning-emphasis">ยังไม่กำหนดประเภท (ไม่จำกัด)</span>') + '</div>');
    }
    fill(\$t, tree, '-- เลือกประเภทแผน --', function (x) { return x.title; });
    \$t.on('change', function () { fill(\$c, cats(), '-- เลือกหมวด --', function (x) { return x.title; }); \$c.trigger('change'); });
    \$c.on('change', function () { fill(\$i, items(), '-- เลือกแผนงาน --', function (x) { return x.title; }); \$i.trigger('change'); });
    \$i.on('change', function () {
        fill(\$p, plans(), '-- ไม่เลือก (นอกแผน) --', function (x) { return x.title + ' — ' + x.unit + ' (คงเหลือ ' + fmt(x.remaining) + ')'; });
        showInfo();
    });
    \$p.on('change', showInfo);

    // ค่าเดิม (แก้ไขใบ): หาเส้นทางของแผนที่เลือกไว้แล้วเลือกตามลำดับ
    var found = false;
    tree.forEach(function (t, ti) { t.cats.forEach(function (c, ci) { c.items.forEach(function (it, ii) { it.plans.forEach(function (p) {
        if (p.id === current && !found) {
            found = true;
            \$t.val(ti).trigger('change'); \$c.val(ci).trigger('change'); \$i.val(ii).trigger('change'); \$p.val(p.id); showInfo();
        }
    }); }); }); });
    if (!found) {
        \$t.trigger('change');
        if (current) { \$info.html('<div class="alert alert-warning py-1 px-2 mb-0">แผนที่เลือกไว้เดิม (#' + current + ') ไม่อยู่ในรายการแผนอนุมัติของปีนี้แล้ว</div>'); }
    }
})();
JS);
?>
<?php else:
echo $form->field($model, 'request_type')->radioList(
    [
    'planned' => 'ในแผน',
    'unplanned' => 'นอกแผน'
], 
    ['custom' => true,'inline' => true]
)->label('ประเภทจัดซื้อ');
endif;
?>
    </div><!-- /คอลัมน์ขวา -->
</div><!-- /row 2 คอลัมน์ -->

<?= $form->field($model, 'data_json[vendor_address]')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'data_json[vendor_phone]')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'data_json[vendor_tax]')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'data_json[account_name]')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'data_json[account_number]')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'data_json[contact_name]')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'data_json[contact_position]')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'data_json[item_type]')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'data_json[leader1_fullname]')->hiddenInput(['value' => $employee->leaderUser()['leader1_fullname']])->label(false) ?>
<?= $form->field($model, 'data_json[department]')->hiddenInput(['value' => $model->getUserReq()['department']])->label(false) ?>
<?= $form->field($model, 'data_json[product_type_name]')->hiddenInput()->label(false) ?>

<?= $form->field($model, 'name')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'status')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'data_json[pr_leader_confirm]')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'data_json[pr_confirm_comment]')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'data_json[pr_director_confirm]')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'data_json[pr_director_comment]')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'data_json[pr_confirm_2]')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'data_json[pr_officer_checker]')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'ref')->hiddenInput()->label(false) ?>


<div class="form-group mt-3 d-flex justify-content-center">
    <?= Html::submitButton('<i class="bi bi-check2-circle"></i> ยืนยัน', ['class' => 'btn btn-primary', 'id' => 'summit']) ?>
</div>


<?php ActiveForm::end(); ?>



<?php
    $ref = $model->ref;
    $urlUpload = Url::to('/filemanager/uploads/single');
    $getAvatar = Url::to(['/filemanager/uploads/show','id' => 1]);
    $js = <<<JS



    thaiDatepicker('#order-data_json-pr_create_date,#order-data_json-due_date')
    $('#form-order').on('beforeSubmit', function (e) {
        var form = \$(this);
        Swal.fire({
        title: "ยืนยัน?",
        text: "บันทึกขออนุมัติจัดซื้อจัดจ้าง!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: "ยกเลิก!",
        confirmButtonText: "ใช่, ยืนยัน!"
        }).then((result) => {
        if (result.isConfirmed) {
            beforLoadModal()
            \$.ajax({
                url: form.attr('action'),
                type: 'post',
                data: form.serialize(),
                dataType: 'json',
                success: async function (response) {
                    form.yiiActiveForm('updateMessages', response, true);
                    if(response.status == 'success') {
                        // closeModal()
                        success()
                        await  \$.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});                               
                    }
                }
            });

        }
        });
            return false;
    });

JS;
$this->registerJS($js, View::POS_END)
    ?>