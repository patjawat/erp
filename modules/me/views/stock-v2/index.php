<?php

use yii\helpers\Html;
?>

<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <h6 class="text-white mt-2"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
    </div>
    <div class="card-body">
        <?php echo $this->render('_search', ['model' => $searchModel]); ?>
    </div>
</div>

<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <div class="d-flex justify-content-between">
            <h6 class="text-white"><i class="bi bi-ui-checks"></i> ทะเบียนเบิกวัสดุ <span class="badge rounded-pill text-bg-primary"><?= $dataProvider->getTotalCount() ?> </span>รายการ</h6>
            <?= Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['create'], ['class' => 'btn btn-light shadow', 'data' => ['size' => 'modal-xl']]) ?>
        </div>
    </div>

    <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>ชื่อรายการ</th>
                        <th style="width:350px">สถานะ</th>

                        <th class="text-center" style="width:100px">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dataProvider->getModels() as $item): ?>
                        <tr class="">
                            <td>
                                <?php echo $item->CreateBy()['avatar'] ?>

                            </td>
                            <td>

                            </td>

                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                        id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                        จัดการ
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                        <li><?= Html::a('<i class="fa-solid fa-pencil me-2"></i>แก้ไข', ['update', 'id' => $item->id], ['class' => 'dropdown-item', 'data' => ['size' => 'modal-xl']]) ?></li>
                                        <li><?= Html::a('<i class="fa-solid fa-eye me-2"></i>แสดง', ['view', 'id' => $item->id], ['class' => 'dropdown-item', 'data' => ['size' => 'modal-xl']]) ?></li>

                                    </ul>
                                </div>
                            </td>
                        <?php endforeach; ?>
                </tbody>
            </table>
    </div>
</div>