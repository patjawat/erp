<div class="card h-100">
        <div class="card-body">
<h6>ขออนุมัติเบิกวัสดุ <span class="badge rounded-pill text-bg-primary"><?=$dataProvider->getTotalCount()?> </span> รายการ</h6>
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
      <td><?=$item->CreateBy('ผู้ขอเบิก')['avatar']?></td>
      <td><?=$item->to_warehouse_id?></td>
      <td><?=$item->from_warehouse_id?></td>
    </tr>
    <?php endforeach;?>
  </tbody>
</table>

</div>
      </div>