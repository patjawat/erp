<?php
use yii\helpers\Url;
use yii\widgets\Pjax;

$this->title = 'My DashBoard';
?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-folder-check"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<style>
#pr-order>.card {
    height: 291px;
}
</style>
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
                        <h5>กิจกรรม/ความเคลื่อนไหว</h5>

                        <?php // $this->render('activity') ?>

                    </div>
                </div>
            </div>
        </div>




    </div>
    <div class="col-6">
        <div id="viewApproveStock">Loading...</div>
        <div id="viewApprovePurchase">Loading...</div>

        <?php Pjax::begin(['id' => 'repair-container', 'timeout' => 5000]); ?>
        <!-- <div class="card" style="height:300px;">
            <div class="card-body">
                <h5>กิจกรรม/ความเคลื่อนไหวss</h5>
                <div id="viewRepair" class="mt-4"></div>
                <?php //  $this->render('activity') ?>

            </div>
        </div> -->
        <?php Pjax::end(); ?>
    </div>
</div>

<div class="row">
    <div class="col-6">
        <div class="card" style="height:300px;">
            <div class="card-body">
                <h6>กลุ่ม/ทีมประสาน</h6>
                <?php // $this->render('activity') ?>
            </div>
        </div>
    </div>
    <div class="col-6">
        <div id="viewOwnerAsset">Loading...</div>
    </div>
</div>


<?php
$urlRepair = Url::to(['/me/repair']);
$ApproveStockUrl = Url::to(['/me/approve/stock']);
$ApprovePurchaseUrl = Url::to(['/me/approve/purchase']);
$ownerAssetUrl = Url::to(['/me/owner']);
// $urlRepair = Url::to(['/me/repair-me']);
$js = <<< JS

    loadRepairHostory();
    loadApproveStock();
    loadPurchase();
    loadOwnerAsset();
    
    //ประวัติการซ่อม
    async function  loadRepairHostory(){
        await \$.ajax({
            type: "get",
            url: "$urlRepair",
            data:{
                "title":"ประวัติการซ่อม",
                "name":"repair",
            },
            dataType: "json",
            success: function (res) {
                if(res.summary > 0){
                    \$('#viewRepair').html(res.content);
                }
            }
        });
    }

     //ขอเบิกวัสดุ
     async function  loadApproveStock(){
        await \$.ajax({
            type: "get",
            url: "$ApproveStockUrl",
            dataType: "json",
            success: function (res) {
                if(res.count != 0){
                    \$('#viewApproveStock').html(res.content);
                }else{
                    $('#viewApproveStock').hide()
                }
            }
        });
    }

         //ขออนุมิติจัดซื้อจัดจ้าง
        async  function  loadPurchase(){
            await \$.ajax({
                type: "get",
                url: "$ApprovePurchaseUrl",
                dataType: "json",
                success: function (res) {
                    console.log(res.count)
                    if(res.count != 0){
                        \$('#viewApprovePurchase').html(res.content);
                    }else{
                        $('#viewApprovePurchase').hide();
                    }
                }
            });
    }

    //ทรัพย์สินที่รับผิดขอบ
    async function  loadOwnerAsset(){
       await  \$.ajax({
            type: "get",
            url: "$ownerAssetUrl",
            dataType: "json",
            success: function (res) {
                console.log(res.count)
                if(res.count != 0){
                    \$('#viewOwnerAsset').html(res.content);
                }else{
                    $('#viewOwnerAsset').hide();
                }
            }
        });
    }





    JS;
$this->registerJS($js, yii\web\View::POS_END);
?>