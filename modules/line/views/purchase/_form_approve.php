<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\widgets\DetailView;
use kartik\form\ActiveField;
/**
 * @var yii\web\View $this
 */
use yii\helpers\ArrayHelper;
?>

<h3 class="text-white text-center mt-5">ขออนุมัติจัดซื้อจัดจ้าง</h3>
<div class="card approve-content">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">รายการ</th>
                        <th class="text-end">ราคา</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($model->purchase->ListOrderItems() as $item):?>
                    <tr class="">
                        <td class="align-middle">
                            <?php
                            try {
                                $msg = 'จำนวน '.$item->qty.' x '.number_format($item->price, 2).' '.(isset($item->product->data_json['unit']) ? $item->product->data_json['unit'] : 'หน่วย');
                                echo $item->product->AvatarLine($msg);
                            } catch (\Throwable $th) {
                                // throw $th;
                            }
                            ?>
                        </td>
                        <td class="align-middle text-end">
                            <div class="d-flex justify-content-end fw-semibold">
                                <?php
                                try {
                                    echo number_format(($item->qty * $item->price), 2);
                                } catch (\Throwable $th) {
                                    // throw $th;
                                }
                                ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach;?>
                </tbody>
            </table>
        </div>
        <div class="card-fooer d-flex justify-content-center">
            <span class="h6">
                รวมเป็นเงิน <span
                    class="fw-semibold"><?= number_format($model->purchase->calculateVAT()['priceAfterVAT'],2) ?></span>
                บาท
            </span>
        </div>
    </div>
</div>
<?php if ($model->status == 'Pending'): ?>
<div class="d-flex justify-content-center gap-3 mt-4" id="btn-warp">
    <button type="button" class="btn btn-lg btn-primary rounded-pill shadow btn-approve border border-light"
        data-value="Approve" data-text='<i class="fa-regular fa-circle-check"></i> เห็นชอบให้ลาได้'><i
            class="fa-regular fa-circle-check"></i> เห็นชอบ</button>
    <button type="button" class="btn btn-lg btn-danger rounded-pill shadow btn-approve" data-value="Reject"
        data-text='<i class="fa-solid fa-xmark"></i> ไม่เห็นชอบให้ลา'><i class="fa-solid fa-xmark"></i>
        ไม่เห็นชอบ</button>

</div>
<?php endif; ?>

<?php if ($model->status == 'Approve'): ?>

<h1 class="text-center text-white mt-5"><i class="fa-regular fa-circle-check"></i> เห็นชอบ</h1>
<?php endif; ?>

<?php if ($model->status == 'Reject'): ?>
<h1 class="text-center text-white mt-5"><i class="fa-solid fa-xmark"></i> ไม่เห็นชอบ</h1>
<?php endif; ?>

<h1 class="load text-center text-wite mt-5" style="display:none"><i class="fas fa-cog fa-spin"></i> กำลังดำเนินการ</h1>

<?php
    $form = ActiveForm::begin(['id' => 'form'])?>
<div class="d-flex justify-content-center flex-column">
    <div class="d-flex justify-content-center">
        <?php echo $form->field($model, 'status')->hiddenInput()->label(false); ?>
    </div>
    <div class="form-group mt-3 d-flex justify-content-center">

        <?php ActiveForm::end(); ?>
    </div>
</div>

<?php
use app\components\SiteHelper;

$js = <<<JS

    $('#form').on('beforeSubmit', function (e) {
                var form = $('#form');
                \$.ajax({
                    url: form.attr('action'),
                    type: 'post',
                    data: form.serialize(),
                    dataType: 'json',
                    success:  function (response) {
                        // if(response.status == 'success'){
                        //     \$('.btn-approve').hide();
                            \$('.load').hide()
                            Swal.fire({
                            title: "บันทึกสำเร็จ!",
                            text: "ดำเนินการลงความเห็นเรียบร้อยแล้ว",
                            icon: "success"
                            });
                        }
                        });
                        return false;
                    });

            
    \$('.btn-approve').click(async function (e) { 

                e.preventDefault();
                
                var text = \$(this).data('text');
                var title = 'ยืนยัน';
                var text = \$(this).data('text');
                await Swal.fire({
                title: title,
                html: '<h4>'+\$(this).html()+'</h4>',
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "ใช่, ยืนยัน!",
                cancelButtonText: "ยกเลิก",
                }).then(async (result) => {
                    $('#approve-status').val(\$(this).data('value'))
                    if (result.value == true) {
                        console.log('Submit')
                            $('#btn-warp').hide()
                            $('.approve-content').hide()
                            $('.load').show()
                            $('#form').submit()
                          
                     
                        
                    }
                });
                                        
            });
            
      

    JS;
$this->registerJS($js, View::POS_END)
?>