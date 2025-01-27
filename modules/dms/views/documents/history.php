
<div class="card">
    <div class="card-body">
    <table
        class="table"
    >
        <thead>
            <tr>
                <th scope="col">ผู้อ่าน</th>
                <th scope="col">วัน/เวลา</th>
            </tr>
        </thead>
        <tbody class="align-middle  table-group-divider table-hover">
            <?php foreach($model->viewHistory() ?? [] as $item):?>
            <tr class="">
                <td><?php echo $item->employee->fullname ?? '-';?><td>
                <div class=" d-flex flex-column">
                            <span class="fw-normal fs-6"><?php   echo Yii::$app->thaiFormatter->asDate(($item->doc_read  ?? '0000-00-00'), 'long');?></span>
                            <span class="fw-lighter fs-13"><?php  echo date('H:i:s', strtotime(($item->doc_read ?? '0000-00-00 00:00:00')));?></span>
                       </div>    
                
                </td>
            </tr>
            <?php endforeach;?>
            
        </tbody>
    </table>
</div>
</div>
