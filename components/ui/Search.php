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
<style>
.select2-container .select2-selection--single .select2-selection__rendered {
    line-height: 25px !important;
}
</style>
<div class="row">
    <div class="col-4">
        <?php echo $form->field($model, 'q')->textInput(['placeholder' => 'ระบุคำค้นหา...','class' => 'form-control'])->label('คำค้นหา') ?>
    </div>

    <!-- <div class="col-3"> -->
    <?php // $this->render('@app/components/ui/input_emp',['model' => $model,'form' => $form,'fieldName' => 'emp_id','label' => $label ?? 'บุคลากร'])?>
    <!-- </div> -->
    <div class="col-2">
        <?php
        echo $form->field($model, 'thai_year')->widget(Select2::classname(), [
            'data' => $model->ListThaiYear(),
            'options' => ['placeholder' => 'ปีงบประมาณ'],
            'pluginOptions' => [
                
                'allowClear' => true,
                // 'width' => '300px',
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
                        $('#meetingsearch-date_start').val('');
                        $('#meetingsearch-date_end').val('');
                        $('#developmentsearch-date_start').val('');
                        $('#developmentsearch-date_end').val('');
                        $('#approvesearch-date_start').val('');
                        $('#approvesearch-date_end').val('');
                        $('#helpdesksearch-date_start').val('');
                        $('#helpdesksearch-date_end').val('');
                        
                        }",
                        ]
                        ])->label('ปีงบประมาณ');
                        ?>

    </div>
    <div class="col-2">
        <?php echo $form->field($model, 'date_start')->textInput(['class' => 'form-control','placeholder' => '__/__/____'])->label('ตั้งแต่วันที่');?>
    </div>
        <div class="col-2">
        <?php echo $form->field($model, 'date_end')->textInput(['class' => 'form-control','placeholder' => '__/__/____'])->label('ถึงวันที่');?>
    </div>
    <div class="col-2">
        <div class="d-flex flex-row align-items-center gap-2 mt-1">
            <?php echo Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i> ค้นหา', ['class' => 'btn btm-sm btn-primary mt-4']) ?>
            <button class="btn btn-primary mt-4" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight"
                aria-controls="offcanvasRight" data-bs-title="เลือกเงื่อนไขของการค้นหาเพิ่มเติม..."><i
                    class="fa-solid fa-filter"></i></button>
        </div>
    </div>
</div>