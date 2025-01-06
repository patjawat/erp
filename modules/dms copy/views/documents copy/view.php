<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\widgets\DetailView;

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
<div class="card">
    <div class="card-body">
        

        <!-- <span class="badge rounded-pill badge-soft-secondary text-primary fw-lighter fs-13"> -->
        <span class="text-primary fw-normal fs-13">
            <div class="border border-secondary border-opacity-25 p-3 rounded">

                <!-- Tab panes -->
                <div class="row">
                    <div class="col-8">
                  <?php echo $model->ref;?>

                        <div class="d-flex justify-content-between">
                            <!-- Nav tabs -->
                            <ul class="nav nav-tabs" role="pillist" style="visibility: visible;">
                                <?php echo Html::button('<i class="fa-solid fa-chevron-left"></i> ย้อนกลับ', ['class' => 'btn btn-secondary me-2','onclick' => 'window.history.back()',]);?>
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link active show" data-bs-toggle="pill" href="#home1" role="pill"
                                        aria-selected="false" tabindex="-1"><i class="fas fa-fw fa-info-circle"></i>
                                        รายละเอียด</a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link" data-bs-toggle="pill" href="#track" role="pill"
                                        aria-selected="false" tabindex="-1"><i class="fas fa-fw fa-binoculars"></i>
                                        การติดตาม</a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link" data-bs-toggle="pill" href="#history" role="pill"
                                        aria-selected="true" tabindex="-1"><i class="fas fa-fw fa-history"></i>
                                        ประวัติ</a>
                                </li>
                            </ul>

                        </div>
                        <div class="tab-content p-0">
                            <div id="home1" class="tab-pane active show" role="tabpanel">
                            <iframe src="<?= Url::to(['/dms/documents/show','ref' => $model->ref]);?>&embedded=true"
                            width='100%' height='1000px' frameborder="0"></iframe>
                            </div>
                            <div id="track" class="tab-pane" role="tabpanel">
                                <?php echo $this->render('track',['model' => $model])?>
                            </div>
                            <div id="history" class="tab-pane" role="tabpanel">
                                <?php echo $this->render('history',['model' => $model])?>
                            </div>
                        </div>
                    </div>

                    <div class="col-4 py-2">
                    
                       <div class="listComment"></div>
                       <div class="viewFormComment"></div>
                    </div>

                </div>







            </div>

    </div>
</div>


<?php
$getCommentUrl = Url::to(['/dms/documents/comment','id' => $model->id]);
$listCommentUrl = Url::to(['/dms/documents/list-comment','id' => $model->id]);
$js = <<< JS
    getComment();
    listComment()
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



JS;
$this->registerJS($js);
?>