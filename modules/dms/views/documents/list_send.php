<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\components\AppHelper;
use app\modules\dms\models\Documents;
/** @var yii\web\View $this */
/** @var app\modules\dms\models\DocumentSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
    $this->title = 'หนังสือส่ง';

$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<?php if($searchModel->document_group == 'receive'):?>
<i class="fa-solid fa-download"></i></i> หนังสือ<?= $this->title; ?>
<?php endif; ?>
<?php if($searchModel->document_group == 'send'):?>
<i class="fa-solid fa-paper-plane text-danger"></i></i> หนังสือ<?= $this->title; ?>
<?php endif; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php  echo $this->render('@app/modules/dms/menu',['model' =>$searchModel]) ?>
<?php $this->endBlock(); ?>

<?php // Pjax::begin(['id' => 'document','timeout' => 80000]); ?>

<div class="documents-index">

    <div class="card">
        <div class="card-body">

            <div class="d-flex justify-content-between align-top align-items-center">
                <h6>
                    <i class="bi bi-ui-checks"></i> ทะเบียน<?php echo $this->title?>
                    <span
                        class="badge rounded-pill text-bg-primary"><?php echo number_format($dataProvider->getTotalCount(), 0) ?></span>
                    รายการ
                </h6>
            </div>
            <div class="d-flex justify-content-between align-top align-items-center">

                <?php  echo $this->render('@app/modules/dms/views/documents/_search', ['model' => $searchModel]); ?>
                    <?= Html::a('<i class="fa-solid fa-plus"></i> ออกเลข'.$this->title, ['/dms/documents/create','document_group' => $searchModel->document_group, 'title' => '<i class="fa-solid fa-calendar-plus"></i> บันทึกขออนุมัติการลา'], ['class' => 'btn btn-primary shadow rounded-pill', 'data' => ['size' => 'modal-lg']]) ?>
            </div>


                
            <div class="table-responsive">

                <table class="table table-striped table-fixed">
                    <thead>
                        <tr>
                            <th style="width:150px;" class="fw-semibold">เลขส่ง</th>
                            <th class="fw-semibold">เรื่อง</th>
                            <th class="fw-semibold" style="width:650px;">ลงความเห็น</th>
                            <th class="fw-semibold" style="width:1000px;">วันที่ส่ง</th>
                            <th class="fw-semibold text-center" style="width:400px;">สถานะ</th>
                            <th class="fw-semibold">แก้ไข</th>
                            <!-- <th class="fw-semibold" style="width:150px;">ส่งต่อ</th> -->
                        </tr>
                    </thead>
                    <tbody class="align-middle  table-group-divider table-hover">
                        <?php foreach($dataProvider->getModels() as $item):?>
                        <tr class="" style="max-width:200px">
                            <td class="fw-semibold"><?php echo $item->doc_regis_number?></td>

                            <td class="fw-light align-middle">
                                <a href="<?php echo Url::to(['/dms/documents/view','id' => $item->id])?>"
                                    class="text-dark open-modal-fullscree-xn">
                                    <div class=" d-flex flex-column" style="max-width:1000px">
                                        <div>
                                            <p class="text-truncate fw-semibold fs-6 mb-0">
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
                                                <?php echo $item->topic?> <?php echo $item->isFile() ? '<i class="fas fa-paperclip"></i>' : ''?>
                                            </p>

                                        </div>
                                    </div>
                                    </a>
                                    <span class="text-primary fw-normal fs-13">
                                        <i class="fa-solid fa-inbox"></i>
                                            <?php  echo $item->documentOrg->title ?? '-';?>
                                        <span class="badge rounded-pill badge-soft-secondary text-primary fw-lighter fs-13">
                                            <i class="fa-regular fa-eye"></i> <?php echo $item->viewCount()?>
                                        </span>
                                    </span>
                                                <?php if($item->countStackDocumentTags() >= 1):?>
                                                    <?php
                                                        echo Html::a('<i class="fa-solid fa-tags"></i> '.$item->countStackDocumentTags(),
                                                            ['/dms/documents/list-comment', 'id' => $item->id,'title' => '<i class="fa-regular fa-comments fs-2"></i> การลงความเห็น'],
                                                            [
                                                                'class' => 'open-modal badge rounded-pill badge-soft-primary text-primary fw-lighter fs-13',
                                                                'data' => [
                                                                    'size' => 'modal-md',
                                                                    'bs-trigger' => 'hover focus',
                                                                    'bs-toggle' => 'popover',
                                                                    'bs-placement' => 'top',
                                                                    'bs-title' => '<i class="fa-solid fa-tags"></i> ส่งต่อ',
                                                                    'bs-html' => 'true',
                                                                    'bs-content' => $item->StackDocumentTags('employee')
                                                                ]
                                                            ]
                                                        );
                                                        ?>
                                
                                        <?php endif?>
                                   
                              
                            </td>
                            <td>
                                <?php echo $item->StackDocumentTags('comment')?>
                            </td>
                            <td class="fw-light align-middle">
                                <div class=" d-flex flex-column">
                                    <?php
                             echo $item->viewCreate()['avatar'];
                            ?>
                                    <!-- <span class="fw-normal fs-6"><?php echo $item->viewReceiveDate()?></span>
                            <span class="fw-lighter fs-13"><?php echo isset($item->doc_time) ? '<i class="fa-solid fa-clock"></i> '.$item->doc_time : ''?></span> -->
                                </div>
                            </td>
                            <td class="text-center">
                                <?php 
                    try {
                        echo $item->documentStatus->title;
                    } catch (\Throwable $th) {
                        //throw $th;
                    }
                    ?>
                            </td>
                            <td><?php echo Html::a('<i class="fa-regular fa-pen-to-square fa-2x"></i>',['update', 'id' => $item->id])?>
                            </td>
                            <!-- <td> -->
                                <?php // echo Html::a(' <i class="fas fa-share fa-2x text-secondary"></i>',['/dms/documents/comment','id' => $item->id,'title' => '<i class="fas fa-share"></i>ส่งต่อ'],['class' => 'open-modal','data' => ['size' => 'modal-md']])?>
                            <!-- </td> -->
                        </tr>
                        <?php endforeach;?>

                    </tbody>
                </table>

            </div>
        </div>
    </div>

</div>

<div class="iq-card-footer text-muted d-flex justify-content-center mt-4">
    <?= yii\bootstrap5\LinkPager::widget([
        'pagination' => $dataProvider->pagination,
        'firstPageLabel' => 'หน้าแรก',
        'lastPageLabel' => 'หน้าสุดท้าย',
        'options' => [
            'listOptions' => 'pagination pagination-sm',
            'class' => 'pagination-sm',
        ],
    ]); ?>
</div>


<?php // Pjax::end(); ?>

<?php
$js = <<< JS
const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]')
const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl))
$('[data-toggle="popover"]').popover({container: 'body' });



JS;
$this->registerJS($js);

?>