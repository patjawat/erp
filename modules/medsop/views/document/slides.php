<?php
use app\modules\medsop\assets\MedSopAsset;
use app\modules\medsop\components\RichText;
use yii\helpers\Html;
MedSopAsset::register($this);
$this->title = 'สไลด์ ' . $model->title;
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>ใช้ปุ่มลูกศรซ้ายและขวาเพื่อเปลี่ยนหน้า<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?><?= Html::a('<i class="bi bi-arrow-left me-1"></i>กลับไปหน้าเอกสาร', ['view', 'id' => $model->id], ['class' => 'btn btn-sm btn-light']) ?><?php $this->endBlock(); ?>

<div class="medsop-slide-deck" data-slide-deck tabindex="0">
    <section class="medsop-slide is-active" data-slide>
        <div class="medsop-slide__cover">
            <span class="medsop-slide__type"><?= Html::encode($model->document_type) ?></span>
            <h1><?= Html::encode($model->title) ?></h1>
            <div class="medsop-slide__lead medsop-richtext"><?= RichText::render($model->objective) ?></div>
            <div><strong><?= Html::encode($model->document_no) ?></strong><span>ฉบับที่ <?= number_format($model->current_revision) ?></span></div>
        </div>
    </section>
    <?php foreach ($model->steps as $index => $step): ?>
    <section class="medsop-slide" data-slide aria-hidden="true">
        <div class="medsop-slide__step">
            <header><span>ขั้นตอน <?= $index + 1 ?></span><h2><?= Html::encode($step->title) ?></h2></header>
            <div class="medsop-slide__body">
                <div class="medsop-slide__copy"><div class="medsop-richtext"><?= RichText::render($step->description) ?></div><?php if ($step->caution): ?><aside><strong><i class="bi bi-exclamation-triangle me-2"></i>ข้อควรระวัง</strong><div class="medsop-richtext"><?= RichText::render($step->caution) ?></div></aside><?php endif; ?></div>
                <?php $image = null; foreach ($step->media as $media) { if ($media->media_type === 'image') { $image = $media; break; } } ?>
                <?php if ($image): ?><figure><img src="<?= Html::encode($image->file_path) ?>" alt="ภาพประกอบ <?= Html::encode($step->title) ?>"><figcaption><?= Html::encode($image->file_name) ?></figcaption></figure><?php endif; ?>
            </div>
        </div>
    </section>
    <?php endforeach; ?>
    <footer class="medsop-slide-controls"><button type="button" class="btn btn-light" data-slide-prev><i class="bi bi-arrow-left"></i><span>ก่อนหน้า</span></button><div><strong data-slide-current>1</strong><span>/</span><span data-slide-total><?= count($model->steps) + 1 ?></span></div><button type="button" class="btn btn-primary" data-slide-next><span>ถัดไป</span><i class="bi bi-arrow-right"></i></button><button type="button" class="btn btn-light" data-slide-fullscreen aria-label="แสดงเต็มหน้าจอ"><i class="bi bi-arrows-fullscreen"></i></button></footer>
</div>
<?php
$this->registerJs(<<<'JS'
(function(){const deck=document.querySelector('[data-slide-deck]');if(!deck)return;const slides=[...deck.querySelectorAll('[data-slide]')];let index=0;const current=deck.querySelector('[data-slide-current]');const prev=deck.querySelector('[data-slide-prev]');const next=deck.querySelector('[data-slide-next]');function show(value){index=Math.max(0,Math.min(slides.length-1,value));slides.forEach((slide,i)=>{slide.classList.toggle('is-active',i===index);slide.setAttribute('aria-hidden',i===index?'false':'true')});current.textContent=index+1;prev.disabled=index===0;next.disabled=index===slides.length-1}prev.addEventListener('click',()=>show(index-1));next.addEventListener('click',()=>show(index+1));deck.querySelector('[data-slide-fullscreen]').addEventListener('click',()=>{if(document.fullscreenElement)document.exitFullscreen();else deck.requestFullscreen()});document.addEventListener('keydown',e=>{if(['ArrowRight','PageDown',' '].includes(e.key)){e.preventDefault();show(index+1)}if(['ArrowLeft','PageUp'].includes(e.key)){e.preventDefault();show(index-1)}if(e.key==='Home')show(0);if(e.key==='End')show(slides.length-1)});show(0)})();
JS);
?>
