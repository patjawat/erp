

<?php

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\AppAsset;
use app\assets\BootstapIconAsset;
use app\widgets\Alert;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;
use yii\web\View;

?>
<header class="p-2">
        <div class="">
            <div class="d-flex justify-content-between">
                <div class="d-flex gap-2">
                    <ul class="nav col-12 col-lg-auto me-lg-auto mb-2 justify-content-center mb-md-0">
                        <li>
                            <a href="#" class="nav-link px-2 link-secondary">เมนูหลัก</a>
                        </li>
                        <li>
                            <a href="#" class="nav-link px-2 link-body-emphasis">เกี่ยวกับบุคลากร</a>
                        </li>
                        <li>
                            <a href="#" class="nav-link px-2 link-body-emphasis">บริหารงานบุคคล</a>
                        </li>
                        <li>
                            <a href="#" class="nav-link px-2 link-body-emphasis">แผนงานและโครงการ</a>
                        </li>
                        <li>
                            <a href="#" class="nav-link px-2 link-body-emphasis">อื่นๆ</a>
                        </li>
                        <li>
                            <a href="#" class="nav-link px-2 link-body-emphasis">สำหรับผู้ดูแลระบบ</a>
                        </li>
                    </ul>
                </div>
                
                <div class="d-flex gap-4">
                <div class="form-search">
                <input type="text" class="form-control rounded-pill" id="exampleInputEmail1" aria-describedby="emailHelp">
                </div>

                    <div class="app-bar d-flex gap-3">
                        <?=Html::img('@web/img/notify.svg')?>
                        <?=Html::img('@web/img/app.svg',['id' => 'app-container'])?>

                        <!-- <button class="btn btn-primary">Login</button> -->
                        <div class="flex-shrink-0 dropdown mt-2">
                            <a href="#" class="d-block link-body-emphasis text-decoration-none dropdown-toggle show"
                                data-bs-toggle="dropdown" aria-expanded="true">
                                <img src="https://github.com/mdo.png" alt="mdo" width="32" height="32"
                                    class="rounded-circle">
                            </a>
                            <ul class="dropdown-menu text-small shadow" data-popper-placement="bottom-end"
                                style="position: absolute; inset: 0px 0px auto auto; margin: 0px; transform: translate(0px, 34px);">
                                <li><a class="dropdown-item" href="#">New project...</a></li>
                                <li><a class="dropdown-item" href="#">Settings</a></li>
                                <li><a class="dropdown-item" href="#">Profile</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="#">Sign out</a></li>
                            </ul>
                        </div>

                    </div>
                    <div></div>
                </div>
            </div>
        </div>

        <div class="app-container" style="z-index: 2;">

            <div class="card border-0">
                <div class="card-body ">
                    <h5 class="card-title text-center">App Center</h5>
                    <div class="d-flex flex-wrap gap-2 p-2">
                        <a href="#" class="app-item">
                            <i class="fa-solid fa-users"></i>
                            บุคลากร</a>
                        <a href="#" class="app-item">
                            <i class="fa-solid fa-box-open"></i>จองรถ</a>
                        <a href="#" class="app-item"><i class="fa-solid fa-download"></i>แผนงาน</a>
                        <a href="#" class="app-item"><i class="fa-solid fa-paperclip"></i>การลา</a>
                        <a href="#" class="app-item"><i class="fa-solid fa-calendar-days"></i>สวัสดิการ</a>
                        <a href="#" class="app-item"><i class="fa-solid fa-heart"></i> App1</a>
                        <a href="#" class="app-item"><i class="fa-solid fa-pen-nib"></i> App1</a>
                        <a href="#" class="app-item"><i class="fa-solid fa-mug-hot"></i> App1</a>

                    </div>

                </div>
            </div>

        </div>
    </header>