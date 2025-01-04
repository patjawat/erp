<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use kartik\widgets\Select2;
use kartik\widgets\ActiveForm;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;
use iamsaint\datetimepicker\Datetimepicker;

/** @var yii\web\View $this */
/** @var app\modules\lm\models\LeaveSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>
<style>
.offcanvas-footer {
    padding: 1rem 1rem;
    border-top: 1px solid #dee2e6;
}
</style>
<?php $form = ActiveForm::begin([
    'action' => ['report'],
    'method' => 'get',
    'options' => [
        'data-pjax' => 1
    ],
]); ?>

<div class="d-flex gap-2">

    <?php //echo $form->field($model, 'q')->textInput(['placeholder' => 'ระบุคำค้นหา...'])->label('คำค้นหา') ?>
    <?php echo $form->field($model, 'data_json[export]')->hiddenInput()->label(false) ?>
   
    <?php
      $url = Url::to(['/depdrop/employee-by-id']);
      try {
          $initEmployee = Employees::find()->where(['id' => $model->emp_id])->one()->fullname;
      } catch (\Throwable $th) {
          $initEmployee = '';
      }
      echo $form->field($model, 'emp_id')->widget(Select2::classname(), [
          'initValueText' => $initEmployee,
          'options' => ['placeholder' => 'เลือกบุคลากร ...'],
          'pluginOptions' => [
              'width' => '230px',
              'allowClear' => true,
              'minimumInputLength' => 1,
              'language' => [
                  'errorLoading' => new JsExpression("function () { return 'Waiting for results...'; }"),
              ],
              'ajax' => [
                  'url' => $url,
                  'dataType' => 'json',
                  'data' => new JsExpression('function(params) { return {q:params.term}; }')
              ],
              'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
              'templateResult' => new JsExpression('function(city) { return city.fullname; }'),
              'templateSelection' => new JsExpression('function (city) { return city.fullname; }'),
          ],
          'pluginEvents' => [
              'select2:select' => 'function(result) { 
              $(this).submit()
              }',
              'select2:unselecting' => 'function() {
                  $(this).submit()
              }',
          ]
      ])->label('บุคลากร');
        ?>
         <?php
        echo $form->field($model, 'thai_year')->widget(Select2::classname(), [
            'data' => $model->ListThaiYear(),
            'options' => ['placeholder' => 'ปีงบประมาณ'],
            'pluginOptions' => [
                'allowClear' => true,
                'width' => '120px',
            ],
            'pluginEvents' => [
                'select2:select' => 'function(result) { 
                        $(this).submit()
                        }',
                'select2:unselecting' => "function() {
                             $(this).submit()
                            $('#leavesearch-date_start').val('__/__/____');
                            $('#leavesearch-date_end').val('__/__/____');
                        }",
            ]
        ])->label('ปีงบประมาณ');
        ?>

<div class="d-flex justify-content-between gap-2">
                <?php
                    echo $form->field($model, 'date_start')->widget(Datetimepicker::className(), [
                        'options' => [
                            'timepicker' => false,
                            'datepicker' => true,
                            'mask' => '99/99/9999',
                            'lang' => 'th',
                            'yearOffset' => 543,
                            'format' => 'd/m/Y',
                        ],
                    ])->label('ตั้งแต่วันที่');
                    ?>
                <?php
                    echo $form->field($model, 'date_end')->widget(Datetimepicker::className(), [
                        'options' => [
                            'timepicker' => false,
                            'datepicker' => true,
                            'mask' => '99/99/9999',
                            'lang' => 'th',
                            'yearOffset' => 543,
                            'format' => 'd/m/Y',
                        ],
                    ])->label('ถึงวันที่');
                    ?>
            </div>

    <div class="d-flex flex-row mb-3 mt-4">
        <?php echo Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i> ค้นหา', ['class' => 'btn btn-primary']) ?>
    </div>



    <!-- Offcanvas -->
    <div class="offcanvas offcanvas-end" data-bs-backdrop="static" tabindex="-1" id="offcanvasExample"
        aria-labelledby="offcanvasExampleLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasExampleLabel">กรองเพิ่มเติม</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body position-relative">
            <?php echo $form->field($model, 'q_department')->widget(\kartik\tree\TreeViewInput::className(), [
                    'name' => 'department',
                    'id' => 'treeID',
                    'query' => Organization::find()->addOrderBy('root, lft'),
                    'value' => 1,
                    'headingOptions' => ['label' => 'รายชื่อหน่วยงาน'],
                    'rootOptions' => ['label' => '<i class="fa fa-building"></i>'],
                    'fontAwesome' => true,
                    'asDropdown' => true,
                    'multiple' => false,
                    'options' => ['disabled' => false, 'allowClear' => true, 'class' => 'close'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ])->label('หน่วยงานภายในตามโครงสร้าง'); ?>


           

            <div class="offcanvas-footer">
            <?php echo Html::submitButton(
                        '<i class="fa-solid fa-magnifying-glass"></i> ค้นหา',
                        [
                            'class' => 'btn btn-primary',
                            'data-bs-backdrop' => 'static',
                            'tabindex' => '-1',
                            'id' => 'offcanvasExample',
                            'aria-labelledby' => 'offcanvasExampleLabel',
                        ]
                    ); ?>
            </div>

        </div>
    </div>


    <div class="mb-3 mt-4">
        <!-- Trigger button -->
        <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasExample"
            aria-controls="offcanvasExample">
            <i class="fa-solid fa-filter"></i> เพิ่มเติม
        </button>


    </div>

</div>

<?php ActiveForm::end(); ?>


<?php

$js = <<< JS

        var thaiYear = function (ct) {
            var leap=3;
            var dayWeek=["พฤ.", "ศ.", "ส.", "อา.","จ.", "อ.", "พ."];
            if(ct){
                var yearL=new Date(ct).getFullYear()-543;
                leap=(((yearL % 4 == 0) && (yearL % 100 != 0)) || (yearL % 400 == 0))?2:3;
                if(leap==2){
                    dayWeek=["ศ.", "ส.", "อา.", "จ.","อ.", "พ.", "พฤ."];
                }
            }
            this.setOptions({
                i18n:{ th:{dayOfWeek:dayWeek}},dayOfWeekStart:leap,
            })
        };

        \$("#leavesearch-date_start").datetimepicker({
            timepicker:false,
            format:'d/m/Y',  // กำหนดรูปแบบวันที่ ที่ใช้ เป็น 00-00-0000
            lang:'th',  // แสดงภาษาไทย
            onChangeMonth:thaiYear,
            onShow:thaiYear,
            yearOffset:543,  // ใช้ปี พ.ศ. บวก 543 เพิ่มเข้าไปในปี ค.ศ
            closeOnDateSelect:true,
        });

        \$("#leavesearch-date_end").datetimepicker({
            timepicker:false,
            format:'d/m/Y',  // กำหนดรูปแบบวันที่ ที่ใช้ เป็น 00-00-0000
            lang:'th',  // แสดงภาษาไทย
            onChangeMonth:thaiYear,
            onShow:thaiYear,
            yearOffset:543,  // ใช้ปี พ.ศ. บวก 543 เพิ่มเข้าไปในปี ค.ศ
            closeOnDateSelect:true,
        });

    JS;
$this->registerJS($js, View::POS_END);

?>