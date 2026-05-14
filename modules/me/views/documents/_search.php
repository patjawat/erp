<?php

use yii\helpers\Html;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use kartik\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\dms\models\DocumentSearch $model */
/** @var yii\widgets\ActiveForm $form */
$hasAdvancedFilters = !empty($model->q_status) || !empty($model->show_reading) || !empty($model->doc_speed);

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
    <p class="mb-0"> ค้นหาสิ่งที่ต้องการ</p>
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
        <p class="mb-0"> ช่วงวันที่เอกสาร</p>
        <?= $this->render('@app/components/ui/_date_filter', ['form' => $form, 'model' => $model, 'label' => false]) ?>
    </div>
    <div class="col-6 col-md-2">
        <p class="mb-0"> ช่วงวันที่เริ่มต้น</p>
        <?= $this->render('@app/components/ui/_date_start', ['form' => $form, 'model' => $model, 'label' => false]) ?>
    </div>
    <div class="col-6 col-md-2">
        <p class="mb-0"> ช่วงวันที่สิ้นสุด</p>
        <?= $this->render('@app/components/ui/_date_end', ['form' => $form, 'model' => $model, 'label' => false]) ?>
    </div>



    <div class="col-12 col-lg-auto d-flex flex-wrap align-items-stretch align-items-lg-center gap-2 mt-4">
        <?= Html::submitButton('<i class="fa-solid fa-magnifying-glass me-1" aria-hidden="true"></i> ค้นหา', [
            'class' => 'btn btn-primary flex-grow-1 flex-lg-grow-0',
        ]) ?>
       <button class="btn btn-outline-primary flex-grow-1 flex-sm-grow-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilter"
                aria-expanded="<?= $hasAdvancedFilters ? 'true' : 'false' ?>" aria-controls="collapseFilter" id="btnToggleFilter">
                <i class="fa-solid fa-sliders me-1"></i> ตัวกรองเพิ่มเติม
            </button>
        
    </div>
</div>

<!-- ตัวกรองเพิ่มเติม: แสดงเมื่อกด "ตัวกรองเพิ่มเติม" (หรือเปิดอัตโนมัติถ้ามีค่ากรองอยู่) -->
<div class="collapse mt-3 pt-3 border-top <?= $hasAdvancedFilters ? 'show' : '' ?>" id="collapseFilter">
    <div class="card border-0 shadow-sm bg-body-tertiary">
        <div class="card-body py-3">
            <p class="small text-muted mb-0"><i class="fa-solid fa-circle-info me-1" aria-hidden="true"></i> เลือกตัวกรองสถานะ ความเร่งด่วน และการแสดงผู้อ่านได้จากส่วนนี้</p>
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
                    <?php echo $form->field($model, 'show_reading', [
                        'options' => ['class' => 'form-check form-switch'],
                    ])->checkbox([
                        'class' => 'form-check-input',
                        'id' => 'documentsearch-show_reading',
                        'uncheck' => 0,
                        'value' => 1,
                    ], false)->label('แสดงผู้อ่านทั้งหมด', ['class' => 'form-check-label']); ?>
                    <div class="small text-muted mt-1">เปิดเพื่อให้ดูรายชื่อผู้อ่านทั้งหมดในหน้ารายละเอียด</div>
                </div>
                <div class="col-6 col-md-3">
                    <p class="mb-0"> ระดับความเร่งด่วน</p>
                    <?php
                    echo $form->field($model, 'doc_speed')->widget(Select2::classname(), [
                        'data' => $model->DocSpeed(),
                        'options' => ['placeholder' => 'ระดับความเร่งด่วนทั้งหมด'],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ])->label(false);
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

    (function () {
        var \$showReading = \$('#documentsearch-show_reading');
        if (\$showReading.length) {
            var hasParam = new URLSearchParams(window.location.search).has('DocumentSearch[show_reading]');
            var stored = localStorage.getItem('show_reading');
            if (stored !== null && !hasParam) {
                \$showReading.prop('checked', stored == 1);
            }
            localStorage.setItem('show_reading', \$showReading.is(':checked') ? 1 : 0);
            if (window.syncDocumentDetailLinks) {
                window.syncDocumentDetailLinks();
            }

            if (\$showReading.is(':checked')) {
                var collapseEl = document.getElementById('collapseFilter');
                if (collapseEl && !collapseEl.classList.contains('show') && window.bootstrap && bootstrap.Collapse) {
                    bootstrap.Collapse.getOrCreateInstance(collapseEl, { toggle: false }).show();
                    \$('#btnToggleFilter').attr('aria-expanded', 'true');
                }
            }
        }
    })();

    $("body").on("change", "#documentsearch-show_reading", function (e) {
        localStorage.setItem('show_reading', \$(this).is(':checked') ? 1 : 0);
        if (window.syncDocumentDetailLinks) {
            window.syncDocumentDetailLinks();
        }
        \$('#document-search').trigger('submit');
    });

                  
    JS;
$this->registerJS($js)
?>
