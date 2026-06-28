<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use kartik\select2\Select2;
use kartik\widgets\ActiveForm;
use app\modules\hr\models\Employees;
use softark\duallistbox\DualListbox;
use app\modules\hr\models\Organization;

/** @var yii\web\View $this */
/** @var app\modules\inventoryV2\models\Warehouse $model */
\yii\web\YiiAsset::register($this);

$formatJs = <<< 'JS'
    var formatRepo = function (repo) {
        if (repo.loading) return repo.avatar;
        var markup = '<div class="row"><div class="col-12"><span>' + repo.avatar + '</span></div></div>';
        if (repo.description) markup += '<p>' + repo.avatar + '</p>';
        return '<div style="overflow:hidden;">' + markup + '</div>';
    };
    var formatRepoSelection = function (repo) { return repo.avatar || repo.avatar; };
JS;
$this->registerJs($formatJs, View::POS_HEAD);

$resultsJs = <<< 'JS'
    function (data, params) {
        params.page = params.page || 1;
        return { results: data.results, pagination: { more: (params.page * 30) < data.total_count } };
    }
JS;
?>
<style>.col-form-label { text-align: end; }</style>
<div class="warehouse-form">
    <?php
    $formAction = $model->isNewRecord
        ? Url::to(['/inventory-v2/warehouse/create'])
        : Url::to(['/inventory-v2/warehouse/update', 'id' => $model->id]);
    $form = ActiveForm::begin(['id' => 'warehouse-form', 'action' => $formAction]);
    ?>
    <div class="row">
        <div class="col-xl-7 col-lg-7 col-md-12 col-sm-12">
            <div class="d-flex justify-content-between gap-1">
                <?= $form->field($model, 'warehouse_type')->radioList(['MAIN' => 'คลังหลัก', 'SUB' => 'คลังย่อย', 'BRANCH' => 'รพ.สต.'], ['custom' => true, 'inline' => true]) ?>
            </div>
            <div class="row">
                <div class="col-7"><?= $form->field($model, 'warehouse_name')->textInput(['maxlength' => true]) ?></div>
                <div class="col-5"><?= $form->field($model, 'warehouse_code')->textInput(['maxlength' => true]) ?></div>
            </div>
            <?php
            // $initEmployee = '';
            // try {
            //     if (!empty($model->data_json['checker'])) {
            //         $emp = Employees::find()->where(['user_id' => $model->data_json['checker']])->one();
            //         if ($emp && method_exists($emp, 'getAvatar')) {
            //             $initEmployee = $emp->getAvatar(false);
            //         }
            //     }
            // } catch (\Throwable $e) {}
            // echo $form->field($model, 'data_json[checker]')->widget(Select2::classname(), [
            //     'initValueText' => $initEmployee,
            //     'options' => ['placeholder' => 'เลือก ...'],
            //     'size' => Select2::LARGE,
            //     'pluginEvents' => [
            //         'select2:unselect' => 'function() { $("#warehouse-data_json-checker_name").val(""); }',
            //         'select2:select' => 'function() {
            //             var d = $(this).select2("data")[0];
            //             if (d) {
            //                 $("#warehouse-data_json-checker_name").val(d.fullname || d.text || "");
            //                 if ($("#order-data_json-position_name").length) $("#order-data_json-position_name").val(d.position_name || "");
            //             }
            //         }',
            //     ],
            //     'pluginOptions' => [
            //         'dropdownParent' => '#main-modal',
            //         'allowClear' => true,
            //         'minimumInputLength' => 1,
            //         'ajax' => [
            //             'url' => Url::to(['/depdrop/employee-by-id']),
            //             'dataType' => 'json',
            //             'delay' => 250,
            //             'data' => new JsExpression('function(params) { return {q:params.term, page: params.page}; }'),
            //             'processResults' => new JsExpression($resultsJs),
            //             'cache' => true,
            //         ],
            //         'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
            //         'templateSelection' => new JsExpression('function (item) { return item.text; }'),
            //         'templateResult' => new JsExpression('formatRepo'),
            //     ],
            // ])->label('หัวหน้าตรวจสอบ');
            ?>
            <?php //  $form->field($model, 'data_json[checker_name]')->textInput()->label(false) ?>
        </div>
        <div class="col-xl-5 col-lg-5 col-md-12 col-sm-12">
            <input type="file" id="my_file" style="display: none;" />
            <a href="#" class="select-photo">
                <?php if ($model->isNewRecord): ?>
                    <?= Html::img('@web/img/placeholder-img.jpg', ['class' => 'avatar-profile h-40 object-cover rounded img-fluid']) ?>
                <?php else: ?>
                    <?= Html::img($model->ShowImg(), ['class' => 'avatar-profile h-40 object-cover rounded img-fluid']) ?>
                <?php endif; ?>
            </a>
            <small>อัตรส่วนที่เหมาะสม 7360 × 4912</small>
        </div>
        <div class="col-12">
            <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="pills-home-tab" data-bs-toggle="pill" data-bs-target="#pills-home" type="button" role="tab">กำหนดผู้รับผิดชอบคลัง</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pills-profile-tab" data-bs-toggle="pill" data-bs-target="#pills-profile" type="button" role="tab">กำหนดประเภทที่รับเข้า</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pills-contact-tab" data-bs-toggle="pill" data-bs-target="#pills-contact" type="button" role="tab">กำหนดแผนก/ฝ่ายที่มีสิทธิเบิก</button>
                </li>
            </ul>
            <div class="tab-content" id="pills-tabContent">
                <div class="tab-pane fade show active" id="pills-home" role="tabpanel">
                    <div class="d-flex align-items-center bg-primary bg-opacity-10 p-2 rounded mb-3 mt-3">
                        <h5 class="mb-0"><i class="fa-solid fa-circle-info text-primary"></i> กำหนดผู้รับผิดชอบคลัง</h5>
                    </div>
                    <?= DualListbox::widget([
                        'model' => $model,
                        'attribute' => 'data_json[officer]',
                        'items' => $model->listUserstore(),
                        'options' => ['multiple' => true, 'size' => 8],
                        'clientOptions' => [
                            'moveOnSelect' => false,
                            'selectedListLabel' => 'เจ้าหน้าที่รับผิดชอบคลัง',
                            'nonSelectedListLabel' => '(กำหนดสิทธิ์ warehouse ก่อนถึงจะปรากฏ)',
                        ],
                    ]) ?>
                </div>
                <div class="tab-pane fade" id="pills-profile" role="tabpanel">
                    <div class="d-flex align-items-center bg-primary bg-opacity-10 p-2 rounded mb-3 mt-3">
                        <h5 class="mb-0"><i class="fa-solid fa-circle-info text-primary"></i> กำหนดประเภทที่รับเข้า</h5>
                    </div>
                    <?= DualListbox::widget([
                        'model' => $model,
                        'attribute' => 'data_json[item_type]',
                        'items' => $model->ListOrderType(),
                        'options' => ['multiple' => true, 'size' => 8],
                        'clientOptions' => [
                            'moveOnSelect' => false,
                            'selectedListLabel' => 'ประเภทที่รับเข้า',
                            'nonSelectedListLabel' => 'รายการทั้งหมด',
                        ],
                    ]) ?>
                </div>
                <div class="tab-pane fade" id="pills-contact" role="tabpanel">
                    <?= $form->field($model, 'department')->widget(\kartik\tree\TreeViewInput::class, [
                        'query' => Organization::find()->addOrderBy('root, lft'),
                        'headingOptions' => ['label' => 'รายชื่อหน่วยงาน'],
                        'rootOptions' => ['label' => '<i class="fa fa-building"></i>'],
                        'fontAwesome' => true,
                        'asDropdown' => true,
                        'multiple' => true,
                        'options' => ['disabled' => false],
                    ])->label('แผนก/ฝ่ายภายในโรงพยาบาลตามโครงสร้าง') ?>
                </div>
            </div>
        </div>
    </div>
    <?= $form->field($model, 'is_main')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'ref')->hiddenInput(['maxlength' => 50])->label(false) ?>
    <div class="form-group mt-3 d-flex justify-content-center">
        <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary', 'id' => 'summit']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
