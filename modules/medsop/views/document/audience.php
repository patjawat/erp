<?php

use app\modules\medsop\assets\MedSopAsset;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

MedSopAsset::register($this);
$this->title = 'ผู้มีสิทธิ์อ่านและรับทราบ';
$appointmentsByTeam = [];
foreach ($appointments as $appointment) {
    $appointmentsByTeam[(int) $appointment->category_id][] = $appointment;
}
?>

<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?><?= Html::encode($model->document_no . ' · ' . $model->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<div class="d-flex flex-wrap align-items-center gap-2">
    <?= $this->render('_nav', ['access' => $access, 'active' => 'index']) ?>
    <?= Html::a('<i class="bi bi-arrow-left me-1" aria-hidden="true"></i>กลับไปยังเอกสาร', ['view', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
    <?= Html::beginForm(['publish', 'id' => $model->id], 'post', ['class' => 'd-inline']) ?>
    <?= Html::submitButton('<i class="bi bi-send-check me-1" aria-hidden="true"></i>เผยแพร่เอกสาร', [
        'class' => 'btn btn-sm btn-primary rounded-pill px-3',
        'data-medsop-confirm' => true,
        'data-confirm-title' => 'ยืนยันการเผยแพร่',
        'data-confirm-text' => 'ระบบจะส่งเอกสารให้ผู้รับที่บันทึกไว้',
        'data-confirm-label' => 'เผยแพร่เอกสาร',
    ]) ?>
    <?= Html::endForm() ?>
</div>
<?php $this->endBlock(); ?>

<?php $form = ActiveForm::begin([
    'id' => 'medsop-audience-form',
    'action' => ['audience', 'id' => $model->id],
    'options' => [
        'data-audience-form' => true,
        'data-preview-url' => Url::to(['audience-preview', 'id' => $model->id]),
    ],
]); ?>
<?= Html::hiddenInput('audience_intent', 'draft', ['data-audience-intent' => true]) ?>

<div class="row g-3 align-items-start">
    <div class="col-12 col-xl-8">
        <section class="surface-card mb-3" aria-labelledby="organization-audience-title">
            <div class="surface-card__head d-flex justify-content-between align-items-center gap-3">
                <div>
                    <h2 id="organization-audience-title" class="surface-card__title mb-1">หน่วยงานตามผังองค์กร</h2>
                    <p class="small text-body-secondary mb-0">เลือกเฉพาะหน่วยงาน หรือรวมหน่วยงานย่อยภายใต้โหนดเดียวกัน</p>
                </div>
                <span class="count-pill" data-organization-count>0</span>
            </div>
            <div class="surface-card__body p-0">
                <div class="medsop-audience-list" role="group" aria-label="เลือกหน่วยงาน">
                    <?php foreach ($organizations as $organization): ?>
                        <?php $selected = $selectedOrganizations[(int) $organization->id] ?? null; ?>
                        <div class="medsop-audience-row audience-level-<?= min(6, max(0, (int) $organization->lvl)) ?>">
                            <label class="medsop-audience-row__main" for="audience-org-<?= (int) $organization->id ?>">
                                <?= Html::checkbox("audiences[organizations][{$organization->id}][selected]", $selected !== null, [
                                    'id' => 'audience-org-' . (int) $organization->id,
                                    'value' => 1,
                                    'data-audience-rule' => 'organization',
                                ]) ?>
                                <span><strong><?= Html::encode($organization->name) ?></strong><small>ระดับ <?= number_format((int) $organization->lvl + 1) ?></small></span>
                            </label>
                            <label class="medsop-audience-row__option">
                                <?= Html::checkbox("audiences[organizations][{$organization->id}][include_children]", $selected && $selected->include_children, ['value' => 1]) ?>
                                รวมหน่วยงานย่อย
                            </label>
                            <?= Html::hiddenInput("audiences[organizations][{$organization->id}][required]", 1) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="surface-card mb-3" aria-labelledby="team-audience-title">
            <div class="surface-card__head d-flex justify-content-between align-items-center gap-3">
                <div>
                    <h2 id="team-audience-title" class="surface-card__title mb-1">กลุ่มและทีมประสาน</h2>
                    <p class="small text-body-secondary mb-0">รายชื่ออ้างอิงตามวาระหรือคำสั่งแต่งตั้งในระบบบุคลากร</p>
                </div>
                <span class="count-pill" data-team-count>0</span>
            </div>
            <div class="surface-card__body p-0">
                <?php if ($teamGroups): ?>
                    <div class="medsop-audience-list" role="group" aria-label="เลือกกลุ่มและทีมประสาน">
                        <?php foreach ($teamGroups as $team): ?>
                            <?php $teamAppointments = $appointmentsByTeam[(int) $team->id] ?? []; ?>
                            <?php foreach ($teamAppointments as $appointment): ?>
                                <?php $selected = $selectedTeams[(int) $appointment->id] ?? null; ?>
                                <label class="medsop-audience-row medsop-audience-row--team" for="audience-team-<?= (int) $appointment->id ?>">
                                    <span class="medsop-audience-row__main">
                                        <?= Html::checkbox("audiences[teams][{$appointment->id}][selected]", $selected !== null, [
                                            'id' => 'audience-team-' . (int) $appointment->id,
                                            'value' => 1,
                                            'data-audience-rule' => 'team',
                                        ]) ?>
                                        <span><strong><?= Html::encode($team->title) ?></strong><small><?= Html::encode($appointment->title) ?> · ปี <?= Html::encode($appointment->thai_year) ?></small></span>
                                    </span>
                                    <span class="count-pill"><?= number_format((int) ($teamMemberCounts[$appointment->id]['total'] ?? 0)) ?> คน</span>
                                    <?= Html::hiddenInput("audiences[teams][{$appointment->id}][team_group_id]", (int) $team->id) ?>
                                    <?= Html::hiddenInput("audiences[teams][{$appointment->id}][required]", 1) ?>
                                </label>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-block m-3"><strong>ยังไม่มีกลุ่มหรือทีมประสาน</strong><p class="mb-0">เพิ่มข้อมูลได้จากระบบบุคลากร แล้วกลับมาเลือกในหน้านี้</p></div>
                <?php endif; ?>
            </div>
        </section>

        <section class="surface-card" aria-labelledby="employee-audience-title">
            <div class="surface-card__head d-flex justify-content-between align-items-center gap-3">
                <div>
                    <h2 id="employee-audience-title" class="surface-card__title mb-1">บุคลากรรายคน</h2>
                    <p class="small text-body-secondary mb-0">ใช้สำหรับเพิ่มผู้เกี่ยวข้องนอกเหนือจากหน่วยงานหรือทีมที่เลือก</p>
                </div>
                <span class="count-pill" data-employee-count>0</span>
            </div>
            <div class="surface-card__body">
                <div class="search-input-wrap mb-3">
                    <i class="bi bi-search search-input__icon" aria-hidden="true"></i>
                    <input type="search" class="form-control form-control-input" placeholder="ค้นหาชื่อ ตำแหน่ง หรือหน่วยงาน" data-employee-search aria-label="ค้นหาบุคลากร">
                </div>
                <label class="form-label" for="audience-employee-ids">รายชื่อบุคลากรที่ยังปฏิบัติงาน</label>
                <select id="audience-employee-ids" class="form-select medsop-employee-select" name="audiences[employee_ids][]" multiple data-employee-select>
                    <?php foreach ($employees as $employee): ?>
                        <?php $departmentName = $employee->empDepartment ? $employee->empDepartment->name : 'ไม่ระบุหน่วยงาน'; ?>
                        <option value="<?= (int) $employee->id ?>" <?= in_array((int) $employee->id, $selectedEmployeeIds, true) ? 'selected' : '' ?> data-search="<?= Html::encode(mb_strtolower($employee->fullname() . ' ' . $employee->positionName() . ' ' . $departmentName)) ?>">
                            <?= Html::encode($employee->fullname() . ' · ' . $employee->positionName() . ' · ' . $departmentName) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="form-text mb-0">กด Ctrl หรือ Command ค้างเพื่อเลือกหลายรายชื่อบนคอมพิวเตอร์</p>
            </div>
        </section>
    </div>

    <aside class="col-12 col-xl-4">
        <div class="surface-card medsop-audience-summary position-sticky" aria-live="polite">
            <div class="surface-card__head"><h2 class="surface-card__title mb-0">สรุปผู้รับเอกสาร</h2></div>
            <div class="surface-card__body">
                <div class="summary-recap mb-3">
                    <div class="summary-recap__row"><span>กติกาที่เลือก</span><strong data-preview-rules>0</strong></div>
                    <div class="summary-recap__row"><span>บุคลากรรวม</span><strong data-preview-total>0 คน</strong></div>
                </div>
                <div data-preview-state class="picker-state">
                    <div class="picker-state__icon"><i class="bi bi-people" aria-hidden="true"></i></div>
                    <strong class="picker-state__title">ยังไม่ได้เลือกผู้รับ</strong>
                    <span class="picker-state__caption">เลือกหน่วยงาน ทีม หรือบุคลากรเพื่อดูรายชื่อรวม</span>
                </div>
                <ul class="list-unstyled medsop-preview-people mb-0" data-preview-list></ul>
                <p class="small text-body-secondary mt-2 mb-0 d-none" data-preview-more></p>
            </div>
            <div class="p-3 border-top d-grid gap-2">
                <?= Html::submitButton('<i class="bi bi-send-check me-1" aria-hidden="true"></i>บันทึกและเผยแพร่', [
                    'class' => 'btn btn-primary btn-block',
                    'name' => 'audience_intent',
                    'value' => 'publish',
                    'formaction' => Url::to(['audience-publish', 'id' => $model->id]),
                    'formmethod' => 'post',
                    'data-publish-submit' => true,
                    'data-medsop-confirm' => true,
                    'data-confirm-title' => 'บันทึกและเผยแพร่',
                    'data-confirm-text' => 'ระบบจะบันทึกผู้รับและเผยแพร่เอกสารทันที',
                    'data-confirm-label' => 'บันทึกและเผยแพร่',
                ]) ?>
                <?= Html::submitButton('<i class="bi bi-check2-circle me-1" aria-hidden="true"></i>บันทึกเป็นฉบับร่าง', ['class' => 'btn btn-outline-secondary btn-block']) ?>
                <?= Html::a('ยกเลิก', ['view', 'id' => $model->id], ['class' => 'btn btn-outline-secondary btn-block']) ?>
            </div>
        </div>
    </aside>
</div>

<?php ActiveForm::end(); ?>

<?php
$script = <<<'JS'
(function () {
  const form = document.querySelector('[data-audience-form]');
  if (!form) return;
  const employeeSearch = form.querySelector('[data-employee-search]');
  const employeeSelect = form.querySelector('[data-employee-select]');
  const publishSubmit = form.querySelector('[data-publish-submit]');
  const audienceIntent = form.querySelector('[data-audience-intent]');
  const previewState = form.querySelector('[data-preview-state]');
  const previewList = form.querySelector('[data-preview-list]');
  const previewMore = form.querySelector('[data-preview-more]');
  let timer;
  let requestController;

  publishSubmit.addEventListener('click', function (event) {
    audienceIntent.value = 'publish';
    form.action = publishSubmit.formAction;
  });

  function updateLocalCounts() {
    ['organization', 'team'].forEach(function (type) {
      const count = form.querySelectorAll('[data-audience-rule="' + type + '"]:checked').length;
      const output = form.querySelector('[data-' + type + '-count]');
      if (output) output.textContent = count;
    });
    form.querySelector('[data-employee-count]').textContent = employeeSelect.selectedOptions.length;
  }

  function renderPreview(data) {
    form.querySelector('[data-preview-rules]').textContent = data.rules;
    form.querySelector('[data-preview-total]').textContent = data.total.toLocaleString('th-TH') + ' คน';
    previewList.replaceChildren();
    previewState.classList.toggle('d-none', data.total > 0);
    data.items.forEach(function (person) {
      const item = document.createElement('li');
      item.className = 'medsop-preview-person';
      const name = document.createElement('strong');
      name.textContent = person.name;
      const department = document.createElement('span');
      department.textContent = person.department;
      item.append(name, department);
      previewList.appendChild(item);
    });
    const remaining = Math.max(0, data.total - data.items.length);
    previewMore.classList.toggle('d-none', remaining === 0);
    previewMore.textContent = remaining ? 'และบุคลากรอีก ' + remaining.toLocaleString('th-TH') + ' คน' : '';
  }

  function refreshPreview() {
    clearTimeout(timer);
    timer = setTimeout(function () {
      updateLocalCounts();
      if (requestController) requestController.abort();
      requestController = new AbortController();
      const body = new FormData(form);
      fetch(form.dataset.previewUrl, {
        method: 'POST',
        body: body,
        headers: {'X-Requested-With': 'XMLHttpRequest'},
        signal: requestController.signal
      }).then(function (response) {
        if (!response.ok) throw new Error('ไม่สามารถตรวจสอบรายชื่อผู้รับได้');
        return response.json();
      }).then(function (data) {
        if (!data.success) throw new Error(data.message || 'ไม่สามารถตรวจสอบรายชื่อผู้รับได้');
        renderPreview(data);
      }).catch(function (error) {
        if (error.name === 'AbortError') return;
        previewState.classList.remove('d-none');
        previewState.querySelector('.picker-state__title').textContent = 'ตรวจสอบรายชื่อไม่สำเร็จ';
        previewState.querySelector('.picker-state__caption').textContent = error.message;
      });
    }, 240);
  }

  employeeSearch.addEventListener('input', function () {
    const query = employeeSearch.value.trim().toLocaleLowerCase('th-TH');
    Array.from(employeeSelect.options).forEach(function (option) {
      option.hidden = query !== '' && !option.dataset.search.includes(query);
    });
  });
  form.addEventListener('change', refreshPreview);
  updateLocalCounts();
  refreshPreview();
})();
JS;
$this->registerJs($script);
?>
