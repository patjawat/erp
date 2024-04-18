<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\models\Categorise $model */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'ตำแหน่งในกลุ่มงาน', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-diagram-3"></i> <?=$this->title;?>
<?php $this->endBlock(); ?>

<?php Pjax::begin(['id' => 'hr-container','enablePushState' => true,'timeout' => 50000 ]); ?>

<div class="row">
    <div class="col-xl-8">
      
        <?=$this->render('./position_list',['model' => $model])?>
    </div>
    <div class="col-md-6 col-xl-4">
    <div class="card">
            <div class="card-header">
				<div class="d-flex justify-content-between">

					<div class="d-flex">
						<?=Html::img('@web/img/patjwat2.png',['class' => 'avatar rounded-circle','width' => 30])?>
						<div class="media-body overflow-hidden">
							<h5 class="card-title mb-2 pr-4 text-truncate">
								<a href="#" class="text-dark"
                                title="Option 2 App Design, Development and Maintenance"><?=$model->title?> (อยู่ระหว่างพัฒนา)</a>
							</h5>
							<p class="text-muted mb-3">นายปัจวัฒน์ ศรีบุญเรือง <code>หัวหน้าประสานงาน</code></p>
                    </div>
					
                </div>
				
				
			</div>
            </div>
            <div class="card-body">


            </div>
        </div>
        <!-- end card -->
	

    </div>


</div>
<?php Pjax::end();?>