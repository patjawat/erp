<?php
use yii\helpers\Url;
use yii\helpers\Html;
?>

    <table class="table">
        <thead>
            <tr>
                <th class="text-center fw-semibold" style="width:30px">ลำดับ</th>
                <th>เรื่อง</th>
                <th>ประเภท</th>
                <th class="fw-semibold" scope="col">เรื่อง/วัน/สถานที่</th>
                <th class="fw-semibold" scope="col">คณะเดินทาง</th>
                <th class="fw-semibold text-center" scope="col">สถานะ</th>
                <th class="fw-semibold text-center">ดำเนินการ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($dataProvider->getModels() as $key => $item): ?>
            <tr>
                <td class="text-center fw-semibold">
                    <?php echo (($dataProvider->pagination->offset + 1) + $key) ?>
                </td>
                <td>
                    <p class="mb-0"><?= $item->topic ?></p>
                <p class="mb-0"> <?= $item->showDateRange() ?></p>    
                
            </td>
                <td>
                    <?=$item->developmentType?->title?>
                    <div>
                       
                        <p class="fw-semibold mb-0 fs-12"> <?=$item->viewResponseStatus()['view'] ?? '';?> </p>
                        สถานที่ <?= $item->data_json['location'] ?? '-' ?>
                    </div>
                </td>

                <td>  
                <?= $item->StackMember() ?></td>
                   <td class="text-center">
                
                   <?=$item->getStatus($item->status)['view'] ?? '-'?></td>
                <td style="width:120px">
                    <div class="btn-group">
                        <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['view', 'id' => $item->id, 'title' => '<i class="fa-solid fa-pen-to-square"></i> แก้ไข'], ['class' => 'btn btn-light w-100 open-modal', 'data' => ['size' => 'modal-xl']]) ?>
                        <button type="button" class="btn btn-light dropdown-toggle dropdown-toggle-split"
                            data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent">
                            <i class="bi bi-caret-down-fill"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><?= Html::a('<i class="fa-solid fa-eye me-1"></i> แสดงรายละเอียด', ['view', 'id' => $item->id], ['class' => 'dropdown-item']) ?></li>
                            <li><?=$item->development_type_id == 'dev3' ? Html::a('<i class="fa-solid fa-user-check me-1"></i> ตอบรับเป็นวิทยากร', ['/me/development/response-dev', 'id' => $item->id,'title' => '<i class="fa-solid fa-user-check"></i> การตอบรับเป็นวิทยากร'], ['class' => 'dropdown-item open-modal','data' => ['size' => 'modal-lg']]) : ''?></li>
                            <li><?= Html::a('<i class="fa-solid fa-print me-1"></i> พิมพ์ใบขอไปราชการ', ['/me/development/form-official', 'id' => $item->id], ['class' => 'dropdown-item open-modal','data' => ['size' => 'modal-xl']]) ?></li>
                            <li><?= Html::a('<i class="fa-solid fa-print me-1"></i> พิมพ์ใบขออนุญาต', ['/me/development/permit-request', 'id' => $item->id], ['class' => 'dropdown-item open-modal','data' => ['size' => 'modal-xl']])?></li>
                            <li><?= Html::a('<i class="fa-solid fa-print me-1"></i> พิมพ์ใบตอบรับเป็นวิทยากร', ['/me/development/form-academic', 'id' => $item->id], ['class' => 'dropdown-item  open-modal','data' => ['size' => 'modal-xl']]) ?></li>
                            <li><?= Html::a('<i class="fa-solid fa-triangle-exclamation me-1"></i> แจ้งยกเลิก', ['view', 'id' => $item->id], ['class' => 'dropdown-item']) ?></li>
                            </ui>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
        <div class="iq-card-footer text-muted d-flex justify-content-center mt-4">
            <?= yii\bootstrap5\LinkPager::widget([
                'pagination' => $dataProvider->pagination,
                'firstPageLabel' => 'หน้าแรก',
                'lastPageLabel' => 'หน้าสุดท้าย',
                'options' => [
                    'listOptions' => 'pagination pagination-sm',
                    'class' => 'pagination-sm',
                ],
            ]); ?>
        </div>