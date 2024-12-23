
    <table
        class="table table-primary"
    >
    <thead>

            <tr>
                <th  class="text-start" scope="col">ปีงบประมาณ</th>
                <th  class="text-center" scope="col">สิทธิวันลา</th>
                <th  class="text-center"scope="col">สะสมมา</th>
                <th  class="text-center"scope="col">สิทธิวันที่ลาได้</th>
                <th  class="text-center"scope="col">ใช้ไป</th>
                <th  class="text-center"scope="col">คงเหลือ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($model as $item):?>
            <tr class="">
                <td  class="text-start" scope="row"><?php echo $item->thai_year?></td>
                <td  class="text-center"><?php echo $item->leave_days?></td>
                <td  class="text-center"><?php echo $item->leave_before_days?></td>
                <td  class="text-center"><?php echo $item->leave_total_days?></td>
                <td  class="text-center">0</td>
                <td  class="text-center">0</td>
            </tr>
            <?php endforeach;?>
        </tbody>
    </table>
