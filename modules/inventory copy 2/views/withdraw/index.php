<?php
use yii\helpers\Html;
/** @var yii\web\View $this */
?>
<div class="card h-100">
        <div class="card-body">
          <?=Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างคำขอเบิก',['/inventory/withdraw/create','title' => '<i class="fa-solid fa-circle-plus"></i> สร้างคำขอเบิก'],['class' => 'btn btn-primary rounded-pill shadow open-modal','data' => 'modal-md'])?>
            
        </div>
      </div>
  
      <div class="card h-100">
        <div class="card-body">
        <h6><i class="bi bi-ui-checks"></i> ทะเบียนเบิกจากคลังหลัก <span class="badge rounded-pill text-bg-primary"><?=$dataProvider->getTotalCount()?> </span> รายการ</h6>
           <table class="table">
             <thead>
               <tr>
                 <th scope="col">รายการ</th>
                 <th scope="col">ผู้อนุมัติ</th>
                 <th scope="col">คลัง</th>
                 <th scope="col" class="text-center" style="width:100px">ดำเนินการ</th>
               </tr>
             </thead>
             <tbody>
              <?php foreach($dataProvider->getMOdels() as $item):?>
               <tr>
                 <th scope="row">
                  <?php echo $item->CreateBy('ผู้ขอเบิก')['avatar']?>
                 </th>
                 <td><?php echo $item->viewChecker()?></td>
                 <td><?php echo $item->tomWarehouse()?></td>
                 <td class="text-center"><?=Html::a('<i class="bi bi-eye text-primary"></i>',['/inventory/withdraw/view','id' => $item->id],['class' => 'btn btn-light'])?></td>
               </tr>
               <?php endforeach;?>
             </tbody>
           </table>
        </div>
      </div>
  