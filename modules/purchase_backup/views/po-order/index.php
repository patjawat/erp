<?php

use app\modules\sm\models\Order;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\OrderSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'ระบบขอซื้อ';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="order-index">

<?php Pjax::begin(['id' => 'purchase-container']); ?>
    <?= $this->render('../default/menu2') ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>


    <div
        class="table-responsive"
    >
        <table
            class="table table-primary"
        >
            <thead>
                <tr>
                    <th>สถานะ</th>
                    <th>รหัสสังซื้อ (PO)</th>
                    <th>รหัสขอซื้อ (PR)</th>
                    <th>ประเภท</th>
                    <th>ผู้จำหน่าย</th>
                    <th>ผู้ขอซื้อ</th>
                    <th>หมายเหตุ</th>
                    <th>วันที่สั่งซื้อ</th>
                    <th>วันที่สร้าง</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dataProvider->getModels() as $model): ?>
                <tr class="">
                    <td><?= $model->viewPrStatus() ?></td>
                    <td>R1C2</td>
                    <td>R1C3</td>
                    <td>R1C3</td>
                    <td>R1C3</td>
                    <td>R1C3</td>
                    <td>R1C3</td>
                    <td>R1C3</td>
                    <td>R1C3</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    


    <?php Pjax::end(); ?>

</div>
