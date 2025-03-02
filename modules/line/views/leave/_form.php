<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
// use yii\jui\DatePicker;
use yii\web\JsExpression;
use app\models\Categorise;
// use kartik\date\DatePicker;
// use kartik\date\DatePicker;
use kartik\date\DatePicker;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
use kartik\sortable\Sortable;
use app\components\SiteHelper;
// use karatae99\datepicker\DatePicker;
use app\components\UserHelper;
use kartik\widgets\ActiveForm;
use app\widgets\FlatpickrWidget;
use yii\web\HtmlResponseFormatter;
use app\modules\hr\models\Employees;
use app\components\ApproveHelper;
use iamsaint\datetimepicker\Datetimepicker;
use app\widgets\Flatpickr\FlatpickrBuddhistWidget;
$totalNotification = ApproveHelper::Info()['total'];
$site = SiteHelper::getInfo();
$me = UserHelper::GetEmployee();


/** @var yii\web\View $this */
/** @var app\modules\lm\models\Leave $model */
/** @var yii\widgets\ActiveForm $form */
$formatJs = <<< 'JS'
    var formatRepo = function (repo) {
        if (repo.loading) {
            return repo.avatar;
        }
        // console.log(repo);
        var markup =
    '<div class="row">' +
        '<div class="col-12">' +
            '<span>' + repo.avatar + '</span>' +
        '</div>' +
    '</div>';
        if (repo.description) {
          markup += '<p>' + repo.avatar + '</p>';
        }
        return '<div style="overflow:hidden;">' + markup + '</div>';
    };
    var formatRepoSelection = function (repo) {
        return repo.avatar || repo.avatar;
    }
    JS;

// Register the formatting script
$this->registerJs($formatJs, View::POS_HEAD);

// script to parse the results into the format expected by Select2
$resultsJs = <<< JS
    function (data, params) {
        params.page = params.page || 1;
        return {
            results: data.results,
            pagination: {
                more: (params.page * 30) < data.total_count
            }
        };
    }
    JS;


?>
<style>
:not(.form-floating)>.input-lg.select2-container--krajee-bs5 .select2-selection--single,
:not(.form-floating)>.input-group-lg .select2-container--krajee-bs5 .select2-selection--single {
    height: calc(2.875rem + 2px);
    padding: 4px;
    font-size: 1.0rem;
    line-height: 1.5;
    border-radius: .3rem;
}

.select2-container--krajee-bs5 .select2-results__option--highlighted[aria-selected] {
    background-color: #e5e5e5;
    color: #000;
}

.form-step {
  display: none;
  opacity: 0;
  transition: opacity 0.5s ease-in-out;
}

.form-step.active {
  display: block;
  opacity: 1;
  transition: opacity 0.5s ease-in-out;
}

.progress-bar {
  transition: width 0.5s ease-in-out;
}

/* .form-control {
    background-color:#e2e4e9 !important;
} */

</style>
<!-- <div class="row d-flex justify-content-center">
<div class="col-8">
<div class="card">
    <div class="card-body"> -->

    <?php $form = ActiveForm::begin([
    'id' => 'form-elave',
    'enableAjaxValidation' => true,  // เปิดการใช้งาน AjaxValidation
    'validationUrl' => ['/hr/leave/create-validator']
]); ?>

    <div class="page-title-box-line mb-5">
        <div class="d-flex justify-content-between align-items-center mt-5">
            <div class="page-title-line">

                <div class="d-flex gap-2">
                    <?=Html::img($site['logo'], ['class' => 'avatar avatar-md me-0 mt-2'])?>

                    <div class="avatar-detail">
                        <h5 class="mb-0 text-white text-truncate mt-3"><?php echo $site['company_name']?></h5>
                        <p class="text-white mb-0 fs-13">ERP Hospital</p>
                    </div>
                </div>
              
            </div>
            <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#staticBackdrop"
                aria-controls="staticBackdrop">
                <i class="fa-solid fa-bars fs-3"></i>
            </button>

        </div>
    </div>
    


    <div class="container mt-5">
        <div class="card">
            <div class="card-body">
 <!-- Progress Bar -->
 <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="progress mb-4" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="25" aria-labelledby="form-progress">
        <div class="progress-bar" id="form-progress" style="width: 25%;"></div>
      </div>
    </div>
  </div>

  <div class="row justify-content-center">
    <div class="col-md-6">
      <!-- Multi-Step Form -->
      <form id="multiStepForm">
        
        <!-- Step 1: Personal Details -->
        <div class="form-step active">
            <?= $form->field($model, 'date_start')->textInput(['placeholder' => 'เลือกวันที่','class' => 'form-control form-control-lg rounded-pill border-0 bg-secondary bg-opacity-10']); ?>
                <?= $form->field($model, 'date_end')->textInput(['placeholder' => 'เลือกวันที่','class' => 'form-control form-control-lg rounded-pill border-0 bg-secondary bg-opacity-10']); ?>
          <button type="button" class="btn btn-primary next-step">Next</button>
        </div>

        <!-- Step 2: User Type Selection -->
        <div class="form-step">
          <div class="mb-3">
            <label for="userType" class="form-label">Are you an individual or a business?</label>
            <select id="userType" class="form-select" required>
              <option value="">Select...</option>
              <option value="individual">Individual</option>
              <option value="business">Business</option>
            </select>
          </div>
          <button type="button" class="btn btn-secondary prev-step">Previous</button>
          <button type="button" class="btn btn-primary next-step">Next</button>
        </div>

        <!-- Step 3: Business Details -->
        <div class="form-step">
          <h4>Business Details</h4>
          <div class="mb-3">
            <label for="businessName" class="form-label">Business Name</label>
            <input type="text" class="form-control" id="businessName">
          </div>
          <div class="mb-3">
            <label for="businessEmail" class="form-label">Business Email</label>
            <input type="email" class="form-control" id="businessEmail">
          </div>
          <button type="button" class="btn btn-secondary prev-step">Previous</button>
          <button type="button" class="btn btn-primary next-step">Next</button>
        </div>

        <!-- Step 4: Review and Submit -->
        <div class="form-step">
          <h4>Review Your Information</h4>
          <div class="review-info">
            <p><strong>First Name:</strong> <span id="reviewFirstName"></span></p>
            <p><strong>Last Name:</strong> <span id="reviewLastName"></span></p>
            <p><strong>Email:</strong> <span id="reviewEmail"></span></p>
          </div>
          <button type="button" class="btn btn-secondary prev-step">Previous</button>
          <button type="submit" class="btn btn-success">Submit</button>
        </div>

      </form>
    </div>
  </div>
  </div>
        </div>
        
