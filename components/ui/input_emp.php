
<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use kartik\widgets\Select2;
use kartik\widgets\ActiveForm;
use app\widgets\FlatpickrWidget;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;

$feild = $fieldName ?? 'emp_id';
$showEmp = $showEmp ?? true;
$label = $label ?? 'บุคลากร';
?>

<style>
/* :not(.form-floating)>.input-lg.select2-container--krajee-bs5 .select2-selection--single,
:not(.form-floating)>.input-group-lg .select2-container--krajee-bs5 .select2-selection--single {
    height: calc(2.875rem + 2px);
    padding: 4px;
    font-size: 1.0rem;
    line-height: 1.5;
    border-radius: .3rem;
} */

.select2-container--krajee-bs5 .select2-results__option--highlighted[aria-selected] {
    background-color: #e5e5e5;
    color: #000;
}

.avatar-form .select2-container--krajee-bs5 .select2-selection--single {
    height: calc(2.25rem + 2px);
    line-height: 1.5;
    padding: 6px;
}

.avatar-form .avatar {
    height: 1.9rem !important;
    width: 1.9rem !important;
}

.avatar-form .select2-container--krajee-bs5 .select2-selection--single {
    height: calc(2.25rem + 2px);
    line-height: 1.5;
    padding: 0.4rem 0.1rem 0.5rem 0.1rem;
}
</style>
<div class="avatar-form">

<?php
            $url = Url::to(['/depdrop/employee-by-id']);
            $employee = Employees::find()->where(['id' => $model->$feild])->one();
            $initEmployee = empty($model->$feild) ? '' : Employees::findOne($model->$feild)->getAvatar(false);//กำหนดค่าเริ่มต้น
            
            echo $form->field($model, $feild)->widget(Select2::classname(), [
                'initValueText' => $initEmployee,
                // 'size' => Select2::,
                'options' => ['placeholder' => 'เลือกบุคลากร ...'],
                'pluginOptions'=>[
                    'dropdownParent' => (isset($modal) ? '#main-modal' : false),
                    'width' => '280px',
                    'allowClear'=>true,
                    'minimumInputLength'=>1,//ต้องพิมพ์อย่างน้อย 3 อักษร ajax จึงจะทำงาน
                    'ajax'=>[
                        'url'=>$url,
                        'dataType'=>'json',//รูปแบบการอ่านคือ json
                        'data'=>new JsExpression('function(params) { return {q:params.term};}')
                    ],
                    'escapeMarkup'=>new JsExpression('function(markup) { return markup;}'),
                    'templateResult' => new JsExpression('function(emp) { return emp && emp.text ? emp.text : "กำลังค้นหา..."; }'),
                    'templateSelection'=>new JsExpression('function(emp) {return emp.text;}'),
                ],
                'pluginEvents' => isset($modal) ? [] : [
                    // 'select2:select' =>  new JsExpression('function() { $(this).closest("form").submit(); }'),
                    // 'select2:unselecting' =>  new JsExpression('function() { $(this).closest("form").submit(); }'),
                ],
                    ])->label($label ?? 'บุคลากร');
                    ?>       
       </div>