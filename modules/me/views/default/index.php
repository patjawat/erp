<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use app\modules\purchase\models\Order;
// $this->registerJsFile('@web/owl/owl.carousel.min.js', ['depends' => [yii\web\JqueryAsset::className()]]);
// $this->registerCssFile('@web/owl/owl.carousel.min.css');

$this->title = 'My DashBoard';
?>

<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-clipboard-user fs-1"></i> <?php echo $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php  echo $this->render('@app/modules/dms/menu') ?>
<?php $this->endBlock(); ?>

<?php // Pjax::begin(['id' => 'me-container', 'timeout' => 500000]); ?>
<style>
#pr-order>.card {
    height: 291px;
}
</style>


<div class="row">
    <div class="col-8">
        <div class="row">
            <div class="col-6">
                <?php echo $this->render('welcome'); ?>
                <?php echo $this->render('attendance'); ?>

            </div>
            <div class="col-6">
                <?php echo $this->render('leave', ['searchModel' => $searchModel]); ?>
                
            </div>
            <div class="col-12">

                <!-- <div class="d-flex justify-content-between">
                    <h6><i class="fa-regular fa-bell"></i> กิจกรรมสำคัญ</h6>
                    <?php echo Html::a('<i class="fa-solid fa-list-check"></i> กิจกรรมทั้งหมด', ['/me/activity'], ['class' => 'btn btn-light']); ?>
                </div>
                <div id="viewApproveStock">Loading...</div>
                <div id="viewApprovePurchase">Loading...</div> -->


                <?php // echo $this->render('document'); ?>
                <div id="viewDocument"></div>
                <?php echo $this->render('team_work'); ?>

            </div>
        </div>
    </div>

    <div class="col-4">
        <div class="card" style="height: 620px;">
            <div class="card-body">
                <h6><i class="bi bi-app-indicator"></i> บริการ</h6>
                <?php echo $this->render('app_indicator'); ?>
            </div>
        </div>


    </div>
</div>
<div class="row">

    <div class="col-3">

        <?php //  $this->render('@app/modules/hr/views/employees/avatar', ['model' => $model])?>
        <!-- <div class="card" style="height:300px;">
                    <div class="card-body">
                        <?php echo $this->render('leave_total'); ?>
                    </div>
                </div> -->
        <?php Pjax::begin(['id' => 'repair-container', 'timeout' => 5000]); ?>
        <!-- <div class="card" style="height:300px;">
            <div class="card-body">
                <h5>กิจกรรม/ความเคลื่อนไหวss</h5>
                <div id="viewRepair" class="mt-4"></div>
                <?php //  $this->render('activity')?>

            </div>
        </div> -->
        <?php Pjax::end(); ?>
    </div>
</div>

<div class="row">

    <div class="col-12">
        <div id="viewOwnerAsset">Loading...</div>
    </div>
</div>




<?php
$urlRepair = Url::to(['/me/repair']);
$ApproveStockUrl = Url::to(['/me/approve/stock-out']);
$ApprovePurchaseUrl = Url::to(['/me/approve/purchase']);
$ownerAssetUrl = Url::to(['/me/owner']);
$documentUrl = Url::to(['/me/documents']);
// $urlRepair = Url::to(['/me/repair-me']);
$js = <<< JS

    loadRepairHostory();
    // loadApproveStock();
    loadPurchase();
    loadOwnerAsset();
    loadDocumentMe();
    

    //หนังสือ
    async function  loadDocumentMe(){
        await $.ajax({
            type: "get",
            url: "$documentUrl",
            dataType: "json",
            data:{
                list:true 
            },
            success: function (res) {
                    $('#viewDocument').html(res.content);
            }
        });
    }
    
    //ประวัติการซ่อม
    async function  loadRepairHostory(){
        await $.ajax({
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
        await $.ajax({
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

<?php // Pjax::end(); ?>