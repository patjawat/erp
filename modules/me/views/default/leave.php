<?php
use yii\helpers\Html;
?>
<div class="card" style="height:300px">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h6><i class="fa-solid fa-chart-pie"></i> สถิติการลา</h6>
            <?php echo $this->render('_search_year', ['model' => $searchModel]); ?>
        </div>
            
        <div class="mt-3">
            <div class="row">
                <div class="col-md-4">
                    <div class="d-flex justify-content-center flex-column">
                       
                        <h4 class="text-primary text-center"><?php echo $searchModel->leaveSumDays()['used_leave']?>/<?php echo $searchModel->leaveSumDays()['sum_days']?></h4>
                        <p class="text-center">ลาพักผ่อน/คงเหลือ</p>
                    </div>
                </div>
                <div class="col-md-4">
                <div class="d-flex justify-content-center flex-column">
                        <h4 class="text-center"><?php echo $searchModel->sumLeaveType('LT3')?></h4>
                        <p class="text-center">ลากิจ</p>
                    </div>
                </div>
                <div class="col-md-4">
                <div class="d-flex justify-content-center flex-column">
                        <h4 class="text-center"><?php echo $searchModel->sumLeaveType('LT1')?></h4>
                        <p class="text-center">ลาป่วย</p>
                    </div>
                </div>
                <div class="col-md-4">
                <div class="d-flex justify-content-center flex-column">
                        <h4 class="text-center"><?php echo $searchModel->countLeaveType('Pending')?></h4>
                        <p class="text-center">รอดำเนินการ</p>
                    </div>
                </div>
                <div class="col-md-4">
                <div class="d-flex justify-content-center flex-column">
                        <h4 class="text-center"><?php echo $searchModel->countLeaveType('Cancel')?></h4>
                        <p class="text-center">ยกเลิก</p>
                    </div>
                </div>
                <div class="col-md-4">
                <div class="d-flex justify-content-center flex-column">
                        <h4 class="text-center"><?php echo $searchModel->countLeaveType('Allow')?></h4>
                        <p class="text-center">อนุมัติ</p>
                    </div>
                </div>
            </div>
        </div>

        </div>
        <div class="card-footer">
        <?=Html::a('ทะเบียนประวัติ <i class="fe fe-arrow-right-circle"></i>',['/me/leave'],['class' => 'btn btn-light'])?>

    </div>
</div>