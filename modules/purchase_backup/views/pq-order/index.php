<?php

use app\modules\purchase\models\Order;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\purchase\models\OrderSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'Orders';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'purchase-container']); ?>

    <?= $this->render('../default/menu2') ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>


    <div class="card">
        <div class="card-body">

    
    <div
        class="table-responsive"
    >
        <table
            class="table table-primary"
        >
            <thead >
            <tr>
                    <th class="fw-semibold">สถานะ</th>
                    <th class="fw-semibold">รหัสขอซื้อ (PR)</th>
                    <th class="fw-semibold">เลขทะเบียนคุม</th>
                    <th class="fw-semibold">ประเภท</th>
                    <th class="fw-semibold">ผู้จำหน่าย</th>
                    <th class="fw-semibold">ผู้ขอซื้อ</th>
                    <th class="fw-semibold">ความคืบหน้า</th>
                    <th class="fw-semibold">หมายเหตุ</th>
                    <th class="fw-semibold text-center">วันที่ใช้งาน</th>
                    <th class="fw-semibold text-center">วันที่ขอซื้อ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dataProvider->getModels() as $model): ?>
                <tr class="">
                    <td class="fw-light"><?= $model->viewPrStatus() ?></td>
                    <td class="fw-light"><?= Html::a($model->pr_number, ['/purchase/pr-order/view', 'id' => $model->id], ['class' => 'fw-bolder']) ?></td>
                    <td class="fw-light"><?= Html::a($model->pq_number, ['/purchase/pq-order/view', 'id' => $model->id], ['class' => 'fw-bolder']) ?></td>
                    <td class="fw-light">
                                <?php
                                try {
                                    echo $model->data_json['product_type_name'];
                                } catch (\Throwable $th) {
                                }
                                ?></td>
                    <td class="fw-light">
                        <?php
                        try {
                            $model->vendor->title;
                        } catch (\Throwable $th) {
                            // throw $th;
                        }
                        ?>

</td>
                    <td class="fw-light"><?php // $model->getUserReq()['avatar'] ?></td>
                    <td class="fw-light"><?php //  $model->data_json['comment'] ?></td>
                    <td class="fw-light">

                                <div class="progress" style="height: 5px;">
                                    <div class="progress-bar" role="progressbar" aria-label="Progress"
                                        style="width: 50%;" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>
                            </td>
                    <td class="text-center fw-light"><?php // $model->viewDueDate() ?></td>
                    <td class="text-center fw-light"><?php //  $model->viewCreatedAt() ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    </div>
    </div>
    

    <?php Pjax::end(); ?>

