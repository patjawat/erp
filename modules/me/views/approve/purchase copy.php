<div class="card h-100">
        <div class="card-body">
<h6>ขออนุมิติจัดซื้อจัดจ้าง <span class="badge rounded-pill text-bg-primary"><?=$dataProvider->getTotalCount()?> </span> รายการ</h6>
<table class="table">
  <thead>
    <tr>
      <th scope="col">รายการ</th>
      <th scope="col">จาก</th>
      <th scope="col">ถึง</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($dataProvider->getModels() as $item):?>
    <tr>
      <td><?=$item->getUserReq()['avatar']?></td>
      <td><?php //$item->to_warehouse_id?></td>
      <td><?php // $item->from_warehouse_id?></td>
    </tr>
    <?php endforeach;?>
  </tbody>
</table>

</div>
      </div>