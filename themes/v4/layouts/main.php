<?php

use yii\web\View;
use app\assets\AppAsset;
use yii\bootstrap5\Html;
use app\models\Categorise;
use app\assets\BootstapIconAsset;

BootstapIconAsset::register($this);

$site = Categorise::findOne(['name' => 'site']);
$colorName = isset($site->data_json['theme_color_name']) ? $site->data_json['theme_color_name'] : 'blue';
$moduleId = Yii::$app->controller->module->id;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?php echo Yii::$app->language; ?>" class="h-100" data-bs-theme="<?php echo $colorName; ?>" class="dark-mode">

<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php $this->registerCsrfMetaTags() ?>

    <title><?= Html::encode($this->title) ?></title>

    <!-- PWA -->
    <link rel="manifest" href="<?= Yii::getAlias('@web') ?>/manifest.json">
    <meta name="theme-color" content="#1a508e">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <link rel="apple-touch-icon" href="<?= Yii::getAlias('@web') ?>/images/logo_new.png">

    <?php $this->head() ?>
</head>
<style>
    :root {
        --erp-primary: #1a508e;
        --erp-primary-light: #eff6ff;
        --erp-icon-bg-active: #dbeafe;
        --erp-bg: #f3f7fa;
    }

    /* body {
        background-color: var(--erp-bg);
    } */

    .header-fixed {
        position: sticky;
        top: 0;
        z-index: 1050;
        height: 64px;
        background-color: var(--bs-primary);
        /* background: linear-gradient(118deg, var(--bs-primary) 0%, #6a7eaf 100%); */
        color: white;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .header-btn {
        color: rgba(255, 255, 255, 0.85) !important;
        transition: all 0.2s;
        cursor: pointer;
        padding: 8px;
        border-radius: 50%;
        background: transparent;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .header-btn:hover {
        color: white !important;
        background-color: rgba(255, 255, 255, 0.1);
    }

    .header-divider {
        width: 1px;
        height: 32px;
        background-color: rgba(255, 255, 255, 0.2);
        margin: 0 16px;
    }

    .navbar-fixed-container {
        position: sticky;
        top: 64px;
        z-index: 1040;
        height: 86px;
        background-color: var(--bs-body-bg, #fff);
        border-bottom: 1px solid var(--bs-border-color, #dee2e6);
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        width: 100%;
        overflow-x: auto;
        overflow-y: hidden;
    }

    .navbar-fixed-container::-webkit-scrollbar {
        height: 6px;
    }

    .navbar-fixed-container::-webkit-scrollbar-track {
        background: #f8fafc;
    }

    .navbar-fixed-container::-webkit-scrollbar-thumb {
        background-color: #cbd5e1;
        border-radius: 3px;
    }

    .erp-nav-list {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: nowrap !important;
        align-items: center;
        height: 100%;
        margin: 0;
        padding: 0 4px;
        list-style: none;
    }

    .erp-nav-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-width: 120px;
        height: 100%;
        padding: 8px 4px;
        text-decoration: none;
        color: #64748b;
        border-bottom: 3px solid transparent;
        transition: all 0.2s ease;
        flex-shrink: 0;
    }

    .erp-nav-item:hover {
        background-color: #f8fafc;
        color: #334155;
    }

    .erp-nav-item.active {
        background-color: var(--erp-primary-light);
        border-bottom-color: var(--erp-primary);
        color: var(--erp-primary);
    }

    .erp-nav-item-active {
        background-color: var(--erp-primary-light);
        border-bottom-color: var(--erp-primary);
        color: var(--erp-primary);
    }

    .erp-icon-box {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 4px;
        background-color: #f1f5f9;
        color: #64748b;
    }

    .erp-icon-box svg {
        width: 20px;
        height: 20px;
        stroke-width: 2px;
        color:var(--bs-primary)
    }

    .erp-icon-box-md {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 4px;
        background-color: #f1f5f9;
        color: #64748b;
    }

        .erp-icon-box-xl {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 4px;
        background-color: #f1f5f9;
        color: #64748b;
    }

    .erp-nav-item.active .erp-icon-box {
        background-color: var(--erp-icon-bg-active);
        color: var(--erp-primary);
    }

    .erp-nav-text {
        font-size: 12px;
        font-weight: 500;
        white-space: nowrap;
    }

    .erp-nav-item.active .erp-nav-text {
        font-weight: 600;
    }

    /* .page-content-wrapper .page-title-box {
            background-color: rgba(var(--bs-primary-rgb), 0.11);
        } */
</style>
<?php $this->head() ?>
</head>

<body class="d-flex flex-column min-vh-100">
    <?php $this->beginBody() ?>

    <?php echo $this->render('modal'); ?>
    <?php echo $this->render('sub_modal'); ?>
    <?php echo $this->render('modal-fullscreen'); ?>
    <?php echo $this->render('header'); ?>



    <div class="navbar-fixed-container d-none d-flex justify-content-center align-items-center">
        <div class="erp-nav-list">
            <?= $this->render('navbar') ?>
        </div>
    </div>

    <div class="d-flex flex-column flex-grow-1 page-content-wrapper">

        <?php echo $this->render('page_title'); ?>

        <main class="px-0">
            <div class="container-fluid mt--45" style="max-width: 1600px;">
                <?= $content ?>
            </div>
        </main>
    </div>

    <footer class="mt-auto bg-white border-top py-4 px-4 position-relative">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center text-secondary small">
            <div class="mb-3 mb-md-0 text-center text-md-start">
                <p class="mb-0 fw-semibold text-dark">2025 © ERP Hospital.</p>
                <p class="mb-0 text-muted" style="font-size: 11px;"><?=Yii::$app->version ?></p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span>ผู้ให้การสนับสนุน</span>
                <a href="#" class="text-primary text-decoration-none fw-medium hover-underline">มูลนิธิรามาธิบดี</a>
            </div>
        </div>
        
    </footer>

    <!-- ปุ่มขึ้นบนสุด / ลงล่างสุด -->
    <?= $this->render('scroll_buttons') ?>

    <?php
    $pwaBaseUrl = rtrim(Yii::getAlias('@web'), '/');
    $pwaBaseUrlJs = json_encode($pwaBaseUrl);
    $pwaIconUrl = json_encode(Yii::getAlias('@web') . '/images/logo_new.png');
    $this->registerJs('window.ERP_PWA_BASE=' . $pwaBaseUrlJs . ';window.ERP_PWA_ICON=' . $pwaIconUrl . ';', View::POS_HEAD);
    $js = <<<'JS'
    function erpLucideIcons() { if (typeof lucide !== 'undefined' && lucide.createIcons) lucide.createIcons(); }
    erpLucideIcons();
    window.addEventListener('load', erpLucideIcons);
    AOS.init({});

    var erpInstallPrompt = null;
    var installBtn = document.getElementById('erp-install-pwa');
    var isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
    if (!isStandalone && installBtn) {
        window.addEventListener('beforeinstallprompt', function (e) {
            e.preventDefault();
            erpInstallPrompt = e;
            installBtn.classList.remove('d-none');
            if (typeof lucide !== 'undefined' && lucide.createIcons) lucide.createIcons();
        });
        installBtn.addEventListener('click', function () {
            if (!erpInstallPrompt) return;
            erpInstallPrompt.prompt();
            erpInstallPrompt.userChoice.then(function (choice) {
                if (choice.outcome === 'accepted') installBtn.classList.add('d-none');
                erpInstallPrompt = null;
            });
        });
    }

    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            var base = (window.ERP_PWA_BASE || '') || '';
            var swUrl = (base ? base + '/' : '/') + 'sw.js';
            var scope = (base ? base + '/' : '/');
            navigator.serviceWorker.register(swUrl, { scope: scope }).then(function (reg) {
                console.log('PWA: Service Worker registered', reg.scope);
            }).catch(function (err) {
                console.warn('PWA: Service Worker registration failed', err);
            });
        });
    }

    window.erpTestNotification = function (title, body) {
        if (!('Notification' in window)) {
            var isIOS = /iPhone|iPad|iPod/i.test(navigator.userAgent);
            var msg = isIOS
                ? 'บน iPhone/iPad: การแจ้งเตือนใช้ได้เมื่อติดตั้งแอปแล้ว (เพิ่มไปยังหน้าจอหลัก) และเปิดจากไอคอนแอป\n\nกรุณา: Safari -> ปุ่มแชร์ -> เพิ่มไปยังหน้าจอหลัก -> จากนั้นเปิดจากไอคอนแอป แล้วลองกดแจ้งเตือนอีกครั้ง'
                : 'เบราว์เซอร์นี้ไม่รองรับการแจ้งเตือน\n\nบนมือถือ Android: กรุณาใช้ Chrome หรือ Edge แล้วเปิดเว็บนี้ผ่าน HTTPS';
            alert(msg);
            return;
        }
        title = title || 'ERP Hospital';
        body = body || 'นี่คือการทดสอบการแจ้งเตือน';
        var iconUrl = window.ERP_PWA_ICON || '';
        function showNow() {
            try {
                new Notification(title, { body: body, icon: iconUrl });
            } catch (e) {
                new Notification(title, { body: body });
            }
        }
        if (Notification.permission === 'granted') {
            var done = false;
            var t = setTimeout(function () { if (!done) { done = true; showNow(); } }, 2500);
            if (navigator.serviceWorker && navigator.serviceWorker.ready) {
                navigator.serviceWorker.ready.then(function (reg) {
                    clearTimeout(t);
                    if (done) return;
                    reg.showNotification(title, { body: body, icon: iconUrl }).then(function () { done = true; }).catch(function () { if (!done) { done = true; showNow(); } });
                }).catch(function () { clearTimeout(t); if (!done) { done = true; showNow(); } });
            } else {
                clearTimeout(t);
                showNow();
            }
        } else if (Notification.permission !== 'denied') {
            Notification.requestPermission().then(function (p) {
                if (p === 'granted') window.erpTestNotification(title, body);
            });
        } else {
            alert('กรุณาอนุญาตการแจ้งเตือน: Chrome -> เมนู (จุด 3 จุด) -> การตั้งค่า -> ความเป็นส่วนตัวและความปลอดภัย -> การตั้งค่าไซต์ -> การแจ้งเตือน -> อนุญาตสำหรับไซต์นี้');
        }
    };
    document.getElementById('erp-test-notification') && document.getElementById('erp-test-notification').addEventListener('click', function () { erpTestNotification(); });

			// });
         	$('header .dropdown-mega').on('show.bs.dropdown', function () {
		if(!ddSliderIns){
			setTimeout(function(){
				//Mega dropdown slider
				megaDDSlider();
			}, 200)
		}
	})
   
		function megaDDSlider() {
	return $(".mega-dd-slider .owl-carousel").owlCarousel({
		loop: true,
		margin: 0,
		nav: false,
		dots: false,
		autoplay: true,
		autoplayTimeout: 2000,
		responsive: {
			0: {
				items: 1
			},
			600: {
				items: 1
			},
			1000: {
				items: 1
			}
		}
	});
}
	

JS;
    $this->registerJS($js, View::POS_END);
    ?>


    <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>