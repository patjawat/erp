<?php
use yii\helpers\Html;
?>
<style>
 
</style>
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th style="width:500px">รายการ(คลัง)</th>
                        <th class="text-center" style="width:100px">จำนวนคงเหลือ</th>
                        <th class="text-center" style="width:100px">จำนวนเบิก</th>
                        <th class="text-center" style="width:80px">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody class="table-group-divider">
                    <?php foreach ($model->ListProductFormwarehouse() as $item): ?>
                    <tr class="">
                        <td class="align-middle"><?php echo $item->product->Avatar(false);?></td>
                        <td class="align-middle text-center">
                            <span  id="total-<?=$item->id?>">
                                <?php
                            try {
                                echo $item->sum_qty; 
                            } catch (\Throwable $th) {
                                //throw $th;
                            } 
                            ?>
                            </span>
                        </td>
                        <td class="align-middle text-center">
                          <div class="mb-3 d-flex flex-row">
                          <button class="btn btn-light">-</button>
                            <input
                                type="number"
                                class="form-control text-center"
                                id="qty-<?=$item->id?>"
                                placeholder="0"
                            />
                            <button class="btn btn-light">+</button>
                          </div>
                          
                        </td>
                        <td class="align-middle gap-2">
                            <div class="d-flex justify-content-center gap-2">
                                <?=Html::button('click',['class' => 'btn btn-sm btn-primary rounded-pill add-product',
                                    'data-id' => $item->id,
                                    'data-rq_number' => $model->rq_number,
                                    'data-total' =>  $item->sum_qty,
                                    'data-product_id' =>  $item->product_id
                                ])?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
        </div>
    </div>
</div>