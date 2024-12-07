
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-cubes-stacked"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('../menu') ?>
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