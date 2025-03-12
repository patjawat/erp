<?php
use yii\helpers\Url;
?>
<div class="row row-cols-1 row-cols-sm-3 row-cols-md-3 g-3 justify-content-center">

    <?php foreach($dataProvider->getModels() as $item):?>
        <div class="col">
            <a href="<?php echo Url::to(['/me/store-v2/set-warehouse','id' => $item->id]);?>">
                <div class="card border-0 shadow-sm hover-card">
                    <div class="d-flex justify-content-center align-items-center bg-secondary p-4 rounded-top">
                        <i class="bi bi-shop fs-1 text-white"></i>
                    </div>
                    <div class="card-body">
                        <h6 class="text-center"><?php echo $item->warehouse_name?></h6>
                    </div>
                </div>
            </a>
        </div>
        <?php endforeach;?>
    </div>