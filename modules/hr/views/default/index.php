<?php
use yii\helpers\Url;
use yii\widgets\Pjax;
use app\components\EmployeeHelper;

$this->title = 'บุคลากร';

?>
<?php Pjax::begin(['id' => 'hr-container', 'timeout' => 50000]); ?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-people-fill"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
Dashboard
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('@app/modules/hr/views/employees/menu') ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?=$this->render('@app/modules/hr/views/employees/menu',['active' => 'dashboard'])?>
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
          
        <?php // $this->render('perfection_data') ?>
    </div>
</div>

</div>

