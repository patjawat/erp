
<?php
use yii\bootstrap5\Html;
use kartik\widgets\Select2;
$placeholder = isset($placeholder) ? $placeholder : 'ท';
?>
<div class="row mb-2">
    <div class="col-2">
        <?=$this->render('@app/components/ui/_date_filter',['form' => $form,'model' => $model,'label' => false])?>
    </div>
    
    <div class="col-2">
        <?= $this->render('@app/components/ui/_date_start',['form' => $form,'model' => $model,'label' => false])?>
    </div>
    <div class="col-2">
        <?= $this->render('@app/components/ui/_date_end',['form' => $form,'model' => $model,'label' => false])?>
    </div>
    <div class="col-2">
        <?=$form->field($model, 'status')->widget(Select2::classname(), [
        'data' => $status,
        'options' => ['placeholder' => 'สถานะทั้งหมด'],
        'pluginOptions' => [
            'allowClear' => true,
            // 'width' => '150px',
        ],
        ])->label(false);?>
    </div>
    <div class="col-3">
        <?=$this->render('@app/components/ui/input_emp',['form' => $form,'model' => $model,'label' => false,'placeholder' => $placeholder])?>
    </div>
    <div class="col-1">
        <div class="d-flex flex-row align-items-center gap-2">
            <?php echo Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i>', ['class' => 'btn btm-sm btn-primary']) ?>
            <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilter"
                aria-expanded="false" aria-controls="collapseFilter">
                <i class="fa-solid fa-filter"></i>
            </button>
        </div>
    </div>

</div>