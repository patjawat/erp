<?php

use yii\helpers\Url;
use yii\helpers\Html;
use app\models\Categorise;
use kartik\widgets\DepDrop;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
use kartik\tree\TreeViewInput;
use kartik\widgets\ActiveForm;
use app\modules\am\components\AssetHelper;

/** @var $model app\modules\plan\models\Plan */
/** @var $items app\modules\plan\models\PlanItem[] */

// หมวดพัสดุ (asset_group) -> plan_category ที่ใช้ดึงรายการ (item pool)
// ครุภัณฑ์ต่ำกว่าเกณฑ์ไม่แยกหมวด รวมอยู่ใต้ "ครุภัณฑ์" (item เป็นตัวกำหนด INV_01/INV_03)
$groupLabels = ['1' => 'ที่ดิน', '2' => 'อาคาร', '3' => 'สิ่งปลูกสร้าง', '4' => 'ครุภัณฑ์', '7' => 'วัสดุ'];
$groupCats   = [
    '1' => ['INV_02'], '2' => ['INV_02'], '3' => ['INV_02'],
    '4' => ['INV_01', 'INV_03'],
    '7' => ['OPS_03'],
];

$itemRows = Categorise::find()
    ->where(['name' => 'plan_item'])
    ->andWhere(['category_id' => ['INV_01', 'INV_02', 'INV_03', 'OPS_03']])
    ->orderBy('code')
    ->all();
$itemsByCat = [];
foreach ($itemRows as $r) {
    $itemsByCat[$r->category_id][] = [$r->code, $r->title];
}
$groupItems = [];
foreach ($groupCats as $g => $cats) {
    $groupItems[$g] = [];
    foreach ($cats as $c) {
        foreach (($itemsByCat[$c] ?? []) as $it) {
            $groupItems[$g][] = $it;
        }
    }
}

// preselect หมวดพัสดุ (โหมดแก้ไข): จาก asset_group_id หรืออนุมานจากหมวดของ item
$curItem  = (string) $model->plan_item_id;
$curGroup = (string) $model->asset_group_id;
if ($curGroup === '' && $curItem !== '') {
    $ci = Categorise::findOne(['code' => $curItem, 'name' => 'plan_item']);
    if ($ci) {
        foreach ($groupCats as $g => $cats) {
            if (in_array($ci->category_id, $cats, true)) {
                $curGroup = $g;
                break;
            }
        }
    }
}
$model->asset_group_id = $curGroup;

// ประเภทวัสดุ (asset_type M1-M16) สำหรับดึงการเบิกปีก่อน
$assetTypes = ArrayHelper::map(
    Categorise::find()->where(['name' => 'asset_type'])->andWhere(['like', 'code', 'M%', false])->orderBy('code')->all(),
    'code',
    'title'
);

// scope: 'plan' = ระบบแผนงาน (เลือกหน่วยงานได้) | 'me' = หัวหน้าจัดทำ (ล็อกหน่วยงานตัวเอง)
$scope        = $scope ?? 'plan';
$lockDept     = $lockDept ?? null;
$lockDeptName = $lockDeptName ?? '';

// หน่วยงานจากทะเบียนกลาง (org_unit) ของปีนี้ — จัดกลุ่ม+เยื้องเหมือนหน้าตั้งค่า (scope=plan)
$ouGroups = [];
if ($scope !== 'me') {
    $ouGroups = \app\modules\settings\models\OrgUnit::groupedForSelect((int) $model->thai_year);
    // ให้ pull JS แปลง plan_unit_id -> tree.id (ref_id) ; null=หน่วยนอกผัง
    $this->registerJs('window.__ouRef = ' . \yii\helpers\Json::encode(\app\modules\settings\models\OrgUnit::refMap((int) $model->thai_year)) . ';', \yii\web\View::POS_HEAD);
}

$form = ActiveForm::begin(array_merge(
    ['id' => 'form'],
    $scope === 'me'
        ? ['enableAjaxValidation' => false]
        : ['enableAjaxValidation' => true, 'validationUrl' => ['/plan/parcel/validator']]
));
?>

