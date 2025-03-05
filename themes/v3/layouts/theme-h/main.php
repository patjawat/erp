<?php

/** @var yii\web\View $this */
/** @var string $content */

use yii\web\View;
use app\assets\AppAsset;
// use app\widgets\Alert;
// use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
// use yii\bootstrap5\Nav;
// use yii\bootstrap5\NavBar;
use app\assets\BootstapIconAsset;

AppAsset::register($this);
BootstapIconAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">

<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php $this->registerCsrfMetaTags() ?>

    <title><?= Html::encode($this->title) ?></title>
    <link rel="stylesheet"
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <?php $this->head() ?>
</head>


<body>

    <?php $this->beginBody() ?>
    <?=$this->render('../modal')?>             
    <main role="main">
        <div class="">
            <?php echo $this->render('header')?>
            <?php echo $this->render('h-navbar')?>
            
        </div>

    
            <div class="p-5" style="margin-top:55px;">
                <?=$content;?>

            </div>

        
    </main>
    
    
    <?php
$js = <<< JS

AOS.init({
    
});

$('body').on('click', '#app-container', function () {
    
    console.log('Click');
    $('.app-container').toggle();
    
});

const body = document.querySelector('body'),
      sidebar = body.querySelector('nav'),
      toggle = body.querySelector(".toggle"),
      searchBtn = body.querySelector(".search-box"),
      modeSwitch = body.querySelector(".toggle-switch"),
      modeText = body.querySelector(".mode-text");


toggle.addEventListener("click" , () =>{
    sidebar.classList.toggle("close");
})

searchBtn.addEventListener("click" , () =>{
    sidebar.classList.remove("close");
})

modeSwitch.addEventListener("click" , () =>{
    body.classList.toggle("dark");
    
    if(body.classList.contains("dark")){
        modeText.innerText = "Light mode";
    }else{
        modeText.innerText = "Dark mode";
        
    }
});

JS;
$this->registerJS($js,View::POS_END);

?>
   
<?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>