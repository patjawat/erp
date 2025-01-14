<?php
namespace app\widgets;

use yii\web\View;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\InputWidget;

class FlatpickrWidget extends InputWidget
{
    public $clientOptions = [];
    
    public function init()
    {
        parent::init();
        
        $defaultOptions = [
            'dateFormat' => 'Y-m-d',
            'locale' => 'th',
            'enableTime' => false,
            'altInput' => true,
            'altFormat' => 'd/m/Y',
            'allowInput' => true
        ];
        
        $this->clientOptions = array_merge($defaultOptions, $this->clientOptions);
        
        if (!isset($this->options['class'])) {
            $this->options['class'] = 'form-control';
        }


    }

    public function run()
    {
        $this->registerClientScript();
        
        if ($this->hasModel()) {
            return Html::activeTextInput($this->model, $this->attribute, $this->options);
        }
        
        return Html::textInput($this->name, $this->value, $this->options);
    }

    protected function registerClientScript()
    {
        $view = $this->getView();
        $inputId = $this->options['id'];
        
        // Register Flatpickr assets
        // $view->registerCssFile('https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css');
        $view->registerCssFile('https://npmcdn.com/flatpickr/dist/themes/dark.css');
        $view->registerJsFile('https://cdn.jsdelivr.net/npm/flatpickr', ['position' => View::POS_END]);
        $view->registerJsFile('https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js', ['position' => View::POS_END]);
        
        // Custom Thai Buddhist Era formatter
        $js = <<<JS
        // ตรวจสอบก่อนว่ามีการประกาศ thaiLocale แล้วหรือไม่
        if (typeof window.thaiLocale === 'undefined') {
            window.thaiLocale = {
                ...flatpickr.l10ns.th,
                reformatYear: function(year) {
                    return parseInt(year) + 543;
                }
            };
        }
        
        // ตรวจสอบก่อนว่ามีการประกาศ thaiDateFormatter แล้วหรือไม่
        if (typeof window.thaiDateFormatter === 'undefined') {
            window.thaiDateFormatter = {
                // แปลง ค.ศ. เป็น พ.ศ.
                formatDate: function(dateObj, format) {
                    if (!dateObj) return '';
                    
                    const buddhistYear = dateObj.getFullYear() + 543;
                    const month = (dateObj.getMonth() + 1).toString().padStart(2, '0');
                    const day = dateObj.getDate().toString().padStart(2, '0');
                    
                    // รูปแบบ d/m/Y
                    if (format === 'd/m/Y') {
                        return `\${day}/\${month}/\${buddhistYear}`;
                    }
                    
                    // รูปแบบ Y-m-d (สำหรับเก็บในฐานข้อมูล)
                    const yearCE = dateObj.getFullYear();
                    return `\${yearCE}-\${month}-\${day}`;
                },
                
                // แปลง พ.ศ. เป็น ค.ศ.
                parseDate: function(dateStr, format) {
                    if (!dateStr) return null;
                    
                    try {
                        // กรณีรูปแบบ d/m/Y
                        if (format === 'd/m/Y') {
                            const parts = dateStr.split('/');
                            if (parts.length !== 3) return null;
                            
                            const day = parseInt(parts[0], 10);
                            const month = parseInt(parts[1], 10);
                            const yearBE = parseInt(parts[2], 10);
                            const yearCE = yearBE - 543;
                            
                            return new Date(yearCE, month - 1, day);
                        }
                        
                        // กรณีรูปแบบ Y-m-d
                        const parts = dateStr.split('-');
                        if (parts.length !== 3) return null;
                        
                        const yearCE = parseInt(parts[0], 10);
                        const month = parseInt(parts[1], 10);
                        const day = parseInt(parts[2], 10);
                        
                        return new Date(yearCE, month - 1, day);
                    } catch (e) {
                        console.error('Error parsing date:', e);
                        return null;
                    }
                }
            };
        }
        
        // สร้าง Flatpickr instance
        flatpickr("#{$inputId}", {
            ...{$this->getClientOptions()},
            locale: window.thaiLocale,
            formatDate: window.thaiDateFormatter.formatDate,
            parseDate: window.thaiDateFormatter.parseDate,
            onChange: function(selectedDates, dateStr, instance) {
                if (selectedDates && selectedDates[0]) {
                    instance.altInput.value = window.thaiDateFormatter.formatDate(selectedDates[0], 'd/m/Y');
                }
            },
            onReady: function(selectedDates, dateStr, instance) {
                // ตรวจสอบว่ามี calendarContainer หรือไม่
                if (instance.calendarContainer) {
                    const yearInput = instance.calendarContainer.querySelector('.numInput.cur-year');
                    if (yearInput) {
                        const currentYear = new Date().getFullYear();
                        yearInput.value = currentYear + 543;
                        yearInput.min = currentYear + 543 - 100; // ย้อนหลัง 100 ปี
                        yearInput.max = currentYear + 543 + 100; // ไปข้างหน้า 100 ปี
                    }
                }
            },
            onYearChange: function(selectedDates, dateStr, instance) {
                const yearInput = instance.calendarContainer.querySelector('.numInput.cur-year');
                if (yearInput) {
                    const yearCE = parseInt(yearInput.value) - 543;
                    instance.currentYear = yearCE;
                    instance.redraw();
                }
            }
        });

        // จัดการค่าเริ่มต้น
        if (flatpickr.selectedDates && flatpickr.selectedDates[0]) {
            flatpickr.altInput.value = window.thaiDateFormatter.formatDate(flatpickr.selectedDates[0], 'd/m/Y');
        }

        // แก้ไขการแสดงผลปีเมื่อเปิดปฏิทิน
        if (flatpickr.calendarContainer) {
            flatpickr.calendarContainer.addEventListener('mouseenter', function() {
                const yearInput = flatpickr.calendarContainer.querySelector('.numInput.cur-year');
                if (yearInput && yearInput.value) {
                    const yearCE = parseInt(yearInput.value);
                    if (yearCE < 2400) { // ถ้าเป็นปี ค.ศ.
                        yearInput.value = yearCE + 543;
                    }
                }
            });
        }
JS;
        $view->registerJs($js, View::POS_END);
    }

    protected function getClientOptions()
    {
        return Json::encode($this->clientOptions);
    }
}