<?php
$ref = $model->ref ?? '';
$urlUpload = Url::to('/filemanager/uploads/single');
$js = <<< JS
$('#warehouse-form').off('beforeSubmit.warehouse').on('beforeSubmit.warehouse', function (e) {
    var form = $(this);
    $.ajax({
        url: form.attr('action'),
        type: 'post',
        data: form.serialize(),
        dataType: 'json',
        success: async function (response) {
            if (response.status == 'success') {
                if (typeof closeModal === 'function') closeModal();
                if (typeof success === 'function') success();
                var container = response.container || '#pjax-warehouse';
                if ($(container).length && $.fn.pjax) {
                    await $.pjax.reload({ container: container, timeout: false });
                } else {
                    window.location.reload();
                }
                return;
            }
            if (response.errors) {
                form.yiiActiveForm('updateMessages', response.errors, true);
            } else {
                form.yiiActiveForm('updateMessages', response, true);
            }
            if (response.message) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: response.message });
                } else {
                    alert(response.message);
                }
            }
        }
    });
    return false;
});
$(".select-photo").on('click', function(e) { e.preventDefault(); $("#my_file").click(); });
$('#my_file').on('change', function (e) {
    e.preventDefault();
    var formdata = new FormData();
    if ($(this).prop('files').length > 0) {
        formdata.append("warehouse", $(this).prop('files')[0]);
        formdata.append("id", 1);
        formdata.append("ref", '$ref');
        formdata.append("name", "warehouse");
        $.ajax({
            url: '$urlUpload',
            type: "POST",
            data: formdata,
            processData: false,
            contentType: false,
            success: function (res) { if (res && res.img) $('.avatar-profile').attr('src', res.img); }
        });
    }
});
JS;
$this->registerJs($js);
?>
