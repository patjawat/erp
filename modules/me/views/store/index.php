<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
/** @var yii\web\View $this */
$this->title = "เบิกวัสดุ/อุปกรณ์";
?>

<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-cart-plus"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

    <div class="card">
        <div class="card-body d-flex align-middle flex-lg-row flex-md-row flex-sm-column flex-sx-column justify-content-lg-between justify-content-md-between justify-content-sm-center">
            <div class="d-flex gap-3 justify-content-start">
                <?= Html::a('<i class="fa-solid fa-cart-plus"></i> เบิกวัสดุ ', ['/me/store/product','title' => '<i class="fa-regular fa-circle-check"></i> เลือกประเภทการซ่อม'], ['class' => 'btn btn-primary rounded-pill shadow', 'data' => ['size' => 'modal-md']]) ?>
        </div>
        <div class="d-flex align-items-center gap-2">
            <?=$this->render('_search', ['model' => $searchModel])?>
            <?= Html::a('<i class="bi bi-list-ul"></i>', ['#', 'view' => 'list'], ['class' => 'btn btn-outline-primary']) ?>
            <?= Html::a('<i class="bi bi-grid"></i>', ['#', 'view' => 'grid'], ['class' => 'btn btn-outline-primary']) ?>
            <?php //  Html::a('<i class="fa-solid fa-gear"></i>', ['#', 'title' => 'การตั้งค่าบุคลากร'], ['class' => 'btn btn-outline-primary open-modal', 'data' => ['size' => 'modal-md']]) ?>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">


        <div class="card">
            <div class="card-body">
                <div class="d-flex justofy-content-between">

                    <h6><i class="bi bi-ui-checks"></i> ทะเบียนเบิกวัสดุ 0 รายการ</h6>
                </div>
                <div class="table-responsive">


                </div>
            </div>
        </div>

    </div>
</div>