
<?php
$sql = "SELECT lt.code,lt.title,
IFNULL((SELECT SUM(l.sum_days) FROM `leave` l WHERE l.emp_id = :emp_id AND thai_year = :thai_year AND l.status = 'Pending' AND l.leave_type_id = lt.code),0) as pending,
IFNULL((SELECT SUM(l.sum_days) FROM `leave` l WHERE l.emp_id = :emp_id AND thai_year = :thai_year AND l.status = 'Allow' AND l.leave_type_id = lt.code),0) as total
FROM `categorise` lt
WHERE lt.name = 'leave_type' AND lt.code IN('LT1','LT3','LT4');";

$querys = Yii::$app->db->createCommand($sql)
->bindValue(':thai_year',$model->thai_year)
->bindValue(':emp_id',$model->emp_id)
->queryAll();
?>
<div
    class="table-responsive"
>
    <table
        class="table table-striped table-hover align-middle"
    >
        <thead class="table-primary">
            <caption>
            สถิติการลาในปีงบประมาณนี้ <?=$model->thai_year?>
            </caption>
            <tr>
                <th>ประเภทการลา</th>
                <th class="text-center">ลามาแล้ว/วัน</th>

            </tr>
        </thead>
        <tbody class="table-group-divider">
            <?php foreach($querys as $item):?>
            <tr>
                <td scope="row"><?php echo $item['title']?></td>
                <td class="text-center"><?php echo $item['total']?></td>
            </tr>
            <?php endforeach?>
        </tbody>
        <tfoot>
            
        </tfoot>
    </table>
</div>
