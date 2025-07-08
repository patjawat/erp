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
        'options' => [
            'data-pjax' => 1
        ],
           'fieldConfig' => ['options' => ['class' => 'form-group mb-0 mr-2 me-2']] // spacing form field groups
    ]); ?>
<div class="d-flex justify-content-center gap-1">

    <?= $form->field($model, 'q')->textInput(['placeholder' => 'ระบุสิ่งที่ต้องการค้นหา..'])->label(false) ?>
                <?php
        echo $form->field($model, 'date_filter')->widget(Select2::classname(), [
            'data' =>  DateFilterHelper::getDropdownItems(),
            'options' => ['placeholder' => 'ทั้งหมดทุกปี'],
            'pluginOptions' => [
                'allowClear' => true,
                'width' => '130px',
            ],
        ])->label(false);
        ?>
          <?=$form->field($model, 'thai_year')->widget(Select2::classname(), [
                    'data' => $model->ListThaiYear(),
                    'options' => ['placeholder' => 'ปีงบประมาณทั้งหมด'],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'width' => '120px',
                    ],
        ])->label(false);?>

    <?= $form->field($model, 'date_start')->textInput(['placeholder' => 'เลือกช่วงวันที่'])->label(false); ?>
    <?= $form->field($model, 'date_end')->textInput(['placeholder' => 'เลือกช่วงวันที่'])->label(false); ?>
    <?php
        echo $form->field($model, 'from_warehouse_id')->widget(Select2::classname(), [
                'data' => $model->listFormWarehouse(),
                'options' => ['placeholder' => 'คลังทั้งหมด ...'],
                'pluginOptions' => ['width' => '350px','allowClear' => true],
                'pluginEvents' => [
                                    'select2:select' => 'function(result) { 
                                                //   $(this).submit()
                                                }',
                                    'select2:unselect' => 'function(result) { 
                                                //   $(this).submit()
                                                }',
                                ]
                            ])->label(false);
                            ?>
    <?php echo $form->field($model, 'order_status')->widget(Select2::classname(), [
                            'data' => $model->listStatus(),
                            'options' => ['placeholder' => 'เลือกสถานะ ...'],
                            'pluginOptions' => [
                                'width' => '150px',
                                'allowClear' => true,
                            ],
                            'pluginEvents' => [],
                        ])->label(false);
                        ?>



    <div class="form-group">
        <?= Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i>', ['class' => 'btn btn-primary']) ?>
        <?php if($dataProvider->pagination !== false):?>
            <?= Html::a('<i class="fa-solid fa-up-right-and-down-left-from-center"></i>', ['/inventory/warehouse/order-request', 'all' => 1], ['class' => 'btn btn-light']) ?>
            <?php else:?>
        <?= Html::a('<i class="fa-solid fa-down-left-and-up-right-to-center"></i>', ['/inventory/warehouse/order-request'], ['class' => 'btn btn-light']) ?>
        <?php endif?>
    </div>
</div>


<!-- Offcanvas -->
<div class="offcanvas offcanvas-end" data-bs-backdrop="static" tabindex="-1" id="offcanvasExample"
    aria-labelledby="offcanvasExampleLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasExampleLabel">กรองเพิ่มเติม</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body position-relative">

        <div class="d-flex flex-row gap-4">
            <?php // echo $form->field($model, 'order_status')->checkboxList($model->listStatus(), ['custom' => true, 'inline' => false, 'id' => 'custom-checkbox-list-inline'])->label('สถานะ'); ?>
        </div>

        <div class="offcanvas-footer">
            <?php echo Html::submitButton(
                    '<i class="fa-solid fa-magnifying-glass"></i> ค้นหา',
                    [
                        'class' => 'btn btn-primary',
                        'data-bs-backdrop' => 'static',
                        'tabindex' => '-1',
                        'id' => 'offcanvasExample',
                        'aria-labelledby' => 'offcanvasExampleLabel',
                    ]
                ); ?>
        </div>

    </div>
</div>

<?php ActiveForm::end(); ?>

<?php
$js = <<< JS

thaiDatepicker('#stockeventsearch-date_start,#stockeventsearch-date_end')

    $('#stockeventsearch-date_start').change(function (e) { 
        e.preventDefault();
        $('#stockeventsearch-thai_year').val(null).trigger('change');
        // $(this).submit();
    });
    
    $('#stockeventsearch-date_end').change(function (e) { 
        e.preventDefault();
        $('#stockeventsearch-thai_year').val(null).trigger('change');
        // $(this).submit();
    });



 
JS;
$this->registerJS($js);
?>
