<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use kartik\widgets\Select2;
use kartik\widgets\ActiveForm;
use app\widgets\FlatpickrWidget;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;
?>
<div class="d-flex gap-2">
    <?php echo $form->field($model, 'q')->textInput(['placeholder' => 'ระบุคำค้นหา...','class' => 'form-control'])->label('คำค้นหา') ?>

<?=$this->render('@app/components/ui/input_emp',['model' => $model,'form' => $form,'fieldName' => 'emp_id','label' => $label ?? 'บุคลากร'])?>

    <?php
        echo $form->field($model, 'thai_year')->widget(Select2::classname(), [
            'data' => $model->ListThaiYear(),
            'options' => ['placeholder' => 'ปีงบประมาณ'],
            'pluginOptions' => [
                'allowClear' => true,
                'width' => '150px',
            ],
            'pluginEvents' => [
                'select2:select' => 'function(result) { 
                        $(this).submit()
                        }',
                'select2:unselecting' => "function() {
                             $(this).submit()
                            $('#leavesearch-date_start').val('');
                            $('#leavesearch-date_end').val('');
                            $('#vehiclesearch-date_start').val('');
                            $('#vehiclesearch-date_end').val('');
                            $('#developmentsearch-date_start').val('');
                            $('#developmentsearch-date_end').val('');
                            
                        }",
            ]
        ])->label('ปีงบประมาณ');
        ?>

    <div class="d-flex justify-content-between gap-2" style="width: 285px;">
        <?php echo $form->field($model, 'date_start')->textInput(['class' => 'form-control','placeholder' => '__/__/____'])->label('ตั้งแต่วันที่');?>
        <?php echo $form->field($model, 'date_end')->textInput(['class' => 'form-control','placeholder' => '__/__/____'])->label('ถึงวันที่');?>
    </div>

    <div class="d-flex flex-row align-items-center gap-2 mt-2">
        <?php echo Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i> ค้นหา', ['class' => 'btn btm-sm btn-light']) ?>
        <button class="btn btn-light" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" aria-controls="offcanvasRight" data-bs-title="เลือกเงื่อนไขของการค้นหาเพิ่มเติม..."><i class="fa-solid fa-filter"></i></button>
    </div>
</div>