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
    <div
        class="card-body d-flex align-middle flex-lg-row flex-md-row flex-sm-column flex-sx-column justify-content-lg-between justify-content-md-between justify-content-sm-center align-items-center ">
        <div class="d-flex gap-3 justify-content-start">
            <?= Html::a('<i class="fa-solid fa-cart-plus"></i> เลือกรายการเบิก ', ['/me/store/product'], ['class' => 'btn btn-primary rounded-pill shadow', 'data' => ['size' => 'modal-md']]) ?>
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

                    <h6><i class="bi bi-ui-checks"></i> ทะเบียนเบิกวัสดุ <span class="badge rounded-pill text-bg-primary"><?=$dataProvider->getTotalCount()?> </span>รายการ</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-primary">
                        <thead>
                            <tr>
                                <th>ชื่อรายการ</th>
                                <th style="width:350px">คลัง</th>
                                <th style="width:350px">ผู้อนุมัติ</th>
                                <th style="width:350px">สถานะ</th>
                                <th class="text-center" style="width:100px">ดำเนินการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($dataProvider->getModels() as $item):?>
                            <tr class="">
                                <td><?php echo $item->CreateBy()['avatar']?></td>
                                <td>
                                    <?=$item->transaction_type?>
                                    <?=$item->warehouse->warehouse_name?></td>
                                <td><?php
                                try {
                                    // echo $item->data_json['check_confirm'];
                                    echo $model->item()['avatar'];
                                } catch (\Throwable $th) {

                                }
                                ?></td>
                                <td>
                                    <?php
                                    if($item->order_status == 'pending'){
                                        echo 'รอดำเนินการ';
                                    }else{
                                        echo 'เสร็จสิ้น';
                                    }
                                    ?>
                                </td>
                                <td class="fw-light">
                                    <div class="btn-group">
                                        <?= Html::a('<i class="fa-regular fa-pen-to-square text-primary"></i>', ['/me/store/view', 'id' => $item->id], ['class' => 'btn btn-light w-100']) ?>
                                        <button type="button"
                                            class="btn btn-light dropdown-toggle dropdown-toggle-split"
                                            data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent">
                                            <i class="bi bi-caret-down-fill"></i>
                                        </button>

                                    </div>

                                </td>
                                <?php endforeach;?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>