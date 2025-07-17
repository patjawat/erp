<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use yii\grid\ActionColumn;
use app\components\AppHelper;
use app\components\SiteHelper;
use app\modules\am\models\Asset;

$this->title = 'ทะเบียนที่ดิน';
$this->params['breadcrumbs'][] = ['label' => 'ทรัพย์สิน', 'url' => ['/am']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-map fs-3"></i> <?=$this->title;?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?=$this->render('../default/menu')?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?=$this->render('../default/menu',['active' => 'asset'])?>
<?php $this->endBlock(); ?>

<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <h6 class="text-white mt-2"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
    </div>
    <div class="card-body">
        <?php echo $this->render('_search', ['model' => $searchModel]); ?>
    </div>
</div>


<div class="d-flex justify-content-between mb-3">
    <h6>
        <i class="bi bi-ui-checks"></i> ทะเบียน<?=$this->title?>
        <span class="badge rounded-pill text-bg-primary"><?=$dataProvider->getTotalCount()?> </span> รายการ
    </h6>
    <?= Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['/am/land/create'], ['class' => 'btn btn-primary rounded-pill shadow']) ?>

</div>

<div class="row g-4">
    <?php foreach($dataProvider->getModels() as $item):?>

    <!-- Deed Card 3 -->
    <div class="col-md-6 col-lg-3">
        <div class="card deed-card">
            <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                <h6 class="text-white">โฉนดเลขที่ <?=$item->data_json['lan_number'] ?? '-'?></h6>
                <button type="button" class="btn btn-success">
                    <?php
                    $landSize = [
                        'rai' => $item->data_json['land_size'] ?? 0,
                        'ngan' => $item->data_json['land_size_ngan'] ?? 0,
                        'tarangwa' => $item->data_json['land_size_tarangwa'] ?? 0
                    ];
                    if ($landSize['rai'] > 0) {
                        echo $landSize['rai'] . ' ไร่ ';
                    }
                    if ($landSize['ngan'] > 0) {
                        echo $landSize['ngan'] . ' งาน ';
                    }
                    if ($landSize['tarangwa'] > 0) {
                        echo $landSize['tarangwa'] . ' ตารางวา';
                    }
                    if ($landSize['tarangwa'] == 0 && $landSize['ngan'] == 0 && $landSize['rai'] == 0) {
                        echo 'ไม่ระบุขนาด';
                    }
                    ?>
                </button>
                <!-- <div>
                            <span class="badge badge-success">3 ไร่ 1 งาน</span>
                        </div> -->
            </div>
            <div class="card-body">
                <div class="deed-info mb-3">
                    <div class="deed-icon">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>
                    <div>
                        <h5 class="card-title mb-1">-</h5>
                        <span class="badge badge-danger">฿ 0</span>
                    </div>
                </div>



                <div class="section-title">
                    <i class="bi bi-map me-2"></i>ข้อมูลที่ดิน
                </div>

                <div class="property-info">
                    <span class="info-label">โฉนดเลขที่:</span>
                    <span class="info-value">12345</span>
                </div>

                <!-- <div class="property-info">
                    <span class="info-label">ที่ตั้ง:</span>
                    <span class="info-value"><?=$item->data_json['land_address'] ?? '-'?></span>
                </div> -->

                <!-- <div class="property-info">
                    <span class="info-label">สถานะ:</span>
                    <span class="badge badge-success">พร้อมโอน</span>
                </div> -->



            </div>
            <div class="card-footer d-flex justify-content-between">
                <?=Html::a('<i class="fa-solid fa-eye"></i> ดูรายละเอียด',['/am/land/view','id' => $item->id],['class' => 'btn btn-outline-primary'])?>
                <?=Html::a('<i class="fa-solid fa-pen-to-square"></i> แก้ไข',['/am/land/update','id' => $item->id],['class' => 'btn btn-primary'])?>
            </div>
        </div>
    </div>
    <?php endforeach;?>
</div>