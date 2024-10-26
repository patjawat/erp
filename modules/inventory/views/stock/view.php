<?php
use yii\helpers\Html;
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-cubes-stacked"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('../default/menu') ?>
<?php $this->endBlock(); ?>
<div class="card">
    <div class="card-body">
        <?=$model->product->Avatar()?>
        <br>
        <table
            class="table table-primary"
        >
            <thead>
                <tr>
                    <th scope="col" style="width:20px">#</th>
                    <th>ล็อต</th>
                    <th>รับเข้า</th>
                    <th>รับเข้า</th>
                    <th class="text-center" style="width:100px">คงเหลือ</th>
                    <th class="text-end" style="width:200px">ราคาต่อหน่วย</th>
                </tr>
            </thead>
            <tbody class="align-middle">
                <?php $num =1; foreach($model->listLotNumber() as $model):?>
                <tr class="">
                    <td scope="row"><?=$num++?></td>
                    <td><?=$model->lot_number?></td>
                    <td><?=$model->order->order->created_at?></td>
                    <td><?=$model->warehouse_id?></td>
                    <td class="text-center"><?=$model->qty?></td>
                    <td class="text-end fw-bolder"><?=number_format($model->unit_price,2)?></td>
                </tr>
                <?php endforeach;?>
            </tbody>
        </table>
    
    </div>
</div>
