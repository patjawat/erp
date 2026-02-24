<?php
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;

$this->title = 'รายการลงเวลา';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-list-check fs-4 text-primary"></i>
        <?= Html::encode($this->title) ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= Html::a('<i class="bi bi-plus-lg me-1"></i> ลงเวลา', ['/attendance/default/checkin'], ['class' => 'btn btn-primary btn-sm']) ?>
<?= Html::a('<i class="bi bi-graph-up me-1"></i> รายงาน', ['report'], ['class' => 'btn btn-outline-primary btn-sm']) ?>
<?= Html::a('<i class="bi bi-upload me-1"></i> นำเข้า CSV', ['import-form'], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
<?php $this->endBlock(); ?>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'layout' => '{items} {pager}',
            'tableOptions' => ['class' => 'table table-hover align-middle mb-0'],
            'columns' => [
                ['class' => 'kartik\grid\SerialColumn'],
                [
                    'attribute' => 'emp_id',
                    'label' => 'พนักงาน',
                    'value' => function ($m) {
                        return $m->employee ? ($m->employee->fname . ' ' . $m->employee->lname) : '-';
                    },
                    'filterInputOptions' => ['class' => 'form-control form-control-sm', 'placeholder' => 'ค้นหา'],
                ],
                [
                    'attribute' => 'checkin_at',
                    'format' => ['date', 'php:d/m/Y H:i'],
                    'filterInputOptions' => ['class' => 'form-control form-control-sm'],
                ],
                [
                    'attribute' => 'method',
                    'value' => function ($m) { return $m->getMethodLabel(); },
                    'filter' => [
                        'qrcode' => 'สแกน QR',
                        'photo' => 'ถ่ายรูป',
                        'manual' => 'กดลงเวลา',
                    ],
                    'filterInputOptions' => ['class' => 'form-control form-control-sm'],
                ],
                [
                    'attribute' => 'check_type',
                    'value' => function ($m) { return $m->getCheckTypeLabel(); },
                    'filter' => ['in' => 'บันทึกเข้า', 'out' => 'บันทึกออก'],
                    'filterInputOptions' => ['class' => 'form-control form-control-sm'],
                ],
                [
                    'attribute' => 'status',
                    'format' => 'raw',
                    'value' => function ($m) {
                        $cls = $m->status === 'approved' ? 'text-bg-success' : ($m->status === 'rejected' ? 'text-bg-danger' : 'text-bg-warning text-dark');
                        return Html::tag('span', $m->getStatusLabel(), ['class' => 'badge ' . $cls]);
                    },
                    'filter' => [
                        'pending' => 'รออนุมัติ',
                        'approved' => 'อนุมัติแล้ว',
                        'rejected' => 'ไม่อนุมัติ',
                    ],
                    'filterInputOptions' => ['class' => 'form-control form-control-sm'],
                ],
                [
                    'class' => 'kartik\grid\ActionColumn',
                    'template' => '{view}',
                    'urlCreator' => function ($action, $model) {
                        return Url::to(['checkin/' . $action, 'id' => $model->id]);
                    },
                ],
            ],
        ]) ?>
    </div>
</div>
