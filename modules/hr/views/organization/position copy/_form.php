<?php
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use app\modules\hr\models\Employees;
?>
<?php
// echo "<pre>";
// print_r($node);
// echo "</pre>";

switch ($node->lvl) {
    case 1:
       $label = [
        'name' => 'ประเภท'
       ];
        break;
        case 2:
            $label = [
             'name' => 'กลุ่มงาน'
            ];
             break;
    
    default:
    $label = [
        'name' => 'ชื่อ'
       ];
        break;
}
?>
    <?= $form->field($node, 'tb_name')->hiddenInput(['value' => 'position','maxlength' => true])->label(false) ?>
<div class="row">
<div class="col-3">
    <?= $form->field($node, 'code')->textInput(['maxlength' => true])->label('รหัส') ?>
</div>
<div class="col-9">
    <?= $form->field($node, 'data_json[position_name]')->textInput(['maxlength' => true])->label('ชื่อสายงาน') ?>
    
    </div>
    <?php //if($node->lvl == 3):?>
        <div class="col-12">
    <?= $form->field($node, 'name')->textInput(['maxlength' => true])->label('ชื่อในตำแหน่งสายงาน') ?>

</div>

<?php // endif;?>
<div class="col-12">
<?php $form->field($node, 'leader')->widget(Select2::classname(), [
    'data' => ArrayHelper::map(Employees::find()->all(),'id','fullname'),
    'options' => ['placeholder' => 'เลือกบุคคล...', 'multiple' => false],
    'pluginOptions' => [
       
    ],
])->label('หัวหน้า/ผู้ควบคุม/ประสานงาน') ?>
</div>
</div>
