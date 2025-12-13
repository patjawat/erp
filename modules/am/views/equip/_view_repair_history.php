<?php

use app\modules\helpdesk2\models\Helpdesk;

$repairHistorys = Helpdesk::find()->where(['asset_number' => $model->code])->all();
?>

<?= $this->render('@app/modules/am/views/asset/_title', ['model' => $model]) ?>

<div class="card mt-4">
    <div class="card-header">
        <?= $this->render('@app/modules/am/views/asset/_view_menu', ['model' => $model, 'menu' => 'repair_history']) ?>
    </div>
    <div class="card-body">
        <?= $this->render('@app/modules/am/views/asset/_list_repair_history', ['model' => $model]) ?>
    </div>
</div>
