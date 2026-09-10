<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var array $tools */

$this->title = 'เครื่องมือ';
?>
<?php $this->beginBlock('page-title'); ?>เครื่องมือ<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>เครื่องมือช่วยคิด วิเคราะห์ และจัดทำเอกสารกระบวนการ<?php $this->endBlock(); ?>

<div class="row g-3">
    <?php foreach ($tools as $t): ?>
    <div class="col-12 col-sm-6 col-lg-4">
        <a href="<?= Url::to($t['url']) ?>" class="text-decoration-none">
            <div class="card h-100 shadow-sm border-0 tool-card">
                <div class="card-body d-flex align-items-start gap-3">
                    <div class="tool-icon bg-<?= $t['tone'] ?>-subtle text-<?= $t['tone'] ?>-emphasis">
                        <i class="bi <?= $t['icon'] ?>"></i>
                    </div>
                    <div>
                        <h6 class="fw-semibold mb-1 text-body"><?= Html::encode($t['title']) ?></h6>
                        <p class="small text-muted mb-0"><?= Html::encode($t['desc']) ?></p>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <?php endforeach; ?>
</div>

<style>
.tool-card { transition: transform .12s ease, box-shadow .12s ease; }
.tool-card:hover { transform: translateY(-2px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.1) !important; }
.tool-icon { width:46px; height:46px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.4rem; flex:none; }
</style>
