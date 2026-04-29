<?php

use yii\helpers\Html;
use yii\helpers\Url;
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
<div class="col-12 col-lg">
        <div class="input-group">
            <span class="input-group-text bg-body text-muted border-end-0">
                <i class="bi bi-search" aria-hidden="true"></i>
            </span>
            <?= $form->field($model, 'q', [
                'options' => ['tag' => false],
            ])->textInput([
                'placeholder' => 'พิมพ์คำค้นหาที่นี่...',
                'class' => 'form-control border-start-0',
            ])->label(false) ?>
        </div>
    </div>
    
    <div class="col-6 col-md-2">
        <?= $this->render('@app/components/ui/_date_filter', ['form' => $form, 'model' => $model, 'label' => false]) ?>
    </div>
    <div class="col-6 col-md-2">
        <?= $this->render('@app/components/ui/_date_start', ['form' => $form, 'model' => $model, 'label' => false]) ?>
    </div>
    <div class="col-6 col-md-2">
        <?= $this->render('@app/components/ui/_date_end', ['form' => $form, 'model' => $model, 'label' => false]) ?>
    </div>



    <div class="col-12 col-lg-auto d-flex flex-wrap align-items-stretch align-items-lg-center gap-2">
        <?= Html::submitButton('<i class="fa-solid fa-magnifying-glass me-1" aria-hidden="true"></i> ค้นหา', [
            'class' => 'btn btn-primary flex-grow-1 flex-lg-grow-0',
        ]) ?>
        <button class="btn btn-outline-primary flex-grow-1 flex-lg-grow-0" type="button" data-bs-toggle="collapse"
            data-bs-target="#collapseFilter" aria-expanded="false" aria-controls="collapseFilter">
            <i class="fa-solid fa-sliders me-1" aria-hidden="true"></i> ตัวกรองเพิ่มเติม
        </button>
        <div class="dropdown flex-grow-1 flex-lg-grow-0">
            <button class="btn btn-success dropdown-toggle w-100" type="button" id="documentsExcelMenu"
                data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa-solid fa-file-excel me-1" aria-hidden="true"></i> Excel
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="documentsExcelMenu">
                <li>
                    <?= Html::a('<i class="fa-solid fa-table me-2" aria-hidden="true"></i> ดาวน์โหลด Template', Url::to(['/me/documents/download-template']), [
                        'class' => 'dropdown-item',
                        'data-pjax' => 0,
                    ]) ?>
                </li>
                <li>
                    <?= Html::a('<i class="fa-solid fa-file-excel me-2" aria-hidden="true"></i> ส่งออกข้อมูล', Url::to(['/me/documents/export-excel']), [
                        'class' => 'dropdown-item',
                        'data-pjax' => 0,
                    ]) ?>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <?= Html::a('<i class="fa-solid fa-file-import me-2" aria-hidden="true"></i> นำเข้าข้อมูล', Url::to(['/me/documents/import-excel']), [
                        'class' => 'dropdown-item',
                        'data-pjax' => 0,
                    ]) ?>
                </li>
            </ul>
        </div>
    </div>
</div>

<div class="collapse mt-3 pt-3 border-top" id="collapseFilter">
    <div class="card border-0 shadow-sm bg-body-tertiary">
        <div class="card-body py-3">
            <p class="small text-muted mb-0"><i class="fa-solid fa-circle-info me-1" aria-hidden="true"></i> ตัวเลือกการกรองเพิ่มเติมจะเพิ่มในขั้นตอนถัดไป</p>
            <div class="row">
                <div class="col-6 col-md-3">
      <?php
                                $status = ArrayHelper::merge($model->listStatus(), ['Y' => 'บันทึกไว้ (ปักดาวแล้ว · bookmark=Y)','read' => 'อ่านแล้ว','unread' => 'ยังไม่ได้อ่าน']);
                                echo $form->field($model, 'q_status')->widget(Select2::classname(), [
                                    'data' =>$status,
                                    'options' => ['placeholder' => 'สถานะทั้งหมด'],
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                    ],
                                ])->label(false);?>
    </div>
                <div class="col-6 col-md-3">
        <?php 
        //  $form->field($model, 'document_org')->widget(Select2::classname(), [
        //     'data' => $model->ListDocumentOrg(),
        //     'options' => ['placeholder' => 'หน่วยงานทั้งหมด'],
        //     'pluginOptions' => ['allowClear' => true, 'tags' => true],
        // ])->label(false); 
        ?>
    </div>
    
            </div>
        </div>
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

