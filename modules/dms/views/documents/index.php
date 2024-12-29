<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\components\AppHelper;
use app\modules\dms\models\Documents;
/** @var yii\web\View $this */
/** @var app\modules\dms\models\DocumentSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'หนังสือรับ';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-journal-text fs-4"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php  echo $this->render('@app/modules/dms/menu') ?>
<?php $this->endBlock(); ?>

<style>

</style>
<?php Pjax::begin(['timeout' => 80000]); ?>

<div class="documents-index">

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-top align-items-center">
                <h6>
                    <i class="bi bi-ui-checks"></i> ทะเบียนหนังสือ
                    <span
                        class="badge rounded-pill text-bg-primary"><?php echo number_format($dataProvider->getTotalCount(), 0) ?></span>
                    รายการ
                </h6>
            </div>
            <div class="d-flex justify-content-between align-top align-items-center">

                <?php  echo $this->render('@app/modules/dms/views/documents/_search', ['model' => $searchModel]); ?>
                <?= Html::a('<i class="fa-solid fa-plus"></i> ออกเลขหนังสือรับ', ['/dms/documents/create', 'title' => '<i class="fa-solid fa-calendar-plus"></i> บันทึกขออนุมัติการลา'], ['class' => 'btn btn-primary shadow rounded-pill', 'data' => ['size' => 'modal-lg']]) ?>
            </div>

            <div class="table-responsive">

                <table class="table table-striped table-fixed">
                    <thead>
                        <tr>
                            <th style="width:70px;">เลขรับ</th>
                            <th>เรื่อง</th>
                            <th style="width:200px;">ลงความเห็น</th>
                            <th class="text-center" style="width:105px;">ไฟล์แนบ</th>
                            <th style="width:130px;">วันที่รับ</th>
                            <th class="text-center" style="width:130px;">สถานะ</th>
                            <th>แก้ไข</th>
                            <th style="width:60px;">ส่งต่อ</th>
                        </tr>
                    </thead>
                    <tbody class="align-middle  table-group-divider table-hover">
                        <?php foreach($dataProvider->getModels() as $item):?>
                        <tr class="" style="max-width:200px">
                            <td class="fw-semibold"><?php echo $item->doc_regis_number?></td>
                            <td class="fw-light align-middle">
                                <a href="<?php echo Url::to(['/dms/documents/view','id' => $item->id])?>"
                                    class="text-dark open-modal-fullscreen">
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
                                                <?php echo $item->topic?>
                                            </p>

                                        </div>
                                    </div>
                                    <span class="text-primary fw-normal fs-13">
                                        <i class="fa-solid fa-inbox"></i>
                                        <?php  echo $item->documentOrg->title ?? '-';?>
                                        <span
                                            class="badge rounded-pill badge-soft-secondary text-primary fw-lighter fs-13">
                                            <i class="fa-regular fa-eye"></i> <?php echo $item->viewCount()?>
                                        </span>
                                    </span>



                                    <?php
// try {
//     echo '<span class="badge rounded-pill badge-soft-secondary text-primary fw-lighter fs-13"><i class="fa-solid fa-user-tag"></i> '.count($item->data_json['employee_tag']).'</span>';
// } catch (\Throwable $th) {
//     //throw $th;
// }
?>




                                </a>
                            </td>
                            <td>
                                <?php echo $item->StackDocumentTags('comment')?>
                            </td>
                            <td class="text-center">
                                <?php echo $item->isFile() ? Html::a('<i class="fas fa-paperclip"></i>',['/dms/documents/clip-file','id' => $item->id],['class' => 'open-modal','data' => ['size' => 'modal-xl']]) : ''?>
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
                            <td>
                                <?php echo Html::a(' <i class="fas fa-share fa-2x text-secondary"></i>',['/dms/documents/share-file','id' => $item->id,'title' => '<i class="fas fa-share"></i>ส่งต่อ'],['class' => 'open-modal','data' => ['size' => 'modal-lg']])?>
                            </td>
                        </tr>
                        <?php endforeach;?>

                    </tbody>
                </table>

            </div>
        </div>
    </div>

</div>

<div class="iq-card-footer text-muted d-flex justify-content-end mt-4">
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
<?php Pjax::end(); ?>


<?php
$js = <<< JS






JS;
$this->registerJS($js);

?>