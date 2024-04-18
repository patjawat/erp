<?php
use app\components\UserHelper;
use yii\bootstrap5\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

$this->title = 'โปรไฟล์';

$this->params['breadcrumbs'][] = $this->title;


$user  = UserHelper::GetUser();
?>

<?php $this->beginBlock('page-title'); ?>
<?=$this->title;?>
<?php $this->endBlock(); ?>

<div class="card">
    <div class="card-body">
    
<div class="row">
  <div class="col-md-">
    <div class="d-flex flex-sm-row-reverse">
      <div class="flex-grow-1 bg-primary">All</div>
      <div class="flex-grow-2 bg-secondary">Best</div>
      <div class="flex-grow-3 bg-warning">Ever</div>
    </div>
  </div>
</div>

    </div>
</div>


<div class="d-flex">
  <div class="p-2 w-100">
    
  </div>
  <div class="p-2 flex-shrink-1">Flex item</div>
</div>


<div class="row">
    <div class="col-xl-8 col-lg-8 col-md-6 col-sm-12">
        <div class="row">
            <div class="col-xxl-4 col-xl-4 col-lg-4 col-md-12 col-sm-12">
                <div class="card card-body">
                    <div class="d-flex">
                        <div class="avatar-md rounded-circle bg-light">
                            <i class="bi bi-calendar2-plus fs-md avatar-title text-primary"></i>
                        </div>
                        <div class="flex-grow-1 text-end">
                            <h5 class="text-dark"><span data-plugin="counterup">Job Description</span></h5>
                            <p class="text-muted mb-0 text-truncate">0</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xxl-4 col-xl-4 col-lg-4 col-md-12 col-sm-12">
                <div class="card card-body">
                    <div class="d-flex">
                        <div class="avatar-md rounded-circle bg-light">
                            <i class="bi bi-clock fs-md avatar-title text-primary"></i>
                        </div>
                        <div class="flex-grow-1 text-end">
                            <h5 class="text-dark"><span data-plugin="counterup">KPI</span></h5>
                            <p class="text-muted mb-0 text-truncate">0</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-4 col-xl-4 col-lg-4 col-md-12 col-sm-12">
                <div class="card card-body">
                    <div class="d-flex">
                        <div class="avatar-md rounded-circle bg-light">
                            <i class="bi bi-hourglass-split avatar-title text-primary"></i>
                        </div>
                        <div class="flex-grow-1 text-end">
                            <h5 class="text-dark"><span data-plugin="counterup">เงินเดือน</span></h5>
                            <p class="text-muted mb-0 text-truncate">140,000 บาท</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
<div class="row">
<div class="col-12">
    <?=$this->render('estimate_chart')?>

</div>
</div>
        <?=$this->render('app_services')?>

    </div>
    <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12">
        <?=$this->render('@app/modules/hr/views/employees/avatar',['model' => $model])?>

     
        <div class="card card-body">
                    <div class="d-flex gap-3">
                        <div class="avatar-md rounded-circle bg-light">
                            <i class="bi bi-clock avatar-title text-primary"></i>
                        </div>
                        <div class="flex-grow-1 text-start">
                            <h6 class="text-dark"><span data-plugin="counterup">อบรม/สัมนา</span></h6>
                            <p class="text-muted mb-0 text-truncate">อายุราชการ</p>
                        </div>
                    </div>
                </div>

                <div class="card card-body">
                    <div class="d-flex gap-3">
                        <div class="avatar-md rounded-circle bg-light">
                            <i class="bi bi-clock fs-md avatar-title text-primary"></i>
                        </div>
                        <div class="flex-grow-1 text-start">
                            <h5 class="text-dark"><span data-plugin="counterup">2 ปี 1 เดือน 10 วัน</span></h5>
                            <p class="text-muted mb-0 text-truncate">อายุราชการ</p>
                        </div>
                    </div>
                </div>


        <?php // $this->render('list_leave')?>
        <?php // $this->render('event_list')?>
    </div>
</div>