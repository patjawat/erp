<?php
use yii\helpers\Url;
use yii\helpers\Html;
?>
<div class="container">

    <div class="row row-cols-1 row-cols-sm-4 row-cols-md-4 g-3">
        <?php foreach($dataProvider->getModels() as $item):?>
        <div class="col">
            <a href="<?php echo Url::to(['/booking/booking-cars-items/create','asset_item_id' => 1])?>" class="open-modal" data-size="modal-lg">
                <div class="card border-0 shadow-sm hover-card">
                    <?php // Html::img($item->showImg(), ['class' => 'avatar-profile']) ?>
                    <?php echo Html::img($item->showImg(),['class' => 'rounded-3']);?>
                    <div class="card-body">
                        <h6 class="text-center"><?php  echo $item->title;?></h6>
                        <ul>
                            <li>ความจุ <?=$item->data_json['seat_capacity'] ?? '-'?> คน</li>
                        </ul>
                        <?php 
                        
                        // echo "<pre>";
                        // print_r($item);
                        // echo "</pre>";
                        ?>
                    </div>
                </div>
            </a>
        </div>
        <?php endforeach;?>
    </div>
</div>