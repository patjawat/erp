

<h5>สถิติการลาในปีงบประมาณนี้ <?=$model->thai_year?></h5>
    <table
        class="table table-striped table-hover align-middle"
        >
        <thead class="table-primary">
            <tr>
                <th>ประเภทลา</th>
                <th class="text-center">ลามาแล้ว</th>
                <th class="text-center">ลาครั้งนี้</th>
                <th class="text-center">รวมเป็น</th>

            </tr>
        </thead>
        <tbody class="table-group-divider">
            <tr>
                <td scope="row">ลาป่วย</td>
                <td class="text-center"><?php echo $model->leaveEmpSummary()['last_lt1']?></td>
                <td class="text-center"><?php echo $model->leave_type_id == 'LT1' ? $model->sum_days : 0?></td>
                <td class="text-center"><?php echo $model->leave_type_id == 'LT1' ? ($model->sum_days + $model->leaveEmpSummary()['last_lt1']) : 0?></td>
            </tr>
            <tr>
                <td scope="row">ลากิจ</td>
                <td class="text-center"><?php echo $model->leaveEmpSummary()['last_lt3']?></td>
                <td class="text-center"><?php echo $model->leave_type_id == 'LT3' ? $model->sum_days : 0?></td>
                <td class="text-center"><?php echo $model->leave_type_id == 'LT3' ? ($model->sum_days + $model->leaveEmpSummary()['last_lt3']) : 0?></td>
            </tr>
            <tr>
                <td scope="row">ลาพักผ่อน</td>
                <td class="text-center"><?php echo $model->leaveEmpSummary()['last_lt4']?></td>
                <td class="text-center"><?php echo $model->leave_type_id == 'LT4' ? $model->sum_days : 0?></td>
                <td class="text-center"><?php echo $model->leave_type_id == 'LT4' ? ($model->sum_days + $model->leaveEmpSummary()['last_lt4']) : 0?></td>
            </tr>
           
        </tbody>
        <tfoot>
            
        </tfoot>
    </table>
