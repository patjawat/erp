<?php

use app\modules\inventory\models\Store;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StoreSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Stores';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(); ?>
<?php // echo $this->render('_search', ['model' => $searchModel]); 
?>

<div class="card" style="height:533px;">
    <div class="card-body">
        <h6><i class="bi bi-ui-checks"></i> วัสดุในคลัง <span class="badge rounded-pill text-bg-primary"> <?= $dataProvider->getTotalCount() ?> </span> รายการ</h6>
        <div
            class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">รายการ</th>
                        <th class="text-center">จำนวน</th>
                        <th>หน่วย</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dataProvider->getModels() as $item): ?>
                        <tr class="">
                            <td scope="row"><?= $item->product->Avatar() ?></td>
                            <td class="text-center"><?= $item->qty ?></td>
                            <td class="text-center"><?= isset($item->product->data_json['unit']) ? $item->product->data_json['unit'] : '-' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center">
    <?= yii\bootstrap5\LinkPager::widget([
        'pagination' => $dataProvider->pagination,
        'firstPageLabel' => 'หน้าแรก',
        'lastPageLabel' => 'หน้าสุดท้าย',
        'options' => [
            'class' => 'pagination pagination-sm',
        ],
    ]); ?>
    </div>
    
    </div>
</div>

<?php Pjax::end(); ?>

</div>