
<div class="card">
    <div class="card-body">
    <table
        class="table"
    >
        <thead>
            <tr>
                <th scope="col">หน่วยงาน</th>
                <th scope="col">ผู้ใช้งาน</th>
                <th scope="col">สถานะ</th>
                <th scope="col">เวลา</th>
            </tr>
        </thead>
        <tbody class="align-middle  table-group-divider table-hover">
            <?php foreach($model->view_json ?? [] as $item):?>
            <tr class="">
                <td><?php  echo $item['department'] ?? '-';?>
                <td><?php  echo $item['fullname'] ?? '-';?>
                <td>อ่าน</td>
                <td>
                <div class=" d-flex flex-column">
                            <span class="fw-normal fs-6"><?php  echo Yii::$app->thaiFormatter->asDate(($item['date_time']  ?? '0000-00-00'), 'long');?></span>
                            <span class="fw-lighter fs-13"><?php echo date('H:i:s', strtotime(($item['date_time'] ?? '0000-00-00 00:00:00')));?></span>
                       </div>    
                
                </td>
            </tr>
            <?php endforeach;?>
            
        </tbody>
    </table>
</div>
</div>
