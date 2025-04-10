<?php
// views/vehicle-booking/calendar.php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\bootstrap\Modal;

/* @var $this yii\web\View */
/* @var $days array */
/* @var $vehicles app\models\Vehicle[] */
/* @var $bookings app\models\VehicleBooking[] */
/* @var $previousDate string */
/* @var $nextDate string */
/* @var $currentDate string */
/* @var $month string */

$this->title = 'ปฏิทินการใช้รถ';
$this->params['breadcrumbs'][] = $this->title;

// เปิดใช้งาน Modal
$this->registerJs("
    $(function () {
        $('#booking-modal').on('hidden.bs.modal', function () {
            $(this).removeData('bs.modal');
        });
    });
");

// สร้าง array ของวันที่แสดงเพื่อใช้ในการคำนวณ colspan
$dayMap = [];
foreach ($days as $index => $day) {
    $dayMap[$day] = $index;
}

// ฟังก์ชันสำหรับคำนวณ colspan
function calculateColspan($booking, $dayMap, $days) {
    $start = new DateTime($booking->date_start);
    $end = new DateTime($booking->date_end);
    
    // หาวันแรกที่อยู่ในช่วงที่แสดง
    $visibleStart = max($start, new DateTime($days[0]));
    
    // หาวันสุดท้ายที่อยู่ในช่วงที่แสดง
    $visibleEnd = min($end, new DateTime($days[count($days) - 1]));
    
    // ถ้าช่วงวันไม่อยู่ในตาราง
    if ($visibleStart > new DateTime($days[count($days) - 1]) || $visibleEnd < new DateTime($days[0])) {
        return 0;
    }
    
    // คำนวณ column เริ่มต้น
    $startColumn = isset($dayMap[$visibleStart->format('Y-m-d')]) ? $dayMap[$visibleStart->format('Y-m-d')] : 0;
    
    // คำนวณ column สิ้นสุด
    $endColumn = isset($dayMap[$visibleEnd->format('Y-m-d')]) ? $dayMap[$visibleEnd->format('Y-m-d')] : count($days) - 1;
    
    // คำนวณ colspan
    return $endColumn - $startColumn + 1;
}

?>

<div class="vehicle-booking-calendar">
    <h1><?= Html::encode($this->title) ?></h1>
    
    <div class="panel panel-default">
        <div class="panel-heading">
            <div class="row">
                <div class="col-md-4">
                    <?= Html::a('<i class="glyphicon glyphicon-chevron-left"></i>', ['calendar', 'date' => $previousDate], ['class' => 'btn btn-default']) ?>
                </div>
                <div class="col-md-4 text-center">
                    <h3><?= $month ?></h3>
                </div>
                <div class="col-md-4 text-right">
                    <?= Html::a('<i class="glyphicon glyphicon-chevron-right"></i>', ['calendar', 'date' => $nextDate], ['class' => 'btn btn-default']) ?>
                </div>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>รถ / วันที่</th>
                        <?php foreach ($days as $day): ?>
                        <th class="text-center">
                            <?= Yii::$app->formatter->asDate($day, 'd M. Y') ?>
                        </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vehicles as $vehicle): ?>
                    <tr>
                        <td><?= Html::encode($vehicle->id . " " . $vehicle->license_plate) ?></td>
                        
                        <?php
                        // สร้าง array เก็บว่าช่องวันไหนถูกใช้ไปแล้ว
                        $usedCells = array_fill(0, count($days), false);
                        
                        // กรองเฉพาะการจองของรถคันนี้
                        $vehicleBookings = array_filter($bookings, function($b) use ($vehicle) {
                            return $b->id == $vehicle->id;
                        });
                        
                        // เรียงลำดับตามวันที่เริ่มต้น
                        usort($vehicleBookings, function($a, $b) {
                            return strtotime($a->date_start) - strtotime($b->date_start);
                        });
                        
                        // วนลูปวันในตาราง
                        for ($i = 0; $i < count($days); $i++) {
                            // ข้ามหากช่องนี้ถูกใช้โดย colspan แล้ว
                            if ($usedCells[$i]) {
                                continue;
                            }
                            
                            $currentDay = $days[$i];
                            $foundBooking = false;
                            
                            // ตรวจสอบการจองที่ครอบคลุมวันนี้
                            foreach ($vehicleBookings as $booking) {
                                $bookingStart = new DateTime($booking->date_start);
                                $bookingEnd = new DateTime($booking->date_end);
                                $cellDate = new DateTime($currentDay);
                                
                                // ถ้าวันปัจจุบันอยู่ในช่วงการจอง และเป็นวันแรกของการจองในตาราง
                                if ($cellDate >= $bookingStart && $cellDate <= $bookingEnd && 
                                    ($cellDate == $bookingStart || $cellDate == new DateTime($days[0]))) {
                                    
                                    // คำนวณ colspan
                                    $colspan = calculateColspan($booking, $dayMap, $days);
                                    
                                    // ถ้า colspan มากกว่า 0 แสดงว่าการจองอยู่ในช่วงที่แสดง
                                    if ($colspan > 0) {
                                        // กำหนดว่าช่องถัดไปถูกใช้แล้ว
                                        for ($j = $i; $j < $i + $colspan; $j++) {
                                            if ($j < count($days)) {
                                                $usedCells[$j] = true;
                                            }
                                        }
                                        
                                        // แสดงข้อมูลการจอง
                                        echo '<td class="text-center" colspan="' . $colspan . '" style="background-color: #f5f5f5; padding: 5px; border-radius: 4px;">';
                                        echo Html::encode($booking->id) . '<br>';
                                        echo Html::encode($booking->description ?? '-') . '<br>';
                                        echo '<small>' . Yii::$app->formatter->asDate($booking->date_start, 'd M') . ' - ' . Yii::$app->formatter->asDate($booking->date_end, 'd M') . '</small>';
                                        echo '<div class="booking-actions">';
                                        echo Html::a('<i class="glyphicon glyphicon-pencil"></i>', ['update', 'id' => $booking->id], [
                                            'class' => 'btn btn-xs btn-primary',
                                            'data-toggle' => 'modal',
                                            'data-target' => '#booking-modal',
                                        ]);
                                        echo ' ';
                                        echo Html::a('<i class="glyphicon glyphicon-trash"></i>', ['delete', 'id' => $booking->id], [
                                            'class' => 'btn btn-xs btn-danger',
                                            'data' => [
                                                'confirm' => 'คุณแน่ใจหรือไม่ว่าต้องการลบรายการนี้?',
                                                'method' => 'post',
                                            ],
                                        ]);
                                        echo '</div>';
                                        echo '</td>';
                                        
                                        $foundBooking = true;
                                        break;
                                    }
                                }
                            }
                            
                            // ถ้าไม่มีการจองในวันนี้
                            if (!$foundBooking) {
                                echo '<td class="text-center">';
                                echo Html::a('<i class="glyphicon glyphicon-plus"></i> จอง', ['create', 'id' => $vehicle->id, 'date' => $currentDay], [
                                    'class' => 'btn btn-sm btn-success',
                                    'data-toggle' => 'modal',
                                    'data-target' => '#booking-modal',
                                ]);
                                echo '</td>';
                            }
                        }
                        ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
Modal::begin([
    'id' => 'booking-modal',
    'header' => '<h4 class="modal-title">การจองรถ</h4>',
    'size' => 'modal-md',
]);
echo "<div id='modalContent'></div>";
Modal::end();
?>