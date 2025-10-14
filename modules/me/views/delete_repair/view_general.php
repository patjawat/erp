<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\widgets\DetailView;
use app\components\UserHelper;

/** @var yii\web\View $this */
/** @var app\modules\helpdesk\models\Repair $model */
$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Repairs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

?>

<style>
#ath_tabs_accordion .tab-style3 .nav-tabs .nav-item a.active {
    border-bottom-color: #4f30f9;
    color: #4f30f9;
}

#ath_tabs_accordion .nav-tabs {
    margin-bottom: 25px;
    border: 0;
}

#ath_tabs_accordion .tab-style3 .nav-tabs li.nav-item a {
    background-color: transparent;
    display: block;
    padding: .5rem 1rem;
    border-top: 0;
    border-left: 0;
    border-right: 0;
    border-bottom: 2px solid rgba(0, 0, 0, 0);
    text-align: center;
    text-transform: uppercase;
    border-radius: 0;
    color: #232323;
}

#ath_tabs_accordion .nav-tabs .nav-link:first-child {
    margin-left: 0 !important;
}
</style>
<?php Pjax::begin(['id' => 'helpdesk-container', 'timeout' => 5000]); ?>
<div class="card">
    <div class="card-body">
        <div class="row">
            
            <div class="col-xl-12 col-lg-12 col-md-1 col-sm-12">

                <table class="table table-striped-columns">
                    <tbody>
                        <?= $this->render('repair_detail', ['repair' => $model]) ?>
                    <tbody>
                </table>
            </div>
            <div class="col-4">
                <?php
                    // try {
                    //     echo Html::img($model->showImg(), ['class' => 'repair-photo object-fit-cover rounded m-auto border border-2 border-secondary-subtle', 'style' => 'max-width:100%;min-width: 320px;']);
                    // } catch (\Throwable $th) {
                    // }
                ?>
            </div>
        </div>

    </div>
    <!-- End col-8 -->
</div>
<!-- End Row -->


    </div>
</div>


<div class="d-flex justify-content-center gap-3">
<?= $model->status == 1 ? Html::a('<i class="fa-solid fa-pen-to-square"></i> แก้ไข', ['/me/repair/update', 'id' => $model->id, 'title' => '<i class="fa-solid fa-circle-minus text-danger"></i> ยกเลิกงานซ่อม'], ['class' => 'btn btn-warning open-modal', 'data' => ['size' => 'modal-lg']]) : '' ?>
<?=Html::a('ติดตาม',['/helpdesk/repair/timeline','id' => $model->id,'title' => '<i class="fa-solid fa-circle-exclamation text-danger"></i> ติดตามงานซ่อม'],['class' => 'btn btn-primary open-modal','data' => ['size' => 'modal-lg']])?>
</div>
<?php
$js = <<< JS

    $("body").on("keypress", ".update-qty", function (e) {
    var keycode = e.keyCode ? e.keyCode : e.which;
    if (keycode == 13) {
        let qty = $(this).val()
        let id = $(this).attr('id')
        console.log(qty);
        
        $.ajax({
            type: "get",
            url: "/helpdesk/stock/update-qty",
            data: {
                'id':id,
                'qty':qty 
            },
            dataType: "json",
            success: function (res) {
                if(res.status == 'error'){
                    Swal.fire({
                    icon: "warning",
                    title: "เกินจำนวน",
                    showConfirmButton: false,
                    timer: 1500,
                });
                }
                $.pjax.reload({ container:res.container, history:false,replace: false,timeout: false});
            }
            });
        }
    });


    $("body").on("click", ".update-cart", function (e) {
        e.preventDefault();
        $.ajax({
            type: "get",
            url: $(this).attr('href'),
            data: {},
            dataType: "json",
            success: function (res) {
                if(res.status == 'error'){
                    Swal.fire({
                    icon: "warning",
                    title: "เกินจำนวน",
                    showConfirmButton: false,
                    timer: 1500,
                });
                }
                $.pjax.reload({ container:res.container, history:false,replace: false,timeout: false});
            }
        });
       
        
    });
    

    \$("body").on("click", ".delete-item-cart", function (e) {
        e.preventDefault();
        \$.ajax({
            type: "get",
            url: \$(this).attr('href'),
            dataType: "json",
            success: function (res) {
                \$.pjax.reload({ container:res.container, history:false,replace: false,timeout: false});
            }
        });
    });
    $("body").on("click", "#checkout", function (e) {
    e.preventDefault();

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
                $.ajax({
                    type: "post",
                    url: $(this).data("url"),
                    data: {id:$(this).data("id")},
                    dataType: "json",
                    success: function (res) {
                        if (res.status == "error") {
                                Swal.fire({
                                    title: "แจ้งเตือน!",
                                    text: res.msg,
                                    icon: "warning"
                                    });
                                }
                                if (res.status == "success") {
                        $.pjax.reload({
                            container: res.container,
                            history: false,
                            replace: false,
                            timeout: false,
                        });
                    }
                    },
                error: function (xhr, status, error) {
                    console.error("เกิดข้อผิดพลาดในการเรียก AJAX:", error);
                },
            });
                    }
                });
                


    });


      

    \$("body").on("click", ".accept-job", async function (e) {
      e.preventDefault();
      var url = \$(this).attr("href");
      await Swal.fire({
        title: "ยืนยันรับเรื่อง?",
        text: "รับเรื่องเพื่อบันทึกงานซ่อมต่อไป!",
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
                console.log(response);
                
              if (response.status == "success") {
                    $.pjax.reload({
                    container: response.container,
                    history: false,
                    url: response.url,
                    });
                    success("ดำเนินการลบสำเร็จ!.");
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