<?php
use yii\helpers\Url;
use yii\db\Expression;
use yii\bootstrap5\Html;
use app\modules\dms\models;
use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\dms\models\Documents;
$me = UserHelper::GetEmployee();


?>
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-top align-items-center">
            <h6>
                <i class="bi bi-ui-checks"></i> ทะเบียนหนังสือ
                <span
                    class="badge rounded-pill text-bg-primary"><?php echo number_format(($dataProvider->getTotalCount()), 0) ?></span>
                รายการ
            </h6>
            <?=Html::a('แสดงทั้งหมด',['/me/documents'],['class' => 'btn btn-sm btn-light rounded-pill','data' => ['pjax' => 0]])?>
        </div>
        <table class="table table-striped table-fixed">
            <thead>
                <tr>
                    <th class="text-center fw-semibold" style="width:50px;">ลำดับ</th>
                    <th class="text-center fw-semibold" style="min-width:100px; width:100px;">เลขที่รับ</th>
                    <th class="fw-semibold" style="min-width:320px;">เรื่อง</th>
                    <th class="fw-semibold" style="min-width:250px;">ผู้บันทึก</th>
                    <th class="fw-semibold" style="min-width:100px;">สถานะ</th>
                    <th class="fw-semibold" style="width:70px;">ดำเนินการ</th>
                </tr>
            </thead>
            <tbody class="align-middle  table-group-divider table-hover">
                <?php foreach($dataProvider->getModels() as $key => $item):?>
                <tr>
                    <td class="text-center fw-semibold"><?php echo (($dataProvider->pagination->offset + 1)+$key)?></td>
                    <td class="text-center fw-semibold"><?= isset($item->document) ? $item->document->doc_regis_number : ''?></td>
                    <td class="fw-light align-middle">
                        <div>
                            <h6 style="width:600px" class="text-truncate fw-semibold mb-0">
                                <?php if(isset($item->document) &&  $item->document->doc_speed == 'ด่วนที่สุด'):?>
                                <span class="badge text-bg-danger fs-13">
                                    <i class="fa-solid fa-circle-exclamation"></i> ด่วนที่สุด
                                </span>
                                <?php endif;?>

                                <?php if(isset($item->document) && $item->document->secret == 'ลับที่สุด'):?>
                                <span class="badge text-bg-danger fs-13"><i class="fa-solid fa-lock"></i>
                                    ลับที่สุด
                                </span>
                                <?php endif;?>
                                <a href="<?php echo Url::to(['/me/documents/view','id' => $item->id,'callback' => '/me'])?>" class="open-modal" data-size="modal-xxl">
                                    เรื่อง : <?php echo $item->document ? $item->document->topic : ''?>
                                </a>
                                <?php echo  $item->document ? ($item->document->isFile() ? '<i class="fas fa-paperclip"></i>' : '') : ''?>
                            </h6>
                        </div>
                        <p class="fw-normal fs-13 mb-0">
                            <?= $item->document ? $item->data_json['des'] ?? '' : ''?>
                        </p>
                        <?php // echo Html::img('@web/img/krut.png',['style' => 'width:20px']);?>
                        <span class="text-danger">
                            <?php echo  $item->document ? $item->document->doc_number : ''?>
                        </span>
                        <span class="text-primary fw-normal fs-13">
                            |
                            <i class="fa-solid fa-inbox"></i>
                            <?php  echo  $item->document ? $item->documentOrg->title ?? '-' : '';?>
                            <span class="badge rounded-pill badge-soft-secondary text-primary fw-lighter fs-13">
                                <i class="fa-regular fa-eye"></i>
                                <?php echo  $item->document ? $item->document->viewCount() : ''?>
                            </span>
                        </span>


                        <?php echo  $item->document ? $item->document->StackDocumentTags('comment') : ''?>
                    </td>
                    <td class="fw-light align-middle">
                        <div class=" d-flex flex-column">
                            <?= $item->document ? $item->document->viewCreate()['avatar'] : '';?>
                            <!-- <span class="fw-normal fs-6"><?php echo  $item->document ? $item->document->viewReceiveDate() : ''?></span>
                            <span class="fw-lighter fs-13"><?php echo  $item->document ? (isset($item->document->doc_time) ? '<i class="fa-solid fa-clock"></i> '.$item->document->doc_time : '') :''?></span> -->
                        </div>
                    </td>
                    <td> <?=$item->document->documentStatus->title ?? '-'?></td>
                    <td><?php echo  $item->document ?  Html::a('<i class="fa-regular fa-pen-to-square fa-2x"></i>',['view', 'id' => $item->id,'callback' => '/me'],['class' => 'open-modal','data' => ['size' => 'modal-xxl']]) : ''?></td>
                </tr>
                <?php endforeach;?>

            </tbody>
    </div>
</div>