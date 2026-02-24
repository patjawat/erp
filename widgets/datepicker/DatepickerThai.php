<?php
namespace app\widgets\datepicker;

use yii\web\View;
use yii\helpers\Html;
use yii\widgets\InputWidget;

/**
 * Widget สำหรับช่องวันที่รูปแบบไทย (พ.ศ.) ใช้ web/js/thai.datepicker.js
 *
 * ตัวอย่าง (กับ model):
 * echo \app\widgets\datepicker\DatepickerThai::widget([
 *     'model' => $model,
 *     'attribute' => 'date_start',
 * ]);
 *
 * ตัวอย่าง (ไม่มี model):
 * echo \app\widgets\datepicker\DatepickerThai::widget([
 *     'name' => 'order_date',
 *     'value' => date('d/m/Y'),
 * ]);
 *
 * หลายช่องใน selector เดียว (ใช้ clientOptions['selector']):
 * echo \app\widgets\datepicker\DatepickerThai::widget([
 *     'model' => $model,
 *     'attribute' => 'date_start',
 *     'clientOptions' => ['selector' => '#leave-date_start,#leave-date_end'],
 * ]);
 */
class DatepickerThai extends InputWidget
{
    /** @var array options สำหรับ input (id, class, placeholder ฯลฯ) */
    public $options = [];

    /**
     * ถ้ากำหนด selector จะใช้แทน #id ของ input นี้ (สำหรับ bind หลาย input กับ thaiDatepicker ตัวเดียว)
     * @var array เช่น ['selector' => '#leave-date_start,#leave-date_end']
     */
    public $clientOptions = [];

    public function init()
    {
        parent::init();
        Html::addCssClass($this->options, 'form-control');
    }

    public function run()
    {
        Assets::register($this->getView());

        if ($this->hasModel()) {
            echo Html::activeTextInput($this->model, $this->attribute, $this->options);
        } else {
            echo Html::textInput($this->name, $this->value, $this->options);
        }

        $selector = !empty($this->clientOptions['selector'])
            ? $this->clientOptions['selector']
            : '#' . $this->options['id'];
        $js = "if (typeof thaiDatepicker === 'function') thaiDatepicker(" . json_encode($selector) . ");";
        $this->getView()->registerJs($js, View::POS_END);
    }
}