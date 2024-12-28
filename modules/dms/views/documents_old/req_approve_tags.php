<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\widgets\DetailView;
?>
    <?php Pjax::begin(['id' => 'document-tag']); ?>
<div class="card border">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h6><i class="fa-solid fa-user-pen"></i> เสนอผู้อำนวยการ</h6>
        </div>
        <div class="d-flex justify-content-between">
            <?php echo $model->StackDocumentTags('req_approve')?>
          <?php 
          try {
            echo $model->documentApprove()['data_json']['comment'];
          } catch (\Throwable $th) {
            //throw $th;
          }
          ?>
        </div>
    
    </div>

    <div class="card-footer">

    <?php if($model->status == 'DS1'):?>
        <?= Html::a('<i class="fa-solid fa-file-signature"></i> เสนอผู้อำนวนการ', ['/dms/document-tags/req-approve'], ['class' => 'btn btn-sm btn-primary rounded-pill req-approve float-end', 'data' => ['document_id' => $model->id,'ref' => $model->ref, 'name' => 'req_approve']]) ?>
    <?php endif;?>
    <?php if($model->status == 'DS3'):?>
            <?= Html::a('<i class="fa-regular fa-pen-to-square"></i> ลงความเห็น', ['/dms/document-tags/comment','id' => $model->id,'tilte' => '<i class="fa-regular fa-pen-to-square"></i> ลงความเห็น'], ['class' => 'btn btn-sm btn-warning rounded-pill open-modal float-end']) ?>
            <?php endif;?>
            
     
            <?php if($model->status == 'DS4'):?>
                <div class="d-flex justify-content-between">
                    <span>
                    <i class="fa-solid fa-calendar-check"></i>
                    <?php

                    try {
                        echo Yii::$app->thaiFormatter->asDateTime($model->documentApprove()['data_json']['comment_date'], 'medium');
                    } catch (\Throwable $th) {
                        //throw $th;
                    }
                    ?>
                    </span>
                    <span class="badge rounded-pill badge-soft-primary text-primary fs-13 "><i class="fa-solid fa-circle-check"></i> ผอ.ลงนามแล้ว</span>
                </div>
            <?php endif;?>
    </div>
    
</div>
<?php Pjax::end(); ?>

<?php
$js = <<< JS
$('.req-approve').click(function (e) { 
e.preventDefault()
    Swal.fire({
            title: 'ยืนยัน',
            html:'<i class="fa-solid fa-file-circle-check fs-1"></i> นำเสนอผู้อำนวยการ',
            icon: "info",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            cancelButtonText: "<i class='bi bi-x-circle'></i> ยกเลิก",
            confirmButtonText: "<i class='bi bi-check-circle'></i> ยืนยัน"
            }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "post",
                    url: $(this).attr('href'),
                    beforeSend : function(){
                    beforLoadModal()
                },
                data:{
                    name:$(this).data('name'),
                    document_id:$(this).data('document_id'),
                    ref:$(this).data('ref'),
                },
                dataType: "json",
                success: function (res) {
                    // $("#main-modal").modal("toggle");
                    console.log(res.status);
                    
                    if(res.status == 'error') {
                        warning()
                    }
                    if(res.status == 'success') {
                        window.location.reload(true);
                        //  $.pjax.reload({ container:res.container, history:false,replace: false,timeout: false});
                    }
                }
            });
            }
            });
        
    });


JS;
$this->registerJS($js, View::POS_END);
?>