</div>

<script>
//   const steps = document.querySelectorAll(".form-step");
//   const nextBtns = document.querySelectorAll(".next-step");
//   const prevBtns = document.querySelectorAll(".prev-step");
//   const progressBar = document.querySelector(".progress-bar");
//   let currentStep = 0;

//   nextBtns.forEach((button) => {
//     button.addEventListener("click", () => {
//       const currentInputs = steps[currentStep].querySelectorAll("input");
//       let valid = true;

//       currentInputs.forEach((input) => {
//         if (!input.checkValidity()) {
//           input.classList.add("is-invalid");
//           valid = false;
//         } else {
//           input.classList.remove("is-invalid");
//           input.classList.add("is-valid");
//         }
//       });

//       if (valid) {
//         steps[currentStep].classList.remove("active");
//         currentStep++;
//         steps[currentStep].classList.add("active");
//         updateProgressBar();
//       }
//     });
//   });

//   prevBtns.forEach((button) => {
//     button.addEventListener("click", () => {
//       steps[currentStep].classList.remove("active");
//       currentStep--;
//       steps[currentStep].classList.add("active");
//       updateProgressBar();
//     });
//   });

//   function updateProgressBar() {
//     const progress = ((currentStep + 1) / steps.length) * 100;
//     progressBar.style.width = `${progress}%`;
//     progressBar.setAttribute("aria-valuenow", progress);
//   }
</script>







    
<div class="container">

    <div class="card">
        <div class="card-body">
            




<div class="row d-flex justify-content-center">
    <div class="col-lg-12 col-md-12">
        <!-- Row -->
        <div class="row">
            <div class="col-12">
              
</div>


