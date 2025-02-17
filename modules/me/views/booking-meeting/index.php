<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use app\components\UserHelper;
$this->title = 'จองห้องประชุม';
$this->params['breadcrumbs'][] = $this->title;
$me = UserHelper::getEmployee();

$dateLast = new DateTime($searchModel->date_start ? $searchModel->date_start : date('Y-m-d'));
$dateNext = new DateTime($searchModel->date_start ? $searchModel->date_start : date('Y-m-d'));
$dateLast->modify('-1 day');
$dateNext->modify('+1 day');

?>


<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-person-chalkboard fs-1 text-white"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-title'); ?>
ระบบขอใช้ยานพาหนะ
<?php $this->endBlock(); ?>


<?php $this->beginBlock('sub-title'); ?>
<?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php  echo $this->render('menu') ?>
<?php $this->endBlock(); ?>

<style>
.fc .fc-toolbar>*> :first-child {
    margin-left: 0;
    font-size: medium;
}
</style>
<?php // echo $this->render('list_room')?>
<div class="alert alert-primary" role="alert">
    <strong>Alert Heading</strong> Some Word
</div>

<div class="row">
    <div class="col-6">
        <div class="card mb-2">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <?php echo Html::a('วันก่อน',['/me/booking-meeting/index','date_start' => $dateLast->format('Y-m-d')])?>
                    <h6><i class="fa-regular fa-calendar-plus"></i>
                        <?php $time = time(); echo Yii::$app->thaiFormatter->asDate($searchModel->date_start, 'full')."<br>";?>
                    </h6>
                    <?php echo Html::a('วันถัดไป',['/me/booking-meeting/index','date_start' => $dateNext->format('Y-m-d')])?>
                </div>
            </div>
        </div>
        <?php echo $this->render('list_room',['model' => $searchModel])?>
        <?php // echo $this->render('grid_room',['model' => $searchModel])?>
    </div>
    <div class="col-6">
        <div class="card">
            <div class="card-body">
                <h6><i class="fa-regular fa-calendar-plus"></i> ปฏิทินรวม </h6>
                <?= edofre\fullcalendar\Fullcalendar::widget([
                        'options'       => [
                                    'id'       => 'calendar',
                                    'language' => 'th',
                                ],
                                'clientOptions' => [
                                    'weekNumbers' => true,
                                    'selectable'  => true,
                                    'droppable' => true,
                                    'defaultView' => 'month',
                                    'eventResize' => new  \yii\web\JsExpression("
                                        function(event, delta, revertFunc, jsEvent, ui, view) {
                                            console.log(event);
                                        }
                                    "),
                                
                                    'droppable' => true,
                                    'drop'              => new \yii\web\JsExpression("
                                        function(date, jsEvent, ui, resourceId) {
                                        console.log('drop', date.format(), resourceId);
                        
                                        if ($('#drop-remove').is(':checked')) {
                                            // if so, remove the element from the \"Draggable Events\" list
                                            // $(this).remove();
                                        }
                                    }
                                "),
                                'eventReceive'      => new \yii\web\JsExpression("
                                    function(event) { // called when a proper external event is dropped
                                        console.log('eventReceive', event);
                                        }
                                        "),
                                        'eventDrop'         => new \yii\web\JsExpression("
                                        function(event, delta, revertFunc, jsEvent, ui, view,info) {
                                            var id =  event.id;
                                            var start = event.start.format();
                                            var end = event.end ? event.end.format() : null;
                                            // console.log('eventDrop',start);
                                            updateEvent(id,start,end)
                                    
                                }
                                "),
                                'select'=> new \yii\web\JsExpression("function(start, end, jsEvent, view) {
                                            addEvent(moment(start).format(),moment(end).format())
                                                // กำหนดปฏิทิน
                                            var calendar = $('#calendar').fullCalendar('getCalendar');
                                    
                                    // เพิ่ม Event แบบ dynamic
                            

                                }"),
                                'eventClick' => new \yii\web\JsExpression("
                                    function(calEvent, jsEvent, view) {
                                    var id =  calEvent.id;
                                    viewEvent(id)

                                }
                                "),
                                    
                                    'eventRender' => new \yii\web\JsExpression("
                                    function(event, element) {
                                        if (event.imageUrl) {
                                            element.find('.fc-title').before('<img src=\"' + event.imageUrl + '\" style=\"width:20px;height:20px;margin-right:5px;\">');
                                        }
                                            
                                    }
                                "),
                                ],
                                'events'        => Url::to(['/me/booking-meeting/events']),
                            ]);
                        ?>


            </div>
        </div>
    </div>

    <div class="col-4">
        <?php //  $this->render('list_room')?>
    </div>
</div>


<?php // echo $this->render('grid_room')?>

<?php
$js = <<< JS

$(".open-canvas").click(function(e){
            e.preventDefault();
            var offcanvasElement = new bootstrap.Offcanvas($("#MyOffcanvas")[0]);
            offcanvasElement.show();
            $.ajax({
                type: "get",
                url: $(this).attr('href'),
                dataType: "json",
                success: function (res) {
                    $('.offcanvas-body').html(res.content)
                }
            });
            $('.offcanvas-title').text('ssss');
        });
        

        function viewEvent(id)
                {
                    $.ajax({
                                type: "get",
                                url: "/me/booking-meeting/view",
                                data:{id:id,title:'<i class="fa-regular fa-calendar-check"></i> รายละเอียดการขอใช้ห้องประชุม'},
                                dataType: "json",
                                success: function (res) {
                                    $("#main-modal").modal("show");
                                    $("#main-modal-label").html(res.title);
                                    $(".modal-body").html(res.content);
                                    $(".modal-footer").html(res.footer);
                                    $(".modal-dialog").removeClass("modal-sm modal-md modal-lg modal-xl");
                                    $(".modal-dialog").addClass("modal-md");
                                }
                            });
                }

JS;
$this->registerJS($js,View::POS_END);
?>