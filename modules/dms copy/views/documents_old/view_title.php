
<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\widgets\DetailView;
?>
<div class="d-flex align-items-center">
            <div class="flex-shrink-0">
                <i class="bi bi-journal-text fs-2"></i>
            </div>
            <div class="flex-grow-1 ms-3">
                <div class="d-flex flex-column">
                    <div>
                        <span class="h5">
                            <?= Html::encode($this->title) ?>
                        </span>
                        <span class="fw-semibold fs-6">
                            <?php if($model->doc_speed == 'ด่วนที่สุด'):?>
                            <span class="badge text-bg-danger fs-13"><i class="fa-solid fa-circle-exclamation"></i>
                                ด่วนที่สุด</span>
                            <?php endif;?>

                            <?php if($model->doc_speed == 'ด่วน'):?>
                            <span class="badge text-bg-warning fs-13"><i class="fa-solid fa-circle-exclamation"></i>
                                ด่วน</span>
                            <?php endif;?>

                    </div>
                    <span class="text-primary">
                        <?php  echo $model->documentOrg->title ?? '-';?>
                    </span>
                </div>
            </div>
        </div>