<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\Store $model */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Stores', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<?php yii\widgets\Pjax::begin(['id' => 'inventory', 'enablePushState' => false, 'timeout' => 5000]);?>
<div class="card h-100">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <?=$model->CreateBy('ผู้ขอเบิก')['avatar']?>
            <div class="dropdown">
                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <i class="fa-solid fa-ellipsis-vertical"></i>
                </button>
                <div class="dropdown-menu">
                    <?php echo  Html::a('<i class="fa-regular fa-pen-to-square me-1"></i>แก้ไข', ['/inventory/withdraw/update', 'id' => $model->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']])?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
  <div class="card-body">

<table class="table">
  <thead>
    <tr>
      <th scope="col">รายการ</th>
      <th scope="col">ขอเบิก</th>
      <th scope="col" class="text-center">จำนวนจ่าย</th>
      <th scope="col">ล็อตผลิต</th>
      <th scope="col" class="text-center" style="width:100px">ดำเนินการ</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($model->ListItems() as $item):?>
    <tr>
      <th scope="row"><?=$item->product->Avatar()?></th>
      <td><?=isset($item->data_json['amount_withdrawal']) ? $item->data_json['amount_withdrawal'] : 0 ?></td>
      <td class="text-center"><?=$item->qty?></td>
      <td><?=$item->lot_number?></td>
      <td class="text-center">        <?php echo Html::a('จ่าย',['/inventory/stock/list-item-in-stock','id' => $item->id],['class' => 'btn btn-sm btn-primary position-relative rounded-pill shadow open-modal','data' => ['size' => 'modal-lg']])?></td>
    </tr>
    <?php endforeach;?>
  </tbody>
</table>
</div>
</div>

<?php yii\widgets\Pjax::end();?>