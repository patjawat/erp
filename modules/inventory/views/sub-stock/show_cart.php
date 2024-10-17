
<div id="viewSubCart"></div>
<?php

use yii\helpers\Url;
use yii\web\View;
$viewSubCartUrl = Url::to(['/inventory/sub-stock/view-cart']);
$js = <<< JS
ViewSubCart();
async function ViewSubCart()
    {
    await $.ajax({
        type: "get",
        url: "$viewSubCartUrl",
        dataType: "json",
        cache: false,
        success: function (res) {
            $('#viewSubCart').html(res.content)
            $('.countSubItem').html(res.countItem)
            if(res.countItem < 1){
                $("#main-modal").modal("hide");
            }
            console.log(res.countItem);
            
        }
    });
    }

    function updateCart(){
        $.ajax({
            type: "get",
            url: $(this).attr('href'),
            data: {},
            dataType: "json",
            cache: false,
            success: function (res) {
                if(res.status == 'error'){
                    Swal.fire({
                    icon: "warning",
                    title: "เกินจำนวน",
                    showConfirmButton: false,
                    timer: 1500,
                });
                }
                ViewSubCart()
                // $.pjax.reload({ container:'#viewCart-conteiner', history:false,replace: false,timeout: false});
                // ViewSubCart();
            }
        });
    }



$("body").on("keypress", ".update-qty", function (e) {
    var keycode = e.keyCode ? e.keyCode : e.which;
    if (keycode == 13) {
        let qty = $(this).val()
        let id = $(this).attr('id')
        console.log(qty);
        
        $.ajax({
            type: "get",
            url: "/inventory/sub-stock/update-qty",
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
                ViewSubCart();
                $.pjax.reload({ container:res.container, history:false,replace: false,timeout: false});
            }
        });
    }
});


    $("body").on("click", ".update-sub-cart", function (e) {
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
                // success()
                ViewSubCart();
                $.pjax.reload({ container:res.container, history:false,replace: false,timeout: false});
            }
        });
       
        
    });

    $("body").on("click", ".delete-item-cart", function (e) {
    e.preventDefault();
    $.ajax({
        type: "get",
        url: $(this).attr('href'),
        dataType: "json",
        success: function (res) {
            ViewSubCart();
            $.pjax.reload({ container:res.container, history:false,replace: false,timeout: false});
        }
    });
});
JS;
$this->registerJS($js,View::POS_END);

?>