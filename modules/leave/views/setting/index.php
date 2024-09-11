<?php

use app\models\Categorise;
use app\modules\leave\models\LeaveEntitlements;
use app\modules\leave\models\LeaveTypes;
use yii\helpers\Html;
use yii\widgets\DetailView;
$this->title = "ตั้งค่าระเบียบสิทธิการลา";
?>


<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-calendar-day"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('../default/menu') ?>
<?php $this->endBlock(); ?>


<div class="card">
  <div class="card-body">

<div class="table-container">
    <table class="table table-striped">
    <thead>
        <tr>
            <th style="width:50px;">รหัส</th>
            <th>รายการ</th>
            <?php foreach(LeaveTypes::find()->all() as $leaveType):?>
                    <th><?=$leaveType->name?></th>
                <?php endforeach;?>
        <tr>
    </thead>
    <tbody>
        <?php foreach(Categorise::find()->where(['name' => 'position_type'])->all() as $item):?>
        <tr>
            <td><?=$item->code?></td>
            <td><?=$item->title?></td>
            <?php foreach(LeaveTypes::find()->all() as $leaveType):?>
                <td></td>
                <?php endforeach;?>
        </tr>
        <?php endforeach;?>
    </tbody>
    </table>
</div>


</div>
</div>



<div class="card">
  <div class="card-body">

<div class="table-container">
    <table class="table table-striped">
    <thead>
        <tr>
            <th style="width:50px;">รหัส</th>
            <th>รายการ</th>
            <?php foreach(Categorise::find()->where(['name' => 'position_type'])->all() as $item):?>
                    <th><?=$item->title?></th>
                <?php endforeach;?>
        <tr>
    </thead>
    <tbody>
        <?php foreach(LeaveTypes::find()->all() as $item):?>
        <tr>
            <td><?=$item->id?></td>
            <td><?=$item->name?></td>
            <?php foreach(Categorise::find()->where(['name' => 'position_type'])->all() as $item):?>
                <td>
                    <?php $le = LeaveEntitlements::findOne(['leave_type_id' => $item->id])?>
                    <?php if($le):?>
                        <?=$le->days_available?>
                        <?else:?>
                            -
                            <?php endif;?>
                </td>
        <?php endforeach;?>
        <?php endforeach;?>
    </tbody>
    </table>
</div>


</div>
</div>


<div class="row d-flex justify-content-center">
<div class="col-xl-2 col-lg-3 col-md-6 col-sm-12">


    <div
        class="p-2 bg-white rounded transform transition-all hover-translate-y-n2 duration-300 shadow-lg hover-shadow mt-3 zoom-in">
        <div class="p-2">
            <!-- Heading -->
            <div class="d-flex justify-content-center">
                <!-- <h2 class="font-weight-bold h5 mb-2">คลังย่อย</h2> -->
                <h2 class="font-weight-bold h5 mb-2"><i class="bi bi-toggles2"></i> ประเภทการลา</h2>
                
            </div>
        </div>
        <div class="d-flex justify-content-center">
            <?=Html::a('<i class="bi bi-gear"></i> ตั้งค่า',['/leave/leave-types'],['class' => 'btn btn-primary text-white bg-purple-600 rounded-md'])?>
        </div>
    </div>
    </div>
</div>