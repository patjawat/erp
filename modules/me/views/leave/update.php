<?php

use app\components\UserHelper;

/* @var yii\web\View $this */
/* @var app\modules\lm\models\Leave $model */

$this->title = 'แก้ไขวันลา';
$this->params['breadcrumbs'][] = ['label' => 'Leaves', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
$me = UserHelper::GetEmployee();
?>

<?php if($me->id == $model->emp_id):?>
    <div class="leave-update">
        
 <?= $this->render('@app/modules/hr/views/leave/_form', [
        'model' => $model,
    ]) ?>

</div>
<?php else:?>
    <h5 class="text-center">ไม่ใช่เจ้าของใบลา</h5>
<?php endif;?>