<div class="row">
    <div class="col-lg-12 col-md-12">
        <div class="card">
            <div class="card-body">

                <?= $form->field($model, 'plan_group_id')->hiddenInput()->label(false) ?>
                <?= $form->field($model, 'plan_type_id')->hiddenInput()->label(false) ?>
                <?= $form->field($model, 'plan_category_id')->hiddenInput()->label(false) ?>
                <!-- ข้อมูลแผน -->
                <div class="row">
                    <div class="col-md-3">
                        <?= $form->field($model, 'thai_year')->textInput(['maxlength' => true, 'readonly' => true])->hint('ตามรอบทำแผนที่เปิด') ?>
                    </div>
                    <div class="col-md-9">
                        <?php if ($scope === 'me' && $lockDept): ?>
                            <?= $form->field($model, 'department_id')->hiddenInput(['id' => 'treeID', 'value' => $lockDept])->label(false) ?>
                            <label class="form-label">หน่วยงาน</label>
                            <div class="form-control-plaintext fw-semibold"><?= Html::encode($lockDeptName) ?></div>
                        <?php else: ?>
                            <?= $form->field($model, 'plan_unit_id')->widget(Select2::class, [
                                'data' => $ouGroups,
                                'options' => ['id' => 'plan-unit', 'placeholder' => '-- เลือกหน่วยงาน --'],
                                'pluginOptions' => ['allowClear' => false],
                            ])->label('หน่วยงาน')->hint('เลือกจากทะเบียนหน่วยงาน (โครงสร้าง/ทีมประสาน/นอกผัง)'); ?>
                        <?php endif; ?>
                    </div>

                   
                    <div class="col-lg-6 col-md-6 col-sm-12">
                        <?= $form->field($model, 'asset_group_id')->dropDownList($groupLabels, [
                            'id' => 'asset-group-select',
                            'prompt' => '-- เลือกหมวดพัสดุ --',
                        ])->label('หมวดพัสดุ') ?>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-12" id="item-select-wrap">
                        <?= $form->field($model, 'plan_item_id')->dropDownList([], [
                            'id' => 'planorder-plan_item_id',
                            'prompt' => '-- เลือกรายการ --',
                        ])->label('รายการ') ?>
                    </div>
                     <div class="col-lg-3 col-md-3 col-sm-12">
                        <?php

                        echo $form->field($model, 'price_ref')->widget(Select2::classname(), [
                            'data' => $model->listPriceRef(),
                            'options' => [
                                'placeholder' => 'เลือกอ้างการอิงตามราคา',
                            ],
                            'pluginOptions' => [
                                'allowClear' => true,
                            ],
                            'pluginEvents' => [
                                "select2:select" => "function() { 
                                    if($(this).val() == 'MARKET')
                                    {
                                        $('.btn-disable').hide()
                                        $('#add-row').show()
                                        $('#btn-show-asset').hide()
                                    }else{
                                        $('.btn-disable').hide()
                                        $('#add-row').hide()
                                        $('#btn-show-asset').show()
                                        }
                                }",
                            ],
                        ])->label('อ้างอิงตามราคา');
                        ?>
                    </div>

                    <div class="col-lg-6 col-md-6 col-sm-12">
                        <?= $form->field($model, 'description')->textInput()->label('วัตถุประสงค์') ?>
                        <?= $form->field($model, 'reference')->textarea(['rows' => 2, 'placeholder' => 'เอกสาร/หลักฐาน ประกอบการพิจารณา'])->label('เอกสาร/ข้อมูลอ้างอิง') ?>
                    </div>
                     
                       <div class="col-lg-6 col-md-6 col-sm-12">
                        <?php

                        echo $form->field($model, 'plan_budget_type_id')->widget(Select2::classname(), [
                            'data' => $model->listBudgetType(),
                            'options' => [
                                'placeholder' => 'เลือกแหล่งของเงิน',
                            ],
                            'pluginOptions' => [
                                'allowClear' => true,
                            ],
                        ])->label('แหล่งของเงิน');
                        ?>
                    </div>

                </div>

                <hr>
               

                <hr>
                <div class="card bg-light border-0 mb-3" id="vasdu-wrap" style="display:none">
                    <div class="card-body py-2">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-5">
                                <label class="form-label small mb-1">ประเภทวัสดุ <span class="text-danger">*</span></label>
                                <?= Html::activeDropDownList($model, 'asset_type_id', $assetTypes, [
                                    'id' => 'pull-asset-type',
                                    'class' => 'form-select form-select-sm',
                                    'prompt' => '-- เลือกประเภทวัสดุ --',
                                ]) ?>
                            </div>
                            <div class="col-md-auto">
                                <div class="form-check mb-1">
                                    <input class="form-check-input" type="checkbox" id="pull-include-children">
                                    <label class="form-check-label small" for="pull-include-children">รวมหน่วยงานย่อย (กรณีกลุ่มงานแม่)</label>
                                </div>
                                <button type="button" class="btn btn-sm btn-info text-white" id="btn-pull-consumption">
                                    <i class="fa-solid fa-clock-rotate-left me-1"></i> ดึงจากแผนคาดการณ์
                                </button>
                            </div>
                            <div class="col">
                                <small class="text-muted" id="pull-info">ระบบจะคาดการณ์ปริมาณใช้ทั้งปีจากยอดเบิกจริงของหน่วยงาน แล้วเฉลี่ยเป็นรายเดือน (÷12) ให้อัตโนมัติ</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6>รายการในแผน</h6>
                    <div>
                        <button type="button" class="btn btn-secondary btn-disable" disabled><i class="fa-solid fa-ban"></i> เพิ่มรายการ</button>
                        <button type="button" class="btn btn-primary" id="add-row"><i class="fa-solid fa-circle-plus"></i> เพิ่มรายการ</button>
                        <?php if ($scope !== 'me'): ?>
                            <?= Html::a('<i class="bi bi-ui-checks"></i> เพิ่มรายการ', ['/plan/parcel/list-asset-item'], ['class' => 'btn btn-primary', 'id' => 'btn-show-asset']) ?>
                        <?php endif; ?>
                    </div>
                </div>
                <table class="table table-bordered" id="item-table">
                    <thead>
                        <tr>
                            <th>ชื่อรายการ</th>
                            <th width="150">จำนวน</th>
                            <th width="200">ราคาต่อหน่วย</th>
                            <th width="200" class="text-end">รวม</th>
                            <th width="50">#</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($items): ?>
                            <?php foreach ($items as $i => $item): ?>
                                <tr>
                                    <td><input type="text" name="items[<?= $i ?>][item_name]" value="<?= Html::encode($item->item_name) ?>" class="form-control"></td>
                                    <td><input type="number" name="items[<?= $i ?>][qty]" value="<?= $item->qty ?>" class="form-control qty"></td>
                                    <td><input type="number" step="0.01" name="items[<?= $i ?>][unit_price]" value="<?= $item->unit_price ?>" class="form-control price"></td>
                                    <td class="total text-end"><?= number_format($item->qty * $item->unit_price, 2) ?></td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-row">ลบ</button></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>

                        <?php endif; ?>
                    </tbody>
                </table>

                <?= $form->field($model, 'order_price')->textInput()->label('รวมเป็นจำนวนเงินทั้งสิ้น') ?>

                 <div>
                    แผนการใช้จ่าย
                    <div class="row">
                        <div class="col-lg-2 col-md-3 col-sm-6"> <?= $form->field($model, 'month_10')->input('number', ['step' => '0.01'])->label('ต.ค.') ?></div>
                        <div class="col-lg-2 col-md-3 col-sm-6"> <?= $form->field($model, 'month_11')->input('number', ['step' => '0.01'])->label('พ.ย.') ?></div>
                        <div class="col-lg-2 col-md-3 col-sm-6"> <?= $form->field($model, 'month_12')->input('number', ['step' => '0.01'])->label('ธ.ค.') ?></div>

                        <div class="col-lg-2 col-md-3 col-sm-6"> <?= $form->field($model, 'month_1')->input('number', ['step' => '0.01'])->label('ม.ค.') ?></div>
                        <div class="col-lg-2 col-md-3 col-sm-6"> <?= $form->field($model, 'month_2')->input('number', ['step' => '0.01'])->label('ก.พ.') ?></div>
                        <div class="col-lg-2 col-md-3 col-sm-6"> <?= $form->field($model, 'month_3')->input('number', ['step' => '0.01'])->label('มี.ค.') ?></div>

                        <div class="col-lg-2 col-md-3 col-sm-6"> <?= $form->field($model, 'month_4')->input('number', ['step' => '0.01'])->label('เม.ย.') ?></div>
                        <div class="col-lg-2 col-md-3 col-sm-6"> <?= $form->field($model, 'month_5')->input('number', ['step' => '0.01'])->label('พ.ค.') ?></div>
                        <div class="col-lg-2 col-md-3 col-sm-6"> <?= $form->field($model, 'month_6')->input('number', ['step' => '0.01'])->label('มิ.ย.') ?></div>
                        <div class="col-lg-2 col-md-3 col-sm-6"> <?= $form->field($model, 'month_7')->input('number', ['step' => '0.01'])->label('ก.ค.') ?></div>
                        <div class="col-lg-2 col-md-3 col-sm-6"> <?= $form->field($model, 'month_8')->input('number', ['step' => '0.01'])->label('ส.ค.') ?></div>
                        <div class="col-lg-2 col-md-3 col-sm-6"> <?= $form->field($model, 'month_9')->input('number', ['step' => '0.01'])->label('ก.ย.') ?></div>
                    </div>
                </div>


                <div class="d-flex justify-content-center align-items-center">
                    <div class="d-flex gap-2">
                        <?= Html::submitButton('บันทึก', ['class' => 'btn btn-success']) ?>
                        <?= Html::a('ยกเลิก', ['index'], ['class' => 'btn btn-light']) ?>
                    </div>
                </div>
            </div>
        </div>


    </div>


