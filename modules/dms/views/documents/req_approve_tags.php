<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\widgets\DetailView;
?>
<div class="card border">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h6><i class="fa-solid fa-user-pen"></i> เสนอผู้อำนวยการ</h6>
        </div>

        <?php Pjax::begin(['id' => 'document-tag']); ?>
        <?php echo $model->StackDocumentTags('req_approve')?>
        <?php Pjax::end(); ?>
    </div>
    <div class="card-footer d-flex justify-content-end">
        <?= Html::a('<i class="fa-solid fa-file-circle-check"></i> เสนอ', ['/dms/document-tags/req-approve'], ['class' => 'btn btn-sm btn-primary rounded-pill req-approve', 'data' => ['document_id' => $model->id,'ref' => $model->ref, 'name' => 'req_approve']]) ?>
    </div>
</div>

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
