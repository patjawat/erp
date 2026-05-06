<?php

use yii\helpers\Html;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\AssetItem $model */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'รายการครุภัณฑ์', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0 text-primary-gradient">
        <i class="fa-solid fa-box-open"></i>
        <?= Html::encode($this->title) ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php Pjax::begin(['id' => 'sm-container', 'enablePushState' => false, 'timeout' => 5000]); ?>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="row g-4 align-items-start">
            <div class="col-md-4 text-center">
                <?= Html::img($model->showImg(), [
                    'class' => 'img-fluid rounded-4 border shadow-sm',
                    'style' => 'max-width:100%;object-fit:cover;',
                ]) ?>

                <div class="mt-3">
                    <span class="badge rounded-pill px-3 py-2 <?= $model->active ? 'bg-success' : 'bg-secondary' ?>">
                        <?= $model->active ? 'ใช้งาน' : 'ไม่ใช้งาน' ?>
                    </span>
                </div>
            </div>

            <div class="col-md-8">
                <div class="table-responsive">
                    <table class="table table-borderless align-middle mb-0">
                        <tbody>
                            <tr>
                                <th class="text-nowrap" style="width: 28%;">ชื่อรายการ</th>
                                <td><?= Html::encode($model->title ?: '-') ?></td>
                            </tr>
                            <tr>
                                <th>รหัส</th>
                                <td><?= Html::encode($model->code ?: '-') ?></td>
                            </tr>
                            <tr>
                                <th>ประเภททรัพย์สิน</th>
                                <td><?= Html::encode($model->assetTypeTitle) ?></td>
                            </tr>
                            <tr>
                                <th>กลุ่มทรัพย์สิน</th>
                                <td><?= Html::encode($model->assetGroupTitle) ?></td>
                            </tr>
                            <tr>
                                <th>หน่วยนับ</th>
                                <td><?= Html::encode($model->unitName) ?></td>
                            </tr>
                            <tr>
                                <th>รายละเอียด</th>
                                <td><?= Html::encode($model->description ?: '-') ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex gap-2 flex-wrap mt-4">
                    <?= Html::a(
                        '<i class="fa-regular fa-pen-to-square me-1"></i> แก้ไข',
                        ['/sm/asset-item/update', 'id' => $model->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'],
                        ['class' => 'btn btn-warning open-modal', 'data' => ['size' => 'modal-lg']]
                    ) ?>
                    <?= Html::a(
                        '<i class="fa-solid fa-trash me-1"></i> ลบ',
                        ['/sm/asset-item/delete', 'id' => $model->id],
                        [
                            'class' => 'btn btn-danger delete-item',
                            'data' => [
                                'confirm' => 'คุณต้องการลบรายการนี้ใช่หรือไม่?',
                                'method' => 'post',
                            ],
                        ]
                    ) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php Pjax::end(); ?>
