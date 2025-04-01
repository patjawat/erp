<?php

namespace app\components;

use yii\web\View;
use yii\base\Widget;
use yii\helpers\Url;
use yii\helpers\Html;

class EventCalendar extends Widget
{
    public $events = [];
    public $year;
    public $month;
    public $options = [];
    
    /**
     * URL configurations
     */
    public $calendarUrl = ['calendar/load']; // URL สำหรับโหลดปฏิทิน
    public $eventUrl = ['event/view']; // URL สำหรับดูรายละเอียดอีเวนต์

    public function init()
    {
        parent::init();
        
        if (!isset($this->year)) {
            $this->year = date('Y');
        }
        
        if (!isset($this->month)) {
            $this->month = date('m');
        }
        
        $this->registerAssets();
    }

    public function run()
    {
        $calendar = $this->generateCalendar();
        return $calendar;
    }

    protected function generateCalendar()
    {
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $this->month, $this->year);
        $firstDayOfMonth = date('N', strtotime($this->year . '-' . $this->month . '-01'));
        $prevMonth = $this->month - 1 <= 0 ? 12 : $this->month - 1;
        $prevYear = $this->month - 1 <= 0 ? $this->year - 1 : $this->year;
        $nextMonth = $this->month + 1 > 12 ? 1 : $this->month + 1;
        $nextYear = $this->month + 1 > 12 ? $this->year + 1 : $this->year;
        
        $monthNames = [
            1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
            5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
            9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
        ];
        
        $currentMonthName = $monthNames[(int)$this->month];
        $currentYear = $this->year;

        $html = '';
        $html .= '<div class="calendar-container">';
        $html .= '<div class="calendar-header mb-3">';
        $html .= '<div class="row">';
        $html .= '<div class="col-md-4">';
        $html .= Html::a('<i class="bi bi-chevron-left"></i> ก่อนหน้า', 'javascript:void(0);', [
            'class' => 'btn btn-outline-primary prev-month',
            'data-year' => $prevYear,
            'data-month' => $prevMonth
        ]);
        $html .= '</div>';
        $html .= '<div class="col-md-4 text-center">';
        $html .= '<h3>' . $currentMonthName . ' ' . $currentYear . '</h3>';
        $html .= '</div>';
        $html .= '<div class="col-md-4 text-end">';
        $html .= Html::a('ถัดไป <i class="bi bi-chevron-right"></i>', 'javascript:void(0);', [
            'class' => 'btn btn-outline-primary next-month',
            'data-year' => $nextYear,
            'data-month' => $nextMonth
        ]);
        $html .= '</div>';
        $html .= '</div>'; // end row
        $html .= '</div>'; // end calendar-header
        
        $html .= '<div class="calendar-body">';
        $html .= '<table class="table table-bordered">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th>จันทร์</th><th>อังคาร</th><th>พุธ</th><th>พฤหัสบดี</th><th>ศุกร์</th><th>เสาร์</th><th>อาทิตย์</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';
        
        $day = 1;
        $html .= '<tr>';
        
        // Fill in the empty cells for the first week
        for ($i = 1; $i < $firstDayOfMonth; $i++) {
            $html .= '<td class="empty"></td>';
        }
        
        // Fill in the days of the month
        for ($i = $firstDayOfMonth; $i <= 7; $i++) {
            $html .= $this->generateDayCell($day);
            $day++;
        }
        
        $html .= '</tr>';
        
        // Fill in the rest of the days
        while ($day <= $daysInMonth) {
            $html .= '<tr>';
            
            for ($i = 1; $i <= 7 && $day <= $daysInMonth; $i++) {
                $html .= $this->generateDayCell($day);
                $day++;
            }
            
            // Fill in the empty cells for the last week
            while ($i <= 7) {
                $html .= '<td class="empty"></td>';
                $i++;
            }
            
            $html .= '</tr>';
        }
        
        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</div>'; // end calendar-body
        $html .= '</div>'; // end calendar-container
        
        // ส่งค่า URL ไปยัง JavaScript
        $calendarLoadUrl = Url::to($this->calendarUrl);
        $eventViewUrl = Url::to($this->eventUrl);
        
        $this->view->registerJs("
            var calendarLoadUrl = '$calendarLoadUrl';
            var eventViewUrl = '$eventViewUrl';
        ", View::POS_HEAD);
        
        return $html;
    }
    
