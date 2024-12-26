<?php

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
<div class="card">
    <div class="card-body">
        <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
                <i class="bi bi-journal-text fs-2"></i>
            </div>
            <div class="flex-grow-1 ms-3">
                <div class="d-flex flex-column">
                    <div>
                        <span class="h5">
                            <?= Html::encode($this->title) ?>
                        </span>
                        <span class="fw-semibold fs-6">
                            <?php if($model->doc_speed == 'ด่วนที่สุด'):?>
                            <span class="badge text-bg-danger fs-13"><i class="fa-solid fa-circle-exclamation"></i>
                                ด่วนที่สุด</span>
                            <?php endif;?>

                            <?php if($model->doc_speed == 'ด่วน'):?>
                            <span class="badge text-bg-waring fs-13"><i class="fa-solid fa-circle-exclamation"></i>
                                ด่วน</span>
                            <?php endif;?>

                    </div>
                    <span class="text-primary">
                        <?php  echo $model->documentOrg->title ?? '-';?>
                    </span>
                </div>
            </div>
        </div>

        <!-- <span class="badge rounded-pill badge-soft-secondary text-primary fw-lighter fs-13"> -->
        <span class="text-primary fw-normal fs-13">
            <div class="border border-secondary border-opacity-25 p-3 rounded">

                <!-- Tab panes -->
                <div class="row">
                    <div class="col-8">
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
                                <iframe src="<?= Url::to(['/dms/documents/show','id' => $model->id]);?>&embedded=true"
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
             <div class="mt-5">
   
                        <?= DetailView::widget([
                            'model' => $model,
                            'attributes' => [
                                'doc_number',
                                
                                [
                                    'label' => 'วันที่หนังสือ',
                                    'value' => $model->documentType->title ?? '-'
                                ],
                                [
                                    'label' => 'หน่วยงาน',
                                    'value' => $model->documentOrg->title ?? '-'
                                ],
                                [
                                    'attribute' => 'document_type',
                                    'value' => $model->documentType->title ?? '-'
                                ],

                                'thai_year',
                                'doc_regis_number',
                                'urgent',
                                'secret',
                            ],
                        ]) ?>
                                     
             </div>
                       
                        <?php echo $this->render('req_approve_tags',['model' => $model])?>
                    <?php echo $this->render('employee_tags',['model' => $model])?>
                    <p>
                            <?php echo Html::a('<i class="fa-regular fa-pen-to-square"></i> แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                            <?php echo Html::a('<i class="fa-solid fa-trash-can"></i> ลบ', ['delete', 'id' => $model->id], [
                                'class' => 'btn btn-danger',
                                'data' => [
                                    'confirm' => 'Are you sure you want to delete this item?',
                                    'method' => 'post',
                                ],
                            ]) ?>
                        </p>


                    </div>

                </div>







            </div>

    </div>
</div>