<button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasBottom" aria-controls="offcanvasBottom">ประเภทการลา</button>

                <?php
                 $form->field($model, 'leave_type_id')->widget(Select2::classname(), [
                    'data' => $model->listLeavetype(),
                    'options' => ['placeholder' => 'เลือกประเภทการลา ...'],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ])->label('ประเภท');
                ?>
               
                <?php echo $form->field($model, 'data_json[reason]')->textArea(['style' => 'height:130px;'])->label('เหตุผล/เนื่องจาก') ?>

                <div class="d-flex justify-content-between  align-middle align-items-center bg-primary bg-opacity-10  pt-2 px-3 rounded mb-3">
                <?php echo $model->total_days ?></span></h6>
                <div>
                    <h6>สรุปวันลา : <span class="cal-days text-black bg-danger-subtle badge rounded-pill fw-ligh fs-13"></h6>

                    <!-- <ul>
                        <li class="day_normal">วันเสาร์-อาทิตย์ : <span class="cal-satsunDays text-black bg-danger-subtle badge rounded-pill fw-ligh fs-13">0</span></li>
                        <li class="day_normal">วันหยุดนักขัตฤกษ์ : <span class="cal-holiday text-black bg-danger-subtle badge rounded-pill fw-ligh fs-13">0</span></li>
                        <li class="day_off">วัน OFF : <span class="cal-holiday_me text-black bg-danger-subtle badge rounded-pill fw-ligh fs-13">0</span>
                    </ul> -->
                    </div>
                </div>

            </div>
            <div class="col-6">
            <div class="d-flex justify-content-between gap-3">
            <?php echo $form->field($model, 'data_json[phone]')->textInput()->label('เบอร์โทรติดต่อ') ?>    
            <?php
                    echo $form->field($model, 'data_json[location]')->widget(Select2::classname(), [
                    'data' => [
                        'ภายในจังหวัด' => 'ภายในจังหวัด',
                        'ต่างจังหวัด' => 'ต่างจังหวัด',
                        'ต่างประเทศ' => 'ต่างประเทศ',
                    ],
                    'options' => ['placeholder' => 'เลือกสถานที่ไป ...'],
                    'pluginOptions' => [
                        'allowClear' => true,
                        // 'width' => '100%',
                    ],
                    ])->label('สถานที่ไป');
                    ?>
                    </div>
            <?php echo $form->field($model, 'data_json[address]')->textArea(['style' => 'height:78px;'])->label('ระหว่างลาติดต่อ') ?>
                <?php
                try {
                    $initEmployee = Employees::find()->where(['id' => $model->data_json['leave_work_send_id']])->one()->getAvatar(false);
                } catch (\Throwable $th) {
                    $initEmployee = '';
                }
                echo $form->field($model, 'data_json[leave_work_send_id]')->widget(Select2::classname(), [
                    'initValueText' => $initEmployee,
                    'options' => ['placeholder' => 'เลือกรายการ...'],
                    'size' => Select2::LARGE,
                    'pluginEvents' => [
                        'select2:unselect' => 'function() {
                            $("#leave-data_json-leave_work_send").val("")
                            }',
                        'select2:select' => 'function() {
                                    var fullname = $(this).select2("data")[0].fullname;
                                    $("#leave-data_json-leave_work_send").val(fullname)
                            }',
                    ],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'minimumInputLength' => 1,
                        'ajax' => [
                            'url' => Url::to(['/depdrop/employee-by-id']),
                            'dataType' => 'json',
                            'delay' => 250,
                            'data' => new JsExpression('function(params) { return {q:params.term, page: params.page}; }'),
                            'processResults' => new JsExpression($resultsJs),
                            'cache' => true,
                        ],
                        'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                        'templateSelection' => new JsExpression('function (item) { return item.text; }'),
                        'templateResult' => new JsExpression('formatRepo'),
                    ],
                ])->label('มอบหมายงานให้')
                ?>
                <?php echo $this->render('@app/modules/hr/views/leave/approve', ['form' => $form, 'model' => $model]) ?>
            </div>
        </div>
    </div>
</div>
       

<?php // echo $this->render('summary', ['model' => $model]) ?>

<?php echo $form->field($model, 'ref')->hiddenInput()->label(false) ?>
<?php echo $form->field($model, 'data_json[leave_work_send]')->hiddenInput()->label(false) ?>
<?php echo $form->field($model, 'total_days')->hiddenInput()->label(false) ?>
<?php echo $form->field($model, 'data_json[title]')->hiddenInput()->label(false) ?>
<?php echo $form->field($model, 'data_json[director]')->hiddenInput()->label(false) ?>
<?php echo $form->field($model, 'data_json[director_fullname]')->hiddenInput()->label(false) ?>


<div class="form-group mt-3 d-flex justify-content-center gap-3">
    <?php echo Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary rounded-pill shadow', 'id' => 'summit']) ?>
    <button type="button" class="btn btn-secondary  rounded-pill shadow" data-bs-dismiss="modal"><i class="fa-regular fa-circle-xmark"></i> ปิด</button>
</div>
</div>
</div>



<?php ActiveForm::end(); ?>



<div class="offcanvas offcanvas-bottom" tabindex="-1" id="offcanvasBottom" aria-labelledby="offcanvasBottomLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="offcanvasBottomLabel">เลือกประเภทการลา</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body small">
    ...
  </div>
</div>



</div>
    </div>
        
</div>

    

<!-- </div>
</div>
</div>
</div> -->

