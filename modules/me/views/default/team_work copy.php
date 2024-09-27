<?php

use yii\web\View;

?>
<style>

@import url("https://cdnjs.cloudflare.com/ajax/libs/owl-carousel/1.3.2/owl.carousel.css");

@import url("https://cdnjs.cloudflare.com/ajax/libs/owl-carousel/1.3.2/owl.carousel.min.css");

@import url("https://cdnjs.cloudflare.com/ajax/libs/owl-carousel/1.3.2/owl.theme.css");

@import url("https://cdnjs.cloudflare.com/ajax/libs/owl-carousel/1.3.2/owl.transitions.min.css");

@import url("https://cdnjs.cloudflare.com/ajax/libs/owl-carousel/1.3.2/owl.theme.min.css");

@import url("https://cdnjs.cloudflare.com/ajax/libs/owl-carousel/1.3.2/owl.transitions.css");


#owl-demo .item{
    background: #42bdc2;
    padding: 30px 0px;
    margin: 5px;
    color: #FFF;
    -webkit-border-radius: 3px;
    -moz-border-radius: 3px;
    border-radius: 3px;
    text-align: center;
}

.owl-nav{
  position: absolute;
  right: 0;
  top: -35px;
}

.owl-nav button.owl-next, .owl-nav button.owl-prev, .owl-carousel .owl-nav button.owl-next, .owl-carousel .owl-nav button.owl-prev {
    width: 30px;
    height: 30px;
    margin-left: 8px;
    border-radius: 30px;
    border: 1px solid #E2E4E6;
    font-size: 14px;
    background: #E2E4E6;
    color: #373B3E;
    transition: ease all 0.5s;
    -webkit-transition: ease all 0.5s;
    -ms-transition: ease all 0.5s;
}
</style>
<div class="card" style="height:300px;">
                    <div class="card-body">
                        <h6>กลุ่ม/ทีมประสาน</h6>


                        <div id="owl-demo" class="owl-carousel owl-theme">
  <div class="item">

<div class="card-img-top p-2 rounded border border-2 border-secondary-subtle">
<h6>กลุมงาน</h6>
</div>
  
  </div>
  <div class="item"><h1>2</h1></div>
  <div class="item"><h1>3</h1></div>
  <div class="item"><h1>4</h1></div>
  <div class="item"><h1>5</h1></div>
  <div class="item"><h1>6</h1></div>
</div>


                        <!-- <div class="card-img-top p-2 rounded border border-2 border-secondary-subtle">



</div> -->

                    </div>
                </div>
            </div>
<?php
$js = <<< JS

$('.owl-carousel').owlCarousel({
    loop:true,
    margin:10,
    nav:true,
    responsive:{
        0:{
            items:1
        },
        600:{
            items:3
        },
        1000:{
            items:5
        }
    }
})
JS;
$this->registerJs($js,View::POS_END);
?>