</div>

<?php ActiveForm::end(); ?>

<?php
$js = <<<JS

checkBtnAdd()
function checkBtnAdd()
{
    if('$scope' === 'me'){
                    $('.btn-disable').hide()
                    $('#add-row').show()
                    $('#btn-show-asset').hide()
                    return
    }
    if($('#planorder-price_ref').val() === 'MARKET'){
                    $('.btn-disable').hide()
                    $('#add-row').show()
                    $('#btn-show-asset').hide()
    }else{
          $('.btn-disable').hide()
                    $('#add-row').hide()
                    $('#btn-show-asset').show()
    }
}


$('#form').on('beforeSubmit', function (e) {
    e.preventDefault();
    let form = $(this);

    let valid = true;
        let message = "";
    $("#item-table tbody tr").each(function(index, row){
        // ดึง input แต่ละช่อง
        let item_name = $(row).find("input[name*='[item_name]']");
        let qty       = $(row).find("input[name*='[qty]']");
        let price     = $(row).find("input[name*='[unit_price]']");

        // reset class ก่อน
        item_name.removeClass("is-invalid");
        qty.removeClass("is-invalid");
        price.removeClass("is-invalid");

        // ตรวจสอบค่า
        if((item_name.val() || "").trim() === ""){
            valid = false;
            item_name.addClass("is-invalid");
        }
        if((qty.val() || "").trim() === ""){
            valid = false;
            qty.addClass("is-invalid");
        }
        if((price.val() || "").trim() === ""){
            valid = false;
            price.addClass("is-invalid");
        }
    });


        // if(!valid){
        //     e.preventDefault(); // หยุดการ submit
        //     return false;
        // }


    Swal.fire({
        title: 'ยืนยันการบันทึก?',
        text: "คุณต้องการบันทึกข้อมูลหรือไม่?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'ใช่, บันทึกเลย!',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            // scope me: ส่งแบบปกติ ให้ controller redirect + แสดง flash เอง (ไม่ใช่ฟอร์มใน modal)
            if ('$scope' === 'me') {
                form.off('beforeSubmit');
                form[0].submit();
                return;
            }
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                dataType: 'json',
                beforeSend: async function () {

                },
                success: async function (response) {
                    if (response.status === 'success') {
                        closeModal();
                        await Swal.fire({
                            title: 'บันทึกสำเร็จ!',
                            text: 'ข้อมูลของคุณถูกบันทึกเรียบร้อยแล้ว',
                            icon: 'success',
                            timer: 2000, // ปิดอัตโนมัติใน 3 วินาที
                            confirmButtonText: 'ตกลง'
                        });
                        window.location.href = response.url; // Redirect to the provided URL
                    }
                },
            });
        }
    });

    return false;
});


