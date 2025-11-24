<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\modules\inventory\models\Stock;
use app\modules\inventory\models\Product;
/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Stocks';
$this->params['breadcrumbs'][] = $this->title;
?>
    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Stock', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php  echo $this->render('_search_show_stock', ['model' => $searchModel]); ?>
    <div class="card">
        <div class="card-body">

    
<table class="table table-bordered">
    <thead>
        <tr>
            <th>ชื่อวัสดุ</th>
            <th class="text-center">จำนวนรับเข้า</th>
            <th class="text-end">มูลค่ารับเข้า</th>
            <th class="text-center">จำนวนจ่าย</th>
            <th class="text-end">มูลค่าจ่าย</th>
            <th class="text-center">คงเหลือ</th>
            <th class="text-end">รวมมูลค่า</th>
        </tr>
    </thead>
    <tbody class="table-group-divider align-middle">
        <?php foreach ($items as $item): ?>
        <tr>
            <td>
            <?php
            // $prodcut = Product::findOne(['code' => $item['asset_item'],'name' => 'asset_item']);
            // if($prodcut){
            //     echo $prodcut->Avatar(false);
            // }
            echo $item['title'];
            ?> </td>
            <td class="text-center"><?= $item['qty_in'] ?></td>
            <td class="text-end"><?= Yii::$app->formatter->asDecimal($item['total_price_in'], 2) ?></td>
            <td class="text-center"><?= $item['qty_out'] ?></td>
            <td class="text-end"><?= Yii::$app->formatter->asDecimal($item['total_price_out'], 2) ?></td>
            <td class="text-center"><?= $item['result_qty'] ?></td>
            <td class="text-end"><?= Yii::$app->formatter->asDecimal($item['result_price'], 2) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
        </div>
    </div>

    <?php Pjax::end(); ?>
