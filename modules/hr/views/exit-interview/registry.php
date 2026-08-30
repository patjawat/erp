<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use app\components\widgets\DataSummaryWidget;
use app\modules\hr\models\ExitInterview;
$this->title = 'รายการ Exit Interview';
$this->beginBlock('page-title'); echo Html::encode($this->title); $this->endBlock();
$this->beginBlock('page-action'); echo $this->render('@app/modules/hr/menu', ['active' => 'exit']); $this->endBlock();
?>
<div class="container-fluid px-0">
<?= $this->render('_nav', ['active' => 'registry']) ?>
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3 mb-3"><div><h1 class="h3 mb-1">รายการสัมภาษณ์</h1><p class="text-body-secondary mb-0">สร้างแบบสัมภาษณ์ กรอกโดย HR หรือออกลิงก์ให้บุคลากรตอบด้วยตนเอง</p></div><div class="d-flex flex-wrap gap-2"><?= Html::a('<i data-lucide="upload"></i> นำเข้า Excel', ['import'], ['class' => 'btn btn-outline-secondary d-inline-flex align-items-center gap-2']) ?><?= Html::a('<i data-lucide="plus"></i> สร้างรายการ', ['create', 'title' => 'สร้างรายการสัมภาษณ์'], ['class' => 'btn btn-primary open-modal d-inline-flex align-items-center gap-2', 'data-size' => 'modal-lg']) ?></div></div>
<?= Html::beginForm(['registry'], 'get', ['class' => 'card bg-body border shadow-sm mb-3']) ?><div class="card-body"><div class="row g-2 align-items-end"><div class="col-12 col-md-6"><label class="form-label" for="exit-q">ค้นหา</label><?= Html::textInput('q', $q, ['class' => 'form-control', 'id' => 'exit-q', 'placeholder' => 'ชื่อหรือหน่วยงาน']) ?></div><div class="col-12 col-md-3"><label class="form-label" for="exit-status">สถานะ</label><?= Html::dropDownList('status', $status, ['' => 'ทุกสถานะ'] + ExitInterview::statusOptions(), ['class' => 'form-select', 'id' => 'exit-status']) ?></div><div class="col-12 col-md-3 d-flex gap-2"><?= Html::submitButton('ค้นหา', ['class' => 'btn btn-primary']) ?><?= Html::a('ล้าง', ['registry'], ['class' => 'btn btn-outline-secondary']) ?></div></div></div><?= Html::endForm() ?>
<?php Pjax::begin(['id' => 'exit-registry', 'enablePushState' => false]); ?>
<section class="card bg-body border shadow-sm"><div class="card-body p-0"><div class="d-none d-lg-block"><table class="table table-hover align-middle mb-0"><thead class="table-secondary"><tr><th>บุคลากร</th><th>วันที่ออก</th><th>ประเภท</th><th>ช่องทาง</th><th>สถานะ</th><th class="text-end">จัดการ</th></tr></thead><tbody>
<?php foreach ($dataProvider->models as $model): $activeLink = current(array_filter($model->links, static fn($l) => $l->status === 'active')) ?: null; ?><tr><td><strong><?= Html::encode($model->employee_name_snapshot) ?></strong><small class="d-block text-body-secondary"><?= Html::encode($model->department_name_snapshot ?: 'ไม่ระบุหน่วยงาน') ?></small></td><td class="font-monospace text-nowrap"><?= Html::encode($model->exit_date ?: '—') ?></td><td><?= Html::encode(ExitInterview::exitTypeOptions()[$model->exit_type] ?? $model->exit_type) ?></td><td><?= Html::encode(['hr_interview' => 'HR กรอก', 'self_service' => 'ลิงก์', 'excel_import' => 'Excel'][$model->response_source] ?? $model->response_source) ?></td><td><span class="badge <?= $model->status === 'submitted' ? 'bg-success-subtle text-success-emphasis' : ($model->status === 'draft' ? 'bg-warning-subtle text-warning-emphasis' : 'bg-secondary-subtle text-secondary-emphasis') ?>"><?= Html::encode($model->statusLabel) ?></span></td><td><div class="d-flex justify-content-end gap-1"><?= Html::a('<i data-lucide="file-pen"></i>', ['form', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-primary', 'title' => 'เปิดแบบสัมภาษณ์', 'aria-label' => 'เปิดแบบสัมภาษณ์', 'data-pjax' => '0']) ?><?= Html::button('<i data-lucide="link"></i>', ['class' => 'btn btn-sm btn-outline-secondary js-issue-link', 'data-url' => Url::to(['issue-link', 'id' => $model->id]), 'title' => 'สร้างและคัดลอกลิงก์', 'aria-label' => 'สร้างและคัดลอกลิงก์']) ?><?php if ($activeLink): ?><?= Html::a('<i data-lucide="link-2-off"></i>', ['revoke-link', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-danger', 'data-method' => 'post', 'data-confirm' => 'ยกเลิกลิงก์ที่ใช้งานอยู่หรือไม่', 'title' => 'ยกเลิกลิงก์', 'aria-label' => 'ยกเลิกลิงก์']) ?><?php endif ?></div></td></tr><?php endforeach ?>
<?php if (!$dataProvider->models): ?><tr><td colspan="6" class="text-center text-body-secondary py-5">ยังไม่มีรายการสัมภาษณ์</td></tr><?php endif ?></tbody></table></div>
<ul class="list-group list-group-flush d-lg-none" role="list"><?php foreach ($dataProvider->models as $model): ?><li class="list-group-item p-3"><div class="d-flex justify-content-between gap-3"><div><strong><?= Html::encode($model->employee_name_snapshot) ?></strong><small class="d-block text-body-secondary mt-1"><?= Html::encode(($model->exit_date ?: 'ไม่ระบุวันที่') . ' · ' . ($model->department_name_snapshot ?: 'ไม่ระบุหน่วยงาน')) ?></small></div><span class="badge bg-secondary-subtle text-secondary-emphasis align-self-start"><?= Html::encode($model->statusLabel) ?></span></div><div class="d-grid d-sm-flex gap-2 mt-3"><?= Html::a('เปิดแบบสัมภาษณ์', ['form', 'id' => $model->id], ['class' => 'btn btn-primary', 'data-pjax' => '0']) ?><?= Html::button('สร้างลิงก์', ['class' => 'btn btn-outline-secondary js-issue-link', 'data-url' => Url::to(['issue-link', 'id' => $model->id])]) ?></div></li><?php endforeach ?></ul></div><div class="card-footer bg-body"><?= DataSummaryWidget::widget(['dataProvider' => $dataProvider]) ?></div></section>
<?php Pjax::end(); ?></div>
<?php
$csrf = Yii::$app->request->csrfToken;
$this->registerJs(<<<JS
document.addEventListener('click', async function (event) {
  var button = event.target.closest('.js-issue-link');
  if (!button) return;
  button.disabled = true;
  try {
    var body = new URLSearchParams(); body.append('_csrf', '{$csrf}'); body.append('days', '14');
    var response = await fetch(button.dataset.url, {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:body.toString()});
    var data = await response.json();
    if (!response.ok || !data.url) throw new Error(data.message || 'สร้างลิงก์ไม่สำเร็จ');
    await navigator.clipboard.writeText(data.url);
    if (window.Swal) Swal.fire({icon:'success', title:'คัดลอกลิงก์แล้ว', text:'ลิงก์มีอายุ 14 วัน', timer:1800, showConfirmButton:false});
    else alert('คัดลอกลิงก์แล้ว');
  } catch (error) { alert(error.message); } finally { button.disabled = false; }
});
JS);
?>
