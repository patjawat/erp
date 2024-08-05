<?php
use yii\helpers\Html;
?>

<style>
    .sidebar li .tooltip{
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
}
.sidebar li:hover .tooltip{
  opacity: 1;
  pointer-events: auto;
  transition: all 0.4s ease;
  top: 50%;
  transform: translateY(-50%);
}
.sidebar.open li .tooltip{
  display: none;
}


/* .sidebar li a .links_name{ */
.links_name{
  /* color: #fff; */
  color: red;
  font-size: 15px;
  font-weight: 400;
  white-space: nowrap;
  opacity: 0;
  pointer-events: none;
  transition: 0.4s;
}
.sidebar.open li a .links_name{
  opacity: 1;
  pointer-events: auto;
}
.sidebar li a:hover .links_name,
.sidebar li a:hover i{
  transition: all 0.5s ease;
  color: #11101D;
}

</style>
<nav class="sidebar close">
        <header>
            <div class="image-text">
                <span class="image">
                  <?=Html::img('@web/img/logo.png')?>
                </span>

                <div class="text logo-text">
                    <span class="name">Hospital</span>
                    <span class="profession">Back Office</span>
                </div>
            </div>

            <!-- <i class="bx bx-chevron-right toggle"></i> -->
            <i class="fa-solid fa-circle-chevron-right toggle"></i>
        </header>

        <div class="menu-bar">
            <div class="menu">

                <li class="search-box">
                <i class="fa-solid fa-magnifying-glass icon"></i>
                    <input type="text" placeholder="Search...">
                </li>

                <ul class="menu-links">
                    <li class="nav-link">
                        <a href="#">
                        <i class="fa-solid fa-house-circle-check icon"></i>
                            <span class="text nav-text">Dashboard</span>
                            
                        </a>
                        <span class="links_name">Dashboard</span>
                    </li>

                    <li class="nav-link">
                        <a href="#">
                        <i class="fa-solid fa-chart-area icon"></i>
                            <span class="text nav-text">เกี่ยวกับบุคลากร</span>
                        </a>
                    </li>

                    <li class="nav-link">
                        <a href="#">
                        <i class="fa-solid fa-bell icon"></i>
                            <span class="text nav-text">บริหารงานบุคคล</span>
                        </a>
                    </li>

                    <li class="nav-link">
                        <a href="#">
                            <i class="bx bx-pie-chart-alt icon"></i>
                            <span class="text nav-text">แผนงานและโครงการ</span>
                        </a>
                    </li>

                    <li class="nav-link">
                        <a href="#">
                            <i class="bx bx-heart icon"></i>
                            <span class="text nav-text">อื่นๆ</span>
                        </a>
                    </li>

                    <li class="nav-link">
                        <a href="#">
                            <i class="bx bx-wallet icon"></i>
                            <span class="text nav-text">สำหรับผู้ดูแลระบบ</span>
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