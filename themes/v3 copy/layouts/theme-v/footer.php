<?php
use yii\helpers\Html;
use app\components\SiteHelper;
?>
<footer class="footer">
		   <div class="container-fluid">
			   <div class="d-flex justify-content-between">
				  <span id="date">	copyright &#169; 2024 | <?=Html::a('มูลนิธิรามาธิบดี','https://www.ramafoundation.or.th/')?></span>
	
		          
				    <div class="d-flex justify-content-start gap-4">
            <?=Html::img('@web/banner/banner1.jpg',['style'=> 'width:70px'])?>
      
            <?=Html::img('@web/banner/banner2.png',['style'=> 'width:40px'])?>
     
            <?=Html::img('@web/banner/banner3.png',['style'=> 'width:40px'])?>
  
    </div>
		         <div class="d-flex flex-column justify-content-start">
					<span><?=SiteHelper::getInfo()['website']?></span>
					<span><?=SiteHelper::getInfo()['address']?></span>
		         </div>
		      </div>
		   </div>
		</footer>