if($('#planorder-price_ref').val() == '')
{
    $('.btn-disable').show()
    $('#add-row').hide()
    $('#btn-show-asset').hide()
}


$('#btn-show-asset').click(function (e) { 
    e.preventDefault();
     beforLoadModal();

    $.ajax({
        type: "get",
        url: $(this).attr('href'),
        data: {
            asset_group_id:$('#asset_group_id').val(),
            asset_type_id: $('#asset_type_id').val(),
            asset_category_id: $('#asset_category_id').val(),
        },
        dataType: "json",
        success: function (response) {
      $("#main-modal").modal("show");
      $("#main-modal-label").html(response.title);
      $(".modal-body").html(response.content);
      $(".modal-footer").html(response.footer);
      $(".modal-dialog").removeClass("modal-sm modal-md modal-lg modal-xl modal-xxl");
      $(".modal-dialog").addClass('modal-xxl');
      $(".modal-content").addClass("card-outline card-primary");
    },
     error: function (xhr) {
      $("#main-modal-label").html("เกิดข้อผิดพลาด");
      $(".modal-body").html(
        '<h5 class="text-center"><i class="fa-solid fa-triangle-exclamation text-danger"></i> ไม่อนุญาต</h5>'
      );
      $(".modal-dialog").removeClass("modal-sm modal-md modal-lg modal-xl modal-xxl");
      $(".modal-dialog").addClass("modal-md");
    
    },
  });
});

