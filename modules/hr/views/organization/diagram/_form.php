<?php

use kartik\select2\Select2;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use app\modules\hr\models\Employees;
?>
<?php
// lvl 1 = บนสุด (ประเภท) = ระดับที่ 2 ในชุดตั้งชื่อระดับ, lvl 2 = กลุ่มงาน = ระดับที่ 1 ในชุดตั้งชื่อระดับ
switch ($node->lvl) {
    case 1:
        $label = ['name' => 'ประเภท (ระดับที่ 2 ในตั้งชื่อระดับ)'];
        break;
    case 2:
        $label = ['name' => 'กลุ่มงาน (ระดับที่ 1 ในตั้งชื่อระดับ)'];
        break;
    default:
        $label = ['name' => 'ชื่อ'];
        break;
}
?>
<?= $form->field($node, 'tb_name')->hiddenInput(['value' => 'diagram', 'maxlength' => true])->label(false) ?>
<?= $form->field($node, 'data_json[leader1_fullname]')->hiddenInput(['maxlength' => true])->label(false) ?>
<?= $form->field($node, 'data_json[leader2_fullname]')->hiddenInput(['maxlength' => true])->label(false) ?>
<div class="row">
    <div class="col-6">
        <?= $form->field($node, 'name')->textInput(['maxlength' => true])->label('ชื่อ') ?>
    </div>
    <div class="col-6">
        <?= $form->field($node, 'data_json[phone]')->textInput(['maxlength' => true])->label('เบอร์โทรภายใน') ?>
    </div>

</div>

<div class="row">
    <div class="col-6">
        <?php
        // โหลดค่าปัจจุบัน ถ้ามี
        $leader1Data = $node->data_json['leader1'] ?? null;
        $leader1Text = $leader1Data ? Employees::find()
            ->select(["CONCAT(fname, ' ', lname) AS fullname"])
            ->where(['id' => $leader1Data])
            ->scalar() : '';

        echo $form->field($node, 'data_json[leader1]')->widget(Select2::classname(), [
            'initValueText' => $leader1Text, // แสดงชื่อเดิมตอน update
            'options' => ['placeholder' => 'เลือกบุคคล...', 'multiple' => false],
            'pluginOptions' => [
                'allowClear' => true,
                'ajax' => [
                    'url' => Url::to(['/hr/organization/list-employee']), // Controller action ที่จะ return JSON
                    'dataType' => 'json',
                    'delay' => 250,
                    'data' => new \yii\web\JsExpression('function(params) { return {q:params.term}; }'),
                    'processResults' => new \yii\web\JsExpression('function(data) { 
                return {results: data.items}; 
            }'),
                ],
                'minimumInputLength' => 1,
            ],
            'pluginEvents' => [
                "change" => "function() { 
            var selectedText = $(this).find('option:selected').text();
            $('#organization-data_json-leader1_fullname').val(selectedText);
        }",
            ]
        ])->label('หัวหน้า/ผู้ควบคุม/ประสานงาน');
        ?>
    </div>
    <div class="col-6">
        <?php

        $leader2Data = $node->data_json['leader2'] ?? null;
        $leader2Text = $leader1Data ? Employees::find()
            ->select(["CONCAT(fname, ' ', lname) AS fullname"])
            ->where(['id' => $leader2Data])
            ->scalar() : '';
        echo $form->field($node, 'data_json[leader2]')->widget(Select2::classname(), [
            'initValueText' => $leader2Text, // แสดงชื่อเดิมตอน update
            'options' => ['placeholder' => 'เลือกบุคคล...', 'multiple' => false],
            'pluginOptions' => [
                'allowClear' => true,
                'ajax' => [
                    'url' => Url::to(['/hr/organization/list-employee']), // Controller action ที่จะ return JSON
                    'dataType' => 'json',
                    'delay' => 250,
                    'data' => new \yii\web\JsExpression('function(params) { return {q:params.term}; }'),
                    'processResults' => new \yii\web\JsExpression('function(data) { 
                return {results: data.items}; 
            }'),
                ],
                'minimumInputLength' => 1,
            ],
            'pluginEvents' => [
                "change" => "function() { 
                    var selectedText = $(this).find('option:selected').text();
                    $('#organization-data_json-leader2_fullname').val(selectedText)
                 }",
            ]
        ])->label('รองหัวหน้า')

        ?>
    </div>

</div>