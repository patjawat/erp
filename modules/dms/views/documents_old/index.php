<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\components\AppHelper;
use app\modules\dms\models\Documents;
/** @var yii\web\View $this */
/** @var app\modules\dms\models\DocumentSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'หนังสือรับ';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-journal-text fs-4"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php  echo $this->render('@app/modules/dms/menu') ?>
<?php $this->endBlock(); ?>

<style>

.modal-fullscreen .modal-dialog {
    max-width: 100%;
    width: 100%;
    height: 100%;
    margin: 0;
    padding: 0;
}

.modal-fullscreen .modal-header{
   padding:0.5rem 0.5rem;
   padding-left:2rem;
}
.modal-fullscreen .modal-content {
    height: 100%;
    border: 0;
    border-radius: 0;
}

.modal-fullscreen .modal-body {
    overflow-y: auto;
    height: calc(100% - 56px); /* Adjust for header/footer height if needed */
}

</style>
<?php Pjax::begin(['timeout' => 80000]); ?>

<div class="documents-index">

    <div class="card">
        <div class="card-body">
        <div class="d-flex justify-content-between align-top align-items-center">
        <h6>
                <i class="bi bi-ui-checks"></i> ทะเบียนหนังสือ
                <span
                    class="badge rounded-pill text-bg-primary"><?php echo number_format($dataProvider->getTotalCount(), 0) ?></span>
                รายการ
            </h6>
</div>
<div class="d-flex justify-content-between align-top align-items-center">
 
    <?php  echo $this->render('@app/modules/dms/views/documents/_search', ['model' => $searchModel]); ?>
    <?= Html::a('<i class="fa-solid fa-plus"></i> ออกเลขหนังสือรับ', ['/dms/documents/create', 'title' => '<i class="fa-solid fa-calendar-plus"></i> บันทึกขออนุมัติการลา'], ['class' => 'btn btn-primary shadow rounded-pill', 'data' => ['size' => 'modal-lg']]) ?>
</div>

<div
    class="table-responsive"
>

    <table
        class="table table-striped table-fixed"
    >
        <thead>
            <tr>
                <th scope="col" style="width:55px;">เลขรับ</th>
                <th scope="col">เรื่อง</th>
                <th scope="col" class="text-center" style="width:105px;">ไฟล์แนบ</th>
                <th scope="col" style="width:130px;">วันที่หนังสือ</th>
                <th scope="col" class="text-center">สถานะ</th>
                <th scope="col">แก้ไข</th>
                <th scope="col" style="width:60px;">ส่งต่อ</th>
            </tr>
        </thead>
        <tbody class="align-middle  table-group-divider table-hover">
            <?php foreach($dataProvider->getModels() as $item):?>
            <tr class="">
                <td class="fw-semibold"><?php echo $item->doc_regis_number?></td>
                <td class="fw-light align-middle">
                    <a href="<?php echo Url::to(['/dms/documents/view','id' => $item->id])?>" class="text-dark open-modal-fullscreen">
                        <div class=" d-flex flex-column">
                        
                            <span class="fw-semibold fs-6">
                                <?php if($item->doc_speed == 'ด่วนที่สุด'):?>
                            <span class="badge text-bg-danger fs-13"><i class="fa-solid fa-circle-exclamation"></i> ด่วนที่สุด</span> 
                            <?php endif;?>   

                            <?php if($item->doc_speed == 'ด่วน'):?>
                            <span class="badge text-bg-warning fs-13"><i class="fa-solid fa-circle-exclamation"></i> ด่วน</span> 
                            <?php endif;?>   
                            <?php echo $item->topic?>
                            
                        </div>
                        <!-- <span class="badge rounded-pill badge-soft-secondary text-primary fw-lighter fs-13"> -->
                            <span class="text-primary fw-normal fs-13">
                            <i class="fa-solid fa-inbox"></i> <?php  echo $item->documentOrg->title ?? '-';?>
                            <span class="badge rounded-pill badge-soft-secondary text-primary fw-lighter fs-13">
                        <i class="fa-regular fa-eye"></i> <?php echo $item->viewCount()?>
                    </span>    
                            </span>
                            </a>
                            </td>
                            <td class="text-center">
                            <?php echo $item->isFile() ? Html::a('<i class="fas fa-paperclip"></i>',['/dms/documents/file-comment','id' => $item->id],['class' => 'open-modal','data' => ['size' => 'modal-xl']]) : ''?>    
                           </td>
                    <td class="fw-light align-middle">
                        <div class=" d-flex flex-column">
                            <span class="fw-normal fs-6"><?php echo $item->viewDocDate()?></span>
                            <span class="fw-lighter fs-13"><?php echo AppHelper::timeDifference($item->doc_date)?></span>
                       </div>
                    </td>
                    <td class="text-center">
                    <?php 
                    try {
                        echo $item->documentStatus->title;
                    } catch (\Throwable $th) {
                        //throw $th;
                    }
                    ?>
                    </td>
                <td><?php echo Html::a('<i class="fa-regular fa-pen-to-square fa-2x"></i>',['update', 'id' => $item->id])?></td>
                <td>
                <?php echo Html::a(' <i class="fas fa-share fa-2x text-secondary"></i>',['/dms/documents/share-file','id' => $item->id,'title' => '<i class="fas fa-share"></i>ส่งต่อ'],['class' => 'open-modal','data' => ['size' => 'modal-lg']])?>    
               </td>
            </tr>
            <?php endforeach;?>
           
        </tbody>
    </table>

    </div>
    </div>
    </div>
    
</div>

<div class="iq-card-footer text-muted d-flex justify-content-end mt-4">
    <?= yii\bootstrap5\LinkPager::widget([
        'pagination' => $dataProvider->pagination,
        'firstPageLabel' => 'หน้าแรก',
        'lastPageLabel' => 'หน้าสุดท้าย',
        'options' => [
            'listOptions' => 'pagination pagination-sm',
            'class' => 'pagination-sm',
        ],
    ]); ?>
</div>
<?php Pjax::end(); ?>


<?php
$js = <<< JS




$("body").on("click", ".open-modal-fullscreen", function (e) {
  e.preventDefault();
  var url = $(this).attr("href");
  var size = $(this).data("size");

  $.ajax({
    type: "get",
    url: url,
    dataType: "json",
    success: function (response) {
      $("#fullscreen-modal").modal("show");
      $("#fullscreen-modal-label").html(response.title);
      $(".modal-body").html(response.content);
      $(".modal-footer").html(response.footer);
      $(".modal-dialog").removeClass("modal-sm modal-md modal-lg modal-xl");
      $(".modal-dialog").addClass(size);
      $(".modal-content").addClass("card-outline card-primary");
    },
    error: function (xhr) {
      $("#fullscreen-modal-label").html('เกิดข้อผิดพลาด');
      $(".modal-body").html('<h5 class="text-center"><i class="fa-solid fa-triangle-exclamation text-danger"></i> ไม่อนุญาต</h5>');
      $(".modal-dialog").removeClass("modal-sm modal-md modal-lg modal-xl");
      $(".modal-dialog").addClass("modal-md");
    },
  });
});



JS;
$this->registerJS($js);

?>