<?php

use yii\helpers\Html;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockEventSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$warehouse = Yii::$app->session->get('warehouse');
$this->title = $warehouse['warehouse_name'].'เบิกจากคลังหลัก';
$this->params['breadcrumbs'][] = $this->title;
$createIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-plus-2"><path d="M4 22h14a2 2 0 0 0 2-2V7l-5-5H6a2 2 0 0 0-2 2v4"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M3 15h6"/><path d="M6 12v6"/></svg>';
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-cubes-stacked"></i> <?php echo $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('@app/modules/me/views/store-v2/menu') ?>
<?php $this->endBlock(); ?>


  <?php Pjax::begin(); ?>
  <?php // echo $this->render('_search', ['model' => $searchModel]);
  ?>


  <div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between">
          <h6><i class="bi bi-ui-checks"></i> ขอเบิกจำนวน <span class="badge rounded-pill text-bg-primary"> <?php echo $dataProvider->getTotalCount(); ?></span> รายการ</h6>
          <div>
            <!-- <button class="btn btn-sm btn-primary rounded-pill"><i class="fa-solid fa-plus"></i>
                                เลือกรายการ</button> -->
          </div>

        </div>
        <table class="table table-primary mb-5">
          <thead>
            <tr>
              <th scope="col" style="width:155px">รหัส</th>
              <th class="text-center">ปีงบประมาณ</th>
              <th>รายการ</th>
              <th>คลัง</th>
              <th>มูลค่า</th>
              <th>สถานะ</th>
              <th class="text-center" style="width:100px">ดำเนินการ</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($dataProvider->getModels() as $item) { ?>
              <tr>
                <td><?php echo $item->code; ?></td>
                <td class="text-center"><?php echo $item->thai_year; ?></td>
                <td><?php echo $item->CreateBy($item->created_at)['avatar']; ?></td>
                <td><?php echo $item->warehouse->warehouse_name; ?></td>
                <td>
                  <?php
                  echo number_format($item->getTotalOrderPrice(), 2);
                try {
                } catch (Throwable $th) {
                }

                ?></td>
                <td><?php echo $item->viewstatus(); ?></td>
                <td class="text-center">
                <?php echo Html::a('<i class="fa-regular fa-pen-to-square text-primary"></i>', ['/inventory/stock-order/view', 'id' => $item->id], ['class' => 'btn btn-light']); ?>
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>

    </div>
  </div>
  <?php Pjax::end(); ?>
