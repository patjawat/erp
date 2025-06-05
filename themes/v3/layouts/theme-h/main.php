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
$colorName = isset($site->data_json['theme_color_name']) ? $site->data_json['theme_color_name'] : '';
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
    <link rel="stylesheet" href="https://lettstartdesign.com/marvel/app/assets/css/styles.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <?php $this->head() ?>
</head>

  <?php echo $this->render('../modal'); ?>
    <?php echo $this->render('../sub_modal'); ?>
    <?php echo $this->render('../modal-fullscreen'); ?>
    
<body class="horizontal-navbar">
   <!-- Begin Page -->
   <div class="page-wrapper">
      <!-- Begin Header -->
      <?=$this->render('header')?>
      <!-- Header End -->
      <?=$this->render('navbar_menu')?>
      
      <!-- Begin main content -->
      <div class="main-content">
         <!-- content -->
         <div class="page-content">
        
              <!-- page header -->
<?php echo $this->render('page_title'); ?>
            
<div class="page-content-wrapper mt--45"  data-aos="fade-left"  data-aos-delay="300">
    <div class="container-fluid">
        <?= $content ?>
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
      				<span>ผู้ให้การสนับสนุน<span class="text-primary font-weight-500">มูลนิธิรามาธิบดี</span></span>
      			</div>
      		</div>
      	</div>
      </footer>



   </div>


<?php
$js = <<< JS

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