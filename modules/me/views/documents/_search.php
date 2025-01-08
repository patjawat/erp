<?php

use yii\helpers\Html;
use kartik\select2\Select2;
use kartik\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\dms\models\DocumentSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>


<?php $form = ActiveForm::begin([
    'action' => ['index'],
    'id' => 'document-search',
    'method' => 'get',
    'options' => [
        'data-pjax' => 1
    ],
]); ?>

<div class="d-flex justify-content-between align-top align-items-center gap-1">
    <?= $form->field($model, 'q')->label('คำค้นหา...') ?>
    <?php
    echo $form->field($model, 'thai_year')->widget(Select2::classname(), [
        'data' => $model->ListThaiYear(),
        'options' => ['placeholder' => 'เลือก พ.ศ.'],
        'pluginOptions' => [
            'allowClear' => true,
            'width' => '130px',
        ],
        'pluginEvents' => [
            'select2:select' => 'function(result) { 
                        $(this).submit()
                        }',
            'select2:unselecting' => 'function() {
                            $(this).submit()
                        }',
        ]
    ])->label('ปี พ.ศ.');
    ?>

    <div class="dropdown mt-2">
        <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i
                class="fa-solid fa-filter"></i> เพิ่มเติม
        </button>
        <div class="dropdown-menu p-4 relative" style="width:500px">

            <div class="d-flex flex-row gap-4">

                <?= $form->field($model, 'show_reading')->checkbox(['custom' => true, 'switch' => true, 'checked' => true])->label('แสดงที่อ่านแล้ว'); ?>
            </div>

        </div>
    </div>

    <?= $form->field($model, 'document_group')->hiddenInput()->label(false) ?>
    <?= Html::submitButton('<i class="bi bi-search"></i>', ['class' => 'btn btn-primary mt-2']) ?>
</div>

<?php ActiveForm::end(); ?>


<?php

$js = <<< JS

    \$( "#documentsearch-show_reading" ).prop( "checked", localStorage.getItem('show_reading') == 1 ? true : false );
    \$("body").on("change", "#documentsearch-show_reading", function (e) {
                            if (\$(this).is(':checked')) {
                                // alert('Checkbox is checked!');
                                localStorage.setItem('show_reading',1);
                                
                            } else {
                                // alert('Checkbox is unchecked!');
                                localStorage.setItem('show_reading',0);
                            }
                            \$(this).submit();
                        });

                  
    JS;
$this->registerJS($js)
?>