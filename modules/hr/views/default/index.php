<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;

$this->title = 'ภาพรวมบุคลากร';
$this->params['breadcrumbs'][] = ['label' => 'บุคลากร', 'url' => ['/me']];
$this->params['breadcrumbs'][] = 'ภาพรวม';

?>
<?php Pjax::begin(['id' => 'hr-container', 'timeout' => 50000]); ?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
<i data-lucide="layout-grid"></i>  
        <span class="d-block">
            <?= $this->title; ?>
        </span>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/hr/menu', ['active' => 'dashboard'])
?>
<?php $this->endBlock(); ?>


<?= $this->render('employee_summary', [
    'dataProvider' => $dataProvider,
]) ?>
<div class="row">
    <div class="col-lg-8 col-md-6 col-sm-12">

        <?= $this->render('gender_chart', ['dataProviderGender' => $dataProviderGender]) ?>

        <?php $this->render('position_name', [
            'dataProviderPositionName' => $dataProviderPositionName
        ]) ?>

        <?php echo $this->render('position_group_type', [
            'dataProviderWorkGroup' => $dataProviderWorkGroup
        ]) ?>
    </div>

    <div class="col-lg-4 col-md-6 col-sm-12">
        <?= $this->render('gender_pie_chart', [
            'dataProviderPositionType' => $dataProviderPositionType,
            'dataProviderGenderM' => $dataProviderGenderM,
            'dataProviderGenderW' => $dataProviderGenderW,
        ]) ?>
        <?= $this->render('generation_chart', [
            'dataProviderGenB' => $dataProviderGenB,
            'dataProviderGenX' => $dataProviderGenX,
            'dataProviderGenY' => $dataProviderGenY,
            'dataProviderGenZ' => $dataProviderGenZ,
            'dataProviderGenA' => $dataProviderGenA,
        ]) ?>
        <?php
        // $this->render('position_level_chart',[
        //     'dataProviderPositionLevel' => $dataProviderPositionLevel
        // ])
        ?>
        <?= $this->render('position_type_chart', [
            'dataProviderPositionType' => $dataProviderPositionType
        ]) ?>

        <?php // $this->render('perfection_data') 
        ?>
    </div>
</div>

</div>