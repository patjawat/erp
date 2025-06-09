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
    <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <?= Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['/am/land/create'], ['class' => 'btn btn-primary rounded-pill shadow']) ?>
                <?= $this->render('_search', ['model' => $searchModel]); ?>
            </div>
    </div>
</div>



<div class="row g-4">
<?php foreach($dataProvider->getModels() as $item):?>

    <div class="col-md-6 col-lg-3">
                        <div class="card property-card">
                            <div class="position-relative">
                                <div class="property-type-badge land-badge">ที่ดิน</div>
                                <svg class="card-img-top" width="100%" height="180" xmlns="http://www.w3.org/2000/svg">
                                    <rect width="100%" height="100%" fill="#e8f5e9"></rect>
                                    <path d="M0,140 C50,120 100,150 150,130 C200,110 250,140 300,120 L300,180 L0,180 Z" fill="#81c784"></path>
                                    <path d="M50,120 C60,115 70,125 80,120 C90,115 100,125 110,120 L110,140 L50,140 Z" fill="#4caf50"></path>
                                    <path d="M150,130 C160,125 170,135 180,130 C190,125 200,135 210,130 L210,150 L150,150 Z" fill="#4caf50"></path>
                                    <circle cx="70" cy="100" r="5" fill="#33691e"></circle>
                                    <circle cx="90" cy="90" r="8" fill="#33691e"></circle>
                                    <circle cx="180" cy="110" r="7" fill="#33691e"></circle>
                                    <circle cx="210" cy="100" r="6" fill="#33691e"></circle>
                                </svg>
                            </div>
                            <div class="card-header">
                                <h5 class="card-title mb-1">ที่ดินเปล่า ติดถนนใหญ่</h5>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge badge-danger">฿ 8,500,000</span>
                                    <span class="badge badge-success">3 ไร่ 2 งาน</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="section-title">
                                    <i class="bi bi-map me-2"></i>ข้อมูลที่ดิน
                                </div>
                                
                                <div class="property-info">
                                    <span class="info-label">โฉนดเลขที่:</span>
                                    <span class="info-value">67890</span>
                                </div>
                                
                                <div class="property-info">
                                    <span class="info-label">เลขที่ดิน:</span>
                                    <span class="info-value">4567</span>
                                </div>
                                
                                <div class="property-info">
                                    <span class="info-label">หน้าสำรวจ:</span>
                                    <span class="info-value">8901</span>
                                </div>
                                
                                <div class="property-info">
                                    <span class="info-label">ที่ตั้ง:</span>
                                    <span class="info-value">ตำบลบางพระ อำเภอศรีราชา จังหวัดชลบุรี</span>
                                </div>
                                
                                <div class="property-info">
                                    <span class="info-label">หน้ากว้าง:</span>
                                    <span class="info-value">40 เมตร ติดถนนใหญ่</span>
                                </div>
                                
                                <div class="property-features">
                                    <span class="feature-badge"><i class="bi bi-water"></i>ใกล้แหล่งน้ำ</span>
                                    <span class="feature-badge"><i class="bi bi-lightning-charge"></i>มีไฟฟ้า</span>
                                    <span class="feature-badge"><i class="bi bi-signpost-2"></i>ถนนลาดยาง</span>
                                </div>
                                
                                <div class="property-info">
                                    <span class="info-label">สถานะ:</span>
                                    <span class="badge bg-danger">ขาย</span>
                                </div>
                            </div>
                            <div class="card-footer d-flex justify-content-between">
                                 <?=Html::a('<i class="fa-solid fa-eye"></i> ดูรายละเอียด',['/asset/land/view','id' => $item->id],['class' => 'btn btn-outline-primary btn-action'])?>
                        <?=Html::a('<i class="fa-solid fa-pen-to-square"></i> แก้ไข',['/asset/land/update','id' => $item->id],['class' => 'btn btn-primary btn-action'])?>
                               
                            </div>
                        </div>
                    </div>

<!-- Deed Card 3 -->
            <div class="col-md-6 col-lg-3">
                <div class="card deed-card">
                    <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                        <h6 class="text-white">โฉนดเลขที่ 24680</h6>
                        <button type="button" class="btn btn-primary">
