<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;
use app\modules\usermanager\components\RouteRule;
use yii\helpers\Json;
use app\modules\usermanager\components\Configs;

$context = $this->context;
$labels = $context->labels();
$rules = Configs::authManager()->getRules();
unset($rules[RouteRule::RULE_NAME]);
$source = Json::htmlEncode(array_keys($rules));

$js = <<<JS
    $('#rule_name').autocomplete({
        source: $source,
    });
JS;
// AutocompleteAsset::register($this);
$this->registerJs($js);
?>

<div class="auth-item-form">
    <?php $form = ActiveForm::begin(['id' => 'item-form']); ?>
    <div class="row">
        <div class="col-md-10 offset-md-1">

        <div class="card">
              <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list-ul"></i> สร้างบทบาท</h3>

                <div class="card-tools">
                 <div style="width: 400px;">
                  </div> 
                </div>
              </div>
              <!-- /.card-header -->
              <div class="card-body table-responsive">
            <?= $form->field($model, 'name')->textInput(['maxlength' => 64]) ?>

            <?= $form->field($model, 'description')->textarea(['rows' => 2]) ?>
  
            <?= $form->field($model, 'ruleName')->textInput(['id' => 'rule_name']) ?>

            <?= $form->field($model, 'data')->textarea(['rows' => 6]) ?>
        </div>
    </div>
    <div class="form-group">
        <?php
        echo Html::submitButton($model->isNewRecord ? 'บันทึก' : 'แก้ไข', [
            'class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary',
            'name' => 'submit-button'])
        ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
</div>
</div>
