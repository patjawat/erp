 <?php

  use yii\web\View;
  use yii\helpers\Url;
  use yii\helpers\Html;

  $this->title = 'สรุปรายงานวัสดุคงคลังรายตัว';
  $this->params['breadcrumbs'][] = ['label' => 'ระบบคลัง', 'url' => ['/inventory/default/index']];
  $this->params['breadcrumbs'][] = $this->title;
  ?>


 <?php $this->beginBlock('page-title'); ?>
 <i class="fa-solid fa-cubes-stacked"></i> <?php echo $this->title; ?>
 <?php $this->endBlock(); ?>

 <?php $this->beginBlock('sub-title'); ?>
 <?php $this->endBlock(); ?>
 <?php $this->beginBlock('page-action'); ?>
 <?php echo $this->render('../default/menu_dashbroad'); ?>
 <?php $this->endBlock(); ?>
 <?php $this->beginBlock('navbar_menu'); ?>
 <?= $this->render('../default/menu_dashbroad', ['active' => 'report']) ?>
 <?php $this->endBlock(); ?>


 <!-- Header -->
 <style>
   table tfoot {
     position: sticky;
     bottom: 0;
     background-color: #f8f9fa;
   }
 </style>

 <div class="card">
   <div class="card-header bg-primary-gradient text-white d-flex align-items-center justify-content-between">
     <h6 class="text-white mt-2"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
     <p class="text-white">จำนวนข้อมูล <?= number_format(count($querys)) ?> รายการ</p>
   </div>
   <div class="card-body">
     <?php echo $this->render('_search_by_item', ['model' => $searchModel]); ?>
   </div>
 </div>

 <div class="card">
   <div class="card-header bg-primary-gradient text-white">
     <div class="d-flex justify-content-between">
       <h6 class="text-white mt-2">
         <i class="bi bi-ui-checks"></i> ทั้งหมด
         <span class="badge text-bg-light">
           <?= number_format(count($querys)) ?></span> รายการ
       </h6>
       </p>
     </div>
   </div>
   <div class="card-body p-0">

     <!-- Table -->
       <?php
        // เตรียมตัวแปรผลรวมก่อนวน loop
        $sum_begin_qty = 0;
        $sum_begin_price = 0;
        $sum_qty_in = 0;
        $sum_price_in = 0;
        $sum_qty_out = 0;
        $sum_price_out = 0;
        $sum_end_qty = 0;
        $sum_end_price = 0;
        ?>
 <div class="table-responsive" style="max-height: 600px;max-height: 600px; overflow: auto;">
            <table class="table table-striped table-hover table-bordered mb-0">
          <thead class="table-primary" style="position: sticky; top: 0; z-index: 10;">
           <tr>
             <th rowspan="2" style="width:40px;" class="text-center align-middle">ลำดับ</th>
             <th rowspan="2" style="width: 125px;" class="text-center align-middle">รหัสสินค้า</th>
             <th rowspan="2" class="text-center align-middle">รายการสินค้า</th>
             <th rowspan="2" class="text-center align-middle">ประเภทวัสดุ</th>
             <th colspan="2">ยอดยกมา</th>
             <th colspan="2">รับเข้า</th>
             <th colspan="2">จ่ายออก</th>
             <th colspan="2">คงเหลือสิ้นเดือน</th>
           </tr>
           <tr>
             <th>จำนวน</th>
             <th>มูลค่า</th>
             <th>จำนวน</th>
             <th>มูลค่า</th>
             <th>จำนวน</th>
             <th>มูลค่า</th>
             <th>จำนวนคงเหลือ</th>
             <th>มูลค่าคงเหลือ</th>
           </tr>
         </thead>

         <tbody>
           <?php $num = 1;foreach ($querys as $item): ?>
             <?php
              // รวมค่าระหว่าง loop
              $sum_begin_qty   += $item['begin_qty'];
              $sum_begin_price += $item['begin_price'];
              $sum_qty_in      += $item['qty_in'];
              $sum_price_in    += $item['price_in'];
              $sum_qty_out     += $item['qty_out'];
              $sum_price_out   += $item['price_out'];
              // $sum_end_qty     += $item['balance_qty'];
              // $sum_end_price   += $item['balance_price'];
              ?>
             <tr>
               <td><?= $num++; ?></td>
               <td><?= $item['asset_item'] ?></td>
               <td><?= $item['asset_name'] ?></td>
               <td><?= $item['asset_type_name'] ?></td>
               <td class="text-end fw-semibold"><?= number_format($item['begin_qty'], 2) ?></td>
               <td class="text-end fw-semibold"><?= number_format($item['begin_price'], 2) ?></td>
               <td class="text-end fw-semibold"><?= number_format($item['qty_in'], 2) ?></td>
               <td class="text-end fw-semibold"><?= number_format($item['price_in'], 2) ?></td>
               <td class="text-end fw-semibold"><?= number_format($item['qty_out'], 2) ?></td>
               <td class="text-end fw-semibold"><?= number_format($item['price_out'], 2) ?></td>
               <td class="text-end fw-semibold"><?= number_format($item['balance_qty'] ?? 0, 2) ?></td>
               <td class="text-end fw-semibold"><?= number_format($item['balance_price'] ?? 0, 2) ?></td>
             </tr>
           <?php endforeach; ?>
         </tbody>
<tfoot class="table-warning" style="position: sticky; bottom: 0; background-color: #ffeb3b; z-index: 9;">
           <tr>
             <td colspan="4" class="text-center fw-semibold">รวมทั้งหมด</td>
             <td class="text-end fw-semibold"><?= number_format($sum_begin_qty, 2) ?></td>
             <td class="text-end fw-semibold"><?= number_format($sum_begin_price, 2) ?></td>
             <td class="text-end fw-semibold"><?= number_format($sum_price_in, 2) ?></td>
             <td class="text-end fw-semibold"><?= number_format($sum_qty_out, 2) ?></td>
             <td class="text-end fw-semibold"><?= number_format($sum_price_out, 2) ?></td>
             <td class="text-end fw-semibold"><?= number_format($sum_end_qty, 2) ?></td>
             <td class="text-end fw-semibold"><?= number_format($sum_end_price, 2) ?></td>
           </tr>
         </tfoot>
       </table>
     </div>
   </div>
 </div>