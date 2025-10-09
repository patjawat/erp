 <!-- Header -->
 <div class="report-header">
   <div class="report-title">รายงานสรุปสต็อกประจำเดือน</div>
   <div class="report-subtitle">ประจำเดือนสิงหาคม 2568 (01/08/2025 – 31/08/2025)</div>
 </div>

 <div class="card">
   <div class="card-header bg-primary-gradient text-white">
     <h6 class="text-white mt-2"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
   </div>
   <div class="card-body">
     <?php echo $this->render('_search_by_item', ['model' => $searchModel]); ?>
   </div>
 </div>

 <!-- Table -->
 <div class="table-responsive">
   <table class="table table-bordered table-striped table-hover align-middle">
     <thead class="table-primary text-center">
       <tr>
         <th rowspan="2">รหัสสินค้า</th>
         <th rowspan="2">รายการสินค้า</th>
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
       <!-- ตัวอย่างข้อมูล -->
       <?php foreach ($querys as $item): ?>
         <tr>
           <td><?= $item['asset_item'] ?></td>
           <td><?= $item['title'] ?></td>
           <td><?= $item['begin_qty'] ?></td>
           <td><?= $item['begin_price'] ?></td>
           <td><?= $item['qty_in'] ?></td>
           <td><?= $item['price_in'] ?></td>
           <td><?= $item['qty_out'] ?></td>
           <td><?= $item['price_out'] ?></td>
           <td><?= $item['end_qty'] ?></td>
           <td><?= $item['end_price'] ?></td>
         </tr>
       <?php endforeach; ?>
     </tbody>

     <tfoot class="table-light fw-bold">
       <tr>
         <td colspan="2">รวมทั้งหมด</td>
         <td>150</td>
         <td>75,000</td>
         <td>50</td>
         <td>27,000</td>
         <td>20</td>
         <td>11,000</td>
         <td>180</td>
         <td>91,000</td>
       </tr>
     </tfoot>
   </table>
 </div>

 <!-- Footer -->
 <div class="text-end text-muted mt-3 small">
   วันที่ออกรายงาน: 9 ตุลาคม 2568
 </div>