<?php
use yii\helpers\Url;
$this->title = "My DashBoard";
?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-folder-check"></i> <?=$this->title;?>
<?php $this->endBlock(); ?>

<div class="row">
    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-sx-12">
        <?= $this->render('@app/modules/hr/views/employees/avatar', ['model' => $model]) ?>

        <div class="row">
            <div class="col-6">
                <div class="card" style="height:300px;">
                    <div class="card-body">
                        <?= $this->render('leave_total') ?>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="card" style="height:300px;">
                    <div class="card-body">
                        <h5>กิจกรม/ความเคลื่อนไหว</h5>
                        <?= $this->render('activity') ?>

                    </div>
                </div>
            </div>
        </div>




    </div>
    <div class="col-6">

        <div class="card" style="height:300px;">
            <div class="card-body">
                <h5>กิจกรม/ความเคลื่อนไหว</h5>
                <?= $this->render('activity') ?>

            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5>ขออนุมัติ</h5>
                <?=$this->render('req_approve') ?>
            </div>
        </div>

    </div>

</div>



<div class="row">
    <div class="col-4">
        <div class="card" style="height:300px;">
            <div class="card-body">
                <h5>ทรัพสินย์ที่รับมอบหมาย</h5>
                <?= $this->render('activity') ?>

            </div>
        </div>
    </div>
    <div class="col-4">
        <div class="card" style="height:300px;">
            <div class="card-body">
                <h5>กลุ่ม/ทีมประสาน</h5>
                <?= $this->render('activity') ?>

            </div>
        </div>

    </div>
    <div class="col-4">
        <div class="card" style="height:300px;">
            <div class="card-body">
                <h5>กิจกรม/ความเคลื่อนไหว</h5>
                <?= $this->render('activity') ?>
            </div>
        </div>

    </div>
</div>



<div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div id="viewRepairHistory" class="mt-4"></div>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-12"></div>
        </div>
<?php
$urlRepair = Url::to(['/me/repair-me']);
$js = <<< JS

    loadRepairHostory()

    //ประวัติการซ่อม
    function  loadRepairHostory(){
        \$.ajax({
            type: "get",
            url: "$urlRepair",
            data:{
                "title":"ประวัติการซ่อม",
                "name":"repair",
            },
            dataType: "json",
            success: function (res) {
                if(res.summary > 0){
                    \$('#viewRepairHistory').html(res.content);
                }
            }
        });
    }


    JS;
$this->registerJS($js, yii\web\View::POS_END);
?>