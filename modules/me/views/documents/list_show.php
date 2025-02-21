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
                    class="badge rounded-pill text-bg-primary"><?php echo number_format(($dataProviderTags->getTotalCount()+$dataProviderDepartment->getTotalCount()), 0) ?></span>
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
                    <th scope="col">เรื่อง</th>
                </tr>
            </thead>
            <tbody class="align-middle  table-group-divider table-hover">
                <?php foreach($dataProviderTags->getModels() as $item):?>
                <tr class="">
                    <td class="fw-light align-middle">
                        <a href="<?php echo Url::to(['/me/documents/view','id' => $item->id,'callback' => 'me'])?>" class="text-dark" data-pjax="false">
                        <div>
                                        <p class="text-primary fw-semibold fs-13 mb-0">
                                            <?php if($item->document->doc_speed == 'ด่วนที่สุด'):?>
                                                    <span class="badge text-bg-danger fs-13">
                                                        <i class="fa-solid fa-circle-exclamation"></i> ด่วนที่สุด
                                                    </span>
                                                    <?php endif;?>
                                                    
                                                    <?php if($item->document->secret == 'ลับที่สุด'):?>
                                                        <span class="badge text-bg-danger fs-13"><i class="fa-solid fa-lock"></i>
                                                        ลับที่สุด
                                                    </span>
                                                    <?php endif;?>

                                                    <?php echo Html::img('@web/img/krut.png',['style' => 'width:20px']);?>
                                            <?php echo $item->document->doc_number?> |
                                            <span class="fw-normal fs-6"><?php echo $item->document->viewDocDate()?></span>
                                            ( <i class="bi bi-clock-history"></i> <span class="fw-lighter fs-13"><?php echo AppHelper::timeDifference($item->document->doc_date)?>)</span>
                                            
                                        </p>
                                        <p style="width:600px" class="text-truncate fw-semibold fs-6 mb-0"><?php echo $item->document->topic?> <?php echo $item->document->isFile() ? '<i class="fas fa-paperclip"></i>' : ''?></p>
                                        </div>
                                        <span class="text-primary fw-normal fs-13">
                                        <i class="fa-solid fa-inbox"></i>
                                            <?php  echo $item->documentOrg->title ?? '-';?>
                                        <span class="badge rounded-pill badge-soft-secondary text-primary fw-lighter fs-13">
                                            <i class="fa-regular fa-eye"></i> <?php echo $item->document->viewCount()?>
                                        </span>
                                    </span>
                                    <span> | <i class="fa-solid fa-user-tag"></i> <?php echo $me->fullname ?> </span>
                        </a>
                    </td>
                    <td class="text-center">
                        <?php echo $item->document->isFile() ? Html::a('<i class="fas fa-paperclip"></i>',['/dms/documents/file-comment','id' => $item->id],['class' => 'open-modal','data' => ['size' => 'modal-xl']]) : ''?>
                    </td>

                </tr>
                <?php endforeach;?>

                <?php foreach($dataProviderDepartment->getModels() as $item):?>
                <tr class="">
                    <td class="fw-light align-middle">
                        
                        <a href="<?php echo Url::to(['/me/documents/view','id' => $item->id,'callback' => 'me'])?>" class="text-dark open-modal-fullscreen-x">
                        <div>
                                        <p class="text-primary fw-semibold fs-13 mb-0">
                                        <?php if($item->document->doc_speed == 'ด่วนที่สุด'):?>
                                                    <span class="badge text-bg-danger fs-13">
                                                        <i class="fa-solid fa-circle-exclamation"></i> ด่วนที่สุด
                                                    </span>
                                                    <?php endif;?>
                                                    
                                                    <?php if($item->document->secret == 'ลับที่สุด'):?>
                                                        <span class="badge text-bg-danger fs-13"><i class="fa-solid fa-lock"></i>
                                                        ลับที่สุด
                                                    </span>
                                                    <?php endif;?>

                                                    <?php echo Html::img('@web/img/krut.png',['style' => 'width:20px']);?>
                                            <?php echo $item->document->doc_number?>
                                        </p>
                                        <p style="width:600px" class="text-truncate fw-semibold fs-6 mb-0"><?php echo $item->topic?> <?php echo $item->isFile() ? '<i class="fas fa-paperclip"></i>' : ''?></p>
                                        </div>
                                        <span class="text-primary fw-normal fs-13">
                                        <i class="fa-solid fa-inbox"></i>
                                            <?php  echo $item->documentOrg->title ?? '-';?>
                                        <span class="badge rounded-pill badge-soft-secondary text-primary fw-lighter fs-13">
                                            <i class="fa-regular fa-eye"></i> <?php echo $item->viewCount()?>
                                        </span>
                                    </span>
                                    <span><i class="bi bi-tags"></i> หน่วยงาน</span>
                        </a>
                    </td>
                    <td class="fw-light align-middle">
                        <div class=" d-flex flex-column">
                            <span class="fw-normal fs-6"><?php echo $item->document->viewDocDate()?></span>
                            <span
                                class="fw-lighter fs-13"><?php echo AppHelper::timeDifference($item->document->doc_date)?></span>
                        </div>
                    </td>
                </tr>
                <?php endforeach;?>
                

            </tbody>
        </table>


    </div>
</div>