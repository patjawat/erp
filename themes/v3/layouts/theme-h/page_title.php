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

<?php if(isset($this->blocks['page-title'])):?>
    <div class="page-title-box">
               <div class="container-fluid mt-4">
                  <div class="d-flex flex-column flex-lg-row justify-content-lg-between">
                     <div>
                        <div class="page-title"  data-aos="fade-left">
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

<?php endif;?>