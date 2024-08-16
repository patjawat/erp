<?php

use app\modules\inventory\models\Stock;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Stock Movements';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="stock-movement-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Stock Movement', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <table
        class="table table-primary"
    >
        <thead>
            <tr>
                <th scope="col">รายการ</th>
                <th scope="col">รับเข้า</th>
                <th scope="col">เบิก</th>
                <th scope="col">Column 3</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($dataProvider->getModels() as $item):?>
            <tr class="">
                <td scope="row"><?=$item->product->Avatar()?></td>
                <td><?=$item->movement_type  == 'IN' ? $item->qty : ''?></td>
                <td><?=$item->movement_type  == 'OUT' ? $item->qty : ''?></td>
                <td>R1C3</td>
            </tr>
<?php endforeach;?>
        </tbody>
    </table>



    <?php Pjax::end(); ?>

</div>