3 ไร่ 1 งาน
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
                                <h5 class="card-title mb-1">ที่ดินเขตบางขุนเทียน</h5>
                                <span class="badge badge-danger">฿ 4,800,000</span>
                            </div>
                        </div>
                        
                        <div class="deed-info">
                            <span class="deed-info-label">ที่ตั้ง:</span>
                            <span class="deed-info-value">แขวงท่าข้าม เขตบางขุนเทียน กรุงเทพมหานคร</span>
                        </div>
                        
                        <div class="deed-info">
                            <span class="deed-info-label">เลขที่ดิน:</span>
                            <span class="deed-info-value">9876</span>
                        </div>
                        
                        <div class="deed-info">
                            <span class="deed-info-label">หน้าสำรวจ:</span>
                            <span class="deed-info-value">5432</span>
                        </div>
                        
                        <div class="deed-info">
                            <span class="deed-info-label">สถานะ:</span>
                            <span class="badge bg-danger">ขาย</span>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-between">
                        <?=Html::a('<i class="fa-solid fa-eye"></i> ดูรายละเอียด',['/asset/land/view','id' => $item->id],['class' => 'btn btn-outline-primary'])?>
                        <?=Html::a('<i class="fa-solid fa-pen-to-square"></i> แก้ไข',['/asset/land/update','id' => $item->id],['class' => 'btn btn-primary'])?>
                    </div>
                </div>
            </div>
            <?php endforeach;?>
        </div>

       


        <div class="row g-4">
                    <!-- Property Card 1: Building with Land -->
                    <div class="col-md-6 col-lg-4">
                        <div class="card property-card">
                            <div class="position-relative">
                                <div class="property-type-badge mixed-badge">สิ่งปลูกสร้างและที่ดิน</div>
                                <svg class="card-img-top" width="100%" height="180" xmlns="http://www.w3.org/2000/svg">
                                    <rect width="100%" height="100%" fill="#e8f5e9"></rect>
                                    <path d="M80,140 L80,70 L160,70 L160,140 Z" fill="#81c784"></path>
                                    <path d="M120,70 L120,40 L80,70 Z" fill="#4caf50"></path>
                                    <rect x="95" y="100" width="20" height="40" fill="#fff"></rect>
                                    <rect x="125" y="85" width="15" height="15" fill="#fff"></rect>
                                    <path d="M0,140 L300,140 L300,180 L0,180 Z" fill="#a5d6a7"></path>
                                </svg>
                            </div>
                            <div class="card-header">
                                <h5 class="card-title mb-1">บ้านเดี่ยว 2 ชั้น พร้อมที่ดิน</h5>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge badge-danger">฿ 5,500,000</span>
                                    <span class="badge badge-success">พื้นที่ 80 ตร.ว.</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="section-title">
                                    <i class="bi bi-house-door me-2"></i>ข้อมูลสิ่งปลูกสร้าง
                                </div>
                                
                                <div class="property-info">
                                    <span class="info-label">ประเภท:</span>
                                    <span class="info-value">บ้านเดี่ยว 2 ชั้น</span>
                                </div>
                                
                                <div class="property-info">
                                    <span class="info-label">พื้นที่ใช้สอย:</span>
                                    <span class="info-value">150 ตร.ม.</span>
                                </div>
                                
                                <div class="property-info">
                                    <span class="info-label">อายุอาคาร:</span>
                                    <span class="info-value">3 ปี</span>
                                </div>
                                
                                <div class="property-features">
                                    <span class="feature-badge"><i class="bi bi-door-open"></i>3 ห้องนอน</span>
                                    <span class="feature-badge"><i class="bi bi-droplet"></i>2 ห้องน้ำ</span>
                                    <span class="feature-badge"><i class="bi bi-car-front"></i>ที่จอดรถ 2 คัน</span>
                                </div>
                                
                                <div class="divider"></div>
                                
                                <div class="section-title">
                                    <i class="bi bi-map me-2"></i>ข้อมูลที่ดิน
                                </div>
                                
                                <div class="property-info">
                                    <span class="info-label">โฉนดเลขที่:</span>
                                    <span class="info-value">12345</span>
                                </div>
                                
                                <div class="property-info">
                                    <span class="info-label">ที่ตั้ง:</span>
                                    <span class="info-value">แขวงบางนา เขตบางนา กรุงเทพมหานคร</span>
                                </div>
                                
                                <div class="property-info">
                                    <span class="info-label">สถานะ:</span>
                                    <span class="badge bg-success">พร้อมโอน</span>
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
                    
                    <!-- Property Card 2: Building Only -->
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
                    
                    <!-- Property Card 3: Land Only -->
                    <div class="col-md-6 col-lg-4">
                        <div class="card property-card">
                            <div class="position-relative">
                                <div class="property-type-badge land-badge">ที่ดิน</div>
                                <svg class="card-img-top" width="100%" height="180" xmlns="http://www.w3.org/2000/svg">
                                    <rect width="100%" height="100%" fill="#e8f5e9"></rect>
                                    <path d="M0,140 C50,120 100,150 150,130 C200,110 250,140 300,120 L300,180 L0,180 Z" fill="#81c784"></path>
                                    <path d="M50,120 C60,115 70,125 80,120 C90,115 100,125 110,120 L110,140 L50,140 Z" fill="#4caf50"></path>
                                    <path d="M150,130 C160,125 170,135 180,130 C190,125 200,135 210,130 L210,150 L150,150 Z" fill="#4caf50"></path>
                                    <circle cx="70" cy="100" r="5" fill="#33691e"></circle>
                                    <circle cx="90" cy="90" r="8" fill="#33691e"></circle>
                                    <circle cx="180" cy="110" r="7" fill="#33691e"></circle>
                                    <circle cx="210" cy="100" r="6" fill="#33691e"></circle>
                                </svg>
                            </div>
                            <div class="card-header">
                                <h5 class="card-title mb-1">ที่ดินเปล่า ติดถนนใหญ่</h5>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge badge-danger">฿ 8,500,000</span>
                                    <span class="badge badge-success">3 ไร่ 2 งาน</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="section-title">
                                    <i class="bi bi-map me-2"></i>ข้อมูลที่ดิน
                                </div>
                                
                                <div class="property-info">
                                    <span class="info-label">โฉนดเลขที่:</span>
                                    <span class="info-value">67890</span>
                                </div>
                                
                                <div class="property-info">
                                    <span class="info-label">เลขที่ดิน:</span>
                                    <span class="info-value">4567</span>
                                </div>
                                
                                <div class="property-info">
                                    <span class="info-label">หน้าสำรวจ:</span>
                                    <span class="info-value">8901</span>
                                </div>
                                
                                <div class="property-info">
                                    <span class="info-label">ที่ตั้ง:</span>
                                    <span class="info-value">ตำบลบางพระ อำเภอศรีราชา จังหวัดชลบุรี</span>
                                </div>
                                
                                <div class="property-info">
                                    <span class="info-label">หน้ากว้าง:</span>
                                    <span class="info-value">40 เมตร ติดถนนใหญ่</span>
                                </div>
                                
                                <div class="property-features">
                                    <span class="feature-badge"><i class="bi bi-water"></i>ใกล้แหล่งน้ำ</span>
                                    <span class="feature-badge"><i class="bi bi-lightning-charge"></i>มีไฟฟ้า</span>
                                    <span class="feature-badge"><i class="bi bi-signpost-2"></i>ถนนลาดยาง</span>
                                </div>
                                
                                <div class="property-info">
                                    <span class="info-label">สถานะ:</span>
                                    <span class="badge bg-danger">ขาย</span>
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
                    
                    <!-- Property Card 4: Condo -->
                    <div class="col-md-6 col-lg-4">
                        <div class="card property-card">
                            <div class="position-relative">
                                <div class="property-type-badge building-badge">สิ่งปลูกสร้าง</div>
                                <svg class="card-img-top" width="100%" height="180" xmlns="http://www.w3.org/2000/svg">
                                    <rect width="100%" height="100%" fill="#e3f2fd"></rect>
                                    <rect x="50" y="30" width="200" height="150" fill="#90caf9"></rect>
                                    <rect x="70" y="50" width="30" height="20" fill="#fff"></rect>
                                    <rect x="110" y="50" width="30" height="20" fill="#fff"></rect>
                                    <rect x="150" y="50" width="30" height="20" fill="#fff"></rect>
                                    <rect x="190" y="50" width="30" height="20" fill="#fff"></rect>
                                    <rect x="70" y="80" width="30" height="20" fill="#fff"></rect>
                                    <rect x="110" y="80" width="30" height="20" fill="#fff"></rect>
                                    <rect x="150" y="80" width="30" height="20" fill="#fff"></rect>
                                    <rect x="190" y="80" width="30" height="20" fill="#fff"></rect>
                                    <rect x="70" y="110" width="30" height="20" fill="#fff"></rect>
                                    <rect x="110" y="110" width="30" height="20" fill="#fff"></rect>
                                    <rect x="150" y="110" width="30" height="20" fill="#fff"></rect>
                                    <rect x="190" y="110" width="30" height="20" fill="#fff"></rect>
                                    <rect x="120" y="140" width="60" height="40" fill="#1976d2"></rect>
                                </svg>
                            </div>
                            <div class="card-header">
                                <h5 class="card-title mb-1">คอนโดมิเนียม ชั้น 15</h5>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge badge-danger">฿ 2,800,000</span>
                                    <span class="badge badge-success">35 ตร.ม.</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="section-title">
                                    <i class="bi bi-house-door me-2"></i>ข้อมูลสิ่งปลูกสร้าง
                                </div>
                                
                                <div class="property-info">
                                    <span class="info-label">ประเภท:</span>
                                    <span class="info-value">คอนโดมิเนียม 1 ห้องนอน</span>
                                </div>
                                
                                <div class="property-info">
                                    <span class="info-label">พื้นที่ใช้สอย:</span>
                                    <span class="info-value">35 ตร.ม.</span>
                                </div>
                                
                                <div class="property-info">
                                    <span class="info-label">ชั้น:</span>
                                    <span class="info-value">15</span>
                                </div>
                                
                                <div class="property-info">
                                    <span class="info-label">อายุอาคาร:</span>
                                    <span class="info-value">2 ปี</span>
                                </div>
                                
                                <div class="property-info">
                                    <span class="info-label">ที่ตั้ง:</span>
                                    <span class="info-value">แขวงคลองเตย เขตคลองเตย กรุงเทพมหานคร</span>
                                </div>
                                
                                <div class="property-features">
                                    <span class="feature-badge"><i class="bi bi-door-open"></i>1 ห้องนอน</span>
                                    <span class="feature-badge"><i class="bi bi-droplet"></i>1 ห้องน้ำ</span>
                                    <span class="feature-badge"><i class="bi bi-train-front"></i>ใกล้ BTS</span>
                                </div>
                                
                                <div class="property-info">
                                    <span class="info-label">สถานะ:</span>
                                    <span class="badge bg-success">พร้อมโอน</span>
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
                </div>