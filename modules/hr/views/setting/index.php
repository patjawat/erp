<?php
/** @var yii\web\View $this */
use yii\helpers\Html;
?>
<h1>ตั้งค่าระบบบุคลากร</h1>

<p>
    You may change the content of this page by modifying
    the file <code><?= __FILE__; ?></code>.
</p>
<style>
    .menu-box > li{
        width: 150px;
    }
    .menu-box > li :hover{
        background-color: #d8dff2;
        border-radius: 10px;
    }
</style>

<ul class="nav col-12 col-lg-auto my-2 justify-content-center my-md-0 text-small menu-box">
                    <li>
                    <?=Html::a(
                        '<span data-aos="fade-up" data-aos-delay="200"><i class="bi bi-database-down fs-1"></i></span>
                        <span data-aos="fade-up" data-aos-delay="100">Import Backoffice</span>',
                        ['/backoffice/person/person'],['class' => 'nav-link text-secondary d-flex flex-column text-center justify-content-center text-truncate open-modal','data' => ['size' => 'modal-md'],'style' => ''])?>
                    </li>
                    <li>
                    <?=Html::a(
                        '<span data-aos="fade-up" data-aos-delay="400"><i class="bi bi-people-fill fs-1"></i></span>
                        <span data-aos="fade-up" data-aos-delay="300">บุคลากร</span>',
                        ['/hr/setting'],['class' => 'nav-link text-secondary d-flex flex-column text-center justify-content-center text-truncate','style' => ''])?>
                        <a href="/hr" class="nav-link text-secondary d-flex flex-column text-center justify-content-center">
                            
                            
                        </a>
                    </li>
                  

                </ul>
