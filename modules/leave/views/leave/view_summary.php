<?php
/** @var app\modules\leave\models\Leave $model */
/** @var bool $hideHeading ไม่แสดงหัวข้อ (เมื่อใช้ใน view_detail ที่มี card-header แล้ว) */
$hideHeading = $hideHeading ?? false;
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
<?php if (!$hideHeading): ?>
<div class="d-flex align-items-center gap-2 mb-4">
    <div class="p-2 bg-primary bg-opacity-10 rounded-circle text-primary"><i class="bi bi-file-text fs-5"></i></div>
    <h6 class="fw-bold mb-0 text-body">สถิติการลาในปีงบประมาณนี้ <?= $model->thai_year ?></h6>
</div>
<?php endif; ?>
<table class="table table-striped table-hover align-middle mb-0">
    <thead class="table-light">
        <tr>
            <th>ประเภทลา</th>
            <th class="text-center">ลามาแล้ว</th>
            <th class="text-center">ลาครั้งนี้</th>
            <th class="text-center">รวมเป็น</th>
        </tr>
    </thead>
    <tbody class="align-middle table-group-divider">
        <?php foreach ($querys as $item): ?>
            <tr>
                <td scope="row"><?= htmlspecialchars($item['title'] ?? '') ?></td>
                <td class="text-center"><?= (float)($item['last_days'] ?? 0) ?></td>
                <td class="text-center"><?= (float)($item['on_days'] ?? 0) ?></td>
                <td class="text-center"><?= (float)($item['total_days'] ?? 0) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
