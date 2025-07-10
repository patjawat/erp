<?php

/** @var yii\web\View $this */

/**
 * @var string $content
 */

use yii\web\View;
use app\assets\AppAsset;
use yii\bootstrap5\Html;
use app\models\Categorise;
use app\assets\BootstapIconAsset;

AppAsset::register($this);
BootstapIconAsset::register($this);

$site = Categorise::findOne(['name' => 'site']);
$colorName = isset($site->data_json['theme_color_name']) ? $site->data_json['theme_color_name'] : 'blue';
$moduleId = Yii::$app->controller->module->id;

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?php echo Yii::$app->language; ?>" class="h-100" data-bs-theme="<?php echo $colorName; ?>">

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
#nprogress .bar {
    background: linear-gradient(90deg, #fce9af 0%, #f7c873 100%) !important;
}
</style>

<style>
/* Table container with relative positioning */
.table-container {
    position: relative;
    min-height: 200px;
}

/* Table Loading Overlay */
.table-loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    /* background-color: rgba(255, 255, 255, 0.8); */
    background-color: rgba(255, 255, 255, 0.5);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    z-index: 100;
    border-radius: 8px;
    backdrop-filter: blur(2px);
    transition: all 0.3s ease;
}

.table-loading-content {
    text-align: center;
    padding: 1rem;
}

.table-loading-spinner {
    width: 50px;
    height: 50px;
    margin-bottom: 1rem;
    margin-left: auto;
    margin-right: auto;
}

.table-loading-spinner .spinner-border {
    width: 3rem;
    height: 3rem;
    border-width: 0.25rem;
    color: var(--primary-color);
}

.table-loading-message {
    font-size: 0.9rem;
    color: #666;
    margin-bottom: 0.5rem;
}

.table-progress {
    height: 6px;
    border-radius: 3px;
    margin-bottom: 0.5rem;
    width: 150px;
    background-color: #e9ecef;
}

.table-progress-bar {
    background-color: var(--primary-color);
    border-radius: 3px;
    transition: width 0.5s ease;
}

/* Row loading styles */
tr.loading-row {
    position: relative;
    background-color: rgba(236, 240, 241, 0.5);
}

tr.loading-row td {
    position: relative;
    color: transparent;
}

tr.loading-row td:after {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, rgba(255, 255, 255, 0), rgba(255, 255, 255, 0.5), rgba(255, 255, 255, 0));
    background-size: 200% 100%;
    animation: loading-shimmer 1.5s infinite;
}

@keyframes loading-shimmer {
    0% {
        background-position: -200% 0;
    }

    100% {
        background-position: 200% 0;
    }
}

/* Cell loading indicator */
.cell-loading {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 2px solid rgba(52, 152, 219, 0.2);
    border-radius: 50%;
    border-top-color: var(--primary-color);
    animation: spin 1s infinite linear;
    vertical-align: middle;
    margin-right: 5px;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }

    100% {
        transform: rotate(360deg);
    }
}

/* Toolbar */
.table-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.table-title {
    margin: 0;
    font-size: 1.2rem;
    font-weight: 600;
}
</style>

</style>
<?php echo $this->render('../modal'); ?>
<?php echo $this->render('../sub_modal'); ?>
<?php echo $this->render('../modal-fullscreen'); ?>

<body class="horizontal-navbar">

    <!-- Begin Page -->
    <div class="page-wrapper">

        <!-- Begin Header -->
        <?= $this->render('header') ?>
        <!-- Header End -->
        <?= $this->render('navbar_menu') ?>

        <!-- Begin main content -->
        <div class="main-content">
            <!-- content -->
            <div class="page-content">

                <!-- page header -->
                <?php echo $this->render('page_title'); ?>
                <?php echo $this->render('loader'); ?>

                <div class="page-content-wrapper mt--45" data-aos="fade-left" data-aos-delay="300">
                    <div class="container-fluid">
                        <div id="page-content">
                            <div class="table-container">
                                <?= $content; ?>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
            <!-- main content End -->
            <!-- footer -->
            <!-- footer -->
            <div class="preloader">
                <div class="status">
                    <div class="spinner-border avatar-sm text-primary m-2" role="status"></div>
                </div>
            </div>
            <footer class="footer">
                <div class="container-fluid">
                    <div class="row align-items-center">
                        <div class="col-md-5 mb-1 mb-md-0">
                            <span><span id="date">2025</span> &copy; ERP Hospital.</span>
                        </div>

                        <div class="col-md-5 text-md-end">
                            <span>ผู้ให้การสนับสนุน<span
                                    class="text-primary font-weight-500">มูลนิธิรามาธิบดี</span></span>
                        </div>
                    </div>
                </div>
            </footer>



        </div>



        <?php
$js = <<< JS
    //ส่วนการ load overlay
        tableLoading1.style.display = 'none';
                function showTableLoading() {
                    let progress = 0;
                    const tableLoading1 = document.getElementById('tableLoading1');
                    const tableProgress1 = document.getElementById('tableProgress1');
                    const tableStatus1 = document.getElementById('tableStatus1');

                    tableProgress1.style.width = '0%';
                    tableProgress1.setAttribute('aria-valuenow', '0');
                    tableStatus1.textContent = '0%';
                    updateProgressColorBar(0);

                    tableLoading1.style.display = 'flex';

                    const interval = setInterval(function () {
                        progress += Math.floor(Math.random() * 15) + 5;
                        if (progress > 100) progress = 100;

                        tableProgress1.style.width = progress + '%';
                        tableProgress1.setAttribute('aria-valuenow', progress);
                        tableStatus1.textContent = progress + '%';

                        updateProgressColorBar(progress);

                        if (progress === 100) {
                            clearInterval(interval);
                            // setTimeout(hideTableLoading, 300);
                        }
                    }, 300);
                }
                            
            function updateProgressColorBar(progress) {
                const el = document.getElementById('tableProgress1');
                
                // ลบคลาสเดิมก่อน
                el.classList.remove('bg-danger', 'bg-warning', 'bg-primary', 'bg-success');
                el.classList.add('bg-primary');  // น้ำเงิน

                // เพิ่มคลาสใหม่ตามช่วง progress
                // if (progress < 30) {
                //     el.classList.add('bg-danger');   // แดง
                // } else if (progress < 60) {
                //     el.classList.add('bg-warning');  // เหลือง
                // } else if (progress < 90) {
                //     el.classList.add('bg-primary');  // น้ำเงิน
                // } else {
                //     el.classList.add('bg-success');  // เขียว
                // }
            }

            function hideTableLoading() {
                tableLoading1.style.opacity = '0';
                setTimeout(function() {
                    tableLoading1.style.display = 'none';
                    tableLoading1.style.opacity = '1';
                }, 300);
            }
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