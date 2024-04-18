<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
?>
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script> -->
<style>

/* .menu-links li {
    position: relative;
  margin: 8px 0;
  list-style: none;
} */

   /* .menu-links li .tooltip {
    position: absolute;
    top: -20px;
    left: calc(100% + 15px);
    z-index: 3;
    background: #fff;
    box-shadow: 0 5px 10px rgba(0, 0, 0, 0.3);
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 15px;
    font-weight: 400;
    opacity: 0;
    white-space: nowrap;
    pointer-events: none;
    transition: 0s;
} */

/* .menu-links li a .links_name{
  color: #fff;
  font-size: 15px;
  font-weight: 400;
  white-space: nowrap;
  opacity: 0;
  pointer-events: none;
  transition: 0.4s;
} */


/* .menu-links li:hover .tooltip{
  opacity: 1;
  pointer-events: auto;
  transition: all 0.4s ease;
  top: 54%;
  transform: translateY(-50%);
}
.menu-links.open li .tooltip{
  display: none;
} */

/* @media (max-width: 420px) {
  .menu-links li .tooltip{
    display: none;
  }
} */
.tooltip {
  --bs-tooltip-bg: var(--bs-primary);
}

</style>
<?php
$menuItems = [
    [
        'title' => 'Dashboard' ,
        'url' => '/'
    ],
    [
        'title' => 'บริหารงานบุคคล' ,
        'url' => '/hr'
    ],
    [
        'title' => 'แผนงานและโครงการ' ,
        'url' => '/'
    ],
    [
        'title' => 'อื่นๆ' ,
        'url' => '/'
    ],
]
?>
<nav class="sidebar close">
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

                <li class="search-box">
                <i class="fa-solid fa-magnifying-glass icon"></i>
                    <input type="text" placeholder="Search...">
                </li>
                <ul class="menu-links">
                    <li class="nav-link" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Dashboard" data-bs-custom-class="custom-tooltip">
                        <a href="<?=Url::to('/')?>">
                        <i class="fa-solid fa-house-circle-check icon"></i>
                            <span class="text nav-text">Dashboard</span>
                        </a>
                    </li>

                    <li class="nav-link"  data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="บริหารงานบุคคล">
                    <a href="<?=Url::to('/hr')?>">
                        <i class="fa-solid fa-user-tag icon"></i>
                            <span class="text nav-text">บริหารงานบุคคล</span>
                        </a>
                    </li>
                    
                    <li class="nav-link" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="ทรัพย์สิน">
                    <a href="<?=Url::to('/sm')?>">
                        <i class="fa-solid fa-box-open icon"></i>
                            <span class="text nav-text">ทรัพย์สิน</span>
                        </a>
                    </li>

                    <li class="nav-link" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="แผนงานและโครงการ">
                    <a href="<?=Url::to('/pm')?>">
                        <i class="fa-solid fa-bars-progress icon"></i>
                            <span class="text nav-text">แผนงานและโครงการ</span>
                        </a>
                    </li>

                
                </ul>
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