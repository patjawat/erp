<?php

$this->title = 'ภาพรวมการลา';
$this->params['breadcrumbs'][] = ['label' => 'ระบบลา', 'url' => ['/me']];
$this->params['breadcrumbs'][] = ['label' => 'ภาพรวม', 'url' => ['/me']];
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
<i data-lucide="layout-grid"></i>  
        ภาพรวมการลา
    </h4>
</div>
<?php $this->endBlock(); ?>


<?php $this->beginBlock('action'); ?>
<?=$this->render('@app/modules/hr/views/leave/menu',['active' => 'dashboard'])?>
<?php $this->endBlock(); ?>



<div class="row">
    <div class="col-6">
    <?php echo $this->render('leave_summary_year', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ])?>
               
    </div>
    <div class="col-6">
<?php echo $this->render('leave_summary_month', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ])?>
    </div>
    <div class="col-12">
                 <?php echo $this->render('leave_summary', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ])?>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <?php // $this->render('leave_summary')?>
    </div>
</div>