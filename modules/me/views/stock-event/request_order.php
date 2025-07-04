<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use yii\bootstrap5\LinkPager;
use app\modules\inventory\models\StockEvent;
$warehouse = Yii::$app->session->get('sub-warehouse');
$this->title = 'คลัง'.$warehouse->warehouse_name;

$cart = Yii::$app->cartSub;
$products = $cart->getItems();
?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-shop fs-4 text-primaryr"></i> <?php echo $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php  echo $this->render('@app/modules/me/menu') ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?php echo $this->render('@app/modules/me/menu',['active' => 'store']) ?>
<?php $this->endBlock(); ?>


<?php $this->beginBlock('action'); ?>
<?php echo $this->render('@app/modules/me/views/store-v2/menu') ?>
<?php $this->endBlock(); ?>



<div class="stock-event-index">

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <div class="card">
        <div class="card-body">



            <div class="d-flex justify-content-between">
                <div>

                    <h6><i class="bi bi-ui-checks"></i> ทะเบียนเบิกวัสดุคลังหลัก <span
                            class="badge rounded-pill text-bg-primary"> <?=$dataProvider->getTotalCount()?></span>
                        รายการ</h6>
                    <?php echo $this->render('_search_order', ['model' => $searchModel]); ?>
                </div>
                <div>
                    <?php // echo Html::a('<i class="fa-solid fa-angles-right"></i> แสดงท้ังหมด', ['/inventory/stock-order'], ['class' => 'btn btn-sm btn-light rounded-pill','data' =>['pjax' => 0]]) ?>
                    <?php echo Html::a('<i class="fa-solid fa-angles-right"></i> แสดงท้ังหมด', ['/me/stock-event/reuqest-order'], ['class' => 'btn btn-sm btn-light rounded-pill','data' =>['pjax' => 0]]) ?>
                </div>

            </div>

            <div class="table-responsive">



                <table class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <th style="width:210px">รหัส/วันที่</th>
                            <th scope="col">ผู้เบิก</th>
                            <th>หัวหน้าตรวจสอบ</th>
                            <th>คลังหลัก</th>
                            <th class="text-center" style="width:350px">สถานะ</th>
                            <th class="text-start">มูลค่า</th>
                            <th class="text-center" style="width:150px">ดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody class="align-middle table-group-divider">
                        <?php foreach ($dataProvider->getModels() as $item): ?>
                        <tr>
                            <td>
                                <div>
                                    <p class="fw-semibold mb-0"><?=$item->code?></p>
                                    <p class="text-muted mb-0 fs-13"><?=$item->viewCreatedAt()?></p>
                                </div>
                            </td>
                            <td>
                                <?php
                                try {
                                   echo $item->CreateBy($item->fromWarehouse->warehouse_name.' | '.$item->viewCreated())['avatar'];
                                } catch (\Throwable $th) {
                                }
                                ?>
                            </td>
                            <td><?=$item->viewChecker()['avatar']?></td>
                            <td>
                            <?php echo $item->warehouse->warehouse_name?></td>
                            <td class="text-start">
                                <?php  if($item->order_status == 'success' && isset($item->data_json['player'])):?>
                                <?php
                                    $datetime = \Yii::$app->thaiDate->toThaiDate($item->data_json['player_date'], true, false);
                                    $msg = $item->viewstatus().' '.$datetime;
                                    echo $item->ShowPlayer($msg)['avatar'];
                                    ?>
                                <?php else:?>
                                <?php echo $item->viewstatus()?>
                                <?php endif;?>
                            </td>
                            <td class="text-end">

                                <span class="fw-semibold mb-0">
                                    <?=number_format($item->getTotalOrderPrice(),2)?>
                            </td>
                            </span>
                            <td class="text-center">
                                <div class="btn-group">
                                    <?=Html::a('<i class="fa-regular fa-pen-to-square text-primary"></i>',['/me/store-v2/view','id' => $item->id],['class'=> 'btn btn-light'])?>
                                    <button type="button" class="btn btn-light dropdown-toggle dropdown-toggle-split"
                                        data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent">
                                        <i class="bi bi-caret-down-fill"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><?= Html::a('<i class="fa-solid fa-print me-1"></i> พิมพ์ใบเบิก', ['/inventory/document/stock-order','id' => $item->id], ['class' => 'dropdown-item open-modal','data-pjax' => '0','data' => ['size' => 'modal-xl']]) ?>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="d-flex justify-content-center mt-5">
                    <div class="text-muted">
                        <?= LinkPager::widget([
                            'pagination' => $dataProvider->pagination,
                            'firstPageLabel' => 'หน้าแรก',
                            'lastPageLabel' => 'หน้าสุดท้าย',
                            'options' => [
                                'listOptions' => 'pagination pagination-sm',
                                'class' => 'pagination-sm',
                            ],
                        ]); ?>
                    </div>
                </div>



            </div>


        </div>
    </div>

    <?php Pjax::end(); ?>

</div>