let rowIndex = $("#item-table tbody tr").length;
$("#add-row").on("click", function(){
    let row = `<tr>
        <td><input type="text" name="items[\${rowIndex}][item_name]" class="form-control"></td>
        <td><input type="number" name="items[\${rowIndex}][qty]" class="form-control qty"></td>
        <td><input type="number" step="0.01" name="items[\${rowIndex}][unit_price]" class="form-control price"></td>
        <td class="total text-end">0.00</td>
        <td><button type="button" class="btn btn-danger btn-sm remove-row">ลบ</button></td>
    </tr>`;
    $("#item-table tbody").append(row);
    rowIndex++;
});

$(document).on("click",".remove-row", function(){ $(this).closest("tr").remove(); });
$(document).on("input",".qty, .price", function(){
    let tr = $(this).closest("tr");
    let qty = parseFloat(tr.find(".qty").val())||0;
    let price = parseFloat(tr.find(".price").val())||0;
    tr.find(".total").text((qty*price).toFixed(2));
});


$(document).ready(function() {
    function calculateTotal() {
        let total = 0;
        // loop input ทุกช่องที่เป็น month_1 .. month_12
        $("input[id^='planorder-month_']").each(function() {
            let val = parseFloat($(this).val()) || 0;
            total += val;
        });
        $("#planorder-order_price").val(total.toFixed(2)); // ใส่ค่าผลรวมลงไป
    }

    // ฟัง event เวลา keyup หรือเปลี่ยนค่า
    $(document).on("input", "input[id^='planorder-month_']", function() {
        calculateTotal();
    });
});




JS;
$this->registerJs($js);

// cascade: หมวดพัสดุ -> รายการ (กรอง item ตามหมวด)
$giJson = \yii\helpers\Json::encode($groupItems);
$ciJson = \yii\helpers\Json::encode($curItem);
$cascadeJs = <<<JS
(function(){
    var groupItems = $giJson;
    var curItem = $ciJson;
    var groupSel = document.getElementById('asset-group-select');
    var itemSel = document.getElementById('planorder-plan_item_id');
    if (!groupSel || !itemSel) return;
    var itemWrap  = document.getElementById('item-select-wrap');
    var vasduWrap = document.getElementById('vasdu-wrap');
    function loadItems(sel){
        itemSel.innerHTML = '<option value="">-- เลือกรายการ --</option>';
        (groupItems[groupSel.value] || []).forEach(function(r){
            var o = document.createElement('option');
            o.value = r[0]; o.textContent = r[1];
            if (String(r[0]) === String(sel)) o.selected = true;
            itemSel.appendChild(o);
        });
    }
    // หมวด=วัสดุ (7): ใช้ประเภทวัสดุช่องเดียว ซ่อน "รายการ"; หมวดอื่น: ใช้ "รายการ"
    function toggleWraps(){
        var isVasdu = (String(groupSel.value) === '7');
        if (vasduWrap) vasduWrap.style.display = isVasdu ? '' : 'none';
        if (itemWrap)  itemWrap.style.display  = isVasdu ? 'none' : '';
    }
    groupSel.addEventListener('change', function(){ loadItems(''); toggleWraps(); });
    if (groupSel.value) { loadItems(curItem); }
    toggleWraps();
})();
JS;
$this->registerJs($cascadeJs);

