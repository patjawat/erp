
<div class="card">
    <div class="card-body">
    <table
        class="table table-primary"
    >
        <thead>
            <tr>
                <th scope="col">หน่วยงาน</th>
                <th scope="col">ชื่อ</th>
                <th scope="col">สถานะ</th>
                <th scope="col">การปฏิบัติ</th>
                <th scope="col">เวลา</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($model->listTrack() as $item):?>
            <tr class="">
                <td scope="row"><?php echo $item->employee->departmentName()?></td>
                <td><?php echo $item->employee->fullname?></td>
                <td><?php echo $item->status;?></td>
                <td><?php echo $item->status;?></td>
                <td><?php echo $item->status;?></td>
            </tr>
          <?php endforeach;?>
        </tbody>
    </table>
</div>
</div>
