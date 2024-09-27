<?php
use yii\helpers\Html;
?>
<div class="card" style="height:300px">
    <div class="card-body">
<h6>สถิติการลา</h6>
        
<div class="attendance-list">
<div class="row">
<div class="col-md-4">
<div class="attendance-details">
<h4 class="text-primary">9</h4>
<p>Total Leaves</p>
</div>
</div>
<div class="col-md-4">
<div class="attendance-details">
<h4 class="text-pink">5.5</h4>
<p>Leaves Taken</p>
</div>
</div>
<div class="col-md-4">
<div class="attendance-details">
<h4 class="text-success">04</h4>
<p>Leaves Absent</p>
</div>
</div>
<div class="col-md-4">
<div class="attendance-details">
<h4 class="text-purple">0</h4>
<p>Pending Approval</p>
</div>
</div>
<div class="col-md-4">
<div class="attendance-details">
<h4 class="text-info">214</h4>
<p>Working Days</p>
</div>
</div>
<div class="col-md-4">
<div class="attendance-details">
<h4 class="text-danger">2</h4>
<p>Loss of Pay</p>
</div>
</div>
</div>
</div>

<div class="view-attendance">
<?=Html::a('ระบบการลา <i class="fe fe-arrow-right-circle"></i>',['/lm/leave'],['class' => 'btn btn-light'])?>
</div>
    </div>
</div>
