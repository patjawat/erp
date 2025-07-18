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
<i class="fa-solid fa-download"></i></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>


<?php $this->beginBlock('page-title'); ?>

<div class="container-fluid">
    <div class=" d-flex flex-column" style="max-width:1000px">
        <div>
            <p class="text-truncate fw-semibold fs-5 mb-0">
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
                <?php echo $model->topic?>
            </p>
            <span class="fs-6">เลขรับ</span> : <span class="fw-medium"><?php echo $model->doc_regis_number?></span>

            <span class="fs-6">เลขหนังสือ</span> : <span class="fs-6 fw-medium"><?php echo $model->doc_number?></span>
            <span class="fs-6">จากหน่วยงาน</span> : <span class="text-primary fw-normal fs-13">
                <i class="fa-solid fa-inbox"></i>
                <?php  echo $model->documentOrg->title ?? '-';?>
                <span class="badge rounded-pill badge-soft-secondary text-primary fw-lighter fs-13">
                    <i class="fa-regular fa-eye"></i> <?php echo $model->viewCount()?>
                </span>
            </span>
        </div>
    </div>
</div>

<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php  // Pjax::begin(['id' => 'document','timeout' => 80000]); ?>
<!-- Tab panes -->


<div class="row">
    <div class="col-8">
        <iframe id="myIframe" src="<?= Url::to(['/dms/documents/show','ref' => $model->ref]);?>&embedded=true"
            frameborder="0" style="width: 100%; height: 500px; border: none;"></iframe>
    </div>
    <div class="col-4 py-2">
        <div class="card">
            <div class="card-body">

        <!-- Nav pills -->
        <div class="d-flex justify-content-between">
            <?php echo Html::a('<i class="fa-solid fa-chevron-left"></i> ย้อนกลับ',['/dms/documents/'.$model->document_group],['class' => 'btn btn-secondary me-2'])?>
            <ul class="nav nav-pills" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="pill" href="#home">กลุ่ม/ทีมประสาน</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="pill" href="#menu1">ประวัติการอ่าน</a>
                </li>
            </ul>
        </div>

        <!-- Tab panes -->
        <div class="tab-content mt-3">
            <div id="home" class="container tab-pane active pb-4">
                <div class="list_commitee"><?php echo $this->render('list_committee',['model' => $model])?></div>
            </div>
            <div id="menu1" class="container tab-pane fade"><br>
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

    const iframe = document.getElementById("myIframe");
    const screenHeight = window.innerHeight; iframe.style.height = screenHeight - 100 + "px";
    
JS;
$this->registerJS($js,View::POS_END);
?>
<?php //  Pjax::end(); ?>
