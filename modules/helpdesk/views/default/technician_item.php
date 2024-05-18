<?php
use yii\helpers\Html;
use app\modules\hr\models\Employees;

// $sql = "SELECT x3.*,ROUND(((x3.rating_user/ x3.total_user) * 100),0) as pp FROM (SELECT x1.*,
// (SELECT (count(h.id)) FROM helpdesk h  WHERE h.name = 'repair' AND h.repair_group = :repair_group AND JSON_CONTAINS(h.data_json->'$.join',CONCAT('"',x1.id,'"'))) as rating_user
// FROM (SELECT DISTINCT e.id, concat(e.fname,' ',e.lname) as fullname,
// (SELECT count(DISTINCT id) FROM employees e INNER JOIN auth_assignment a ON a.user_id = e.user_id) as total_user
// FROM employees e
// INNER JOIN auth_assignment a ON a.user_id = e.user_id) as x1
// GROUP BY x1.id) as x3;";
 $sql = "SELECT x3.*,ROUND(((x3.rating_user/ x3.total_user) * 100),0) as p FROM (SELECT x1.*,
 (SELECT (count(h.id)) FROM helpdesk h  WHERE h.name = 'repair' AND h.repair_group = :repair_group AND JSON_CONTAINS(h.data_json->'$.join',CONCAT('" . '"' . "',x1.user_id,'" . '"' . "'))) as rating_user
 FROM (SELECT DISTINCT e.user_id, concat(e.fname,' ',e.lname) as fullname,
 (SELECT count(DISTINCT id) FROM employees e INNER JOIN auth_assignment a ON a.user_id = e.user_id) as total_user
 FROM employees e
 INNER JOIN auth_assignment a ON a.user_id = e.user_id ) as x1
 GROUP BY x1.user_id) as x3;";
 $querys  = Yii::$app->db->createCommand($sql)
 ->bindValue('repair_group',$repair_group)
 ->queryAll();
?>
<?php foreach($querys as $model):?>

<div class="d-flex flex-column total font-weight-bold mt-1 text-bg-light rounded p-3 gap-2">
    <?php 
         $employee = Employees::find()->where(['user_id' => $model['user_id']])->one();
         if($employee){
             echo $employee->getAvatar(false);
         }else{
         }
        ?>
            <?=app\components\AppHelper::viewProgressBar($model['p'])?>
</div>
<?php endforeach;?>