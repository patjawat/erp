<?php

use kartik\form\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

$data = $model->data_json ?? [];
$bindings = $bindings ?? [];
$departments = [];
foreach ($bindings as $bindingUser) {
    $departmentName = $bindingUser->employee ? ($bindingUser->employee->departmentName() ?: '-') : '-';
    $departments[$departmentName] = $departmentName;
}
ksort($departments);

$form = ActiveForm::begin([
'id' => 'telegram-setting-form',
'type' => ActiveForm::TYPE_VERTICAL
]);
?>

<div class="container-fluid py-3">

<div class="card border-0 shadow-sm">
    <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">

        <h4 class="mb-0">
            <i class="bi bi-telegram text-primary"></i>
            Telegram Settings
        </h4>

        <span class="badge bg-success px-3 py-2">
            <i class="bi bi-check-circle"></i>
            Bot Connected
        </span>

    </div>
    </div>
</div>




    <div class="row g-3">

        <!-- BOT CONFIG -->
        <div class="col-lg-6">

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-primary text-white py-2 px-3">
                    <i class="bi bi-robot"></i>
                    Bot Configuration
                </div>

                <div class="card-body">

                    <?= $form->field($model,'data_json[bot_token]')
->textInput([
'value'=>$data['bot_token'] ?? '',
'placeholder'=>'Bot Token'
]) ?>

                    <?= $form->field($model,'data_json[bot_username]')
->textInput([
'value'=>$data['bot_username'] ?? '',
'placeholder'=>'@yourbot'
]) ?>

                    <?= $form->field($model,'data_json[webhook]')
->textInput([
'value'=>$data['webhook'] ?? '',
'placeholder'=>'https://domain.com/telegram/webhook'
]) ?>

                    <hr>

                    <div class="d-flex gap-2 justify-content-end">

                        <button type="button" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-plug"></i>
                            Test Bot
                        </button>

                        <button type="button" class="btn btn-outline-dark btn-sm">
                            <i class="bi bi-link-45deg"></i>
                            Set Webhook
                        </button>

                    </div>

                </div>
            </div>

        </div>


        <!-- MINI APP -->
        <div class="col-lg-6">

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-primary text-white py-2 px-3">
                    <i class="bi bi-phone"></i>
                    Mini App
                </div>

                <div class="card-body">

                    <?= $form->field($model,'data_json[mini_app]')
->textInput([
'value'=>$data['mini_app'] ?? '',
'placeholder'=>'https://domain.com/mobile'
]) ?>

                    <?= $form->field($model,'data_json[enable_mini_app]')
->checkbox([
'value'=>1,
'checked'=>($data['enable_mini_app'] ?? 0)
])->label('Enable Mini App') ?>

                </div>

            </div>

        </div>




        <!-- NOTIFICATION -->
        <div class="col-lg-6">

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-primary text-white py-2 px-3">
                    <i class="bi bi-bell"></i>
                    Notification Settings
                </div>

                <div class="card-body">

                    <div class="row">

                        <div class="col-6">

                            <?= $form->field($model,'data_json[notify_approve]')
->checkbox([
'value'=>1,
'checked'=>($data['notify_approve'] ?? 0)
])->label('Approve Request') ?>

                        </div>

                        <div class="col-6">

                            <?= $form->field($model,'data_json[notify_asset]')
->checkbox([
'value'=>1,
'checked'=>($data['notify_asset'] ?? 0)
])->label('Asset') ?>

                        </div>

                        <div class="col-6">

                            <?= $form->field($model,'data_json[notify_leave]')
->checkbox([
'value'=>1,
'checked'=>($data['notify_leave'] ?? 0)
])->label('Leave') ?>

                        </div>

                        <div class="col-6">

                            <?= $form->field($model,'data_json[notify_repair]')
