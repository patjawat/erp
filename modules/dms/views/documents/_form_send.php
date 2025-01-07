<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
// use softark\duallistbox\DualListbox;
use app\components\UserHelper;
use kartik\widgets\ActiveForm;
use app\modules\hr\models\Employees;
use softark\duallistbox\DualListbox;
use app\modules\hr\models\Organization;
use app\modules\dms\models\DocumentTags;
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

$tags = DocumentTags::find()->where(['name' => 'employee','document_id' => $model->id])->all();
$list = ArrayHelper::map($tags, 'tag_id','tag_id');
$model->tags_employee = $list;
echo $form->field($model, 'tags_employee')->widget(Select2::classname(), [
    'data' => $model->listEmployeeSelectTag(),
    'options' => ['placeholder' => 'Select a state ...'],
    'pluginOptions' => [
        'allowClear' => true,
       'multiple' => true,
    ],
])->label('ส่งต่อ');

?>

</div>

<?php
$me = Employees::findOne(Yii::$app->user->id);
// echo $me->user->line_id;
?>