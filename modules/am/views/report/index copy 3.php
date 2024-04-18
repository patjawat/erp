<?php
/** @var yii\web\View $this */
use yii\helpers\Html;
use app\models\Categorise;
use app\modules\am\models\AssetDetail;

$this->title = 'รายงานค่าเสื่อม';
$this->params['breadcrumbs'][] = ['label' => 'บริหารทรัพย์สิน', 'url' => ['/am']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title');?>
<i class="bi bi-folder-check"></i> <?=$this->title;?>
<?php $this->endBlock();?>
<?php $this->beginBlock('sub-title');?>
<?php $this->endBlock();?>
<?php $this->beginBlock('page-action');?>
<?=$this->render('../default/menu')?>
<?php $this->endBlock()?>


<style>
th.rotate {
    /* Something you can count on */
    height: 140px;
    white-space: nowrap;
}

th.rotate>div {
    transform:
        /* Magic Numbers */
        translate(25px, 51px)
        /* 45 is really 360 - 45 */
        rotate(270deg);
    width: 30px;
    margin-bottom: 45px;
    /* margin-right: 22px; */
}

th.rotate>div>span {
    /* border-bottom: 1px solid #ccc; */
    padding: 5px 10px;
}
</style>
<?php yii\widgets\Pjax::begin(['id' => 'am-container', 'enablePushState' => true, 'timeout' => 50000]);?>


<div class="row">
    <div class="col-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <span class="text-muted text-uppercase fs-6">มูลค่าทรัพย์สิน</span>
                        <h2 class="text-muted text-uppercase fs-5"> <?=number_format($totalPrice,2)?> บาท</h2>
                    </div>
                    <div class="text-center" style="position: relative;">
                        <div id="t-rev" style="min-height: 45px;">
                            <i class="bi bi-coin fs-1"></i>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="col-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <span class="text-muted text-uppercase fs-6">ค่าเสื่อมยกมา</span>
                        <h2 class="text-muted text-uppercase fs-5"> 0 บาท</h2>
                    </div>
                    <div class="text-center" style="position: relative;">
                        <div id="t-rev" style="min-height: 45px;">
                            <i class="bi bi-arrow-down-right-circle fs-1"></i>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="col-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <span class="text-muted text-uppercase fs-6">ราคาทุนสุทธิของระยะเวลา</span>
                        <h2 class="text-muted text-uppercase fs-5"> 0 บาท</h2>
                    </div>
                    <div class="text-center" style="position: relative;">
                        <div id="t-rev" style="min-height: 45px;">
                            <i class="bi bi-coin fs-1"></i>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="col-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <span class="text-muted text-uppercase fs-6">ค่าเสื่อมราคาเดือนนี้</span>
                        <h2 class="text-muted text-uppercase fs-5"> 0 บาท</h2>
                    </div>
                    <div class="text-center" style="position: relative;">
                        <div id="t-rev" style="min-height: 45px;">
                            <i class="bi bi-coin fs-1"></i>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>


</div>
</div>
<div class="card">
    <div class="card-body">
        <?= $this->render('_search', ['model' => $searchModel]); ?>
    </div>
</div>

<div class="row">
    <div class="col-12">

        <div class="card rounded-4">

            <div class="card-body">
                <div class="d-flex justify-content-between">

                    <div class="card-title">รายงานค่าเสื่อม</div>

                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th style="width:20px;">รายการทรัพย์สิน</th>
                                <th class="text-center">ราคา</th>
                                <th class="rotate">
                                    <div><span>ราคาค่าเสื่อมสะสมยกมา</span></div>
                                </th>
                                <th class="rotate">
                                    <div><span>ราคาทุนทรัพย์สินของรอบระยะเวลา</span></div>
                                </th>
                                <th class="rotate">
                                    <div><span>อายุการใช้งาน/ปี</span></div>
                                </th>

                                <th class="rotate">
                                    <div><span>อัตรา</span></div>
                                </th>
                                <th class="rotate">
                                    <div><span>จำนวนวัน</span></div>
                                </th>
                                <th class="rotate">
                                    <div><span>เดือนที่</span></div>
                                </th>
                                <th class="rotate">
                                    <div><span>จำนวนเดือน</span></div>
                                </th>
                                <th class="rotate">
                                    <div><span>จำนวนเดือน</span></div>
                                </th>
                                <th class="rotate">
                                    <div><span>ค่าเสื่อมราคาเดือนนี้</span></div>
                                </th>
                                <th class="rotate">
                                    <div><span>ค่าเสื่อมราคาสะสมยกไป</span></div>
                                </th>
                                <th class="rotate">
                                    <div><span>ราคาทุนสุทธิทรัพย์สินของรอบระยะเวลา
                                        </span></div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="table-group-divider">
                            <?php foreach($querys as $model):?>

                            <tr>
                                <td>
                                    <?=Html::a('<i class="bi bi-check2-square fs-5 me-1"></i><span class="fw-bolder">'.$model['title'].'</span>',['/am/asset/depreciation','id'=> $model['id']],['class' => 'text-truncate open-modal text-primary','data' => ['size' => 'modal-lg']])?>
                                    <br>
                                    <i class="bi bi-check2-square fs-5 me-1"></i> <code>
                                        <?=$model['code']?>
                                    </code> | <i class="bi bi-calendar-plus"></i>
                                    <?=Yii::$app->thaiFormatter->asDate($model['date'], 'medium') ?>
                                </td>

                                <td class="text-end align-middle"><?=number_format($model['price'],2)?> </td>
                                <td></td>
                                <td>1.00</td>
                                <td class="text-center align-middle"><?=$model['life']?></td>
                                <td class="text-center align-middle"><?=$model['depreciation']?></td>
                                <td class="text-center align-middle"><?=$model['day_number']?></td>
                                <td
                                    class="text-center align-middle <?=$model['date_number'] < 1 ? 'bg-danger text-white bg-opacity-50' : ''?>">
                                    <?=$model['date_number']?></td>
                                <td class="text-end align-middle"><?=number_format($model['price_days'],2)?></td>
                                <td class="text-end align-middle"><?=number_format($model['price_month'],2)?></td>
                                <td></td>
                                <td></td>
                                <td class="text-end align-middle fw-semibold"><?=number_format($model['total_price'],2)?></td>
                            </tr>

                            <?php endforeach;?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>


    </div>
</div>

<?php yii\widgets\Pjax::end();?>