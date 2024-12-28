<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\select2\Select2;
use kartik\widgets\ActiveForm;
// use softark\duallistbox\DualListbox;
use softark\duallistbox\DualListbox;
use app\modules\hr\models\Organization;
use iamsaint\datetimepicker\Datetimepicker;

// use iamsaint\datetimepicker\DateTimePickerAsset::register($this);

/** @var yii\web\View $this */
/** @var app\modules\dms\models\Documents $model */
/** @var yii\widgets\ActiveForm $form */
?>
<?= $form->field($model, 'req_approve')->checkbox(['custom' => true, 'switch' => true, 'checked' => $model->req_approve == 1 ? true : false])->label('เสนอผู้อำนวยการ'); ?>
<?= $form->field($model, 'data_json[department_tag]')->widget(\kartik\tree\TreeViewInput::className(), [
                    'query' => Organization::find()->addOrderBy('root, lft'),
                    'headingOptions' => ['label' => 'รายชื่อหน่วยงาน'],
                    'rootOptions' => ['label' => '<i class="fa fa-building"></i>'],
                    'fontAwesome' => true,
                    'asDropdown' => true,
                    'multiple' => true,
                    'options' => ['disabled' => false],
                ])->label('ส่งหน่วยงาน'); ?>

<div class="border border-secondary border-opacity-25 p-3 rounded py-5">

              
<?php 
    echo DualListbox::widget([
        'model' => $model,
        'attribute' => 'data_json[employee_tag]',
        'items' => $model->listEmployeeSelectTag(),
        'options' => [
            'id' => 'myDualListbox', // กำหนด ID ให้ custom
            'multiple' => true,
            'size' => 10,
            'encode' => false, // รองรับ HTML
        ],
        'clientOptions' => [
            'moveOnSelect' => false,
            'nonSelectedListLabel' => 'รายชื่อบุคลากร',
            'selectedListLabel' => 'ส่งต่อบุคลากร',
            'selectorMinimalHeight' => 300, // ความสูงของ Dual Listbox
        ],
    ]);
    ?>

</div>