->checkbox([
'value'=>1,
'checked'=>($data['notify_repair'] ?? 0)
])->label('Repair') ?>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- USER BINDING -->
        <div class="col-12">

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-primary text-white py-2 px-3">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="fw-bold">
                            <i class="bi bi-people"></i>
                            Telegram User Binding
                        </div>
                        <span class="badge bg-light text-primary rounded-pill px-3 py-2">
                            <?= count($bindings) ?> บัญชีที่เชื่อมต่อ
                        </span>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-7">
                            <label for="telegram-binding-search" class="form-label">ค้นหาชื่อ, ตำแหน่ง หรือ Telegram ID</label>
                            <input type="text" id="telegram-binding-search" class="form-control" placeholder="พิมพ์เพื่อค้นหา...">
                        </div>
                        <div class="col-md-5">
                            <label for="telegram-binding-department" class="form-label">กรองตามแผนก</label>
                            <select id="telegram-binding-department" class="form-select">
                                <option value="">ทุกแผนก</option>
                                <?php foreach ($departments as $department): ?>
                                    <option value="<?= Html::encode($department) ?>"><?= Html::encode($department) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">

                        <thead class="table-light">

                            <tr>
                                <th>ชื่อ - นามสกุล</th>
                                <th>แผนก</th>
                                <th>Telegram ID</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>

                        </thead>

                        <tbody class="align-middle table-group-divider" id="telegram-binding-table-body">
                            <?php if (!empty($bindings)): ?>
                                <?php foreach ($bindings as $user): ?>
                                    <?php $employee = $user->employee; ?>
                                    <?php
                                    $fullname = $employee->fullname ?? $user->fullname ?? $user->username;
                                    $position = $employee ? ($employee->positionName() ?: '-') : ($user->username ?? '-');
                                    $department = $employee ? ($employee->departmentName() ?: '-') : '-';
                                    $avatarUrl = $employee ? $employee->showAvatar() : '@web/img/placeholder-img.jpg';
                                    ?>
                                    <tr>
                                        <td>
                                            <div
                                                class="d-flex align-items-center gap-3"
                                                data-search="<?= Html::encode(mb_strtolower($fullname . ' ' . $position . ' ' . $department . ' ' . $user->telegram_id)) ?>"
                                                data-department="<?= Html::encode($department) ?>"
                                            >
                                                <?= Html::img($avatarUrl, [
                                                    'class' => 'rounded-circle border flex-shrink-0',
                                                    'width' => 44,
                                                    'height' => 44,
                                                    'alt' => $fullname,
                                                ]) ?>
                                                <div>
                                                    <div class="fw-semibold"><?= Html::encode($fullname) ?></div>
                                                    <div class="small text-muted"><?= Html::encode($position) ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2">
                                                <?= Html::encode($department) ?>
                                            </span>
                                        </td>
                                        <td><code><?= Html::encode((string) $user->telegram_id) ?></code></td>
                                        <td>
                                            <span class="badge bg-success rounded-pill px-3 py-2">Connected</span>
                                        </td>
                                        <td class="text-end">
                                            <button
                                                type="button"
                                                class="btn btn-outline-primary btn-test-telegram"
                                                data-url="<?= Url::to(['/telegrambot/default/test-user', 'id' => $user->id]) ?>"
                                                data-name="<?= Html::encode($fullname) ?>"
                                            >
                                                <i class="bi bi-send"></i>
                                                ทดสอบส่งข้อความ
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr id="telegram-binding-empty-default">
                                    <td colspan="5" class="text-center text-muted py-4">
                                        ยังไม่พบผู้ใช้งานที่ผูก `telegram_id`
                                    </td>
                                </tr>
                            <?php endif; ?>
                            <tr id="telegram-binding-no-result" class="d-none">
                                <td colspan="5" class="text-center text-muted py-4">
                                    ไม่พบข้อมูลตามคำค้นหรือแผนกที่เลือก
                                </td>
                            </tr>

                        </tbody>

                    </table>
                    </div>

                </div>

            </div>

        </div>


    </div>


    <div class="text-end mt-4">

        <?= Html::submitButton(
'<i class="bi bi-save"></i> Save Settings',
[
'class'=>'btn btn-primary px-4',
'id'=>'btn-save-telegram'
]
) ?>

    </div>

</div>

<?php ActiveForm::end(); ?>

<?php
$js = <<<JS

$('#telegram-setting-form').on('beforeSubmit', function(){

Swal.fire({
title:'บันทึกการตั้งค่า?',
icon:'question',
showCancelButton:true,
confirmButtonText:'บันทึก'
}).then((result)=>{

if(result.isConfirmed){

Swal.fire({
title:'กำลังบันทึก...',
allowOutsideClick:false,
didOpen:()=>{
Swal.showLoading();
}
});

$('#telegram-setting-form')[0].submit();

}

});

return false;

});

$('body').on('click', '.btn-test-telegram', function () {
    const url = $(this).data('url');
    const name = $(this).data('name');

    Swal.fire({
        title: 'ส่งข้อความทดสอบ?',
        text: 'ต้องการส่งข้อความไปยัง ' + name + ' ใช่หรือไม่',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'ส่งข้อความ',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (!result.isConfirmed) {
            return;
        }

        Swal.fire({
            title: 'กำลังส่งข้อความ...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: url,
            type: 'POST',
            dataType: 'json',
            data: {
                _csrf: yii.getCsrfToken()
            },
            success: function (response) {
                Swal.fire({
                    icon: response.status === 'success' ? 'success' : 'error',
                    title: response.status === 'success' ? 'สำเร็จ' : 'ไม่สำเร็จ',
                    text: response.message || ''
                });
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด',
                    text: 'ไม่สามารถส่งข้อความทดสอบได้'
                });
            }
        });
    });
});

(function () {
    const searchInput = document.getElementById('telegram-binding-search');
    const departmentSelect = document.getElementById('telegram-binding-department');
    const body = document.getElementById('telegram-binding-table-body');
    const noResult = document.getElementById('telegram-binding-no-result');
    const defaultEmpty = document.getElementById('telegram-binding-empty-default');

    if (!searchInput || !departmentSelect || !body) {
        return;
    }

    const rows = Array.from(body.querySelectorAll('tr')).filter((row) => row.id !== 'telegram-binding-no-result' && row.id !== 'telegram-binding-empty-default');

    function applyBindingFilters() {
        const keyword = (searchInput.value || '').trim().toLowerCase();
        const department = departmentSelect.value || '';
        let visibleCount = 0;

        rows.forEach((row) => {
            const searchable = row.querySelector('[data-search]');
            if (!searchable) {
                return;
            }

            const searchText = searchable.getAttribute('data-search') || '';
            const rowDepartment = searchable.getAttribute('data-department') || '';
            const matchKeyword = keyword === '' || searchText.indexOf(keyword) !== -1;
            const matchDepartment = department === '' || rowDepartment === department;
            const visible = matchKeyword && matchDepartment;

            row.classList.toggle('d-none', !visible);
            if (visible) {
                visibleCount += 1;
            }
        });

        if (noResult) {
            noResult.classList.toggle('d-none', visibleCount > 0 || rows.length === 0);
        }
    }

    searchInput.addEventListener('input', applyBindingFilters);
    departmentSelect.addEventListener('change', applyBindingFilters);
})();

JS;
$this->registerJs($js, View::POS_END);
?>