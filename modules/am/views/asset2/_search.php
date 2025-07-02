<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\modules\hr\models\Organization;

/** @var yii\web\View $this */
/** @var app\modules\am\models\Asset2Search $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="asset-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

        <h1><?=$model->q_department?></h1>
<?php
//  $form->field($model, 'q_department')->widget(\kartik\tree\TreeViewInput::className(), [
//     'name' => 'department',
//     'id' => 'treeID',
//     'query' => Organization::find()->addOrderBy('root, lft'),
//     'value' => null,  // ไม่ตั้งค่าเริ่มต้น
//     'headingOptions' => ['label' => 'รายชื่อหน่วยงาน'],
//     'rootOptions' => ['label' => '<i class="fa fa-building"></i>'],
//     'fontAwesome' => true,
//     'asDropdown' => true,
//     'multiple' => false,
//     'options' => [
//         'class' => 'close',
//         'allowClear' => true,
//     ],
//     'pluginOptions' => [
//         'allowClear' => true,
//         'placeholder' => 'เลือกหน่วยงาน...',
//     ],
// ])->label(false);

?>


    <?php echo $form->field($model, 'q_department') ?>
    <?php echo $form->field($model, 'department') ?>

    <?php // echo $form->field($model, 'price') ?>

    <?php // echo $form->field($model, 'purchase') ?>

    <?php // echo $form->field($model, 'department') ?>

    <?php // echo $form->field($model, 'owner') ?>

    <?php // echo $form->field($model, 'life') ?>

    <?php // echo $form->field($model, 'on_year') ?>

    <?php // echo $form->field($model, 'dep_id') ?>

    <?php // echo $form->field($model, 'depre_type') ?>

    <?php // echo $form->field($model, 'budget_year') ?>

    <?php // echo $form->field($model, 'asset_status') ?>

    <?php // echo $form->field($model, 'data_json') ?>

    <?php // echo $form->field($model, 'device_items') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'created_by') ?>

    <?php // echo $form->field($model, 'updated_by') ?>

    <?php // echo $form->field($model, 'deleted_at') ?>

    <?php // echo $form->field($model, 'deleted_by') ?>

    <?php // echo $form->field($model, 'license_plate') ?>

    <?php // echo $form->field($model, 'car_type') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
