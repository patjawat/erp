<?php
use yii\helpers\Url;
use yii\helpers\Html;
?>

    <div class="row row-cols-1 row-cols-sm-5 row-cols-md-5 g-3">
        <?php foreach($dataProvider->getModels() as $item):?>
        <div class="col">
            <a href="<?php echo Url::to(['/booking/meeting-room/view','id' => 1])?>" class="open-modal" data-size="modal-lg">
                <div class="card border-0 shadow-sm hover-card">
                    <?php // Html::img($item->showImg(), ['class' => 'avatar-profile']) ?>
                    <?php echo Html::img($item->showImg(),['class' => 'rounded-3','style' => 'object-fit: cover;max-height: 148px;']);?>
                    <div class="card-body">
                        <h6 class="text-center"><?php  echo $item->title;?></h6>
                      
                    </div>
                </div>
            </a>
        </div>
        <?php endforeach;?>
    </div>