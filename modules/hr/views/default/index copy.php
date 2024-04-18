<?php
use yii\helpers\Url;
use app\components\EmployeeHelper;
$this->title = "บุคลากร"
?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-people-fill"></i> <?=$this->title;?>
<?php $this->endBlock(); ?>


<?=$this->render('employee_summary')?>
<div class="row">
    <div class="col-xxl-5 col-xl-6 col-lg-6">
        <?=$this->render('pie_chart')?>
    </div>
    <div class="col-xxl-7 col-xl-12">
        <?=$this->render('bar_chart')?>

    </div>
</div>


<div class="row mt-4">

    <div class="col-4">


    </div>
    <div class="col-4">
        <?=$this->render('client1')?>
    </div>
    <div class="col-4">
        <?=$this->render('client2')?>

    </div>

</div>

<div class="row mt-4 mb-5">
    <div class="col-12">
        <?=$this->render('employees_list')?>

    </div>
</div>