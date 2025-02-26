<?php 
use yii\helpers\Url;
use yii\helpers\Html;
use app\components\UserHelper;
?>
<a href="<?php echo Url::to(['/me/documents/view','id' => $item->id])?>"
            class="text-dark open-modal-fullscreen-x">
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
                <p style="width:320px" class="text-truncate fw-semibold fs-6 mb-0">
                    <?php echo $item->document->topic?>
                    <?php echo $item->document->isFile() ? '<i class="fas fa-paperclip"></i>' : ''?></p>
            </div>
            <span class="text-primary fw-normal fs-13">
                <i class="fa-solid fa-inbox"></i>
                <?php  echo $item->documentOrg->title ?? '-';?>
                <span class="badge rounded-pill badge-soft-secondary text-primary fw-lighter fs-13">
                    <i class="fa-regular fa-eye"></i> <?php echo $item->document->viewCount()?>
                </span>
            </span>
            <?php  echo Html::a(($item->bookmark() == 'Y' ? '<i class="fa-solid fa-star text-warning"></i>' : '<i class="fa-regular fa-star"></i>'),['/me/documents/bookmark', 'id' => $item->id],['class' => 'bookmark','id' => 'bookmark-'.$item->id])?>
        </a>