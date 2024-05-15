<?php
$sql = "select * FROM  (SELECT c.title,count(a.id) as total FROM asset a
INNER JOIN categorise c ON c.code = a.asset_item AND c.name = 'asset_item' AND c.category_id = 18
GROUP BY c.code) as x1
order by x1.total DESC;";
$querys = Yii::$app->db->createCommand($sql)->queryAll();
?>

<div class="card">
    <div class="card-body">

<h6>จำนวนครุภัณฑ์ที่อยู่ในความดูแล</h6>

    <table
        class="table table-primary"
    >
        <thead>
            <tr>
                <th scope="col">รายการ</th>
                <th scope="col">จำนวน</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($querys as $model):?>
            <tr class="">
                <td><?=$model['title']?></td>
                <td><?=$model['total']?></td>
            </tr>
            <?php endforeach;?>
        </tbody>
    </table>
    </div>
</div>