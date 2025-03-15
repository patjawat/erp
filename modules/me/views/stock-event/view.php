<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockEvent $model */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Stock Events', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="stock-event-view">

</div>

<div
    class="table-responsive"
>
    <table
        class="table table-primary"
    >
        <thead>
            <tr>
                <th scope="col">รายการ</th>
                <th scope="col">ล็อต</th>
                <th scope="col" class="text-center">จำนวน</th>
                <th scope="col">หน่วย</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($model->ListItems() as $item):?>
            <tr class="">
                <td scope="row"><?php echo $item->product->Avatar();?></td>
                <td><?php echo $item->lot_number?></td>
                <td class="text-center"><?php echo $item->qty?></td>
                <td><?php echo $item->product->unit_name?></td>
            </tr>
            <?php endforeach;?>
        </tbody>
    </table>
</div>

