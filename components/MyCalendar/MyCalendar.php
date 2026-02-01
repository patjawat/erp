<?php
namespace app\components\MyCalendar;

use yii\base\Widget;
use yii\helpers\Url;
use yii\helpers\Json;

class MyCalendar extends Widget
{
    public $apiUrl;
    public $maxDisplay = 2; // กำหนดจำนวน event ที่จะโชว์ในช่องวันที่ (ที่เหลือต้องกดดูใน Modal)
    public $modalTitle = 'รายละเอียดกิจกรรม';

    public function run()
    {
        return $this->render('calendarView', [
            'apiUrl' => $this->apiUrl,
            'maxDisplay' => $this->maxDisplay,
            'modalTitle' => $this->modalTitle,
        ]);
    }
}