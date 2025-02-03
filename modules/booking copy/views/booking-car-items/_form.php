<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use app\modules\am\models\Asset;
use app\modules\booking\models\BookingCarsItems;

/** @var yii\web\View $this */
/** @var app\modules\booking\models\BookingCarsItems $model */
/** @var yii\widgets\ActiveForm $form */

$array = BookingCarsItems::find()->all();
$notIn = ArrayHelper::getColumn($array,'asset_item_id');
// \Yii::$app->response->format = Response::FORMAT_JSON;


$asset = Asset::find()
    ->leftJoin('categorise', 'categorise.code = asset.asset_item')
    ->where([
        'asset.asset_group' => '3',
        'categorise.category_id' => '4'
    ])
    ->andWhere(['not in','asset.id',$notIn])
    ->orderBy([
        'asset.code' => SORT_DESC,
        'asset.receive_date' => SORT_ASC
    ])
    ->all();

    $listCars = ArrayHelper::map($asset,'id',function($model){
        return $model->assetItem->title;
    })
    
?>

<div class="booking-cars-items-form">

    <?php $form = ActiveForm::begin(['id' =>'form']); ?>
    <?php
                echo $form->field($model, 'asset_item_id')->widget(Select2::classname(), [
                    'data' => $listCars,
                    'options' => ['placeholder' => 'เลือกรถยนต์ ...'],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'dropdownParent' => '#main-modal',
                    ],
                    'pluginEvents' => [
                        "select2:unselect" => "function() { 
                            $('#asset-code').val('')
                        }",
                        "select2:select" => "function() {
                            // console.log($(this).val());
                            $.ajax({
                                type: 'get',
                                url: '".Url::to(['/booking/booking-cars-items/get-img'])."',
                                data: {id: $(this).val()},
                                dataType: 'json',
                                success: function (res) {
                                    console.log(res)
                                    $('#showImg').html(res);
                                    
                                }
                            });
                    }",],
                ])->label('รถยนต์');?>

    <div class="row">
        <div class="col-8">
            <?=$form->field($model, 'car_type')->widget(Select2::classname(), [
                    'data' => [
                        'general' => 'รถใช้งานทั่วไป',
                        'ambulance' => 'รถพยาบาล'
                    ],
                    'options' => ['placeholder' => 'เลือกรถยนต์ ...'],
                   
                    'pluginOptions' => [
                        'allowClear' => true,
                        'dropdownParent' => '#main-modal',
                    ],
                ])->label('ประรถยนต์');
                ?>
            <?= $form->field($model, 'license_plate')->textInput(['maxlength' => true]) ?>
            
            <div class="form-group mt-3 d-flex justify-content-center gap-3">
                <?php echo Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary rounded-pill shadow', 'id' => 'summit']) ?>
                <button type="button" class="btn btn-secondary  rounded-pill shadow" data-bs-dismiss="modal"> <i class="fa-regular fa-circle-xmark"></i> ปิด</button>
            </div>

        </div>
        <div class="col-4">
            <div id="showImg">
                <?=!$model->isNewRecord ? Html::img($model->asset->showImg(),['class' => 'card-img-top p-2 rounded border border-2 border-secondary-subtle']) : ''?>
            </div>
        </div>
    </div>



    <?php ActiveForm::end(); ?>

</div>


<?php
$js = <<< JS
      $('#form').on('beforeSubmit', function (e) {
        var form = \$(this);
        Swal.fire({
        title: "ยืนยัน?",
        text: "บันทึกข้อมูล!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: "ยกเลิก!",
        confirmButtonText: "ใช่, ยืนยัน!"
        }).then((result) => {
        if (result.isConfirmed) {
            
            \$.ajax({
                url: form.attr('action'),
                type: 'post',
                data: form.serialize(),
                dataType: 'json',
                success: async function (response) {
                    form.yiiActiveForm('updateMessages', response, true);
                    if(response.status == 'success') {
                        closeModal()
                        // success()
                        await  \$.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});                               
                    }
                }
            });

        }
        });
        return false;
    });

JS;
$this->registerJS($js, View::POS_END);
?>