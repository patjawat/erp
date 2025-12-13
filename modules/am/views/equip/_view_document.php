<?php

use yii\helpers\Html;
use app\modules\helpdesk2\models\Helpdesk;

$repairHistorys = Helpdesk::find()->where(['asset_number' => $model->code])->all();
?>
<?= $this->render('@app/modules/am/views/asset/_title', ['model' => $model]) ?>
<div class="card mt-4">
    <div class="card-header">
        <?= $this->render('@app/modules/am/views/asset/_view_menu', ['model' => $model, 'menu' => 'documents']) ?>
    </div>
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="card-title mb-0">เอกสารที่เกี่ยวข้อง</h5>
            <?= Html::a(' <i class="bi bi-plus-circle"></i> เพิ่มเอกสาร', ['/am/asset-document/create','name' => 'asset_document','id' => $model->id], ['class' => 'btn btn-sm btn-primary open-modal', 'data' => ['size' => 'modal-lg']]) ?>
        </div>
        <?= $this->render('@app/modules/am/views/asset-document/_list_documents', ['model' => $model]) ?>
    </div>
</div>