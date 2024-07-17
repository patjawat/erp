<?php

use app\models\Categorise;
use app\modules\sm\models\Order;
use unclead\multipleinput\MultipleInput;
use wbraganca\dynamicform\DynamicFormWidget;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\DetailView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\Order $model */
$this->title = 'ระบบพัสดุ';
$this->params['breadcrumbs'][] = ['label' => 'Orders', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

?>


<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-box-seam"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?php //  $this->render('../default/menu') ?>
<?php $this->endBlock(); ?>
<?php Pjax::begin(['id' => 'purchase-container']); ?>

<?= $this->render('../default/menu2') ?>
<div class="row justify-content-center">
    <div class="col-lg-2 col-md-4 col-sm-12">
        <?= $this->render('step', ['model' => $model]) ?>
    </div>
    <div class="col-lg-8 col-md-8 col-sm-12">
    
            <?= $this->render('step1', ['model' => $model]) ?>
            <?= $this->render('list_items', ['model' => $model]) ?>
        <?= $this->render('step2', ['model' => $model]) ?>
        <?= $this->render('step3', ['model' => $model]) ?>

    </div>
</div>


<?php
$js = <<< JS


    \$("body").on("click", ".pr-confirm", async function (e) {
      e.preventDefault();
      var url = \$(this).attr("href");
      await Swal.fire({
        title: "ยืนยัน?",
        text: "ส่งคำขอซื้อเพื่อให้หัวหน้าพิจารณาเห็นชอบ!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "ใช่, ลบเลย!",
        cancelButtonText: "ยกเลิก",
      }).then(async (result) => {
        console.log("result", result.value);
        if (result.value == true) {
           await \$.ajax({
            type: "post",
            url: url,
            dataType: "json",
            success:  function (response) {
              if (response.status == "success") {
                 \$.pjax.reload({
                  container: response.container,
                  history: false,
                  url: response.url,
                });
                success("ดำเนินการลบสำเร็จ รอหัวหน้าเห็นชอบ!.");
                // location.reload();
                if (response.close) {
                   \$("#main-modal").modal("hide");
                }
              }
            },
          });
        }
      });
    });

    JS;
$this->registerJS($js);
?>

<?php Pjax::end(); ?>