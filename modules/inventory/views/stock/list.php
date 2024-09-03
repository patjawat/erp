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

$this->title = 'Stocks';
$this->params['breadcrumbs'][] = $this->title;
?>

    <?php Pjax::begin(); ?>
    
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between">
          <h6><i class="bi bi-ui-checks"></i> วัสดุในสต๊อก <span class="badge rounded-pill text-bg-primary"> <?=$dataProvider->getTotalCount();?> </span> รายการ</h6>
          <div class="d-flex">
            
            <?php  echo $this->render('_search', ['model' => $searchModel]); ?>
            <?php echo Html::a('แสดงท้ังหมด', ['/inventory/stock/warehouse'], ['class' => 'btn btn-sm btn-light rounded-pill','data' => ['pjax' => 0]]) ?>
          </div>
        </div>
      <table class="table">
        <thead>
          <tr>
            <th scope="col">ชื่อรายการ</th>
            <th scope="col" class="text-center">มูลค่า</th>
            <th scope="col" class="text-center">คงเหลือ</th>
          </tr>
        </thead>
        <tbody>
            <?php foreach($dataProvider->getModels() as $item):?>
          <tr>
            <th scope="row">
              <?=Html::a($item->product->Avatar(),['/inventory/stock/view','id' => $item->id])?></th>
            <td class="text-center"><?=$item->SumPriceByItem()?></td>
            <td class="text-center"><?=$item->SumQty()?></td>
          </tr>
          <?php endforeach;?>
        </tbody>
      </table>
      </div>
    </div>
    <?php Pjax::end(); ?>

