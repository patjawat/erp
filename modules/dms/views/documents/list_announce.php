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
    $this->title = 'หนังประกาศ/นโยบาย ';

$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-bullhorn fs-1"></i></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php  echo $this->render('@app/modules/dms/menu',['model' =>$searchModel]) ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?php  echo $this->render('@app/modules/dms/menu',['model' =>$searchModel,'active' => 'announce']) ?>
<?php $this->endBlock(); ?>

<?php // Pjax::begin(['id' => 'document','timeout' => 80000]); ?>

<div class="card">
    <div class="card-body  align-top align-items-center">
         <div class="d-flex justify-content-between align-top align-items-center">
        <?= Html::a('<i class="fa-solid fa-circle-plus"></i> เพิ่ม'.$this->title, ['/dms/documents/create','document_group' => $searchModel->document_group,'document_type' => $searchModel->document_type, 'title' => '<i class="fa-solid fa-calendar-plus"></i> หนังสือส่ง'], ['class' => 'btn btn-primary shadow rounded-pill', 'data' => ['size' => 'modal-lg']]) ?>
         <?php  echo $this->render('_search_announce', ['model' => $searchModel]); ?>
                 <?= Html::a(
            '<i class="fa-solid fa-file-excel ne-1"></i> ส่งออก',
            ['/dms/documents/receive'],
            [
                'class' => 'btn btn-success shadow rounded-pill',
                'onclick' => 'return alert("ช้าก่อนกำลังเขียน code ยังไม่เสร็จ");'
            ]
        ) ?>
    </div>
    </div>
</div>

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

            <div class="table-responsive">

                <table class="table table-striped table-fixed">
                    <thead>
                        <tr>
                            <th class="text-center fw-semibold" style="width:100px">เลขที่</th>
                            <th class="fw-semibold">เรื่อง</th>
                            <th class="fw-semibold text-center"  style="width:130px">ดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody class="align-middle  table-group-divider table-hover">
                        <?php foreach($dataProvider->getModels() as $item):?>
                        <tr class="" style="max-width:200px">
                            <td class="fw-semibold text-center"><?php echo $item->doc_regis_number?></td>

                            <td class="fw-light align-middle">
                                <a href="<?php echo Url::to(['/dms/documents/view','id' => $item->id])?>"
                                    class="text-dark open-modal-fullscree-xn">
                                    <div class=" d-flex flex-column w-75">
                                        <div>
                                            <p class="text-primary fw-semibold fs-13 mb-0">
                                                <?php echo Html::img('@web/img/krut.png',['style' => 'width:23px']);?>
                                                <?php echo $item->topic?>
                                                <?php echo $item->isFile() ? '<i class="fas fa-paperclip"></i>' : ''?>

                                            </p>

                                        </div>
                                    </div>
                                </a>
                                <span class="text-primary fw-normal fs-13">
                                    <span class="badge rounded-pill badge-soft-secondary text-primary fw-lighter fs-13">
                                        <i class="fa-regular fa-eye"></i> <?php echo $item->viewCount()?>
                                    </span>
                                </span>

                            </td>
                            <td class="text-center"><?php echo Html::a('<i class="fa-regular fa-pen-to-square fa-2x"></i>',['update', 'id' => $item->id])?>
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