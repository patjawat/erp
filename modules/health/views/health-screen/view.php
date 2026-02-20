<?php

use yii\helpers\Url;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var app\modules\health\models\HealthScreen $model
 */

$this->title = 'ผลตรวจสุขภาพ';
$this->params['breadcrumbs'][] = ['label' => 'ข้อมูลสุขภาพ', 'url' => ['/health/health-screen/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="health-screen-view py-4">
    <div class="container-fluid">
        <?= $this->render('@app/modules/me/views/health/view', ['model' => $model]) ?>

        <div class="d-flex gap-3 mt-4">
            <?= Html::a('<i class="fas fa-arrow-left me-2"></i> กลับรายการ', ['/health/health-screen/index'], ['class' => 'btn btn-light py-3 px-4 rounded-4 border']) ?>
            <?= Html::a('<i class="fas fa-print me-2"></i> พิมพ์รายงานผล', ['/health/health-screen/print', 'id' => $model->id], ['class' => 'btn btn-primary py-3 px-4 rounded-4 shadow-sm', 'target' => '_blank']) ?>
        </div>
    </div>
</div>
