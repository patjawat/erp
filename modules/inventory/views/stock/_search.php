<?php

use kartik\select2\Select2;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="stock-search">

    <?php $form = ActiveForm::begin([
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>
    <div class="d-flex gap-3">
        <?= $form->field($model, 'q')->label(false) ?>
        <?php
  
  echo $form->field($model, 'asset_type')->widget(Select2::classname(), [
      'data' => $model->ListProductType(),
      'options' => ['placeholder' => 'เลือกประเภทวัสดุ',
  ],
      'pluginOptions' => [
          'allowClear' => true,
          'width' => '200px',
        ],
        'pluginEvents' => [
            'select2:select' => "function(result) { 
                $(this).submit()
                }",
                'select2:unselect' => "function(result) { 
                    $(this).submit()
                    }",
            ],
            ])->label(false);
            ?>
        <?= Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i> ค้นหา', ['class' => 'btn btn-light']) ?>
        <!-- <div class="btn-group">
            <?php Html::a('<i class="bi bi-clock"></i> ดำเนินการ', ['/purchase/order/view', 'id' => $model->id], ['class' => 'btn btn-light w-100']) ?>
            <button type="button" class="btn btn-light dropdown-toggle dropdown-toggle-split"
                data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent"> <i class="bi bi-caret-down-fill"></i>
            </button>
            <ul class="dropdown-menu">
                <li>
                    <?php //  Html::a('พิมพ์สต๊อกการ์ด', ['/ms-word/stockcard', 'id' => $model->id], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-xl']]) 
                    ?>
                    <?php // Html::a('<i class="fa-solid fa-print me-1"></i> พิมพ์', ['/sm/order/document', 'id' => $model->id, 'title' => '<i class="fa-solid fa-print"></i> พิมพ์เอกสารประกอบการจัดซื้อ'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']]) 
                    ?>

                </li>
            </ul>
        </div> -->


        <div class="form-group">

        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>