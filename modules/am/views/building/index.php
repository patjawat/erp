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
<i class="bi bi-folder-check fs-1"></i> <?=$this->title;?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?=$this->render('../default/menu')?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?=$this->render('../default/menu',['active' => 'asset'])?>
<?php $this->endBlock(); ?>

<style>
     .title-section {
            background: linear-gradient(135deg, #2e7d32 0%, #388e3c 100%);
            color: white;
            padding: 2rem 0;
            border-radius: 0 0 20px 20px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .property-card {
            border-radius: 12px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            height: 100%;
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        
        .property-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.12);
        }
        
        .card-img-top {
            height: 180px;
            object-fit: cover;
        }
        
        .property-type-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 0.8rem;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-weight: 500;
        }
        
        .building-badge {
            background-color: #ff9800;
            color: white;
        }
        
        .land-badge {
            background-color: #4caf50;
            color: white;
        }
        
        .mixed-badge {
            background: linear-gradient(90deg, #ff9800 50%, #4caf50 50%);
            color: white;
        }
        
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding: 1rem 1.25rem;
        }
        
        .property-icon {
            background-color: #e8f5e9;
            color: #2e7d32;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-size: 1.5rem;
            margin-right: 1rem;
        }
        
        .property-info {
            display: flex;
            align-items: center;
            margin-bottom: 0.75rem;
        }
        
        .info-label {
            color: #6c757d;
            font-size: 0.9rem;
            width: 120px;
            flex-shrink: 0;
        }
        
        .info-value {
            font-weight: 500;
            color: #212529;
        }
        
        /* .badge-success, .badge-danger {
            font-weight: normal;
            font-size: 0.85rem;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
        }
        
        .badge-success {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        
        .badge-danger {
            background-color: #fff3e0;
            color: #e65100;
        } */
        
        .card-footer {
            background-color: white;
            border-top: 1px solid rgba(0,0,0,0.05);
            padding: 1rem 1.25rem;
        }
        
        .btn-action {
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }
        
        .search-box {
            border-radius: 50px;
            padding: 0.75rem 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .filter-section {
            background-color: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .tab-section {
            margin-bottom: 2rem;
        }
        
        .nav-tabs .nav-link {
            border: none;
            color: #6c757d;
            font-weight: 500;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            margin-right: 0.5rem;
        }
        
        .nav-tabs .nav-link.active {
            background-color: #2e7d32;
            color: white;
        }
        
        .property-features {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        
        .feature-badge {
            background-color: #f1f8e9;
            color: #558b2f;
            font-size: 0.8rem;
            padding: 0.3rem 0.7rem;
            border-radius: 20px;
            display: flex;
            align-items: center;
        }
        
        .feature-badge i {
            margin-right: 0.3rem;
            font-size: 0.9rem;
        }
        
        .divider {
            height: 1px;
            background-color: rgba(0,0,0,0.05);
            margin: 1rem 0;
        }
        
        .section-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: #2e7d32;
        }
</style>


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
                                <button class="btn btn-outline-success btn-action">
                                    <i class="bi bi-eye me-1"></i> ดูรายละเอียด
                                </button>
                                <button class="btn btn-success btn-action">
                                    <i class="bi bi-pencil-square me-1"></i> แก้ไข
                                </button>
                            </div>
                        </div>
                    </div>
            <?php endforeach;?>
        </div>

       