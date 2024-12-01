<?php
use yii\bootstrap5\Html;
use app\components\AppHelper;
use app\components\UserHelper;
$emp = UserHelper::GetEmployee();
$thai_year = AppHelper::YearBudget();
$sql = "SELECT lt.title,count(l.id) as total,
(select count(x1.id) FROM `leave` x1 where x1.emp_id = l.emp_id AND x1.thai_year = l.thai_year AND x1.status = 'Pending' AND x1.leave_type_id = l.leave_type_id) as leave_pending,
(select count(x2.id) FROM `leave` x2 where x2.emp_id = l.emp_id AND x2.thai_year = l.thai_year AND x2.status = 'Allow' AND x2.leave_type_id = l.leave_type_id) as leave_allow,
(select count(x3.id) FROM `leave` x3 where x3.emp_id = l.emp_id AND x3.thai_year = l.thai_year AND x3.status = 'Reject' AND x3.leave_type_id = l.leave_type_id) as leave_reject
FROM `leave` l 
LEFT JOIN categorise lt ON lt.code = l.leave_type_id AND lt.name = 'leave_type'
WHERE l.emp_id = :emp_id AND l.thai_year = :thai_year
GROUP BY lt.code";
$querys = Yii::$app->db->createCommand($sql,[':emp_id'=>$emp->id,'thai_year' =>$thai_year])->queryAll();
?>
<div class="card" style="height:300px;">
    <div class="card-body">
สถิติการลาในปีงบประมาณ <span class="fw-semibold"><?php echo $thai_year?></span>
    <table
        class="table table-striped table-hover table-borderless align-middle">
        <thead>
          
            <tr>
                <th>ประเภท</th>
                <th class="text-center">รออนุมัติ</th>
                <th class="text-center">ไม่อนุมัติ</th>
                <th class="text-center">อนุมัติ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($querys as $item):?>
            <tr>
                <td scope="row"><?php echo $item['title']?></td>
                <td class="text-center"><?php echo $item['leave_pending']?></td>
                <td class="text-center"><?php echo $item['leave_reject']?></td>
                <td class="text-center"><?php echo $item['leave_allow']?></td>
            </tr>
            <?php endforeach;?>
           
            
        </tbody>
    </table>

    </div>
    <div class="card-footer">
        <?=Html::a('ทะเบียนประวัติ <i class="fe fe-arrow-right-circle"></i>',['/hr/leave'],['class' => 'btn btn-light'])?>

    </div>
</div>


