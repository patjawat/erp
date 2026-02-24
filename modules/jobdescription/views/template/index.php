<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\LinkPager;

/** @var yii\web\View $this */
/** @var app\modules\jobdescription\models\JdTemplateSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Template คำอธิบายงาน (JD)';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-file-earmark-text fs-4 text-primary"></i>
        <?= Html::encode($this->title) ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= Html::a('<i class="bi bi-plus-lg me-1"></i> สร้าง Template', ['create', 'title' => '<i class="bi bi-file-earmark-plus"></i> สร้าง Template JD'], ['class' => 'btn btn-primary open-modal', 'data' => ['size' => 'modal-lg']]) ?>
<?php $this->endBlock(); ?>

<?php Pjax::begin(['id' => 'jd-template-index']); ?>
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body">
        <?php $form = ActiveForm::begin([
            'method' => 'get',
            'action' => ['index'],
            'options' => ['class' => 'mb-3'],
        ]); ?>
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <?= $form->field($searchModel, 'name')->textInput(['class' => 'form-control', 'placeholder' => 'ค้นหาชื่อ template'])->label('ชื่อ') ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($searchModel, 'position_code')->dropDownList(
                    ['' => '-- ทุกตำแหน่ง --'] + \app\components\CategoriseHelper::PositionName(),
                    ['class' => 'form-control form-select']
                )->label('ตำแหน่งงาน') ?>
            </div>
            <div class="col-md-2">
                <?= $form->field($searchModel, 'is_active')->dropDownList(
                    ['' => 'ทั้งหมด', 1 => 'ใช้งาน', 0 => 'ปิดใช้'],
                    ['class' => 'form-control form-select']
                )->label('สถานะ') ?>
            </div>
            <div class="col-md-2">
                <?= Html::submitButton('<i class="bi bi-search me-1"></i> ค้นหา', ['class' => 'btn btn-primary']) ?>
                <?= Html::a('ล้าง', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-nowrap">ลำดับ</th>
                        <th>ชื่อ template</th>
                        <th>ตำแหน่งงาน</th>
                        <th>สถานะ</th>
                        <th class="text-nowrap">จำนวนหัวข้อ</th>
                        <th class="text-end" style="width: 140px;">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="align-middle table-group-divider">
                    <?php
                    $models = $dataProvider->getModels();
                    $pagination = $dataProvider->pagination;
                    $offset = $pagination ? $pagination->offset : 0;
                    foreach ($models as $key => $item):
                        $num = $offset + $key + 1;
                    ?>
                        <tr>
                            <td class="text-nowrap"><?= $num ?></td>
                            <td><?= Html::encode($item->name) ?></td>
                            <td><?= Html::encode($item->getPositionTitle()) ?></td>
                            <td>
                                <?= $item->is_active
                                    ? Html::tag('span', 'ใช้งาน', ['class' => 'badge text-bg-success'])
                                    : Html::tag('span', 'ปิดใช้', ['class' => 'badge text-bg-secondary']) ?>
                            </td>
                            <td><?= count($item->sections) ?></td>
                            <td class="text-end">
                                <?= Html::a('<i class="bi bi-eye"></i>', ['view', 'id' => $item->id], ['class' => 'btn btn-sm btn-outline-secondary', 'title' => 'ดู']) ?>
                                <?= Html::a('<i class="bi bi-pencil"></i>', ['update', 'id' => $item->id, 'title' => 'แก้ไข Template: ' . $item->name], ['class' => 'btn btn-sm btn-outline-primary open-modal', 'data' => ['size' => 'modal-lg'], 'title' => 'แก้ไข']) ?>
                                <?= Html::a('<i class="bi bi-trash"></i>', ['delete', 'id' => $item->id], [
                                    'class' => 'btn btn-sm btn-outline-danger',
                                    'title' => 'ลบ',
                                    'data' => ['method' => 'post', 'confirm' => 'ยืนยันลบ template นี้?'],
                                ]) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($pagination && $pagination->totalCount > $pagination->limit): ?>
            <div class="d-flex justify-content-center mt-3">
                <?= LinkPager::widget([
                    'pagination' => $pagination,
                    'options' => ['class' => 'pagination mb-0'],
                ]) ?>
            </div>
        <?php endif; ?>

        <?php if (empty($models)): ?>
            <p class="text-muted text-center py-4 mb-0">ไม่พบรายการ</p>
        <?php endif; ?>
    </div>
</div>
<?php Pjax::end(); ?>
