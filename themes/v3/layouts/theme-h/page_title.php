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
               <div class="container-fluid mt-4">
                  <div class="d-flex justify-content-between">
                     <div>
                        <div class="page-title"  data-aos="fade-left">
                           <h4 class="mb-1 text-primary text-primary-gradient"><?=isset($this->blocks['page-title']) ? $this->blocks['page-title'] : 'ERP';?></h4>
                          
                        </div>
                     </div>
                     <div>
                       <?= isset($this->blocks['action']) ? $this->blocks['action'] : Breadcrumbs::widget([
                                 'encodeLabels' => false,
                                 'homeLink' => [
                                    'label' => '<i class="bi bi-house"></i> หน้าหลัก',
                                    'url' => '/',
                                ],
								'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
								]) ?>
                     </div>
                  </div>
               </div>
            </div>

