<?php
use yii\helpers\Html;
?>
<div class="card">
    <div class="card-body">
        <h6>สถิติการเข้าทำงาน</h6>


            <div class="d-flex justify-content-between align-items-center  bg-primary bg-opacity-10  p-2 rounded">
                <div>
                    <h6>เวลาทำงาน</h6>
                    <p>08:30</p>
                </div>
                <?=Html::a('<i class="fa-solid fa-stopwatch"></i> Check-in',['/'],['class' => 'btn btn-primary shadow rounded-pill'])?>
            </div>

    </div>
</div>