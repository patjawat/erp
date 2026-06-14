<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/** @var yii\web\View $this */
/** @var string $current_page */
/** @var app\modules\leave\models\Leave $model */
/** @var app\modules\approveV2\models\Approve $approve */

$this->params['current_page'] = $current_page ?? 'services';
$this->params['mobileTitle'] = 'อนุมัติใบลา';
$this->params['mobileSubtitle'] = 'ตรวจสอบรายละเอียดและบันทึกผลการพิจารณา';

$modelData = is_array($model->data_json ?? null) ? $model->data_json : [];
$approveData = is_array($approve->data_json ?? null) ? $approve->data_json : [];

$requesterName = '-';
$requesterPosition = '';
$requesterAvatarUrl = \Yii::getAlias('@web') . '/img/placeholder_cid.png';
try {
    $requesterEmployee = $model->employee ?? null;
    $requesterName = (string) ($requesterEmployee->fullname ?? '-');
    $requesterPosition = (string) ($requesterEmployee ? $requesterEmployee->positionName() : '');
    if ($requesterEmployee && method_exists($requesterEmployee, 'ShowAvatar')) {
        $avatar = (string) $requesterEmployee->ShowAvatar();
        if ($avatar !== '') {
            $requesterAvatarUrl = $avatar;
        }
    }
} catch (\Throwable $e) {
    $requesterName = '-';
    $requesterPosition = '';
    $requesterAvatarUrl = \Yii::getAlias('@web') . '/img/placeholder_cid.png';
}

$leaveType = (string) ($model->leaveType->title ?? 'ใบลา');
$dateRange = trim(preg_replace('/\s+/', ' ', strip_tags((string) $model->showLeaveDate())));
$reason = trim((string) ($modelData['reason'] ?? ''));
$stepLabel = (string) ($approveData['label'] ?? $approve->title ?? 'ผู้อนุมัติ');
$approveItems = $model->listApprove();
$totalDays = (float) ($model->total_days ?? 0);
$totalDaysText = rtrim(rtrim(number_format($totalDays, 1), '0'), '.');
if ($totalDaysText === '') {
    $totalDaysText = '0';
}

$backParams = ['/mobile/default/leave-approvals'];
if (!empty($model->thai_year)) {
    $backParams['year'] = (int) $model->thai_year;
}
$backUrl = Url::to($backParams);
?>

