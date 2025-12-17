<?php
use yii\bootstrap5\Breadcrumbs;
?>

<style>
   .text-primary-gradient{
      background: linear-gradient(90deg, #0866ad, #f1a57a);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
   }
</style>


   <div class="page-title-box">
      <div class="container-fluid" style="max-width: 1600px;">
         <div class="d-flex flex-column flex-lg-row justify-content-lg-between d-flex align-items-center" style="height: 115px;">
            <div>
               <div class="page-title">
                  <?= Breadcrumbs::widget([
                     'encodeLabels' => false,
                     'homeLink' => [
                        'label' => '<i class="bi bi-house"></i> หน้าหลัก',
                        'url' => '/',
                     ],
                     'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
                     ]) ?>
                              <?=isset($this->blocks['page-title']) ? $this->blocks['page-title'] : '';?>
                           </div>
                        </div>
                        <div>
                           <?= isset($this->blocks['action']) ? $this->blocks['action'] : '' ?>
                        </div>
                     </div>
                  </div>
               </div>
               
      