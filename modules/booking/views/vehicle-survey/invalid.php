<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var string $message */
?>
<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="card border-0 shadow rounded-4">
            <div class="card-body p-4 text-center">
                <div class="display-6 text-secondary mb-2"><i class="bi bi-link-45deg"></i></div>
                <h5 class="mb-2">ไม่สามารถเปิดแบบประเมินได้</h5>
                <p class="text-muted mb-0"><?= Html::encode($message) ?></p>
            </div>
        </div>
    </div>
</div>
