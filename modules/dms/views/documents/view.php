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
 .section-title {
    border-left: 4px solid var(--bs-primary);
    padding: 10px;
    margin-bottom: 20px;
    color: #333;
    font-weight: 300;
    background-color: rgba(217, 230, 247, 0.5098039216);
}
/* เพิ่มสไตล์สำหรับจัดการความยาวข้อความในมือถือ */
.text-header-responsive {
    word-break: break-word;
    overflow-wrap: break-word;
}
</style>

<?php $this->beginBlock('page-title'); ?>
<div class="container-fluid">
    <div class="d-flex flex-column" style="max-width:1000px">
        <div class="text-header-responsive">
            <div class="d-flex flex-wrap gap-2 mb-2">
                <?php if($model->doc_speed == 'ด่วนที่สุด'):?>
                <span class="badge text-bg-danger fs-13">
                    <i class="fa-solid fa-circle-exclamation"></i> ด่วนที่สุด
                </span>
                <?php endif;?>

                <?php if($model->secret == 'ลับที่สุด'):?>
                <span class="badge text-bg-danger fs-13"><i class="fa-solid fa-lock"></i>
                    ลับที่สุด
                </span>
                <?php endif;?>
            </div>
            
            <p class="fs-5 mb-2 fw-bold text-dark">
                <?php echo $model->topic?>
            </p>

            <div class="row g-2 fs-13">
                <div class="col-auto">
                    <span class="text-muted">เลขรับ:</span> <span class="fw-medium"><?php echo $model->doc_regis_number?></span>
                </div>
                <div class="col-auto">
                    <span class="text-muted">เลขหนังสือ:</span> <span class="fw-medium"><?php echo $model->doc_number?></span>
                </div>
                <div class="col-12 col-md-auto">
                    <span class="text-muted">จากหน่วยงาน:</span> 
                    <span class="text-primary">
                        <i class="fa-solid fa-inbox"></i>
                        <?php echo $model->documentOrg->title ?? '-';?>
                    </span>
                    <span class="badge rounded-pill badge-soft-secondary text-primary ms-1">
                        <i class="fa-regular fa-eye"></i> <?php echo $model->viewCount()?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->endBlock(); ?>

<div class="row g-3">
    <div class="col-12 col-lg-7">
        <div class="card h-100 shadow-sm">
            <iframe id="myIframe" src="<?= Url::to(['/dms/documents/show','ref' => $model->ref]);?>&embedded=true"
                frameborder="0" style="width: 100%; border: none; min-height: 400px; border-radius: 8px;"></iframe>
        </div>
    </div>

    <div class="col-12 col-lg-5">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">

                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-4 gap-3">
                    <ul class="nav nav-pills" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="pill" href="#home">ลงความเห็น</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="pill" href="#menu1">ประวัติการอ่าน</a>
                        </li>
                    </ul>
                    <?php echo Html::a('<i class="fa-regular fa-pen-to-square"></i> แก้ไข',['/dms/documents/update','id' => $model->id],['class' => 'btn btn-warning open-modal rounded-pill shadow-sm w-100 w-sm-auto','data' => ['size' => 'modal-xxl']])?>
                </div>

                <div class="mb-3">
                    <h5 class="section-title fs-6"><?=$model->data_json['des'] ?? 'รายละเอียด'?></h5>
                    <div class="text-muted fs-13">
                        <i class="fa-solid fa-users"></i> ถึงหน่วยงาน : <span class="text-dark fw-medium"><?=$model->viewTagsDepartment()?></span>
                    </div>
                </div>

                <div class="tab-content mt-3">
                    <div id="home" class="container tab-pane active pb-4 px-0">
                        <div class="listComment mb-3"></div>
                        <hr>
                        <div class="viewFormComment"></div>
                    </div>
                    <div id="menu1" class="container tab-pane fade px-0"><br>
                        <?php echo $this->render('history',['model' => $model])?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php
$getCommentUrl = Url::to(['/dms/documents/comment','id' => $model->id]);
$listCommentUrl = Url::to(['/dms/documents/list-comment','id' => $model->id]);

$js = <<< JS
(function(){
    $(function(){
        // ฟังก์ชันปรับความสูง Iframe แบบ Responsive
        function resizeIframe() {
            let iframe = document.getElementById("myIframe");
            if (iframe) {
                let screenHeight = window.innerHeight;
                let screenWidth = window.innerWidth;
                
                if (screenWidth < 992) { // ขนาด Mobile/Tablet
                    iframe.style.height = "550px";
                } else { // ขนาด Desktop
                    iframe.style.height = (screenHeight - 180) + "px";
                }
            }
        }

        // เรียกใช้งานตอนโหลดหน้าและตอนเปลี่ยนขนาดจอ
        resizeIframe();
        $(window).on('resize', resizeIframe);

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
            $('.viewFormComment').html(res.content)
        }
    });
}

async function listComment() {
    await $.ajax({
        type: "get",
        url: "$listCommentUrl",
        dataType: "json",
        success: function (res) {
            $('.listComment').html(res.content)
        }
    });
}

// ... ส่วน Delete และ Update Comment คงเดิม ...
JS;
$this->registerJS($js, View::POS_END);
?>