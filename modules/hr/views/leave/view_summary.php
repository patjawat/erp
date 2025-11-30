<?php
$sql = "SELECT x1.*,
       SUM(x1.last_days + x1.on_days) AS total_days
FROM (
    SELECT 
        t.code,
        t.title, 
        l.thai_year,
        IFNULL(SUM(CASE WHEN l.leave_type_id = t.code  AND l.status = 'Approve' AND l.date_start < :date_start THEN l.total_days ELSE 0 END), 0) AS last_days,
        IFNULL(SUM(CASE WHEN l.leave_type_id = t.code  AND l.date_start = :date_start THEN l.total_days ELSE 0 END), 0) AS on_days
    FROM `leave` l
    LEFT JOIN categorise t ON t.code = l.leave_type_id
    WHERE l.emp_id = :emp_id 
          AND l.thai_year = :thai_year
    GROUP BY t.code, t.title, l.thai_year
) AS x1 
GROUP BY x1.code, x1.title, x1.thai_year;";

$querys = Yii::$app->db->createCommand($sql)
    ->bindValue(':date_start', $model->date_start)
    ->bindValue(':thai_year', $model->thai_year)
    ->bindValue(':emp_id', $model->emp_id)
    ->queryAll();


?>
<div class="d-flex align-items-center gap-2 mb-4">
    <div class="p-2 bg-primary bg-opacity-10 rounded-circle text-primary"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-text" aria-hidden="true">
            <path d="M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z">
            </path>
            <path d="M14 2v5a1 1 0 0 0 1 1h5"></path>
            <path d="M10 9H8"></path>
            <path d="M16 13H8"></path>
            <path d="M16 17H8"></path>
        </svg></div>
    <h6 class="fw-bold mb-0 text-dark">สถิติการลาในปีงบประมาณนี้ <?= $model->thai_year ?></h6>
</div>
<table
    class="table table-striped table-hover align-middle">
    <thead class="table-primary">
        <tr>
            <th>ประเภทลา</th>
            <th class="text-center">ลามาแล้ว</th>
            <th class="text-center">ลาครั้งนี้</th>
            <th class="text-center">รวมเป็น</th>

        </tr>
    </thead>
    <tbody class="table-group-divider">
        <?php foreach ($querys as $item): ?>
            <tr>
                <td scope="row"><?php echo $item['title'] ?></td>
                <td class="text-center"><?php echo $item['last_days'] ?></td>
                <td class="text-center"><?php echo $item['on_days'] ?></td>
                <td class="text-center"><?php echo $item['total_days'] ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>

    </tfoot>
</table>