<?php
$calDaysUrl = Url::to(['/hr/leave/cal-days']);
$js = <<< JS

    const steps = document.querySelectorAll(".form-step");
    const nextBtns = document.querySelectorAll(".next-step");
    const prevBtns = document.querySelectorAll(".prev-step");
    const progressBar = document.querySelector(".progress-bar");

    let currentStep = 0;

    // Initialize dataLayer if not already initialized
    window.dataLayer = window.dataLayer || [];

    // Function to update progress bar
    function updateProgressBar() {
    const progress = ((currentStep + 1) / steps.length) * 100;
    progressBar.style.width = progress+'%';
    progressBar.setAttribute("aria-valuenow", progress);
    }

    // Function to show the current step and send dataLayer event
    function showCurrentStep() {
    steps.forEach((step, index) => {
        if (index === currentStep) {
        step.classList.add("active");
        } else {
        step.classList.remove("active");
        }
    });
    
    // Fire dataLayer event for each step
    window.dataLayer.push({
        event: 'form_step_'+(currentStep + 1)
    });

    updateProgressBar();
    }

    // Initialize first step visibility
    showCurrentStep();

    // Next button event listeners
    nextBtns.forEach((button) => {
    button.addEventListener("click", () => {
        const currentInputs = steps[currentStep].querySelectorAll("input, select");
        let valid = true;

        // Validate the current inputs
        currentInputs.forEach((input) => {
        if (!input.checkValidity()) {
            input.classList.add("is-invalid");
            valid = false;
        } else {
            input.classList.remove("is-invalid");
            input.classList.add("is-valid");
        }
        });

        if (valid) {
        steps[currentStep].classList.remove("active");
        currentStep++;

        // Populate review fields if on the final step
        if (currentStep === steps.length - 1) {
            document.getElementById('reviewFirstName').textContent = document.getElementById('firstName').value;
            document.getElementById('reviewLastName').textContent = document.getElementById('lastName').value;
            document.getElementById('reviewEmail').textContent = document.getElementById('businessEmail').value || document.getElementById('email').value;
        }

        showCurrentStep();
        }
    });
    });

    // Previous button event listeners
    prevBtns.forEach((button) => {
    button.addEventListener("click", () => {
        currentStep--;
        showCurrentStep();
    });
    });

    // Add event listener for form submission to fire the generate_lead event
    document.getElementById("multiStepForm").addEventListener("submit", function (e) {
    e.preventDefault();  // Prevent the default form submission for this example
    
    // Fire generate_lead event in the dataLayer
    window.dataLayer.push({
        'event': 'generate_lead'
    });

    // Continue with form submission logic or AJAX call, if needed
    // Example: alert('Form submitted successfully!');
    });



      thaiDatepicker('#leave-date_start,#leave-date_end')


      \$('#form-elave').on('beforeSubmit', function (e) {
        var form = \$(this);
        console.log('Submit');
        if($('#leave-total_days').val() <= 0){
            alert('no');
            return false;
        }
        
        Swal.fire({
        title: "ยืนยัน?",
        text: "บันทึกขออนุมัติการลา!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: "ยกเลิก!",
        confirmButtonText: "ใช่, ยืนยัน!"
        }).then((result) => {
        if (result.isConfirmed) {
            beforLoadModal()
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

        $('#leave-date_start').on('change', function() {
            var selectedDate = $(this).val();
            calDays(selectedDate);
        });

        $('#leave-date_end').on('change', function() {
            var selectedDate = \$(this).val();
            calDays(selectedDate);
        });

        $("#leave-data_json-auto").change(function() {
            //ไม่รวมวันหยุด Auto
            if(this.checked) {
                calDays()
            }else{
                calDays()
            }
        });
        
        // \$("#leave-on_holidays").change(function() {
        //     //ไม่รวมวันหยุด Auto
        //     if(this.checked) {
        //         $(this).val(1)
        //         calDays()
        //     }else{
        //         $(this).val(0)
        //         calDays()
        //     }
        // });


    function calDays()
    {
            \$.ajax({
                type: "get",
                url: "$calDaysUrl",
                data:{
                    date_start:\$('#leave-date_start').val(),
                    date_end:\$('#leave-date_end').val(),
                    date_start_type:\$('#leave-data_json-date_start_type').val(),
                    date_end_type:\$('#leave-data_json-date_end_type').val(),
                    on_holidays:$('#leave-on_holidays').val()
                    
                },
                dataType: "json",
                success: function (res) {
                    console.log(\$('#leave-data_json-date_start_type').val());
                   \$('.cal-days').html(res.total)
                   \$('#leave-total_days').val(res.total)
                   if(res.isDayOff >= 1){
                    $('.day_normal').hide()
                    $('.day_off').show()
                }
                
                if(res.isDayOff == 0){
                    $('.day_off').hide()
                    $('.day_normal').show()
                   }
                    
                }
            });
        }


    \$("input[name='Leave[data_json][leave_type]']").on('change', function() {
            // ดึงค่าที่ถูกเลือก
            var selectedValue = \$("input[name='Leave[data_json][leave_type]']:checked").val();
            console.log(selectedValue); // แสดงค่าใน console
            if(selectedValue == "เต็มวัน"){
                \$('#leave-leave_time_type').val(1);
            }else{
                \$('#leave-leave_time_type').val(0.5);
            }
        });



    JS;
$this->registerJS($js, View::POS_END);

?>

