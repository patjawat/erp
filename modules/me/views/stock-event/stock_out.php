<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\modules\inventory\models\StockEvent;
/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockEventSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Stock Events';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="stock-event-index">

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <div
        class="table-responsive"
    >
        <table
            class="table table-striped table-hover"
        >
            <thead>
                <tr>
                    <th scope="col">รายการ</th>
                    <th scope="col">วัน/เวลา</th>
                </tr>
            </thead>
            <tbody class="align-middle table-group-divider">
                <?php foreach($dataProvider->getModels() as $item):?>
                <tr class="">
                    <td scope="row">
                        <?php
                        $msg = $item->data_json['note'] ?? '-';
                        ?>
                        <?php echo $item->CreateBy($msg)['avatar']?>
                    </td>
                    <td>
                        <p class="text-muted mb-0 fs-13"><?php echo $item->viewCreatedAt();?></p>
                        <?php echo Html::a('<span class="badge rounded-pill badge-soft-primary text-primary fs-13 "><i class="bi bi-exclamation-circle-fill"></i> เพิ่มเติม...</span>',['/me/stock-event/view','id' => $item->id],['class' => 'open-modal','data' => ['size' => 'modal-lg']])?>
                    </td>

                </tr>
                <?php endforeach;?>
            </tbody>
        </table>
    </div>
    

    <?php Pjax::end(); ?>

</div>
