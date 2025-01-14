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
$this->params['breadcrumbs'][] = ['label' => 'Documents', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-journal-text fs-4"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php  echo $this->render('@app/modules/dms/menu') ?>
<?php $this->endBlock(); ?>
<?php Pjax::begin(['id' => 'document','timeout' => 80000]); ?>

                <!-- Tab panes -->
                <div class="row">
                    <div class="col-8">
                    <iframe id="myIframe" src="<?= Url::to(['/dms/documents/show','ref' => $model->ref]);?>&embedded=true" frameborder="0" style="width: 100%; height: 900px; border: none;"></iframe>
                    <?php  echo Html::button('<i class="fa-solid fa-chevron-left"></i> ย้อนกลับ', ['class' => 'btn btn-secondary me-2','onclick' => 'window.history.back()',]);?>
                       
                    <?php // echo $this->render('history',['model' => $model])?>
                    <?php // echo $this->render('track',['model' => $model])?>
 
                    </div>
                    <div class="col-4 py-2">
                    <h6><i class="fa-regular fa-comments fs-2"></i> การลงความเห็น</h6>
                       <div class="listComment"></div>
                       <div class="viewFormComment"></div>
                        <?php // echo $this->render('_form_comment',['model'=> $modelComment]);?>
                    </div>
                </div>
            </div>

<?php
$getCommentUrl = Url::to(['/dms/documents/comment','id' => $model->id]);
$listCommentUrl = Url::to(['/dms/documents/list-comment','id' => $model->id]);
$js = <<< JS
    getComment();
    listComment()

    // ดึงค่าความสูงของหน้าจอ
    const screenHeight = window.innerHeight;

  
    console.log(screenHeight); // แสดงค่าความสูงของหน้าจอ

    const iframe = document.getElementById("myIframe");
    iframe.style.height = screenHeight - 100 + "px";

    // iframe.onload = () => {
    // const iframeDocument = iframe.contentDocument || iframe.contentWindow.document;
    // if (iframeDocument) {
    //     iframeDocument.body.style.zoom = "150%";
    // }
    // };

    async function getComment()
    {
        await $.ajax({
            type: "get",
            url: "$getCommentUrl",
            dataType: "json",
            success: async function (res) {
                $('.viewFormComment').html(res.content)
            }
        });
    }

    async function listComment()
    {
     
        await $.ajax({
            type: "get",
            url: "$listCommentUrl",
            dataType: "json",
            success: async function (res) {
                $('.listComment').html(res.content)
            }
        });
    }
    
    $("body").on("click", ".update-comment", function (e) {
        e.preventDefault();
        console.log('update commetn');
         $.ajax({
            type: "get",
            url: $(this).attr('href'),
            dataType: "json",
            success: async function (res) {
                $('.viewFormComment').html(res.content)       
            }
        });
    });

    $("body").on("click", ".delete-comment", function (e) {
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
            $.ajax({
                type: "post",
                url: $(this).attr('href'),
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
<?php Pjax::end(); ?>