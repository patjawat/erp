<?php
use yii\helpers\Url;
use yii\db\Expression;
use yii\bootstrap5\Html;
use app\modules\dms\models;
use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\dms\models\Documents;
?>
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-top align-items-center">
            <h6>
                <i class="bi bi-ui-checks"></i> ทะเบียนหนังสือ
                <span
                    class="badge rounded-pill text-bg-primary"><?php echo number_format($dataProvider->getTotalCount(), 0) ?></span>
                รายการ
            </h6>
            <?php if(isset($list)):?>
            <?=Html::a('ดูทั้งหมด',['/me/documents'],['class' => 'btn btn-light','data' => ['pjax' => 0]])?>
            <?php endif;?>
        </div>
        <?php if(!isset($list)):?>
        <div class="d-flex justify-content-between align-top align-items-center">

            <?php  echo $this->render('_search', ['model' => $searchModel]); ?>
            
        </div>
        <?php endif;?>
      
        <table class="table table-striped table-fixed">
                    <thead>
                        <tr>
                            <th style="width:80px;" class="fw-semibold">เลขรับ</th>
                            <th class="fw-semibold" style="width:900px;">เรื่อง</th>
                            <th class="fw-semibold" style="width:150px;">ลงความเห็น</th>
                            <th class="fw-semibold text-center" style="width:150px;">ไฟล์แนบ</th>
                            <th class="fw-semibold" style="width:300px;">วันที่รับ</th>
                            <th class="fw-semibold text-center" style="width:200px;">สถานะ</th>
                        </tr>
                    </thead>
                    <tbody class="align-middle  table-group-divider table-hover">
                        <?php foreach($dataProvider->getModels() as $item):?>
                            <?php // if($item->viewCount()['reading'] <= 0):?>
                        <tr class="" style="max-width:200px">
                            <td class="fw-semibold">
      
                            <?php echo $item->doc_regis_number?></td>
                            <td class="fw-light align-middle">
                                <a href="<?php echo Url::to(['/me/documents/view','id' => $item->id])?>"
                                    class="text-dark open-modal-fullscreen-x">
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




                                </a>
                            </td>
                            <td>
                                <?php echo $item->StackDocumentTags('comment')?>
                            </td>
                            <td class="text-center">
                                <?php // echo $item->isFile() ? Html::a('<i class="fas fa-paperclip"></i>',['/dms/documents/clip-file','id' => $item->id],['class' => 'open-modal','data' => ['size' => 'modal-xl']]) : ''?>
                                <?php echo $item->isFile() ? '<i class="fas fa-paperclip"></i>' : ''?>
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

                        </tr>
                        <?php //endif;?>
                        <?php endforeach;?>

                    </tbody>
                </table>


    </div>
</div>