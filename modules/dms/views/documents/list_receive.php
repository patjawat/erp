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
$this->title = 'หนังสือรับ';

$this->params['breadcrumbs'][] = $this->title;

$dataFile = Yii::getAlias('@app/doc_receive/data.json');
$jsonCount = 0;
if (file_exists($dataFile)) {
    $jsonData = file_get_contents($dataFile);
    $jsonArray = json_decode($jsonData, true);
    if (is_array($jsonArray)) {
        $jsonCount = count($jsonArray);
    }
}
?>
<?php $this->beginBlock('page-title'); ?>
<?php if($searchModel->document_group == 'receive'):?>
<i class="fa-solid fa-download"></i></i> <?= $this->title; ?>
<?php endif; ?>
<?php if($searchModel->document_group == 'send'):?>
<i class="fa-solid fa-paper-plane text-danger"></i></i><?= $this->title; ?>
<?php endif; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php  echo $this->render('@app/modules/dms/menu',['model' =>$searchModel]) ?>
<?php $this->endBlock(); ?>

<?php if($jsonCount > 0):?>
<?php $this->beginBlock('action'); ?>
<?= Html::a('<i class="fa-regular fa-circle-down"></i> หนังสือรอรับ <span class="badge text-bg-primary">'.$jsonCount.'</span>', ['/dms/doc-receive'], ['class' => 'btn btn-primary shadow rounded-pill', 'class' => 'btn btn-warning shadow rounded-pill animate__animated animate__headShake animate__infinite']);?>
<?php $this->endBlock(); ?>
<?php endif;?>

<?php $this->beginBlock('navbar_menu'); ?>
<?php  echo $this->render('@app/modules/dms/menu',['model' =>$searchModel,'active' => 'receive']) ?>
<?php $this->endBlock(); ?>

<?php // Pjax::begin(['id' => 'document','timeout' => 80000]); ?>

<div class="card">
    <div class="card-body d-flex justify-content-between align-top align-items-center">
        <?= Html::a('<i class="fa-solid fa-circle-plus"></i> ออกเลข'.$this->title, ['/dms/documents/create','document_group' => $searchModel->document_group], ['class' => 'btn btn-primary shadow rounded-pill', 'data' => ['size' => 'modal-lg']]) ?>
        <?= $this->render('@app/modules/dms/views/documents/_search', ['model' => $searchModel]); ?>
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
<div class="documents-index">

    <div class="card">
        <div class="card-body">

            <div class="d-flex justify-content-between">

                <h6> <i class="bi bi-ui-checks"></i> ทะเบียน<?php echo $this->title?>
                    <span
                        class="badge rounded-pill text-bg-primary"><?php echo number_format($dataProvider->getTotalCount(), 0) ?></span>
                    รายการ
                </h6>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-fixed">
                    <thead>
                        <tr>
                            <th class="text-center fw-semibold" style="width:50px;">ลำดับ</th>
                            <th class="text-center fw-semibold" style="min-width:100px; width:100px;">เลขที่รับ</th>
                            <th class="fw-semibold" style="min-width:320px;">เรื่อง</th>
                            <th class="fw-semibold" style="min-width:250px;">ผู้บันทึก</th>
                            <th class="fw-semibold" style="min-width:100px;">สถานะ</th>
                            <th class="fw-semibold" style="width:70px;">แก้ไข</th>
                        </tr>
                    </thead>
                    <tbody class="align-middle  table-group-divider table-hover">
                        <?php foreach($dataProvider->getModels() as $key => $item):?>
                        <td class="text-center fw-semibold"><?php echo (($dataProvider->pagination->offset + 1)+$key)?>
                        </td>
                        <td class="text-center fw-semibold"><?php echo $item->doc_regis_number?></td>
                        <td class="fw-light align-middle">
                            <div>
                                <h6 style="width:600px" class="text-truncate fw-semibold mb-0">
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
                                    <a href="<?php echo Url::to(['/dms/documents/view','id' => $item->id])?>">
                                        เรื่อง : <?php echo $item->topic?>
                                    </a>

                                    <?php echo $item->isFile() ? '<i class="fas fa-paperclip"></i>' : ''?>
                                </h6>
                            </div>
                            <p class="fw-normal fs-13 mb-0">
                                <?=$item->data_json['des'] ?? ''?>
                            </p>
                            <?php // echo Html::img('@web/img/krut.png',['style' => 'width:20px']);?>
                            <span class="text-danger">
                                <?php echo $item->doc_number?>
                            </span>
                            <span class="text-primary fw-normal fs-13">
                                |
                                <i class="fa-solid fa-inbox"></i>
                                <?php  echo $item->documentOrg->title ?? '-';?>
                                <span class="badge rounded-pill badge-soft-secondary text-primary fw-lighter fs-13">
                                    <i class="fa-regular fa-eye"></i> <?php echo $item->viewCount()?>
                                </span>
                            </span>


                            <?php echo $item->StackDocumentTags('comment')?>
                        </td>
                        <td class="fw-light align-middle">
                            <div class=" d-flex flex-column">
                                <?=$item->viewCreate()['avatar'];?>
                                <!-- <span class="fw-normal fs-6"><?php echo $item->viewReceiveDate()?></span>
                            <span class="fw-lighter fs-13"><?php echo isset($item->doc_time) ? '<i class="fa-solid fa-clock"></i> '.$item->doc_time : ''?></span> -->
                            </div>
                        </td>
                        <td> <?=$item->documentStatus->title ?? '-'?></td>
                        <td><?php echo Html::a('<i class="fa-regular fa-pen-to-square fa-2x"></i>',['update', 'id' => $item->id])?>
                        </td>
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