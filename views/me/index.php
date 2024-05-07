<?php
/** @var yii\web\View $this */
?>
<div class="row">
    <div class="col-xl-8 col-lg-8 col-md-6 col-sm-12 col-sx-12">
                <?=$this->render('@app/modules/hr/views/employees/avatar',['model' => $model])?>
    </div>
    <div class="col-4">
        <div class="card">
            <div class="card-body">
                <?=$this->render('leave_total')?>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h4 class="card-title">ปฏิทินกิจกรรม</h4>
                <p class="card-text">Text</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">คณะกรรมการทีมประสาน</h4>
                <p class="card-text">Text</p>
            </div>
        </div>

    </div>

</div>