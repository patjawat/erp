<?php
use yii\helpers\Html;
$warehouse = Yii::$app->session->get('warehouse');

$this->title = 'คลัง'.$warehouse->warehouse_name;

$cart = Yii::$app->cartSub;
$products = $cart->getItems();
?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-shop fs-1"></i> <?php echo $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('@app/modules/me/views/store-v2/menu') ?>
<?php $this->endBlock(); ?>

<div class="card">
    <div class="card-body">


        <div class="row">
            <div class="col-4 d-flex justify-content-start align-items-center">
                <h6><i class="bi bi-ui-checks"></i> จำนวนใบเบิก <span class="badge rounded-pill text-bg-primary"><?= $dataProvider->getTotalCount(); ?> </span> รายการ</h6>
            </div>
            <div class="col-4"><?= $this->render('_search_order_in', ['model' => $searchModel]); ?></div>

            </div>
            <div class="table-responsive">
                <table class="table table-primary">
                    <thead>
                        <tr>
                            <th scope="col">รายการ</th>
                            <th scope="col">ผู้ขอเบิก</th>
                            <th scope="col">เหตุผล</th>
                            <th scope="col">คลัง</th>
                            <th scope="col">มูลค่า</th>
                            <th scope="col">สถานะ</th>
                            <th class="text-center">ดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($dataProvider->getModels() as $item):?>
                        <tr class="">
                            <td>
                                <div class="d-flex flex-column">
                                    <div class="fw-semibold"><?php echo $item->code?></div>
                                    <div><?php echo $item->viewCreatedAt()?></div>
                                </div>
                            </td>
                            <td>
                                <?php
                                try {
                                    echo $item->CreateBy($item->warehouse->warehouse_name.' | '.$item->viewCreated())['avatar'];
                                } catch (\Throwable $th) {
                                }
                                ?>
                            </td>
                            <td><?php echo $item->data_json['note'] ?? '-'?></td>
                            <td><?php echo $item->warehouse->warehouse_name?></td>
                            <td><?php echo $item->getTotalOrderPrice()?></td>
                            <td><?php echo $item->viewStatus()?></td>
                            <td class="text-center">
                                <?php echo Html::a('<i class="fa-solid fa-eye fs-3"></i>',['/me/store-v2/view','id' => $item->id])?>
                            </td>
                        </tr>
                        <?php endforeach;?>
                    </tbody>
                </table>
            </div>

        </div>
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