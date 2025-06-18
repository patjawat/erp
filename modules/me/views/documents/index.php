<?php
use yii\helpers\Url;
use yii\bootstrap5\Html;
use yii\bootstrap5\LinkPager;
$this->title = 'ทะเบียนหนังสือ';
?>
     
<?php $this->beginBlock('page-title'); ?>
<i class="fa-regular fa-file-lines fs-4"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?php  echo $this->render('@app/modules/me/views/documents/menu',['action' => $action]) ?>
<?php $this->endBlock(); ?>


<?php $this->beginBlock('navbar_menu'); ?>
<?php echo $this->render('@app/modules/me/menu',['active' => 'dashboard']) ?>
<?php $this->endBlock(); ?>

<?php if(!isset($list)):?>
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-center align-top align-items-center">
            <?php  echo $this->render('_search', ['model' => $searchModel,'action' => $action]); ?>
              

        </div>
    </div>
</div>
<?php endif;?>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-top align-items-center">
            <h6>
                <i class="bi bi-ui-checks"></i> ทะเบียนหนังสือ
                <span
                    class="badge rounded-pill text-bg-primary"><?php echo number_format($dataProvider->getTotalCount(), 0) ?></span>
                รายการ
            </h6>
            <?php if(isset($list)):?>
            <?=Html::a('แสดงทั้งหมด',['/me/documents'],['class' => 'btn btn-sm btn-light rounded-pill','data' => ['pjax' => 0]])?>
            <?php endif;?>
        </div>
    
 <table class="table table-striped table-fixed">
                    <thead>
                        <tr>
                            <th class="text-center fw-semibold" style="width:50px;">ลำดับ</th>
                            <th class="text-center fw-semibold" style="min-width:100px; width:100px;">เลขที่รับ</th>
                            <th class="fw-semibold" style="min-width:320px;">เรื่อง</th>
                            <th class="fw-semibold" style="min-width:250px;">ผู้บันทึก</th>
                            <th class="fw-semibold" style="min-width:130px;">สถานะ</th>
                            <th class="fw-semibold" style="width:120px;">ลงความเห็น</th>
                        </tr>
                    </thead>
                    <tbody class="align-middle  table-group-divider table-hover">
                        <?php foreach($dataProvider->getModels() as $key => $item):?>
                        <td class="text-center fw-semibold"><?php echo (($dataProvider->pagination->offset + 1)+$key)?></td>
                        <td class="text-center fw-semibold">
                            <?php echo $item->doc_regis_number?>
                        </td>
                        <td class="fw-light align-middle">
                            <div>
                                <p class="text-primary fw-semibold fs-13 mb-0">
                                </p>
                                <p style="width:600px" class="text-truncate fw-semibold fs-6 mb-0">
                                    <?php if($item->doc_speed == 'ด่วนที่สุด'):?>
                                    <span class="badge text-bg-danger fs-13">
                                        <i class="fa-solid fa-circle-exclamation"></i> ด่วนที่สุด
                                    </span>
                                    <?php endif;?>

                                    <?php if($item->secret == 'ลับที่สุด'):?>
                                    <span class="badge text-bg-danger fs-13"><i class="fa-solid fa-lock"></i>
                                        ลับที่สุด
                                    </span>
                                    <?php endif;?>
                                    <a href="<?php echo Url::to(['/me/documents/view','id' => $item->documentDetail->id])?>">
                                        เรื่อง : <?php echo $item->topic?>
                                    </a>

                                    <?php echo $item->isFile() ? '<i class="fas fa-paperclip"></i>' : ''?>
                                </p>
                            </div>
                             <p class="fw-normal fs-13 mb-0">
                                <?=$item->data_json['des'] ?? ''?>
                                </p>
                            <?php // echo Html::img('@web/img/krut.png',['style' => 'width:20px']);?>
                            <span class="text-danger">
                                <?php echo $item->doc_number?>
                            </span>
                            <span class="text-primary fw-normal fs-13">
                                |
                                <i class="fa-solid fa-inbox"></i>
                                <?php  echo $item->documentOrg->title ?? '-';?>
                                <span class="badge rounded-pill badge-soft-secondary text-primary fw-lighter fs-13">
                                    <i class="fa-regular fa-eye"></i> <?php echo $item->viewCount()?>
                                </span>
                            </span>
                           

                            <?php echo $item->StackDocumentTags('comment')?>
                        </td>
                        <td class="fw-light align-middle">
                            <div class=" d-flex flex-column">
                                <?=$item->viewCreate()['avatar'];?>
                                <!-- <span class="fw-normal fs-6"><?php echo $item->viewReceiveDate()?></span>
                            <span class="fw-lighter fs-13"><?php echo isset($item->doc_time) ? '<i class="fa-solid fa-clock"></i> '.$item->doc_time : ''?></span> -->
                            </div>
                        </td>
                        <td> 
                              <?php echo Html::a(($item->documentDetail->bookmark('fs-3')['view']),['/me/documents/bookmark', 'id' => $item->documentDetail->id],['class' => 'bookmark','id' =>  $item->documentDetail->id])?>
                             <?php echo $item->documentStatus->title ?? '-'?></td>
                        <td><?php echo Html::a('<i class="fa-regular fa-pen-to-square fa-2x"></i>',['view', 'id' => $item->documentDetail->id])?>
                        </td>
                        <!-- <td> -->
                        <?php // echo Html::a(' <i class="fas fa-share fa-2x text-secondary"></i>',['/dms/documents/comment','id' => $item->id,'title' => '<i class="fas fa-share"></i>ส่งต่อ'],['class' => 'open-modal','data' => ['size' => 'modal-md']])?>
                        <!-- </td> -->
                        </tr>
                        <?php endforeach;?>

                    </tbody>
                </table>

                <div class="d-flex justify-content-center">

    <div class="text-muted">
        <?= LinkPager::widget([
            'pagination' => $dataProvider->pagination,
            'firstPageLabel' => 'หน้าแรก',
            'lastPageLabel' => 'หน้าสุดท้าย',
            'options' => [
                'listOptions' => 'pagination pagination-sm',
                'class' => 'pagination-sm',
            ],
        ]); ?>
    </div>
</div>
    </div>
</div>
<?php
$js = <<< JS

     
            \$("body").on("click", ".bookmark", function (e) {
                e.preventDefault();
                var title = \$(this).data('title')
                console.log('update commetn');
                 \$.ajax({
                    type: "get",
                    url: \$(this).attr('href'),
                    dataType: "json",
                    success: async function (res) { 
                        console.log(res.data);
                        if(res.data.bookmark == 'Y'){
                            $('#'+res.data.id).html('<i class="fa-solid fa-star text-warning fs-4"></i>')
                            success('ติดดาว')  
                        }
                        
                        if(res.data.bookmark == 'N'){
                             $('#'+res.data.id).html('<i class="fa-regular fa-star fs-4"></i>')
                            success('ยกเลิกติดดาว')  
                        }
                        // location.reload();
                    }
                });
            });

JS;
$this->registerJS($js);

?>