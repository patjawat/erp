<?php
use yii\helpers\Url;
?>
<div class="row">
    <div class="col-xl-8 col-lg-8 col-md-6 col-sm-12 col-sx-12">
                <?=$this->render('@app/modules/hr/views/employees/avatar',['model' => $model])?>
<div class="card">
    <div class="card-body">
        <h4>การแจ้งซ่อม</h4>
        <div id="viewRepairHistory" class="mt-4"></div>
    </div>
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
            "user_id":"$model->created_by"
        },
        dataType: "json",
        success: function (res) {
            $('#viewRepairHistory').html(res.content);
            console.log(res.content);
        }
    });
}


JS;
$this->registerJS($js, yii\web\View::POS_END);
?>