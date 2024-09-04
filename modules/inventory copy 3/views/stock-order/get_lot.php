<?php
use app\components\AppHelper;
use yii\bootstrap5\Html;

?>
<table class="table">
    <thead>
        <tr>
            <th scope="col">ล็อตผลิต</th>
            <th scope="col">ผลิต</th>
            <th scope="col">หมดอายุ</th>
            <th scope="col">คงเหลือ</th>
            <th scope="col">ดำเนินการ</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($model->listLots() as $item):?>
        <tr>
            <th scope="row"><?=$item->lot_number?></th>
            <td class="align-middle text-center">
                <?= isset($item->data_json['mfg_date']) ? AppHelper::convertToThai($item->data_json['mfg_date']) : '-' ?>
            </td>
            <td class="align-middle text-center">
                <?= isset($item->data_json['exp_date']) ? AppHelper::convertToThai($item->data_json['exp_date']) : '-' ?>
            </td>
            <td><?=$item->qty?></td>
            <td>
                <?=Html::a('เลือก',['/inventory/stock-out/set-lot'],['class' => 'btn btn-sm btn-primary shadow'])?>
            </td>
        </tr>
        <?php endforeach;?>
    </tbody>
</table>