<?php
use app\components\SiteHelper;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Dashbroad';
$this->params['breadcrumbs'][] = $this->title;
$companyName = SiteHelper::getInfo()['company_name'];
?>
<?php $this->beginBlock('page-title'); ?>
<?php echo $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
ยินดีต้อนรับ <?php echo $companyName; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>

<?php $this->endBlock(); ?>



<div class="row">
    <div class="col-md-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <?php echo Html::a('บุคลากร', ['/hr'], ['class' => 'text-muted text-uppercase fs-6']); ?>
                        <h2 class="mb-0 mt-1" id="totalEmployees"></h2>
                    </div>
                    <div class="text-center" style="position: relative;">
                        <span class="text-success fw-bold fs-13">
                            <i class="bx bx-up-arrow-alt"></i> 10.21%
                        </span>
                        <div class="resize-triggers">
                            <div class="expand-trigger">
                                <div style="width: 91px; height: 70px;"></div>
                            </div>
                            <div class="contract-trigger"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <span class="text-muted text-uppercase fs-6">วัสดุ</span>
                        <h2 class="mb-0 mt-1">0</h2>
                    </div>
                    <div class="text-center" style="position: relative;">
                        
                        <span class="text-danger fw-bold fs-13">
                            <i class="bx bx-down-arrow-alt"></i> 1234,443,344.50 บาท
                        </span>
                        <div class="resize-triggers">
                            <div class="expand-trigger">
                                <div style="width: 91px; height: 70px;"></div>
                            </div>
                            <div class="contract-trigger"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-xl-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <?php echo Html::a('ทรัพย์สิน', ['/am'], ['class' => 'text-muted text-uppercase fs-6']); ?>
                        <h2 class="mb-0 mt-1" id="totalAsset"></h2>
                    </div>
                    <div class="text-center" style="position: relative;">
                       
                        <span class="text-danger fw-bold fs-13">
                            <i class="bx bx-down-arrow-alt"></i> 234,443,344.50
                        </span>
                        <div class="resize-triggers">
                            <div class="expand-trigger">
                                <div style="width: 91px; height: 70px;"></div>
                            </div>
                            <div class="contract-trigger"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php echo $this->render('./row2'); ?>
<?php echo $this->render('./row3'); ?>
<?php echo $this->render('./row5'); ?>
<?php
use yii\web\View;

$urlSummary = Url::to(['/summary']);
$js = <<< JS
loadSummary()

async function loadSummary(){
    await $.ajax({
        type: "get",
        url: "$urlSummary",
        dataType: "json",
        success: function (res) {
            console.log(res);
            $('#totalEmployees').text(res.totalEmployee.total);
            $('#totalAsset').text(res.totalAssetPrice.total);
        }
    });
}
JS;
$this->registerJS($js, View::POS_END);
?>