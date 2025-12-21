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

<style>
    /* ปรับแต่งสไตล์เพิ่มเติม */
    .section-title {
        border-left: 4px solid var(--bs-primary);
        padding: 5px 10px;
        margin-bottom: 15px;
        background-color: #f8f9fa;
        font-size: 1.1rem;
    }
    .doc-header-info span { display: inline-block; }
</style>

<?php $this->beginBlock('page-title'); ?>
<div class="container-fluid">
    <div class="d-flex flex-column" style="max-width:1000px">
        <div class="doc-header-info">
            <p class="fs-5 mb-2 fw-bold text-dark text-wrap">
                <?php if ($model->doc_speed == 'ด่วนที่สุด'): ?>
                    <span class="badge text-bg-danger fs-13 mb-1"><i class="fa-solid fa-circle-exclamation"></i> ด่วนที่สุด</span>
                <?php endif; ?>

                <?php if ($model->secret == 'ลับที่สุด'): ?>
                    <span class="badge text-bg-danger fs-13 mb-1"><i class="fa-solid fa-lock"></i> ลับที่สุด</span>
                <?php endif; ?>
                <br class="d-block d-sm-none"> <?php echo Html::encode($model->topic) ?>
            </p>
            
            <div class="d-flex flex-wrap gap-2 fs-14">
                <span class="text-muted">เลขรับ : <span class="fw-medium text-dark"><?= $model->doc_regis_number ?></span></span>
                <span class="text-muted">| เลขหนังสือ : <span class="fw-medium text-dark"><?= $model->doc_number ?></span></span>
                <span class="text-muted">| จาก : 
                    <span class="text-primary fw-normal">
                        <i class="fa-solid fa-inbox"></i> <?= $modelOrg->title ?? '-' ?>
                    </span>
                    <span class="badge rounded-pill badge-soft-secondary text-primary ms-1">
                        <i class="fa-regular fa-eye"></i> <?= $model->viewCount() ?>
                    </span>
                </span>
            </div>
        </div>
    </div>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<div class="d-flex gap-2">
    <?php echo $this->render('menu') ?>
</div>
<?php $this->endBlock(); ?>

<div class="row g-3">
    <div class="col-12 col-lg-8">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body p-2 p-md-3">
                <div class="d-flex justify-content-end mb-2">
                    <?php echo Html::a(($detail->docRead('fs-3')['view']),['/me/documents/bookmark', 'id' => $detail->id],['class' => 'bookmark bookmark-star-'. $detail->id,'id' => $detail->id])?>
                </div>
                <div class="ratio ratio-16x9" id="iframeWrapper" style="min-height: 450px;">
                    <iframe id="myIframe"
                        src="<?= Url::to(['/me/documents/show', 'ref' => $model->ref]); ?>&embedded=true"
                        frameborder="0" style="width: 100%; border: none; border-radius: 8px;"></iframe>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                    <ul class="nav nav-pills nav-custom-sm" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="pill" href="#home">ลงความเห็น</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="pill" href="#menu1">ประวัติการอ่าน</a>
                        </li>
                    </ul>
                </div>

                <div class="tab-content mt-2">
                    <div id="home" class="tab-pane active pb-4">
                        <h5 class="section-title"><?= $model->data_json['des'] ?? 'รายละเอียด' ?></h5>
                        <p class="fs-13 text-muted mb-3"><i class="fa-solid fa-users"></i> ถึงหน่วยงาน: <span class="text-dark fw-medium"><?= $model->viewTagsDepartment() ?></span></p>
                        
                        <div class="listComment mb-3"></div>
                        <div class="viewFormComment border-top pt-3"></div>
                    </div>
                    
                    <div id="menu1" class="tab-pane fade">
                        <div class="py-2">
                            <?php echo $this->render('@app/modules/dms/views/documents/history', ['model' => $model]) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$getCommentUrl = Url::to(['/me/documents/comment', 'id' => $model->id]);
$listCommentUrl = Url::to(['/me/documents/list-comment', 'id' => $model->id]);

$js = <<<JS
(function(){
    $(function(){
        // ฟังก์ชันปรับความสูง Iframe ตามขนาดหน้าจอ
        function updateIframeHeight() {
            let iframe = document.getElementById("myIframe");
            if (iframe) {
                let winHeight = window.innerHeight;
                let winWidth = window.innerWidth;
                
                if (winWidth < 992) {
                    // มือถือ/แท็บเล็ตแนวตั้ง
                    iframe.style.height = "500px";
                } else {
                    // จอคอมพิวเตอร์
                    iframe.style.height = (winHeight - 200) + "px";
                }
            }
        }

        updateIframeHeight();
        $(window).on('resize', updateIframeHeight);

        getComment();
        listComment();
    });
})();

async function getComment() {
    await $.ajax({
        type: "get",
        url: "$getCommentUrl",
        dataType: "json",
        success: function (res) {
            $('.viewFormComment').html(res.content);
        }
    });
}

async function listComment() {
    await $.ajax({
        type: "get",
        url: "$listCommentUrl",
        dataType: "json",
        success: function (res) {
            $('.listComment').html(res.content);
        }
    });
}

// Event delegation สำหรับการทำงานใน AJAX content
$("body").on("click", ".update-comment", function (e) {
    e.preventDefault();
    $.ajax({
        type: "get",
        url: $(this).attr('href'),
        dataType: "json",
        success: function (res) {
            $('.viewFormComment').html(res.content);
            // Scroll down ไปที่ฟอร์มแก้ไขในมือถือ
            if($(window).width() < 992) {
                $('html, body').animate({ scrollTop: $('.viewFormComment').offset().top - 100 }, 500);
            }
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
        confirmButtonText: "ใช่, ยืนยัน!",
        cancelButtonText: "ยกเลิก",
    }).then((result) => {
        if(result.isConfirmed) {
            $.ajax({
                type: "post",
                url: $(this).attr('href'),
                dataType: "json",
                success: function (res) {
                    if(res.status == 'success'){
                        listComment();
                    }
                }
            });
        }
    }); 
});
JS;
$this->registerJS($js, View::POS_END);
?>