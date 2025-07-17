<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use yii\grid\ActionColumn;
use app\components\AppHelper;
use app\components\SiteHelper;
use app\modules\am\models\Asset;

$this->title = 'ทะเบียนสิ่งปลูกสร้าง';
$this->params['breadcrumbs'][] = ['label' => 'ทรัพย์สิน', 'url' => ['/am']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-folder-check fs-1"></i> <?=$this->title;?>
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

   

        <div class="col-md-6 col-lg-4">
                        <div class="card property-card">
                            <div class="position-relative">
                                <div class="property-type-badge building-badge">สิ่งปลูกสร้าง</div>
                                <svg class="card-img-top" width="100%" height="180" xmlns="http://www.w3.org/2000/svg">
                                    <rect width="100%" height="100%" fill="#fff3e0"></rect>
                                    <rect x="40" y="50" width="220" height="90" fill="#ffb74d"></rect>
                                    <rect x="60" y="80" width="40" height="60" fill="#fff"></rect>
                                    <rect x="130" y="80" width="40" height="30" fill="#fff"></rect>
                                    <rect x="200" y="80" width="40" height="30" fill="#fff"></rect>
                                    <path d="M40,50 L150,20 L260,50 Z" fill="#ff9800"></path>
                                </svg>
                            </div>
                            <div class="card-header">
                                <h5 class="card-title mb-1">อาคารพาณิชย์ 3 ชั้น</h5>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge badge-danger">฿ 3,200,000</span>
                                    <span class="badge badge-success">พื้นที่ 120 ตร.ม.</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="section-title">
                                    <i class="bi bi-house-door me-2"></i>ข้อมูลสิ่งปลูกสร้าง
                                </div>
                                
                                <div class="property-info">
                                    <span class="info-label">ประเภท:</span>
                                    <span class="info-value">อาคารพาณิชย์ 3 ชั้น</span>
                                </div>
                                
                                <div class="property-info">
                                    <span class="info-label">พื้นที่ใช้สอย:</span>
                                    <span class="info-value">120 ตร.ม.</span>
                                </div>
                                
                                <div class="property-info">
                                    <span class="info-label">อายุอาคาร:</span>
                                    <span class="info-value">5 ปี</span>
                                </div>
                                
                                <div class="property-info">
                                    <span class="info-label">ที่ตั้ง:</span>
                                    <span class="info-value">แขวงลาดพร้าว เขตลาดพร้าว กรุงเทพมหานคร</span>
                                </div>
                                
                                <div class="property-features">
                                    <span class="feature-badge"><i class="bi bi-door-open"></i>1 ห้องนอน</span>
                                    <span class="feature-badge"><i class="bi bi-droplet"></i>2 ห้องน้ำ</span>
                                    <span class="feature-badge"><i class="bi bi-shop"></i>พื้นที่ค้าขาย</span>
                                </div>
                                
                                <div class="property-info">
                                    <span class="info-label">สถานะ:</span>
                                    <span class="badge bg-warning text-dark">รอปรับปรุง</span>
                                </div>
                            </div>
                                <div class="card-footer d-flex justify-content-between">
            <?=Html::a('<i class="fa-solid fa-eye"></i> ดูรายละเอียด',['/am/land/view','id' => $item->id],['class' => 'btn btn-outline-primary'])?>
            <?=Html::a('<i class="fa-solid fa-pen-to-square"></i> แก้ไข',['/am/land/update','id' => $item->id],['class' => 'btn btn-primary'])?>
        </div>
                        </div>
                    </div>
    

<?php endforeach;?>
</div>
