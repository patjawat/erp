<?php
namespace app\widgets\datepicker;

use yii\web\View;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\InputWidget;

class DatepickerThai extends InputWidget
{
    public $options = [];

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

        $options = "";
        if (!empty($this->options)) {
            $options .= "{\n";
            foreach ((array) $this->options as $key => $value) {
                if (is_array($value)) {
                    $values = [];
                    foreach ($value as $_key => $_value) {
                        if (is_int($_value) || is_float($_value)) {
                            $values[] = $_value; // хотя по факту не нужно, используется только для передачи дат
                        } else {
                            $values[] = "'{$_value}'";
                        }
                    }
                    $value = "[" . implode(', ', $values) . "]";
                    $options .= "    {$key}: {$value},\n";
                } elseif (is_int($value) || is_float($value)) {
                    $options .= "    {$key}: {$value},\n";
                } else {
                    $options .= "    {$key}: '{$value}',\n";
                }
            }
            $options .= "}\n";
        }
        // thaiDatepicker('#leave-date_start,#leave-date_end')
        $JavaScript = "thaiDatepicker('";
        $JavaScript .= '#'.$this->options['id'];
        $JavaScript .= "');";

        $this->getView()->registerJs($JavaScript, View::POS_END);
    }
}