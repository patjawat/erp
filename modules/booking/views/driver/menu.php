<?php
use yii\helpers\Html;
use app\models\Categorise;
use app\components\ApproveHelper;
use app\components\CategoriseHelper; 
use app\modules\am\models\AssetItem;
$path = Yii::$app->request->getPathInfo();

?>
<!-- <div class="d-flex gap-2"> -->
        <?php // Html::a('<i class="fa-solid fa-chart-pie"></i> Dashboard <span class="badge rounded-pill badge-soft-primary text-primary fs-13 fw-semibold">0</span>',['/booking/driver/dashboard'],['class' => $path == 'booking/booking-car-items' ? 'btn btn-light' : 'btn btn-light'])?>
        <?php // Html::a('<i class="fa-solid fa-car"></i> รถทั่วไป <span class="badge rounded-pill badge-soft-primary text-primary fs-13 fw-semibold">0</span>',['/booking/driver','car_type' => 'general'],['class' => $path == 'booking/booking-car' ? 'btn btn-light' : 'btn btn-light'])?>
        <?php // Html::a('<i class="fa-solid fa-truck-medical"></i> รถพยาบาล <span class="badge rounded-pill badge-soft-primary text-primary fs-13 fw-semibold">0</span>',['/booking/driver','car_type' => 'ambulance'],['class' => $path == 'booking/booking-car' ? 'btn btn-light' : 'btn btn-light'])?>

<!-- </div> -->


    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="bi bi-truck me-2"></i>ระบบจัดรถยนต์
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <?php echo Html::a('<i class="fa-solid fa-chart-pie"></i> dashboard',['/booking/driver/dashboard'],['class' => 'nav-link active'])?>
                    </li>
                    <li class="nav-item">
                        <?php echo Html::a('<i class="bi bi-ui-checks"></i> ทะเบียนจัดสรร',['/booking/driver'],['class' => 'nav-link active'])?>
                    </li>
                </ul>
            </div>
        </div>
    </nav>