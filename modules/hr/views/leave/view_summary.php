

<?php
$sql = "SELECT x1.*,
       SUM(x1.last_days + x1.on_days) AS total_days
FROM (
    SELECT 
        t.code,
        t.title, 
        l.thai_year,
        IFNULL(SUM(CASE WHEN l.leave_type_id = t.code  AND l.status = 'Allow' AND l.date_start > :date_start THEN l.total_days ELSE 0 END), 0) AS last_days,
        IFNULL(SUM(CASE WHEN l.leave_type_id = t.code AND l.date_start = :date_start THEN l.total_days ELSE 0 END), 0) AS on_days
    FROM `leave` l
    LEFT JOIN categorise t ON t.code = l.leave_type_id
    WHERE l.emp_id = :emp_id 
          AND l.thai_year = :thai_year
    GROUP BY t.code, t.title, l.thai_year
) AS x1 
GROUP BY x1.code, x1.title, x1.thai_year;";
$querys = Yii::$app->db->createCommand($sql)
->bindValue(':date_start',$model->date_start)
->bindValue(':thai_year',$model->thai_year)
->bindValue(':emp_id',$model->emp_id)
->queryAll();


?>
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
            <?php foreach($querys as $item):?>
            <tr>
                <td scope="row"><?php echo $item['title']?></td>
                <td class="text-center"><?php echo $item['last_days']?></td>
                <td class="text-center"><?php echo $item['on_days']?></td>
                <td class="text-center"><?php echo $item['total_days']?></td>
            </tr>
           <?php endforeach;?>
        </tbody>
        <tfoot>
            
        </tfoot>
    </table>
