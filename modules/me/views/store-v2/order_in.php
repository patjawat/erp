<?php
use yii\helpers\Html;

$title = '';
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-clipboard-user fs-1"></i> <?php echo $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php  echo $this->render('@app/modules/me/views/store-v2/menu') ?>
<?php $this->endBlock(); ?>
<div class="card">
    <div class="card-body">


        <div class="row">
            <div class="col-4 d-flex justify-content-start align-items-center">
                <h6><i class="bi bi-ui-checks"></i> จำนวนใบเบิก <span class="badge rounded-pill text-bg-primary"><?= $dataProvider->getTotalCount(); ?> </span> รายการ</h6>
            </div>
            <div class="col-4">
                <?= $this->render('_search_order_in', ['model' => $searchModel]); ?>
            </div>
            
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">รายการ</th>
                            <th scope="col">ผู้ขอเบิก</th>
                            <th scope="col">คลังหลัก</th>
                            <th scope="col">มูลค่า</th>
                            <th scope="col">สถานะ</th>
                            <th class="text-center">ดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody class="table-group-divider">
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
                                   echo $item->CreateBy($item->fromWarehouse->warehouse_name.' | '.$item->viewCreated())['avatar'];
                                } catch (\Throwable $th) {
                                }
                                ?>
                            </td>
                            <td><?php echo $item->warehouse->warehouse_name?></td>
                            <td>

                            <span class="fw-semibold"><?php echo number_format($item->getTotalOrderPrice(),2)?></span>
                                
                            </td>
                            <td><?php echo $item->viewStatus()?></td>
                            <td class="text-center">
                                <?php echo Html::a('<i class="fa-solid fa-pen-to-square fs-3"></i>',['/me/store-v2/view','id' => $item->id])?>
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