<?php
use yii\helpers\Url;
use yii\widgets\Pjax;
use app\components\EmployeeHelper;
$this->title = "บุคลากร";


?>
<?php Pjax::begin(['id' => 'hr-container','timeout' => 50000 ]); ?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-people-fill"></i> <?=$this->title;?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
Dashboard
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?php  echo $this->render('_search', ['model' => $searchModel]); ?>
<?php $this->endBlock(); ?>



<div class="row">
    <div class="col-lg-8 col-md-6 col-sm-12">
        <?=$this->render('employee_summary',['dataProvider' => $dataProvider,])?>
        <?=$this->render('gender_chart',['dataProviderGender' => $dataProviderGender])?>
        
        <?php $this->render('position_name',[
            'dataProviderPositionName' => $dataProviderPositionName
            ])?>

<?php  echo $this->render('position_group_type',[
    'dataProviderWorkGroup' => $dataProviderWorkGroup
    ])?>
    </div>
    
    <div class="col-lg-4 col-md-6 col-sm-12">
        <?=$this->render('gender_pie_chart',[
            'dataProviderPositionType' => $dataProviderPositionType,
            'dataProviderGenderM' => $dataProviderGenderM,
            'dataProviderGenderW' => $dataProviderGenderW,
            ])?>
        <?=$this->render('generation_chart',[
              'dataProviderGenB' => $dataProviderGenB,
              'dataProviderGenX' => $dataProviderGenX,
              'dataProviderGenY' => $dataProviderGenY,
              'dataProviderGenZ' => $dataProviderGenZ,
              'dataProviderGenA' => $dataProviderGenA,
        ])?>
        <?php 
        // $this->render('position_level_chart',[
        //     'dataProviderPositionLevel' => $dataProviderPositionLevel
        // ])
        ?>
        <?= $this->render('position_type_chart',[
            'dataProviderPositionType' => $dataProviderPositionType
        ])?>
          
        <?php // $this->render('perfection_data')?>
    </div>
</div>

</div>