// ปุ่ม "ดึงจากการเบิกปีก่อน" -> เติมตารางรายการ + เฉลี่ยรายเดือน (÷12)
$pullUrl = \yii\helpers\Url::to(['pull-consumption']);
$pullJs = <<<JS
$('#btn-pull-consumption').on('click', function(){
    var atype = $('#pull-asset-type').val();
    var puid  = $('#plan-unit').val();
    var dept  = (window.__ouRef && puid) ? window.__ouRef[puid] : ($('#treeID').val() || $('[name="department"]').val());
    var year  = $('#planorder-thai_year').val();
    var incChild = $('#pull-include-children').is(':checked') ? 1 : 0;
    if(!atype){ Swal.fire('กรุณาเลือกประเภทวัสดุก่อน'); return; }
    if(!dept){ Swal.fire('กรุณาเลือกหน่วยงานก่อน'); return; }
    $('#pull-info').text('กำลังดึงข้อมูล...');
    $.post('$pullUrl', {department_id: dept, asset_type_id: atype, thai_year: year, include_children: incChild}, function(res){
        if(res.status !== 'success'){ $('#pull-info').text(res.message || 'เกิดข้อผิดพลาด'); return; }
        if(!res.items.length){ $('#pull-info').text('ไม่พบการเบิกใช้ของหน่วยงานนี้ในปีงบ ' + res.prev_year); return; }
        var tbody = $('#item-table tbody'); tbody.empty();
        var total = 0, idx = 0;
        res.items.forEach(function(it){
            var qty = parseFloat(it.qty_year) || 0;
            var price = parseFloat(it.last_price) || 0;
            var lineTotal = qty * price;
            total += lineTotal;
            var nameEsc = $('<div>').text(it.name).html();
            tbody.append('<tr>' +
                '<td><input type="text" name="items[' + idx + '][item_name]" class="form-control" value="' + nameEsc + '"></td>' +
                '<td><input type="number" name="items[' + idx + '][qty]" class="form-control qty" value="' + qty + '"></td>' +
                '<td><input type="number" step="0.01" name="items[' + idx + '][unit_price]" class="form-control price" value="' + price + '"></td>' +
                '<td class="total text-end">' + lineTotal.toFixed(2) + '</td>' +
                '<td><button type="button" class="btn btn-danger btn-sm remove-row">ลบ</button></td>' +
                '</tr>');
            idx++;
        });
        // เฉลี่ยยอดรวมเป็นรายเดือน (÷12) เดือนสุดท้ายรับเศษ
        var per = Math.floor((total / 12) * 100) / 100, acc = 0;
        for(var m = 1; m <= 12; m++){
            var val = (m < 12) ? per : (total - acc);
            $('#planorder-month_' + m).val(val.toFixed(2));
            if(m < 12) acc += per;
        }
        $('#planorder-order_price').val(total.toFixed(2));
        var scopeTxt = (res.child_count > 0) ? (' รวม ' + res.child_count + ' หน่วยย่อย') : '';
        // บอกที่มาของตัวเลขให้ครบ ผู้ตั้งงบจะได้รู้ว่าตัวเลขถูกปรับมาแล้วกี่ชั้น
        var basisTxt = 'ยอดใช้จริงปีงบ ' + res.prev_year;
        if (res.months_covered && res.months_covered < 12) {
            basisTxt += ' (' + res.months_covered + ' เดือน ปรับเต็มปี ×' + res.annual_factor + ')';
        }
        if (res.growth_pct) { basisTxt += ' บวกเผื่อ ' + res.growth_pct + '%'; }
        $('#pull-info').html('<i class="fa-solid fa-check text-success me-1"></i>ดึง ' + res.count + ' รายการ' + scopeTxt
            + ' — คำนวณจาก' + basisTxt + ' เฉลี่ย ÷12 แล้ว (แก้ไข/เพิ่มได้)');
    }, 'json').fail(function(){ $('#pull-info').text('เชื่อมต่อเซิร์ฟเวอร์ไม่ได้'); });
});
JS;
$this->registerJs($pullJs);
?>