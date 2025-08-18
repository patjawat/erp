<?php
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\plan\models\Plan $model */
/** @var app\modules\plan\models\PlanItem[] $items */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'แผนทั้งหมด', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="plan-view">

    <h3><?= Html::encode($this->title) ?></h3>

    <p>
        <?= Html::a('แก้ไขแผน', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('ลบแผน', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'คุณแน่ใจหรือไม่ว่าต้องการลบแผนนี้?',
                'method' => 'post',
            ],
        ]) ?>
        <?= Html::a('จัดการรายการแผน', ['/plan/plan-item/manage', 'plan_id' => $model->id], ['class' => 'btn btn-secondary']) ?>
    </p>

    <div class="card mb-3">
        <div class="card-header">ข้อมูลแผน</div>
        <div class="card-body">
            <p><strong>ประเภทแผน:</strong> <?= $model->plan_type ?></p>
            <p><strong>รายละเอียด:</strong> <?= $model->description ?></p>
            <p><strong>ช่วงเวลา:</strong> <?= $model->start_date ?> ถึง <?= $model->end_date ?></p>
            <p><strong>งบประมาณรวม:</strong> <?= number_format($model->budget_total,2) ?></p>
            <p><strong>งบที่ใช้ไปแล้ว:</strong> <?= number_format($model->budget_used,2) ?></p>
            <p><strong>สถานะ:</strong> <?= $model->status ?></p>
        </div>
    </div>

    <div class="card">
        <div class="card-header">รายการในแผน</div>
        <div class="card-body">
            <?php if ($items): ?>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>ชื่อรายการ</th>
                            <th>จำนวน</th>
                            <th>ราคาต่อหน่วย</th>
                            <th>รวม</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $i => $item): ?>
                        <tr>
                            <td><?= $i+1 ?></td>
                            <td><?= Html::encode($item->item_name) ?></td>
                            <td><?= $item->quantity ?></td>
                            <td><?= number_format($item->unit_price,2) ?></td>
                            <td><?= number_format($item->total_price,2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>ยังไม่มีรายการในแผน</p>
            <?php endif; ?>
        </div>
    </div>

</div>
