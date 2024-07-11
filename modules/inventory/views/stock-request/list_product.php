<?php
use yii\helpers\Html;
?>
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th style="width:500px">รายการ(คลัง)</th>
                        <th class="text-center" style="width:100px">จำนวนคงเหลือ</th>
                        <th class="text-center" style="width:80px">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody class="table-group-divider">
                    <?php foreach ($model->ListProductFormwarehouse() as $item): ?>
                    <tr class="">
                        <td class="align-middle"><?php echo $item->product->Avatar(false);?></td>
                        <td class="align-middle text-center">
                            <?php
                            try {
                                echo $item->sum_qty; 
                            } catch (\Throwable $th) {
                                //throw $th;
                            } 
                            ?>
                        </td>
                        <td class="align-middle gap-2">
                            <div class="d-flex justify-content-center gap-2">
                                <?= Html::a('<i class="fa-solid fa-circle-plus"></i> เลือก', ['/inventory/receive/add-item', 'id' => $item->id, 'title' => '<i class="bi bi-ui-checks-grid"></i> เลือกรายการวัสดุเข้าคลัง'], ['class' => 'btn btn-sm btn-primary rounded-pill open-modal', 'data' => ['size' => 'modal-md']]) ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
        </div>
    </div>
</div>