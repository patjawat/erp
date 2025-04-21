<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use kartik\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockEvent $model */
/** @var yii\widgets\ActiveForm $form */
// $formWarehouse = Yii::$app->session->get('selectMainWarehouse');

$cart = \Yii::$app->cartMain;
$products = $cart->getItems();
$assetType = \Yii::$app->session->get('asset_type');

?>
    <?php $form = ActiveForm::begin([
        'id' => 'form',
        'enableAjaxValidation' => true,  // เปิดการใช้งาน AjaxValidation
        'validationUrl' => ['/me/main-stock/create-validator'],
    ]); ?>

<div class="row">
<div class="col-6">
        <?php echo $this->render('_form_order', ['form' => $form, 'model' => $model]); ?>
    <?php echo $form->field($model, 'name')->hiddenInput()->label(false); ?>
    <?php echo $form->field($model, 'data_json[checker_confirm]')->hiddenInput()->label(false); ?>
    <?php if($model->isNewRecord):?>
    <?php echo $form->field($model, 'data_json[asset_type]')->hiddenInput(['value' => $assetType->code ?? ''])->label(false); ?>
    <?php echo $form->field($model, 'data_json[asset_type_name]')->hiddenInput(['value' => $assetType->title ?? '' ])->label(false); ?>
    <?php endif;?>
    <?php echo $model->isNewRecord ? $form->field($model, 'category_id')->hiddenInput()->label(false) : null; ?>



    <div class="text-center">
        <?php if($cart->getCount() == 0):?>
            <button type="button" class="btn btn-primary" disabled><i class="fa-solid fa-cart-shopping"></i> เบิก</button>
        <?php else:?>
            <?php  echo Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary rounded-pill shadow', 'id' => 'summit']); ?>
            <?php
            try {
               // echo Html::a('<i class="fa-solid fa-cart-shopping"></i> บันทึก', ['/me/main-stock/create','name' => 'order','type' => 'OUT','title' => 'เบิก'.$warehouseSelect['warehouse_name']], ['class' => 'btn btn-primary rounded-pill shadow position-relative open-modal','data' => ['size' => 'modal-ld']]);
                //code...
            } catch (\Throwable $th) {
                //throw $th;
            }
            ?>
                
        <?php endif?>
    </div>


    </div>
</div>
<?php ActiveForm::end(); ?>


<div id="viewMainCart"></div>


<?php
$viewMainCartUrl = Url::to(['/me/main-stock/view-cart']);
$js = <<< JS


ViewMainCar();
async function ViewMainCar()
    {
    await $.ajax({
        type: "get",
        url: "$viewMainCartUrl",
        dataType: "json",
        success: function (res) {
            $('#viewMainCart').html(res.content)
            $('.countMainItem').html(res.countItem)
            if(res.countItem < 1){
                $("#main-modal").modal("hide");

            }
            console.log(res.countItem);
        }
    });
    }
    
    $('#form').on('beforeSubmit',  function (e) {
        e.preventDefault();
         Swal.fire({
                title: 'ยืนยัน',
                text: 'เบิกวัสดุ',
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "ใช่, ยืนยัน!",
                cancelButtonText: "ยกเลิก",
                }).then(async (result) => {
                if (result.value == true) {
                    var form = \$(this);
                    $.ajax({
                        url: form.attr('action'),
                        type: 'post',
                        data: form.serialize(),
                        dataType: 'json',
                        success: async function (response) {
                                form.yiiActiveForm('updateMessages', response, true);
                                if(response.status == 'error') 
                                {
                                    Swal.fire({
                                        title: "เกิดข้อผิดพลาดบางอย่าง!",
                                        text: response.message,
                                        icon: "error"
                                        });
                                    }
                                    if(response.status == 'success') {
                                            location.reload()
                                            // closeModal()
                                            // success()
                                            // await  \$.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});                               
                                        }
                                    }
                            });
                        return false;
                        
                    }
                    return false;
                });
                return false;
                
    });
JS;
$this->registerJS($js);
?>