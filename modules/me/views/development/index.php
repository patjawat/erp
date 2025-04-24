<?php
use yii\helpers\Html;
/** @var yii\web\View $this */
$this->title = 'อบรม/ประชุม/ดูงาน';
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-briefcase fs-1 text-white"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
ทะเบียน<?=$this->title?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?=$this->render('menu')?>
<?php $this->endBlock(); ?>


<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between">
        <h6><i class="bi bi-ui-checks"></i> <?=$this->title?></h6>
        <?=Html::a('<i class="fa-solid fa-circle-plus"></i> เพิ่ม'.$this->title,['/me/development/create','title' => '<i class="bi bi-mortarboard-fill me-2"></i>แบบฟอร์มบันทึกข้อมูลการพัฒนาบุคลากร'],['class' => 'btn btn-primary rounded-pill shadow open-modal','data' => ['size' => 'modal-xl']])?>
    </div>
           <?=$this->render('_search', ['model' => $searchModel,'type' => 'development'])?>

        <div class="table-responsive pb-5">
            <table class="table table-primary">
                <thead>
                    <tr>
                        <th class="text-center fw-semibold" style="width:30px">ลำดับ</th>
                        <th class="text-center fw-semibold" style="width:30px">ปีงบประมาณ</th>

                        <th scope="col">ประเภท/เรื่อง</th>
                        <th scope="col">วัน/สถานที่</th>
                        <th scope="col">คณะเดินทาง</th>
                        <th class="fw-semibold text-center">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                    <tr>
                        <td class="text-center fw-semibold">
                            <?php echo (($dataProvider->pagination->offset + 1) + $key) ?>
                        </td>
                        <td><?=$item->thai_year;?></td>
                        <td>
                            <div>
                                <p class="text-muted mb-0 fs-13"><?=$item->data_json['development_type_name'] ?? '-'?>
                                </p>
                                <p class="fw-semibold mb-0"><?=$item->topic?></p>
                            </div>

                        </td>
                        <td>
                            <div>
                                <p class="text-muted mb-0 fs-13"><?=$item->showDateRange()?></p>
                                <p class="fw-semibold mb-0"><?=$item->data_json['location'] ?? '-'?></p>
                            </div>
                        </td>
                        <td>-</td>
                        <td style="width:120px">
                            <div class="btn-group">
                                <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['update','id' => $item->id,'title' => '<i class="fa-solid fa-pen-to-square"></i> แก้ไข'], ['class' => 'btn btn-light w-100 open-modal','data' => ['size' => 'modal-xl']]) ?>
                                <button type="button" class="btn btn-light dropdown-toggle dropdown-toggle-split"
                                    data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent">
                                    <i class="bi bi-caret-down-fill"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><?= Html::a('<i class="fa-solid fa-eye me-1"></i> แสดงรายละเอียด', ['view','id' => $item->id], ['class' => 'dropdown-item']) ?></li>
                                    </ui>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach;?>
                </tbody>
            </table>
        </div>

    </div>
</div>