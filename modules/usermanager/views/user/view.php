<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\usermanager\models\User */

$this->title = $model->username;
$this->params['breadcrumbs'][] = ['label' => 'ภาพรวม', 'url' => ['/usermanager/default/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'ผู้ใช้งาน', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<?php $this->beginBlock('navbar_menu'); ?>
<?= $this->render('@app/modules/settings/views/menu', ['active' => 'user']) ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<div class="d-flex flex-wrap gap-2 align-items-center">
    <?= Html::a('<i class="bi bi-pencil me-1"></i> แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-primary rounded-3 link-loading']) ?>
    <?= Html::a('<i class="bi bi-trash me-1"></i> ลบทิ้ง', ['delete', 'id' => $model->id], [
        'class' => 'btn btn-outline-danger rounded-3',
        'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
        'data-method' => 'post',
    ]) ?>
    <?= $this->render('../default/navlink') ?>
</div>
<?php $this->endBlock(); ?>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body">
        <?= DetailView::widget([
            'model' => $model,
            'options' => ['class' => 'table table-borderless mb-0'],
            'attributes' => [
                'email:email',
                [
                    'format' => 'html',
                    'label' => 'ชื่อ-นามสกุล',
                    'value' => $model->employee ? $model->employee->fullname : '-',
                ],
                [
                    'format' => 'html',
                    'label' => 'สถานะ',
                    'value' => $model->status == \app\modules\usermanager\models\User::STATUS_ACTIVE
                        ? '<span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill fw-medium px-2 py-1">' . Html::encode($model->statusName) . '</span>'
                        : '<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill fw-medium px-2 py-1">' . Html::encode($model->statusName) . '</span>',
                ],
            ],
        ]) ?>
    </div>
</div>
