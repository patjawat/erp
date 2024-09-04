<?php
use yii\helpers\Html;
?>

            <table class="table table-striped">
                <thead>
                    <tr>
                        <th style="width:500px">
                            <?= Html::a('<i class="fa-solid fa-circle-plus"></i> เลือกรายการ', ['/inventory/stock-request/list-product', 'id' => $model->id, 'title' => 'รายการวัสดุคลัง'], ['class' => 'btn btn-sm btn-primary rounded-pill open-modal', 'data' => ['size' => 'modal-xl']]) ?>
                        </th>
                        <th class="text-center" style="width:100px">คงเหลือ</th>
                        <th class="text-center" style="width:100px">จำนวเบิก</th>
                        <th class="text-center" style="width:80px">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody class="table-group-divider">
                    <?php foreach ($model->ListItemRequest() as $item): ?>
                    <tr class="">
                        <td class="align-middle"><?php echo $item->product->Avatar(false);?></td>
                        <td class="align-middle text-center"><?=$item->qty;?></td>
                        <td class="align-middle text-center"><?=$item->qty;?></td>
                        <td class="align-middle gap-2">
                            <div class="d-flex justify-content-center gap-2">
                                <?=Html::a('<i class="fa-solid fa-xmark"></i>',['/inventory/stock-request/delete','id' => $item->id],['class' => 'btn btn-sm btn-danger rounded-pill delete-item'])?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
