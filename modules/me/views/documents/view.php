<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\widgets\DetailView;
use app\components\UserHelper;

/** @var yii\web\View $this */
/** @var app\modules\dms\models\Documents $model */
$this->title = $model->topic;
\yii\web\YiiAsset::register($this);
?>

<?php $this->beginBlock('page-title'); ?>

<div class="container-fluid">
    <div class=" d-flex flex-column" style="max-width:1000px">
        <div>
            <p class="text-truncate fw-semibold fs-5 mb-0">
                <?php if ($model->doc_speed == 'ด่วนที่สุด'): ?>
                <span class="badge text-bg-danger fs-13">
                    <i class="fa-solid fa-circle-exclamation"></i> ด่วนที่สุด
                </span>
                <?php endif; ?>

                <?php if ($model->secret == 'ลับที่สุด'): ?>
                <span class="badge text-bg-danger fs-13"><i class="fa-solid fa-lock"></i>
                    ลับที่สุด
                </span>
                <?php endif; ?>
                <?php echo $model->topic ?>
            </p>
            <span class="fs-6">เลขรับ</span> : <span
                class="fw-medium"><?php echo $model->doc_regis_number ?></span>

            <span class="fs-6">เลขหนังสือ</span> : <span
                class="fs-6 fw-medium"><?php echo $model->doc_number ?></span>
            <span class="fs-6">จากหน่วยงาน</span> : <span class="text-primary fw-normal fs-13">
                <i class="fa-solid fa-inbox"></i>
                <?php echo $modelOrg->title ?? '-'; ?>
                <span class="badge rounded-pill badge-soft-secondary text-primary fw-lighter fs-13">
                    <i class="fa-regular fa-eye"></i> <?php echo $model->viewCount() ?>
                </span>
            </span>

        </div>

    </div>

    <?php $this->endBlock(); ?>
    <?php $this->beginBlock('page-action'); ?>
    <?php echo $this->render('menu') ?>
    <?php $this->endBlock(); ?>

    <div class="row">
        <div class="col-8">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-end mb-2">
                    <?php echo Html::a(($detail->bookmark == 'Y' ? '<i class="fa-solid fa-star text-warning fs-2"></i>' : '<i class="fa-regular fa-star fs-2"></i>'),['/me/documents/bookmark', 'id' => $model->id],['class' => 'bookmark'])?>
                    </div>
                    <iframe id="myIframe"
                        src="<?= Url::to(['/dms/documents/show', 'ref' => $model->ref]); ?>&embedded=true"
                        frameborder="0" style="width: 100%; height: 500px; border: none;"></iframe>

                </div>
            </div>

        </div>
        <div class="col-4 py-2">
            <div class="card">
                <div class="card-body">

                    <!-- Nav pills -->
                    <div class="d-flex justify-content-between">
                        <?php echo Html::a('<i class="fa-solid fa-chevron-left"></i> ย้อนกลับ', ['/me/documents'], ['class' => 'btn btn-secondary me-2']) ?>
                        <ul class="nav nav-pills" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="pill" href="#home">ลงความเห็น</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="pill" href="#menu1">ประวัติการอ่าน</a>
                            </li>
                        </ul>
                    </div>

                    <!-- Tab panes -->
                    <div class="tab-content mt-3">
                        <div id="home" class="container tab-pane active pb-4">
                            <div class="viewFormComment"></div>
                            <div class="listComment"></div>
                        </div>
                        <div id="menu1" class="container tab-pane fade"><br>
                            <?php echo $this->render('@app/modules/dms/views/documents/history', ['model' => $model]) ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>


        <?php // echo $this->render('_form_comment',['model'=> $modelComment]); ?>


        <?php // echo $this->render('track',['model' => $model]) ?>

    </div>




<?php
    // $getCommentUrl = Url::to(['/dms/documents/comment','id' => $model->id]);
    // $listCommentUrl = Url::to(['/dms/documents/list-comment','id' => $model->id]);

$getCommentUrl = Url::to(['/me/documents/comment', 'id' => $model->id]);
$listCommentUrl = Url::to(['/me/documents/list-comment', 'id' => $model->id]);
$js = <<<JS

            getComment();
            listComment()

            const iframe = document.getElementById("myIframe");
              // ดึงค่าความสูงของหน้าจอ
              const screenHeight = window.innerHeight;
                iframe.style.height = screenHeight - 100 + "px";


            // iframe.onload = () => {
            // const iframeDocument = iframe.contentDocument || iframe.contentWindow.document;
            // if (iframeDocument) {
            //     iframeDocument.body.style.zoom = "150%";
            // }
            // };

            async function getComment()
            {
                await \$.ajax({
                    type: "get",
                    url: "$getCommentUrl",
                    dataType: "json",
                    success: async function (res) {
                        \$('.viewFormComment').html(res.content)
                    }
                });
            }

            async function listComment()
            {
             
                await \$.ajax({
                    type: "get",
                    url: "$listCommentUrl",
                    dataType: "json",
                    success: async function (res) {
                        \$('.listComment').html(res.content)
                    }
                });
            }
            

                    
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
                            $('.bookmark').html('<i class="fa-solid fa-star text-warning fs-2"></i>')
                            success('ติดดาว')  
                        }
                        
                        if(res.data.bookmark == 'N'){
                            $('.bookmark').html('<i class="fa-regular fa-star fs-2"></i>')
                            success('ยกเลิกติดดาว')  
                        }
                        // location.reload();
                    }
                });
            });
            
            \$("body").on("click", ".update-comment", function (e) {
                e.preventDefault();
                console.log('update commetn');
                 \$.ajax({
                    type: "get",
                    url: \$(this).attr('href'),
                    dataType: "json",
                    success: async function (res) {
                        \$('.viewFormComment').html(res.content)       
                    }
                });
            });

            \$("body").on("click", ".delete-comment", function (e) {
                e.preventDefault();
                Swal.fire({
                title: 'ยืนยัน',
                text: 'ต้องการลบหรือไม่',
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "ใช่, ยืนยัน!",
                cancelButtonText: "ยกเลิก",
            }).then(async (result) => {
                if(result.value == true)
                    \$.ajax({
                        type: "post",
                        url: \$(this).attr('href'),
                        dataType: "json",
                        success: async function (res) {
                            if(res.status == 'success'){
                                listComment()    
                                }else{
                                    warning()
                                }
                            }
                        });
                    }); 
            });
JS;
$this->registerJS($js);
?>
    <?php // Pjax::end(); ?>