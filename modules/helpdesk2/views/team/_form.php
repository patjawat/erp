<?php

use yii\helpers\Html;
use kartik\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\helpdesk2\models\HelpdeskDetail $model */
/** @var app\modules\helpdesk2\models\Helpdesk $helpdesk */
/** @var app\modules\hr\models\Employees[] $technicians */
/** @var string $repairGroupLabel */
?>

<?php $form = ActiveForm::begin([
    'id' => 'assign-team-form',
    'enableAjaxValidation' => false,
]); ?>

<div class="border rounded-3 bg-body-tertiary p-3 mb-4">
    <div class="small text-body-secondary">แผนกช่างที่รับงาน</div>
    <div class="fw-semibold text-body-emphasis mt-1"><?= Html::encode($repairGroupLabel) ?></div>
    <div class="small text-body-secondary mt-2">
        รายการด้านล่างแสดงเฉพาะบุคลากรที่ยังปฏิบัติงานและมีสิทธิ์ในระบบซ่อมของแผนกนี้
    </div>
</div>

<div id="assign-team-feedback" class="alert alert-danger d-none" role="alert"></div>

<?php if (empty($technicians)): ?>
    <div class="alert alert-secondary mb-0" role="status" tabindex="-1" data-assign-team-empty>
        <div class="fw-semibold">ไม่มีช่างที่สามารถเพิ่มได้</div>
        <div class="small mt-1">
            บุคลากรที่มีสิทธิ์ในแผนกนี้ถูกมอบหมายแล้วทั้งหมด หรือยังไม่ได้กำหนดสิทธิ์ในระบบ
        </div>
    </div>
<?php else: ?>
    <fieldset>
        <legend class="h6 fw-semibold mb-3">เลือกช่างผู้รับผิดชอบ</legend>
        <div class="d-flex flex-column gap-2" role="radiogroup" aria-label="รายชื่อช่างที่เลือกได้">
            <?php foreach ($technicians as $technician): ?>
                <?php
                $technicianId = (int) $technician->id;
                $inputId = 'assign-team-employee-' . $technicianId;
                $fullname = trim((string) ($technician->fullname ?? '')) ?: 'ไม่ระบุชื่อ';
                $department = method_exists($technician, 'departmentName')
                    ? (string) $technician->departmentName()
                    : 'ไม่ระบุหน่วยงาน';
                $avatar = method_exists($technician, 'ShowAvatar') ? $technician->ShowAvatar() : '';
                ?>
                <?= Html::activeRadio($model, 'emp_id', [
                    'id' => $inputId,
                    'value' => $technicianId,
                    'label' => null,
                    'class' => 'btn-check',
                    'autocomplete' => 'off',
                    'required' => true,
                    'uncheck' => false,
                ]) ?>
                <label class="btn btn-outline-secondary text-start p-3 d-flex align-items-center gap-3" for="<?= Html::encode($inputId) ?>">
                    <?= Html::img($avatar, [
                        'class' => 'rounded-circle border object-fit-cover flex-shrink-0',
                        'alt' => '',
                        'loading' => 'lazy',
                        'width' => 40,
                        'height' => 40,
                    ]) ?>
                    <span class="d-block overflow-hidden">
                        <span class="d-block fw-semibold text-break"><?= Html::encode($fullname) ?></span>
                        <span class="d-block small text-body-secondary text-break"><?= Html::encode($department) ?></span>
                    </span>
                </label>
            <?php endforeach; ?>
        </div>
    </fieldset>
<?php endif; ?>

<?= $form->field($model, 'helpdesk_id')->hiddenInput()->label(false) ?>

<div class="d-grid d-sm-flex justify-content-sm-end gap-2 border-top mt-4 pt-3">
    <?php if (!empty($technicians)): ?>
        <?= Html::submitButton(
            '<i class="fa-solid fa-user-plus me-1" aria-hidden="true"></i> เพิ่มช่างผู้รับผิดชอบ',
            ['class' => 'btn btn-primary', 'id' => 'assign-team-submit']
        ) ?>
    <?php endif; ?>
    <?= Html::button('ยกเลิก', [
        'class' => 'btn btn-outline-secondary',
        'data' => ['bs-dismiss' => 'offcanvas'],
    ]) ?>
</div>

<?php ActiveForm::end(); ?>

<?php
$js = <<<JS
$(document)
  .off('beforeSubmit.assignTeam', '#assign-team-form')
  .on('beforeSubmit.assignTeam', '#assign-team-form', function (event) {
    event.preventDefault();

    var form = $(this);
    var feedback = $('#assign-team-feedback');
    var submitButton = $('#assign-team-submit');
    var selectedTechnician = form.find('input[name="HelpdeskDetail[emp_id]"]:checked');

    feedback.addClass('d-none').text('');
    if (!selectedTechnician.length) {
      feedback.removeClass('d-none').text('กรุณาเลือกช่างผู้รับผิดชอบ');
      return false;
    }

    if (submitButton.data('request-pending')) {
      return false;
    }

    submitButton
      .data('request-pending', true)
      .data('original-html', submitButton.html())
      .prop('disabled', true)
      .attr('aria-busy', 'true')
      .html('<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>กำลังเพิ่มช่าง...');

    $.ajax({
      url: form.attr('action'),
      type: 'post',
      data: form.serialize(),
      dataType: 'json'
    })
      .done(function (response) {
        if (!response || response.status !== 'success') {
          feedback
            .removeClass('d-none')
            .text((response && response.message) || 'ไม่สามารถเพิ่มช่างผู้รับผิดชอบได้');
          return;
        }

        Swal.fire({
          icon: 'success',
          title: response.message || 'เพิ่มช่างผู้รับผิดชอบเรียบร้อยแล้ว',
          timer: 900,
          showConfirmButton: false
        }).then(function () {
          var offcanvasElement = document.getElementById('assign-team-offcanvas');
          var refreshView = function () {
            if (typeof window.refreshRepairView === 'function') {
              window.refreshRepairView();
              return;
            }
            window.location.reload();
          };

          if (offcanvasElement && offcanvasElement.classList.contains('show')) {
            $(offcanvasElement).one('hidden.bs.offcanvas.assignTeam', refreshView);
            bootstrap.Offcanvas.getOrCreateInstance(offcanvasElement).hide();
            return;
          }

          refreshView();
        });
      })
      .fail(function (xhr) {
        var response = xhr.responseJSON || {};
        feedback
          .removeClass('d-none')
          .text(response.message || 'ไม่สามารถเชื่อมต่อระบบได้ กรุณาลองใหม่อีกครั้ง');
      })
      .always(function () {
        submitButton
          .prop('disabled', false)
          .removeAttr('aria-busy')
          .html(submitButton.data('original-html') || 'เพิ่มช่างผู้รับผิดชอบ')
          .removeData('request-pending original-html');
      });

    return false;
  });
JS;
$this->registerJs($js);
?>
