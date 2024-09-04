<?php
use yii\helpers\Html;
use app\modules\inventory\models\Warehouse;
?>
<div class="card">
  <div class="card-body">
<h6><i class="bi bi-ui-checks"></i> คลังวัสดุ <span class="badge rounded-pill text-bg-primary"> <?=count(Warehouse::find()->all())?> </span> รายการ</h6>
<table class="table">
  <thead>
    <tr>
      <th scope="col">#</th>
      <th>รายการ</th>
      <th>ผู้รับผิดชอบคลัง</th>
      <th class="text-center">คำขอเบิก</th>
      <th>ปริมาณการเบิก/จ่าย</th>
    </tr>
  </thead>
  <tbody class="align-middle">
    <?php $i = 1; foreach(Warehouse::find()->all() as $model):?>
    <tr>
      <th scope="row"><?=$i++;?></th>
      <td><?=Html::a($model->warehouse_name,['/inventory/warehouse/view','id' => $model->id])?></td>
      <td> <?= $model->avatarStack() ?></td>
      <td class="text-center"> <span class="badge rounded-pill text-bg-primary"> 0 </span></td>
      <td>
      <div class="progress-stacked">
  <div class="progress" role="progressbar" aria-label="Segment one" aria-valuenow="15" aria-valuemin="0" aria-valuemax="100" style="width: 70%">
    <div class="progress-bar"></div>
  </div>
  <div class="progress" role="progressbar" aria-label="Segment two" aria-valuenow="30" aria-valuemin="0" aria-valuemax="100" style="width: 30%">
    <div class="progress-bar bg-warning"></div>
  </div>

</div>
      </td>
    </tr>
    <?php endforeach;?>
  </tbody>
</table>
  </div>
</div>