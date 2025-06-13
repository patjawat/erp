<?php

// Widget สำหรับแสดงตราประทับ
namespace app\widgets;

use yii\base\Widget;
use yii\helpers\Html;

class StampPreviewWidget extends Widget
{
    public $stampData = [];
    public $options = [];
    
    public function init()
    {
        parent::init();
        
        // ตั้งค่าเริ่มต้น
        $this->stampData = array_merge([
            'doc_number' => '2568/001',
            'receive_date' => date('Y-m-d'),
            'receive_time' => date('H:i'),
            'department' => 'กรมการสาธารณสุข',
            'office' => 'กรุงเทพมหานคร',
            'phone' => '0-2000-0000',
        ], $this->stampData);
        
        $this->options = array_merge([
            'width' => '250px',
            'height' => '180px',
            'class' => 'stamp-preview'
        ], $this->options);
    }
    
    public function run()
    {
        $thaiDate = \app\components\StampHelper::formatThaiDate($this->stampData['receive_date']);
        
        $html = Html::beginTag('div', [
            'class' => $this->options['class'],
            'style' => "width: {$this->options['width']}; height: {$this->options['height']}; border: 1px solid #ccc; position: relative; font-family: 'Sarabun', sans-serif; background: #f8f9fa;"
        ]);
        
        // ตราราชการ
        $html .= Html::tag('div', 
            Html::img('/images/garuda_logo.png', [
                'alt' => 'ตราราชการ',
                'style' => 'width: 40px; height: 40px;',
                'onerror' => "this.style.display='none'"
            ]),
            ['style' => 'position: absolute; top: 10px; left: 15px;']
        );
        
        // หัวเรื่อง
        $html .= Html::tag('div', 'หน่วยงานรับเพื่อพระราชดำเนิน', [
            'style' => 'text-align: center; font-weight: bold; font-size: 12px; margin-top: 8px; padding: 0 60px;'
        ]);
        
        $html .= Html::tag('hr', '', ['style' => 'margin: 5px 60px;']);
        
        // ข้อมูลหลัก
        $html .= Html::beginTag('div', ['style' => 'padding: 5px 15px; font-size: 11px;']);
        
        $html .= Html::tag('div', 
            Html::tag('span', 'รับที่') . 
            Html::tag('span', $this->stampData['doc_number'], ['style' => 'float: right;']),
            ['style' => 'margin-bottom: 3px;']
        );
        
        $html .= Html::tag('div', 
            Html::tag('span', 'วันที่') . 
            Html::tag('span', $thaiDate, ['style' => 'float: right;']),
            ['style' => 'margin-bottom: 3px;']
        );
        
        $html .= Html::tag('div', 
            Html::tag('span', 'เวลา') . 
            Html::tag('span', $this->stampData['receive_time'] . ' น.', ['style' => 'float: right;']),
            ['style' => 'margin-bottom: 3px;']
        );
        
        $html .= Html::endTag('div');
        
        // ข้อมูลหน่วยงาน
        $html .= Html::beginTag('div', ['style' => 'text-align: center; font-size: 10px; margin-top: 10px;']);
        $html .= Html::tag('div', $this->stampData['department']);
        $html .= Html::tag('div', $this->stampData['office']);
        $html .= Html::tag('div', 'โทร ' . $this->stampData['phone']);
        $html .= Html::endTag('div');
        
        $html .= Html::endTag('div');
        
        return $html;
    }
}