<style>
.lav-root {
    margin: -1rem -1rem 0;
}
.lav-scroll {
    padding-bottom: calc(env(safe-area-inset-bottom, 0px) + 8rem);
}
.lav-body {
    display: flex;
    flex-direction: column;
    gap: var(--space-md);
    padding: var(--space-md);
}
.lav-back {
    min-height: 2.75rem;
    width: fit-content;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-2xs);
    font-weight: 700;
    box-shadow: 0 1px 0 var(--ink-line);
    transition:
        transform 160ms cubic-bezier(0.16, 1, 0.3, 1),
        box-shadow 160ms cubic-bezier(0.16, 1, 0.3, 1);
}
.lav-back svg {
    width: 1.125rem;
    height: 1.125rem;
}
.lav-card {
    border: 1px solid var(--ink-line);
    border-radius: 16px;
    background: var(--surface);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
}
.lav-card-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: var(--space-md);
    padding: var(--space-md);
    border-bottom: 1px solid var(--ink-line);
}
.lav-card-title {
    display: inline-flex;
    align-items: center;
    gap: var(--space-xs);
    margin: 0;
    color: var(--ink);
    font-size: var(--fs-md);
    font-weight: 800;
    line-height: 1.3;
}
.lav-card-title svg {
    width: 1.125rem;
    height: 1.125rem;
    color: var(--mobile-primary);
}
.lav-card-body {
    padding: var(--space-md);
}
.lav-person {
    display: flex;
    align-items: flex-start;
    gap: var(--space-md);
    margin-bottom: var(--space-md);
}
.lav-person-main {
    display: flex;
    align-items: center;
    gap: var(--space-sm);
    min-width: 0;
}
.lav-person-avatar {
    position: relative;
    width: 4rem;
    height: 4rem;
    border-radius: 18px;
    flex: 0 0 auto;
    overflow: hidden;
    border: 1px solid var(--ink-line);
    background: var(--surface-2);
    box-shadow: 0 8px 22px color-mix(in oklch, var(--ink) 8%, transparent);
}
.lav-person-avatar img {
    width: 100%;
    height: 100%;
    display: block;
    object-fit: cover;
}
.lav-person-avatar-badge {
    position: absolute;
    right: -2px;
    bottom: -2px;
    width: 1.5rem;
    height: 1.5rem;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 2px solid var(--surface);
    background: var(--mobile-primary);
    color: #fff;
}
.lav-person-avatar-badge svg {
    width: .85rem;
    height: .85rem;
}
.lav-person-name {
    margin: 0;
    color: var(--ink);
    font-size: var(--fs-lg);
    font-weight: 800;
    line-height: 1.3;
    text-wrap: pretty;
}
.lav-person-role {
    margin: 2px 0 0;
    color: var(--ink-3);
    font-size: var(--fs-xs);
    line-height: 1.4;
}
.lav-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: var(--space-sm);
}
.lav-field {
    min-width: 0;
}
.lav-field.is-wide {
    grid-column: 1 / -1;
}
.lav-label {
    color: var(--ink-4);
    font-size: var(--fs-2xs);
    font-weight: 800;
    line-height: 1.35;
}
.lav-value {
    margin-top: 3px;
    color: var(--ink-2);
    font-size: var(--fs-sm);
    font-weight: 700;
    line-height: 1.45;
    overflow-wrap: anywhere;
}
.lav-value.is-primary {
    color: var(--mobile-primary);
}
.lav-approval-list {
    display: flex;
    flex-direction: column;
    gap: var(--space-xs);
}
.lav-approval-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: var(--space-sm);
    padding: var(--space-sm);
    border: 1px solid var(--ink-line);
    border-radius: 12px;
    background: var(--surface-2);
}
.lav-approval-title {
    color: var(--ink);
    font-size: var(--fs-sm);
    font-weight: 800;
    line-height: 1.35;
}
.lav-approval-person {
    margin-top: 2px;
    color: var(--ink-3);
    font-size: var(--fs-xs);
    line-height: 1.35;
}
.lav-actions {
    position: sticky;
    bottom: 0;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-sm);
    margin: var(--space-sm) calc(var(--space-md) * -1) calc(var(--space-md) * -1);
    padding: var(--space-sm) var(--space-md) calc(env(safe-area-inset-bottom, 0px) + var(--space-sm));
    background: color-mix(in oklch, var(--surface) 94%, transparent);
    backdrop-filter: blur(12px);
    box-shadow: 0 -1px 0 var(--ink-line), 0 -8px 22px color-mix(in oklch, var(--ink) 6%, transparent);
}
.lav-actions .btn {
    min-height: 3rem;
    border-radius: 12px;
    font-size: var(--fs-md);
    font-weight: 800;
}
.lav-actions .btn svg {
    width: 1.125rem;
    height: 1.125rem;
}
@media (hover: hover) {
    .lav-back:hover {
        transform: translateY(-1px);
        box-shadow: var(--shadow-sm);
    }
}
@media (prefers-reduced-motion: reduce) {
    .lav-back,
    .lav-card,
    .lav-approval-item,
    .lav-actions .btn {
        transition: none !important;
        animation: none !important;
    }
    .lav-back:hover {
        transform: none !important;
    }
}
@media (prefers-reduced-motion: no-preference) {
    .lav-card {
        animation: lav-card-in 220ms cubic-bezier(0.16, 1, 0.3, 1) both;
        animation-delay: calc(var(--lav-i, 0) * 35ms);
    }
}
@keyframes lav-card-in {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<div class="lav-root">
    <?= $this->render('@app/modules/mobile/views/layouts/_partials/_hero_shell', [
        'icon' => 'shield-check',
        'title' => $this->params['mobileTitle'],
        'subtitle' => $requesterName !== '-' ? 'ผู้ขอ ' . $requesterName : $this->params['mobileSubtitle'],
        'stats' => [
            ['value' => $totalDaysText, 'label' => 'วันลา', 'tone' => 'primary'],
            ['value' => count($approveItems), 'label' => 'ขั้นตอน', 'tone' => 'warning'],
        ],
        'statsLabel' => 'สรุปใบลาที่รออนุมัติ',
    ]) ?>

    <div class="app-scroll has-stats lav-scroll">
        <div class="lav-body">
            <a href="<?= Html::encode($backUrl) ?>" class="btn btn-outline-secondary lav-back">
                <i data-lucide="arrow-left" aria-hidden="true"></i>
                <span>กลับไปรายการอนุมัติ</span>
            </a>

            <section class="lav-card" style="--lav-i: 0" aria-labelledby="lav-request-title">
                <header class="lav-card-head">
                    <h2 class="lav-card-title" id="lav-request-title">
                        <i data-lucide="file-text" aria-hidden="true"></i>
                        ข้อมูลใบลา
                    </h2>
                    <div><?= $model->viewStatus() ?></div>
                </header>
                <div class="lav-card-body">
                    <div class="lav-person">
                        <div class="lav-person-main">
                            <span class="lav-person-avatar" aria-hidden="true">
                                <?= Html::img($requesterAvatarUrl, [
                                    'alt' => '',
                                    'loading' => 'eager',
                                    'decoding' => 'async',
                                ]) ?>
                                <span class="lav-person-avatar-badge">
                                    <i data-lucide="user-check" aria-hidden="true"></i>
                                </span>
                            </span>
                            <div class="min-w-0">
                                <p class="lav-person-name"><?= Html::encode($requesterName) ?></p>
                                <?php if ($requesterPosition !== ''): ?>
                                    <p class="lav-person-role"><?= Html::encode($requesterPosition) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="lav-grid">
                        <div class="lav-field">
                            <div class="lav-label">ประเภทการลา</div>
                            <div class="lav-value"><?= Html::encode($leaveType) ?></div>
                        </div>
                        <div class="lav-field">
                            <div class="lav-label">จำนวนวัน</div>
                            <div class="lav-value"><?= Html::encode($totalDaysText) ?> วัน</div>
                        </div>
                        <div class="lav-field is-wide">
                            <div class="lav-label">ช่วงเวลาที่ลา</div>
                            <div class="lav-value"><?= Html::encode($dateRange !== '' ? $dateRange : '-') ?></div>
                        </div>
                        <div class="lav-field is-wide">
                            <div class="lav-label">เหตุผล</div>
                            <div class="lav-value"><?= Html::encode($reason !== '' ? $reason : '-') ?></div>
                        </div>
                        <div class="lav-field is-wide">
                            <div class="lav-label">ขั้นตอนที่รออนุมัติ</div>
                            <div class="lav-value is-primary"><?= Html::encode($stepLabel) ?></div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="lav-card" style="--lav-i: 1" aria-labelledby="lav-approval-title">
                <header class="lav-card-head">
                    <h2 class="lav-card-title" id="lav-approval-title">
                        <i data-lucide="workflow" aria-hidden="true"></i>
                        ลำดับการอนุมัติ
                    </h2>
                </header>
                <div class="lav-card-body">
                    <div class="lav-approval-list">
                        <?php foreach ($approveItems as $item): ?>
                            <?php $itemData = is_array($item->data_json ?? null) ? $item->data_json : []; ?>
                            <div class="lav-approval-item">
                                <div class="min-w-0">
                                    <div class="lav-approval-title"><?= Html::encode($item->title ?: ($itemData['label'] ?? 'ผู้อนุมัติ')) ?></div>
                                    <div class="lav-approval-person"><?= Html::encode($item->employee->fullname ?? 'รอมอบหมาย') ?></div>
                                </div>
                                <div><?= $item->viewApproveStatus() ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

            <div class="lav-actions">
                <button type="button" class="btn btn-outline-danger approve-action" data-status="Reject">
                    <i data-lucide="x-circle" aria-hidden="true"></i>
                    ไม่อนุมัติ
                </button>
                <button type="button" class="btn btn-primary approve-action" data-status="Pass">
                    <i data-lucide="check-circle-2" aria-hidden="true"></i>
                    อนุมัติ
                </button>
            </div>
        </div>
    </div>
</div>

<?php
$updateUrl = Url::to(['/mobile/default/approve-leave-update', 'id' => $approve->id]);
$redirectUrl = Url::to(['/mobile/default/leave-approvals']);
$js = <<<JS
$('body').on('click', '.approve-action', function () {
    const status = $(this).data('status');
    const label = status === 'Pass' ? 'อนุมัติ' : 'ไม่อนุมัติ';

    Swal.fire({
        title: 'ยืนยันการทำรายการ',
        text: label + 'ใบลานี้ใช่หรือไม่',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'ยืนยัน',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (!result.isConfirmed) {
            return;
        }

        $.ajax({
            type: 'POST',
            url: '{$updateUrl}',
            data: {status: status},
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'บันทึกสำเร็จ',
                        timer: 1200,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = response.redirect || '{$redirectUrl}';
                    });
                    return;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด',
                    text: response.message || 'ไม่สามารถบันทึกข้อมูลได้'
                });
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด',
                    text: 'ไม่สามารถเชื่อมต่อกับระบบได้'
                });
            }
        });
    });
});
JS;
$this->registerJs($js, View::POS_END);
?>
