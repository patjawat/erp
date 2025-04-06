<?php
use yii\helpers\Url;
use yii\helpers\Html;
?>
<div class="container">

    <div class="row row-cols-1 row-cols-sm-6 row-cols-md-6 g-3">
        <?php foreach($dataProvider as $item):?>
        <div class="col">
            <a href="<?php echo Url::to(['/booking/booking-cars-items/create','asset_item_id' => $item->id])?>" class="open-modal" data-size="modal-lg">
                <div class="card border-0 shadow-sm hover-card">
                    <?= Html::img($item->showImg(), ['class' => 'avatar-profile']) ?>
                    <div class="card-body">
                        <h6 class="text-center"><?php echo $item->assetItem->title;?></h6>
                    </div>
                </div>
            </a>
        </div>
        <?php endforeach;?>
    </div>
</div>