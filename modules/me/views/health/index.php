
<?php
use yii\helpers\Html;
use app\components\AppHelper;
$this->title = 'บันทึกรายการ LAB และค่าใช้จ่าย';
$this->params['breadcrumbs'][] = ['label' => 'ข้อมูลสุขภาพ', 'url' => ['/health']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="card border-0" style="min-height:500px">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h5 class="card-title">ข้อมูลประวัติการตรวจสุขภาพ</h5>
            <div class="d-flex gap-2">
                <?= Html::a('<i class="fa-solid fa-circle-plus"></i> ทำแบบคัดกรองใหม่', ['/me/health/create','name' => 'health', 'title' => 'แบบคัดกรองสุขภาพ'], ['class' => 'btn btn-outline-primary open-modal', 'data' => ['size' => 'modal-xl', 'pjax' => '0']]) ?>
                <div class="dropdown">
                    <button class="btn btn-outline-primary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-settings-icon lucide-settings">
                            <path d="M9.671 4.136a2.34 2.34 0 0 1 4.659 0 2.34 2.34 0 0 0 3.319 1.915 2.34 2.34 0 0 1 2.33 4.033 2.34 2.34 0 0 0 0 3.831 2.34 2.34 0 0 1-2.33 4.033 2.34 2.34 0 0 0-3.319 1.915 2.34 2.34 0 0 1-4.659 0 2.34 2.34 0 0 0-3.32-1.915 2.34 2.34 0 0 1-2.33-4.033 2.34 2.34 0 0 0 0-3.831A2.34 2.34 0 0 1 6.35 6.051a2.34 2.34 0 0 0 3.319-1.915" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                        <span class="d-none d-sm-inline">ตั้งค่า</span>
                    </button>

                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                        <li>
                            <?= Html::a('<i class="fa-solid fa-angle-right me-1"></i> โรคประจำตัว', ['/hr/chronic-diseases', 'title' => 'การตั้งค่าโรคประจำตัว'], ['class' => 'open-modal dropdown-item', 'data' => ['size' => 'modal-lg']]) ?>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="table-responsive" style="min-height:500px">
            <table class="table table-striped">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr class="text-secondary text-uppercase">
                            <th>ปี</th>
                            <th>วันที่คัดกรอง</th>
                            <th>BMI</th>
                            <th class="text-center">สรุปผลสุขภาพ</th>
                            <th class="text-center">สถานะ</th>
                            <th class="text-center align-middle" style="width: 265px;">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                            <tr>
                                <td class="fw-bold text-dark"><?= $item->thai_year ?></td>
                                <td class="px-4 py-4">
                                    <?=AppHelper::convertToThai($item->date_checkup) ??  '';?>
                                </td>
                                <td class="text-secondary">
                                     <?php $color = $item->getBmiResult()['color'];?>
                                    <span class="badge bg-<?= $color ?> bg-opacity-10 text-<?= $color ?> border border-<?= $color ?>-subtle rounded-pill fw-medium px-2 py-1" style="font-size: 10px;"><?= $item->getBmiResult()['label'] ?></span>
                                </td>
                                <td class="text-center">
                                    <?php
                                    $sumKey   = $item->data_json['final_summary'] ?? 'healthy';
                                    $sumLabel = $item::getFinalSummaryDisplay($sumKey, 'label');
                                    $sumColor = $item::getFinalSummaryDisplay($sumKey, 'color');
                                    $sumIcon  = $item::getFinalSummaryDisplay($sumKey, 'icon');
                                    ?>
                                    <span class="badge rounded-pill bg-<?= $sumColor ?>-subtle text-<?= $sumColor ?> border border-<?= $sumColor ?>-subtle px-3 py-2">
                                        <i class="<?= $sumIcon ?> me-1"></i> <?= $sumLabel ?>
                                    </span>
                                </td>

                                <td class="text-muted small">
                                    <?= $item->viewStatus()?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group shadow-sm rounded-pill p-1 bg-white border">
                                        <?= Html::a(
                                            '<i class="fas fa-eye"></i> ดูผลตรวจ',
                                            ['/me/health/view', 'id' => $item->id],
                                            ['class' => 'btn btn-sm btn-outline-info border-0 rounded-pill px-3 open-modal', 'title' => 'ดูผลตรวจ','data' => ['size' => 'modal-xl']]
                                        )
                                        ?>

                                        <?= Html::a(
                                            '<i class="fa-regular fa-pen-to-square me-1"></i>แก้ไข',
                                            ['update', 'id' => $item->id],
                                            [
                                            'class' => 'btn btn-sm btn-outline-warning border-0 rounded-pill px-3 open-modal',
                                            'title' => 'แก้ไข',
                                            'data' => ['size' => 'modal-xl', 'pjax' => '0']
                                            ]
                                        )
                                        ?>
                                        <?= Html::a(
                                            '<i class="fa-solid fa-trash me-1"></i>ลบ',
                                            ['delete', 'id' => $item->id],
                                            ['class' => 'btn btn-sm btn-outline-danger border-0 rounded-pill px-2 delete-item']
                                        )
                                        ?>
                                    </div>
                                </td>
                                <!-- <td class="text-center align-middle">
                                    <div class="dropdown">
                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                            data-bs-toggle="dropdown" aria-expanded="false"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                                        <div class="dropdown-menu">
                                            <? ?>
                                            <?= Html::a('<i class="fa-regular fa-pen-to-square me-1"></i>แก้ไข', ['/me/health/update', 'id' => $item->id, 'title' => '<i class="fa-solid fa-user-tag"></i> การศึกษา'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-xl', 'pjax' => '0']]) ?>

                                            <?= Html::a('<i class="fa-solid fa-trash me-1"></i>ลบ', ['/hr/employee-detail/delete', 'id' => $item->id], [
                                                'class' => 'dropdown-item delete-item',
                                            ]) ?>
                                        </div>
                                    </div>
                                </td> -->
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
        </div>


    </div>
</div>