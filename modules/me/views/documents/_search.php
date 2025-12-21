<?php

use yii\helpers\Html;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use kartik\widgets\ActiveForm;
use app\components\DateFilterHelper;

/** @var yii\web\View $this */
/** @var app\modules\dms\models\DocumentSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>


<?php $form = ActiveForm::begin([
    'action' => [$action],
    'id' => 'document-search',
    'method' => 'get',
    'options' => [
        'data-pjax' => 1
    ],
     'fieldConfig' => ['options' => ['class' => 'form-group mb-1 mr-2 me-2']] // spacing form field groups
]); ?>
<div class="row g-2 align-items-start">
    <div class="col-6 col-md-2">
        <?= $this->render('@app/components/ui/_date_filter', ['form' => $form, 'model' => $model, 'label' => false]) ?>
    </div>
    <div class="col-6 col-md-2">
        <?= $this->render('@app/components/ui/_date_start', ['form' => $form, 'model' => $model, 'label' => false]) ?>
    </div>
    <div class="col-6 col-md-2">
        <?= $this->render('@app/components/ui/_date_end', ['form' => $form, 'model' => $model, 'label' => false]) ?>
    </div>
    <div class="col-6 col-md-3">
      <?php
                                $status = ArrayHelper::merge( $model->listStatus(), ['Y' => 'บันทึกไว้']);
                                echo $form->field($model, 'q_status')->widget(Select2::classname(), [
                                    'data' =>$status,
                                    'options' => ['placeholder' => 'สถานะทั้งหมด'],
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                    ],
                                ])->label(false);?>
    </div>
    <div class="col-12 col-md-3">
        <?= $form->field($model, 'document_org')->widget(Select2::classname(), [
            'data' => $model->ListDocumentOrg(),
            'options' => ['placeholder' => 'หน่วยงานทั้งหทด'],
            'pluginOptions' => ['allowClear' => true, 'tags' => true],
        ])->label(false); ?>
    </div>

    <div class="col-12">
        <div class="input-group mb-3">
            <span class="input-group-text bg-light text-muted border-end-0">
                <i class="bi bi-search"></i>
            </span>
            <?= $form->field($model, 'q', [
                'options' => ['tag' => false], // ลบ div wrapper ของฟิลด์ออกเพื่อให้เข้าชุดกับ input-group
            ])->textInput([
                'placeholder' => 'พิมพ์คำค้นหาที่นี่...',
                'class' => 'form-control border-start-0'
            ])->label(false) ?>
            
            <button class="btn btn-primary px-4" type="submit">
                ค้นหา
            </button>
            <button class="btn btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilter" aria-expanded="false border-start-0">
                <i class="fa-solid fa-filter"></i> ตัวกรอง
            </button>
        </div>
    </div>
</div>

<div class="collapse" id="collapseFilter">
  <div class="card card-body mb-3 shadow-sm border-primary">
    <p class="small text-muted mb-0">ตัวเลือกการกรองเพิ่มเติม...</p>
  </div>
</div>

<?php ActiveForm::end(); ?>


<?php

$js = <<< JS

    thaiDatepicker('#documentsearch-date_start,#documentsearch-date_end')

    $('#documentsearch-date_start, #documentsearch-date_end').on('change', function () {
        $('#documentsearch-thai_year, #documentsearch-date_filter').val(null).trigger('change');
    });

    $( "#documentsdetailsearch-show_reading" ).prop( "checked", localStorage.getItem('show_reading') == 1 ? true : false );
    $("body").on("change", "#documentsdetailsearch-show_reading", function (e) {
        
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

