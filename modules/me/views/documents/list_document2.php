 <?php
use yii\helpers\Url;
use yii\bootstrap5\Html;
use app\modules\dms\models;
use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\dms\models\Documents;
$this->title = 'ทะเบียนหนังสือ';
?>

<?php $this->beginBlock('page-title'); ?>
<i class="fa-regular fa-file-lines fs-4"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?php  echo $this->render('@app/modules/me/views/documents/menu',['action' => $action]) ?>
<?php $this->endBlock(); ?>


<?php $this->beginBlock('navbar_menu'); ?>
<?php echo $this->render('@app/modules/me/menu',['active' => 'document']) ?>
<?php $this->endBlock(); ?>
 <table class="table table-striped table-fixed">
                    <thead>
                        <tr>
                            <th class="text-center" style="width:50px;">ลำดับ</th>
                            <th class="text-center" style="min-width:100px; width:100px;">เลขที่รับ</th>
                            <th style="min-width:320px;">เรื่อง</th>
                            <th style="min-width:250px;">ผู้บันทึก</th>
                            <th style="min-width:100px;">สถานะ</th>
                            <th style="width:120px;">ลงความเห็น</th>
                        </tr>
                    </thead>
                    <tbody class="align-middle  table-group-divider table-hover">
                        <?php foreach($dataProvider->getModels() as $key => $item):?>
                        <td class="text-center"><?php echo (($dataProvider->pagination->offset + 1)+$key)?></td>
                        <td class="text-center"><?php echo $item->document?->doc_regis_number?></td>
                        <td class="fw-light align-middle">
                            <div>
                                <p class="text-primary fs-13 mb-0">
                                </p>
                                <p style="width:600px" class="text-truncate fs-6 mb-0">
                                    <?php if($item->document?->doc_speed == 'ด่วนที่สุด'):?>
                                    <span class="badge text-bg-danger fs-13">
                                        <i class="fa-solid fa-circle-exclamation"></i> ด่วนที่สุด
                                    </span>
                                    <?php endif;?>

                                    <?php if($item->document?->secret == 'ลับที่สุด'):?>
                                    <span class="badge text-bg-danger fs-13"><i class="fa-solid fa-lock"></i>
                                        ลับที่สุด
                                    </span>
                                    <?php endif;?>
                                    <a href="<?php echo Url::to(['/me/documents/view','id' => $item->id])?>">
                                        เรื่อง : <?php echo $item->document?->topic?>
                                    </a>

                                    <?php echo $item->document?->isFile() ? '<i class="fas fa-paperclip"></i>' : ''?>
                                </p>
                            </div>
                             <p class="fw-normal fs-13 mb-0">
                                <?=$item->document?->data_json['des'] ?? ''?>
                                </p>
                            <?php // echo Html::img('@web/img/krut.png',['style' => 'width:20px']);?>
                            <span class="text-danger">
                                <?php echo $item->document?->doc_number?>
                            </span>
                            <span class="text-primary fw-normal fs-13">
                                |
                                <i class="fa-solid fa-inbox"></i>
                                <?php  echo $item->document?->documentOrg->title ?? '-';?>
                                <span class="badge rounded-pill badge-soft-secondary text-primary fw-lighter fs-13">
                                    <i class="fa-regular fa-eye"></i> <?php echo $item->document?->viewCount()?>
                                </span>
                            </span>
                           

                            <?php echo $item->document?->StackDocumentTags('comment')?>
                        </td>
                        <td class="fw-light align-middle">
                            <div class=" d-flex flex-column">
                                <?=$item->document?->viewCreate()['avatar'];?>
                                <!-- <span class="fw-normal fs-6"><?php echo $item->document?->viewReceiveDate()?></span>
                            <span class="fw-lighter fs-13"><?php echo isset($item->document?->doc_time) ? '<i class="fa-solid fa-clock"></i> '.$item->document?->doc_time : ''?></span> -->
                            </div>
                        </td>
                        <td> <?=$item->document?->documentStatus->title ?? '-'?></td>
                        <td><?php echo Html::a('<i class="fa-regular fa-pen-to-square fa-2x"></i>',['view', 'id' => $item->id])?>
                        </td>
                        <!-- <td> -->
                        <?php // echo Html::a(' <i class="fas fa-share fa-2x text-secondary"></i>',['/dms/documents/comment','id' => $item->document?->id,'title' => '<i class="fas fa-share"></i>ส่งต่อ'],['class' => 'open-modal','data' => ['size' => 'modal-md']])?>
                        <!-- </td> -->
                        </tr>
                        <?php endforeach;?>

                    </tbody>
                </table>