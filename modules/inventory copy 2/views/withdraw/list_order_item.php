<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\Pjax;
?>

<div class="card h-100">
        <div class="card-body">

        <?=Html::a('เพิ่มรายการ',['/inventory/withdraw/product-list'],['class' => 'btn btn-primary rounded-pill shadow open-modal','data' => ['size' => 'modal-lg']])?>


<table class="table">
      <thead>
        <tr>
          <th scope="col">รายการ</th>
          <th scope="col">จำนวนเบิก</th>
          <th scope="col">จำนวนจ่าย</th>
          <th scope="col" class="text-center" style="width:100px;">ดำเนินการ</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($model->ListItems() as $item):?>
        <tr>
          <th scope="row"><?=$item->product->Avatar()?></th>
          <td><?=isset($item->data_json['amount_withdrawal']) ? $item->data_json['amount_withdrawal'] : 0 ?></td>
          <td><?=$item->qty?></td>
          <td class="text-center">
            <?=Html::a('<i class="fa-regular fa-pen-to-square text-primary"></i>',['/inventory/withdraw/update-item','id' => $item->id,'title' => 'แก้ไขรายการ'],['class' => 'btn btn-sm btn-light open-modal','data' => ['size' => 'modal-md']])?>
            <?=Html::a('<i class="fa-solid fa-trash"></i>',['/inventory/withdraw/delete-item','id' => $item->id,'title' => 'แก้ไขรายการ'],['class' => 'btn btn-sm  btn-danger delete-order-item'])?>
        
        </td>
        </tr>
        <?php endforeach;?>
      </tbody>
    </table>

    </div>
      </div>
   