    protected function generateDayCell($day)
    {
        $currentDate = $this->year . '-' . str_pad($this->month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);
        $isToday = (date('Y-m-d') === $currentDate) ? ' today' : '';
        
        $html = '<td class="day' . $isToday . '">';
        $html .= '<div class="day-number">' . $day . '</div>';
        
        // Check if there are events for this day
        $dayEvents = $this->getEventsForDay($currentDate);
        
        if (!empty($dayEvents)) {
            $html .= '<div class="events-container">';
            
            foreach ($dayEvents as $event) {
                // กำหนด class พิเศษสำหรับวันแรกและวันสุดท้ายของอีเวนต์
                $eventClasses = 'event';
                if ($event['date_start'] === $currentDate) {
                    $eventClasses .= ' event-start';
                }
                if ($event['date_end'] === $currentDate) {
                    $eventClasses .= ' event-end';
                }
                
                // สร้างสัญลักษณ์หรือข้อความแสดงระยะเวลา
                $durationText = '';
                $startDate = new \DateTime($event['date_start']);
                $endDate = new \DateTime($event['date_end']);
                $interval = $startDate->diff($endDate);
                
                if ($interval->days > 0) {
                    if ($event['date_start'] === $currentDate) {
                        $durationText = ' (เริ่ม)';
                    } elseif ($event['date_end'] === $currentDate) {
                        $durationText = ' (สิ้นสุด)';
                    } else {
                        $durationText = ' (ต่อเนื่อง)';
                    }
                }
                
                $html .= '<div class="' . $eventClasses . '" style="background-color:' . $event['color'] . '">';
                $html .= Html::a($event['title'] . $durationText, 'javascript:void(0);', [
                    'class' => 'event-link',
                    'data-id' => $event['id'],
                    'data-toggle' => 'tooltip',
                    'title' => $event['description']
                ]);
                $html .= '</div>';
            }
            
            $html .= '</div>';
        }
        
        $html .= '</td>';
        
        return $html;
    }
    
    protected function getEventsForDay($date)
    {
        $dayEvents = [];
        
        foreach ($this->events as $event) {
            // ตรวจสอบว่าอีเวนต์อยู่ในวันที่กำหนดหรือไม่
            $startDate = strtotime($event['date_start']);
            $endDate = strtotime($event['date_end']);
            $checkDate = strtotime($date);
            
            if ($checkDate >= $startDate && $checkDate <= $endDate) {
                $dayEvents[] = $event;
            }
        }
        
        return $dayEvents;
    }
    
    protected function registerAssets()
    {
        $this->view->registerCss("
            .calendar-container {
                width: 100%;
                border-radius: 5px;
                overflow: hidden;
            }
            .calendar-body table {
                width: 100%;
                border-collapse: collapse;
            }
            .calendar-body th {
                background-color: #f8f9fa;
                text-align: center;
                padding: 10px;
            }
            .calendar-body td {
                height: 120px;
                vertical-align: top;
                padding: 5px;
                position: relative;
            }
            .calendar-body td.empty {
                background-color: #f9f9f9;
            }
            .calendar-body td.today {
                background-color: #e8f4ff;
            }
            .day-number {
                font-weight: bold;
                font-size: 1.1em;
                margin-bottom: 5px;
            }
            .events-container {
                margin-top: 5px;
            }
            .event {
                margin-bottom: 3px;
                padding: 3px 5px;
                border-radius: 3px;
                font-size: 0.85em;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            .event a {
                color: white;
                text-decoration: none;
            }
            .event-start {
                border-left: 4px solid #28a745;
            }
            .event-end {
                border-right: 4px solid #dc3545;
            }
        ");
        
        $js = <<<JS
            $(document).ready(function() {
                // Enable tooltips
                $('[data-toggle="tooltip"]').tooltip();
                
                // Handle previous month click
                $('.prev-month').on('click', function() {
                    let year = $(this).data('year');
                    let month = $(this).data('month');
                    loadCalendar(year, month);
                });
                
                // Handle next month click
                $('.next-month').on('click', function() {
                    let year = $(this).data('year');
                    let month = $(this).data('month');
                    loadCalendar(year, month);
                });
                
                // Handle event click
                $(document).on('click', '.event-link', function() {
                    let eventId = $(this).data('id');
                    showEventDetails(eventId);
                });
                
                function loadCalendar(year, month) {
                    $.ajax({
                        url: calendarLoadUrl,
                        type: 'GET',
                        data: {
                            year: year,
                            month: month
                        },
                        success: function(response) {
                            $('.calendar-container').replaceWith(response);
                        },
                        error: function() {
                            alert('เกิดข้อผิดพลาดในการโหลดปฏิทิน');
                        }
                    });
                }
                
                function showEventDetails(eventId) {
                    $.ajax({
                        url: eventViewUrl,
                        type: 'GET',
                        data: {
                            id: eventId
                        },
                        success: function(response) {
                            $('#eventDetailsModal .modal-body').html(response);
                            $('#eventDetailsModal').modal('show');
                        },
                        error: function() {
                            alert('เกิดข้อผิดพลาดในการโหลดข้อมูลอีเวนต์');
                        }
                    });
                }
            });
        JS;
        
        $this->view->registerJs($js, View::POS_END);
    }
}