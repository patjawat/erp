<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\modules\inventory\models\Warehouse;
?>
<div class="card">
  <div class="card-body">
    <div class="d-flex justify-content-between">
      <h6><i class="bi bi-ui-checks"></i> คลังวัสดุ <span class="badge rounded-pill text-bg-primary"> <?=count(Warehouse::find()->all())?> </span> รายการ</h6>
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
<table class="table">
  <thead>
    <tr>
      <th scope="col">#</th>
      <th>รายการ</th>
      <th>ผู้ดูแล</th>
      <th class="text-center">รอดำเนินการ</th>
      <th>ร้อยละปริมาณวัสดุ</th>
    </tr>
  </thead>
  <tbody class="align-middle">
    <?php $i = 1; foreach($dataProvider->getModels() as $model):?>
    <tr>
      <th scope="row"><?=$i++;?></th>
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
              <span class="text-muted mb-0 text-truncate"><?=$model->warehouse_type == 'MAIN' ? 'คลังหลัก' : 'คลังย่อย'?></span>
            </a>
                                </div>

                                <div class="flex-shrink-0">
                                    <div class="dropdown">
                                        <a class="dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="bx bx-dots-horizontal text-muted font-size-22"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end" style="">
                                            <a class="dropdown-item" href="#">Yearly</a>
                                            <a class="dropdown-item" href="#">Monthly</a>
                                            <a class="dropdown-item" href="#">Weekly</a>
                                            <a class="dropdown-item" href="#">Today</a>
                                        </div>
                                    </div>
                                </div>
                            </div>  
      <?php // Html::a($model->warehouse_name,['/inventory/warehouse/view','id' => $model->id])?>
    
    </td>
      <td> <?= $model->avatarStack() ?></td>
      <td class="text-center"> 
        <?php if($model->countOrderRequest() > 0):?>
      <a href="<?=Url::to(['/inventory/order-request','id' => $model->id])?>">
        <span class="badge rounded-pill text-bg-primary"><?=$model->countOrderRequest()?> </span>
    </a>
    <?php else:?>

      <?php endif;?>
      </td>
      <td>
      <div class="progress-stacked">
  <div class="progress" role="progressbar" aria-label="Segment one" aria-valuenow="<?=isset($model->TransactionStock()['progress']) ? $model->TransactionStock()['progress'] : 0?>" aria-valuemin="0" aria-valuemax="100" style="width: <?=isset($model->TransactionStock()['progress']) ? $model->TransactionStock()['progress'] : 0?>%">
    <div class="progress-bar"><?=isset($model->TransactionStock()['progress']) ? ($model->TransactionStock()['progress'] >=1 ? $model->TransactionStock()['progress'].'%' : '') : 0?></div>
  </div>


</div>
      </td>
    </tr>
    <?php endforeach;?>
  </tbody>
</table>
  </div>
</div>