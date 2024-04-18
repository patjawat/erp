<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
?>
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script> -->
<style>

</style>

<nav class="sidebar <?=Yii::$app->controller->id == 'profile' ? '' : 'close'?>">
        <header>
            <div class="image-text">
                <span class="image">
       <?= Html::img('@web/img/logo.png')?>
                </span>

                <div class="text logo-text">
                    <span class="name">Hospital</span>
                    <span class="profession">Back Office</span>
                </div>
            </div>
            <i class="fa-solid fa-circle-chevron-right toggle"></i>
        </header>

        <div class="menu-bar">
            <div class="menu">

             profile
            </div>

            <div class="bottom-content">
                <li class="">
                    <a href="#">
                        <i class="bx bx-log-out icon"></i>
                        <span class="text nav-text">Logout</span>
                    </a>
                </li>

                <li class="mode">
                    <div class="sun-moon">
                        <i class="bx bx-moon icon moon"></i>
                        <i class="bx bx-sun icon sun"></i>
                    </div>
                    <span class="mode-text text">Dark mode</span>

                    <div class="toggle-switch">
                        <span class="switch"></span>
                    </div>
                </li>
                
            </div>
        </div>

    </nav>
    <?php 
    $js = <<< JS

    // $(function(){
    //     const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    //     const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
    // })


    JS;
    $this->registerJS($js,View::POS_END)
    
    ?>