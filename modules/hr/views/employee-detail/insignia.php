<?php
use iamsaint\datetimepicker\Datetimepicker;
use kartik\widgets\Select2;
use yii\web\View;

?>

<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12">
        <?=$form->field($model, 'data_json[date_start]')->widget(yii\widgets\MaskedInput::className(), [
                'mask' => '99/99/9999',
            ])->label('วันที่เสนอขอ');
        ?>
        <?=$form->field($model, 'data_json[date_end]')->widget(yii\widgets\MaskedInput::className(), [
                'mask' => '99/99/9999',
            ])->label('วันที่รับรางวัล');
        ?>
    </div>
    <div class="col-lg-12 col-md-12 col-sm-12">
        <?= $form->field($model, 'data_json[name]')->textInput(['autofocus' => true])->label('ชื่อรางวัล') ?>
    </div>
    <div class="col-lg-12 col-md-12 col-sm-12">
        <?= $form->field($model, 'data_json[company_name]')->textInput(['autofocus' => true])->label('หน่วยงานที่มอบรางวัล') ?>
    </div>
</div>