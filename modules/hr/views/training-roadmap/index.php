<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use app\modules\hr\models\TrainingRoadmap;
use app\components\widgets\DataSummaryWidget;

$this->title = 'Training Roadmap';
echo $this->render('_styles');
$this->beginBlock('page-title'); echo Html::encode($this->title); $this->endBlock();
$this->beginBlock('page-action'); echo $this->render('@app/modules/hr/menu', ['active' => 'training-roadmap']); $this->endBlock();
$models = $dataProvider->getModels();
?>
<div class="trm-shell">
    <div class="trm-page-head">
        <div><h1>แม่แบบ Training Roadmap</h1><p>กำหนดเส้นทางพัฒนาที่ใช้ร่วมกันได้ทุกวิชาชีพ พร้อมระยะฝึก กิจกรรม สมรรถนะ และจุดประเมิน</p></div>
        <?= Html::a('<i class="bi bi-plus-lg me-1"></i> สร้าง Roadmap', ['create', 'title' => 'สร้าง Training Roadmap'], ['class' => 'btn btn-primary open-modal', 'data-size' => 'modal-xl']) ?>
    </div>
    <?php Pjax::begin(['id' => 'training-roadmap-list', 'enablePushState' => false]); ?>
    <div class="trm-card">
        <form class="trm-toolbar" action="<?= Url::to(['index']) ?>" method="get" data-pjax="1">
            <div class="flex-grow-1"><input class="form-control" name="q" value="<?= Html::encode($q) ?>" placeholder="ค้นหาจากรหัสหรือชื่อ Roadmap" aria-label="ค้นหา Roadmap"></div>
            <div><select class="form-select" name="status" aria-label="กรองสถานะ"><option value="">ทุกสถานะ</option><?php foreach (TrainingRoadmap::statusOptions() as $key => $label): ?><option value="<?= $key ?>" <?= $status === $key ? 'selected' : '' ?>><?= Html::encode($label) ?></option><?php endforeach ?></select></div>
            <button class="btn btn-outline-primary" type="submit">ค้นหา</button>
        </form>
        <?php if ($models): ?>
        <div class="trm-desktop-table">
            <table class="table trm-table">
                <thead><tr><th>รหัสและชื่อ</th><th>ประเภท</th><th class="text-center">เวอร์ชัน</th><th>ระยะเวลา</th><th class="text-center">โครงสร้าง</th><th>สถานะ</th><th class="text-end">จัดการ</th></tr></thead>
                <tbody>
                <?php foreach ($models as $model):
                    $activityCount = 0; foreach ($model->phases as $phase) $activityCount += count($phase->activities);
                ?>
                    <tr>
                        <td><a class="trm-code" href="<?= Url::to(['view', 'id' => $model->id]) ?>" data-pjax="0"><?= Html::encode($model->code) ?></a><div><?= Html::encode($model->title) ?></div></td>
                        <td><?= Html::encode(TrainingRoadmap::typeOptions()[$model->roadmap_type] ?? $model->roadmap_type) ?></td>
                        <td class="text-center"><?= (int) $model->version_no ?></td>
                        <td><?= (int) $model->duration_value ?> <?= Html::encode(TrainingRoadmap::durationUnitOptions()[$model->duration_unit] ?? $model->duration_unit) ?></td>
                        <td class="text-center"><span class="trm-meta"><?= count($model->phases) ?> ระยะ · <?= $activityCount ?> กิจกรรม</span></td>
                        <td><span class="trm-status trm-status--<?= Html::encode($model->status) ?>"><?= Html::encode($model->statusLabel) ?></span></td>
                        <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="<?= Url::to(['view', 'id' => $model->id]) ?>" data-pjax="0">เปิด Roadmap</a></td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>
        <div class="trm-mobile-list"><?php foreach ($models as $model): ?><div class="trm-person-plan"><div class="d-flex justify-content-between gap-2"><a class="trm-code" href="<?= Url::to(['view', 'id' => $model->id]) ?>" data-pjax="0"><?= Html::encode($model->code) ?></a><span class="trm-status trm-status--<?= Html::encode($model->status) ?>"><?= Html::encode($model->statusLabel) ?></span></div><div class="fw-semibold mt-1"><?= Html::encode($model->title) ?></div><div class="trm-meta mt-2"><?= count($model->phases) ?> ระยะ · เวอร์ชัน <?= (int) $model->version_no ?></div></div><?php endforeach ?></div>
        <div class="card-footer bg-white"><?= DataSummaryWidget::widget(['dataProvider' => $dataProvider]) ?></div>
        <?php else: ?><div class="trm-empty"><h3>ยังไม่มีแม่แบบ Training Roadmap</h3><p>เริ่มจากสร้าง Roadmap แล้วกำหนดระยะ กิจกรรม และจุดประเมิน</p><?= Html::a('สร้าง Roadmap แรก', ['create', 'title' => 'สร้าง Training Roadmap'], ['class' => 'btn btn-primary open-modal', 'data-size' => 'modal-xl']) ?></div><?php endif ?>
    </div>
    <?php Pjax::end(); ?>
</div>
