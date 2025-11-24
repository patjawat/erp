<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\modules\inventory\models\Warehouse;
use app\modules\inventory\models\StockEvent;
?>
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h6><i class="bi bi-ui-checks"></i> คลังวัสดุ <span class="badge rounded-pill text-bg-primary">
                    <?=count(Warehouse::find()->all())?> </span> รายการ</h6>
            <div class="dropdown float-end">
                <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <i class="fa-solid fa-ellipsis"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <?= Html::a('<i class="fa-solid fa-circle-info text-primary me-2"></i> ตั้งค่า', ['/inventory/warehouse'], ['class' => 'dropdown-item']) ?>
                </div>
            </div>
        </div>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th class="text-end" scope="col">#</th>
                    <th>รายการ</th>
                    <th>ผู้ดูแล</th>
                    <th class="text-center">รอดำเนินการ</th>
                    <th class="text-end">มูลค่าวัสดุคงคลัง</th>
                </tr>
            </thead>
            <tbody class="align-middle table-group-divider">
                <?php $i = 1; foreach($dataProviderWarehouse->getModels() as $model):?>
                <tr>
                    <th class="text-end" scope="row"><?=$i++;?></th>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar">
                                <div class="avatar-title rounded bg-primary bg-opacity-25">
                                    <i class="fa-solid fa-store text-primary"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <a href="<?=Url::to(['/inventory/warehouse/view','id' => $model->id])?>">
                                    <h6 class="mb-0 font-size-15"><?=$model->warehouse_name?></h6>
                                    <span
                                        class="text-muted mb-0 text-truncate"><?=$model->warehouse_type == 'MAIN' ? 'คลังหลัก' : 'คลังย่อย'?></span>
                                </a>
                            </div>
                        </div>
                        <?php // Html::a($model->warehouse_name,['/inventory/warehouse/view','id' => $model->id])?>

                    </td>
                    <td> <?= $model->avatarStack() ?></td>
                    <td class="text-center">
                        <?php if($model->countOrderRequest() > 0):?>
                            <span class="badge rounded-pill text-bg-primary"><?=$model->countOrderRequest()?> </span>
                        <?php else:?>

                        <?php endif;?>
                    </td>
                    <td class="text-end">
                        <span class="fw-semibold "> <?php // echo $model->SumPiceStockWarehouse()?></span>
                        <span class="fw-semibold "> 
                            <?php 

                            $params = [
                                ':date_start' => $dateStart,
                                ':date_end' => $dateEnd,
                            ];
                            $conditions = [
                                "e.name = 'order'",
                                "wi.warehouse_type = 'MAIN'",
                                "i.asset_item IS NOT NULL",

                            ];
                            $warehouseId = $model->id;
                            if (!empty($warehouseId)) {
                                $conditions[] = "e.warehouse_id = :warehouse_id";
                                $params[':warehouse_id'] = $warehouseId;
                            }

                        // ----- Auto GROUP / ORDER -----
                        $groupBy = '';

                        list($sql, $params) = StockEvent::buildStockOrderSql(
                            $conditions,
                            $params,
                            $groupBy ?? null,
                            $orderBy ?? null
                        );

        $querys = Yii::$app->db->createCommand($sql, $params)->queryOne();
                        echo number_format($querys['end_price'] ?? 0,2);
 
                            ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach;?>
            </tbody>
        </table>
    </div>
</div>