<?php 
use yii\helpers\Url;
use yii\helpers\Html;
use app\components\UserHelper;
use app\modules\dms\models\DocumentsDetail;

$emp = UserHelper::GetEmployee();

$docDepartment = DocumentsDetail::find()
    ->joinWith('document')
    ->andWhere(['thai_year' => (date('Y') + 543)])
    ->andWhere(['to_id' => $emp->department])
    ->andWhere(['name' => 'department'])
    // ->andWhere(['doc_read' => null])
    ->all();



    $docTags = DocumentsDetail::find()
        ->joinWith('document')
        ->andWhere(['thai_year' => (date('Y') + 543)])
        ->andWhere(['to_id' => $emp->id])
        ->andWhere(['name' => 'tags'])
        // ->andWhere(['doc_read' => null])
        ->all();
    



?>
<div class="d-flex justify-content-between">

    <h6 class="text-white"><i class="fa-regular fa-file-lines"></i> หนังสือ/ประกาศ/ประชาสัมพันธ์ <?php echo count($docDepartment)?></h6>
    <?php echo Html::a('ทั้งหมด',['/'],['class' => 'text-white'])?>
</div>
    <div class="card rounded-4">
        <div class="card-body rounded-4" style="background:rgba(241, 238, 240, 0.98); min-height:200px">
            <?php foreach($docDepartment as $itemDep):?>
                <div style="width:100px;">
                    <p > <?php echo Html::img('@web/img/krut.png',['style' => 'width:20px']);?> <?php echo $itemDep->document->topic?></p>
               
            </div>
            <?php endforeach?>
            <?php foreach($docTags as $itemTag):?>
                
                <a href="<?php echo Url::to(['/me/documents/view','id' => $itemTag->id])?>"
                            class="text-dark open-modal-fullscreen-x">
                            <div>
                                        <p class="text-primary fw-semibold fs-13 mb-0">
                                        <?php if($itemTag->document->doc_speed == 'ด่วนที่สุด'):?>
                                                    <span class="badge text-bg-danger fs-13">
                                                        <i class="fa-solid fa-circle-exclamation"></i> ด่วนที่สุด
                                                    </span>
                                                    <?php endif;?>
                                                    
                                                    <?php if($itemTag->document->secret == 'ลับที่สุด'):?>
                                                        <span class="badge text-bg-danger fs-13"><i class="fa-solid fa-lock"></i>
                                                        ลับที่สุด
                                                    </span>
                                                    <?php endif;?>
                                                    <?php echo Html::img('@web/img/krut.png',['style' => 'width:20px']);?>
                                            <?php echo $itemTag->document->doc_number?>
                                        </p>
                                        <p style="width:320px" class="text-truncate fw-semibold fs-6 mb-0"><?php echo $itemTag->document->topic?> <?php echo $itemTag->document->isFile() ? '<i class="fas fa-paperclip"></i>' : ''?></p>
                                        </div>
                                        <span class="text-primary fw-normal fs-13">
                                        <i class="fa-solid fa-inbox"></i>
                                            <?php  echo $itemTag->documentOrg->title ?? '-';?>
                                        <span class="badge rounded-pill badge-soft-secondary text-primary fw-lighter fs-13">
                                            <i class="fa-regular fa-eye"></i> <?php echo $itemTag->document->viewCount()?>
                                        </span>
                                    </span>
                            <?php  echo Html::a(($itemTag->bookmark() == 'Y' ? '<i class="fa-solid fa-star text-warning"></i>' : '<i class="fa-regular fa-star"></i>'),['/me/documents/bookmark', 'id' => $itemTag->id],['class' => 'bookmark','id' => 'bookmark-'.$itemTag->id])?>
                        </a>
                    <hr>
            <?php endforeach?>
        </div>
    </div>
</div>