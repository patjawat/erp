<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;

$this->title = 'MyDashboard';
$this->params['breadcrumbs'][] = ['label' => 'MyDashboard', 'url' => ['/me']];
?>


<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-gauge fs-4 text-primaryr"></i> <?php echo $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php  echo $this->render('@app/modules/me/menu') ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?php echo $this->render('@app/modules/me/menu',['active' => 'dashboard']) ?>
<?php $this->endBlock(); ?>


<style>
#pr-order>.card {
    height: 291px;
}
</style>

<div class="row">
    <div class="col-12">
        <div class="row">
            <div class="col-6">
                <?php echo $this->render('welcome'); ?>
                <?php echo $this->render('attendance'); ?>

            </div>
            <div class="col-6">
                <?php echo $this->render('leave', ['searchModel' => $searchModel]); ?>
            </div>
            <div class="col-12">
                <?php echo Html::a('<i class="fa-solid fa-list-check"></i> กิจกรรมทั้งหมด', ['/me/activity'], ['class' => 'btn btn-light']); ?>
                <div id="viewDocument"></div>

            </div>
        </div>
    </div>
</div>




<?php
$documentUrl = Url::to(['/me/documents/show-home']);
// $urlRepair = Url::to(['/me/repair']);
// $ApproveStockUrl = Url::to(['/me/approve/stock-out']);
// $ApprovePurchaseUrl = Url::to(['/me/approve/purchase']);
// $ownerAssetUrl = Url::to(['/me/owner']);
// $urlRepair = Url::to(['/me/repair-me']);
$js = <<< JS
    loadDocumentMe();

    // loadRepairHostory();
    // loadApproveStock();
    // loadPurchase();
    // loadOwnerAsset();
    
    //หนังสือ
    async function  loadDocumentMe(){
        await $.ajax({
            type: "get",
            url: "$documentUrl",
            dataType: "json",
            data:{
                list:true,
                callback:'me'
            },
            beforeSend: function(){
                $('#viewDocument').html('<p>กำลังโหลดหนังสือ</p>');
            },
            success: function (res) {
                    $('#viewDocument').html(res.content);
            }
        });
    }
    
    //ประวัติการซ่อม
    // async function  loadRepairHostory(){
    //     await $.ajax({
    //         type: "get",
    //         url: "urlRepair",
    //         data:{
    //             "title":"ประวัติการซ่อม",
    //             "name":"repair",
    //         },
    //         dataType: "json",
    //         success: function (res) {
    //             if(res.summary > 0){
    //                 \$('#viewRepair').html(res.content);
    //             }
    //         }
    //     });
    // }

     //ขอเบิกวัสดุ
    //  async function  loadApproveStock(){
    //     await $.ajax({
    //         type: "get",
    //         url: "ApproveStockUrl",
    //         dataType: "json",
    //         success: function (res) {
    //             if(res.count != 0){
    //                 \$('#viewApproveStock').html(res.content);
    //             }else{
    //                 $('#viewApproveStock').hide()
    //             }
    //         }
    //     });
    // }

         //ขออนุมิติจัดซื้อจัดจ้าง
        // async  function  loadPurchase(){
        //     await \$.ajax({
        //         type: "get",
        //         url: "ApprovePurchaseUrl",
        //         dataType: "json",
        //         success: function (res) {
        //             console.log(res.count)
        //             if(res.count != 0){
        //                 \$('#viewApprovePurchase').html(res.content);
        //             }else{
        //                 $('#viewApprovePurchase').hide();
        //             }
        //         }
        //     });
        // }


    //ทรัพย์สินที่รับผิดขอบ
    // async function  loadOwnerAsset(){
    //    await  \$.ajax({
    //         type: "get",
    //         url: "ownerAssetUrl",
    //         dataType: "json",
    //         success: function (res) {
    //             console.log(res.count)
    //             if(res.count != 0){
    //                 \$('#viewOwnerAsset').html(res.content);
    //             }else{
    //                 $('#viewOwnerAsset').hide();
    //             }
    //         }
    //     });
    // }

    JS;
$this->registerJS($js, yii\web\View::POS_END);
?>

<?php // Pjax::end(); ?>