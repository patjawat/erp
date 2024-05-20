<?php
use yii\helpers\Url;
?>
<div class="row">
    <div class="col-xl-8 col-lg-8 col-md-6 col-sm-12 col-sx-12">
        <?=$this->render('@app/modules/hr/views/employees/avatar',['model' => $model])?>
        <!-- <div class="card">
    <div class="card-body">
        <h6>บันทึกการแจ้งซ่อม</h6> -->
        <!-- </div>
    </div> -->
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div id="viewRepairHistory" class="mt-4"></div>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-12"></div>
        </div>

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

<?php
$urlRepair = Url::to(['/helpdesk/repair/history']);
$js = <<< JS

loadRepairHostory()

//ประวัติการซ่อม
function  loadRepairHostory(){
    $.ajax({
        type: "get",
        url: "$urlRepair",
        data:{
            "title":"ประวัติการซ่อม",
            "name":"repair",
        },
        dataType: "json",
        success: function (res) {
            if(res.summary > 0){
                $('#viewRepairHistory').html(res.content);
            }
        }
    });
}


JS;
$this->registerJS($js, yii\web\View::POS_END);
?>