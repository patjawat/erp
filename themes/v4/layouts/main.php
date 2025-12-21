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

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
        integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
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
        background-color: white;
        border-bottom: 1px solid #dee2e6;
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
    .page-content-wrapper{
        background-color: var(--bs-body-bg);
    }

    /* .page-content-wrapper .page-title-box {
            background-color: rgba(var(--bs-primary-rgb), 0.11);
        } */
</style>
<?php $this->head() ?>
</head>

<body class="d-flex flex-column min-vh-100">
    <?php $this->beginBody() ?>

    <?php echo $this->render('loader'); ?>
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
        <div class="position-fixed bottom-0 end-0 p-4 d-flex flex-column gap-2 z-3">
            <button class="btn btn-secondary rounded-circle d-flex align-items-center justify-content-center shadow border-0 p-0 header-scroll-btn" style="width: 40px; height: 40px; background-color: #6b7280;"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white">
                    <path d="m5 12 7-7 7 7"></path>
                    <path d="M12 19V5"></path>
                </svg></button>
            <button class="btn btn-secondary rounded-circle d-flex align-items-center justify-content-center shadow border-0 p-0 header-scroll-btn" style="width: 40px; height: 40px; background-color: #6b7280;"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white">
                    <path d="M12 5v14"></path>
                    <path d="m19 12-7 7-7-7"></path>
                </svg></button>
        </div>
    </footer>

    <!-- ปุ่มขึ้นบนสุด / ลงล่างสุด -->
    <?= $this->render('scroll_buttons') ?>

    <?php
    $js = <<< JS
    //ส่วนการ load overlay
        // tableLoading1.style.display = 'none';
        //         function showTableLoading() {
        //             let progress = 0;
        //             const tableLoading1 = document.getElementById('tableLoading1');
        //             const tableProgress1 = document.getElementById('tableProgress1');
        //             const tableStatus1 = document.getElementById('tableStatus1');

        //             tableProgress1.style.width = '0%';
        //             tableProgress1.setAttribute('aria-valuenow', '0');
        //             tableStatus1.textContent = '0%';
        //             updateProgressColorBar(0);

        //             tableLoading1.style.display = 'flex';

        //             const interval = setInterval(function () {
        //                 progress += Math.floor(Math.random() * 15) + 5;
        //                 if (progress > 100) progress = 100;

        //                 tableProgress1.style.width = progress + '%';
        //                 tableProgress1.setAttribute('aria-valuenow', progress);
        //                 tableStatus1.textContent = progress + '%';

        //                 updateProgressColorBar(progress);

        //                 if (progress === 100) {
        //                     clearInterval(interval);
        //                     // setTimeout(hideTableLoading, 300);
        //                 }
        //             }, 300);
        //         }
                            
        //     function updateProgressColorBar(progress) {
        //         const el = document.getElementById('tableProgress1');
                
        //         // ลบคลาสเดิมก่อน
        //         el.classList.remove('bg-danger', 'bg-warning', 'bg-primary', 'bg-success');
        //         el.classList.add('bg-primary');  // น้ำเงิน

        //     }

        //     function hideTableLoading() {
        //         tableLoading1.style.opacity = '0';
        //         setTimeout(function() {
        //             tableLoading1.style.display = 'none';
        //             tableLoading1.style.opacity = '1';
        //         }, 300);
        //     }
            // จบส่วนการ load overlay
  AOS.init({});

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