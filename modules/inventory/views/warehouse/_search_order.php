<?php

use yii\helpers\Html;
use kartik\select2\Select2;
use kartik\widgets\ActiveForm;
use app\components\DateFilterHelper;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\WarehouseSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<?php $form = ActiveForm::begin([
    'action' => ['/inventory/warehouse/order-request'],
    'method' => 'get',
    'id' => 'formRequestSearch',
    'options' => [
        'data-pjax' => 1
    ],
    'fieldConfig' => ['options' => ['class' => 'form-group mb-0 mr-2 me-2']] // spacing form field groups
]); ?>
<div class="row">
    <div class="col-lg-6 col-md-6 col-sm-12">
        <fieldset class="border p-3 rounded">
            <legend class="float-none w-auto px-2 fs-6">ค้นหาจากคำขอ</legend>
            <div class="row">
                
                <div class="col-6">
                    <?= $form->field($model, 'req_date_start')->textInput(['placeholder' => 'เลือกช่วงวันที่'])->label(false); ?>
                </div>
                <div class="col-6">
                    <?= $form->field($model, 'req_date_end')->textInput(['placeholder' => 'เลือกช่วงวันที่'])->label(false); ?>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-6">
                    <?= $form->field($model, 'code')->textInput(['placeholder' => 'รหัสคำขอ..'])->label(false) ?>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <?php
                    echo $form->field($model, 'from_warehouse_id')->widget(Select2::classname(), [
                        'data' => $model->listFormWarehouse(),
                        'options' => ['placeholder' => 'คลังทั้งหมด ...'],
                        'pluginOptions' => ['allowClear' => true],
                        
                    ])->label(false);
                    ?>
                </div>
            </div>
        </fieldset>
    </div>
    <div class="col-lg-6 col-md-6 col-sm-12">
        <fieldset class="border p-3 rounded">
            <legend class="float-none w-auto px-2 fs-6">ค้นหาจากการจ่าย</legend>
            <div class="row">
                <div class="col-6">
                    <?= $form->field($model, 'date_start')->textInput(['placeholder' => 'เลือกช่วงวันที่'])->label(false); ?>

                </div>
                <div class="col-6">
                    <?= $form->field($model, 'date_end')->textInput(['placeholder' => 'เลือกช่วงวันที่'])->label(false); ?>

                </div>
            </div>
            <div class="row mt-2">
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <?php echo $form->field($model, 'order_status')->widget(Select2::classname(), [
                        'data' => $model->listStatus(),
                        'options' => ['placeholder' => 'เลือกสถานะ ...'],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],

                    ])->label(false);
                    ?>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-12">
                        <?= $form->field($model, 'thai_year')->widget(Select2::classname(), [
        'data' => $model->ListThaiYear(),
        'options' => ['placeholder' => 'ปีงบประมาณทั้งหมด'],
        'pluginOptions' => [
            'allowClear' => true,
        ],
    ])->label(false); ?>


                    </div>
                <div class="col-lg-2 col-md-2 col-sm-12">
                <div class="d-flex flex-row align-items-center gap-2">
                    <?php echo Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i>', ['class' => 'btn btm-sm btn-primary']) ?>
                    <button class="btn btn-warning reset" type="button"><i class="fa-solid fa-rotate-right"></i></button>
                    <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilter"
                        aria-expanded="false" aria-controls="collapseFilter">
                        <i class="fa-solid fa-filter"></i>
                    </button>
                </div>
            </div>

        </fieldset>
    </div>
</div>




<div class="d-flex justify-content-center gap-1">





    <!-- 
    <div class="form-group">
        <?= Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i>', ['class' => 'btn btn-primary']) ?>
        <?php if ($dataProvider->pagination !== false): ?>
            <?= Html::a('<i class="fa-solid fa-up-right-and-down-left-from-center"></i>', ['/inventory/warehouse/order-request', 'all' => 1], ['class' => 'btn btn-light']) ?>
            <?php else: ?>
        <?= Html::a('<i class="fa-solid fa-down-left-and-up-right-to-center"></i>', ['/inventory/warehouse/order-request'], ['class' => 'btn btn-light']) ?>
        <?php endif ?>
    </div> -->


</div>

<div class="collapse mt-3" id="collapseFilter">
    <div class="row">
        <div class="col-lg-5 col-md-5 col-sm-12">

        </div>
    </div>
</div>

<?php ActiveForm::end(); ?>

<?php
$js = <<< JS

thaiDatepicker('#stockeventsearch-date_start,#stockeventsearch-date_end,#stockeventsearch-req_date_start,#stockeventsearch-req_date_end')




    $('#stockeventsearch-req_date_start').change(function (e) { 
        e.preventDefault();
        $('#stockeventsearch-date_filter').val(null).trigger('change');
    });

        $('#stockeventsearch-req_date_end').change(function (e) { 
        e.preventDefault();
        $('#stockeventsearch-date_filter').val(null).trigger('change');
    });




    $('#stockeventsearch-date_start').change(function (e) { 
        e.preventDefault();
        $('#stockeventsearch-thai_year').val(null).trigger('change');
        $('#stockeventsearch-date_filter').val(null).trigger('change');
    });
    
    $('#stockeventsearch-date_end').change(function (e) { 
        e.preventDefault();
        $('#stockeventsearch-thai_year').val(null).trigger('change');
          $('#stockeventsearch-date_filter').val(null).trigger('change');
    });

$('.reset').click(function (e) {
    e.preventDefault();

    Swal.fire({
        title: 'ยืนยันการรีเซ็ต?',
        text: "คุณต้องการล้างค่าการค้นหาทั้งหมดหรือไม่",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'ใช่, รีเซ็ตเลย',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            $('#formRequestSearch').trigger('reset');
            $('#formRequestSearch').find('select').val(null).trigger('change');
            $('#formRequestSearch').find('input[type=text]').val('');
             $('#formRequestSearch').submit();
            
            // Swal.fire('ล้างข้อมูลแล้ว!', '', 'success');
        }
    });
});

 
JS;
$this->registerJS($js);
?>