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
            <?= Html::a('<i class="fa-solid fa-plus"></i> ออกเลขหนังสือรับ', ['/me/documents/create', 'title' => '<i class="fa-solid fa-calendar-plus"></i> บันทึกขออนุมัติการลา'], ['class' => 'btn btn-primary shadow rounded-pill', 'data' => ['size' => 'modal-lg']]) ?>
        </div>
        <?php endif;?>

        <table class="table table-striped table-fixed">
            <thead>
                <tr>
                    <th scope="col" style="width:55px;">เลขรับ</th>
                    <th scope="col">เรื่อง</th>
                    <th scope="col" class="text-center" style="width:105px;">ไฟล์แนบ</th>
                    <th scope="col" style="width:130px;">วันที่หนังสือ</th>
                    <!-- <th scope="col" style="width:60px;">ส่งต่อ</th> -->
                </tr>
            </thead>
            <tbody class="align-middle  table-group-divider table-hover">
                <?php foreach($dataProvider->getModels() as $item):?>
                <tr class="">
                    <td class="fw-semibold"><?php echo $item->doc_regis_number?></td>
                    <td class="fw-light align-middle">
                        <a href="<?php echo Url::to(['/me/documents/view','id' => $item->id])?>" class="text-dark open-modal-fullscreen">
                            <div class=" d-flex flex-column">

                                <span class="fw-semibold fs-6">
                                    <?php if($item->doc_speed == 'ด่วนที่สุด'):?>
                                    <span class="badge text-bg-danger fs-13"><i
                                            class="fa-solid fa-circle-exclamation"></i> ด่วนที่สุด</span>
                                    <?php endif;?>

                                    <?php if($item->doc_speed == 'ด่วน'):?>
                                    <span class="badge text-bg-waring fs-13"><i
                                            class="fa-solid fa-circle-exclamation"></i> ด่วน</span>
                                    <?php endif;?>
                                    <?php echo $item->topic?>

                            </div>
                            <!-- <span class="badge rounded-pill badge-soft-secondary text-primary fw-lighter fs-13"> -->
                            <span class="text-primary fw-normal fs-13">
                                <i class="fa-solid fa-inbox"></i> <?php  echo $item->documentOrg->title ?? '-';?>
                            </span>
                        </a>
                    </td>
                    <td class="text-center">
                        <?php echo $item->isFile() ? Html::a('<i class="fas fa-paperclip"></i>',['/dms/documents/file-comment','id' => $item->id],['class' => 'open-modal','data' => ['size' => 'modal-xl']]) : ''?>
                    </td>
                    <td class="fw-light align-middle">
                        <div class=" d-flex flex-column">
                            <span class="fw-normal fs-6"><?php echo $item->viewDocDate()?></span>
                            <span
                                class="fw-lighter fs-13"><?php echo AppHelper::timeDifference($item->doc_date)?></span>
                        </div>
                    </td>
                    <!-- <td>
                <?php // echo Html::a(' <i class="fas fa-share fa-2x text-secondary"></i>',['/me/documents/share-file','id' => $item->id,'title' => '<i class="fas fa-share"></i>ส่งต่อ'],['class' => 'open-modal','data' => ['size' => 'modal-lg']])?>    
               </td> -->
                </tr>
                <?php endforeach;?>

            </tbody>
        </table>


    </div>
</div>