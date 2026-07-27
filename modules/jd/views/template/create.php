<?php
use yii\helpers\Html;
/** @var yii\web\View $this */
/** @var app\modules\jd\models\JdTemplate $model */
$this->title = 'สร้าง Template JD';
$this->params['breadcrumbs'][] = ['label' => 'คลัง Template JD', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?><div><div class="d-flex align-items-center gap-2 flex-wrap"><h4 class="fw-semibold mb-1"><?= Html::encode($this->title) ?></h4><span class="badge bg-secondary-subtle text-secondary-emphasis">ขั้นตอนที่ 1 จาก 2</span></div><div class="text-muted small">กำหนดข้อมูลหลัก แล้วกรอกเนื้อหา 10 หมวดด้วยรูปแบบเดียวกับการสร้าง JD</div></div><?php $this->endBlock(); ?>
<div class="container-fluid py-3 py-md-4 px-3 px-md-4"><div class="card shadow-sm border-0 mx-auto" style="max-width:980px;border-radius:10px"><div class="card-header bg-white border-bottom px-3 py-3"><h6 class="fw-semibold mb-0"><i class="bi bi-file-earmark-plus me-2 text-primary"></i>ข้อมูล Template</h6></div><div class="card-body p-3 p-md-4"><?= $this->render('_library_form', ['model' => $model]) ?></div></div></div>
