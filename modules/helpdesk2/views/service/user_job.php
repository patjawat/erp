<?php
use yii\helpers\Html;
use app\modules\hr\models\Employees;

?>
<?php foreach($searchModel->listUserJob() as $model):?>
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