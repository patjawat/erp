
$("body").on("input", ".qty", function (e) {
    const maxlot = parseInt($(this).data('maxlot')); 
    // ลบตัวอักษรที่ไม่ใช่ตัวเลขออก
    // this.value.replace(/[^0-9]/g, '');
    this.value = this.value.replace(/[^0-9]/g, '');
    let = value = $(this).val();

      if (parseInt($(this).val()) > maxlot) {
        $(this).val(maxlot);
      }
});

$("body").on("click", ".minus", async function (e) {
    quantityField = $(this).next();
    var lotQty = $(this).data('lot_qty');
    var id = $(this).data('id');
    console.log(id);
  
  if (quantityField.val() != 0) {
    var setVal = parseInt(quantityField.val(), 10) - 1;
            if(setVal > lotQty){
                Swal.fire({icon: "warning",title: "เกินจำนวน",showConfirmButton: false,timer: 1500});
            }else{
               await quantityField.val(parseInt(setVal));   
               await $.ajax({
                    type: "get",
                    url: "/inventory/stock-order/update-qty",
                    data: {
                        id:id,
                        qty:setVal
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
                        if(res.status == 'success')
                        {
                           
                        }
                    }
                });
            }   
        }
  
});

$("body").on("click", ".plus", async function (e) {
    quantityField = $(this).prev();
    var lotQty = $(this).data('lot_qty');
    var total = $(this).data('total');
    console.log(total);
    
    var id = $(this).data('id');
    var setVal = parseInt(quantityField.val(), 10) + 1;
    if(setVal > lotQty){
        Swal.fire({
                    icon: "warning",
                    title: "เกินจำนวน",
                    showConfirmButton: false,
                    timer: 1500,
                });
    }else{
       await quantityField.val(parseInt(setVal)); 
       await $.ajax({
            type: "get",
            url: "/inventory/stock-order/update-qty",
            data: {
                id:id,
                qty:setVal
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
                if(res.status == 'success')
                {
                     
                }
            }
        });
    }
});


$("body").on("keypress", ".qty", function (e) {
    var keycode = e.keyCode ? e.keyCode : e.which;
    if (keycode == 13) {
        let qty = $(this).val()
        let id = $(this).attr('id')
        console.log(qty);
        $.ajax({
            type: "get",
            url: "/inventory/stock-order/update-qty",
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
                location.reload();
                //   $.pjax.reload({container:res.container, history:false});
            }
        });
    }
});


$("body").on("click", ".update-qty", function (e) {
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
                if(res.status == 'success')
                {
                    $('#'+res.data.id).val(res.data.qty)
                    console.log(res.data.qty);
                    
                }


                //   $.pjax.reload({container:'#inventory', history:false});
            }
        });
        
    });


    $("body").on("click", ".checkout", async function (e) {
    e.preventDefault();

  await Swal.fire({
    title: "ยืนยัน?",
    text: "บันทึกสั่งจ่ายรายการนี้!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "ใช่, ยืนยัน!",
    cancelButtonText: "ยกเลิก",
  }).then(async (result) => {
    if (result.value == true) {
      await $.ajax({
        type: "post",
        url: $(this).attr('href'),
        dataType: "json",
        success: async function (response) {

          if (response.status == "success") {
            // await  $.pjax.reload({container:response.container, history:false,url:response.url});
            success("บันสำเร็จ!.");
          }

          if (response.status == "error") {
              $.pjax.reload({container:response.container, history:false});
            Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    text: response.message,
                    footer: 'เกิดข้อผิดพลาด'
                    });
          }
          
        },
      });
    }
  });

  });


$('.confirm-order').click(async function (e) { 
    e.preventDefault();

  await Swal.fire({
    title: "ยืนยัน?",
    text: "บันทึกรายการนี้!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "ใช่, ยืนยัน!",
    cancelButtonText: "ยกเลิก",
        }).then(async (result) => {
            if (result.value == true) {
            await $.ajax({
                type: "post",
                url: $(this).attr('href'),
                dataType: "json",
                success: async function (response) {
                if (response.status == "success") {
                    await  $.pjax.reload({container:response.container, history:false,url:response.url});
                    success("บันสำเร็จ!.");
                }
                },
            });
            }
        });

  });


  $("body").on("click", ".copy-item", function (e) {
    e.preventDefault();

        //  Swal.fire({
        //     title: "ยืนยัน?",
        //     text: "บันทึกสั่งจ่ายรายการนี้!",
        //     icon: "warning",
        //     showCancelButton: true,
        //     confirmButtonColor: "#3085d6",
        //     cancelButtonColor: "#d33",
        //     confirmButtonText: "ใช่, ยืนยัน!",
        //     cancelButtonText: "ยกเลิก",
        // }).then( (result) => {
        //     if (result.value == true) {
             $.ajax({
                type: "get",
                url: $(this).attr('href'),
                dataType: "json",
                success: function (res) {
                    console.log(res);
                    
                    if (res.status == "error") {
                            Swal.fire({
                                icon: "error",
                                title: "Oops...",
                                text: res.message,
                                footer: 'เกิดข้อผิดพลาด'
                            });
                        }

                  if (res.status == "success") {
                    location.reload();
                    //   $.pjax.reload({container:res.container, history:false,url:res.url});
                    // success("บันสำเร็จ!.");
                  }
                },
            });
            // }
        // });

  });

