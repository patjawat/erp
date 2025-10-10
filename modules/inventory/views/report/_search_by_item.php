<?php

use yii\helpers\Html;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\ProductTypeSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<?php $form = ActiveForm::begin([
    'action' => ['/inventory/report/list-by-item'],
    'method' => 'get',
    'options' => [
        'data-pjax' => 1
    ],
]); ?>


<div class="row">

    <div class="col-2">
        <?= $this->render('@app/components/ui/_date_filter', [
            'form' => $form,
            'model' => $model,
        ])
        ?>

    </div>
    <div class="col-2">
        <?= $this->render('@app/components/ui/_date_start', ['form' => $form, 'model' => $model]) ?>
    </div>
    <div class="col-2">
        <?= $this->render('@app/components/ui/_date_end', ['form' => $form, 'model' => $model]) ?>
    </div>


    <div class="col-3">

        <?= $form->field($model, 'warehouse_id')->widget(Select2::classname(), [
            'data' => $model->listWareHouseMain(),
            'options' => ['placeholder' => 'เลือกคลังที่ต้องการเบิก'],
            'pluginEvents' => [
                "select2:unselect" => "function() { 

                                            }",
                "select2:select" => "function() {
                                               console.log($(this).val());
                                        }",
            ],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ])->label(false);

        ?>
    </div>
    <div class="col-3">
        <?php

        // Select2 - ประเภทครุภัณฑ์
        echo $form->field($model, 'asset_type_id')->widget(Select2::classname(), [
            'data' => $model->listAssetType(),
            'options' => [
                'placeholder' => 'เลือกประเภท...',
                'id' => 'asset_type_id'
            ],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ])->label(false);
        ?>
    </div>




</div>

<div class="row mt-2">
    <div class="col-9">
        <?php echo $form->field($model, 'q')->textInput(['class' => 'form-control', 'placeholder' => 'ค้นหา...'])->label(false); ?>
    </div>
    <div class="col-3">
        <div class="d-flex flex-row align-items-center gap-2">
            <?php echo Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i>', ['class' => 'btn btm-sm btn-primary']) ?>
            <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilter"
                aria-expanded="false" aria-controls="collapseFilter">
                <i class="fa-solid fa-filter"></i>
            </button>
            <?= \yii\helpers\Html::a(
                '<i class="fa fa-file-excel"></i> Excel',
                array_merge(['export-excel-by-item'], Yii::$app->request->queryParams),
                 ['class' => 'btn btn-success', 'id' => 'btn-export-excel']
            ) ?>
        </div>
    </div>
</div>


<div class="collapse mt-3" id="collapseFilter">


</div>
<?php ActiveForm::end(); ?>

<?php
$js = <<< JS
 
 $('#btn-export-excel').on('click', function(e){
    e.preventDefault(); // ป้องกัน default action ของ <a>
    var url = $(this).attr('href');

    Swal.fire({
        title: 'ยืนยันการส่งออก Excel?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'ใช่, ส่งออก',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if(result.isConfirmed){
            // แสดง loading
            Swal.fire({
                title: 'กำลังสร้างไฟล์ Excel...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();

                    // สร้าง iframe ซ่อนเพื่อดาวน์โหลดไฟล์
                    var iframe = document.createElement('iframe');
                    iframe.style.display = 'none';
                    iframe.src = url;
                    document.body.appendChild(iframe);

                    // ตรวจสอบว่า download เสร็จ (ไม่สามารถรู้ exact แต่ใช้ timeout ประมาณ)
                    setTimeout(function(){
                        Swal.close();
                        location.reload(); // reload หน้า
                    }, 3000); // ปรับเวลาให้เหมาะสมกับไฟล์ใหญ่/เล็ก
                }
            });
        }
    });
});

JS;
$this->registerJS($js);
?>