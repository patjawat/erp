<?php
use yii\helpers\Html;
use yii\widgets\Pjax;
use app\components\AppHelper;
use yii\bootstrap5\LinkPager;
use yii\bootstrap5\Breadcrumbs;

$this->title = 'ทะเบียนประวัติ';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th class="text-center text-muted small fw-semibold" style="width:50px">#</th>
                <th class="text-muted small fw-semibold">บุคลากร</th>
                <th class="text-muted small fw-semibold">ตำแหน่ง / ประเภท</th>
                <th class="text-muted small fw-semibold">หน่วยงาน</th>
                <th class="text-muted small fw-semibold text-center">เริ่มงาน / อายุงาน</th>
                <th class="text-muted small fw-semibold text-center">ประเภทเวร</th>
                <th class="text-muted small fw-semibold">สถานะ</th>
                <th class="text-muted small fw-semibold" style="width:230px">เกษียณ / สิ้นสุดสัญญาจ้าง</th>
                <th class="text-muted small fw-semibold text-center" style="width:80px">จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                <tr>
                    <td class="text-center text-muted small"><?php echo (($dataProvider->pagination->offset + 1) + $key) ?></td>
                    <td>
                        <?= Html::a($item->getAvatar(false), ['/hr/employees/view', 'id' => $item->id], ['class' => 'text-decoration-none']) ?>
                    </td>
                    <td>
                        <div class="fw-semibold"><?= $item->positionType?->title ?? '<span class="text-muted fw-normal">ไม่ระบุ</span>' ?></div>
                        <div class="small text-muted"><?= $item->positionName(['icon' => true]) ?></div>
                    </td>
                    <td>
                        <span class="text-truncate d-inline-block" style="max-width:180px">
                            <?= $item->departmentName() ?: '<span class="text-muted">ไม่ระบุ</span>' ?>
                        </span>
                    </td>
                    <td class="text-center">
                        <div class="small">
                            <?php
                            try {
                                echo Yii::$app->thaiFormatter->asDate($item->joinDate(), 'medium');
                            } catch (\Throwable $th) {
                            }
                            ?>
                        </div>
                        <div class="small text-muted"><?= $item->age_join_date['full'] ?></div>
                    </td>
                    <td class="text-center"><?= $item->viewWorkType() ?></td>
                    <td>
                        <span class="badge rounded-pill bg-success bg-opacity-10 text-success-emphasis">
                            <i class="bi bi-clipboard-check me-1"></i><?= $item->statusName() ?>
                        </span>
                    </td>
                    <td>
                        <div class="small mb-1"><?= AppHelper::CountDown($item->Retire()['date']) ?></div>
                        <div class="progress progress-sm w-100">
                            <div class="progress-bar bg-<?= $item->Retire()['color'] ?>" role="progressbar"
                                <?= "style='width:" . $item->Retire()['progress'] . "%;'" ?> aria-valuenow="65"
                                aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </td>
                    <td class="text-center">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary border-0" type="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-solid fa-ellipsis-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                <li><?= Html::a('<i class="fa-solid fa-pen-to-square me-2"></i>แก้ไข', ['update', 'id' => $item->id], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-xl']]) ?></li>
                            </ul>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
