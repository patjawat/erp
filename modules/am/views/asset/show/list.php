<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use yii\grid\ActionColumn;
use app\components\AppHelper;
use app\modules\am\models\Asset;

?>
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="fw-semibold" scope="col" style="text-align: center;">ลำดับ</th>
                        <th class="fw-semibold" scope="col" style="width:70px;">รูปภาพ</th>
                        <th class="fw-semibold" scope="col" style="width:200px;">หมายเลขคุภัณฑ์</th>
                        <th class="fw-semibold" scope="col">รายการทรัพย์สิน</th>
                        <th class="fw-semibold" scope="col" style="width: 350px;">ยี่ห้อ</th>
                        <th class="fw-semibold" scope="col">ราคา</th>
                        <th class="fw-semibold" scope="col">วันที่รับเข้า</th>
                        <th class="fw-semibold" scope="col">สถานะ</th>
                        <th class="fw-semibold text-center" scope="col" style="width: 100px;">จัดการ</th>
                    </tr>
                </thead>
              <tbody class="table-group-divider align-middle">
                    <?php foreach($dataProvider->getModels() as $key => $item):?>
                    <tr>
                        <td class="text-center fw-semibold"><?php echo (($dataProvider->pagination->offset + 1)+$key)?></td>
                     <td>  <?= Html::a(Html::img($item->showImg()['image'],['class' => 'avatar avatar-sm bg-primary text-white lazyautosizes ls-is-cached lazyloaded']), ['view','id' => $item->id],['class' => '', ]) ?></td>
                     <td class="fw-semibold text-primary"><?=$item->code?></td>
                        <td class="align-middle">
                           <?=$item->AssetitemName()?>
                            <?php // $this->render('item_list',['model' => $item])?>
                        </td>
                        <td class="align-middle"></td>
                        <td class="align-middle"><?=number_format($item->price,0)?></td>
                        <td class="align-middle"><?=$item->receive_date?></td>
                        <td><?=$item->statusName()?></td>

                        <td>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                    id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                    จัดการ
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                    <li><?= Html::a('<i class="fa-solid fa-eye me-1"></i>แสดง', ['view', 'id' => $item->id], ['class' => 'dropdown-item']) ?></li>
                                    <li><?= Html::a('<i class="fa-regular fa-pen-to-square me-1"></i> แก้ไข', ['update', 'id' => $item->id], ['class' => 'dropdown-item']) ?></li>
                                </ul>
                            </div>
                        </td>


                        <!-- <td class="align-middle text-center">
                            <div class="d-flex gap-3">
                                <?=Html::a('<i class="fa-solid fa-eye fa-2x"></i>',['view','id' => $item->id])?>
                                <?=Html::a('<i class="fa-solid fa-pen-to-square fa fa-2x text-warning"></i>',['update','id' => $item->id])?>
                                <?= Html::a('<i class="fa-solid fa-trash fa-2x text-danger"></i>', ['delete', 'id' => $item->id], ['class' => 'delete-asset']) ?>
                            </div>
                        </td> -->
                    </tr>
                    <?php endforeach; ?>

                </tbody>
            </table>
        </div>
    </div>
</div>