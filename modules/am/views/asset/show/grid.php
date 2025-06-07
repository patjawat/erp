<?php
use yii\helpers\Html;
?>
<style>
.card-img-top {
    max-height: 220px;
    min-height: 220px;
}

.status-active {
    background-color: #d1e7dd;
    color: #0f5132;
}

.status-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}



</style>

<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-5 g-4 mb-4">
    <?php foreach($dataProvider->getModels() as $key => $model):?>
    <div class="col">
                    <div class="card h-100">
                        <div class="equipment-card-img">
                             <?= Html::img($model->showImg(),['class' => 'card-img-top p-2'])?>

                            <span class="status-badge status-active">ใช้งานอยู่</span>
                        </rect></div>
                        <div class="card-body">
                            <h5 class="equipment-title"><?=$model->AssetitemName();?></h5>
                            <p class="equipment-number"><i class="bi bi-upc"></i> <?=$model->code?></p>
                            <p class="text-primary h6"><?=isset($model->price) ? number_format($model->price,2) : ''?> บาท</p>
                            <div class="equipment-info"><i class="bi bi-tag"></i> Dell</div>
                            <div class="equipment-info"><i class="bi bi-calendar-event"></i> ปีงบประมาณ <?=$model->on_year?></div>
                        </div>
                        <div class="card-footer">
                            <div class="d-flex justify-content-between">
                                <?=Html::a('<i class="bi bi-eye"></i> รายละเอียด', ['/am/asset/view','id' => $model->id],['class' => 'btn btn-sm btn-outline-primary view-btn']) ?>
                                <div>
                                       <?=Html::a(' <i class="bi bi-pencil"></i>', ['/am/asset/update','id' => $model->id],['class' => 'btn btn-sm btn-outline-warning edit-btn']) ?>
                                    <!-- <button class="btn btn-sm btn-outline-warning edit-btn" data-id="1">
                                        <i class="bi bi-pencil"></i>
                                    </button> -->

                                    <button class="btn btn-sm btn-outline-danger delete-btn" data-id="1">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
    <?php endforeach?>
</div>