<?php

?>

<caption>
            สถิติการลาในปีงบประมาณ <span class="fw-semibold"><?php echo $model->thai_year?></span>
            </caption>
    <table
        class="table table-striped table-hover table-borderless align-middle">
        <thead>
          
            <tr>
                <th>ประเภท</th>
                <th class="text-center">ลามาแล้ว</th>
                <th class="text-center">ลาครั้งนี้</th>
                <th class="text-center">รวมเป็น</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($model->leaveEmpSummary() as $item):?>
            <tr>
                <td scope="row"><?php echo $item['title']?></td>
                <td class="text-center"><?php echo $item['total']?></td>
                <td class="text-center">0.0</td>
                <td class="text-center">0.0</td>
            </tr>
            <?php endforeach;?>
           
            
        </tbody>
        <tfoot>
            
        </tfoot>
    </table>

