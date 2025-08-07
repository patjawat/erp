<?php
use kartik\select2\Select2;
use yii\helpers\Url;

?>

<tr>
    <td>
        <input type="text" name="materials[<?= $index ?>][code]" class="form-control" readonly>
    </td>
    <td>
        <?= Select2::widget([
            'name' => "materials[$index][material_id]",
            'options' => [
                'placeholder' => 'ค้นหาวัสดุ...',
                'class' => 'form-control select2-material',
                'data-index' => $index,
            ],
            'pluginOptions' => [
                'allowClear' => true,
                'minimumInputLength' => 2,
                'ajax' => [
                    'url' => Url::to(['/me/stock-v2/material-list']),
                    'dataType' => 'json',
                    'delay' => 250,
                    'data' => new \yii\web\JsExpression('function(params) { return {q:params.term}; }'),
                    'processResults' => new \yii\web\JsExpression('function(data) {
                        return {results: data.results};
                    }'),
                    'cache' => true
                ],
            ],
        ]) ?>
    </td>
    <td>
        <input type="number" name="materials[<?= $index ?>][quantity]" class="form-control" min="1">
    </td>
    <td>
        <input type="text" name="materials[<?= $index ?>][unit]" class="form-control" readonly>
    </td>
    <td>
        <input type="text" name="materials[<?= $index ?>][note]" class="form-control">
    </td>
    <td class="text-center">
        <button type="button" class="btn btn-danger btn-sm" onclick="removeMaterialRow(this)">
            <i class="fas fa-trash"></i>
        </button>
    </td>
</tr>
