<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\LinkPager;

/** @var yii\web\View $this */
/** @var app\modules\jd\models\JdTemplateSearch $searchModel */
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
<div class="d-flex gap-2">
    <?= Html::a('<i class="bi bi-plus-lg me-1"></i> สร้าง Template', ['create'], ['class' => 'btn btn-primary']) ?>
    <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#modalImportSeed">
        <i class="bi bi-cloud-download me-1"></i> นำเข้า Template สาธารณสุข
    </button>
</div>
<?php $this->endBlock(); ?>

<!-- Modal ยืนยันการนำเข้า -->
<div class="modal fade" id="modalImportSeed" tabindex="-1" aria-labelledby="modalImportSeedLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white py-2 px-3">
                <h6 class="modal-title mb-0 small fw-normal" id="modalImportSeedLabel">
                    <i class="bi bi-cloud-download me-1"></i> นำเข้า Template ตำแหน่งงานสาธารณสุข
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">ระบบจะนำเข้า <strong>15 ตำแหน่งงาน</strong> มาตรฐานกระทรวงสาธารณสุข ได้แก่:</p>
                <div class="row g-1 small text-muted mb-3">
                    <div class="col-6">
                        <ul class="mb-0 ps-3">
                            <li>นายแพทย์</li>
                            <li>พยาบาลวิชาชีพ</li>
                            <li>เภสัชกร</li>
                            <li>ทันตแพทย์</li>
                            <li>นักเทคนิคการแพทย์</li>
                            <li>นักกายภาพบำบัด</li>
                            <li>นักรังสีการแพทย์</li>
                            <li>นักโภชนาการ</li>
                        </ul>
                    </div>
                    <div class="col-6">
                        <ul class="mb-0 ps-3">
                            <li>นักสังคมสงเคราะห์</li>
                            <li>นักวิชาการสาธารณสุข</li>
                            <li>เจ้าพนักงานสาธารณสุข</li>
                            <li>นักวิเคราะห์นโยบายและแผน</li>
                            <li>นักทรัพยากรบุคคล</li>
                            <li>นักจัดการงานทั่วไป</li>
                            <li>นักวิชาการคอมพิวเตอร์</li>
                        </ul>
                    </div>
                </div>
                <div class="alert border-0 rounded-2 d-flex gap-2 align-items-start py-2 px-3 mb-0" style="background:#fff3cd">
                    <i class="bi bi-exclamation-triangle text-warning mt-1 flex-shrink-0"></i>
                    <div class="small">
                        ตำแหน่งที่<strong>มีอยู่แล้ว</strong> (position_code ซ้ำ) จะถูกข้ามไป ข้อมูลเดิมจะไม่ถูกทับ<br>
                        หลังนำเข้าแนะนำให้แก้ไข <strong>ตำแหน่งงาน</strong> ให้ตรงกับ Categorise ของระบบ
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <?= Html::beginForm(['import-seed'], 'post') ?>
                    <?= Html::submitButton('<i class="bi bi-cloud-download me-1"></i> นำเข้าเลย', ['class' => 'btn btn-success']) ?>
                <?= Html::endForm() ?>
            </div>
        </div>
    </div>
</div>

<?php Pjax::begin(['id' => 'jd-template-index']); ?>
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body">
        <?php $form = ActiveForm::begin([
            'method' => 'get',
            'action' => ['index'],
            'options' => ['class' => 'mb-3', 'data-pjax' => 1],
        ]); ?>
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <?= $form->field($searchModel, 'name')->textInput([
                    'class' => 'form-control',
                    'placeholder' => 'ค้นหา ชื่อ template',
                    'autocomplete' => 'off',
                ])->label('ค้นหา') ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($searchModel, 'position_code')->dropDownList(
                    ['' => '-- ทุกตำแหน่ง --'] + \app\components\CategoriseHelper::PositionName(),
                    ['class' => 'form-select']
                )->label('ตำแหน่งงาน') ?>
            </div>
            <div class="col-md-2">
                <?= $form->field($searchModel, 'is_active')->dropDownList(
                    ['' => 'ทั้งหมด', 1 => 'ใช้งาน', 0 => 'ปิดใช้'],
                    ['class' => 'form-select']
                )->label('สถานะ') ?>
            </div>
            <div class="col-md-2">
                <?= Html::submitButton('<i class="bi bi-search me-1"></i> ค้นหา', ['class' => 'btn btn-primary']) ?>
                <?= Html::a('ล้าง', ['index'], ['class' => 'btn btn-outline-secondary', 'data-pjax' => '0']) ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-nowrap">ลำดับ</th>
                        <th>ชื่อ Template</th>
                            <th>ตำแหน่งงาน</th>
                            <th>ระดับ</th>
                            <th>สถานะ</th>
                            <th class="text-nowrap">หัวข้อ</th>
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
                            <td class="text-nowrap"><?= $item->job_level ? Html::encode($item->job_level) : '<span class="text-muted">—</span>' ?></td>
                            <td>
                                <?= $item->is_active
                                    ? Html::tag('span', 'ใช้งาน', ['class' => 'badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill fw-medium px-2 py-1'])
                                    : Html::tag('span', 'ปิดใช้', ['class' => 'badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill fw-medium px-2 py-1']) ?>
                            </td>
                            <td><?= count($item->sections) ?></td>
                            <td class="text-end">
                                <?= Html::a('<i class="bi bi-eye"></i>', ['view', 'id' => $item->id], ['class' => 'btn btn-sm btn-outline-secondary', 'title' => 'ดู', 'data-pjax' => '0']) ?>
                                <?= Html::a('<i class="bi bi-pencil"></i>', ['update', 'id' => $item->id], ['class' => 'btn btn-sm btn-outline-primary', 'title' => 'แก้ไข', 'data-pjax' => '0']) ?>
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
