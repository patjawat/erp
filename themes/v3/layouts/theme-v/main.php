<?php

/**
 * @var View $this
 */

/*
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
$colorName = isset($site->data_json['theme_color_name']) ? $site->data_json['theme_color_name'] : '';
$moduleId = Yii::$app->controller->module->id;
?>

<?php $this->beginPage(); ?>
<!DOCTYPE html>
<html lang="<?php echo Yii::$app->language; ?>" class="h-100" data-bs-theme="<?php echo $colorName; ?>">

<head>
    <meta charset="<?php echo Yii::$app->charset; ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php $this->registerCsrfMetaTags(); ?>

    <title><?php echo Html::encode($this->title); ?></title>
    <script src="https://static.line-scdn.net/liff/edge/2/sdk.js"></script>

    <?php $this->head(); ?>
</head>

<style>
#nprogress .bar {
    background: #ffc107 !important;
}

.tooltip-inner {
    background-color: #2f4fff;
    box-shadow: 0px 0px 4px black;
    opacity: 1 !important;
    font-family: kanit !important;
}

.tooltip.bs-tooltip-right .tooltip-arrow::before {
    border-right-color: #2f4fff !important;
}

.tooltip.bs-tooltip-left .tooltip-arrow::before {
    border-left-color: #2f4fff !important;
}

.tooltip.bs-tooltip-bottom .tooltip-arrow::before {
    border-bottom-color: #2f4fff !important;
}

.tooltip.bs-tooltip-top .tooltip-arrow::before {
    border-top-color: #2f4fff !important;
}


</style>

<body>
    <?php $this->beginBody(); ?>
    <?php echo $this->render('../modal'); ?>
    <?php echo $this->render('../sub_modal'); ?>
    <?php echo $this->render('../modal-fullscreen'); ?>
    <?php echo $this->render('offcanvas'); ?>
    <main role="main">
	<?php if(!Yii::$app->user->isGuest):?>
        <div class="page-wrapper">
            <?php echo $this->render('header'); ?>
            <?php echo $this->render('sidebar'); ?>
            <div class="main-content">
                <div class="page-content">
                    <?php echo $this->render('page_title'); ?>
                    <div class="page-content-wrapper mt--45">
                        <div  id="page-content">
                            <?php  echo $this->render('content', ['content' => $content]); ?>
                        </div>
						<div id="loader">
							<?php echo $this->render('loader'); ?>
						</div>
                    </div>

                </div>
            </div>
            <?php echo $this->render('footer'); ?>
			<?php echo $this->render('right_setting'); ?>
        </div>
		<?php endif;?>
    </main>

<?php
$js = <<< JS



// let popover = new bootstrap.Popover($(".popover-hover"), {
//   trigger: "manual",
// });

// $(".popover-hover")
//   .on("mouseenter", function () {
//     popover.show();
//   })
//   .on("mouseleave", function () {
//     popover.hide();
//   });

  
const metisMenu = $('.employee-welcome');
			var scrollBarCont, isfullscreen = false, ddSliderIns;
			var sidebarActive = localStorage.getItem("classes") == '' ? false : true;
			var sidebarMenu = $('#side-menu > li > a.side-nav-link>i')
			if(sidebarActive){
				\$("body").addClass('left-side-menu-condensed');
				$('.employee-welcome').addClass('d-none')
				sidebarMenu.addClass('fs-4');
				
			}
			
		$("#vertical-menu-btn").on("click", function (e) {
					e.preventDefault();
					e.stopPropagation();
			        console.log(\$(window).width());
					if(\$(window).width() > 1024) {
						if ($("body").hasClass("left-side-menu-condensed")) {
							$("body").removeClass("left-side-menu-condensed");
							$('.employee-welcome').removeClass('d-none')
							sidebarMenu.removeClass('fs-4');
						}
						else {
							$("body").addClass('left-side-menu-condensed');
							$('.employee-welcome').addClass('d-none')
							sidebarMenu.addClass('fs-4');
						}
						var classList = document.body.classList.value.trim();
						localStorage.setItem("classes", classList);
						
					}
					else {
						$("body").toggleClass("show-sidebar");
					}
				});
			// });

			AOS.init({});
			\$("#full-screen").on("click", function () {
					\$(this).children().toggleClass("bx-fullscreen bx-exit-fullscreen");
					if (!isfullscreen) {
						isfullscreen = fullScreen(isfullscreen);
					}
					else {
						isfullscreen = exitFullScreen(isfullscreen);
					}
				});
				
			function fullScreen(isfullscreen) {
				var docBrowserElem = document.documentElement
				if (docBrowserElem.requestFullscreen) {
					docBrowserElem.requestFullscreen();
				} else if (docBrowserElem.mozRequestFullScreen) { /* Firefox */
					docBrowserElem.mozRequestFullScreen();
				} else if (docBrowserElem.webkitRequestFullscreen) { /* Chrome, Safari and Opera */
					docBrowserElem.webkitRequestFullscreen();
				} else if (docBrowserElem.msRequestFullscreen) { /* IE/Edge */
					docBrowserElem.msRequestFullscreen();
				}
				isfullscreen = true;
				return isfullscreen
			}

			function exitFullScreen(isfullscreen) {
				if (document.exitFullscreen) {
					document.exitFullscreen();
				} else if (document.mozCancelFullScreen) {
					document.mozCancelFullScreen();
				} else if (document.webkitExitFullscreen) {
					document.webkitExitFullscreen();
				} else if (document.msExitFullscreen) {
					document.msExitFullscreen();
				}
				isfullscreen = false;
				return isfullscreen
			}

			var tooltipTriggerList = [].slice.call(
			    document.querySelectorAll('[data-bs-toggle="tooltip"]')
			    );
			    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
			    return new bootstrap.Tooltip(tooltipTriggerEl);
			    });

JS;
$this->registerJS($js, View::POS_END);
?>


<!--Start of Tawk.to Script-->
<script type="text/javascript">
// var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
// (function(){
// var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
// s1.async=true;
// s1.src='https://embed.tawk.to/682df6ff98a1b819123fbd6c/1irpopm6l';
// s1.charset='UTF-8';
// s1.setAttribute('crossorigin','*');
// s0.parentNode.insertBefore(s1,s0);
// })();
</script>
<!--End of Tawk.to Script-->

    <?php $this->endBody(); ?>
</body>

</html>
<?php $this->endPage(); ?>