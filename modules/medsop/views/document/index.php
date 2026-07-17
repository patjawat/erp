<?php
use app\components\widgets\DataSummaryWidget;
use app\modules\medsop\assets\MedSopAsset;
use app\modules\medsop\models\Document;
use yii\helpers\Html;

MedSopAsset::register($this);
$this->title = 'คลังเอกสาร SOP/WI';
$models = $dataProvider->getModels();
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>ค้นหาและเปิดอ่านเอกสารคุณภาพตามสิทธิ์ของคุณ<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?><?= $this->render('_nav') ?><?php $this->endBlock(); ?>

<div class="medsop-index">
    <?= $this->render('_search', ['searchModel' => $searchModel]) ?>

    <section class="medsop-kpi" aria-label="สรุปข้อมูลเอกสาร">
        <?php foreach ([['เอกสารทั้งหมด', $kpi['total']], ['รออนุมัติ', $kpi['pending']], ['เผยแพร่แล้ว', $kpi['published']], ['แผนกที่มีเอกสาร', $kpi['organizations']]] as $metric): ?>
            <div class="medsop-kpi__item"><strong><?= number_format($metric[1]) ?></strong><span><?= Html::encode($metric[0]) ?></span></div>
        <?php endforeach; ?>
    </section>

    <section class="card shadow-sm medsop-list" aria-labelledby="medsop-list-title">
        <div class="medsop-list__head">
            <h2 id="medsop-list-title">รายการเอกสาร</h2>
            <span><?= number_format($dataProvider->getTotalCount()) ?> รายการ</span>
        </div>
        <?php if (!$models): ?>
            <div class="medsop-empty">
                <h3>ไม่พบเอกสารตามเงื่อนไข</h3>
                <p>ลองเปลี่ยนคำค้นหา ประเภท หรือสถานะ แล้วค้นหาอีกครั้ง</p>
                <?= Html::a('ล้างตัวกรอง', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
            </div>
        <?php else: ?>
            <div class="d-none d-lg-block">
                <table class="medsop-table">
                    <thead><tr><th>เลขที่เอกสาร</th><th>ชื่อเอกสาร</th><th>แผนก</th><th>ประเภท</th><th>แก้ไขล่าสุด</th><th>สถานะ</th><th class="text-end">จัดการ</th></tr></thead>
                    <tbody>
                    <?php foreach ($models as $model): $badge = Document::getStatusBadgeConfigFor($model->status); ?>
                        <tr>
                            <td class="medsop-table__number"><?= Html::encode($model->document_no) ?></td>
                            <td><strong><?= Html::encode($model->title) ?></strong><small><?= Html::encode(mb_strimwidth($model->objective, 0, 100, '…', 'UTF-8')) ?></small></td>
                            <td><?= Html::encode(isset($organizations[$model->organization_id]) ? $organizations[$model->organization_id]->name : 'ไม่ระบุ') ?></td>
                            <td><?= Html::encode($model->document_type) ?></td>
                            <td class="medsop-table__date"><?= Yii::$app->formatter->asDate($model->updated_at, 'medium') ?></td>
                            <td><span class="<?= Html::encode($badge['class']) ?>"><i data-lucide="<?= Html::encode($badge['icon']) ?>"></i><?= Html::encode($badge['label']) ?></span></td>
                            <td class="text-end"><?= Html::a('เปิดดู', ['view', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-primary']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <ul class="medsop-mobile-list d-lg-none" role="list">
                <?php foreach ($models as $model): $badge = Document::getStatusBadgeConfigFor($model->status); ?>
                    <li>
                        <div class="d-flex justify-content-between gap-2"><strong><?= Html::encode($model->document_no) ?></strong><span class="<?= Html::encode($badge['class']) ?>"><?= Html::encode($badge['label']) ?></span></div>
                        <h3><?= Html::encode($model->title) ?></h3>
                        <p><?= Html::encode(isset($organizations[$model->organization_id]) ? $organizations[$model->organization_id]->name : 'ไม่ระบุ') ?> · <?= Html::encode($model->document_type) ?></p>
                        <?= Html::a('เปิดดูเอกสาร', ['view', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <?php if ($dataProvider->getTotalCount() > 0): ?>
            <footer class="medsop-list__footer"><?= DataSummaryWidget::widget(['dataProvider' => $dataProvider]) ?></footer>
        <?php endif; ?>
    </section>
</div>
