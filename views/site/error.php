<?php

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = $name;
$me = Yii::$app->employee::GetEmployee();
?>

<!-- Error 404 Template 1 - Bootstrap Brain Component -->
<section class="py-3 py-md-5 min-vh-100 d-flex justify-content-center align-items-center">
  <div class="container">
    <div class="row">
      <div class="col-12">
        <div class="text-center">
          <h2 class="d-flex justify-content-center align-items-center gap-2 mb-4">
            <span class="display-1 fw-bold">4</span>
            <i class="bi bi-exclamation-circle-fill text-danger display-4"></i>
            <span class="display-1 fw-bold bsb-flip-h">4</span>
          </h2>
          <h3 class="h2 mb-2"><?=$me->fullname?> เราไม่อนุญาติให้คุณเข้าถึงข้อมูลส่วนนี้</h3>
          <p class="mb-5">หากต้องการกรุณาติดต่อผู้ดูแลระบบ</p>
          <a class="btn bsb-btn-5xl btn-dark rounded-pill px-5 fs-6 m-0" href="<?=Url::to(['/me'])?>" role="button">Back to Home</a>
    
        </div>
      </div>
    </div>
  </div>
</section>