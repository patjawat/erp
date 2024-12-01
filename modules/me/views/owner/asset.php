
<div class="card">
    <div class="card-body">
                <h6><i class="bi bi-ui-checks"></i> ทรัพย์สินที่รับมอบหมาย <span class="badge rounded-pill text-bg-primary"> <?=$dataProvider->getTotalCount()?></span> รายการ</h6>
<table class="table">
    <thead>
        <tr>
            <th>ชื่อรายการ</th>
            <th> ประจำหน่วยงาน</th>
            <th class="text-center">สถานะ</th>
        </tr>
    </thead>
  <tbody>
    <?php foreach($dataProvider->getModels() as $item):?>
    <tr>
      <td><?=$item->Avatar()?></td>
      <td>
      <?php if(isset($item->data_json['department_name']) && $item->data_json['department_name'] == ''):?>
                            <?= isset($item->data_json['department_name_old']) ? $item->data_json['department_name_old'] : ''?>
                            <?php else:?>
                            <?= isset($item->data_json['department_name']) ? $item->data_json['department_name'] : ''?>
                            <?php endif;?>
      </td>
      <td> <?=$item->viewstatus()?></td>
    </tr>
    <?php endforeach;?>
  </tbody>
</table>
</div>
</div>