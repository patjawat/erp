<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\components\AppHelper;
use app\modules\dms\models\Documents;
/** @var yii\web\View $this */
/** @var app\modules\dms\models\DocumentSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'หนังสือรับ';

$this->params['breadcrumbs'][] = $this->title;

$dataFile = Yii::getAlias('@app/doc_receive/data.json');
$jsonCount = 0;
if (file_exists($dataFile)) {
    $jsonData = file_get_contents($dataFile);
    $jsonArray = json_decode($jsonData, true);
    if (is_array($jsonArray)) {
        $jsonCount = count($jsonArray);
    }
}
?>
<?php $this->beginBlock('page-title'); ?>
<?php if($searchModel->document_group == 'receive'):?>
<i class="fa-solid fa-download"></i></i> <?= $this->title; ?>
<?php endif; ?>
<?php if($searchModel->document_group == 'send'):?>
<i class="fa-solid fa-paper-plane text-danger"></i></i><?= $this->title; ?>
<?php endif; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php  echo $this->render('@app/modules/dms/menu',['model' =>$searchModel]) ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?php  echo $this->render('@app/modules/dms/menu',['model' =>$searchModel,'active' => 'receive']) ?>
<?php $this->endBlock(); ?>

<style>
.transaction-card {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    margin-bottom: 15px;
    padding: 20px;
    transition: all 0.3s ease;
}

.transaction-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.transaction-card.highlighted {
    border: 2px solid #007bff;
    background: #f8f9ff;
}

.transaction-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    color: white;
}

.icon-transfer {
    background: #6c757d;
}

.icon-failed {
    background: #dc3545;
}

.status-success {
    color: #28a745;
    font-weight: 600;
}

.status-failed {
    color: #dc3545;
    font-weight: 600;
}

.amount-text {
    font-size: 1.1em;
    font-weight: 600;
}

.amount-negative {
    color: #333;
}

.transaction-details {
    font-size: 0.9em;
    color: #6c757d;
}

.dropdown-custom {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 8px 12px;
    background: white;
}

.month-divider {
    background: #e9ecef;
    color: #495057;
    font-weight: 600;
    padding: 10px 20px;
    margin: 20px 0 10px 0;
    border-radius: 8px;
}

.account-badge {
    background: #6c757d;
    color: white;
    padding: 4px 8px;
    border-radius: 20px;
    font-size: 0.8em;
    margin-right: 10px;
}

.bank-icon {
    width: 30px;
    height: 30px;
    background: #e9ecef;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 10px;
}

.filter-section {
    background: white;
    padding: 15px;
    border-radius: 12px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}
</style>


<div class="card position-relative">
    <div class="card-body d-flex justify-content-between align-top align-items-center">
        <?= Html::a('<i class="fa-solid fa-circle-plus"></i> ออกเลข'.$this->title, ['/dms/documents/create','document_group' => $searchModel->document_group], ['class' => 'btn btn-primary shadow rounded-pill', 'data' => ['size' => 'modal-lg']]) ?>
        <?php  echo $this->render('@app/modules/dms/views/documents/_search', ['model' => $searchModel]); ?>
    </div>
</div>

<div class="transaction-card">
    <div class="row align-items-center">
        <div class="col-1">
            เลขที่รับ
            <!-- <div class="transaction-icon icon-transfer">
                        </div> -->
        </div>
        <div class="col-md-6">
            เรื่อง
        </div>
        <div class="col-md-3">
            ผู้ดำเนินการ
        </div>
        <div class="col-md-2 text-end">
            
        </div>
    </div>
</div>
<!-- May 2025 Section -->
<div class="month-divider">
    พฤษภาคม 2025
</div>
<?php foreach($dataProvider->getModels() as $item):?>
<!-- Transaction 1 -->
<div class="transaction-card">
    <div class="row align-items-center">
        <div class="col-1">
            <?php echo $item->doc_regis_number?>
            <!-- <div class="transaction-icon icon-transfer">
                        </div> -->
        </div>
        <div class="col-md-6">
            <div class="fw-bold mb-1 text-primary text-truncate">
                <div class="d-flex flex-column">

                    <span>
                        เรื่อง : <?=Html::a($item->topic,['/dms/documents/view','id' => $item->id])?>
                        <?php echo $item->isFile() ? '<i class="fas fa-paperclip"></i>' : ''?>

                    </span>

                </div>

            </div>
            <div class="transaction-details">
                <span class="fw-bold text-success">
                    <?=$item->doc_number?> |
                </span>
                
            </div>
            <?php echo $item->StackDocumentTags('comment')?>
        </div>
        <div class="col-md-3">
            <div class="d-flex align-items-center">
                 <?php echo $item->viewCreate()['avatar'];?>
            </div>
        </div>
        <div class="col-md-2 text-end">
            <div class="status-success mb-1">
                ปกติ
            </div>
            <!-- <div class="amount-text amount-negative"> <?=$item->doc_number?></div> -->
            <p class="mb-0 transaction-details">
<?php echo $item->viewReceiveDate()?>
</p>
            <span class="transaction-details">
                <?php  echo $item->documentOrg->title ?? '-';?>
            </span>
        </div>

        <!-- <div class="col-md-2 text-end">
            <div class="dropdown float-end">
                <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <i class="fa-solid fa-ellipsis"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <?php echo Html::a('<i class="fa-regular fa-pen-to-square me-2"></i> แก้ไข',['update', 'id' => $item->id],['class' => 'dropdown-item'])?>
                </div>
            </div>
        </div> -->
    </div>
</div>
<?php endforeach?>


<!-- Interactive functionality -->
<script>
// Add click functionality to dropdowns
document.querySelectorAll('.dropdown-custom').forEach(dropdown => {
    dropdown.addEventListener('click', function() {
        this.style.backgroundColor = '#f8f9fa';
        setTimeout(() => {
            this.style.backgroundColor = 'white';
        }, 200);
    });
});

// Add click functionality to transaction cards
document.querySelectorAll('.transaction-card').forEach(card => {
    card.addEventListener('click', function() {
        // Remove highlighted class from all cards
        document.querySelectorAll('.transaction-card').forEach(c => {
            c.classList.remove('highlighted');
        });
        // Add highlighted class to clicked card
        this.classList.add('highlighted');
    });
});

// Filter functionality simulation
const filterButtons = document.querySelectorAll('.dropdown-custom');
filterButtons.forEach(button => {
    button.addEventListener('click', function() {
        // Simple animation for user feedback
        this.style.transform = 'scale(0.98)';
        setTimeout(() => {
            this.style.transform = 'scale(1)';
        }, 100);
    });
});
</script>