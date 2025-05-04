<?php
use yii\helpers\Html;
?>
<div class="card mt-4">
        <div class="card-header bg-light p-2">
            <div class="d-flex align-items-center justify-content-between">
                <strong><i class="fa-solid fa-money-bill-1 me-2"></i> ค่าใช้จ่าย</strong>
                <?=Html::a('<i class="fa-solid fa-circle-plus"></i> เพิ่ม',['/me/development-detail/create','name' => 'expense_type','development_id' => $model->id,'title' => '<i class="fa-solid fa-money-bill-1 me-2"></i> ค่าใช้จ่าย'],['class' => 'btn btn-sm btn-primary rounded-pill shadow open-modal','data' => ['size' => 'modal-md']])?>
            </div>
        </div>
        <div class="card-body">
            <div
                class="table-responsive pb-5"
            >
                <table
                    class="table table-primary"
                >
                    <thead>
                        <tr>
                            <th scope="col">ลำดับ</th>
                            <th scope="col">รายการ</th>
                            <th scope="col">จำนวนเงิน</th>
                            <th scope="col">หมายเหตุ</th>
                            <th scope="col" style="width:100px">ดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($model->expenses as $key => $item):?>
                        <tr class="">
                            <td scope="row"><?=$key+1?></td>
                            <td><?=$item->expenseType?->title?></td>
                            <td><?=isset($item->price) && is_numeric($item->price) ? number_format($item->price, 2) : '-'?></td>
                            <td><?= isset($item->data_json['note']) ? Html::encode($item->data_json['note']) : '-' ?></td>
                           <td class="fw-light text-center">
                           <div class="btn-group">
                               <?= Html::a('<i class="fa-solid fa-pen-to-square"></i>', ['/me/development-detail/update', 'id' => $item->id,'title' => 'แก้ไข'], ['class' => 'btn btn-light w-100 open-modal','data' => ['size' => 'modal-md']]) ?>
                               <button type="button" class="btn btn-light dropdown-toggle dropdown-toggle-split"
                                   data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent">
                                   <i class="bi bi-caret-down-fill"></i>
                               </button>
                               <ul class="dropdown-menu">
                                   <li><?php echo Html::a('<i class="fa-solid fa-circle-xmark me-1"></i> ลบ',['/me/development-detail/delete','id' => $item->id,'title' => '<i class="fa-regular fa-pen-to-square"></i> ลบ'],['class' => 'dropdown-item delete-item','data' => ['size' => 'modal-lg']])?>
                                   </li>
                               </ul>
                           </div>
                       </td>
                        </tr>
                        <?php endforeach?>
                    </tbody>
                </table>
            </div>
            
        </div>
    </div>