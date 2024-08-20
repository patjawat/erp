<?php

use app\components\AppHelper;
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\modules\inventory\models\StockEvent;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockEvent $model */

$this->title = 'เลขที่รับเข้า : ' . $model->code;
$this->params['breadcrumbs'][] = ['label' => 'Stock Ins', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<?php Pjax::begin(['id' => 'inventory']); ?>
<div class="row">
    <div class="col-8">
        <div class="card">
            <div class="card-body">

                <div class="d-flex justify-content-between">
                    <h6><i class="bi bi-ui-checks"></i> รับเข้า <span class="badge rounded-pill text-bg-primary"><?=count($model->getItems())?> </span> รายการ</h6>
                    <?=Html::a('<i class="fa-solid fa-circle-plus"></i> เลืกอรายการ',['/inventory/stock-in/create','order_id' => $model->id,'name' => 'order_item','title' => 'เพิ่มรายการ'],['class' => 'btn btn-sm btn-primary rounded-pill shadow open-modal','data' => ['size' => 'modal-md']])?>
                </div>
                <table class="table table-striped mt-3">
                <thead class="table-primary">
                    <tr>
                        <th>
                            รายการ
                        </th>
                        <th class="text-center">หน่วย</th>
                        <th class="text-center">ประเภท</th>
                        <th class="text-end">ราคาต่อหน่วย</th>
                        <th class="text-center">จำนวน</th>
                        <th class="text-center">ล็อตผลิต</th>
                        <th class="text-center">วันผลิต</th>
                        <th class="text-center">วันหมดอายุ</th>
                        <th class="text-center" scope="col" style="width: 120px;">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody class="table-group-divider">
                    <?php foreach ($model->getItems() as $item): ?>
                    <tr class="<?=$item->order_status == 'pending' ? 'bg-warning-subtle' : ''?>">
                        <td class="align-middle">
                            <?php
                            try {
                                echo $item->product->Avatar();
                            } catch (\Throwable $th) {}
                            ?>
                        </td>

                        <td class="align-middle text-center">
                            <?=isset($item->product->data_json['unit']) ? $item->product->data_json['unit'] : '-'?></td>
                        <td class="align-middle text-center">
                            <?=isset($item->data_json['item_type']) ? $item->data_json['item_type'] : '-'?></td>
                        <td class="align-middle text-end">
                            <?=isset($item->unit_price) ? number_format($item->unit_price, 2) : '-' ?></td>

                        <td class="align-middle text-center"><?= $item->qty ?></td>

                        <td class="align-middle text-center"><?= $item->lot_number ?></td>
                        <td class="align-middle text-center">
                            <?= isset($item->data_json['mfg_date']) ? AppHelper::convertToThai($item->data_json['mfg_date']) : '-' ?></td>
                        <td class="align-middle text-center">
                            <?= isset($item->data_json['exp_date']) ? AppHelper::convertToThai($item->data_json['exp_date']) : '-' ?></td>

                        <td class="align-middle">
                            <div class="d-flex justify-content-center gap-2">
                                <?php if($item->order_status == 'pending'):?>
                                <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['/inventory/stock-in/update', 'id' => $item->id,'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'], ['class' => 'btn btn-sm btn-primary shadow rounded-pill open-modal', 'data' => ['size' => 'modal-md']]) ?>
                                <?= Html::a('<i class="fa-regular fa-trash-can"></i>', ['/inventory/stock-in/delete', 'id' => $item->id], ['class' => 'btn btn-sm btn-danger shadow rounded-pill delete-item']) ?>
                                <?php else:?>
                                    <span>ดำเนินการแล้ว</span>
                                    <?php endif?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>

                </tbody>
            </table>
            
            </div>
        </div>
        <div class="form-group mt-3 d-flex justify-content-center">
            <?=($model->isPending() >= 1) ? Html::a('<i class="bi bi-check2-circle"></i> บันทึกรับเข้า',['/inventory/stock-in/confirm-order','id' => $model->id],['class' => 'btn btn-primary rounded-pill shadow confirm-order']) : ''?>
    </div>
    </div>
    <div class="col-4">
        <!-- Star Card -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h6><?= Html::encode($this->title) ?></h6>
                    <div class="dropdown float-end">
                        <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="fa-solid fa-ellipsis"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <?= Html::a('<i class="fa-regular fa-pen-to-square me-2"></i> แก้ไข', ['/inventory/stock-in/update', 'id' => $model->id, 'title' => 'แก้ไขใบรับเข้า'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']]) ?>
                        </div>
                    </div>
                </div>

                <?= DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        'order_status',
                        'name',
                        'code',
                    ],
                ]) ?>

            </div>
        </div>
        <!-- End Card -->
        <!-- committee -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h6><i class="bi bi-person-circle"></i> กรรมการตรวจรับเข้าคลัง</h6>
                </div>
                <?=$model->StackComittee()?>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <?= Html::a('รายการ', [
                            '/inventory/committee/list','id' => $model->id,'title' => '<i class="bi bi-person-circle"></i> กรรมการกำหนดรายละเอียด'
                        ], ['class' => 'open-modal','data' => ['size' => 'modal-lg']]) ?>
                <?= Html::a('<i class="fa-solid fa-circle-plus me-1"></i> เพิ่มกรรมการ', ['/inventory/committee/create', 'id' => $model->id, 'action' => 'create','name' => 'receive_committee', 'title' => '<i class="fa-regular fa-pen-to-square"></i> กรรมการตรวจรับเข้าคลัง'], ['class' => 'btn btn-sm btn-primary rounded-pill open-modal', 'data' => ['size' => 'modal-md']]) ?>
            </div>
        </div>
        <!-- End Committee -->
    </div>
</div>

<?php

$js = <<< JS

               
$('.confirm-order').click(async function (e) { 
    e.preventDefault();

  await Swal.fire({
    title: "ยืนยัน?",
    text: "บันทึกรายการนี้!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "ใช่, ยืนยัน!",
    cancelButtonText: "ยกเลิก",
  }).then(async (result) => {
    if (result.value == true) {
      await $.ajax({
        type: "post",
        url: $(this).attr('href'),
        dataType: "json",
        success: async function (response) {
          if (response.status == "success") {
            await  $.pjax.reload({container:response.container, history:false,url:response.url});
            success("บันสำเร็จ!.");
          }
        },
      });
    }
  });

  });

JS;
$this->registerJS($js)
?>

<?php Pjax::end()?>