<?php

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\AppAsset;
use app\assets\BootstapIconAsset;
// use app\widgets\Alert;
// use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
// use yii\bootstrap5\Nav;
// use yii\bootstrap5\NavBar;
use yii\web\View;

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
        integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <?php $this->head() ?>
</head>

<style>
/* html,
body {

}

h6,
.h6,
h5,
.h5,
h4,
.h4,
h3,
.h3,
h2,
.h2,
h1,
.h1,
{

    font-family: "IBM Plex Sans Thai", Sans-serif;
    font-weight: 100 !important;
    text-transform: capitalize;

}


.btn {
    font-family: 'Prompt', sans-serif !important;
    font-size: 0.9375rem;
}

thead,
tbody,
tfoot,
tr,
td,
th {
    font-family: 'Prompt', sans-serif !important;
    font-weight: 400;
} */
</style>

<body>

    <?php $this->beginBody() ?>
    <?php \dominus77\sweetalert2\Alert::widget(['useSessionFlash' => true]); ?>
    <?=$this->render('modal')?>             
    <main role="main" class="d-flex">
    <?php if(Yii::$app->controller->id == 'profile'):?>
        <?=$this->render('sidebar')?>
        <?php else:?>
            <?=$this->render('sidebar')?>
            <?php endif;?>
        <div class="home">
            <?=$this->render('navbar')?>
            <?=$this->render('content',['content' => $content ])?>
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