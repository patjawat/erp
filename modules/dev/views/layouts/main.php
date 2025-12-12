<?php

/** @var yii\web\View $this */
/** @var string $content */

use yii\web\View;
use app\assets\AppAsset;
use yii\bootstrap5\Html;
use app\models\Categorise;
use app\assets\BootstapIconAsset;

AppAsset::register($this);
BootstapIconAsset::register($this);

// Config Theme
$site = Categorise::findOne(['name' => 'site']);
$colorName = isset($site->data_json['theme_color_name']) ? $site->data_json['theme_color_name'] : 'blue';

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">

<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            /* สีน้ำเงินเข้ม */
            --erp-primary: #1a508e; 
            --erp-primary-light: #eff6ff;
            --erp-icon-bg-active: #dbeafe;
            --erp-bg: #f3f7fa;
        }
        
        * { font-family: 'Sarabun', sans-serif !important; }
        body { background-color: var(--erp-bg); }

        /* --- HEADER STYLES --- */
        .header-fixed {
            position: sticky; top: 0; z-index: 1050; 
            height: 64px; 
            background-color: var(--erp-primary); color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        /* ปุ่มไอคอนบน Header */
        .header-btn {
            color: rgba(255,255,255,0.85) !important;
            transition: all 0.2s; cursor: pointer;
            padding: 8px; border-radius: 50%;
            background: transparent; border: none;
            display: flex; align-items: center; justify-content: center;
        }
        .header-btn:hover { 
            color: white !important; 
            background-color: rgba(255,255,255,0.1);
        }
        
        /* เส้นคั่นแนวตั้ง */
        .header-divider {
            width: 1px; height: 32px; 
            background-color: rgba(255,255,255,0.2);
            margin: 0 16px;
        }
        
        /* ส่วนโปรไฟล์ */
        .header-profile { cursor: pointer; transition: opacity 0.2s; }
        .header-profile:hover { opacity: 0.9; }

        /* --- NAVBAR STYLES --- */
        .navbar-fixed-container {
            position: sticky; top: 64px; z-index: 1040; height: 86px;
            background-color: white; border-bottom: 1px solid #dee2e6;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            width: 100%; overflow-x: auto; overflow-y: hidden;
        }
        .navbar-fixed-container::-webkit-scrollbar { height: 6px; }
        .navbar-fixed-container::-webkit-scrollbar-track { background: #f8fafc; }
        .navbar-fixed-container::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 3px; }

        /* --- MENU LIST --- */
        .erp-nav-list {
            display: flex !important; flex-direction: row !important; flex-wrap: nowrap !important;
            align-items: center; height: 100%; margin: 0; padding: 0 4px; list-style: none;
        }

        /* --- MENU ITEM --- */
        .erp-nav-item {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            min-width: 120px; height: 100%; padding: 8px 4px;
            text-decoration: none; color: #64748b; border-bottom: 3px solid transparent;
            transition: all 0.2s ease; flex-shrink: 0;
        }
        .erp-nav-item:hover { background-color: #f8fafc; color: #334155; }
        .erp-nav-item.active {
            background-color: var(--erp-primary-light);
            border-bottom-color: var(--erp-primary);
            color: var(--erp-primary);
        }

        .erp-icon-box {
            width: 36px; height: 36px; border-radius: 50%; 
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 4px; background-color: #f1f5f9; color: #64748b;
        }
        
        /* บังคับขนาด SVG ใน Navbar */
        .erp-icon-box svg {
            width: 20px; height: 20px; stroke-width: 2px;
        }

        .erp-nav-item.active .erp-icon-box { 
            background-color: var(--erp-icon-bg-active); color: var(--erp-primary); 
        }
        
        .erp-nav-text { font-size: 12px; font-weight: 500; white-space: nowrap; }
        .erp-nav-item.active .erp-nav-text { font-weight: 600; }

        /* --- BREADCRUMB --- */
        .breadcrumb-item + .breadcrumb-item::before { content: ">"; font-size: 10px; color: #9ca3af; }
        .breadcrumb a { text-decoration: none; color: #64748b; }
        .breadcrumb-item.active { color: #1e293b; font-weight: 600; }
    </style>
    <?php $this->head() ?>
</head>

<body class="d-flex flex-column min-vh-100">
    <?php $this->beginBody() ?>

    <header class="header-fixed d-flex align-items-center justify-content-between px-4">
        
        <div class="d-flex align-items-center gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-white">
                <path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"></path>
                <path d="M12 22V12"></path>
                <polyline points="3.29 7 12 12 20.71 7"></polyline>
                <path d="m7.5 4.27 9 5.15"></path>
            </svg>
            
            <div style="line-height: 1;">
                <div class="fw-bold text-white" style="font-size: 1.35rem; letter-spacing: 0.5px;">HOSPITAL</div>
                <div class="text-white-50" style="font-size: 11px; font-weight: 500; letter-spacing: 1px;">ERP SYSTEM</div>
            </div>
        </div>

        <div class="d-flex align-items-center">
            
            <div class="d-flex align-items-center gap-1">
                <button type="button" class="header-btn" title="Switch to Dark Mode">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.985 12.486a9 9 0 1 1-9.473-9.472c.405-.022.617.46.402.803a6 6 0 0 0 8.268 8.268c.344-.215.825-.004.803.401"></path></svg>
                </button>
                
                <button type="button" class="header-btn d-lg-none">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5h16"></path><path d="M4 12h16"></path><path d="M4 19h16"></path></svg>
                </button>
                
                <button type="button" class="header-btn d-none d-lg-flex">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 3H5a2 2 0 0 0-2 2v3"></path><path d="M21 8V5a2 2 0 0 0-2-2h-3"></path><path d="M3 16v3a2 2 0 0 0 2 2h3"></path><path d="M16 21h3a2 2 0 0 0 2-2v-3"></path></svg>
                </button>
                
                <button type="button" class="header-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.671 4.136a2.34 2.34 0 0 1 4.659 0 2.34 2.34 0 0 0 3.319 1.915 2.34 2.34 0 0 1 2.33 4.033 2.34 2.34 0 0 0 0 3.831 2.34 2.34 0 0 1-2.33 4.033 2.34 2.34 0 0 0-3.319 1.915 2.34 2.34 0 0 1-4.659 0 2.34 2.34 0 0 0-3.32-1.915 2.34 2.34 0 0 1-2.33-4.033 2.34 2.34 0 0 0 0-3.831A2.34 2.34 0 0 1 6.35 6.051a2.34 2.34 0 0 0 3.319-1.915"></path><circle cx="12" cy="12" r="3"></circle></svg>
                </button>
            </div>

            <div class="header-divider"></div>

            <div class="d-flex align-items-center gap-3 header-profile">
                <div class="rounded-circle border border-2 border-white border-opacity-50 d-flex align-items-center justify-content-center overflow-hidden" style="width: 38px; height: 38px; background-color: rgba(255,255,255,0.1);">
                     <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                </div>
                <div class="d-none d-md-block text-white fw-medium">
                    Administrator Lastname
                </div>
            </div>
            
        </div>
    </header>

    <div class="navbar-fixed-container">
        <div class="erp-nav-list">
            <?= $this->render('navbar') ?>
        </div>
    </div>

    <div class="d-flex flex-column flex-grow-1">
        
        <div class="bg-white border-bottom py-3 px-0">
            <div class="container-fluid px-4" style="max-width: 1600px;">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 small">
                        <li class="breadcrumb-item"><a href="#">หน้าหลัก</a></li>
                        <?php if(isset($this->context->module->id) && $this->context->module->id == 'dev'): ?>
                            <li class="breadcrumb-item"><a href="#">ระบบงานสารบรรณ</a></li>
                        <?php endif; ?>
                        <li class="breadcrumb-item active" aria-current="page"><?= Html::encode($this->title) ?></li>
                    </ol>
                </nav>
                <h2 class="h4 fw-bold text-dark m-0" style="color: #1e293b;"><?= Html::encode($this->title) ?></h2>
            </div>
        </div>

        <main class="py-4 px-0">
            <div class="container-fluid px-4" style="max-width: 1600px;">
                <?= $content ?>
            </div>
        </main>
    </div>

    <footer class="mt-auto bg-white border-top py-4 px-4 position-relative">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center text-secondary small">
            
            <div class="mb-3 mb-md-0 text-center text-md-start">
                <p class="mb-0 fw-semibold text-dark">2025 © ERP Hospital.</p>
                <p class="mb-0 text-muted" style="font-size: 11px;">v1.4.1</p>
            </div>

            <div class="d-flex align-items-center gap-2">
                <span>ผู้ให้การสนับสนุน</span>
                <a href="#" class="text-primary text-decoration-none fw-medium hover-underline">มูลนิธิรามาธิบดี</a>
            </div>
        </div>

        <div class="position-fixed bottom-0 end-0 p-4 d-flex flex-column gap-2 z-3">
            
            <button class="btn btn-secondary rounded-circle d-flex align-items-center justify-content-center shadow border-0 p-0 header-scroll-btn" style="width: 40px; height: 40px; background-color: #6b7280;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white">
                    <path d="m5 12 7-7 7 7"></path><path d="M12 19V5"></path>
                </svg>
            </button>

            <button class="btn btn-secondary rounded-circle d-flex align-items-center justify-content-center shadow border-0 p-0 header-scroll-btn" style="width: 40px; height: 40px; background-color: #6b7280;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white">
                    <path d="M12 5v14"></path><path d="m19 12-7 7-7-7"></path>
                </svg>
            </button>
            
        </div>
    </footer>

    <style>
        .header-scroll-btn:hover {
            background-color: #4b5563 !important; /* สีเทาเข้มขึ้นตอน Hover */
            transform: translateY(-2px); /* ขยับขึ้นเล็กน้อย */
            transition: all 0.2s;
        }
        .hover-underline:hover {
            text-decoration: underline !important;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>